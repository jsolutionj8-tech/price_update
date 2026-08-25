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

	public function get_all($filters = array())
	{
		$this->db->from($this->table);
		if (!empty($filters['keyword'])) {
			$this->db->group_start()
				->like('channel_name', $filters['keyword'])
				->or_like('channel_code', $filters['keyword'])
				->group_end();
		}
		$this->db->order_by('sort_order', 'ASC')->order_by('channel_name', 'ASC');
		return $this->db->get()->result_array();
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
}
