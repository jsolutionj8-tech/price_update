<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper umum aplikasi: badge status, generate kode batch, dsb.
 */

if (!function_exists('status_badge')) {
	function status_badge($status)
	{
		$map = array(
			'sent'       => 'success',
			'processing' => 'info',
			'pending'    => 'warning',
			'queued'     => 'warning',
			'partial'    => 'warning',
			'failed'     => 'danger',
			'active'     => 'success',
			'inactive'   => 'secondary',
		);
		$color = isset($map[$status]) ? $map[$status] : 'secondary';
		return '<span class="badge bg-' . $color . '">' . ucfirst($status) . '</span>';
	}
}

if (!function_exists('generate_batch_code')) {
	function generate_batch_code()
	{
		return 'PCB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
	}
}

if (!function_exists('current_user')) {
	function current_user()
	{
		$ci =& get_instance();
		return array(
			'id'    => $ci->session->userdata('user_id'),
			'name'  => $ci->session->userdata('full_name'),
			'email' => $ci->session->userdata('email'),
			'role'  => $ci->session->userdata('role_code'),
		);
	}
}
