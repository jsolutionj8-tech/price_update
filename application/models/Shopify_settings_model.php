<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shopify_settings_model
 * Menyimpan 1 baris kredensial & token OAuth Shopify (diisi lewat menu Shopify, ADMIN-only).
 * client_secret & access_token adalah rahasia — model ini sengaja tidak dipakai untuk
 * menampilkan ulang nilainya ke form (lihat Shopify::index()).
 */
class Shopify_settings_model extends CI_Model
{
	protected $table = 'shopify_settings';

	/**
	 * Ambil baris pengaturan tersimpan, atau NULL kalau belum pernah diisi sama sekali.
	 */
	public function get()
	{
		return $this->db->limit(1)->get($this->table)->row_array();
	}

	/**
	 * Upsert baris tunggal — tabel ini memang cuma dipakai untuk 1 baris kredensial aktif.
	 */
	public function save($data)
	{
		$existing = $this->db->select('id')->limit(1)->get($this->table)->row_array();
		if ($existing) {
			$this->db->where('id', $existing['id'])->update($this->table, $data);
			return $existing['id'];
		}
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
	 * Simpan access_token hasil tukar `code` OAuth, dipanggil dari Shopify::callback().
	 */
	public function save_token($scope, $access_token, $user_id)
	{
		$this->db->where('id', $this->get()['id'])->update($this->table, array(
			'access_token' => $access_token,
			'scope'        => $scope,
			'connected_at' => date('Y-m-d H:i:s'),
			'updated_by'   => $user_id,
		));
	}

	/**
	 * Putuskan koneksi: hapus access_token tersimpan (kredensial client_id/secret tetap ada).
	 */
	public function clear_token($user_id)
	{
		$current = $this->get();
		if (!$current) return;
		$this->db->where('id', $current['id'])->update($this->table, array(
			'access_token' => NULL,
			'connected_at' => NULL,
			'updated_by'   => $user_id,
		));
	}
}
