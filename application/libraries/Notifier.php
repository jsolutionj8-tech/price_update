<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notifier
 * Menyusun & mengirim email notifikasi otomatis setiap ada perubahan harga.
 * Menggunakan library Email bawaan CodeIgniter (protocol SMTP, lihat config/email.php).
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

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('price_change_batch_model');
		$this->CI->load->model('email_log_model');
		$this->CI->load->model('notification_group_model');
		$this->CI->config->load('email');
		$this->CI->load->library('email', array(
			'protocol'    => $this->CI->config->item('protocol'),
			'smtp_host'   => $this->CI->config->item('smtp_host'),
			'smtp_port'   => $this->CI->config->item('smtp_port'),
			'smtp_user'   => $this->CI->config->item('smtp_user'),
			'smtp_pass'   => $this->CI->config->item('smtp_pass'),
			'smtp_crypto' => $this->CI->config->item('smtp_crypto'),
			'mailtype'    => $this->CI->config->item('mailtype'),
			'charset'     => $this->CI->config->item('charset'),
			'newline'     => $this->CI->config->item('newline'),
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
			$this->CI->email->from($this->CI->config->item('from_email'), $this->CI->config->item('from_name'));
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

		foreach ($recipients_by_email as $email => $info) {
			$subject = '[Update Harga] ' . count($info['items']) . ' Produk - ' . tgl_indo(date('Y-m-d'));
			$body = $this->render_group_template($info['items']);

			$log_ids = array();
			foreach ($info['items'] as $item) {
				$log_ids[$item['batch_id']] = $this->CI->email_log_model->create_queued($item['batch_id'], $info['user']['id'], $email, $subject);
			}

			$this->CI->email->clear(TRUE);
			$this->CI->email->from($this->CI->config->item('from_email'), $this->CI->config->item('from_name'));
			$this->CI->email->to($email);
			$this->CI->email->subject($subject);
			$this->CI->email->message($body);

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
			'success'    => TRUE,
			'batches'    => count($batches),
			'recipients' => count($recipients_by_email),
			'sent'       => $total_sent,
			'failed'     => $total_failed,
		);
	}

	/**
	 * Render body email gabungan: satu blok ringkasan per produk/batch,
	 * hanya menampilkan kanal yang relevan untuk penerima tsb.
	 */
	protected function render_group_template(array $items)
	{
		$blocks = '';
		foreach ($items as $item) {
			$batch = $item['batch'];
			$new = json_decode($batch['new_values'], TRUE);
			$old = json_decode($batch['old_values'], TRUE);

			$rows = '';
			if (!empty($new['channel_prices'])) {
				foreach ($new['channel_prices'] as $channel => $new_price) {
					if (!in_array($channel, $item['visible_channels'], TRUE)) continue;
					$old_price = $old['channel_prices'][$channel] ?? '-';
					$rows .= '<tr><td>' . htmlspecialchars($channel) . '</td><td>' . rupiah($old_price) . '</td><td><b>' . rupiah($new_price) . '</b></td></tr>';
				}
			}

			$blocks .= '<div style="margin-bottom:16px;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;">'
				. '<div style="background:#1F3864;color:#fff;padding:8px 12px;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">'
				. htmlspecialchars($batch['product_name'])
				. ' <span style="font-weight:normal;font-size:12px;">(' . htmlspecialchars($batch['product_code']) . ')</span></div>'
				. '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:13px;width:100%;">'
				. '<tr style="background:#f4f6f9;"><th align="left">Kanal</th><th align="left">Harga Lama</th><th align="left">Harga Baru</th></tr>'
				. $rows
				. '</table>'
				. '<div style="padding:6px 12px;font-family:Arial,sans-serif;font-size:12px;color:#5b6b7d;">Efektif ' . tgl_indo($batch['effective_date'])
				. ' &middot; Diubah oleh ' . htmlspecialchars($batch['changed_by_name'])
				. ' &middot; <a href="' . base_url('price-history/detail/' . $batch['id']) . '">Lihat detail</a></div>'
				. '</div>';
		}

		return '<div style="font-family:Arial,sans-serif;">'
			. '<p>Berikut ringkasan <b>' . count($items) . ' perubahan harga produk</b> yang baru saja diperbarui:</p>'
			. $blocks
			. '<p style="font-size:12px;color:#5b6b7d;">Email ini dikirim otomatis oleh Sistem Update Harga.</p>'
			. '</div>';
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
