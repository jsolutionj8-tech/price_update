<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event_schedule_model
 * Pilihan tanggal/jam per event (kartu pilihan di step 1 form RSVP publik).
 */
class Event_schedule_model extends CI_Model
{
	protected $table = 'event_schedules';

	public function get_for_event($event_id, $active_only = FALSE)
	{
		$this->db->where('event_id', $event_id)->order_by('sort_order')->order_by('event_date');
		if ($active_only) $this->db->where('is_active', 1);
		return $this->db->get($this->table)->result_array();
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	/**
	 * Berapa RSVP (jumlah tamu, bukan jumlah baris) yang sudah masuk utk 1 jadwal —
	 * dipakai membandingkan ke `quota` saat validasi submit RSVP baru.
	 */
	public function guest_count_booked($schedule_id)
	{
		$row = $this->db->select_sum('guest_count')
			->where('schedule_id', $schedule_id)
			->where('payment_status !=', 'cancelled')
			->get('event_rsvps')->row_array();
		return (int) ($row['guest_count'] ?? 0);
	}

	public function create($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function update($id, $data)
	{
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	public function delete($id)
	{
		return $this->db->where('id', $id)->delete($this->table);
	}
}
