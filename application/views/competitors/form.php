<div class="card card-stat p-4" style="max-width:560px;">
	<form method="post" action="<?= isset($competitor) ? base_url('competitors/update/' . $competitor['id']) : base_url('competitors/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Kompetitor</label>
			<input type="text" name="competitor_name" class="form-control" required value="<?= htmlspecialchars($competitor['competitor_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Website (opsional)</label>
			<input type="url" name="website_url" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($competitor['website_url'] ?? '') ?>">
		</div>
		<?php if (isset($competitor)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $competitor['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($competitor) ? 'Simpan Perubahan' : 'Tambah Kompetitor' ?></button>
		<a href="<?= base_url('competitors') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
