<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Price_history_exporter
 * Logika export Riwayat Perubahan Harga ke Excel, dipakai bersama oleh
 * Reports::export() (menu Import/Export) dan Price_history::export() (tombol
 * Export Excel langsung di halaman Riwayat Perubahan) — supaya masing-masing
 * halaman bisa punya aturan akses sendiri tanpa duplikasi logika export.
 *
 * Satu batch bisa mengubah harga di beberapa marketplace sekaligus, jadi setiap
 * marketplace yang berubah diekspor sebagai baris tersendiri (kolom Marketplace,
 * Harga Lama, Harga Baru) — bukan digabung jadi satu baris per batch.
 */
class Price_history_exporter
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('price_change_batch_model');
		$this->CI->load->model('marketplace_model');
	}

	/**
	 * Susun baris data (satu baris per marketplace yang berubah dalam satu batch)
	 * dari $filters — dipakai bersama oleh export Excel & PDF supaya isinya identik,
	 * hanya beda format file.
	 */
	protected function _build_rows(array $filters)
	{
		$batches = $this->CI->price_change_batch_model->get_all_filtered($filters);

		// Peta kode -> nama marketplace, termasuk yang sudah nonaktif (data riwayat lama
		// mungkin merujuk marketplace yang kini tidak aktif lagi).
		$channel_names = array();
		foreach ($this->CI->marketplace_model->get_all() as $ch) {
			$channel_names[$ch['channel_code']] = $ch['channel_name'];
		}

		$rows = array();
		foreach ($batches as $b) {
			$old = json_decode($b['old_values'], TRUE) ?: array();
			$new = json_decode($b['new_values'], TRUE) ?: array();
			$changed_channels = $new['channels_changed'] ?? array();

			if (empty($changed_channels)) {
				$rows[] = array(
					$b['effective_date'], $b['product_code'], $b['product_name'], $b['vendor_code'],
					'-', NULL, NULL,
					$b['changed_by_name'], $b['notify_status'], $b['created_at'],
				);
				continue;
			}

			foreach ($changed_channels as $code) {
				$rows[] = array(
					$b['effective_date'], $b['product_code'], $b['product_name'], $b['vendor_code'],
					$channel_names[$code] ?? $code,
					$old['channel_prices'][$code] ?? NULL,
					$new['channel_prices'][$code] ?? NULL,
					$b['changed_by_name'], $b['notify_status'], $b['created_at'],
				);
			}
		}
		return $rows;
	}

	/**
	 * Bangun file .xlsx dari $filters lalu langsung dikirim ke browser (download).
	 * Method ini exit() di akhir — tidak ada kode setelah pemanggilan yang jalan.
	 */
	public function export_to_browser(array $filters)
	{
		if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
			show_error('Library PhpSpreadsheet belum terpasang. Jalankan "composer install" pada root project.');
		}

		$headers = array('Tanggal Efektif', 'Kode Produk', 'Produk', 'Vendor', 'Marketplace', 'Harga Lama', 'Harga Baru', 'Diubah Oleh', 'Status Notifikasi', 'Dibuat Pada');

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Riwayat Harga');
		$sheet->fromArray($headers, NULL, 'A1');
		$sheet->fromArray($this->_build_rows($filters), NULL, 'A2');

		$filename = 'laporan_harga_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}

	/**
	 * Bangun file .pdf (tabel sederhana) dari $filters lalu langsung dikirim ke
	 * browser (download). Method ini exit() di akhir.
	 */
	public function export_to_pdf_browser(array $filters)
	{
		if (!class_exists('\Dompdf\Dompdf')) {
			show_error('Library Dompdf belum terpasang. Jalankan "composer install" pada root project.');
		}

		$headers = array('Tanggal Efektif', 'Kode Produk', 'Produk', 'Vendor', 'Marketplace', 'Harga Lama', 'Harga Baru', 'Diubah Oleh', 'Status Notifikasi', 'Dibuat Pada');
		$rows = $this->_build_rows($filters);

		$html = '<html><head><meta charset="utf-8"><style>
			body { font-family: sans-serif; font-size: 10px; }
			h4 { margin: 0 0 10px; }
			table { width: 100%; border-collapse: collapse; }
			th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
			th { background: #f1f1f1; }
			td.num { text-align: right; }
		</style></head><body>';
		$html .= '<h4>Riwayat Perubahan Harga — dicetak ' . htmlspecialchars(date('d/m/Y H:i')) . '</h4>';
		$html .= '<table><thead><tr>';
		foreach ($headers as $h) $html .= '<th>' . htmlspecialchars($h) . '</th>';
		$html .= '</tr></thead><tbody>';
		foreach ($rows as $r) {
			$html .= '<tr>';
			foreach ($r as $i => $v) {
				$is_price = in_array($i, array(5, 6), TRUE);
				$html .= '<td class="' . ($is_price ? 'num' : '') . '">' . ($v === NULL ? '-' : htmlspecialchars((string) $v)) . '</td>';
			}
			$html .= '</tr>';
		}
		if (empty($rows)) {
			$html .= '<tr><td colspan="' . count($headers) . '" style="text-align:center;color:#888;">Tidak ada data.</td></tr>';
		}
		$html .= '</tbody></table></body></html>';

		$dompdf = new \Dompdf\Dompdf(array('isRemoteEnabled' => FALSE));
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->loadHtml($html);
		$dompdf->render();

		$filename = 'laporan_harga_' . date('Ymd_His') . '.pdf';
		$dompdf->stream($filename, array('Attachment' => TRUE));
		exit;
	}
}
