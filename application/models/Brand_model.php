<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Brand_model
 * Master data Brand (dipakai untuk mengelompokkan produk pada modul Produk).
 * Mengikuti pola sederhana yang sama seperti Category_model: hanya nama + status aktif.
 */
class Brand_model extends CI_Model
{
	protected $table = 'brands';

	public function get_all($filters = array(), $limit = NULL, $offset = 0)
	{
		$this->db->from($this->table);
		if (!empty($filters['keyword'])) {
			$this->db->like('brand_name', $filters['keyword']);
		}
		$this->db->order_by('brand_name', 'ASC');
		if ($limit !== NULL) $this->db->limit($limit, $offset);
		return $this->db->get()->result_array();
	}

	public function count_all($filters = array())
	{
		if (!empty($filters['keyword'])) {
			$this->db->like('brand_name', $filters['keyword']);
		}
		return $this->db->count_all_results($this->table);
	}

	public function get_all_active()
	{
		return $this->db->where('is_active', 1)->order_by('brand_name', 'ASC')->get($this->table)->result_array();
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	public function find_by_name($name)
	{
		return $this->db->where('brand_name', $name)->get($this->table)->row_array();
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
		return $this->db->where('brand_id', $id)->count_all_results('products');
	}

	public function delete($id)
	{
		return $this->db->where('id', $id)->delete($this->table);
	}
}
