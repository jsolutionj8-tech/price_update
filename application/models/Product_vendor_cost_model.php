<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Product_vendor_cost_model
 * Mengelola Modal, Target HPP, SRP Suggest, Markup%, Margin% per kombinasi produk-vendor.
 */
class Product_vendor_cost_model extends CI_Model
{
	protected $table = 'product_vendor_costs';

	public function get_for_product($product_id)
	{
		return $this->db->select('product_vendor_costs.*, vendors.vendor_code, vendors.vendor_name')
			->from($this->table)
			->join('vendors', 'vendors.id = product_vendor_costs.vendor_id')
			->where('product_vendor_costs.product_id', $product_id)
			->order_by('vendors.vendor_code')
			->get()->result_array();
	}

	public function find($product_id, $vendor_id)
	{
		return $this->db->where(array('product_id' => $product_id, 'vendor_id' => $vendor_id))
			->get($this->table)->row_array();
	}

	public function upsert($product_id, $vendor_id, $data)
	{
		$existing = $this->find($product_id, $vendor_id);
		$data['product_id'] = $product_id;
		$data['vendor_id']  = $vendor_id;

		if ($existing) {
			$this->db->where('id', $existing['id'])->update($this->table, $data);
			return $existing['id'];
		}
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}
}
