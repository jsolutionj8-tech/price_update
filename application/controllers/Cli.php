<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cli
 * Controller khusus dipanggil dari command line (cron job), bukan dari browser.
 * Contoh penjadwalan (crontab -e):
 *   * * * * * php /path/to/project/index.php cli send_email_queue >> /path/to/project/application/logs/cron.log 2>&1
 */
class Cli extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->input->is_cli_request()) {
			show_error('Controller ini hanya dapat diakses melalui command line.', 403);
		}
	}

	/**
	 * Worker: proses ulang batch dengan notify_status = pending/failed/partial
	 * yang belum berhasil sepenuhnya (mode antrian, alternatif dari pengiriman synchronous).
	 */
	public function send_email_queue()
	{
		$this->load->model('price_change_batch_model');
		$this->load->library('notifier');

		$pending = $this->db->where_in('notify_status', array('pending', 'failed'))
			->order_by('created_at', 'ASC')->limit(20)->get('price_change_batches')->result_array();

		foreach ($pending as $batch) {
			echo "Memproses batch #{$batch['id']} ({$batch['batch_code']})...\n";
			$result = $this->notifier->dispatch($batch['id']);
			echo "  -> sent: {$result['sent']}, failed: {$result['failed']}\n";
		}

		echo "Selesai. " . count($pending) . " batch diproses.\n";
	}
}
