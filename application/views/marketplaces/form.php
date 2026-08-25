<div class="card card-stat p-4" style="max-width:560px;">
	<form method="post" action="<?= isset($marketplace) ? base_url('marketplaces/update/' . $marketplace['id']) : base_url('marketplaces/store') ?>">
		<?php if (!isset($marketplace)): ?>
		<div class="mb-3">
			<label class="form-label">Kode Marketplace</label>
			<input type="text" name="channel_code" id="channelCode" class="form-control" required maxlength="30" placeholder="mis. TOKOPEDIA, SHOPEE, OFFLINE">
			<div class="form-text">Huruf/angka tanpa spasi, otomatis diubah ke huruf besar. Dipakai sebagai identitas unik marketplace/kanal.</div>
		</div>
		<?php else: ?>
		<div class="mb-3">
			<label class="form-label">Kode Marketplace</label>
			<input type="text" class="form-control" value="<?= htmlspecialchars($marketplace['channel_code']) ?>" disabled>
		</div>
		<?php endif; ?>
		<div class="mb-3">
			<label class="form-label">Nama Marketplace</label>
			<input type="text" name="channel_name" class="form-control" required value="<?= htmlspecialchars($marketplace['channel_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Urutan Tampil</label>
			<input type="number" name="sort_order" class="form-control" value="<?= $marketplace['sort_order'] ?? $suggested_order ?? 0 ?>">
			<div class="form-text">Menentukan urutan tampil marketplace pada form Update Harga (angka kecil tampil lebih dulu).</div>
		</div>
		<?php if (isset($marketplace)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $marketplace['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($marketplace) ? 'Simpan Perubahan' : 'Tambah Marketplace' ?></button>
		<a href="<?= base_url('marketplaces') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var codeInput = document.getElementById('channelCode');
	if (codeInput) {
		codeInput.addEventListener('input', function () {
			this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '_');
		});
	}
});
</script>
