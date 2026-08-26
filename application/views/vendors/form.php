<div class="card card-stat p-4" style="max-width:560px;">
	<form method="post" action="<?= isset($vendor) ? base_url('vendors/update/' . $vendor['id']) : base_url('vendors/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Vendor</label>
			<input type="text" name="vendor_name" class="form-control" value="<?= htmlspecialchars($vendor['vendor_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Kategori (opsional)</label>
			<input type="text" name="vendor_category" class="form-control" list="vendorCategoryList" placeholder="mis. Supplier CK, Supplier NCK, Umum" value="<?= htmlspecialchars($vendor['vendor_category'] ?? '') ?>">
			<datalist id="vendorCategoryList">
				<option value="Supplier CK">
				<option value="Supplier NCK">
				<option value="Umum">
			</datalist>
		</div>
		<?php if (isset($vendor)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $vendor['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($vendor) ? 'Simpan Perubahan' : 'Tambah Vendor' ?></button>
		<a href="<?= base_url('vendors') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
