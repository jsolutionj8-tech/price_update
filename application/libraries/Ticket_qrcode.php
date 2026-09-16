<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ticket_qrcode
 * Bungkus tipis di atas chillerlan/php-qrcode (composer) utk QR code di email konfirmasi
 * pembayaran RSVP event — dipakai oleh Event_rsvp::qrcode() (di-embed via <img> yang
 * menunjuk ke endpoint itu, sama seperti pola Ticket_barcode utk e-ticket).
 */
class Ticket_qrcode
{
	/**
	 * @return string isi biner PNG
	 */
	public function png($code, $scale = 6)
	{
		$options = new \chillerlan\QRCode\QROptions(array(
			'outputType'       => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
			'eccLevel'         => \chillerlan\QRCode\QRCode::ECC_M,
			'scale'            => $scale,
			'imageTransparent' => false,
			// Default chillerlan/php-qrcode: render() balikin STRING data-URI base64
			// ("data:image/png;base64,...."), bukan biner PNG mentah. imageBase64=false
			// wajib diset spy hasilnya biner asli yg bisa langsung di-output sbg image/png.
			'imageBase64'      => false,
		));

		return (new \chillerlan\QRCode\QRCode($options))->render($code);
	}
}
