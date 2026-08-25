<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reports
 * Import data awal dari Excel/CSV (migrasi dari spreadsheet lama) & export laporan.
 * Menggunakan PhpSpreadsheet (via Composer) — lihat dokumentasi instalasi.
 */
class Reports extends MY_Controller
{
	protected $menu_key = 'reports';

	/**
	 * Export Riwayat Perubahan Harga ke Excel lewat menu Import/Export (khusus
	 * ADMIN/EDITOR, mengikuti hak akses menu 'reports'). Untuk tombol Export Excel
	 * langsung di halaman Riwayat Perubahan — yang terbuka untuk semua role yang
	 * bisa melihat halaman tsb — lihat Price_history::export().
	 */
	public function export()
	{
		$filters = array(
			'product_id' => $this->input->get('product_id'),
			'status'     => $this->input->get('status'),
			'date_from'  => $this->input->get('date_from'),
			'date_to'    => $this->input->get('date_to'),
		);
		$this->load->library('price_history_exporter');
		$this->price_history_exporter->export_to_browser($filters);
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
