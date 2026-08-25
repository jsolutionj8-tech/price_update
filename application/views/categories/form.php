<div class="card card-stat p-4" style="max-width:480px;">
	<form method="post" action="<?= isset($category) ? base_url('categories/update/' . $category['id']) : base_url('categories/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Kategori</label>
			<input type="text" name="category_name" class="form-control" required autofocus value="<?= htmlspecialchars($category['category_name'] ?? '') ?>">
		</div>
		<?php if (isset($category)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $category['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($category) ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button>
		<a href="<?= base_url('categories') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
