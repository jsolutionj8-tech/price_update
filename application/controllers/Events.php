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
		$this->_validate();

		$this->event_model->update($id, array(
			'event_name'               => $this->input->post('event_name', TRUE),
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

		$this->event_schedule_model->delete($id);
		$this->session->set_flashdata('success', 'Jadwal berhasil dihapus.');
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

		$rsvps = $this->event_rsvp_model->get_for_event($event_id);
		foreach ($rsvps as &$r) {
			$r['guests'] = $this->event_rsvp_model->get_guests($r['id']);
		}
		unset($r);

		$this->render_view('events/rsvps', array(
			'title' => 'RSVP - ' . $event['event_name'],
			'event' => $event,
			'rsvps' => $rsvps,
		));
	}

	public function mark_paid($rsvp_id)
	{
		$rsvp = $this->event_rsvp_model->find($rsvp_id);
		if (!$rsvp) show_404();

		$this->event_rsvp_model->mark_paid($rsvp_id, $this->auth_lib->user_id());
		$this->session->set_flashdata('success', 'Deposit ditandai lunas.');
		redirect('events/rsvps/' . $rsvp['event_id']);
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
		$this->load->library('form_validation');
		$this->form_validation->set_rules('event_name', 'Nama Event', 'required');
		$this->form_validation->set_rules('deposit_per_guest', 'Deposit per Tamu', 'required|numeric');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect($_SERVER['HTTP_REFERER'] ?? 'events');
		}
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
