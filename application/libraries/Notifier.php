<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notifier
 * Menyusun & mengirim email notifikasi otomatis setiap ada perubahan harga.
 * Menggunakan library Email bawaan CodeIgniter (protocol SMTP). Kredensial pengirim
 * (host/port/enkripsi/username/password/from) diambil dari menu Settings (tabel
 * smtp_settings, lihat Smtp_settings_model) — bukan lagi hardcode di config/email.php,
 * yang sekarang cuma dipakai sebagai fallback kalau menu Settings belum pernah diisi.
 *
 * Dua mode pengiriman:
 *  - dispatch($batch_id): kirim notifikasi untuk SATU batch (dipakai oleh
 *    Price_history::resend() untuk mengirim ulang satu batch tertentu).
 *  - dispatch_group($batch_ids): kirim notifikasi untuk BEBERAPA batch sekaligus,
 *    digabung jadi SATU email per penerima (dipakai oleh Price_update::send_pending()
 *    saat user meng-update banyak produk berurutan lalu klik "Kirim Notifikasi Sekarang",
 *    supaya penerima tidak dibanjiri email terpisah per produk).
 *
 * Alur dispatch() (single):
 *  1. Dipanggil dari Price_update::save() versi lama / Price_history::resend().
 *  2. Menentukan daftar penerima berdasarkan notification_group_channels & notification_group_members
 *     yang cocok dengan kanal harga yang berubah pada batch tsb.
 *  3. Merender template email_templates dengan data batch.
 *  4. Mengirim ke tiap penerima, mencatat hasil ke email_logs.
 *  5. Meng-update status batch: sent / partial / failed.
 */
class Notifier
{
	protected $CI;
	protected $smtpSettings;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('price_change_batch_model');
		$this->CI->load->model('email_log_model');
		$this->CI->load->model('notification_group_model');
		$this->CI->load->model('smtp_settings_model');
		$this->CI->load->model('price_model');
		$this->CI->config->load('email');

		$this->smtpSettings = $this->CI->smtp_settings_model->get();

