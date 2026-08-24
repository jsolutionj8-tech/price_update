<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Price_history extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('price_change_batch_model');
		$this->load->model('email_log_model');
		$this->load->model('product_model');
	}

	public function index()
	{
		$page = (int) ($this->input->get('page') ?: 1);
		$per_page = 15;
		$filters = array(
			'product_id' => $this->input->get('product_id'),
			'status'     => $this->input->get('status'),
			'date_from'  => $this->input->get('date_from'),
			'date_to'    => $this->input->get('date_to'),
		);

		$total = $this->price_change_batch_model->count_all_filtered($filters);
		$data = array(
			'title'    => 'Riwayat Perubahan Harga',
			'batches'  => $this->price_change_batch_model->get_paginated($per_page, ($page - 1) * $per_page, $filters),
			'products' => $this->product_model->get_all(),
			'filters'  => $filters,
			'page'     => $page,
			'total_pages' => (int) ceil($total / $per_page),
		);
		$this->render_view('price_history/index', $data);
	}

	public function detail($id)
	{
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
	 */
	public function resend($id)
	{
		$this->load->library('notifier');
		$result = $this->notifier->dispatch($id);
		$this->session->set_flashdata($result['success'] ? 'success' : 'error',
			$result['success'] ? "Notifikasi dikirim ulang: {$result['sent']} berhasil, {$result['failed']} gagal." : $result['message']);
		redirect('price-history/detail/' . $id);
	}
}
