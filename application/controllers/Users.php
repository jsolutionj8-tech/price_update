<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

	public function index()
	{
		$data = array('title' => 'Manajemen User', 'users' => $this->user_model->get_all());
		$this->render_view('users/index', $data);
	}

	public function create()
	{
		$data = array('title' => 'Tambah User', 'roles' => $this->user_model->get_all_roles());
		$this->render_view('users/form', $data);
	}

	public function store()
	{
		$this->_validate(TRUE);
		$this->user_model->create(array(
			'full_name' => $this->input->post('full_name', TRUE),
			'email'     => $this->input->post('email', TRUE),
			'phone'     => $this->input->post('phone', TRUE),
			'role_id'   => $this->input->post('role_id', TRUE),
			'password'  => $this->input->post('password'),
			'is_active' => 1,
		));
		$this->session->set_flashdata('success', 'User berhasil ditambahkan.');
		redirect('users');
	}

	public function edit($id)
	{
		$data = array('title' => 'Edit User', 'user' => $this->user_model->find($id), 'roles' => $this->user_model->get_all_roles());
		if (!$data['user']) show_404();
		$this->render_view('users/form', $data);
	}

	public function update($id)
	{
		$this->_validate(FALSE);
		$this->user_model->update($id, array(
			'full_name' => $this->input->post('full_name', TRUE),
			'phone'     => $this->input->post('phone', TRUE),
			'role_id'   => $this->input->post('role_id', TRUE),
			'password'  => $this->input->post('password'),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		));
		$this->session->set_flashdata('success', 'User berhasil diperbarui.');
		redirect('users');
	}

	public function delete($id)
	{
		$this->user_model->delete($id);
		$this->session->set_flashdata('success', 'User berhasil dinonaktifkan.');
		redirect('users');
	}

	protected function _validate($require_password)
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('full_name', 'Nama', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('role_id', 'Role', 'required|integer');
		if ($require_password) $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER'] ?? 'users');
		}
	}
}
