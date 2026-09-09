<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event_rsvp
 * Halaman PUBLIK (tanpa login) tempat tamu mengisi form RSVP event & melihat e-ticket-nya.
 * Sengaja extends CI_Controller langsung (bukan MY_Controller) — sama seperti Auth.php,
 * krn halaman ini diakses tamu umum lewat link undangan, bukan user internal yang login.
 * Data event/jadwal dikelola terpisah lewat menu admin Events (lihat Events.php).
 */
class Event_rsvp extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('event_model');
		$this->load->model('event_schedule_model');
		$this->load->model('event_rsvp_model');
	}

	/**
	 * Halaman flyer/undangan — tampil PERTAMA saat link dibuka, sebelum form pendaftaran.
	 * Tanggal & jam diringkas otomatis dari Jadwal Reservasi (event_schedules), bukan field
	 * terpisah, supaya tidak ada data yang bisa tidak sinkron antara flyer & pilihan jadwal.
	 */
	public function index($slug)
	{
		$event = $this->event_model->find_by_slug($slug);
		if (!$event) show_404();

		$schedules = $this->event_schedule_model->get_for_event($event['id'], TRUE);

		$dates = array();
		$times = array();
		foreach ($schedules as $s) {
			$dates[tgl_indo($s['event_date'])] = TRUE;
			$times[substr($s['event_time'], 0, 5)] = TRUE;
		}

		$this->load->view('event_rsvp/flyer', array(
			'event'      => $event,
			'date_text'  => implode(' & ', array_keys($dates)),
			'time_text'  => implode(' / ', array_keys($times)) . ' WIB',
		));
	}

	/**
	 * Halaman wizard 4 langkah (klik "RSVP Sekarang" dari flyer). Semua langkah dirender
	 * dalam satu halaman (ditoggle lewat JS di sisi klien) — data baru benar2 dikirim ke
	 * server sekali, lewat submit() di langkah terakhir, supaya tidak ada RSVP "setengah
	 * jadi" tersimpan di database.
	 */
	public function form($slug)
	{
		$event = $this->event_model->find_by_slug($slug);
		if (!$event) show_404();

		$data = array(
			'event'     => $event,
			'schedules' => $this->event_schedule_model->get_for_event($event['id'], TRUE),
		);
		$this->load->view('event_rsvp/wizard', $data);
	}

	/**
	 * AJAX submit langkah terakhir wizard: validasi, hitung deposit, simpan RSVP + nama
	 * tamu (transaksi), kirim email konfirmasi, balas JSON berisi URL e-ticket.
	 */
	public function submit($slug)
	{
		$event = $this->event_model->find_by_slug($slug);
		if (!$event) {
			return $this->_json_error('Event tidak ditemukan.');
		}

		$this->load->library('form_validation');
		$this->form_validation->set_rules('schedule_id', 'Jadwal', 'required|integer');
		$this->form_validation->set_rules('orderer_name', 'Nama pemesan', 'required|trim');
		$this->form_validation->set_rules('phone', 'Nomor handphone', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('guest_count', 'Total guest', 'required|integer|greater_than[0]');
		$this->form_validation->set_rules('is_member', 'Status member', 'required|in_list[yes,no]');
		$this->form_validation->set_rules('guest_name[]', 'Nama tamu', 'required|trim');

		if ($this->form_validation->run() === FALSE) {
			return $this->_json_error(strip_tags(validation_errors()));
		}

		$schedule = $this->event_schedule_model->find((int) $this->input->post('schedule_id'));
		if (!$schedule || (int) $schedule['event_id'] !== (int) $event['id']) {
			return $this->_json_error('Jadwal tidak valid.');
		}

		$guest_count = (int) $this->input->post('guest_count');
		$guest_names = array_values(array_filter((array) $this->input->post('guest_name'), function ($n) {
			return trim((string) $n) !== '';
		}));
		if (count($guest_names) !== $guest_count) {
			return $this->_json_error('Jumlah nama tamu tidak sesuai dengan Total guest.');
		}

		if (!empty($schedule['quota'])) {
			$booked = $this->event_schedule_model->guest_count_booked($schedule['id']);
			if ($booked + $guest_count > $schedule['quota']) {
				$sisa = max(0, $schedule['quota'] - $booked);
				return $this->_json_error('Kuota jadwal ini tidak mencukupi. Sisa kuota: ' . $sisa . ' tamu.');
			}
		}

		$has_allergy = $this->input->post('has_allergy') === '1';
		$deposit_amount = $event['deposit_per_guest'] * $guest_count;

		$data = array(
			'event_id'          => $event['id'],
			'schedule_id'       => $schedule['id'],
			'orderer_name'      => $this->input->post('orderer_name', TRUE),
			'phone'             => $this->input->post('phone', TRUE),
			'email'             => $this->input->post('email', TRUE),
			'guest_count'       => $guest_count,
			'is_member'         => $this->input->post('is_member'),
			'marketing_consent' => $this->input->post('marketing_consent') === '1' ? 1 : 0,
			'has_allergy'       => $has_allergy ? 1 : 0,
			'allergy_note'      => $has_allergy ? $this->input->post('allergy_note', TRUE) : NULL,
			'deposit_amount'    => $deposit_amount,
			'payment_method'    => $this->input->post('payment_method', TRUE) ?: 'bank_transfer',
			'ticket_code'       => $this->event_rsvp_model->generate_unique_ticket_code('ADT'),
			'gift_code'         => $this->event_rsvp_model->generate_unique_ticket_code('GIFT'),
		);

		$rsvp = $this->event_rsvp_model->create_with_guests($data, $guest_names);
		if (!$rsvp) {
			return $this->_json_error('Gagal menyimpan RSVP. Coba lagi.');
		}

		$this->_send_confirmation_email($event, $schedule, $rsvp, $guest_names);

		return $this->_json_success(array(
			'ticket_code'  => $rsvp['ticket_code'],
			'redirect_url' => base_url('rsvp-ticket/' . $rsvp['ticket_code']),
		));
	}

	/**
	 * Halaman e-ticket (step 5) — bisa dibuka ulang lewat link di email konfirmasi, tidak
	 * cuma sekali muncul pas submit.
	 */
	public function ticket($ticket_code)
	{
		$rsvp = $this->event_rsvp_model->find_by_ticket_code($ticket_code);
		if (!$rsvp) show_404();

		$event = $this->event_model->find($rsvp['event_id']);
		$schedule = $this->event_schedule_model->find($rsvp['schedule_id']);
		$guests = $this->event_rsvp_model->get_guests($rsvp['id']);

		$this->load->view('event_rsvp/eticket', array(
			'event'    => $event,
			'schedule' => $schedule,
			'rsvp'     => $rsvp,
			'guests'   => $guests,
		));
	}

	/**
	 * Gambar barcode PNG on-the-fly (Code128) — dipakai di halaman e-ticket (langsung
	 * <img src="...">) DAN di-embed di email konfirmasi lewat URL yang sama, jadi tidak
	 * perlu generate/attach file gambar terpisah.
	 */
	public function barcode($code)
	{
		$rsvp = $this->event_rsvp_model->find_by_any_code($code);
		if (!$rsvp) show_404();

		$this->load->library('ticket_barcode');
		$png = $this->ticket_barcode->png($code);

		$this->output->set_content_type('image/png')->set_output($png);
	}

	private function _json_error($message)
	{
		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success' => FALSE,
			'message' => $message,
		)));
	}

	private function _json_success($data)
	{
		$this->output->set_content_type('application/json')->set_output(json_encode(array_merge(
			array('success' => TRUE),
			$data
		)));
	}

	/**
	 * Kirim email konfirmasi ke pemesan — pakai kredensial SMTP yang sama dengan notifikasi
	 * harga (menu Settings), tapi lepas dari class Notifier (yang khusus utk batch harga).
	 * Kegagalan kirim email TIDAK membatalkan RSVP yang sudah tersimpan (lihat pemanggilnya).
	 */
	private function _send_confirmation_email($event, $schedule, $rsvp, $guest_names)
	{
		$this->load->model('smtp_settings_model');
		$smtp = $this->smtp_settings_model->get();
		if (empty($smtp)) return;

		$this->load->config('email');
		$this->load->library('email', array(
			'protocol'     => 'smtp',
			'smtp_host'    => $smtp['smtp_host'],
			'smtp_port'    => $smtp['smtp_port'],
			'smtp_user'    => $smtp['smtp_user'],
			'smtp_pass'    => $smtp['smtp_pass'],
			'smtp_crypto'  => $smtp['smtp_crypto'],
			'smtp_timeout' => $this->config->item('smtp_timeout') ?: 30,
			'mailtype'     => $this->config->item('mailtype'),
			'charset'      => $this->config->item('charset'),
			'newline'      => $this->config->item('newline'),
		));

		$body = $this->load->view('emails/templates/event_confirmation', array(
			'event'       => $event,
			'schedule'    => $schedule,
			'rsvp'        => $rsvp,
			'guest_names' => $guest_names,
		), TRUE);

		$this->email->clear(TRUE);
		$this->email->from($smtp['from_email'], $smtp['from_name']);
		$this->email->to($rsvp['email']);
		$this->email->subject('Konfirmasi RSVP - ' . $event['event_name']);
		$this->email->message($body);

		try {
			$this->email->send();
		} catch (Exception $e) {
			log_message('error', 'Gagal kirim email konfirmasi RSVP (ticket ' . $rsvp['ticket_code'] . '): ' . $e->getMessage());
		}
	}
}
