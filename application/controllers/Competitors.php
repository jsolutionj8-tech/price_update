<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Competitors
 * Master data kompetitor (dipakai oleh modul Harga Kompetitor & Update Harga).
 */
class Competitors extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'competitors';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('competitor_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));

		$total = $this->competitor_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'       => 'Master Kompetitor',
			'competitors' => $this->competitor_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'     => $filters,
			'pagination'  => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
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
		$name = trim((string) $this->input->post('competitor_name', TRUE));

		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Kompetitor wajib diisi.');
			redirect('competitors/create');
		}

		$this->competitor_model->create(array(
			'competitor_code' => $this->_generate_code($name),
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
		$competitor = $this->competitor_model->find($id);
		if (!$competitor) show_404();

		if ($this->competitor_model->count_usage($id) > 0) {
			$this->session->set_flashdata('error', 'Kompetitor "' . $competitor['competitor_name'] . '" tidak bisa dihapus karena masih memiliki data harga kompetitor tercatat.');
			redirect('competitors');
		}

		$this->competitor_model->delete($id);
		$this->session->set_flashdata('success', 'Kompetitor berhasil dihapus.');
		redirect('competitors');
	}

	public function activate($id)
	{
		$this->competitor_model->set_active($id, 1);
		$this->session->set_flashdata('success', 'Kompetitor berhasil diaktifkan.');
		redirect('competitors');
	}

	protected function _generate_code($name)
	{
		$base = strtoupper(trim((string) $name));
		$base = trim(preg_replace('/[^A-Z0-9]+/', '_', $base), '_');
		if ($base === '') {
			$base = 'KOMPETITOR';
		}

		$code = $base;
		$i = 2;
		while ($this->competitor_model->find_by_code($code)) {
			$code = $base . '_' . $i;
			$i++;
		}
		return $code;
	}
}
