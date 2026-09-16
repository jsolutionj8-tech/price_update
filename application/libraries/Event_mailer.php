<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Event_mailer
 * Kirim email terkait RSVP event (konfirmasi submit, konfirmasi pembayaran, dst) pakai
 * kredensial SMTP yang sama dengan notifikasi harga (menu Settings), lepas dari class
 * Notifier (yang khusus utk batch harga). Dipakai oleh Event_rsvp (konfirmasi submit) &
 * Events (konfirmasi pembayaran saat admin klik Paid).
 *
 * Pakai PHPMailer langsung (bukan library Email bawaan CodeIgniter) — CI3 meng-encode body
 * HTML pakai quoted-printable yg WAJIB dipotong tiap ~76 karakter dgn soft line break
 * "=\r\n" (aturan MIME, tidak bisa dimatikan lewat config 'wordwrap'). Sebagian SMTP
 * relay/anti-spam scanner (terbukti kejadian di Dewaweb) tidak merekonstruksi soft break
 * itu dgn benar saat diteruskan, jadi muncul spasi nyasar di tengah kata ("door" jadi
 * "do or") dan atribut style="..." yg kepotong bikin browser/webmail buang semua styling.
 * PHPMailer dgn Encoding=base64 tidak punya soft-break yg bermakna sama sekali (whitespace
 * di dalam base64 diabaikan total oleh decoder manapun), jadi kebal dari masalah ini.
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

		$body = $this->CI->load->view($view, $data, TRUE);

		$mail = new PHPMailer(TRUE);
		try {
			$mail->isSMTP();
			$mail->Host       = $smtp['smtp_host'];
			$mail->Port       = (int) $smtp['smtp_port'];
			$mail->SMTPAuth   = TRUE;
			$mail->Username   = $smtp['smtp_user'];
			$mail->Password   = $smtp['smtp_pass'];
			$mail->SMTPSecure = $smtp['smtp_crypto'] ?: FALSE;
			$mail->CharSet    = 'UTF-8';
			$mail->Encoding   = PHPMailer::ENCODING_BASE64;

			$mail->setFrom($smtp['from_email'], $smtp['from_name']);
			$mail->addAddress($to);
			$mail->isHTML(TRUE);
			$mail->Subject = $subject;
			$mail->Body    = $body;

			return $mail->send();
		} catch (PHPMailerException $e) {
			log_message('error', 'Event_mailer::send gagal (' . $subject . '): ' . $mail->ErrorInfo);
			return FALSE;
		}
	}
}
