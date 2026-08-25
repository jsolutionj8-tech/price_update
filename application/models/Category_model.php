<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model
{
	protected $table = 'product_categories';

	public function get_all($filters = array())
	{
		$this->db->from($this->table);
		if (!empty($filters['keyword'])) {
			$this->db->like('category_name', $filters['keyword']);
		}
		$this->db->order_by('category_name', 'ASC');
		return $this->db->get()->result_array();
	}

	public function get_all_active()
	{
		return $this->db->where('is_active', 1)->order_by('category_name', 'ASC')->get($this->table)->result_array();
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	public function find_by_name($name)
	{
		return $this->db->where('category_name', $name)->get($this->table)->row_array();
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

	public function count_products($id)
	{
		return $this->db->where('category_id', $id)->count_all_results('products');
	}
}
