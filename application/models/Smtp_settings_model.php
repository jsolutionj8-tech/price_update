<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Smtp_settings_model
 * Menyimpan 1 baris konfigurasi SMTP pengirim (diisi lewat menu Settings, ADMIN-only) —
 * menggantikan kredensial hardcode di application/config/email.php. Dipakai oleh Notifier
 * agar pengiriman email notifikasi memakai kredensial dari database, bukan file.
 */
class Smtp_settings_model extends CI_Model
{
	protected $table = 'smtp_settings';

	/**
	 * Ambil baris pengaturan tersimpan. Kalau belum pernah disimpan lewat menu Settings
	 * (tabel masih kosong), fallback ke nilai default di application/config/email.php
	 * supaya pengiriman email tetap jalan tanpa perlu setup dulu.
	 */
	public function get()
	{
		$row = $this->db->limit(1)->get($this->table)->row_array();
		if ($row) return $row;

		$CI =& get_instance();
		$CI->config->load('email');
		return array(
			'id'          => null,
			'smtp_host'   => $CI->config->item('smtp_host'),
			'smtp_port'   => $CI->config->item('smtp_port'),
			'smtp_crypto' => $CI->config->item('smtp_crypto'),
			'smtp_user'   => $CI->config->item('smtp_user'),
			'smtp_pass'   => $CI->config->item('smtp_pass'),
			'from_email'  => $CI->config->item('from_email'),
			'from_name'   => $CI->config->item('from_name'),
		);
	}

	/**
	 * Upsert baris tunggal — tabel ini memang cuma dipakai untuk 1 baris konfigurasi aktif.
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
}
