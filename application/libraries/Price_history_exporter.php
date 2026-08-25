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
	 * Bangun file .xlsx dari $filters lalu langsung dikirim ke browser (download).
	 * Method ini exit() di akhir — tidak ada kode setelah pemanggilan yang jalan.
	 */
	public function export_to_browser(array $filters)
	{
		if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
			show_error('Library PhpSpreadsheet belum terpasang. Jalankan "composer install" pada root project.');
		}

		$batches = $this->CI->price_change_batch_model->get_all_filtered($filters);

		// Peta kode -> nama marketplace, termasuk yang sudah nonaktif (data riwayat lama
		// mungkin merujuk marketplace yang kini tidak aktif lagi).
		$channel_names = array();
		foreach ($this->CI->marketplace_model->get_all() as $ch) {
			$channel_names[$ch['channel_code']] = $ch['channel_name'];
		}

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Riwayat Harga');
		$headers = array('Tanggal Efektif', 'Kode Produk', 'Produk', 'Vendor', 'Marketplace', 'Harga Lama', 'Harga Baru', 'Diubah Oleh', 'Status Notifikasi', 'Dibuat Pada');
		$sheet->fromArray($headers, NULL, 'A1');

		$row = 2;
		foreach ($batches as $b) {
			$old = json_decode($b['old_values'], TRUE) ?: array();
			$new = json_decode($b['new_values'], TRUE) ?: array();
			$changed_channels = $new['channels_changed'] ?? array();

			if (empty($changed_channels)) {
				$sheet->fromArray(array(
					$b['effective_date'], $b['product_code'], $b['product_name'], $b['vendor_code'],
					'-', NULL, NULL,
					$b['changed_by_name'], $b['notify_status'], $b['created_at'],
				), NULL, 'A' . $row);
				$row++;
				continue;
			}

			foreach ($changed_channels as $code) {
				$sheet->fromArray(array(
					$b['effective_date'], $b['product_code'], $b['product_name'], $b['vendor_code'],
					$channel_names[$code] ?? $code,
					$old['channel_prices'][$code] ?? NULL,
					$new['channel_prices'][$code] ?? NULL,
					$b['changed_by_name'], $b['notify_status'], $b['created_at'],
				), NULL, 'A' . $row);
				$row++;
			}
		}

		$filename = 'laporan_harga_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}
}
