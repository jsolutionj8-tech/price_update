<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cost_model extends CI_Model
{
	protected $table = 'costs';

	public function get_all($filters = array(), $limit = NULL, $offset = 0)
	{
		$this->db->from($this->table);
		if (!empty($filters['keyword'])) {
			$this->db->like('cost_name', $filters['keyword']);
		}
		$this->db->order_by('cost_name', 'ASC');
		if ($limit !== NULL) $this->db->limit($limit, $offset);
		return $this->db->get()->result_array();
	}

	public function count_all($filters = array())
	{
		if (!empty($filters['keyword'])) {
			$this->db->like('cost_name', $filters['keyword']);
		}
		return $this->db->count_all_results($this->table);
	}

	public function get_all_active()
	{
		return $this->db->where('is_active', 1)->order_by('cost_name', 'ASC')->get($this->table)->result_array();
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	public function find_by_name($name)
	{
		return $this->db->where('cost_name', $name)->get($this->table)->row_array();
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

	public function count_usage($id)
	{
		return $this->db->where('cost_id', $id)->count_all_results('price_channel_costs');
	}

	public function delete($id)
	{
		return $this->db->where('id', $id)->delete($this->table);
	}
}
