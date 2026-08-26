<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Marketplace_model
 * Master marketplace / kanal penjualan. Menggunakan tabel `price_channels`
 * yang sama dipakai modul Update Harga & Grup Notifikasi.
 */
class Marketplace_model extends CI_Model
{
	protected $table = 'price_channels';

	public function get_all($filters = array(), $limit = NULL, $offset = 0)
	{
		$this->db->from($this->table);
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('channel_name', $filters['keyword'])
				->or_like('channel_code', $filters['keyword'])
				->group_end();
		}
		$this->db->order_by('sort_order', 'ASC')->order_by('channel_name', 'ASC');
		if ($limit !== NULL) $this->db->limit($limit, $offset);
		return $this->db->get()->result_array();
	}

	public function count_all($filters = array())
	{
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('channel_name', $filters['keyword'])
				->or_like('channel_code', $filters['keyword'])
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
		return $this->db->where('channel_code', $code)->get($this->table)->row_array();
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

	public function next_sort_order()
	{
		$row = $this->db->select_max('sort_order')->get($this->table)->row_array();
		return ((int) ($row['sort_order'] ?? 0)) + 10;
	}

	public function count_active()
	{
		return $this->db->where('is_active', 1)->count_all_results($this->table);
	}

	public function count_usage($id)
	{
		return $this->db->where('channel_id', $id)->count_all_results('product_prices');
	}

	public function delete($id)
	{
		return $this->db->where('id', $id)->delete($this->table);
	}

	public function get_cost_ids($channel_id)
	{
		$rows = $this->db->select('cost_id')->where('channel_id', $channel_id)->get('price_channel_costs')->result_array();
		return array_map(function ($r) { return (int) $r['cost_id']; }, $rows);
	}

	public function get_costs_for_channel($channel_id)
	{
		return $this->db->select('costs.*')
			->from('costs')
			->join('price_channel_costs', 'price_channel_costs.cost_id = costs.id')
			->where('price_channel_costs.channel_id', $channel_id)
			->order_by('costs.cost_name', 'ASC')
			->get()->result_array();
	}

	public function sync_costs($channel_id, array $cost_ids)
	{
		$this->db->where('channel_id', $channel_id)->delete('price_channel_costs');

		$cost_ids = array_unique(array_filter(array_map('intval', $cost_ids)));
		if (empty($cost_ids)) return;

		$rows = array();
		foreach ($cost_ids as $cost_id) {
			$rows[] = array('channel_id' => $channel_id, 'cost_id' => $cost_id);
		}
		$this->db->insert_batch('price_channel_costs', $rows);
	}
}
