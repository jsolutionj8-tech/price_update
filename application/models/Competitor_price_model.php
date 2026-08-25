<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Competitor_price_model
 * CRUD untuk tabel `competitor_prices` (harga kompetitor per produk per tanggal pantau).
 */
class Competitor_price_model extends CI_Model
{
	protected $table = 'competitor_prices';

	protected function _apply_filters($filters)
	{
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('products.product_name', $filters['keyword'])
				->or_like('products.product_code', $filters['keyword'])
				->or_like('competitors.competitor_name', $filters['keyword'])
				->group_end();
		}
	}

	public function get_all($filters = array(), $limit = NULL, $offset = 0)
	{
		$this->db->select('competitor_prices.*, products.product_name, products.product_code, competitors.competitor_name, competitors.competitor_code')
			->from($this->table)
			->join('products', 'products.id = competitor_prices.product_id')
			->join('competitors', 'competitors.id = competitor_prices.competitor_id');
		$this->_apply_filters($filters);
		$this->db->order_by('competitor_prices.captured_date', 'DESC')->order_by('competitor_prices.id', 'DESC');
		if ($limit !== NULL) $this->db->limit($limit, $offset);
		return $this->db->get()->result_array();
	}

	public function count_all($filters = array())
	{
		$this->db->from($this->table)
			->join('products', 'products.id = competitor_prices.product_id')
			->join('competitors', 'competitors.id = competitor_prices.competitor_id');
		$this->_apply_filters($filters);
		return $this->db->count_all_results();
	}

	public function find($id)
	{
		return $this->db->select('competitor_prices.*, products.product_name, products.product_code, competitors.competitor_name')
			->from($this->table)
			->join('products', 'products.id = competitor_prices.product_id')
			->join('competitors', 'competitors.id = competitor_prices.competitor_id')
			->where('competitor_prices.id', $id)
			->get()->row_array();
	}

	public function find_existing($product_id, $competitor_id, $captured_date, $exclude_id = NULL)
	{
		$this->db->where(array(
			'product_id'     => $product_id,
			'competitor_id'  => $competitor_id,
			'captured_date'  => $captured_date,
		));
		if ($exclude_id !== NULL) $this->db->where('id !=', $exclude_id);
		return $this->db->get($this->table)->row_array();
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
		return $this->db->where('id', $id)->delete($this->table);
	}
}
