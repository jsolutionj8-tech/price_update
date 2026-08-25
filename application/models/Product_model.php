<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
	protected $table = 'products';

	public function get_all($filters = array(), $limit = NULL, $offset = 0)
	{
		$this->db->select('products.*, brands.brand_name, product_categories.category_name')
			->from($this->table)
			->join('brands', 'brands.id = products.brand_id', 'left')
			->join('product_categories', 'product_categories.id = products.category_id', 'left');

		if (!empty($filters['brand_id'])) $this->db->where('products.brand_id', $filters['brand_id']);
		if (!empty($filters['category_id'])) $this->db->where('products.category_id', $filters['category_id']);
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('products.product_name', $filters['keyword'])
				->or_like('products.product_code', $filters['keyword'])
				->group_end();
		}
		$this->db->where('products.status', 'active')->order_by('products.product_name', 'ASC');

		if ($limit !== NULL) $this->db->limit($limit, $offset);

		return $this->db->get()->result_array();
	}

	public function count_all($filters = array())
	{
		if (!empty($filters['brand_id'])) $this->db->where('brand_id', $filters['brand_id']);
		if (!empty($filters['category_id'])) $this->db->where('category_id', $filters['category_id']);
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('product_name', $filters['keyword'])
				->or_like('product_code', $filters['keyword'])
				->group_end();
		}
		$this->db->where('status', 'active');

		return $this->db->count_all_results($this->table);
	}

	public function find($id)
	{
		return $this->db->select('products.*, brands.brand_name, product_categories.category_name')
			->from($this->table)
			->join('brands', 'brands.id = products.brand_id', 'left')
			->join('product_categories', 'product_categories.id = products.category_id', 'left')
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

	public function get_all_categories()
	{
		return $this->db->where('is_active', 1)->order_by('category_name')->get('product_categories')->result_array();
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
