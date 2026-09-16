<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event_mailer
 * Kirim email terkait RSVP event (konfirmasi submit, konfirmasi pembayaran, dst) pakai
 * kredensial SMTP yang sama dengan notifikasi harga (menu Settings), lepas dari class
 * Notifier (yang khusus utk batch harga). Dipakai oleh Event_rsvp (konfirmasi submit) &
 * Events (konfirmasi pembayaran saat admin klik Paid).
 */
class Event_mailer
{
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * @return bool TRUE kalau berhasil dikirim, FALSE kalau SMTP belum diset / gagal kirim
	 *              (kegagalan TIDAK melempar exception — pemanggil tetap lanjut tanpa email).
	 */
	public function send($to, $subject, $view, array $data)
	{
		$this->CI->load->model('smtp_settings_model');
		$smtp = $this->CI->smtp_settings_model->get();
		if (empty($smtp)) return FALSE;

		$this->CI->load->config('email');
		$this->CI->load->library('email', array(
			'protocol'     => 'smtp',
			'smtp_host'    => $smtp['smtp_host'],
			'smtp_port'    => $smtp['smtp_port'],
			'smtp_user'    => $smtp['smtp_user'],
			'smtp_pass'    => $smtp['smtp_pass'],
			'smtp_crypto'  => $smtp['smtp_crypto'],
			'smtp_timeout' => $this->CI->config->item('smtp_timeout') ?: 30,
			'mailtype'     => $this->CI->config->item('mailtype'),
			'charset'      => $this->CI->config->item('charset'),
			'newline'      => $this->CI->config->item('newline'),
		));

		$body = $this->CI->load->view($view, $data, TRUE);

		$this->CI->email->clear(TRUE);
		$this->CI->email->from($smtp['from_email'], $smtp['from_name']);
		$this->CI->email->to($to);
		$this->CI->email->subject($subject);
		$this->CI->email->message($body);

		try {
			return (bool) $this->CI->email->send();
		} catch (Exception $e) {
			log_message('error', 'Event_mailer::send gagal (' . $subject . '): ' . $e->getMessage());
			return FALSE;
		}
	}
}
