<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Brands
 * Master data Brand (dipakai untuk mengelompokkan produk pada modul Produk).
 * Mengikuti pola sederhana yang sama seperti Categories: hanya nama + status aktif.
 */
class Brands extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'brands';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('brand_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));

		$total = $this->brand_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'      => 'Master Brand',
			'brands'     => $this->brand_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'    => $filters,
			'pagination' => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('brands/index', $data);
	}

	public function create()
	{
		$data = array('title' => 'Tambah Brand');
		$this->render_view('brands/form', $data);
	}

	public function store()
	{
		$name = trim((string) $this->input->post('brand_name', TRUE));

		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Brand wajib diisi.');
			redirect('brands/create');
		}
		if ($this->brand_model->find_by_name($name)) {
			$this->session->set_flashdata('error', 'Brand "' . $name . '" sudah ada.');
			redirect('brands/create');
		}

		$this->brand_model->create(array(
			'brand_name' => $name,
			'is_active'  => 1,
		));
		$this->session->set_flashdata('success', 'Brand berhasil ditambahkan.');
		redirect('brands');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit Brand', 'brand' => $this->brand_model->find($id));
		if (!$data['brand']) show_404();
		$this->render_view('brands/form', $data);
	}

	public function update($id)
	{
		$name = trim((string) $this->input->post('brand_name', TRUE));
		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Brand wajib diisi.');
			redirect('brands/edit/' . $id);
		}

		$this->brand_model->update($id, array(
			'brand_name' => $name,
			'is_active'  => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'Brand berhasil diperbarui.');
		redirect('brands');
	}

	public function delete($id)
	{
		$brand = $this->brand_model->find($id);
		if (!$brand) show_404();

		if ($this->brand_model->count_products($id) > 0) {
			$this->session->set_flashdata('error', 'Brand "' . $brand['brand_name'] . '" tidak bisa dihapus karena masih dipakai pada data produk.');
			redirect('brands');
		}

		$this->brand_model->delete($id);
		$this->session->set_flashdata('success', 'Brand berhasil dihapus.');
		redirect('brands');
	}

	public function activate($id)
	{
		$this->brand_model->set_active($id, 1);
		$this->session->set_flashdata('success', 'Brand berhasil diaktifkan.');
		redirect('brands');
	}
}
