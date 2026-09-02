<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
	// Semua role boleh melihat dashboard (default $allowed_roles di MY_Controller)

	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
		$this->load->model('price_change_batch_model');
		$this->load->model('email_log_model');
	}

	public function index()
	{
		$data = array(
			'title'          => 'Dashboard',
			'total_products' => $this->product_model->count_active(),
			'new_products'   => $this->product_model->count_new_this_month(),
			'emails_sent'    => $this->email_log_model->count_sent_this_month(),
			'recent_batches' => $this->price_change_batch_model->recent(10),
			'trend'          => $this->price_change_batch_model->monthly_trend(6),
		);

		$this->render_view('dashboard/index', $data);
	}
}
