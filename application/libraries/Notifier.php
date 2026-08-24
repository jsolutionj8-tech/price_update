<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notifier
 * Menyusun & mengirim email notifikasi otomatis setiap ada perubahan harga.
 * Menggunakan library Email bawaan CodeIgniter (protocol SMTP, lihat config/email.php).
 *
 * Alur:
 *  1. dispatch($batch_id) dipanggil dari Price_update::save() setelah batch tersimpan.
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
