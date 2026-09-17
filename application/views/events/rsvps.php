<div class="card card-stat p-3 mb-3">
	<div class="d-flex justify-content-between align-items-center">
		<h6 class="fw-bold mb-0">RSVP — <?= $event['event_name'] !== '' ? htmlspecialchars($event['event_name']) : '(Tanpa nama)' ?></h6>
		<div class="d-flex gap-2">
			<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manualRsvpModal"><i class="bi bi-plus-lg"></i> Tambah RSVP Manual</button>
			<a href="<?= base_url('events/rsvps-export/' . $event['id']) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
			<a href="<?= base_url('events/rsvps-export-pdf/' . $event['id']) ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
			<a href="<?= base_url('events') ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
		</div>
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
					<td><?= $r['is_member'] === 'yes' ? '<span class="badge bg-brand text-white">Member</span>' : '-' ?></td>
					<td><?= rupiah($r['deposit_amount']) ?></td>
					<td><?= status_badge($r['payment_status']) ?></td>
					<td class="text-end text-nowrap">
						<?php if ($r['payment_status'] === 'pending'): ?>
							<a href="<?= base_url('events/mark-paid/' . $r['id']) ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Tandai deposit RSVP ini sudah lunas?')"><i class="bi bi-check-lg"></i> Paid</a>
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

<!-- Modal: Tambah RSVP Manual (mis. tamu booking lewat telepon/WhatsApp) -->
<div class="modal fade" id="manualRsvpModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form method="post" action="<?= base_url('events/rsvp-store/' . $event['id']) ?>">
				<div class="modal-header">
					<h5 class="modal-title">Tambah RSVP Manual</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="row g-3">
						<div class="col-md-6">
							<label class="form-label">Jadwal <span class="text-danger">*</span></label>
							<select name="schedule_id" id="manualScheduleId" class="form-select" required>
								<option value="">-- Pilih Jadwal --</option>
								<?php foreach ($schedules as $s): ?>
									<option value="<?= $s['id'] ?>" data-quota="<?= $s['quota'] !== null ? (int) $s['quota'] : '' ?>">
										<?= tgl_indo($s['event_date']) ?> &middot; <?= substr($s['event_time'], 0, 5) ?> WIB<?= $s['is_active'] ? '' : ' (nonaktif)' ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Jumlah Tamu <span class="text-danger">*</span></label>
							<input type="number" name="guest_count" id="manualGuestCount" class="form-control" min="1" value="1" required>
						</div>
						<div class="col-md-6">
							<label class="form-label">Nama Pemesan <span class="text-danger">*</span></label>
							<input type="text" name="orderer_name" class="form-control" required>
						</div>
						<div class="col-md-6">
							<label class="form-label">No. HP <span class="text-danger">*</span></label>
							<input type="text" name="phone" class="form-control" required>
						</div>
						<div class="col-md-12">
							<label class="form-label">Email <span class="text-danger">*</span></label>
							<input type="email" name="email" class="form-control" required>
						</div>
						<div class="col-md-12">
							<label class="form-label">Nama Tamu <span class="text-danger">*</span></label>
							<textarea name="guest_names" class="form-control" rows="3" placeholder="Satu nama per baris, jumlahnya harus sama dengan Jumlah Tamu" required></textarea>
						</div>
						<div class="col-md-4">
							<label class="form-label">Status Member</label>
							<select name="is_member" class="form-select" required>
								<option value="no">Tidak</option>
								<option value="yes">Ya</option>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Metode Pembayaran</label>
							<select name="payment_method" class="form-select">
								<option value="bank_transfer">Transfer Bank</option>
								<option value="cash">Tunai</option>
								<option value="other">Lainnya</option>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
							<select name="payment_status" class="form-select" required>
								<option value="pending">Pending</option>
								<option value="paid">Paid (sudah lunas)</option>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Deposit (Rp) <span class="text-danger">*</span></label>
							<input type="text" inputmode="numeric" name="deposit_amount" id="manualDeposit" class="form-control rupiah-input" required>
							<div class="form-text">Terisi otomatis dari Jadwal &times; Jumlah Tamu, boleh diubah manual.</div>
						</div>
						<div class="col-md-6">
							<label class="form-label">Catatan Alergi (opsional)</label>
							<input type="text" name="allergy_note" class="form-control">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button class="btn btn-primary">Simpan RSVP</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
(function () {
	var depositPerGuest = <?= (float) $event['deposit_per_guest'] ?>;
	var scheduleSelect = document.getElementById('manualScheduleId');
	var guestCountInput = document.getElementById('manualGuestCount');
	var depositInput = document.getElementById('manualDeposit');

	function rupiahDigits(str) { return String(str || '').replace(/\D/g, ''); }
	function formatRupiahDigits(digits) { return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }

	function updateManualDeposit() {
		var count = Math.max(1, parseInt(guestCountInput.value, 10) || 1);
		depositInput.value = formatRupiahDigits(String(Math.round(count * depositPerGuest)));
	}
	guestCountInput.addEventListener('input', updateManualDeposit);
	scheduleSelect.addEventListener('change', updateManualDeposit);

	depositInput.addEventListener('input', function () {
		var digitsBeforeCursor = rupiahDigits(depositInput.value.slice(0, depositInput.selectionStart));
		depositInput.value = formatRupiahDigits(rupiahDigits(depositInput.value));
		var newPos = formatRupiahDigits(digitsBeforeCursor).length;
		depositInput.setSelectionRange(newPos, newPos);
	});
	if (depositInput.form) {
		depositInput.form.addEventListener('submit', function () {
			depositInput.value = rupiahDigits(depositInput.value);
		});
	}

	updateManualDeposit();
})();
</script>
