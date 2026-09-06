<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shopify
 * Halaman "Shopify": simpan kredensial OAuth (shop domain, client_id, client_secret) &
 * jalankan alur OAuth Authorization Code untuk mendapatkan access_token. Sengaja
 * Admin_Controller (bukan lewat Menu_access_model) krn halaman ini menyimpan client_secret
 * & access_token — sama alasannya dgn Settings (SMTP) & Access_control.
 *
 * connect()/callback() dijalankan di dalam sesi admin yang sudah login (redirect ke Shopify
 * lalu kembali ke browser admin yang sama), jadi tetap aman di-gate lewat Admin_Controller
 * meski endpoint-nya diakses lewat redirect, bukan klik menu biasa.
 */
class Shopify extends Admin_Controller
{
	const OAUTH_STATE_KEY = 'shopify_oauth_state';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('shopify_settings_model');
	}

	public function index()
	{
		$data = array(
			'title'    => 'Shopify',
			'settings' => $this->shopify_settings_model->get(),
		);
		$this->render_view('shopify/index', $data);
	}

	public function save_credentials()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('shop_domain', 'Shop Domain', 'required');
		$this->form_validation->set_rules('client_id', 'Client ID', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('shopify');
		}

		$current = $this->shopify_settings_model->get();

		$data = array(
			'shop_domain' => $this->_normalize_domain($this->input->post('shop_domain', TRUE)),
			'client_id'   => $this->input->post('client_id', TRUE),
			'updated_by'  => $this->auth_lib->user_id(),
		);

		// Client Secret sengaja tidak ditampilkan ulang di form (lihat views/shopify/index.php)
		// — field dikosongkan berarti "jangan ubah", hanya ditimpa kalau diisi ulang.
		$new_secret = $this->input->post('client_secret');
		if ($new_secret !== '' && $new_secret !== NULL) {
			$data['client_secret'] = $new_secret;
		} elseif (empty($current['id'])) {
			$this->session->set_flashdata('error', 'Client Secret wajib diisi.');
			redirect('shopify');
		} else {
			$data['client_secret'] = $current['client_secret'];
		}

		// Ganti shop/client_id/secret berarti koneksi lama (kalau ada) sudah tidak relevan lagi.
		$data['access_token'] = NULL;
		$data['connected_at'] = NULL;

		$this->shopify_settings_model->save($data);
		$this->session->set_flashdata('success', 'Kredensial Shopify berhasil disimpan. Klik "Hubungkan ke Shopify" untuk melanjutkan.');
		redirect('shopify');
	}

	/**
	 * Mulai alur OAuth: redirect browser admin ke halaman izin (consent) Shopify.
	 * `state` acak disimpan di session lalu diverifikasi lagi di callback() utk mencegah
	 * CSRF pada proses OAuth (permintaan authorize/callback palsu dari pihak lain).
	 */
	public function connect()
	{
		$settings = $this->shopify_settings_model->get();
		if (empty($settings['shop_domain']) || empty($settings['client_id']) || empty($settings['client_secret'])) {
			$this->session->set_flashdata('error', 'Isi dulu Shop Domain, Client ID & Client Secret sebelum menghubungkan.');
			redirect('shopify');
		}

		$state = bin2hex(random_bytes(16));
		$this->session->set_userdata(self::OAUTH_STATE_KEY, $state);

		$redirect_uri = base_url('shopify/callback');
		$authorize_url = 'https://' . $settings['shop_domain'] . '/admin/oauth/authorize'
			. '?client_id=' . rawurlencode($settings['client_id'])
			. '&scope=' . rawurlencode($settings['scope'])
			. '&redirect_uri=' . rawurlencode($redirect_uri)
			. '&state=' . rawurlencode($state);

		// Log persis redirect_uri yang dikirim — dipakai utk diagnosa error Shopify "redirect_uri
		// and application url must have matching hosts" (host redirect_uri ini HARUS sama dgn
		// host "App URL" yg terdaftar di Partner/Dev Dashboard, bukan cuma di Allowed Redirection URL).
		log_message('error', 'Shopify OAuth connect() — base_url(): ' . base_url() . ' | redirect_uri dikirim: ' . $redirect_uri . ' | authorize_url: ' . $authorize_url);

		redirect($authorize_url);
	}

	/**
	 * Shopify redirect ke sini setelah admin toko klik "Install/Allow". Verifikasi state & hmac
	 * dulu (wajib — tanpa ini siapa pun bisa memalsukan callback ini), baru tukar `code` dgn
	 * access_token lewat server-to-server call (client_secret tidak pernah dikirim ke browser).
	 */
	public function callback()
	{
		$state = $this->input->get('state');
		$session_state = $this->session->userdata(self::OAUTH_STATE_KEY);
		$this->session->unset_userdata(self::OAUTH_STATE_KEY); // one-time use, langsung dibuang

		if (empty($state) || empty($session_state) || !hash_equals($session_state, $state)) {
			// Log alasan spesifik (bukan cuma pesan generik ke user) supaya gampang didiagnosis
			// lewat application/logs/ kalau ini kejadian lagi — session cookie hilang antar
			// request (beda host/domain, cookie diblokir, dsb) vs state memang sudah dipakai/kadaluarsa.
			log_message('error', 'Shopify OAuth state mismatch — state dari Shopify: ' . var_export($state, TRUE)
				. ', state di session: ' . var_export($session_state, TRUE)
				. ', session_id: ' . session_id());
			$this->session->set_flashdata('error', 'Koneksi Shopify dibatalkan: state tidak valid (permintaan kadaluarsa atau tidak sah).');
			redirect('shopify');
		}

		$settings = $this->shopify_settings_model->get();
		$shop = $this->input->get('shop');
		if (empty($settings) || empty($shop) || !hash_equals($settings['shop_domain'], $shop)) {
			$this->session->set_flashdata('error', 'Koneksi Shopify dibatalkan: shop domain tidak cocok dengan yang tersimpan.');
			redirect('shopify');
		}

		if (!$this->_verify_hmac($settings['client_secret'])) {
			$this->session->set_flashdata('error', 'Koneksi Shopify dibatalkan: verifikasi keamanan (hmac) gagal.');
			redirect('shopify');
		}

		$code = $this->input->get('code');
		if (empty($code)) {
			$this->session->set_flashdata('error', 'Koneksi Shopify gagal: kode otorisasi tidak diterima.');
			redirect('shopify');
		}

		$ch = curl_init('https://' . $shop . '/admin/oauth/access_token');
		curl_setopt_array($ch, array(
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
			CURLOPT_POSTFIELDS     => json_encode(array(
				'client_id'     => $settings['client_id'],
				'client_secret' => $settings['client_secret'],
				'code'          => $code,
			)),
		));
		$response = curl_exec($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);

		$result = $response ? json_decode($response, true) : null;

		if ($curl_error || empty($result['access_token'])) {
			log_message('error', 'Shopify OAuth token exchange gagal: ' . ($curl_error ?: $response));
			$this->session->set_flashdata('error', 'Koneksi Shopify gagal saat menukar kode otorisasi. Coba lagi.');
			redirect('shopify');
		}

		$this->shopify_settings_model->save_token($result['scope'] ?? $settings['scope'], $result['access_token'], $this->auth_lib->user_id());
		$this->session->set_flashdata('success', 'Berhasil terhubung ke Shopify (' . $shop . ').');
		redirect('shopify');
	}

	public function disconnect()
	{
		$this->shopify_settings_model->clear_token($this->auth_lib->user_id());
		$this->session->set_flashdata('success', 'Koneksi ke Shopify sudah diputuskan.');
		redirect('shopify');
	}

	/**
	 * Pastikan access_token yang tersimpan benar-benar valid & bisa dipakai — bukan cuma
	 * "tersimpan di DB" tapi belum tentu jalan (mis. salah salin, scope kurang, dsb). Panggil
	 * endpoint Admin API paling ringan (shop.json) pakai access_token, laporkan hasilnya apa adanya.
	 */
	public function test_connection()
	{
		$settings = $this->shopify_settings_model->get();
		if (empty($settings['access_token'])) {
			$this->session->set_flashdata('error', 'Belum ada Access Token tersimpan.');
			redirect('shopify');
		}

		$ch = curl_init('https://' . $settings['shop_domain'] . '/admin/api/2024-01/shop.json');
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true, // shop_domain custom (mis. domain sendiri) biasanya redirect ke *.myshopify.com
			CURLOPT_MAXREDIRS      => 3,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_HTTPHEADER     => array('X-Shopify-Access-Token: ' . $settings['access_token']),
		));
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);
		curl_close($ch);

		$result = $response ? json_decode($response, true) : null;

		if ($curl_error) {
			$this->session->set_flashdata('error', 'Test koneksi gagal: tidak bisa menghubungi ' . $settings['shop_domain'] . ' (' . $curl_error . ').');
		} elseif ($http_code === 200 && !empty($result['shop']['name'])) {
			// shop.json cuma membuktikan token valid, BUKAN scope apa saja yang aktif — cek scope
			// SEBENARNYA lewat access_scopes.json (bisa beda dari kolom `scope` tersimpan kalau
			// scope app diubah di Dev Dashboard SETELAH token ini terbit; upstream tidak retroaktif
			// menaikkan scope token lama, hanya reconnect/token baru yang membawa scope terbaru).
			$active_scope = $this->_fetch_active_scopes($settings);
			if ($active_scope !== null && $active_scope !== $settings['scope']) {
				$this->shopify_settings_model->save(array('scope' => $active_scope));
			}
			$scope_text = $active_scope ?? $settings['scope'] . ' (tidak bisa verifikasi scope aktif)';
			$this->session->set_flashdata('success', 'Token VALID & berfungsi — berhasil mengambil data toko "' . $result['shop']['name'] . '" dari Shopify API. Scope aktif: ' . $scope_text . '.');
		} elseif ($http_code === 401) {
			$this->session->set_flashdata('error', 'Token TIDAK valid (HTTP 401 Unauthorized) — Access Token salah/sudah dicabut. Buat ulang Custom App/token-nya.');
		} else {
			log_message('error', 'Shopify test_connection gagal — HTTP ' . $http_code . ': ' . $response);
			$this->session->set_flashdata('error', 'Test koneksi gagal (HTTP ' . $http_code . '). Detail sudah dicatat di log server.');
		}

		redirect('shopify');
	}

	/**
	 * Ambil daftar scope yang BENAR-BENAR aktif untuk access_token tersimpan, langsung dari
	 * Shopify (bukan dari kolom `scope` kita, yang cuma snapshot hasil callback() terakhir).
	 * Return null kalau gagal dipanggil (bukan berarti scope kosong).
	 */
	private function _fetch_active_scopes($settings)
	{
		$ch = curl_init('https://' . $settings['shop_domain'] . '/admin/oauth/access_scopes.json');
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 3,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_HTTPHEADER     => array('X-Shopify-Access-Token: ' . $settings['access_token']),
		));
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($http_code !== 200) return null;

		$result = json_decode($response, true);
		if (empty($result['access_scopes'])) return null;

		return implode(',', array_column($result['access_scopes'], 'handle'));
	}

	private function _normalize_domain($domain)
	{
		$domain = trim($domain);
		$domain = preg_replace('#^https?://#i', '', $domain);
		return rtrim($domain, '/');
	}

	/**
	 * Verifikasi hmac query string sesuai dokumentasi OAuth Shopify: seluruh parameter GET
	 * (kecuali hmac & signature) diurutkan alfabetis lalu di-HMAC-SHA256 pakai Client Secret.
	 */
	private function _verify_hmac($client_secret)
	{
		$params = $this->input->get();
		$hmac = isset($params['hmac']) ? $params['hmac'] : '';
		unset($params['hmac'], $params['signature']);
		if (empty($hmac)) return FALSE;

		ksort($params);
		$pairs = array();
		foreach ($params as $key => $value) {
			$pairs[] = $key . '=' . $value;
		}
		$computed = hash_hmac('sha256', implode('&', $pairs), $client_secret);

		return hash_equals($computed, $hmac);
	}
}
