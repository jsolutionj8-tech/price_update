<div class="card card-stat p-4" style="max-width:640px;">
	<form method="post" action="<?= isset($group) ? base_url('notification-groups/update/' . $group['id']) : base_url('notification-groups/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Grup</label>
			<input type="text" name="group_name" class="form-control" required value="<?= htmlspecialchars($group['group_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Deskripsi</label>
			<input type="text" name="description" class="form-control" value="<?= htmlspecialchars($group['description'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label fw-bold">Kanal yang Diikuti</label><br>
			<?php foreach ($channels as $ch): ?>
				<div class="form-check form-check-inline">
					<input type="checkbox" class="form-check-input" name="channel_ids[]" value="<?= $ch['id'] ?>"
						<?= (isset($selected_channels) && in_array($ch['id'], $selected_channels)) ? 'checked' : '' ?>>
					<label class="form-check-label"><?= htmlspecialchars($ch['channel_name']) ?></label>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="mb-3">
			<label class="form-label fw-bold">Anggota Grup</label>
			<select name="user_ids[]" class="form-select" multiple size="6">
				<?php foreach ($users as $u): ?>
					<option value="<?= $u['id'] ?>" <?= (isset($selected_members) && in_array($u['id'], $selected_members)) ? 'selected' : '' ?>>
						<?= htmlspecialchars($u['full_name'] . ' (' . $u['email'] . ')') ?>
					</option>
				<?php endforeach; ?>
			</select>
			<small class="text-muted">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</small>
		</div>
		<?php if (isset($group)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $group['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary">Simpan</button>
		<a href="<?= base_url('notification-groups') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
