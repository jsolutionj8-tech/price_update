<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Settings
 * Halaman "Settings": mengatur kredensial SMTP pengirim email notifikasi (host, port,
 * enkripsi, username, password, from_email, from_name), menggantikan kredensial hardcode
 * di application/config/email.php. Sengaja Admin_Controller (bukan lewat Menu_access_model)
 * krn halaman ini menyimpan password SMTP — sama alasannya dgn Access_control.
 */
class Settings extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('smtp_settings_model');
	}

	public function index()
	{
		$data = array(
			'title'    => 'Settings',
			'settings' => $this->smtp_settings_model->get(),
		);
		$this->render_view('settings/index', $data);
	}

	public function update()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('smtp_host', 'SMTP Host', 'required');
		$this->form_validation->set_rules('smtp_port', 'SMTP Port', 'required|integer');
		$this->form_validation->set_rules('smtp_user', 'Email Pengirim', 'required|valid_email');
		$this->form_validation->set_rules('from_email', 'From Email', 'required|valid_email');
		$this->form_validation->set_rules('from_name', 'From Name', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('settings');
		}

		$current = $this->smtp_settings_model->get();

		$data = array(
			'smtp_host'   => $this->input->post('smtp_host', TRUE),
			'smtp_port'   => (int) $this->input->post('smtp_port'),
			'smtp_crypto' => $this->input->post('smtp_crypto', TRUE),
			'smtp_user'   => $this->input->post('smtp_user', TRUE),
			'from_email'  => $this->input->post('from_email', TRUE),
			'from_name'   => $this->input->post('from_name', TRUE),
			'updated_by'  => $this->auth_lib->user_id(),
		);

		// Password sengaja tidak ditampilkan ulang di form (lihat views/settings/index.php)
		// — field dikosongkan berarti "jangan ubah", hanya ditimpa kalau diisi ulang.
		$new_pass = $this->input->post('smtp_pass');
		if ($new_pass !== '' && $new_pass !== NULL) {
			$data['smtp_pass'] = $new_pass;
		} elseif (empty($current['id'])) {
			$this->session->set_flashdata('error', 'Password SMTP wajib diisi.');
			redirect('settings');
		} else {
			$data['smtp_pass'] = $current['smtp_pass'];
		}

		$this->smtp_settings_model->save($data);
		$this->session->set_flashdata('success', 'Pengaturan email pengirim berhasil disimpan.');
		redirect('settings');
	}
}
