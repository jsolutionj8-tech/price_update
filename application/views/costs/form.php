<div class="card card-stat p-4" style="max-width:480px;">
	<form method="post" action="<?= isset($cost) ? base_url('costs/update/' . $cost['id']) : base_url('costs/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Biaya</label>
			<input type="text" name="cost_name" class="form-control" required autofocus placeholder="mis. Biaya Admin, Biaya Packing" value="<?= htmlspecialchars($cost['cost_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Amount (Rp)</label>
			<input type="number" step="1" min="0" name="amount" class="form-control" required value="<?= $cost['amount'] ?? 0 ?>">
		</div>
		<?php if (isset($cost)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $cost['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($cost) ? 'Simpan Perubahan' : 'Tambah Biaya' ?></button>
		<a href="<?= base_url('costs') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
