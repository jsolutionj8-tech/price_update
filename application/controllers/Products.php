<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Editor_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
		$this->load->model('product_vendor_cost_model');
	}

	public function index()
	{
		$filters = array(
			'brand_id' => $this->input->get('brand_id'),
			'keyword'  => $this->input->get('keyword'),
		);
		$data = array(
			'title'    => 'Daftar Produk',
			'products' => $this->product_model->get_all($filters),
			'brands'   => $this->product_model->get_all_brands(),
			'filters'  => $filters,
		);
		$this->render_view('products/index', $data);
	}

	public function create()
	{
		$data = array(
			'title'   => 'Tambah Produk',
			'brands'  => $this->product_model->get_all_brands(),
			'vendors' => $this->product_model->get_all_vendors(),
		);
		$this->render_view('products/form', $data);
	}

	public function store()
	{
		$this->_validate();
		$id = $this->product_model->create(array(
			'product_code' => $this->input->post('product_code', TRUE),
			'product_name' => $this->input->post('product_name', TRUE),
			'brand_id'     => $this->input->post('brand_id', TRUE),
			'unit'         => $this->input->post('unit', TRUE) ?: 'pcs',
			'created_by'   => $this->auth_lib->user_id(),
		));
		$this->session->set_flashdata('success', 'Produk berhasil ditambahkan.');
		redirect('products/edit/' . $id);
	}

	public function edit($id)
	{
		$data = array(
			'title'   => 'Edit Produk',
			'product' => $this->product_model->find($id),
			'brands'  => $this->product_model->get_all_brands(),
			'vendors' => $this->product_model->get_all_vendors(),
			'costs'   => $this->product_vendor_cost_model->get_for_product($id),
		);
		if (!$data['product']) show_404();
		$this->render_view('products/form', $data);
	}

	public function update($id)
	{
		$this->_validate();
		$this->product_model->update($id, array(
			'product_name' => $this->input->post('product_name', TRUE),
			'brand_id'     => $this->input->post('brand_id', TRUE),
			'unit'         => $this->input->post('unit', TRUE) ?: 'pcs',
		));
		$this->session->set_flashdata('success', 'Produk berhasil diperbarui.');
		redirect('products/edit/' . $id);
	}

	public function delete($id)
	{
		$this->product_model->delete($id);
		$this->session->set_flashdata('success', 'Produk berhasil dinonaktifkan.');
		redirect('products');
	}

	protected function _validate()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('product_name', 'Nama Produk', 'required');
		$this->form_validation->set_rules('brand_id', 'Brand', 'required');
		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER'] ?? 'products');
		}
	}
}
