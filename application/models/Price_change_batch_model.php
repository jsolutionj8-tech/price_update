<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Price_change_batch_model
 * Inti audit trail: setiap penyimpanan perubahan harga menghasilkan 1 baris di sini,
 * berisi snapshot JSON sebelum & sesudah, serta menjadi pemicu (trigger) Notifier.
 */
class Price_change_batch_model extends CI_Model
{
	protected $table = 'price_change_batches';

	public function create($data)
	{
		$data['batch_code'] = generate_batch_code();
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function update_status($id, $status)
	{
		return $this->db->where('id', $id)->update($this->table, array('notify_status' => $status));
	}

	/**
	 * Jumlah & daftar batch berstatus 'pending' secara global (lintas user/sesi) —
	 * dipakai sebagai sumber kebenaran untuk banner "Kirim Notifikasi Sekarang",
	 * bukan disimpan di session supaya tidak hilang saat logout/ganti user.
	 */
	public function count_pending()
	{
		return $this->db->where('notify_status', 'pending')->count_all_results($this->table);
	}

	public function get_pending_ids()
	{
		return array_column(
			$this->db->select('id')->where('notify_status', 'pending')->get($this->table)->result_array(),
			'id'
		);
	}

	public function get_with_detail($id)
	{
		return $this->db->select('price_change_batches.*, products.product_name, products.product_code, users.full_name as changed_by_name')
			->from($this->table)
			->join('products', 'products.id = price_change_batches.product_id')
			->join('users', 'users.id = price_change_batches.changed_by')
			->where('price_change_batches.id', $id)
			->get()->row_array();
	}

	public function get_paginated($limit, $offset, $filters = array())
	{
		$this->_apply_filters($filters);
		return $this->db->select('price_change_batches.*, products.product_name, products.product_code, vendors.vendor_code, users.full_name as changed_by_name')
			->from($this->table)
			->join('products', 'products.id = price_change_batches.product_id')
			->join('vendors', 'vendors.id = price_change_batches.vendor_id')
			->join('users', 'users.id = price_change_batches.changed_by')
			->order_by('price_change_batches.created_at', 'DESC')
			->limit($limit, $offset)
			->get()->result_array();
	}

	public function count_all_filtered($filters = array())
	{
		$this->_apply_filters($filters);
		return $this->db->from($this->table)->count_all_results();
	}

	protected function _apply_filters($filters)
	{
		if (!empty($filters['product_id'])) $this->db->where('price_change_batches.product_id', $filters['product_id']);
		if (!empty($filters['status'])) $this->db->where('price_change_batches.notify_status', $filters['status']);
		if (!empty($filters['date_from'])) $this->db->where('price_change_batches.effective_date >=', $filters['date_from']);
		if (!empty($filters['date_to'])) $this->db->where('price_change_batches.effective_date <=', $filters['date_to']);
	}

	public function recent($limit = 10)
	{
		return $this->db->select('price_change_batches.*, products.product_name')
			->from($this->table)
			->join('products', 'products.id = price_change_batches.product_id')
			->order_by('price_change_batches.created_at', 'DESC')
			->limit($limit)
			->get()->result_array();
	}

	public function count_this_week()
	{
		return $this->db->from($this->table)
			->where('created_at >=', date('Y-m-d 00:00:00', strtotime('monday this week')))
			->count_all_results();
	}

	public function monthly_trend($months = 6)
	{
		$from = date('Y-m-01', strtotime("-" . ($months - 1) . " months"));
		return $this->db->select("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
			->from($this->table)
			->where('created_at >=', $from)
			->group_by("DATE_FORMAT(created_at, '%Y-%m')")
			->order_by('ym', 'ASC')
			->get()->result_array();
	}
}
