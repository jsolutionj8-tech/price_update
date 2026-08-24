<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper format angka & tanggal untuk tampilan (locale Indonesia).
 */

if (!function_exists('rupiah')) {
	function rupiah($value, $with_prefix = TRUE)
	{
		$formatted = number_format((float) $value, 0, ',', '.');
		return $with_prefix ? 'Rp ' . $formatted : $formatted;
	}
}

if (!function_exists('percent_fmt')) {
	function percent_fmt($value)
	{
		return number_format((float) $value, 2) . '%';
	}
}

if (!function_exists('tgl_indo')) {
	function tgl_indo($date, $with_time = FALSE)
	{
		if (empty($date) || $date === '0000-00-00') return '-';
		$bulan = array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
		$ts = strtotime($date);
		$out = date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
		if ($with_time) $out .= ' ' . date('H:i', $ts);
		return $out;
	}
}
