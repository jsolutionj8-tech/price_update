<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Price_history extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'price-history';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('price_change_batch_model');
		$this->load->model('email_log_model');
		$this->load->model('product_model');
	}

	public function index()
	{
		$filters = array(
			'product_id' => $this->input->get('product_id'),
			'status'     => $this->input->get('status'),
			'date_from'  => $this->input->get('date_from'),
			'date_to'    => $this->input->get('date_to'),
		);

		$total = $this->price_change_batch_model->count_all_filtered($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'            => 'Riwayat Perubahan Harga',
			'batches'          => $this->price_change_batch_model->get_paginated(self::PER_PAGE, $offset, $filters),
			'filters'          => $filters,
			'selected_product' => !empty($filters['product_id']) ? $this->product_model->find($filters['product_id']) : NULL,
			'pagination'       => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('price_history/index', $data);
	}

	/**
	 * Detail satu batch riwayat, termasuk tombol "Kirim Ulang Notifikasi" — sengaja
	 * dibatasi ke ADMIN/EDITOR saja (bukan lewat matriks Hak Akses yang bisa diubah
	 * ADMIN, tapi hardcode) karena halaman ini menampilkan aksi kirim ulang email,
	 * bukan sekadar melihat. VIEWER tetap bisa melihat daftar riwayat di halaman
	 * index, hanya tombol "Detail"-nya yang dinonaktifkan (lihat views/price_history/index.php).
	 */
	public function detail($id)
	{
		$this->auth_lib->require_role(array('ADMIN', 'EDITOR'));

		$batch = $this->price_change_batch_model->get_with_detail($id);
		if (!$batch) show_404();

		$data = array(
			'title' => 'Detail Perubahan Harga',
			'batch' => $batch,
			'old_values' => json_decode($batch['old_values'], TRUE),
			'new_values' => json_decode($batch['new_values'], TRUE),
			'email_logs' => $this->email_log_model->get_by_batch($id),
		);
		$this->render_view('price_history/detail', $data);
	}

	/**
	 * Kirim ulang notifikasi untuk batch tertentu (mis. jika sebelumnya gagal/partial).
	 * Sama seperti detail(), dibatasi ke ADMIN/EDITOR.
	 */
	public function resend($id)
	{
		$this->auth_lib->require_role(array('ADMIN', 'EDITOR'));

		$this->load->library('notifier');
		$result = $this->notifier->dispatch($id);
		$this->session->set_flashdata($result['success'] ? 'success' : 'error',
			$result['success'] ? "Notifikasi dikirim ulang: {$result['sent']} berhasil, {$result['failed']} gagal." : $result['message']);
		redirect('price-history/detail/' . $id);
	}

	/**
	 * Export Excel langsung dari halaman Riwayat Perubahan — terbuka untuk semua
	 * role yang bisa melihat halaman ini (mengikuti $menu_key di atas, bukan
	 * dibatasi tambahan seperti detail()/resend()).
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
}
