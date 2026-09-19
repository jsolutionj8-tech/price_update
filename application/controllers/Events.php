<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Events
 * Kelola event RSVP (mis. acara tasting) beserta jadwalnya & lihat daftar RSVP yang masuk.
 * Halaman isi RSVP publiknya sendiri ada di Event_rsvp.php (tanpa login, diakses via slug).
 */
class Events extends MY_Controller
{
	protected $menu_key = 'events';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('event_model');
		$this->load->model('event_schedule_model');
		$this->load->model('event_rsvp_model');
	}

	public function index()
	{
		$this->render_view('events/index', array(
			'title'  => 'Events',
			'events' => $this->event_model->get_all(),
		));
	}

	public function create()
	{
		$this->render_view('events/form', array('title' => 'Tambah Event'));
	}

	public function store()
	{
		$this->_validate();

		$slug = $this->_generate_slug($this->input->post('event_name', TRUE));

		$this->event_model->create(array(
			'slug'                     => $slug,
			'event_name'               => $this->input->post('event_name', TRUE),
			'invite_text'              => $this->input->post('invite_text', TRUE),
			'tagline'                  => $this->input->post('tagline', TRUE),
			'venue_name'               => $this->input->post('venue_name', TRUE),
			'venue_address'            => $this->input->post('venue_address', TRUE),
			'deposit_per_guest'        => (float) $this->input->post('deposit_per_guest'),
			'membership_gift_text'     => $this->input->post('membership_gift_text', TRUE),
			'membership_register_url'  => $this->input->post('membership_register_url', TRUE),
			'bank_name'                => $this->input->post('bank_name', TRUE),
			'bank_account_number'      => $this->input->post('bank_account_number', TRUE),
			'bank_account_name'        => $this->input->post('bank_account_name', TRUE),
			'rsvp_assistance_phone'    => $this->input->post('rsvp_assistance_phone', TRUE),
			'dresscode'                => $this->input->post('dresscode', TRUE),
			'price_text'               => $this->input->post('price_text', TRUE),
			'flyer_background'         => $this->_handle_flyer_upload(),
			'is_active'                => 1,
			'created_by'               => $this->auth_lib->user_id(),
		));

		$this->session->set_flashdata('success', 'Event berhasil dibuat. Lanjutkan menambahkan jadwal.');
		redirect('events');
	}

	public function edit($id)
	{
		$event = $this->event_model->find($id);
		if (!$event) show_404();

		$this->render_view('events/form', array(
			'title'     => 'Edit Event',
			'event'     => $event,
			'schedules' => $this->event_schedule_model->get_for_event($id),
		));
	}

	public function update($id)
	{
		$current = $this->event_model->find($id);
		if (!$current) show_404();

		$this->_validate();

		$this->event_model->update($id, array(
			'event_name'               => $this->input->post('event_name', TRUE),
			'invite_text'              => $this->input->post('invite_text', TRUE),
			'tagline'                  => $this->input->post('tagline', TRUE),
			'venue_name'               => $this->input->post('venue_name', TRUE),
			'venue_address'            => $this->input->post('venue_address', TRUE),
			'deposit_per_guest'        => (float) $this->input->post('deposit_per_guest'),
			'membership_gift_text'     => $this->input->post('membership_gift_text', TRUE),
			'membership_register_url'  => $this->input->post('membership_register_url', TRUE),
			'bank_name'                => $this->input->post('bank_name', TRUE),
			'bank_account_number'      => $this->input->post('bank_account_number', TRUE),
			'bank_account_name'        => $this->input->post('bank_account_name', TRUE),
			'rsvp_assistance_phone'    => $this->input->post('rsvp_assistance_phone', TRUE),
			'dresscode'                => $this->input->post('dresscode', TRUE),
			'price_text'               => $this->input->post('price_text', TRUE),
			'flyer_background'         => $this->_handle_flyer_upload($current['flyer_background']),
			'is_active'                => $this->input->post('is_active') ? 1 : 0,
		));

		$this->session->set_flashdata('success', 'Event berhasil diperbarui.');
		redirect('events/edit/' . $id);
	}

	public function delete($id)
	{
		$this->event_model->delete($id);
		$this->session->set_flashdata('success', 'Event berhasil dihapus.');
		redirect('events');
	}

	/**
	 * Tambah 1 jadwal ke event (form terpisah kecil di halaman edit event, bukan modal —
	 * langsung redirect balik ke halaman edit yang sama).
	 */
	public function schedule_store($event_id)
	{
		$event = $this->event_model->find($event_id);
		if (!$event) show_404();

		$this->load->library('form_validation');
		$this->form_validation->set_rules('event_date', 'Tanggal', 'required');
		$this->form_validation->set_rules('event_time', 'Jam', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('events/edit/' . $event_id);
		}

		$this->event_schedule_model->create(array(
			'event_id'   => $event_id,
			'event_date' => $this->input->post('event_date'),
			'event_time' => $this->input->post('event_time'),
			'quota'      => $this->input->post('quota') !== '' ? (int) $this->input->post('quota') : NULL,
			'sort_order' => (int) $this->input->post('sort_order'),
			'is_active'  => 1,
		));

		$this->session->set_flashdata('success', 'Jadwal berhasil ditambahkan.');
		redirect('events/edit/' . $event_id);
	}

	public function schedule_delete($id)
	{
		$schedule = $this->event_schedule_model->find($id);
		if (!$schedule) show_404();

		$rsvp_count = $this->event_schedule_model->count_rsvps($id);
		if ($rsvp_count > 0) {
			$this->session->set_flashdata('error', 'Jadwal ini tidak bisa dihapus karena sudah ada ' . $rsvp_count . ' RSVP tamu yang memakainya. Nonaktifkan saja jadwal ini (tombol "Nonaktifkan") supaya tidak muncul lagi di form RSVP, tanpa menghapus data tamu yang sudah terlanjur mendaftar.');
			redirect('events/edit/' . $schedule['event_id']);
		}

		$this->event_schedule_model->delete($id);
		$this->session->set_flashdata('success', 'Jadwal berhasil dihapus.');
		redirect('events/edit/' . $schedule['event_id']);
	}

	/**
	 * Alternatif hapus utk jadwal yang sudah punya RSVP (lihat schedule_delete()) — cukup
	 * disembunyikan dari form RSVP publik (get_for_event($id, TRUE) hanya ambil is_active=1),
	 * data RSVP tamu yang sudah ada tetap utuh.
	 */
	public function schedule_toggle($id)
	{
		$schedule = $this->event_schedule_model->find($id);
		if (!$schedule) show_404();

		$this->event_schedule_model->update($id, array('is_active' => $schedule['is_active'] ? 0 : 1));
		$this->session->set_flashdata('success', $schedule['is_active'] ? 'Jadwal dinonaktifkan.' : 'Jadwal diaktifkan kembali.');
		redirect('events/edit/' . $schedule['event_id']);
	}

	/**
	 * Daftar RSVP yang masuk utk satu event — admin verifikasi pembayaran manual di sini
	 * (tidak ada integrasi payment gateway, deposit dicek manual lewat mutasi rekening).
	 */
	public function rsvps($event_id)
	{
		$event = $this->event_model->find($event_id);
		if (!$event) show_404();

		$filters = array(
			'date_from' => $this->input->get('date_from'),
			'date_to'   => $this->input->get('date_to'),
		);

		$rsvps = $this->event_rsvp_model->get_for_event($event_id, $filters);
		foreach ($rsvps as &$r) {
			$r['guests'] = $this->event_rsvp_model->get_guests($r['id']);
		}
		unset($r);

		$this->render_view('events/rsvps', array(
			'title'     => 'RSVP - ' . ($event['event_name'] !== '' ? $event['event_name'] : '(Tanpa nama)'),
			'event'     => $event,
			'rsvps'     => $rsvps,
			'schedules' => $this->event_schedule_model->get_for_event($event_id),
			'filters'   => $filters,
		));
	}

	/**
	 * Input RSVP manual oleh admin (mis. tamu yang booking lewat telepon/WhatsApp) —
	 * dari modal "Tambah RSVP Manual" di halaman detail RSVP. Alur & validasinya
	 * meniru Event_rsvp::submit() (form publik) supaya datanya konsisten (kuota,
	 * kode tiket unik, dst), hanya field-nya diisi admin, bukan tamu sendiri.
	 */
	public function rsvp_store($event_id)
	{
		$event = $this->event_model->find($event_id);
		if (!$event) show_404();

		$this->load->library('form_validation');
		$this->form_validation->set_rules('schedule_id', 'Jadwal', 'required|integer');
		$this->form_validation->set_rules('orderer_name', 'Nama Pemesan', 'required|trim');
		$this->form_validation->set_rules('phone', 'No. HP', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('guest_count', 'Jumlah Tamu', 'required|integer|greater_than[0]');
		$this->form_validation->set_rules('guest_names', 'Nama Tamu', 'required');
		$this->form_validation->set_rules('is_member', 'Status Member', 'required|in_list[yes,no]');
		$this->form_validation->set_rules('payment_status', 'Status Pembayaran', 'required|in_list[pending,paid]');
		$this->form_validation->set_rules('deposit_amount', 'Deposit', 'required|numeric');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('events/rsvps/' . $event_id);
		}

		$schedule = $this->event_schedule_model->find((int) $this->input->post('schedule_id'));
		if (!$schedule || (int) $schedule['event_id'] !== (int) $event_id) {
			$this->session->set_flashdata('error', 'Jadwal tidak valid.');
			redirect('events/rsvps/' . $event_id);
		}

		$guest_count = (int) $this->input->post('guest_count');
		$guest_names = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $this->input->post('guest_names'))), function ($n) {
			return $n !== '';
		}));
		if (count($guest_names) !== $guest_count) {
			$this->session->set_flashdata('error', 'Jumlah nama tamu (' . count($guest_names) . ') tidak sesuai dengan Jumlah Tamu (' . $guest_count . '). Isi satu nama per baris.');
			redirect('events/rsvps/' . $event_id);
		}

		if (!empty($schedule['quota'])) {
			$booked = $this->event_schedule_model->guest_count_booked($schedule['id']);
			if ($booked + $guest_count > $schedule['quota']) {
				$sisa = max(0, $schedule['quota'] - $booked);
				$this->session->set_flashdata('error', 'Kuota jadwal ini tidak mencukupi. Sisa kuota: ' . $sisa . ' tamu.');
				redirect('events/rsvps/' . $event_id);
			}
		}

		$allergy_note = trim($this->input->post('allergy_note', TRUE));
		$payment_status = $this->input->post('payment_status');

		$data = array(
			'event_id'          => $event_id,
			'schedule_id'       => $schedule['id'],
			'orderer_name'      => $this->input->post('orderer_name', TRUE),
			'phone'             => $this->input->post('phone', TRUE),
			'email'             => $this->input->post('email', TRUE),
			'guest_count'       => $guest_count,
			'is_member'         => $this->input->post('is_member'),
			'marketing_consent' => 0,
			'has_allergy'       => $allergy_note !== '' ? 1 : 0,
			'allergy_note'      => $allergy_note !== '' ? $allergy_note : NULL,
			'deposit_amount'    => (float) $this->input->post('deposit_amount'),
			'payment_method'    => $this->input->post('payment_method', TRUE) ?: 'bank_transfer',
			'payment_status'    => $payment_status,
			'ticket_code'       => $this->event_rsvp_model->generate_unique_ticket_code('ADT'),
			'gift_code'         => $this->event_rsvp_model->generate_unique_ticket_code('GIFT'),
		);

		if ($payment_status === 'paid') {
			$data['verified_by'] = $this->auth_lib->user_id();
			$data['verified_at'] = date('Y-m-d H:i:s');
		}

		$rsvp = $this->event_rsvp_model->create_with_guests($data, $guest_names);
		if (!$rsvp) {
			$this->session->set_flashdata('error', 'Gagal menyimpan RSVP.');
			redirect('events/rsvps/' . $event_id);
		}

		// Khusus input manual: TIDAK kirim email konfirmasi booking — cukup kirim email
		// (konfirmasi pembayaran + QR check-in) kalau admin langsung menandainya lunas.
		if ($payment_status === 'paid') {
			$this->_send_payment_confirmed_email($rsvp['id']);
		}

		$this->session->set_flashdata('success', 'RSVP manual berhasil ditambahkan.');
		redirect('events/rsvps/' . $event_id);
	}

	/**
	 * Tombol "Export Excel" di halaman detail RSVP — lihat Rsvp_exporter utk isi kolom.
	 * date_from/date_to opsional (dari filter tanggal di halaman yang sama) memfilter
	 * berdasarkan tanggal Jadwal, supaya export bisa dibatasi per-hari utk event
	 * multi-hari alih-alih selalu semua RSVP.
	 */
	public function rsvps_export($event_id)
	{
		$filters = array(
			'date_from' => $this->input->get('date_from'),
			'date_to'   => $this->input->get('date_to'),
		);
		$this->load->library('rsvp_exporter');
		$this->rsvp_exporter->export_to_browser($event_id, $filters);
	}

	/**
	 * Tombol "Export PDF" di halaman detail RSVP.
	 */
	public function rsvps_export_pdf($event_id)
	{
		$filters = array(
			'date_from' => $this->input->get('date_from'),
			'date_to'   => $this->input->get('date_to'),
		);
		$this->load->library('rsvp_exporter');
		$this->rsvp_exporter->export_to_pdf_browser($event_id, $filters);
	}

	public function mark_paid($rsvp_id)
	{
		$rsvp = $this->event_rsvp_model->find($rsvp_id);
		if (!$rsvp) show_404();

		$this->event_rsvp_model->mark_paid($rsvp_id, $this->auth_lib->user_id());
		$this->_send_payment_confirmed_email($rsvp_id);
		$this->session->set_flashdata('success', 'Deposit ditandai lunas & email konfirmasi terkirim.');
		redirect('events/rsvps/' . $rsvp['event_id']);
	}

	/**
	 * Kirim email konfirmasi pembayaran (dgn QR code check-in) ke pemesan saat admin klik
	 * "Paid" di daftar RSVP. Kegagalan kirim email TIDAK membatalkan status paid yang sudah
	 * tersimpan — deposit tetap tercatat lunas walau emailnya gagal terkirim.
	 */
	private function _send_payment_confirmed_email($rsvp_id)
	{
		$rsvp = $this->event_rsvp_model->find($rsvp_id);
		$event = $this->event_model->find($rsvp['event_id']);
		$schedule = $this->event_schedule_model->find($rsvp['schedule_id']);
		$guests = $this->event_rsvp_model->get_guests($rsvp_id);

		$this->load->library('event_mailer');
		$this->event_mailer->send(
			$rsvp['email'],
			'Deposit Terverifikasi - ' . ($event['event_name'] !== '' ? $event['event_name'] : 'Atambah'),
			'emails/templates/event_payment_confirmed',
			array(
				'event'    => $event,
				'schedule' => $schedule,
				'rsvp'     => $rsvp,
				'guests'   => $guests,
			)
		);
	}

	public function cancel_rsvp($rsvp_id)
	{
		$rsvp = $this->event_rsvp_model->find($rsvp_id);
		if (!$rsvp) show_404();

		$this->event_rsvp_model->cancel($rsvp_id);
		$this->session->set_flashdata('success', 'RSVP dibatalkan.');
		redirect('events/rsvps/' . $rsvp['event_id']);
	}

	private function _validate()
	{
		// Nama Event sengaja TIDAK wajib — beberapa flyer cukup pakai teks undangan (invite_text)
		// tanpa judul besar (lihat Event_rsvp::index() & views/event_rsvp/flyer.php). Slug tetap
		// otomatis terisi "event" kalau nama dikosongkan (lihat _generate_slug()).
		$this->load->library('form_validation');
		$this->form_validation->set_rules('deposit_per_guest', 'Deposit per Tamu', 'required|numeric');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER'] ?? 'events');
		}
	}

	/**
	 * Upload background flyer (opsional) — dipakai sbg latar halaman flyer publik (Event_rsvp::index()).
	 * Kalau tidak ada file baru dipilih, background lama tetap dipakai (tidak dihapus/direset).
	 * @param string|null $current nama file lama (utk dihapus kalau diganti dgn file baru)
	 * @return string|null nama file tersimpan, atau $current kalau tidak ada upload baru
	 */
	private function _handle_flyer_upload($current = null)
	{
		if (empty($_FILES['flyer_background']['name'])) {
			return $current;
		}

		$upload_path = FCPATH . 'assets/images/events/';
		$this->load->library('upload', array(
			'upload_path'   => $upload_path,
			'allowed_types' => 'jpg|jpeg|png|webp',
			'max_size'      => 5120,
			'encrypt_name'  => TRUE,
		));

		if (!$this->upload->do_upload('flyer_background')) {
			$this->session->set_flashdata('error', 'Gagal upload background flyer: ' . strip_tags($this->upload->display_errors('', '')));
			redirect($_SERVER['HTTP_REFERER'] ?? 'events');
		}

		$new_filename = $this->upload->data('file_name');

		if (!empty($current) && $current !== $new_filename) {
			@unlink($upload_path . $current);
		}

		return $new_filename;
	}

	private function _generate_slug($name)
	{
		$base = strtolower(trim((string) $name));
		$base = trim(preg_replace('/[^a-z0-9]+/', '-', $base), '-');
		if ($base === '') $base = 'event';

		$slug = $base;
		$i = 2;
		while ($this->event_model->slug_exists($slug)) {
			$slug = $base . '-' . $i;
			$i++;
		}
		return $slug;
	}
}
