<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vendors
 * Master data vendor (dipakai oleh modul Produk, Update Harga, dan Riwayat Perubahan).
 */
class Vendors extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'vendors';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('vendor_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));

		$total = $this->vendor_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'      => 'Master Vendor',
			'vendors'    => $this->vendor_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'    => $filters,
			'pagination' => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('vendors/index', $data);
	}

	public function create()
	{
		$data = array('title' => 'Tambah Vendor');
		$this->render_view('vendors/form', $data);
	}

	public function store()
	{
		$code = $this->_sanitize_code($this->input->post('vendor_code', TRUE));
		$name = trim((string) $this->input->post('vendor_name', TRUE));

		if ($code === '') {
			$this->session->set_flashdata('error', 'Kode Vendor wajib diisi.');
			redirect('vendors/create');
		}
		if ($this->vendor_model->find_by_code($code)) {
			$this->session->set_flashdata('error', 'Kode vendor "' . $code . '" sudah digunakan.');
			redirect('vendors/create');
		}

		$this->vendor_model->create(array(
			'vendor_code'     => $code,
			'vendor_name'     => $name ?: NULL,
			'vendor_category' => $this->input->post('vendor_category', TRUE) ?: NULL,
			'contact_info'    => $this->input->post('contact_info', TRUE) ?: NULL,
			'is_active'       => 1,
		));
		$this->session->set_flashdata('success', 'Vendor berhasil ditambahkan.');
		redirect('vendors');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit Vendor', 'vendor' => $this->vendor_model->find($id));
		if (!$data['vendor']) show_404();
		$this->render_view('vendors/form', $data);
	}

	public function update($id)
	{
		$this->vendor_model->update($id, array(
			'vendor_name'     => trim((string) $this->input->post('vendor_name', TRUE)) ?: NULL,
			'vendor_category' => $this->input->post('vendor_category', TRUE) ?: NULL,
			'contact_info'    => $this->input->post('contact_info', TRUE) ?: NULL,
			'is_active'       => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'Vendor berhasil diperbarui.');
		redirect('vendors');
	}

	public function delete($id)
	{
		$this->vendor_model->set_active($id, 0);
		$this->session->set_flashdata('success', 'Vendor berhasil dinonaktifkan.');
		redirect('vendors');
	}

	public function activate($id)
	{
		$this->vendor_model->set_active($id, 1);
		$this->session->set_flashdata('success', 'Vendor berhasil diaktifkan.');
		redirect('vendors');
	}

	protected function _sanitize_code($code)
	{
		$code = strtoupper(trim((string) $code));
		return preg_replace('/[^A-Z0-9_-]/', '_', $code);
	}
}
