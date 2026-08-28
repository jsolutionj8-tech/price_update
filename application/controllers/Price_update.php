<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Price_update
 * Controller inti aplikasi: form update Modal/HPP/harga per kanal, kalkulasi otomatis,
 * simpan sebagai batch riwayat, dan memicu pengiriman notifikasi email.
 */
class Price_update extends MY_Controller
{
	const PER_PAGE = 25;
	protected $menu_key = 'price-update';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
		$this->load->model('product_vendor_cost_model');
		$this->load->model('price_model');
		$this->load->model('price_change_batch_model');
		$this->load->model('marketplace_model');
		$this->load->library('price_calculator');
	}

	public function index()
	{
		$filters = array('keyword' => $this->input->get('keyword'));

		$total = $this->product_model->count_all($filters);
		$total_pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = max(1, min($total_pages, (int) $this->input->get('page')));
		$offset = ($page - 1) * self::PER_PAGE;

		$data = array(
			'title'    => 'Update Harga',
			'products' => $this->product_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'  => $filters,
			'pagination' => array(
				'page'        => $page,
				'total_pages' => $total_pages,
				'total'       => $total,
				'per_page'    => self::PER_PAGE,
			),
		);
		$this->render_view('price_update/index', $data);
	}

	/**
	 * Form update harga untuk 1 produk (semua vendor A/B/C ditampilkan sebagai tab).
	 */
	public function form($product_id)
	{
		$product = $this->product_model->find($product_id);
		if (!$product) show_404();

		$vendor_costs = $this->product_vendor_cost_model->get_for_product($product_id);
		$existing_vendor_ids = array_column($vendor_costs, 'vendor_id');
		$all_vendors = $this->product_model->get_all_vendors();
		$channels = $this->_channels_with_biaya();

		$data = array(
			'title'       => 'Update Harga - ' . $product['product_name'],
			'product'     => $product,
			'vendor_costs'=> $vendor_costs,
			'available_vendors' => array_values(array_filter($all_vendors, function ($v) use ($existing_vendor_ids) {
				return !in_array($v['id'], $existing_vendor_ids);
			})),
			'channels'    => $channels,
			'competitors' => $this->price_model->get_competitors(),
			'competitor_prices' => $this->price_model->get_current_competitor_prices($product_id),
		);

		// Untuk tiap vendor, lampirkan harga saat ini per kanal
		foreach ($data['vendor_costs'] as &$vc) {
			$vc['current_prices'] = $this->price_model->get_current_prices($product_id, $vc['vendor_id']);
		}

		$this->render_view('price_update/form', $data);
	}

	/**
	 * Tambahkan vendor baru (cost awal 0) untuk produk ini, agar muncul sebagai tab
	 * di form Update Harga dan bisa langsung diisi Modal/HPP-nya.
	 * Dipanggil lewat AJAX dari form.php (tab baru disisipkan tanpa reload halaman);
	 * redirect biasa tetap disediakan sebagai fallback jika JS mati.
	 */
	public function add_vendor($product_id)
	{
		$product = $this->product_model->find($product_id);
		if (!$product) show_404();

		$vendor_id = (int) $this->input->post('vendor_id');
		$is_ajax = $this->input->is_ajax_request();

		if (!$vendor_id) {
			if ($is_ajax) {
				$this->output->set_status_header(422)->set_content_type('application/json')
					->set_output(json_encode(array('success' => FALSE, 'message' => 'Pilih vendor terlebih dahulu.')));
				return;
			}
			redirect('price-update/form/' . $product_id);
		}

		$this->product_vendor_cost_model->upsert($product_id, $vendor_id, array(
			'modal' => 0,
			'target_hpp_pct' => 0,
			'srp_suggest' => 0,
			'srp_markup_pct' => 0,
			'srp_margin_pct' => 0,
			'updated_by' => $this->auth_lib->user_id(),
		));

		if (!$is_ajax) {
			redirect('price-update/form/' . $product_id);
		}

		$vc = NULL;
		foreach ($this->product_vendor_cost_model->get_for_product($product_id) as $row) {
			if ((int) $row['vendor_id'] === $vendor_id) { $vc = $row; break; }
		}
		if (!$vc) show_404();
		$vc['current_prices'] = $this->price_model->get_current_prices($product_id, $vendor_id);

		$tab_html = $this->load->view('price_update/_vendor_tab', array(
			'product' => $product,
			'vc' => $vc,
			'channels' => $this->_channels_with_biaya(),
			'competitors' => $this->price_model->get_competitors(),
			'competitor_prices' => $this->price_model->get_current_competitor_prices($product_id),
			'active' => FALSE,
		), TRUE);

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success'     => TRUE,
			'vendor_id'   => $vendor_id,
			'vendor_name' => $vc['vendor_name'],
			'tab_html'    => $tab_html,
		)));
	}

	/**
	 * Total Biaya (Master Biaya) yang dikaitkan ke tiap sales channel — dipakai untuk
	 * rumus SRP Suggest per kanal: (Modal + Total Biaya kanal) / (1 - Margin%).
	 * Biaya bertipe nominal dijumlah langsung (Rp); biaya bertipe persen dihitung sebagai
	 * % dari Modal vendor yang aktif (dihitung di browser, lihat form.php), sehingga di sini
	 * cukup dijumlah terpisah: total_biaya_nominal & total_biaya_percent. Kanal tanpa Biaya
	 * (keduanya 0) otomatis sama dengan SRP Suggest global.
	 */
	protected function _channels_with_biaya()
	{
		$channels = $this->price_model->get_channels();
		foreach ($channels as &$ch) {
			$ch['total_biaya_nominal'] = 0;
			$ch['total_biaya_percent'] = 0;
			foreach ($this->marketplace_model->get_costs_for_channel($ch['id']) as $cost) {
				if ($cost['cost_type'] === 'percent') {
					$ch['total_biaya_percent'] += (float) $cost['amount'];
				} else {
					$ch['total_biaya_nominal'] += (float) $cost['amount'];
				}
			}
		}
		unset($ch);
		return $channels;
	}

	/**
	 * Endpoint AJAX: hitung SRP Suggest / Markup% / Margin% secara real-time.
	 * Markup% & Margin% dihitung dari harga jual aktual (kanal Offline sebagai acuan utama,
	 * sesuai konvensi pada spreadsheet acuan) jika sudah diisi; jika belum, fallback ke SRP Suggest.
	 */
	public function calculate()
	{
		$modal = (float) $this->input->post('modal');
		$margin = (float) $this->input->post('margin_pct');
		$actual_price = $this->input->post('actual_price');
		$result = $this->price_calculator->calculate($modal, $margin, $actual_price);

		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}

	/**
	 * Simpan perubahan harga: update cost, update harga aktif per kanal,
	 * catat batch riwayat (snapshot lama vs baru), lalu panggil Notifier.
	 */
	public function save()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('product_id', 'Produk', 'required|integer');
		$this->form_validation->set_rules('vendor_id', 'Vendor', 'required|integer');
		$this->form_validation->set_rules('modal', 'Modal', 'required|numeric');
		$this->form_validation->set_rules('margin_pct', 'Margin', 'required|numeric');
		$this->form_validation->set_rules('effective_date', 'Tanggal Efektif', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('price-update/form/' . $this->input->post('product_id'));
		}

		$product_id = (int) $this->input->post('product_id');
		$vendor_id  = (int) $this->input->post('vendor_id');
		$modal      = (float) $this->input->post('modal');
		$margin     = (float) $this->input->post('margin_pct');
		$effective_date = $this->input->post('effective_date');
		$notes = $this->input->post('notes', TRUE);
		$user_id = $this->auth_lib->user_id();
		// Kanal Offline dipakai sebagai acuan utama perhitungan Markup%/Margin% (sesuai konvensi
		// spreadsheet acuan); jika Offline tidak diisi, fallback dilakukan di dalam Price_calculator.
		$reference_price = $this->input->post('price_OFFLINE');

		// --- snapshot SEBELUM perubahan ---
		$old_cost = $this->product_vendor_cost_model->find($product_id, $vendor_id);
		$old_prices = $this->price_model->get_current_prices($product_id, $vendor_id);

		// --- hitung nilai baru ---
		$calc = $this->price_calculator->calculate($modal, $margin, $reference_price);

		// --- simpan cost baru (kolom DB `target_hpp_pct` kini menyimpan nilai Margin% target) ---
		$this->product_vendor_cost_model->upsert($product_id, $vendor_id, array(
			'modal' => $modal,
			'target_hpp_pct' => $margin,
			'srp_suggest' => $calc['srp_suggest'],
			'srp_markup_pct' => $calc['markup_pct'],
			'srp_margin_pct' => $calc['margin_pct'],
			'effective_date' => $effective_date,
			'updated_by' => $user_id,
		));

		// --- simpan harga baru per kanal (hanya kanal yang dikirim & berubah) ---
		$channels = $this->price_model->get_channels();
		$new_prices = array();
		$channels_changed = array();

		foreach ($channels as $ch) {
			$posted = $this->input->post('price_' . $ch['channel_code']);
			if ($posted === NULL || $posted === '') continue;

			$posted = (float) $posted;
			$new_prices[$ch['channel_code']] = $posted;

			if (!isset($old_prices[$ch['channel_code']]) || (float) $old_prices[$ch['channel_code']] !== $posted) {
				$channels_changed[] = $ch['channel_code'];
			}

			$this->price_model->upsert_price($product_id, $vendor_id, $ch['id'], $posted, $effective_date, $user_id);
		}

		// --- simpan harga kompetitor (opsional, diinput langsung dari form Update Harga) ---
		$competitor_prices_posted = (array) $this->input->post('competitor_price');
		foreach ($competitor_prices_posted as $competitor_id => $price) {
			if ($price === NULL || $price === '') continue;
			$this->price_model->upsert_competitor_price($product_id, (int) $competitor_id, (float) $price, $effective_date, $user_id);
		}

		// --- catat batch riwayat (trigger notifikasi) ---
		$batch_id = $this->price_change_batch_model->create(array(
			'product_id' => $product_id,
			'vendor_id'  => $vendor_id,
			'effective_date' => $effective_date,
			'changed_by' => $user_id,
			'notes' => $notes,
			'old_values' => json_encode(array(
				'modal' => $old_cost['modal'] ?? 0,
				'margin_pct' => $old_cost['target_hpp_pct'] ?? 0,
				'srp_suggest' => $old_cost['srp_suggest'] ?? 0,
				'channel_prices' => $old_prices,
			)),
			'new_values' => json_encode(array(
				'modal' => $modal,
				'margin_pct' => $margin,
				'srp_suggest' => $calc['srp_suggest'],
				'markup_pct' => $calc['markup_pct'],
				'margin_pct' => $calc['margin_pct'],
				'channel_prices' => $new_prices,
				'channels_changed' => $channels_changed,
			)),
		));

		// Batch tersimpan dengan notify_status default 'pending' (lihat kolom di database_schema.sql).
		// Notifikasi TIDAK langsung dikirim di sini — lihat send_pending(), yang mengirim
		// SATU email gabungan untuk SEMUA batch berstatus 'pending' di database (lintas
		// user/sesi), dipicu lewat tombol "Kirim Notifikasi Sekarang" pada banner global.
		$pending_count = $this->price_change_batch_model->count_pending();
		$this->session->set_flashdata('success', 'Harga berhasil disimpan. Total ' . $pending_count . ' perubahan menunggu dikirim notifikasi — lanjutkan update produk lain, lalu klik "Kirim Notifikasi Sekarang" di atas jika sudah selesai.');

		redirect('price-history/detail/' . $batch_id);
	}

	/**
	 * Kirim SATU email konsolidasi untuk SEMUA batch berstatus 'pending' di database
	 * (lintas user/sesi — lihat Price_change_batch_model::get_pending_ids()).
	 */
	public function send_pending()
	{
		$pending = $this->price_change_batch_model->get_pending_ids();

		if (empty($pending)) {
			$this->session->set_flashdata('error', 'Tidak ada perubahan harga yang menunggu dikirim.');
			redirect($this->_safe_referer());
		}

		$this->load->library('notifier');
		$result = $this->notifier->dispatch_group($pending);

		if ($result['success']) {
			$msg = "Notifikasi terkirim: {$result['batches']} produk ke {$result['recipients']} penerima ({$result['sent']} email berhasil";
			$msg .= $result['failed'] > 0 ? ", {$result['failed']} gagal)." : ').';
			$this->session->set_flashdata('success', $msg);
		} else {
			$this->session->set_flashdata('error', $result['message']);
		}

		redirect('price-history');
	}

	protected function _safe_referer()
	{
		$referer = $this->input->server('HTTP_REFERER');
		return $referer ?: 'price-update';
	}

	/**
	 * Endpoint AJAX: pratinjau isi email sebelum benar-benar disimpan/dikirim.
	 */
	public function preview_email()
	{
		$product = $this->product_model->find($this->input->post('product_id'));
		$modal = (float) $this->input->post('modal');
		$margin = (float) $this->input->post('margin_pct');
		$reference_price = $this->input->post('price_OFFLINE');
		$calc = $this->price_calculator->calculate($modal, $margin, $reference_price);

		$html = $this->load->view('emails/templates/price_update', array(
			'product' => $product,
			'calc' => $calc,
			'effective_date' => $this->input->post('effective_date'),
			'changed_by' => current_user()['name'],
		), TRUE);

		$this->output->set_content_type('text/html')->set_output($html);
	}
}
