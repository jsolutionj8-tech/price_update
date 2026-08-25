<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Competitors
 * Master data kompetitor (dipakai oleh modul Harga Kompetitor & Update Harga).
 */
class Competitors extends Editor_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('competitor_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));
		$data = array(
			'title'       => 'Master Kompetitor',
			'competitors' => $this->competitor_model->get_all($filters),
			'filters'     => $filters,
		);
		$this->render_view('competitors/index', $data);
	}

	public function create()
	{
		$data = array('title' => 'Tambah Kompetitor');
		$this->render_view('competitors/form', $data);
	}

	public function store()
	{
		$code = $this->_sanitize_code($this->input->post('competitor_code', TRUE));
		$name = trim((string) $this->input->post('competitor_name', TRUE));

		if ($code === '' || $name === '') {
			$this->session->set_flashdata('error', 'Kode dan Nama Kompetitor wajib diisi.');
			redirect('competitors/create');
		}
		if ($this->competitor_model->find_by_code($code)) {
			$this->session->set_flashdata('error', 'Kode kompetitor "' . $code . '" sudah digunakan.');
			redirect('competitors/create');
		}

		$this->competitor_model->create(array(
			'competitor_code' => $code,
			'competitor_name' => $name,
			'website_url'     => $this->input->post('website_url', TRUE) ?: NULL,
			'is_active'       => 1,
		));
		$this->session->set_flashdata('success', 'Kompetitor berhasil ditambahkan.');
		redirect('competitors');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit Kompetitor', 'competitor' => $this->competitor_model->find($id));
		if (!$data['competitor']) show_404();
		$this->render_view('competitors/form', $data);
	}

	public function update($id)
	{
		$name = trim((string) $this->input->post('competitor_name', TRUE));
		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Kompetitor wajib diisi.');
			redirect('competitors/edit/' . $id);
		}

		$this->competitor_model->update($id, array(
			'competitor_name' => $name,
			'website_url'     => $this->input->post('website_url', TRUE) ?: NULL,
			'is_active'       => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'Kompetitor berhasil diperbarui.');
		redirect('competitors');
	}

	public function delete($id)
	{
		$this->competitor_model->set_active($id, 0);
		$this->session->set_flashdata('success', 'Kompetitor berhasil dinonaktifkan.');
		redirect('competitors');
	}

	public function activate($id)
	{
		$this->competitor_model->set_active($id, 1);
		$this->session->set_flashdata('success', 'Kompetitor berhasil diaktifkan.');
		redirect('competitors');
	}

	protected function _sanitize_code($code)
	{
		$code = strtoupper(trim((string) $code));
		return preg_replace('/[^A-Z0-9_]/', '_', $code);
	}
}
