<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Categories
 * Master data kategori barang (dipakai untuk mengelompokkan produk pada modul Produk).
 * Mengikuti pola sederhana yang sama seperti master Brand: hanya nama + status aktif.
 */
class Categories extends Editor_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('category_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));
		$data = array(
			'title'      => 'Master Kategori Barang',
			'categories' => $this->category_model->get_all($filters),
			'filters'    => $filters,
		);
		$this->render_view('categories/index', $data);
	}

	public function create()
	{
		$data = array('title' => 'Tambah Kategori Barang');
		$this->render_view('categories/form', $data);
	}

	public function store()
	{
		$name = trim((string) $this->input->post('category_name', TRUE));

		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Kategori wajib diisi.');
			redirect('categories/create');
		}
		if ($this->category_model->find_by_name($name)) {
			$this->session->set_flashdata('error', 'Kategori "' . $name . '" sudah ada.');
			redirect('categories/create');
		}

		$this->category_model->create(array(
			'category_name' => $name,
			'is_active'     => 1,
		));
		$this->session->set_flashdata('success', 'Kategori barang berhasil ditambahkan.');
		redirect('categories');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit Kategori Barang', 'category' => $this->category_model->find($id));
		if (!$data['category']) show_404();
		$this->render_view('categories/form', $data);
	}

	public function update($id)
	{
		$name = trim((string) $this->input->post('category_name', TRUE));
		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Kategori wajib diisi.');
			redirect('categories/edit/' . $id);
		}

		$this->category_model->update($id, array(
			'category_name' => $name,
			'is_active'     => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'Kategori barang berhasil diperbarui.');
		redirect('categories');
	}

	public function delete($id)
	{
		$this->category_model->set_active($id, 0);
		$this->session->set_flashdata('success', 'Kategori barang berhasil dinonaktifkan.');
		redirect('categories');
	}

	public function activate($id)
	{
		$this->category_model->set_active($id, 1);
		$this->session->set_flashdata('success', 'Kategori barang berhasil diaktifkan.');
		redirect('categories');
	}
}
