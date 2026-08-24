<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Price_model
 * Mengelola harga aktif (product_prices) per produk x vendor x kanal, dan harga kompetitor.
 */
class Price_model extends CI_Model
{
	protected $table = 'product_prices';

	public function get_channels()
	{
		return $this->db->where('is_active', 1)->order_by('sort_order')->get('price_channels')->result_array();
	}

	public function get_competitors()
	{
		return $this->db->where('is_active', 1)->get('competitors')->result_array();
	}

	/**
	 * Ambil harga aktif semua kanal untuk 1 produk+vendor, dalam bentuk map [channel_code => price]
	 */
	public function get_current_prices($product_id, $vendor_id)
	{
		$rows = $this->db->select('product_prices.price, price_channels.channel_code')
			->from($this->table)
			->join('price_channels', 'price_channels.id = product_prices.channel_id')
			->where(array('product_id' => $product_id, 'vendor_id' => $vendor_id))
			->get()->result_array();

		$map = array();
		foreach ($rows as $r) $map[$r['channel_code']] = $r['price'];
		return $map;
	}

	public function get_current_competitor_prices($product_id)
	{
		$rows = $this->db->select('competitor_prices.price, competitors.competitor_code')
			->from('competitor_prices')
			->join('competitors', 'competitors.id = competitor_prices.competitor_id')
			->where('competitor_prices.product_id', $product_id)
			->order_by('competitor_prices.captured_date', 'DESC')
			->get()->result_array();

		$map = array();
		foreach ($rows as $r) {
			if (!isset($map[$r['competitor_code']])) $map[$r['competitor_code']] = $r['price'];
		}
		return $map;
	}

	/**
	 * Simpan/replace harga aktif untuk satu kanal (upsert berdasarkan unique key produk+vendor+kanal).
	 */
	public function upsert_price($product_id, $vendor_id, $channel_id, $price, $effective_date, $user_id)
	{
		$existing = $this->db->where(array(
			'product_id' => $product_id, 'vendor_id' => $vendor_id, 'channel_id' => $channel_id,
		))->get($this->table)->row_array();

		$data = array(
			'price' => $price,
			'effective_date' => $effective_date,
			'updated_by' => $user_id,
		);

		if ($existing) {
			$this->db->where('id', $existing['id'])->update($this->table, $data);
		} else {
			$data['product_id'] = $product_id;
			$data['vendor_id'] = $vendor_id;
			$data['channel_id'] = $channel_id;
			$this->db->insert($this->table, $data);
		}
	}

	public function upsert_competitor_price($product_id, $competitor_id, $price, $date, $user_id)
	{
		$this->db->replace('competitor_prices', array(
			'product_id' => $product_id,
			'competitor_id' => $competitor_id,
			'price' => $price,
			'captured_date' => $date,
			'updated_by' => $user_id,
		));
	}
}
