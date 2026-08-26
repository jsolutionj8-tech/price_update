<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Price_calculator
 * Menghitung SRP Suggest, Markup (%), dan Margin (%) mengikuti formula pada
 * spreadsheet "Product Price Change List" acuan.
 *
 * Input utama kini Margin% (target margin dari harga jual, bukan lagi Target HPP%):
 *   SRP Suggest = Modal / (1 - Margin% / 100)
 * Contoh: Modal 652.500, Margin 30% -> SRP Suggest 932.143.
 *
 * Markup% & Margin% (output) tetap dihitung dari harga jual AKTUAL (kolom "OFFLINE")
 * terhadap Modal, BUKAN dari SRP Suggest — mencerminkan keputusan harga jual
 * sesungguhnya, bukan cuma rekomendasi awal. Jika harga jual aktual belum diisi,
 * Markup/Margin sementara dihitung dari SRP Suggest sebagai fallback.
 *
 * Formula:
 *   SRP Suggest = Modal / (1 - Margin% / 100)
 *   Markup (%)  = ((Harga Jual Aktual - Modal) / Modal) * 100
 *   Margin (%)  = ((Harga Jual Aktual - Modal) / Harga Jual Aktual) * 100
 */
class Price_calculator
{
	/**
	 * @param float $modal
	 * @param float $margin_pct       target margin dari harga jual, contoh: 30 artinya 30%
	 * @param float|null $actual_price  harga jual aktual (mis. kanal Offline) sebagai dasar Markup/Margin
	 * @return array ['srp_suggest'=>.., 'markup_pct'=>.., 'margin_pct'=>.., 'basis'=>'actual_price'|'srp_suggest']
	 */
	public function calculate($modal, $margin_pct, $actual_price = null)
	{
		$modal = (float) $modal;
		$margin_pct = (float) $margin_pct;

		if ($modal <= 0 || $margin_pct <= 0 || $margin_pct >= 100) {
			return array('srp_suggest' => 0, 'markup_pct' => 0, 'margin_pct' => 0, 'basis' => 'srp_suggest');
		}

		$srp_suggest = $modal / (1 - ($margin_pct / 100));

		$reference_price = (!empty($actual_price) && (float) $actual_price > 0) ? (float) $actual_price : $srp_suggest;
		$basis = (!empty($actual_price) && (float) $actual_price > 0) ? 'actual_price' : 'srp_suggest';

		$markup_pct = (($reference_price - $modal) / $modal) * 100;
		$margin_pct_actual = (($reference_price - $modal) / $reference_price) * 100;

		return array(
			'srp_suggest' => round($srp_suggest, 2),
			'markup_pct'  => round($markup_pct, 2),
			'margin_pct'  => round($margin_pct_actual, 2),
			'basis'       => $basis,
		);
	}
}
