<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth
 * Login / logout. Tidak extends MY_Controller karena belum tentu ada session login.
 */
class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

	public function login()
	{
		if ($this->auth_lib->is_logged_in()) redirect('dashboard');
		$this->load->view('auth/login', array('error' => $this->session->flashdata('error')));
	}

	public function do_login()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('login');
		}

		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password', TRUE);
		$user = $this->user_model->find_by_email($email);

		if (!$user || !password_verify($password, $user['password_hash'])) {
			$this->session->set_flashdata('error', 'Email atau password salah.');
			redirect('login');
		}

		$this->auth_lib->login_session($user);
		$this->user_model->update_last_login($user['id']);
		redirect('dashboard');
	}

	public function logout()
	{
		$this->auth_lib->logout();
		redirect('login');
	}
}
