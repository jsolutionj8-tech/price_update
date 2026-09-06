<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shopify_client
 * Panggilan Admin API Shopify: cari variant by SKU (GraphQL, butuh scope read_products) &
 * update harga variant (REST, butuh scope write_products). Kredensial diambil dari
 * shopify_settings (lihat Shopify::connect()/save_manual_token()).
 */
class Shopify_client
{
	const API_VERSION = '2024-01';

	protected $settings;

	public function __construct()
	{
		$CI =& get_instance();
		$CI->load->model('shopify_settings_model');
		$this->settings = $CI->shopify_settings_model->get();
	}

	public function is_connected()
	{
		return !empty($this->settings['access_token']) && !empty($this->settings['shop_domain']);
	}

	/**
	 * Cari variant_id Shopify berdasarkan SKU (product_code kita) lewat GraphQL Admin API.
	 * Return null kalau tidak ditemukan, belum terhubung, atau scope read_products belum ada.
	 */
	public function find_variant_id_by_sku($sku)
	{
		if (!$this->is_connected()) return null;

		$query = 'query($q: String!) { productVariants(first: 1, query: $q) { edges { node { id } } } }';
		$result = $this->_graphql($query, array('q' => 'sku:' . $sku));

		$gid = $result['data']['productVariants']['edges'][0]['node']['id'] ?? null;
		if (empty($gid)) return null;

		// gid://shopify/ProductVariant/123456789 -> 123456789
		$parts = explode('/', $gid);
		return (int) end($parts);
	}

	/**
	 * Update harga satu variant.
	 * @return array ['success' => bool, 'error' => string|null]
	 */
	public function update_variant_price($variant_id, $price)
	{
		if (!$this->is_connected()) {
			return array('success' => false, 'error' => 'Belum terhubung ke Shopify.');
		}

		$response = $this->_request('PUT', '/admin/api/' . self::API_VERSION . '/variants/' . $variant_id . '.json', array(
			'variant' => array('id' => $variant_id, 'price' => (string) $price),
		));

		if ($response['http_code'] === 200) {
			return array('success' => true, 'error' => null);
		}

		return array('success' => false, 'error' => 'HTTP ' . $response['http_code'] . ': ' . $response['body']);
	}

	protected function _graphql($query, $variables = array())
	{
		$response = $this->_request('POST', '/admin/api/' . self::API_VERSION . '/graphql.json', array(
			'query'     => $query,
			'variables' => $variables,
		));
		$decoded = json_decode($response['body'], true);
		return $decoded ?: array();
	}

	protected function _request($method, $path, $body = null)
	{
		$ch = curl_init('https://' . $this->settings['shop_domain'] . $path);
		$options = array(
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 3,
			CURLOPT_TIMEOUT        => 20,
			CURLOPT_HTTPHEADER     => array(
				'Content-Type: application/json',
				'X-Shopify-Access-Token: ' . $this->settings['access_token'],
			),
		);
		if ($body !== null) {
			$options[CURLOPT_POSTFIELDS] = json_encode($body);
		}
		curl_setopt_array($ch, $options);
		$raw = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		return array(
			'http_code' => $error ? 0 : $http_code,
			'body'      => $error ? $error : $raw,
		);
	}
}
