<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Competitor_price
 * Master-style CRUD untuk harga kompetitor: daftar berpaging, tombol Tambah,
 * form tambah/edit satu entri per submit. Nama kompetitor selalu diambil dari
 * master data (tabel `competitors`, hanya yang aktif).
 */
class Competitor_price extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'competitor-price';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('competitor_price_model');
		$this->load->model('product_model');
		$this->load->model('competitor_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));

		$total = $this->competitor_price_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'      => 'Harga Kompetitor',
			'prices'     => $this->competitor_price_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'    => $filters,
			'pagination' => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('competitor_price/index', $data);
	}

	public function create()
	{
		$data = array(
			'title'       => 'Tambah Harga Kompetitor',
			'competitors' => $this->competitor_model->get_all_active(),
		);
		$this->render_view('competitor_price/form', $data);
	}

	public function store()
	{
		$product_id = (int) $this->input->post('product_id');
		$competitor_id = (int) $this->input->post('competitor_id');
		$price = $this->input->post('price');
		$date = $this->input->post('captured_date') ?: date('Y-m-d');

		if (!$product_id || !$competitor_id || $price === '' || $price === NULL) {
			$this->session->set_flashdata('error', 'Produk, Kompetitor, dan Harga wajib diisi.');
			redirect('competitor-price/create');
		}

		$data = array(
			'product_id'    => $product_id,
			'competitor_id' => $competitor_id,
			'price'         => (float) $price,
			'captured_date' => $date,
			'updated_by'    => $this->auth_lib->user_id(),
		);

		$existing = $this->competitor_price_model->find_existing($product_id, $competitor_id, $date);
		if ($existing) {
			$this->competitor_price_model->update($existing['id'], $data);
			$this->session->set_flashdata('success', 'Harga kompetitor untuk produk, kompetitor & tanggal tersebut sudah ada — data diperbarui.');
		} else {
			$this->competitor_price_model->create($data);
			$this->session->set_flashdata('success', 'Harga kompetitor berhasil ditambahkan.');
		}
		redirect('competitor-price');
	}

	public function edit($id)
	{
		$row = $this->competitor_price_model->find($id);
		if (!$row) show_404();

		$data = array(
			'title'       => 'Edit Harga Kompetitor',
			'price_row'   => $row,
			'competitors' => $this->competitor_model->get_all_active(),
		);
		$this->render_view('competitor_price/form', $data);
	}

	public function update($id)
	{
		$row = $this->competitor_price_model->find($id);
		if (!$row) show_404();

		$competitor_id = (int) $this->input->post('competitor_id');
		$price = $this->input->post('price');
		$date = $this->input->post('captured_date') ?: date('Y-m-d');

		if (!$competitor_id || $price === '' || $price === NULL) {
			$this->session->set_flashdata('error', 'Kompetitor dan Harga wajib diisi.');
			redirect('competitor-price/edit/' . $id);
		}

		$dup = $this->competitor_price_model->find_existing($row['product_id'], $competitor_id, $date, $id);
		if ($dup) {
			$this->session->set_flashdata('error', 'Sudah ada entri lain untuk produk, kompetitor & tanggal yang sama.');
			redirect('competitor-price/edit/' . $id);
		}

		$this->competitor_price_model->update($id, array(
			'competitor_id' => $competitor_id,
			'price'         => (float) $price,
			'captured_date' => $date,
			'updated_by'    => $this->auth_lib->user_id(),
		));
		$this->session->set_flashdata('success', 'Harga kompetitor berhasil diperbarui.');
		redirect('competitor-price');
	}

	public function delete($id)
	{
		$this->competitor_price_model->delete($id);
		$this->session->set_flashdata('success', 'Data harga kompetitor berhasil dihapus.');
		redirect('competitor-price');
	}
}
