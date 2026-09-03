<div class="card card-stat p-4" style="max-width:520px;">
	<h6 class="fw-bold mb-3">Pengaturan Email Pengirim (SMTP)</h6>
	<p class="text-muted small">Kredensial di bawah dipakai untuk mengirim notifikasi email (Kirim Notifikasi Sekarang, Kirim Ulang Notifikasi). Untuk Gmail, gunakan <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">App Password</a>, bukan password akun biasa.</p>
	<form method="post" action="<?= base_url('settings/update') ?>">
		<div class="mb-3">
			<label class="form-label">SMTP Host</label>
			<input type="text" name="smtp_host" class="form-control" required value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
		</div>
		<div class="row g-3">
			<div class="col-md-6">
				<div class="mb-3">
					<label class="form-label">SMTP Port</label>
					<input type="number" name="smtp_port" class="form-control" required value="<?= htmlspecialchars($settings['smtp_port'] ?? 587) ?>">
				</div>
			</div>
			<div class="col-md-6">
				<div class="mb-3">
					<label class="form-label">Enkripsi</label>
					<select name="smtp_crypto" class="form-select">
						<?php $crypto = $settings['smtp_crypto'] ?? 'tls'; ?>
						<option value="tls" <?= $crypto === 'tls' ? 'selected' : '' ?>>TLS</option>
						<option value="ssl" <?= $crypto === 'ssl' ? 'selected' : '' ?>>SSL</option>
						<option value="" <?= $crypto === '' ? 'selected' : '' ?>>Tanpa Enkripsi</option>
					</select>
				</div>
			</div>
		</div>
		<div class="mb-3">
			<label class="form-label">Email Pengirim (SMTP Username)</label>
			<input type="email" name="smtp_user" class="form-control" required value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Password SMTP (kosongkan jika tidak diubah)</label>
			<input type="password" name="smtp_pass" class="form-control" autocomplete="new-password" placeholder="<?= !empty($settings['id']) ? '••••••••' : '' ?>">
		</div>
		<hr>
		<div class="mb-3">
			<label class="form-label">Nama Pengirim (From Name)</label>
			<input type="text" name="from_name" class="form-control" required value="<?= htmlspecialchars($settings['from_name'] ?? '') ?>" placeholder="Sistem Update Harga">
		</div>
		<div class="mb-3">
			<label class="form-label">Email Pengirim (From Email)</label>
			<input type="email" name="from_email" class="form-control" required value="<?= htmlspecialchars($settings['from_email'] ?? '') ?>">
			<div class="form-text">Biasanya sama dengan Email Pengirim (SMTP Username) di atas.</div>
		</div>
		<button class="btn btn-primary">Simpan Pengaturan</button>
	</form>
</div>
