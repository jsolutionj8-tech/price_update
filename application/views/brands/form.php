<div class="card card-stat p-4" style="max-width:480px;">
	<form method="post" action="<?= isset($brand) ? base_url('brands/update/' . $brand['id']) : base_url('brands/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Brand</label>
			<input type="text" name="brand_name" class="form-control" required autofocus value="<?= htmlspecialchars($brand['brand_name'] ?? '') ?>">
		</div>
		<?php if (isset($brand)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $brand['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($brand) ? 'Simpan Perubahan' : 'Tambah Brand' ?></button>
		<a href="<?= base_url('brands') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
