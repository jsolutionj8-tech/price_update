<div class="card card-stat p-4 mb-3" style="max-width:720px;">
	<h6 class="fw-bold mb-3"><?= isset($event) ? 'Edit Event' : 'Tambah Event' ?></h6>
	<form method="post" action="<?= isset($event) ? base_url('events/update/' . $event['id']) : base_url('events/store') ?>" enctype="multipart/form-data">
		<div class="mb-3">
			<label class="form-label">Nama Event</label>
			<input type="text" name="event_name" class="form-control" required value="<?= htmlspecialchars($event['event_name'] ?? '') ?>" placeholder="A Dialogue of Taste">
		</div>
		<div class="mb-3">
			<label class="form-label">Tagline / Deskripsi Singkat</label>
			<textarea name="tagline" class="form-control" rows="2" placeholder="Satu malam, satu pengalaman omakase yang dirancang khusus..."><?= htmlspecialchars($event['tagline'] ?? '') ?></textarea>
		</div>
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Nama Venue</label>
				<input type="text" name="venue_name" class="form-control" value="<?= htmlspecialchars($event['venue_name'] ?? '') ?>" placeholder="Deka Hall, Surabaya">
			</div>
			<div class="col-md-6">
				<label class="form-label">Alamat Venue</label>
				<input type="text" name="venue_address" class="form-control" value="<?= htmlspecialchars($event['venue_address'] ?? '') ?>">
			</div>
		</div>
		<hr>
		<h6 class="fw-bold mb-3">Flyer (Halaman Undangan)</h6>
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Dresscode</label>
				<input type="text" name="dresscode" class="form-control" value="<?= htmlspecialchars($event['dresscode'] ?? '') ?>" placeholder="Smart Casual">
			</div>
			<div class="col-md-6">
				<label class="form-label">Background Flyer</label>
				<input type="file" name="flyer_background" class="form-control" accept="image/png,image/jpeg,image/webp">
				<?php if (!empty($event['flyer_background'])): ?>
					<div class="mt-2 d-flex align-items-center gap-2">
						<img src="<?= base_url('assets/images/events/' . $event['flyer_background']) ?>" alt="Background flyer saat ini" style="height:60px;border-radius:6px;object-fit:cover;">
						<span class="small text-muted">Background saat ini — upload file baru untuk mengganti.</span>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<p class="text-muted small mb-0">Tanggal &amp; jam pada flyer diambil otomatis dari Jadwal Reservasi di bawah — tidak perlu diisi manual.</p>
		<hr>
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Deposit per Tamu (Rp)</label>
				<input type="number" name="deposit_per_guest" class="form-control" required min="0" step="1000" value="<?= htmlspecialchars($event['deposit_per_guest'] ?? '') ?>">
			</div>
			<div class="col-md-6">
				<label class="form-label">RSVP Assistance (No. HP)</label>
				<input type="text" name="rsvp_assistance_phone" class="form-control" value="<?= htmlspecialchars($event['rsvp_assistance_phone'] ?? '') ?>" placeholder="+62 813-3613-2778">
			</div>
		</div>
		<hr>
		<div class="mb-3">
			<label class="form-label">Teks Membership Gift</label>
			<input type="text" name="membership_gift_text" class="form-control" value="<?= htmlspecialchars($event['membership_gift_text'] ?? '') ?>" placeholder="Member Atambah mendapatkan special gift">
		</div>
		<div class="mb-3">
			<label class="form-label">URL Daftar Member</label>
			<input type="text" name="membership_register_url" class="form-control" value="<?= htmlspecialchars($event['membership_register_url'] ?? '') ?>" placeholder="https://atambah.com/member">
		</div>
		<hr>
		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Nama Bank</label>
				<input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($event['bank_name'] ?? '') ?>" placeholder="BRI">
			</div>
			<div class="col-md-4">
				<label class="form-label">No. Rekening / VA</label>
				<input type="text" name="bank_account_number" class="form-control" value="<?= htmlspecialchars($event['bank_account_number'] ?? '') ?>">
			</div>
			<div class="col-md-4">
				<label class="form-label">Atas Nama</label>
				<input type="text" name="bank_account_name" class="form-control" value="<?= htmlspecialchars($event['bank_account_name'] ?? '') ?>">
			</div>
		</div>
		<?php if (isset($event)): ?>
		<div class="form-check form-switch mt-3">
			<input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= $event['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif (form RSVP publik bisa diakses)</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary mt-3"><?= isset($event) ? 'Simpan Perubahan' : 'Buat Event' ?></button>
		<a href="<?= base_url('events') ?>" class="btn btn-outline-secondary mt-3">Batal</a>
	</form>
</div>

<?php if (isset($event)): ?>
<div class="card card-stat p-4" style="max-width:720px;">
	<h6 class="fw-bold mb-3">Jadwal Reservasi</h6>
	<div class="table-responsive mb-3">
		<table class="table table-sm align-middle">
			<thead><tr><th>Tanggal</th><th>Jam</th><th>Kuota</th><th>Urutan</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($schedules as $s): ?>
				<tr>
					<td><?= tgl_indo($s['event_date']) ?></td>
					<td><?= substr($s['event_time'], 0, 5) ?> WIB</td>
					<td><?= $s['quota'] !== null ? $s['quota'] . ' tamu' : 'Tanpa batas' ?></td>
					<td><?= $s['sort_order'] ?></td>
					<td class="text-end">
						<a href="<?= base_url('events/schedule-delete/' . $s['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus jadwal ini?')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($schedules)): ?>
				<tr><td colspan="5" class="text-center text-muted py-3">Belum ada jadwal. Tambahkan minimal satu jadwal sebelum menyebar link undangan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<form method="post" action="<?= base_url('events/schedule-store/' . $event['id']) ?>" class="row g-2 align-items-end">
		<div class="col-md-3">
			<label class="form-label">Tanggal</label>
			<input type="date" name="event_date" class="form-control" required>
		</div>
		<div class="col-md-2">
			<label class="form-label">Jam</label>
			<input type="time" name="event_time" class="form-control" required value="18:00">
		</div>
		<div class="col-md-2">
			<label class="form-label">Kuota (opsional)</label>
			<input type="number" name="quota" class="form-control" min="1" placeholder="Tanpa batas">
		</div>
		<div class="col-md-2">
			<label class="form-label">Urutan</label>
			<input type="number" name="sort_order" class="form-control" value="0">
		</div>
		<div class="col-md-3">
			<button class="btn btn-outline-primary w-100"><i class="bi bi-plus-lg me-1"></i>Tambah Jadwal</button>
		</div>
	</form>
</div>
<?php endif; ?>
