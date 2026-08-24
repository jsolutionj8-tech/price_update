<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_group_model extends CI_Model
{
	protected $table = 'notification_groups';

	public function get_all()
	{
		return $this->db->order_by('group_name')->get($this->table)->result_array();
	}

	public function find($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row_array();
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
		$this->db->where('group_id', $id)->delete('notification_group_channels');
		$this->db->where('group_id', $id)->delete('notification_group_members');
		return $this->db->where('id', $id)->delete($this->table);
	}

	public function set_channels($group_id, array $channel_ids)
	{
		$this->db->where('group_id', $group_id)->delete('notification_group_channels');
		foreach ($channel_ids as $cid) {
			$this->db->insert('notification_group_channels', array('group_id' => $group_id, 'channel_id' => $cid));
		}
	}

	public function set_members($group_id, array $user_ids)
	{
		$this->db->where('group_id', $group_id)->delete('notification_group_members');
		foreach ($user_ids as $uid) {
			$this->db->insert('notification_group_members', array('group_id' => $group_id, 'user_id' => $uid));
		}
	}

	public function get_channel_ids($group_id)
	{
		return array_column($this->db->select('channel_id')->where('group_id', $group_id)->get('notification_group_channels')->result_array(), 'channel_id');
	}

	public function get_member_ids($group_id)
	{
		return array_column($this->db->select('user_id')->where('group_id', $group_id)->get('notification_group_members')->result_array(), 'user_id');
	}

	/**
	 * Ambil daftar penerima untuk sekumpulan kode kanal yang berubah, beserta
	 * 'matched_channels' — subset kanal (dari $channel_codes) yang benar-benar
	 * dilanggan tiap penerima (gabungan dari semua grup yang diikutinya).
	 * Dipakai oleh Notifier::dispatch() agar isi email tiap penerima hanya
	 * menampilkan kanal yang relevan untuknya, bukan semua kanal yang berubah.
	 */
	public function get_recipients_for_channels(array $channel_codes)
	{
		if (empty($channel_codes)) return array();

		$this->db->select('users.id, users.email, users.full_name, GROUP_CONCAT(DISTINCT price_channels.channel_code) AS matched_channels')
			->from('notification_group_members')
			->join('notification_groups', 'notification_groups.id = notification_group_members.group_id')
			->join('notification_group_channels', 'notification_group_channels.group_id = notification_groups.id')
			->join('price_channels', 'price_channels.id = notification_group_channels.channel_id')
			->join('users', 'users.id = notification_group_members.user_id')
			->where('notification_groups.is_active', 1)
			->where('users.is_active', 1)
			->where_in('price_channels.channel_code', $channel_codes)
			->group_by('users.id, users.email, users.full_name');

		return $this->db->get()->result_array();
	}
}
