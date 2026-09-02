<div class="card card-stat p-3 mb-3">
	<div class="d-flex justify-content-between align-items-start">
		<div>
			<h5 class="fw-bold mb-1"><?= htmlspecialchars($batch['product_name']) ?> <small class="text-muted">(<?= htmlspecialchars($batch['product_code']) ?>)</small></h5>
			<div>Kode Batch: <code><?= htmlspecialchars($batch['batch_code']) ?></code></div>
			<div>Tanggal Efektif: <?= tgl_indo($batch['effective_date']) ?> &nbsp;|&nbsp; Diubah oleh: <?= htmlspecialchars($batch['changed_by_name']) ?></div>
			<div class="mt-1 notify-status-cell">Status Notifikasi: <?= status_badge($batch['notify_status']) ?></div>
		</div>
		<div>
			<a href="<?= base_url('price-update/form/' . $batch['product_id']) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>Edit Update Harga</a>
			<a href="<?= base_url('price-history/resend/' . $batch['id']) ?>" class="btn btn-outline-primary btn-sm" onclick="return confirm('Kirim ulang notifikasi email untuk perubahan ini?')"><i class="bi bi-arrow-repeat me-1"></i>Kirim Ulang Notifikasi</a>
			<a href="<?= base_url('price-history') ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
		</div>
	</div>
</div>

<div class="row g-3">
	<div class="col-md-6">
		<div class="card card-stat p-3">
			<h6 class="fw-bold">Perbandingan Harga per Kanal</h6>
			<table class="table table-sm">
				<thead><tr><th>Kanal</th><th>Harga Lama</th><th>Harga Baru</th></tr></thead>
				<tbody>
				<?php foreach (($new_values['channel_prices'] ?? array()) as $ch => $newp): ?>
					<tr>
						<td><?= htmlspecialchars($ch) ?></td>
						<td><?= rupiah($old_values['channel_prices'][$ch] ?? 0) ?></td>
						<td class="fw-bold"><?= rupiah($newp) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<table class="table table-sm mt-2">
				<tr><td>Modal</td><td><?= rupiah($old_values['modal'] ?? 0) ?></td><td class="fw-bold"><?= rupiah($new_values['modal'] ?? 0) ?></td></tr>
				<tr><td>SRP Suggest</td><td><?= rupiah($old_values['srp_suggest'] ?? 0) ?></td><td class="fw-bold"><?= rupiah($new_values['srp_suggest'] ?? 0) ?></td></tr>
				<tr><td>Markup %</td><td colspan="2" class="fw-bold"><?= percent_fmt($new_values['markup_pct'] ?? 0) ?></td></tr>
				<tr><td>Margin %</td><td colspan="2" class="fw-bold"><?= percent_fmt($new_values['margin_pct'] ?? 0) ?></td></tr>
			</table>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card card-stat p-3">
			<h6 class="fw-bold">Log Pengiriman Email</h6>
			<table class="table table-sm">
				<thead><tr><th>Penerima</th><th>Status</th><th>Waktu</th></tr></thead>
				<tbody>
				<?php foreach ($email_logs as $log): ?>
					<tr>
						<td><?= htmlspecialchars($log['recipient_email']) ?></td>
						<td><?= status_badge($log['status']) ?></td>
						<td><?= $log['sent_at'] ? tgl_indo($log['sent_at'], TRUE) : '-' ?></td>
					</tr>
				<?php endforeach; ?>
				<?php if (empty($email_logs)): ?>
					<tr><td colspan="3" class="text-center text-muted">Belum ada log pengiriman.</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