		$this->CI->load->library('email', array(
			'protocol'     => 'smtp',
			'smtp_host'    => $this->smtpSettings['smtp_host'],
			'smtp_port'    => $this->smtpSettings['smtp_port'],
			'smtp_user'    => $this->smtpSettings['smtp_user'],
			'smtp_pass'    => $this->smtpSettings['smtp_pass'],
			'smtp_crypto'  => $this->smtpSettings['smtp_crypto'],
			// CI3 default smtp_timeout cuma 5 detik kalau tidak di-set eksplisit — sering
			// kepotong duluan sebelum handshake TLS ke Gmail selesai, muncul sbg error
			// SMTP 10060 (connection timeout) padahal koneksinya sendiri sehat. Dari
			// config/email.php spy gampang disesuaikan tanpa perlu ubah kode ini lagi.
			'smtp_timeout' => $this->CI->config->item('smtp_timeout') ?: 30,
			'mailtype'     => $this->CI->config->item('mailtype'),
			'charset'      => $this->CI->config->item('charset'),
			'newline'      => $this->CI->config->item('newline'),
		));
	}

	/**
	 * Proses & kirim notifikasi untuk satu batch perubahan harga.
	 * @param int $batch_id
	 * @return array ringkasan hasil pengiriman
	 */
	public function dispatch($batch_id)
	{
		$batch = $this->CI->price_change_batch_model->get_with_detail($batch_id);
		if (!$batch) {
			return array('success' => FALSE, 'message' => 'Batch tidak ditemukan');
		}

		$this->CI->price_change_batch_model->update_status($batch_id, 'processing');

		$recipients = $this->CI->notification_group_model->get_recipients_for_channels(
			json_decode($batch['new_values'], TRUE)['channels_changed'] ?? array()
		);

		if (empty($recipients)) {
			$this->CI->price_change_batch_model->update_status($batch_id, 'sent');
			return array('success' => TRUE, 'message' => 'Tidak ada penerima terdaftar untuk kanal ini', 'sent' => 0, 'failed' => 0);
		}

		$subject_template = $this->subject_template();
		$body_template    = $this->body_template();
		$subject = $this->render_template($subject_template, $batch);

		$sent_count = 0;
		$failed_count = 0;

		foreach ($recipients as $r) {
			// Isi email hanya menampilkan kanal yang benar-benar dilanggan penerima ini
			// (lihat Notification_group_model::get_recipients_for_channels), bukan semua
			// kanal yang berubah pada batch — supaya sesuai dengan checklist kanal di
			// menu Grup Notifikasi.
			$visible_channels = explode(',', $r['matched_channels']);
			$body = $this->render_template($body_template, $batch, $visible_channels);

			$log_id = $this->CI->email_log_model->create_queued($batch_id, $r['id'], $r['email'], $subject);

			$this->CI->email->clear(TRUE);
			$this->CI->email->from($this->smtpSettings['from_email'], $this->smtpSettings['from_name']);
			$this->CI->email->to($r['email']);
			$this->CI->email->subject($subject);
			$this->CI->email->message($body);

			// Kirim; bungkus try/catch agar satu kegagalan tidak menghentikan penerima lain
			try {
				$ok = $this->CI->email->send();
			} catch (Exception $e) {
				$ok = FALSE;
			}

			if ($ok) {
				$this->CI->email_log_model->mark_sent($log_id);
				$sent_count++;
			} else {
				$this->CI->email_log_model->mark_failed($log_id, $this->CI->email->print_debugger(array('headers')));
				$failed_count++;
			}
		}

		$final_status = 'sent';
		if ($failed_count > 0 && $sent_count > 0) $final_status = 'partial';
		if ($failed_count > 0 && $sent_count === 0) $final_status = 'failed';

		$this->CI->price_change_batch_model->update_status($batch_id, $final_status);

		return array('success' => TRUE, 'sent' => $sent_count, 'failed' => $failed_count, 'status' => $final_status);
	}

	/**
	 * Kirim notifikasi untuk beberapa batch sekaligus, digabung jadi SATU email
	 * per penerima (bukan satu email per batch). Penerima hanya melihat produk
	 * yang relevan dengan kanal langganannya, sama seperti dispatch() tunggal.
	 * Tetap mencatat satu baris email_logs per (penerima, batch) agar histori
	 * per-produk di halaman Riwayat Perubahan tidak berubah — hanya pengiriman
	 * SMTP fisiknya yang digabung.
	 *
	 * @param int[] $batch_ids
	 * @return array ringkasan hasil pengiriman
	 */
	public function dispatch_group(array $batch_ids)
	{
		$batch_ids = array_values(array_unique(array_filter($batch_ids)));
		if (empty($batch_ids)) {
			return array('success' => FALSE, 'message' => 'Tidak ada perubahan harga untuk dikirim');
		}

		$batches = array();
		foreach ($batch_ids as $bid) {
			$batch = $this->CI->price_change_batch_model->get_with_detail($bid);
			if ($batch) {
				$batches[$bid] = $batch;
				$this->CI->price_change_batch_model->update_status($bid, 'processing');
			}
		}
		if (empty($batches)) {
			return array('success' => FALSE, 'message' => 'Batch tidak ditemukan');
		}

		// Kumpulkan penerima per batch, lalu gabungkan per alamat email supaya
		// tiap orang hanya menerima SATU email walau beberapa produk berubah.
		$recipients_by_email = array(); // email => ['user' => row, 'items' => [ ['batch_id'=>, 'batch'=>, 'visible_channels'=>] ]]
		$batch_has_recipient = array();

		foreach ($batches as $bid => $batch) {
			$channels_changed = json_decode($batch['new_values'], TRUE)['channels_changed'] ?? array();
			$recips = $this->CI->notification_group_model->get_recipients_for_channels($channels_changed);
			$batch_has_recipient[$bid] = !empty($recips);

			foreach ($recips as $r) {
				if (!isset($recipients_by_email[$r['email']])) {
					$recipients_by_email[$r['email']] = array('user' => $r, 'items' => array());
				}
				$recipients_by_email[$r['email']]['items'][] = array(
					'batch_id'         => $bid,
					'batch'            => $batch,
					'visible_channels' => explode(',', $r['matched_channels']),
				);
			}
		}

		// Batch tanpa penerima terdaftar sama sekali -> langsung 'sent' (konsisten dgn dispatch() tunggal)
		foreach ($batches as $bid => $batch) {
			if (empty($batch_has_recipient[$bid])) {
				$this->CI->price_change_batch_model->update_status($bid, 'sent');
			}
		}

		if (empty($recipients_by_email)) {
			return array('success' => TRUE, 'message' => 'Tidak ada penerima terdaftar untuk kanal ini', 'batches' => count($batches), 'recipients' => 0, 'sent' => 0, 'failed' => 0);
		}

		$batch_outcomes = array();
		foreach ($batches as $bid => $b) $batch_outcomes[$bid] = array('sent' => 0, 'failed' => 0);

		$total_sent = 0;
		$total_failed = 0;
		// Alasan lampiran PDF gagal dibuat/dipasang (kalau ada) — ditangkap sekali & dikirim
		// balik lewat return value supaya kelihatan di flashdata UI (bukan cuma di server log,
		// yang sering tidak aktif/tidak kebaca di VPS). Lihat Price_update::send_pending() &
		// Price_history::resend_bulk() yg menampilkannya.
		$pdf_warning = null;

		foreach ($recipients_by_email as $email => $info) {
			$subject = '[Update Harga] ' . count($info['items']) . ' Produk - ' . tgl_indo(date('Y-m-d'));
			$body = $this->render_group_template($info['items']);

			$log_ids = array();
			foreach ($info['items'] as $item) {
				$log_ids[$item['batch_id']] = $this->CI->email_log_model->create_queued($item['batch_id'], $info['user']['id'], $email, $subject);
			}

			$this->CI->email->clear(TRUE);
			$this->CI->email->from($this->smtpSettings['from_email'], $this->smtpSettings['from_name']);
			$this->CI->email->to($email);
			$this->CI->email->subject($subject);
			$this->CI->email->message($body);

			// Lampiran PDF berisi ringkasan yang sama dgn isi email (hanya kanal yg dilanggan
			// penerima ini) — supaya penerima punya salinan yang bisa dicetak/diarsipkan.
			// Kalau gagal (library tak terpasang di server, mis. lupa composer install saat
			// deploy ke VPS, atau folder cache font Dompdf tidak writable), JANGAN gagalkan
			// seluruh pengiriman notifikasi — cukup catat alasannya utk ditampilkan di UI.
			if (!class_exists('\Dompdf\Dompdf')) {
				$pdf_warning = $pdf_warning ?: 'Lampiran PDF tidak terpasang: library Dompdf tidak ditemukan di server (jalankan "composer install" di server).';
			} else {
				try {
					$pdfContent = $this->_build_group_pdf($info['items']);
					$this->CI->email->attach($pdfContent, 'attachment', 'ringkasan-update-harga-' . date('Ymd') . '.pdf', 'application/pdf');
				} catch (\Throwable $e) {
					$pdf_warning = $pdf_warning ?: ('Lampiran PDF gagal dibuat: ' . $e->getMessage());
				}
			}

			try {
				$ok = $this->CI->email->send();
			} catch (Exception $e) {
				$ok = FALSE;
			}

			foreach ($info['items'] as $item) {
				$bid = $item['batch_id'];
				if ($ok) {
					$this->CI->email_log_model->mark_sent($log_ids[$bid]);
					$batch_outcomes[$bid]['sent']++;
				} else {
					$this->CI->email_log_model->mark_failed($log_ids[$bid], $this->CI->email->print_debugger(array('headers')));
					$batch_outcomes[$bid]['failed']++;
				}
			}

			if ($ok) $total_sent++; else $total_failed++;
		}

		foreach ($batch_outcomes as $bid => $o) {
			if ($o['sent'] === 0 && $o['failed'] === 0) continue; // sudah ditandai 'sent' di atas (tanpa penerima)
			$status = 'sent';
			if ($o['failed'] > 0 && $o['sent'] > 0) $status = 'partial';
			if ($o['failed'] > 0 && $o['sent'] === 0) $status = 'failed';
			$this->CI->price_change_batch_model->update_status($bid, $status);
		}

		return array(
			'success'     => TRUE,
			'batches'     => count($batches),
			'recipients'  => count($recipients_by_email),
			'sent'        => $total_sent,
			'failed'      => $total_failed,
			'pdf_warning' => $pdf_warning,
		);
	}

	/**
	 * Peta channel_code => channel_name terurut sesuai sort_order di Master Sales
	 * Channel (lihat Price_model::get_channels(), dipakai juga di form Update Harga) —
	 * jadi urutan kolom kanal di tabel email/PDF konsisten dengan urutan di form.
	 */
	protected function _channel_order()
	{
		$order = array();
		foreach ($this->CI->price_model->get_channels() as $ch) {
			$order[$ch['channel_code']] = $ch['channel_name'];
		}
		return $order;
	}

	/**
	 * Susun data mentah $items (satu per produk/batch) jadi struktur matrix siap-render:
	 * daftar kolom kanal yang benar2 dipakai (union semua produk, hanya yg dilanggan
	 * penerima), dan satu baris per produk berisi harga lama/baru per kanal tsb. Dipakai
	 * bersama oleh render_group_template() (HTML email) & _build_group_pdf() (lampiran)
	 * supaya isinya selalu identik, cuma beda format output.
	 */
	protected function _build_price_matrix(array $items)
	{
		$channel_order = $this->_channel_order();
		$used_codes = array();
		$rows = array();

		foreach ($items as $item) {
			$batch = $item['batch'];
			$new = json_decode($batch['new_values'], TRUE) ?: array();
			$old = json_decode($batch['old_values'], TRUE) ?: array();

			$old_prices = array();
			$new_prices = array();
			foreach ((array) ($new['channel_prices'] ?? array()) as $code => $price) {
				if (!in_array($code, $item['visible_channels'], TRUE)) continue;
				$used_codes[$code] = TRUE;
				$new_prices[$code] = $price;
				$old_prices[$code] = $old['channel_prices'][$code] ?? null;
			}

			$rows[] = array(
				'batch_id'       => $batch['id'],
				'product_code'   => $batch['product_code'],
				'product_name'   => $batch['product_name'],
				'effective_date' => $batch['effective_date'],
				'changed_by'     => $batch['changed_by_name'],
				'notes'          => $batch['notes'] ?? '',
				'old'            => $old_prices,
				'new'            => $new_prices,
			);
		}

		// Urutkan kolom sesuai sort_order Master Sales Channel; kanal yg sudah tidak
		// ada di master (mis. dihapus) tetap ditampilkan pakai code-nya sendiri di akhir.
		$channels = array();
		foreach ($channel_order as $code => $name) {
			if (isset($used_codes[$code])) $channels[$code] = $name;
		}
		foreach ($used_codes as $code => $_) {
			if (!isset($channels[$code])) $channels[$code] = $code;
		}

		return array('channels' => $channels, 'rows' => $rows);
	}

	/**
	 * Ringkasan header (jumlah produk / tanggal efektif / diperbarui oleh) utk satu
	 * penerima. Kalau baris-barisnya punya tanggal/pengubah yg berbeda-beda (jarang,
	 * tapi mungkin kalau batch dari sesi update berbeda ikut digabung), tampilkan
	 * "Beberapa tanggal"/nama gabungan alih-alih memaksa satu nilai yg salah.
	 */
	protected function _group_summary(array $rows)
	{
		$dates = array_unique(array_column($rows, 'effective_date'));
		$users = array_unique(array_column($rows, 'changed_by'));

		return array(
			'count'          => count($rows),
			'effective_date' => count($dates) === 1 ? tgl_indo($dates[0]) : 'Beberapa tanggal',
			'changed_by'     => count($users) === 1 ? $users[0] : implode(', ', $users),
		);
	}

	/**
	 * Render body email gabungan sesuai format "Pemberitahuan Internal - Perubahan
	 * Harga Produk": header navy, ringkasan (jumlah produk/tanggal/pengubah), tabel
	 * matrix produk x kanal (baris Lama & Baru per produk), catatan, & kotak Tindak
	 * Lanjut. Hanya menampilkan kanal yang dilanggan penerima ini.
	 */
	protected function render_group_template(array $items)
	{
		$matrix = $this->_build_price_matrix($items);
		$channels = $matrix['channels'];
		$summary = $this->_group_summary($matrix['rows']);

		$header_cols = '<th align="left">Produk</th><th align="left">Status Harga</th>';
		foreach ($channels as $name) $header_cols .= '<th align="left">' . htmlspecialchars($name) . '</th>';
		$header_cols .= '<th align="left">Status</th>';

		$body_rows = '';
		$notes_lines = '';
		foreach ($matrix['rows'] as $row) {
			$product_cell = '<b>' . htmlspecialchars($row['product_name']) . '</b><br><span style="color:#5b6b7d;font-size:11px;">' . htmlspecialchars($row['product_code']) . '</span>';

			$old_cells = '';
			$new_cells = '';
			foreach (array_keys($channels) as $code) {
				$old_cells .= '<td style="color:#94a3b8;text-decoration:line-through;">' . (($row['old'][$code] ?? null) !== null ? rupiah($row['old'][$code]) : '-') . '</td>';
				$new_cells .= '<td><b>' . (array_key_exists($code, $row['new']) ? rupiah($row['new'][$code]) : '-') . '</b></td>';
			}

			$body_rows .= '<tr style="background:#f4f6f9;">'
				. '<td rowspan="2" style="vertical-align:top;">' . $product_cell . '</td>'
				. '<td>Lama</td>' . $old_cells . '<td></td></tr>';
			$body_rows .= '<tr>'
				. '<td>Baru</td>' . $new_cells
				. '<td style="color:#1a7f37;font-weight:bold;">NEW</td></tr>';

			if (!empty($row['notes'])) {
				$notes_lines .= '<div>&bull; <b>' . htmlspecialchars($row['product_name']) . ':</b> ' . htmlspecialchars($row['notes']) . '</div>';
			}
		}

		$notes_block = $notes_lines !== ''
			? '<div style="font-family:Arial,sans-serif;font-size:12px;color:#334155;margin:14px 0;"><b>Catatan:</b>' . $notes_lines . '</div>'
			: '';

		return '<div style="font-family:Arial,sans-serif;max-width:820px;">'
			. '<div style="background:#3D5C6C;color:#fff;padding:18px 22px;">'
			. '<div style="font-size:12px;letter-spacing:.06em;text-transform:uppercase;opacity:.85;">Pemberitahuan Internal</div>'
			. '<div style="font-size:22px;font-weight:bold;margin-top:4px;">Perubahan Harga Produk</div>'
			. '</div>'
			. '<div style="padding:18px 22px;border:1px solid #e2e8f0;border-top:none;">'
			. '<p style="margin:0 0 4px;">Berikut adalah ringkasan harga produk yang telah diperbarui.</p>'
			. '<p style="margin:0 0 16px;">Mohon memastikan harga pada seluruh kanal penjualan sudah sesuai dengan informasi di bawah ini.</p>'
			. '<table cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:13px;margin-bottom:16px;">'
			. '<tr style="background:#eaf1f8;"><th align="left">Jumlah Produk</th><th align="left">Tanggal Efektif</th><th align="left">Diperbarui Oleh</th></tr>'
			. '<tr><td><b>' . $summary['count'] . ' SKU</b></td><td>' . htmlspecialchars($summary['effective_date']) . '</td><td>' . htmlspecialchars($summary['changed_by']) . '</td></tr>'
			. '</table>'
			. '<p style="margin:0 0 12px;">Mohon memastikan harga pada POS, website, marketplace, price tag, dan materi promosi telah diperbarui sesuai daftar berikut.</p>'
			. '<div style="overflow-x:auto;"><table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-size:12px;width:100%;">'
			. '<tr style="background:#3D5C6C;color:#fff;">' . $header_cols . '</tr>'
			. $body_rows
			. '</table></div>'
			. $notes_block
			. '<div style="background:#fff3cd;border-left:4px solid #E34F05;padding:10px 14px;margin-top:16px;font-size:12px;">'
			. '<b style="color:#E34F05;">TINDAK LANJUT:</b> Tim terkait wajib melakukan pengecekan dan konfirmasi setelah seluruh harga berhasil diperbarui.'
			. '</div>'
			. '<p style="font-size:12px;color:#5b6b7d;margin-top:16px;">Ringkasan ini turut dilampirkan dalam bentuk PDF pada email ini. Email dikirim otomatis oleh Sistem Update Harga.</p>'
			. '</div>'
			. '</div>';
	}

	/**
	 * Bangun PDF lampiran email gabungan (isinya sama dengan render_group_template(),
	 * hanya kanal yg dilanggan penerima ini) — dipakai supaya penerima punya salinan
	 * ringkasan yang bisa dicetak/diarsipkan lepas dari isi HTML email.
	 * @param array $items Sama seperti parameter render_group_template().
	 * @return string Isi biner PDF (belum disimpan ke file).
	 */
	protected function _build_group_pdf(array $items)
	{
		$matrix = $this->_build_price_matrix($items);
		$channels = $matrix['channels'];
		$summary = $this->_group_summary($matrix['rows']);

		$html = '<html><head><meta charset="utf-8"><style>
			body { font-family: sans-serif; font-size: 10px; color:#1c2b36; }
			.header { background:#3D5C6C; color:#fff; padding:14px 18px; margin-bottom:16px; }
			.header .label { font-size:10px; letter-spacing:.06em; text-transform:uppercase; opacity:.85; }
			.header .title { font-size:18px; font-weight:bold; margin-top:3px; }
			.summary { width:100%; border-collapse:collapse; margin-bottom:14px; font-size:10px; }
			.summary th, .summary td { border: 1px solid #ccc; padding: 6px 8px; text-align:left; }
			.summary th { background:#eaf1f8; }
			table.matrix { width: 100%; border-collapse: collapse; margin-bottom:16px; font-size:9px; }
			table.matrix th, table.matrix td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
			table.matrix th { background: #3D5C6C; color:#fff; }
			.row-old { color:#94a3b8; text-decoration:line-through; background:#f4f6f9; }
			.row-new b { color:#111; }
			.status-new { color:#1a7f37; font-weight:bold; }
			.notes { font-size:10px; margin-bottom:14px; }
			.tindaklanjut { background:#fff3cd; border-left:4px solid #E34F05; padding:8px 12px; font-size:10px; margin-bottom:10px; }
			.tindaklanjut b { color:#E34F05; }
			.footer { font-size:9px; color:#5b6b7d; text-align:center; margin-top:10px; }
		</style></head><body>';

		$html .= '<div class="header"><div class="label">Pemberitahuan Internal</div><div class="title">Perubahan Harga Produk</div></div>';
		$html .= '<p>Berikut adalah ringkasan harga produk yang telah diperbarui. Mohon memastikan harga pada seluruh kanal penjualan sudah sesuai dengan informasi di bawah ini.</p>';
		$html .= '<table class="summary"><tr><th>Jumlah Produk</th><th>Tanggal Efektif</th><th>Diperbarui Oleh</th></tr>'
			. '<tr><td><b>' . $summary['count'] . ' SKU</b></td><td>' . htmlspecialchars($summary['effective_date']) . '</td><td>' . htmlspecialchars($summary['changed_by']) . '</td></tr></table>';
		$html .= '<p>Mohon memastikan harga pada POS, website, marketplace, price tag, dan materi promosi telah diperbarui sesuai daftar berikut.</p>';

		$html .= '<table class="matrix"><thead><tr><th>Produk</th><th>Status Harga</th>';
		foreach ($channels as $name) $html .= '<th>' . htmlspecialchars($name) . '</th>';
		$html .= '<th>Status</th></tr></thead><tbody>';

		$notes_lines = '';
		foreach ($matrix['rows'] as $row) {
			$product_cell = '<b>' . htmlspecialchars($row['product_name']) . '</b><br><span style="color:#5b6b7d;">' . htmlspecialchars($row['product_code']) . '</span>';

			$html .= '<tr class="row-old"><td rowspan="2">' . $product_cell . '</td><td>Lama</td>';
			foreach (array_keys($channels) as $code) {
				$html .= '<td>' . (($row['old'][$code] ?? null) !== null ? rupiah($row['old'][$code]) : '-') . '</td>';
			}
			$html .= '<td></td></tr>';

			$html .= '<tr class="row-new"><td>Baru</td>';
			foreach (array_keys($channels) as $code) {
				$html .= '<td><b>' . (array_key_exists($code, $row['new']) ? rupiah($row['new'][$code]) : '-') . '</b></td>';
			}
			$html .= '<td class="status-new">NEW</td></tr>';

			if (!empty($row['notes'])) {
				$notes_lines .= '&bull; <b>' . htmlspecialchars($row['product_name']) . ':</b> ' . htmlspecialchars($row['notes']) . '<br>';
			}
		}
		$html .= '</tbody></table>';

		if ($notes_lines !== '') {
			$html .= '<div class="notes"><b>Catatan:</b><br>' . $notes_lines . '</div>';
		}

		$html .= '<div class="tindaklanjut"><b>TINDAK LANJUT:</b> Tim terkait wajib melakukan pengecekan dan konfirmasi setelah seluruh harga berhasil diperbarui.</div>';
		$html .= '<div class="footer">Dokumen ini dibuat sebagai lampiran pemberitahuan perubahan harga internal ATAMBAH &mdash; dicetak ' . htmlspecialchars(date('d/m/Y H:i')) . '</div>';
		$html .= '</body></html>';

		$dompdf = new \Dompdf\Dompdf(array('isRemoteEnabled' => FALSE));
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->loadHtml($html);
		$dompdf->render();
		return $dompdf->output();
	}

	protected function subject_template()
	{
		$tpl = $this->CI->db->get_where('email_templates', array('template_code' => 'PRICE_UPDATE_DEFAULT', 'is_active' => 1))->row_array();
		return $tpl ? $tpl['subject_template'] : '[Update Harga] {{product_name}} - Efektif {{effective_date}}';
	}

	protected function body_template()
	{
		$tpl = $this->CI->db->get_where('email_templates', array('template_code' => 'PRICE_UPDATE_DEFAULT', 'is_active' => 1))->row_array();
		return $tpl ? $tpl['body_template'] : '<p>{{product_name}} berubah harga.</p>{{price_table}}';
	}

	/**
	 * Ganti placeholder {{...}} pada template dengan data batch aktual.
	 * @param array|null $visible_channels Jika diisi, tabel harga hanya menampilkan
	 *        kanal-kanal ini (dipakai agar isi email tiap penerima sesuai langganan
	 *        kanalnya di Grup Notifikasi). Null berarti tampilkan semua kanal.
	 */
	protected function render_template($template, $batch, $visible_channels = null)
	{
		$new = json_decode($batch['new_values'], TRUE);
		$old = json_decode($batch['old_values'], TRUE);

		$price_table = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:13px;">';
		$price_table .= '<tr style="background:#1F3864;color:#fff;"><th>Kanal</th><th>Harga Lama</th><th>Harga Baru</th></tr>';
		if (!empty($new['channel_prices'])) {
			foreach ($new['channel_prices'] as $channel => $new_price) {
				if ($visible_channels !== null && !in_array($channel, $visible_channels, TRUE)) continue;
				$old_price = $old['channel_prices'][$channel] ?? '-';
				$price_table .= '<tr><td>' . htmlspecialchars($channel) . '</td><td>' . rupiah($old_price) . '</td><td><b>' . rupiah($new_price) . '</b></td></tr>';
			}
		}
		$price_table .= '</table>';

		$replacements = array(
			'{{product_name}}'    => $batch['product_name'],
			'{{product_code}}'    => $batch['product_code'],
			'{{changed_by}}'      => $batch['changed_by_name'],
			'{{effective_date}}'  => tgl_indo($batch['effective_date']),
			'{{price_table}}'     => $price_table,
			'{{product_url}}'     => base_url('price-history/detail/' . $batch['id']),
		);

		return strtr($template, $replacements);
	}
}
