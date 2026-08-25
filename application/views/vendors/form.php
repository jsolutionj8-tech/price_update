<div class="card card-stat p-4" style="max-width:560px;">
	<form method="post" action="<?= isset($vendor) ? base_url('vendors/update/' . $vendor['id']) : base_url('vendors/store') ?>">
		<?php if (!isset($vendor)): ?>
		<div class="mb-3">
			<label class="form-label">Kode Vendor</label>
			<input type="text" name="vendor_code" id="vendorCode" class="form-control" required maxlength="20" placeholder="mis. VENDOR-A">
			<div class="form-text">Huruf/angka (boleh pakai tanda "-"), otomatis diubah ke huruf besar. Dipakai sebagai identitas unik vendor.</div>
		</div>
		<?php else: ?>
		<div class="mb-3">
			<label class="form-label">Kode Vendor</label>
			<input type="text" class="form-control" value="<?= htmlspecialchars($vendor['vendor_code']) ?>" disabled>
		</div>
		<?php endif; ?>
		<div class="mb-3">
			<label class="form-label">Nama Vendor</label>
			<input type="text" name="vendor_name" class="form-control" value="<?= htmlspecialchars($vendor['vendor_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Kontak (opsional)</label>
			<input type="text" name="contact_info" class="form-control" placeholder="No. telepon / email / alamat singkat" value="<?= htmlspecialchars($vendor['contact_info'] ?? '') ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
	var codeInput = document.getElementById('vendorCode');
	if (codeInput) {
		codeInput.addEventListener('input', function () {
			this.value = this.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '_');
		});
	}
});
</script>
