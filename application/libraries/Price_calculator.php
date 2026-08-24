<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Price_calculator
 * Menghitung SRP Suggest, Markup (%), dan Margin (%) mengikuti formula pada
 * spreadsheet "Product Price Change List" acuan.
 *
 * Penting — cara Markup/Margin dihitung:
 * Pada data acuan (Modal 652.500, Target HPP 70% -> SRP Suggest 932.143), harga jual
 * AKTUAL yang ditetapkan tim (kolom "OFFLINE") adalah 830.000 — lebih rendah dari SRP
 * Suggest. Markup (27%) dan Margin (21%) pada tabel dihitung dari harga jual AKTUAL
 * tersebut terhadap Modal, BUKAN dari SRP Suggest. SRP Suggest hanya dipakai sebagai
 * "harga acuan/rekomendasi awal", sedangkan Markup% & Margin% final mencerminkan
 * keputusan harga jual sesungguhnya (mis. harga kanal Offline sebagai referensi utama).
 *
 * Formula:
 *   SRP Suggest = Modal / (Target HPP% / 100)
 *   Markup (%)  = ((Harga Jual Aktual - Modal) / Modal) * 100
 *   Margin (%)  = ((Harga Jual Aktual - Modal) / Harga Jual Aktual) * 100
 *
 * Jika harga jual aktual belum diisi, Markup/Margin sementara dihitung dari SRP Suggest
 * sebagai fallback (agar preview tetap menampilkan angka sebelum user mengisi harga kanal).
 */
class Price_calculator
{
	/**
	 * @param float $modal
	 * @param float $target_hpp_pct   contoh: 70 artinya 70%
	 * @param float|null $actual_price  harga jual aktual (mis. kanal Offline) sebagai dasar Markup/Margin
	 * @return array ['srp_suggest'=>.., 'markup_pct'=>.., 'margin_pct'=>.., 'basis'=>'actual_price'|'srp_suggest']
	 */
	public function calculate($modal, $target_hpp_pct, $actual_price = null)
	{
		$modal = (float) $modal;
		$target_hpp_pct = (float) $target_hpp_pct;

		if ($modal <= 0 || $target_hpp_pct <= 0) {
			return array('srp_suggest' => 0, 'markup_pct' => 0, 'margin_pct' => 0, 'basis' => 'srp_suggest');
		}

		$srp_suggest = $modal / ($target_hpp_pct / 100);

		$reference_price = (!empty($actual_price) && (float) $actual_price > 0) ? (float) $actual_price : $srp_suggest;
		$basis = (!empty($actual_price) && (float) $actual_price > 0) ? 'actual_price' : 'srp_suggest';

		$markup_pct = (($reference_price - $modal) / $modal) * 100;
		$margin_pct = (($reference_price - $modal) / $reference_price) * 100;

		return array(
			'srp_suggest' => round($srp_suggest, 2),
			'markup_pct'  => round($markup_pct, 2),
			'margin_pct'  => round($margin_pct, 2),
			'basis'       => $basis,
		);
	}
}
