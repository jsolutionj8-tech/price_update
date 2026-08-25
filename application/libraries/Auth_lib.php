<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth_lib
 * Menangani pengecekan status login & role-based access control (RBAC).
 * Diautoload agar tersedia di seluruh controller (lihat config/autoload.php).
 */
class Auth_lib
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('session');
	}

	public function is_logged_in()
	{
		return (bool) $this->CI->session->userdata('user_id');
	}

	public function user_id()
	{
		return $this->CI->session->userdata('user_id');
	}

	public function role()
	{
		return $this->CI->session->userdata('role_code');
	}

	/**
	 * Wajib login, redirect ke halaman login jika belum.
	 */
	public function require_login()
	{
		if (!$this->is_logged_in()) {
			redirect('login');
		}
	}

	/**
	 * Wajib memiliki salah satu role dari daftar yang diizinkan.
	 * Contoh: $this->auth_lib->require_role(['ADMIN', 'EDITOR']);
	 */
	public function require_role(array $allowed_roles)
	{
		$this->require_login();
		if (!in_array($this->role(), $allowed_roles, TRUE)) {
			show_error('Anda tidak memiliki akses ke halaman ini (403 Forbidden).', 403, 'Akses Ditolak');
		}
	}

	/**
	 * Wajib punya akses ke menu tertentu, sesuai konfigurasi di menu
	 * Administrasi -> Hak Akses (tabel role_menu_access). Role ADMIN selalu
	 * lolos (lihat Menu_access_model::can_access()).
	 */
	public function require_menu_access($menu_key)
	{
		$this->require_login();
		$this->CI->load->model('menu_access_model');
		if (!$this->CI->menu_access_model->can_access($this->role(), $menu_key)) {
			show_error('Anda tidak memiliki akses ke menu ini. Hubungi Administrator jika ini seharusnya bisa diakses.', 403, 'Akses Ditolak');
		}
	}

	public function login_session($user)
	{
		$this->CI->session->set_userdata(array(
			'user_id'   => $user['id'],
			'full_name' => $user['full_name'],
			'email'     => $user['email'],
			'role_code' => $user['role_code'],
		));
	}

	public function logout()
	{
		$this->CI->session->sess_destroy();
	}
}
