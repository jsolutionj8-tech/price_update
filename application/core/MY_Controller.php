<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 * Controller dasar aplikasi. Seluruh controller (kecuali Auth) di-extend dari sini
 * agar otomatis terproteksi login, dan menyediakan helper render_view() dengan layout.
 */
class MY_Controller extends CI_Controller
{
	protected $allowed_roles = array('ADMIN', 'EDITOR', 'VIEWER'); // override di child controller bila perlu dibatasi

	public function __construct()
	{
		parent::__construct();
		$this->auth_lib->require_role($this->allowed_roles);

		$this->load->model('user_model');
		$this->data['logged_in_user'] = current_user();
	}

	/**
	 * Render view dengan layout header/sidebar/footer standar.
	 */
	protected function render_view($view, $data = array())
	{
		$data = array_merge(isset($this->data) ? $this->data : array(), $data);
		$this->load->view('layout/header', $data);
		$this->load->view('layout/sidebar', $data);
		$this->load->view($view, $data);
		$this->load->view('layout/footer', $data);
	}
}

/**
 * Admin_Controller
 * Untuk halaman yang hanya boleh diakses role ADMIN (mis. Users, Notification Groups).
 */
class Admin_Controller extends MY_Controller
{
	protected $allowed_roles = array('ADMIN');
}

/**
 * Editor_Controller
 * Untuk halaman yang boleh diakses ADMIN & EDITOR (mis. Products, Price Update).
 */
class Editor_Controller extends MY_Controller
{
	protected $allowed_roles = array('ADMIN', 'EDITOR');
}
