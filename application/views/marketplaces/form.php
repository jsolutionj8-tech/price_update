<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<div class="card card-stat p-4" style="max-width:560px;">
	<form method="post" action="<?= isset($marketplace) ? base_url('marketplaces/update/' . $marketplace['id']) : base_url('marketplaces/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Sales Channel</label>
			<input type="text" name="channel_name" class="form-control" required value="<?= htmlspecialchars($marketplace['channel_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Urutan Tampil</label>
			<input type="number" name="sort_order" class="form-control" value="<?= $marketplace['sort_order'] ?? $suggested_order ?? 0 ?>">
			<div class="form-text">Menentukan urutan tampil sales channel pada form Update Harga (angka kecil tampil lebih dulu).</div>
		</div>
		<div class="mb-3">
			<label class="form-label">Biaya Tambahan (opsional)</label>
			<select name="cost_ids[]" id="costSelect" class="form-select" multiple style="width:100%">
				<?php foreach ($costs as $cst): ?>
					<option value="<?= $cst['id'] ?>" <?= in_array((int) $cst['id'], $selected_cost_ids, TRUE) ? 'selected' : '' ?>><?= htmlspecialchars($cst['cost_name']) ?> (<?= cost_amount_fmt($cst) ?>)</option>
				<?php endforeach; ?>
			</select>
			<div class="form-text">Biaya yang dipilih dari <a href="<?= base_url('costs') ?>" target="_blank">Master Biaya</a> akan menjadi komponen tambahan biaya untuk sales channel ini.</div>
			<?php if (empty($costs)): ?>
				<div class="form-text text-danger">Belum ada biaya aktif. Tambahkan lewat menu <a href="<?= base_url('costs/create') ?>">Master Data → Master Biaya</a> terlebih dahulu jika diperlukan.</div>
			<?php endif; ?>
		</div>
		<?php if (isset($marketplace)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $marketplace['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($marketplace) ? 'Simpan Perubahan' : 'Tambah Sales Channel' ?></button>
		<a href="<?= base_url('marketplaces') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(function ($) {
	$('#costSelect').select2({
		theme: 'bootstrap-5',
		width: '100%',
		placeholder: 'Pilih biaya tambahan (opsional)...',
		allowClear: true
	});
});
</script>
