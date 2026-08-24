<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	protected $table = 'users';

	public function find_by_email($email)
	{
		return $this->db->select('users.*, roles.role_code, roles.role_name')
			->from($this->table)
			->join('roles', 'roles.id = users.role_id')
			->where('users.email', $email)
			->where('users.is_active', 1)
			->get()->row_array();
	}

	public function get_all()
	{
		return $this->db->select('users.*, roles.role_name, roles.role_code')
			->from($this->table)
			->join('roles', 'roles.id = users.role_id')
			->order_by('users.full_name', 'ASC')
			->get()->result_array();
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	public function create($data)
	{
		$data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
		unset($data['password']);
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function update($id, $data)
	{
		if (!empty($data['password'])) {
			$data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
		}
		unset($data['password']);
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	public function delete($id)
	{
		return $this->db->where('id', $id)->update($this->table, array('is_active' => 0));
	}

	public function update_last_login($id)
	{
		$this->db->where('id', $id)->update($this->table, array('last_login_at' => date('Y-m-d H:i:s')));
	}

	public function get_all_roles()
	{
		return $this->db->get('roles')->result_array();
	}
}
