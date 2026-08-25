<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Menu_access_model
 * Sumber kebenaran untuk hak akses menu per role (dikonfigurasi lewat menu
 * Administrasi → Hak Akses). Role ADMIN selalu dianggap punya akses penuh
 * di seluruh aplikasi (tidak tersimpan di tabel ini) supaya tidak bisa
 * mengunci diri sendiri lewat kesalahan konfigurasi.
 */
class Menu_access_model extends CI_Model
{
	public function get_all_menus()
	{
		return $this->db->order_by('sort_order', 'ASC')->get('menus')->result_array();
	}

	/**
	 * Cek apakah suatu role (kode, mis. 'EDITOR') punya akses ke satu menu.
	 * ADMIN selalu TRUE. Role/menu yang tidak punya baris eksplisit dianggap
	 * TIDAK punya akses (default aman: tertutup, bukan terbuka).
	 */
	public function can_access($role_code, $menu_key)
	{
		if ($role_code === 'ADMIN') return TRUE;

		$row = $this->db->select('role_menu_access.can_access')
			->from('role_menu_access')
			->join('roles', 'roles.id = role_menu_access.role_id')
			->join('menus', 'menus.id = role_menu_access.menu_id')
			->where('roles.role_code', $role_code)
			->where('menus.menu_key', $menu_key)
			->get()->row_array();

		return $row ? (bool) $row['can_access'] : FALSE;
	}

	/**
	 * Daftar menu_key yang bisa diakses satu role — dipakai sidebar untuk
	 * menyembunyikan link yang tidak diizinkan.
	 */
	public function get_accessible_keys($role_code)
	{
		if ($role_code === 'ADMIN') {
			return array_column($this->get_all_menus(), 'menu_key');
		}

		$rows = $this->db->select('menus.menu_key')
			->from('role_menu_access')
			->join('roles', 'roles.id = role_menu_access.role_id')
			->join('menus', 'menus.id = role_menu_access.menu_id')
			->where('roles.role_code', $role_code)
			->where('role_menu_access.can_access', 1)
			->get()->result_array();

		return array_column($rows, 'menu_key');
	}

	/**
	 * Matriks lengkap untuk halaman pengaturan: tiap menu + status akses
	 * per role yang bisa dikonfigurasi (EDITOR, VIEWER — ADMIN tidak
	 * ditampilkan karena selalu penuh & tidak bisa diubah).
	 */
	public function get_matrix()
	{
		$menus = $this->get_all_menus();
		$roles = $this->db->where_in('role_code', array('EDITOR', 'VIEWER'))->order_by('role_code')->get('roles')->result_array();

		$access = array();
		foreach ($this->db->get('role_menu_access')->result_array() as $row) {
			$access[$row['role_id']][$row['menu_id']] = (bool) $row['can_access'];
		}

		return array('menus' => $menus, 'roles' => $roles, 'access' => $access);
	}

	/**
	 * Simpan seluruh matriks sekaligus. $checked format: [role_id => [menu_id, ...]]
	 * berisi kombinasi yang DICENTANG saja (checkbox yang tidak dicentang tidak
	 * dikirim browser) — semua kombinasi lain untuk role yang dikirim di-set 0.
	 */
	public function save_matrix(array $role_ids, array $checked)
	{
		// Nilai 'id' dari hasil query selalu string (driver mysqli) — di-cast ke int
		// supaya perbandingan in_array(..., TRUE) di bawah benar-benar strict-match.
		$menu_ids = array_map('intval', array_column($this->get_all_menus(), 'id'));

		foreach ($role_ids as $role_id) {
			$role_id = (int) $role_id;
			$checked_menu_ids = array_map('intval', $checked[$role_id] ?? array());

			foreach ($menu_ids as $menu_id) {
				$can_access = in_array($menu_id, $checked_menu_ids, TRUE) ? 1 : 0;
				// INSERT ... ON DUPLICATE KEY UPDATE supaya tetap benar walau ada
				// menu baru yang belum punya baris untuk role ini.
				$this->db->query(
					'INSERT INTO role_menu_access (role_id, menu_id, can_access) VALUES (?, ?, ?)
					 ON DUPLICATE KEY UPDATE can_access = VALUES(can_access)',
					array($role_id, $menu_id, $can_access)
				);
			}
		}
	}
}
