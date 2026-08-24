<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
	protected $table = 'products';

	public function get_all($filters = array())
	{
		$this->db->select('products.*, brands.brand_name')
			->from($this->table)
			->join('brands', 'brands.id = products.brand_id', 'left');

		if (!empty($filters['brand_id'])) $this->db->where('products.brand_id', $filters['brand_id']);
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('products.product_name', $filters['keyword'])
				->or_like('products.product_code', $filters['keyword'])
				->group_end();
		}
		$this->db->where('products.status', 'active')->order_by('products.product_name', 'ASC');

		return $this->db->get()->result_array();
	}

	public function find($id)
	{
		return $this->db->select('products.*, brands.brand_name')
			->from($this->table)
			->join('brands', 'brands.id = products.brand_id', 'left')
			->where('products.id', $id)
			->get()->row_array();
	}

	public function find_by_code($code)
	{
		return $this->db->where('product_code', $code)->get($this->table)->row_array();
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

	public function delete($id)
	{
		return $this->db->where('id', $id)->update($this->table, array('status' => 'inactive'));
	}

	public function get_all_brands()
	{
		return $this->db->where('is_active', 1)->order_by('brand_name')->get('brands')->result_array();
	}

	public function get_all_vendors()
	{
		return $this->db->where('is_active', 1)->order_by('vendor_code')->get('vendors')->result_array();
	}

	public function count_active()
	{
		return $this->db->where('status', 'active')->count_all_results($this->table);
	}
}
