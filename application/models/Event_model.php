<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event_model
 * CRUD event RSVP (mis. acara tasting/undangan) — dikelola lewat menu Events (admin).
 * Halaman isi RSVP publiknya sendiri diakses lewat `slug`.
 */
class Event_model extends CI_Model
{
	protected $table = 'events';

	public function get_all()
	{
		return $this->db->order_by('created_at', 'DESC')->get($this->table)->result_array();
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
	}

	public function find_by_slug($slug)
	{
		return $this->db->where('slug', $slug)->where('is_active', 1)->get($this->table)->row_array();
	}

	public function slug_exists($slug, $exclude_id = null)
	{
		$this->db->where('slug', $slug);
		if ($exclude_id) $this->db->where('id !=', $exclude_id);
		return (bool) $this->db->get($this->table)->row_array();
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
