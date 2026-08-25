<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
	protected $menu_key = 'products';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
		$this->load->model('product_vendor_cost_model');
	}

	const PER_PAGE = 25;

	public function index()
	{
		$filters = array(
			'brand_id'    => $this->input->get('brand_id'),
			'category_id' => $this->input->get('category_id'),
			'keyword'     => $this->input->get('keyword'),
		);

		$total = $this->product_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'      => 'Daftar Produk',
			'products'   => $this->product_model->get_all($filters, self::PER_PAGE, $offset),
			'brands'     => $this->product_model->get_all_brands(),
			'categories' => $this->product_model->get_all_categories(),
			'filters'    => $filters,
			'pagination' => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('products/index', $data);
	}

	public function create()
	{
		$data = array(
			'title'      => 'Tambah Produk',
			'brands'     => $this->product_model->get_all_brands(),
			'categories' => $this->product_model->get_all_categories(),
			'vendors'    => $this->product_model->get_all_vendors(),
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
			'category_id'  => $this->input->post('category_id', TRUE) ?: NULL,
			'unit'         => $this->input->post('unit', TRUE) ?: 'pcs',
			'created_by'   => $this->auth_lib->user_id(),
		));
		$this->session->set_flashdata('success', 'Produk berhasil ditambahkan.');
		redirect('products/edit/' . $id);
	}

	public function edit($id)
	{
		$data = array(
			'title'      => 'Edit Produk',
			'product'    => $this->product_model->find($id),
			'brands'     => $this->product_model->get_all_brands(),
			'categories' => $this->product_model->get_all_categories(),
			'vendors'    => $this->product_model->get_all_vendors(),
			'costs'      => $this->product_vendor_cost_model->get_for_product($id),
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
			'category_id'  => $this->input->post('category_id', TRUE) ?: NULL,
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

	/**
	 * Endpoint AJAX bersama: cari produk (kode/nama) untuk dropdown pencarian
	 * Select2 di berbagai modul (Harga Kompetitor, Riwayat Perubahan, dst).
	 * Dibatasi 20 hasil — katalog produk terlalu besar untuk <select> biasa.
	 */
	public function search()
	{
		$keyword = trim((string) $this->input->get('q'));
		$results = $keyword === '' ? array() : $this->product_model->get_all(array('keyword' => $keyword), 20, 0);

		$out = array();
		foreach ($results as $p) {
			$out[] = array('id' => (int) $p['id'], 'code' => $p['product_code'], 'name' => $p['product_name']);
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($out));
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
