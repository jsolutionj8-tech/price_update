<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Access_control
 * Halaman "Hak Akses": mengatur menu apa saja yang boleh diakses role EDITOR
 * dan VIEWER. Sengaja tetap Admin_Controller (bukan lewat Menu_access_model)
 * supaya ADMIN tidak bisa mengunci diri sendiri dari halaman ini lewat
 * kesalahan konfigurasi — ADMIN selalu punya akses penuh ke seluruh aplikasi.
 */
class Access_control extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('menu_access_model');
	}

	public function index()
	{
		$data = array('title' => 'Hak Akses') + $this->menu_access_model->get_matrix();
		$this->render_view('access_control/index', $data);
	}

	public function update()
	{
		$role_ids = $this->input->post('role_ids') ?: array();
		$checked  = $this->input->post('menu') ?: array(); // format: menu[role_id][] = menu_id

		$this->menu_access_model->save_matrix($role_ids, $checked);
		$this->session->set_flashdata('success', 'Hak akses berhasil disimpan.');
		redirect('access-control');
	}
}
