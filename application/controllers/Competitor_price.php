<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Competitor_price extends Editor_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
		$this->load->model('price_model');
	}

	public function index()
	{
		$data = array(
			'title'       => 'Harga Kompetitor',
			'products'    => $this->product_model->get_all(),
			'competitors' => $this->price_model->get_competitors(),
		);
		$this->render_view('competitor_price/index', $data);
	}

	public function save()
	{
		$product_id = (int) $this->input->post('product_id');
		$date = $this->input->post('captured_date') ?: date('Y-m-d');
		$competitors = $this->price_model->get_competitors();

		foreach ($competitors as $c) {
			$price = $this->input->post('price_' . $c['competitor_code']);
			if ($price === NULL || $price === '') continue;
			$this->price_model->upsert_competitor_price($product_id, $c['id'], (float) $price, $date, $this->auth_lib->user_id());
		}

		$this->session->set_flashdata('success', 'Harga kompetitor berhasil disimpan.');
		redirect('competitor-price');
	}
}
