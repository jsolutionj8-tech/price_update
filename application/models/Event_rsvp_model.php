<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event_rsvp_model
 * Satu baris per submission form RSVP publik + baris nama tamu (event_rsvp_guests).
 * Pembayaran deposit manual — payment_status diverifikasi manual oleh admin (lihat
 * mark_paid()), tidak ada integrasi payment gateway.
 */
class Event_rsvp_model extends CI_Model
{
	protected $table = 'event_rsvps';

	/**
	 * Simpan RSVP + daftar nama tamu dalam satu transaksi (all-or-nothing) — kalau salah
	 * satu insert nama tamu gagal, RSVP induknya ikut dibatalkan (tidak ada RSVP yatim
	 * tanpa nama tamu).
	 * @param array $data       kolom-kolom event_rsvps
	 * @param array $guest_names daftar nama tamu (index 0-based, urutan = sort_order)
	 * @return array|null baris event_rsvps yang baru tersimpan (dgn id), atau null kalau gagal
	 */
	public function create_with_guests($data, array $guest_names)
	{
		$this->db->trans_start();

		$this->db->insert($this->table, $data);
		$rsvp_id = $this->db->insert_id();

		$rows = array();
		foreach ($guest_names as $i => $name) {
			$rows[] = array('rsvp_id' => $rsvp_id, 'guest_name' => $name, 'sort_order' => $i);
		}
		if (!empty($rows)) {
			$this->db->insert_batch('event_rsvp_guests', $rows);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) return null;
		return $this->find($rsvp_id);
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	public function find_by_ticket_code($ticket_code)
	{
		return $this->db->where('ticket_code', $ticket_code)->get($this->table)->row_array();
	}

	/**
	 * Dipakai barcode() — kode yang mau digambar bisa berupa ticket_code ATAU gift_code
	 * (dua barcode berbeda ditampilkan di halaman e-ticket yang sama).
	 */
	public function find_by_any_code($code)
	{
		return $this->db->where('ticket_code', $code)->or_where('gift_code', $code)->get($this->table)->row_array();
	}

	public function get_guests($rsvp_id)
	{
		return $this->db->where('rsvp_id', $rsvp_id)->order_by('sort_order')->get('event_rsvp_guests')->result_array();
	}

	/**
	 * Daftar RSVP utk 1 event (admin), lengkap dgn nama jadwal — dipakai di halaman detail
	 * event pada menu Events.
	 */
	public function get_for_event($event_id)
	{
		return $this->db->select('event_rsvps.*, event_schedules.event_date, event_schedules.event_time')
			->from($this->table)
			->join('event_schedules', 'event_schedules.id = event_rsvps.schedule_id')
			->where('event_rsvps.event_id', $event_id)
			->order_by('event_rsvps.created_at', 'DESC')
			->get()->result_array();
	}

	public function mark_paid($id, $user_id)
	{
		return $this->db->where('id', $id)->update($this->table, array(
			'payment_status' => 'paid',
			'verified_by'    => $user_id,
			'verified_at'    => date('Y-m-d H:i:s'),
		));
	}

	public function cancel($id)
	{
		return $this->db->where('id', $id)->update($this->table, array('payment_status' => 'cancelled'));
	}

	/**
	 * Kode tiket & kode gift unik (format ADT-XXXXXX / GIFT-XXXXXX, 6 digit acak) — dicek
	 * ke DB spy tidak pernah tabrakan walau probabilitasnya sangat kecil.
	 */
	public function generate_unique_ticket_code($prefix = 'ADT')
	{
		do {
			$code = $prefix . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
		} while ($this->find_by_ticket_code($code));
		return $code;
	}
}
