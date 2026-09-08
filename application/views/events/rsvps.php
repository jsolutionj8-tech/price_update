<div class="card card-stat p-3 mb-3">
	<div class="d-flex justify-content-between align-items-center">
		<h6 class="fw-bold mb-0">RSVP — <?= htmlspecialchars($event['event_name']) ?></h6>
		<a href="<?= base_url('events') ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
	</div>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead>
				<tr>
					<th>Kode Tiket</th>
					<th>Pemesan</th>
					<th>Jadwal</th>
					<th>Tamu</th>
					<th>Member</th>
					<th>Deposit</th>
					<th>Status</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($rsvps as $r): ?>
				<tr>
					<td><code><?= htmlspecialchars($r['ticket_code']) ?></code></td>
					<td>
						<b><?= htmlspecialchars($r['orderer_name']) ?></b>
						<div class="small text-muted"><?= htmlspecialchars($r['phone']) ?> &middot; <?= htmlspecialchars($r['email']) ?></div>
						<?php if (!empty($r['has_allergy'])): ?>
							<div class="small text-danger"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($r['allergy_note']) ?></div>
						<?php endif; ?>
					</td>
					<td><?= tgl_indo($r['event_date']) ?><br><span class="text-muted small"><?= substr($r['event_time'], 0, 5) ?> WIB</span></td>
					<td>
						<?= $r['guest_count'] ?> orang
						<div class="small text-muted"><?= htmlspecialchars(implode(', ', array_column($r['guests'], 'guest_name'))) ?></div>
					</td>
					<td><?= $r['is_member'] === 'yes' ? '<span class="badge bg-purple text-white" style="background:#7c3aed;">Member</span>' : '-' ?></td>
					<td><?= rupiah($r['deposit_amount']) ?></td>
					<td><?= status_badge($r['payment_status']) ?></td>
					<td class="text-end text-nowrap">
						<?php if ($r['payment_status'] === 'pending'): ?>
							<a href="<?= base_url('events/mark-paid/' . $r['id']) ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Tandai deposit RSVP ini sudah lunas?')"><i class="bi bi-check-lg"></i> Lunas</a>
							<a href="<?= base_url('events/cancel-rsvp/' . $r['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan RSVP ini?')"><i class="bi bi-x-lg"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($rsvps)): ?>
				<tr><td colspan="8" class="text-center text-muted py-3">Belum ada RSVP masuk untuk event ini.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
