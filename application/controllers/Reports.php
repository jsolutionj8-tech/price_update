<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reports
 * Import data awal dari Excel/CSV (migrasi dari spreadsheet lama) & export laporan.
 * Menggunakan PhpSpreadsheet (via Composer) — lihat dokumentasi instalasi.
 */
class Reports extends Editor_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('price_change_batch_model');
	}

	public function export()
	{
		if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
			show_error('Library PhpSpreadsheet belum terpasang. Jalankan "composer install" pada root project.');
		}

		$batches = $this->price_change_batch_model->get_paginated(1000, 0, array());

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Riwayat Harga');
		$headers = array('Tanggal Efektif', 'Kode Produk', 'Produk', 'Vendor', 'Diubah Oleh', 'Status Notifikasi', 'Dibuat Pada');
		$sheet->fromArray($headers, NULL, 'A1');

		$row = 2;
		foreach ($batches as $b) {
			$sheet->fromArray(array(
				$b['effective_date'], $b['product_code'], $b['product_name'], $b['vendor_code'],
				$b['changed_by_name'], $b['notify_status'], $b['created_at'],
			), NULL, 'A' . $row);
			$row++;
		}

		$filename = 'laporan_harga_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}

	public function import()
	{
		$this->render_view('reports/import', array('title' => 'Import Data Produk'));
	}

	/**
	 * Import produk awal dari file Excel/CSV sesuai format "Product Price Change List".
	 * Kolom yang dibaca: PRODUCT_CODE, PRODUCT_NAME, VENDOR, MODAL, Target HPP%, dst.
	 */
	public function do_import()
	{
		if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
			show_error('Library PhpSpreadsheet belum terpasang. Jalankan "composer install" pada root project.');
		}

		$file = $_FILES['import_file']['tmp_name'] ?? NULL;
		if (!$file) {
			$this->session->set_flashdata('error', 'File tidak ditemukan.');
			redirect('reports/import');
		}

		$this->load->model('product_model');
		$this->load->model('product_vendor_cost_model');

		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
		$rows = $spreadsheet->getActiveSheet()->toArray();
		$imported = 0;

		// Asumsi baris pertama header; kolom: code, name, vendor_code, modal, target_hpp_pct
		foreach (array_slice($rows, 1) as $row) {
			if (empty($row[0])) continue;
			list($code, $name, $vendor_code, $modal, $target_hpp) = array_pad($row, 5, NULL);

			$existing = $this->product_model->find_by_code($code);
			$product_id = $existing ? $existing['id'] : $this->product_model->create(array(
				'product_code' => $code, 'product_name' => $name, 'brand_id' => 1, 'created_by' => $this->auth_lib->user_id(),
			));

			$vendor = $this->db->where('vendor_code', $vendor_code)->get('vendors')->row_array();
			if ($vendor && $modal !== NULL) {
				$this->product_vendor_cost_model->upsert($product_id, $vendor['id'], array(
					'modal' => (float) $modal,
					'target_hpp_pct' => (float) $target_hpp,
				));
			}
			$imported++;
		}

		$this->session->set_flashdata('success', "{$imported} baris berhasil diimpor.");
		redirect('reports/import');
	}
}
