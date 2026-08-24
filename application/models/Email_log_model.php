<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_log_model extends CI_Model
{
	protected $table = 'email_logs';

	public function create_queued($batch_id, $user_id, $email, $subject)
	{
		$this->db->insert($this->table, array(
			'price_change_batch_id' => $batch_id,
			'recipient_user_id' => $user_id,
			'recipient_email' => $email,
			'subject' => $subject,
			'status' => 'queued',
		));
		return $this->db->insert_id();
	}

	public function mark_sent($id)
	{
		$this->db->where('id', $id)->update($this->table, array(
			'status' => 'sent', 'sent_at' => date('Y-m-d H:i:s'),
		));
	}

	public function mark_failed($id, $error)
	{
		$this->db->where('id', $id)->update($this->table, array(
			'status' => 'failed',
			'error_message' => mb_substr((string) $error, 0, 490),
			'attempt_count' => $this->db->query("SELECT attempt_count FROM {$this->table} WHERE id = ?", array($id))->row('attempt_count') + 1,
		));
	}

	public function get_by_batch($batch_id)
	{
		return $this->db->where('price_change_batch_id', $batch_id)->get($this->table)->result_array();
	}

	public function count_sent_this_month()
	{
		return $this->db->where('status', 'sent')->where('sent_at >=', date('Y-m-01'))->count_all_results($this->table);
	}

	public function count_failed_this_month()
	{
		return $this->db->where('status', 'failed')->where('created_at >=', date('Y-m-01'))->count_all_results($this->table);
	}
}
