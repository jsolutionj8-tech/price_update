<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rsvp_exporter
 * Export daftar RSVP satu event ke Excel/PDF, dipakai oleh tombol "Export Excel"/
 * "Export PDF" di halaman detail RSVP (Events::rsvps()). Pola & struktur mengikuti
 * Price_history_exporter (Excel via PhpSpreadsheet, PDF via Dompdf) supaya konsisten
 * dengan export yang sudah ada di menu Riwayat Perubahan Harga.
 */
class Rsvp_exporter
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('event_model');
		$this->CI->load->model('event_rsvp_model');
	}

	protected function _headers()
	{
		return array('Kode Tiket', 'Pemesan', 'No. HP', 'Email', 'Tanggal', 'Jam', 'Jumlah Tamu', 'Nama Tamu', 'Member', 'Deposit', 'Status');
	}

	/**
	 * @param bool  $raw_deposit TRUE = angka mentah (Excel bisa dijumlah), FALSE = teks
	 *              sudah diformat rupiah (utk PDF, murni tampilan cetak).
	 * @param array $filters    date_from/date_to opsional, memfilter berdasarkan tanggal Jadwal.
	 */
	protected function _build_rows($event_id, $raw_deposit, array $filters = array())
	{
		$rsvps = $this->CI->event_rsvp_model->get_for_event($event_id, $filters);

		$status_labels = array(
			'pending'   => 'Pending',
			'paid'      => 'Paid',
			'cancelled' => 'Cancelled',
		);

		$rows = array();
		foreach ($rsvps as $r) {
			$guests = $this->CI->event_rsvp_model->get_guests($r['id']);
			$guest_names = implode(', ', array_column($guests, 'guest_name'));

			$rows[] = array(
				$r['ticket_code'],
				$r['orderer_name'],
				$r['phone'],
				$r['email'],
				tgl_indo($r['event_date']),
				substr($r['event_time'], 0, 5) . ' WIB',
				(int) $r['guest_count'],
				$guest_names,
				$r['is_member'] === 'yes' ? 'Ya' : 'Tidak',
				$raw_deposit ? (float) $r['deposit_amount'] : rupiah($r['deposit_amount']),
				$status_labels[$r['payment_status']] ?? ucfirst($r['payment_status']),
			);
		}
		return $rows;
	}

	/**
	 * Bangun file .xlsx dari RSVP satu event lalu langsung dikirim ke browser
	 * (download). Method ini exit() di akhir.
	 */
	public function export_to_browser($event_id, array $filters = array())
	{
		if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
			show_error('Library PhpSpreadsheet belum terpasang. Jalankan "composer install" pada root project.');
		}

		$event = $this->CI->event_model->find($event_id);
		if (!$event) show_404();

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('RSVP');
		$sheet->fromArray($this->_headers(), NULL, 'A1');
		$sheet->fromArray($this->_build_rows($event_id, TRUE, $filters), NULL, 'A2');

		$slug = $event['event_name'] !== '' ? $event['event_name'] : $event['slug'];
		$filename = 'rsvp_' . preg_replace('/[^a-z0-9]+/i', '_', $slug) . $this->_filename_date_suffix($filters) . '_' . date('Ymd_His') . '.xlsx';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}

	/**
	 * Bangun file .pdf (tabel sederhana) dari RSVP satu event lalu langsung
	 * dikirim ke browser (download). Method ini exit() di akhir.
	 */
	public function export_to_pdf_browser($event_id, array $filters = array())
	{
		if (!class_exists('\Dompdf\Dompdf')) {
			show_error('Library Dompdf belum terpasang. Jalankan "composer install" pada root project.');
		}

		$event = $this->CI->event_model->find($event_id);
		if (!$event) show_404();

		$headers = $this->_headers();
		$rows = $this->_build_rows($event_id, FALSE, $filters);
		$title = 'RSVP — ' . ($event['event_name'] !== '' ? $event['event_name'] : '(Tanpa nama)');
		if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
			$title .= ' (' . ($filters['date_from'] ? tgl_indo($filters['date_from']) : '...')
				. ' s/d ' . ($filters['date_to'] ? tgl_indo($filters['date_to']) : '...') . ')';
		}

		$html = '<html><head><meta charset="utf-8"><style>
			body { font-family: sans-serif; font-size: 9px; }
			h4 { margin: 0 0 10px; }
			table { width: 100%; border-collapse: collapse; }
			th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
			th { background: #f1f1f1; }
			td.num { text-align: right; }
		</style></head><body>';
		$html .= '<h4>' . htmlspecialchars($title) . ' — dicetak ' . htmlspecialchars(date('d/m/Y H:i')) . '</h4>';
		$html .= '<table><thead><tr>';
		foreach ($headers as $h) $html .= '<th>' . htmlspecialchars($h) . '</th>';
		$html .= '</tr></thead><tbody>';
		foreach ($rows as $r) {
			$html .= '<tr>';
			foreach ($r as $i => $v) {
				$is_num = in_array($i, array(6, 9), TRUE);
				$html .= '<td class="' . ($is_num ? 'num' : '') . '">' . ($v === NULL || $v === '' ? '-' : htmlspecialchars((string) $v)) . '</td>';
			}
			$html .= '</tr>';
		}
		if (empty($rows)) {
			$html .= '<tr><td colspan="' . count($headers) . '" style="text-align:center;color:#888;">Belum ada RSVP masuk untuk event ini.</td></tr>';
		}
		$html .= '</tbody></table></body></html>';

		$dompdf = new \Dompdf\Dompdf(array('isRemoteEnabled' => FALSE));
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->loadHtml($html);
		$dompdf->render();

		$slug = $event['event_name'] !== '' ? $event['event_name'] : $event['slug'];
		$filename = 'rsvp_' . preg_replace('/[^a-z0-9]+/i', '_', $slug) . $this->_filename_date_suffix($filters) . '_' . date('Ymd_His') . '.pdf';
		$dompdf->stream($filename, array('Attachment' => TRUE));
		exit;
	}

	/**
	 * "_2026-09-19" (satu tanggal) atau "_2026-09-19_2026-09-20" (rentang) utk disisipkan
	 * ke nama file export kalau filter tanggal dipakai — kosong kalau tidak difilter.
	 */
	protected function _filename_date_suffix(array $filters)
	{
		if (empty($filters['date_from']) && empty($filters['date_to'])) return '';
		if (($filters['date_from'] ?? '') === ($filters['date_to'] ?? '')) return '_' . $filters['date_from'];
		return '_' . ($filters['date_from'] ?: 'awal') . '_sd_' . ($filters['date_to'] ?: 'akhir');
	}
}
