<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Marketplaces
 * Master data marketplace / kanal penjualan (dipakai oleh modul Update Harga
 * & Grup Notifikasi). Data disimpan pada tabel `price_channels`.
 */
class Marketplaces extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'marketplaces';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('marketplace_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));

		$total = $this->marketplace_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'        => 'Sales Channel',
			'marketplaces' => $this->marketplace_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'      => $filters,
			'pagination'   => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('marketplaces/index', $data);
	}

	public function create()
	{
		$data = array(
			'title'            => 'Tambah Sales Channel',
			'suggested_order'  => $this->marketplace_model->next_sort_order(),
		);
		$this->render_view('marketplaces/form', $data);
	}

	public function store()
	{
		$name = trim((string) $this->input->post('channel_name', TRUE));

		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Sales Channel wajib diisi.');
			redirect('marketplaces/create');
		}

		$this->marketplace_model->create(array(
			'channel_code' => $this->_generate_code($name),
			'channel_name' => $name,
			'sort_order'   => (int) $this->input->post('sort_order') ?: $this->marketplace_model->next_sort_order(),
			'is_active'    => 1,
		));
		$this->session->set_flashdata('success', 'Sales Channel berhasil ditambahkan.');
		redirect('marketplaces');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit Sales Channel', 'marketplace' => $this->marketplace_model->find($id));
		if (!$data['marketplace']) show_404();
		$this->render_view('marketplaces/form', $data);
	}

	public function update($id)
	{
		$name = trim((string) $this->input->post('channel_name', TRUE));
		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Sales Channel wajib diisi.');
			redirect('marketplaces/edit/' . $id);
		}

		$this->marketplace_model->update($id, array(
			'channel_name' => $name,
			'sort_order'   => (int) $this->input->post('sort_order'),
			'is_active'    => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'Sales Channel berhasil diperbarui.');
		redirect('marketplaces');
	}

	public function delete($id)
	{
		$this->marketplace_model->set_active($id, 0);
		$this->session->set_flashdata('success', 'Sales Channel berhasil dinonaktifkan.');
		redirect('marketplaces');
	}

	public function activate($id)
	{
		$this->marketplace_model->set_active($id, 1);
		$this->session->set_flashdata('success', 'Sales Channel berhasil diaktifkan.');
		redirect('marketplaces');
	}

	protected function _generate_code($name)
	{
		$base = strtoupper(trim((string) $name));
		$base = trim(preg_replace('/[^A-Z0-9]+/', '_', $base), '_');
		if ($base === '') {
			$base = 'CHANNEL';
		}

		$code = $base;
		$i = 2;
		while ($this->marketplace_model->find_by_code($code)) {
			$code = $base . '_' . $i;
			$i++;
		}
		return $code;
	}
}
