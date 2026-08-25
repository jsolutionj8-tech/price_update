<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_groups extends MY_Controller
{
	protected $menu_key = 'notification-groups';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('notification_group_model');
		$this->load->model('price_model');
		$this->load->model('user_model');
	}

	public function index()
	{
		$data = array('title' => 'Grup Notifikasi', 'groups' => $this->notification_group_model->get_all());
		$this->render_view('notification_groups/index', $data);
	}

	public function create()
	{
		$data = array(
			'title' => 'Tambah Grup Notifikasi',
			'channels' => $this->price_model->get_channels(),
			'users' => $this->user_model->get_all(),
		);
		$this->render_view('notification_groups/form', $data);
	}

	public function store()
	{
		$id = $this->notification_group_model->create(array(
			'group_name'  => $this->input->post('group_name', TRUE),
			'description' => $this->input->post('description', TRUE),
			'is_active'   => 1,
		));
		$this->notification_group_model->set_channels($id, (array) $this->input->post('channel_ids'));
		$this->notification_group_model->set_members($id, (array) $this->input->post('user_ids'));
		$this->session->set_flashdata('success', 'Grup notifikasi berhasil dibuat.');
		redirect('notification-groups');
	}

	public function edit($id)
	{
		$data = array(
			'title' => 'Edit Grup Notifikasi',
			'group' => $this->notification_group_model->find($id),
			'channels' => $this->price_model->get_channels(),
			'users' => $this->user_model->get_all(),
			'selected_channels' => $this->notification_group_model->get_channel_ids($id),
			'selected_members' => $this->notification_group_model->get_member_ids($id),
		);
		if (!$data['group']) show_404();
		$this->render_view('notification_groups/form', $data);
	}

	public function update($id)
	{
		$this->notification_group_model->update($id, array(
			'group_name'  => $this->input->post('group_name', TRUE),
			'description' => $this->input->post('description', TRUE),
			'is_active'   => $this->input->post('is_active') ? 1 : 0,
		));
		$this->notification_group_model->set_channels($id, (array) $this->input->post('channel_ids'));
		$this->notification_group_model->set_members($id, (array) $this->input->post('user_ids'));
		$this->session->set_flashdata('success', 'Grup notifikasi berhasil diperbarui.');
		redirect('notification-groups');
	}

	public function delete($id)
	{
		$this->notification_group_model->delete($id);
		$this->session->set_flashdata('success', 'Grup notifikasi berhasil dihapus.');
		redirect('notification-groups');
	}
}
