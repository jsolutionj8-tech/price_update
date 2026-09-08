<div class="card card-stat p-3 mb-3">
	<div class="d-flex justify-content-between align-items-center">
		<h6 class="fw-bold mb-0">Daftar Event</h6>
		<a href="<?= base_url('events/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Event</a>
	</div>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Nama Event</th><th>Venue</th><th>Deposit/Tamu</th><th>Status</th><th>Link Undangan</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($events as $e): ?>
				<?php $rsvp_url = base_url('rsvp/' . $e['slug']); ?>
				<tr>
					<td><b><?= htmlspecialchars($e['event_name']) ?></b></td>
					<td><?= htmlspecialchars($e['venue_name'] ?? '-') ?></td>
					<td><?= rupiah($e['deposit_per_guest']) ?></td>
					<td><?= status_badge($e['is_active'] ? 'active' : 'inactive') ?></td>
					<td>
						<div class="input-group input-group-sm" style="max-width:260px;">
							<input type="text" class="form-control form-control-sm rsvp-link-input" readonly value="<?= htmlspecialchars($rsvp_url) ?>">
							<button type="button" class="btn btn-outline-secondary copy-link-btn" data-link="<?= htmlspecialchars($rsvp_url) ?>"><i class="bi bi-clipboard"></i></button>
						</div>
					</td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('events/rsvps/' . $e['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-people"></i> RSVP</a>
						<a href="<?= base_url('events/edit/' . $e['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('events/delete/' . $e['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus event ini beserta seluruh jadwal & RSVP-nya? Tindakan ini tidak dapat dibatalkan.')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($events)): ?>
				<tr><td colspan="6" class="text-center text-muted py-3">Belum ada event.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<script>
document.querySelectorAll('.copy-link-btn').forEach(function (btn) {
	btn.addEventListener('click', function () {
		navigator.clipboard.writeText(btn.dataset.link).then(function () {
			const icon = btn.querySelector('i');
			icon.className = 'bi bi-check-lg';
			setTimeout(function () { icon.className = 'bi bi-clipboard'; }, 1500);
		});
	});
});
</script>
