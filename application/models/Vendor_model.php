<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendor_model extends CI_Model
{
	protected $table = 'vendors';

	public function get_all($filters = array(), $limit = NULL, $offset = 0)
	{
		$this->db->from($this->table);
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('vendor_name', $filters['keyword'])
				->or_like('vendor_code', $filters['keyword'])
				->group_end();
		}
		$this->db->order_by('vendor_code', 'ASC');
		if ($limit !== NULL) $this->db->limit($limit, $offset);
		return $this->db->get()->result_array();
	}

	public function count_all($filters = array())
	{
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('vendor_name', $filters['keyword'])
				->or_like('vendor_code', $filters['keyword'])
				->group_end();
		}
		return $this->db->count_all_results($this->table);
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	public function find_by_code($code)
	{
		return $this->db->where('vendor_code', $code)->get($this->table)->row_array();
	}

	public function create($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function update($id, $data)
	{
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	public function set_active($id, $is_active)
	{
		return $this->db->where('id', $id)->update($this->table, array('is_active' => $is_active));
	}

	public function count_active()
	{
		return $this->db->where('is_active', 1)->count_all_results($this->table);
	}
}
