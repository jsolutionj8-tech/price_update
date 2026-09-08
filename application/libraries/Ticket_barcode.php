<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ticket_barcode
 * Bungkus tipis di atas picqer/php-barcode-generator (composer) utk barcode Code128 tiket
 * event — dipakai oleh Event_rsvp::barcode() (ditampilkan di e-ticket & di-embed di email
 * konfirmasi lewat <img> yang menunjuk ke endpoint itu, bukan attachment/CID).
 */
class Ticket_barcode
{
	/**
	 * @return string isi biner PNG (transparan, teks hitam)
	 */
	public function png($code, $width_factor = 2, $height = 60)
	{
		$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
		return $generator->getBarcode($code, $generator::TYPE_CODE_128, $width_factor, $height, array(0, 0, 0));
	}
}
