<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Marketplaces
 * Master data marketplace / kanal penjualan (dipakai oleh modul Update Harga
 * & Grup Notifikasi). Data disimpan pada tabel `price_channels`.
 */
class Marketplaces extends Editor_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('marketplace_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));
		$data = array(
			'title'        => 'Master Marketplace',
			'marketplaces' => $this->marketplace_model->get_all($filters),
			'filters'      => $filters,
		);
		$this->render_view('marketplaces/index', $data);
	}

	public function create()
	{
		$data = array(
			'title'            => 'Tambah Marketplace',
			'suggested_order'  => $this->marketplace_model->next_sort_order(),
		);
		$this->render_view('marketplaces/form', $data);
	}

	public function store()
	{
		$code = $this->_sanitize_code($this->input->post('channel_code', TRUE));
		$name = trim((string) $this->input->post('channel_name', TRUE));

		if ($code === '' || $name === '') {
			$this->session->set_flashdata('error', 'Kode dan Nama Marketplace wajib diisi.');
			redirect('marketplaces/create');
		}
		if ($this->marketplace_model->find_by_code($code)) {
			$this->session->set_flashdata('error', 'Kode marketplace "' . $code . '" sudah digunakan.');
			redirect('marketplaces/create');
		}

		$this->marketplace_model->create(array(
			'channel_code' => $code,
			'channel_name' => $name,
			'sort_order'   => (int) $this->input->post('sort_order') ?: $this->marketplace_model->next_sort_order(),
			'is_active'    => 1,
		));
		$this->session->set_flashdata('success', 'Marketplace berhasil ditambahkan.');
		redirect('marketplaces');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit Marketplace', 'marketplace' => $this->marketplace_model->find($id));
		if (!$data['marketplace']) show_404();
		$this->render_view('marketplaces/form', $data);
	}

	public function update($id)
	{
		$name = trim((string) $this->input->post('channel_name', TRUE));
		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Marketplace wajib diisi.');
			redirect('marketplaces/edit/' . $id);
		}

		$this->marketplace_model->update($id, array(
			'channel_name' => $name,
			'sort_order'   => (int) $this->input->post('sort_order'),
			'is_active'    => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'Marketplace berhasil diperbarui.');
		redirect('marketplaces');
	}

	public function delete($id)
	{
		$this->marketplace_model->set_active($id, 0);
		$this->session->set_flashdata('success', 'Marketplace berhasil dinonaktifkan.');
		redirect('marketplaces');
	}

	public function activate($id)
	{
		$this->marketplace_model->set_active($id, 1);
		$this->session->set_flashdata('success', 'Marketplace berhasil diaktifkan.');
		redirect('marketplaces');
	}

	protected function _sanitize_code($code)
	{
		$code = strtoupper(trim((string) $code));
		return preg_replace('/[^A-Z0-9_]/', '_', $code);
	}
}
