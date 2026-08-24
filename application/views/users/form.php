<div class="card card-stat p-4" style="max-width:500px;">
	<form method="post" action="<?= isset($user) ? base_url('users/update/' . $user['id']) : base_url('users/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Lengkap</label>
			<input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Email</label>
			<input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email'] ?? '') ?>" <?= isset($user) ? 'readonly' : '' ?>>
		</div>
		<div class="mb-3">
			<label class="form-label">No. Telepon</label>
			<input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Role</label>
			<select name="role_id" class="form-select" required>
				<?php foreach ($roles as $r): ?>
					<option value="<?= $r['id'] ?>" <?= (isset($user) && $user['role_id'] == $r['id']) ? 'selected' : '' ?>><?= htmlspecialchars($r['role_name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="mb-3">
			<label class="form-label">Password <?= isset($user) ? '(kosongkan jika tidak diubah)' : '' ?></label>
			<input type="password" name="password" class="form-control" <?= isset($user) ? '' : 'required minlength="8"' ?>>
		</div>
		<?php if (isset($user)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $user['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($user) ? 'Simpan Perubahan' : 'Tambah User' ?></button>
		<a href="<?= base_url('users') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
