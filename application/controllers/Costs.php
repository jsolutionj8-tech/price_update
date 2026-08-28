<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Costs
 * Master data biaya tambahan (dipakai sebagai komponen tambahan biaya pada
 * modul Sales Channel — satu sales channel bisa memakai satu/lebih biaya).
 */
class Costs extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'costs';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('cost_model');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));

		$total = $this->cost_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'      => 'Master Biaya',
			'costs'      => $this->cost_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'    => $filters,
			'pagination' => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('costs/index', $data);
	}

	public function create()
	{
		$data = array('title' => 'Tambah Biaya');
		$this->render_view('costs/form', $data);
	}

	public function store()
	{
		$name = trim((string) $this->input->post('cost_name', TRUE));
		$cost_type = $this->input->post('cost_type', TRUE) === 'percent' ? 'percent' : 'nominal';
		$amount = $this->_parse_amount($cost_type, (string) $this->input->post('amount', TRUE));

		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Biaya wajib diisi.');
			redirect('costs/create');
		}
		if ($this->cost_model->find_by_name($name)) {
			$this->session->set_flashdata('error', 'Biaya "' . $name . '" sudah ada.');
			redirect('costs/create');
		}
		if ($cost_type === 'percent' && ($amount < 0 || $amount > 100)) {
			$this->session->set_flashdata('error', 'Persentase harus di antara 0 - 100.');
			redirect('costs/create');
		}

		$this->cost_model->create(array(
			'cost_name' => $name,
			'cost_type' => $cost_type,
			'amount'    => $amount,
			'is_active' => 1,
		));
		$this->session->set_flashdata('success', 'Biaya berhasil ditambahkan.');
		redirect('costs');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit Biaya', 'cost' => $this->cost_model->find($id));
		if (!$data['cost']) show_404();
		$this->render_view('costs/form', $data);
	}

	public function update($id)
	{
		$name = trim((string) $this->input->post('cost_name', TRUE));
		$cost_type = $this->input->post('cost_type', TRUE) === 'percent' ? 'percent' : 'nominal';
		$amount = $this->_parse_amount($cost_type, (string) $this->input->post('amount', TRUE));

		if ($name === '') {
			$this->session->set_flashdata('error', 'Nama Biaya wajib diisi.');
			redirect('costs/edit/' . $id);
		}
		if ($cost_type === 'percent' && ($amount < 0 || $amount > 100)) {
			$this->session->set_flashdata('error', 'Persentase harus di antara 0 - 100.');
			redirect('costs/edit/' . $id);
		}

		$this->cost_model->update($id, array(
			'cost_name' => $name,
			'cost_type' => $cost_type,
			'amount'    => $amount,
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'Biaya berhasil diperbarui.');
		redirect('costs');
	}

	public function delete($id)
	{
		$cost = $this->cost_model->find($id);
		if (!$cost) show_404();

		if ($this->cost_model->count_usage($id) > 0) {
			$this->session->set_flashdata('error', 'Biaya "' . $cost['cost_name'] . '" tidak bisa dihapus karena masih dipakai pada satu atau lebih Sales Channel.');
			redirect('costs');
		}

		$this->cost_model->delete($id);
		$this->session->set_flashdata('success', 'Biaya berhasil dihapus.');
		redirect('costs');
	}

	/**
	 * Nominal Rupiah dikirim client sudah polos tanpa titik ribuan (lihat costs/form.php),
	 * tapi tetap dibersihkan lagi di sini (fallback jika JS mati) — jangan pakai (float) langsung
	 * karena titik selalu dibaca sebagai pemisah desimal, bukan pemisah ribuan
	 * (mis. "7.000" jadi 7, bukan 7000).
	 */
	protected function _parse_amount($cost_type, $raw)
	{
		if ($cost_type === 'percent') {
			return (float) str_replace(',', '.', $raw);
		}
		return (float) preg_replace('/[^\d]/', '', $raw);
	}
}
