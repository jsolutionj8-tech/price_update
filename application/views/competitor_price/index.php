<div class="card card-stat p-3 mb-3">
	<label class="form-label fw-bold">Pilih Produk</label>
	<select id="productSelect" class="form-select">
		<option value="">-- Pilih produk --</option>
		<?php foreach ($products as $p): ?>
			<option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['product_code'] . ' - ' . $p['product_name']) ?></option>
		<?php endforeach; ?>
	</select>
</div>

<form method="post" action="<?= base_url('competitor-price/save') ?>" class="card card-stat p-3" id="compForm" style="display:none;">
	<input type="hidden" name="product_id" id="productIdInput">
	<div class="row g-3">
		<?php foreach ($competitors as $c): ?>
		<div class="col-md-4">
			<label class="form-label"><?= htmlspecialchars($c['competitor_name']) ?></label>
			<input type="number" step="0.01" name="price_<?= $c['competitor_code'] ?>" class="form-control" placeholder="Harga saat ini">
		</div>
		<?php endforeach; ?>
		<div class="col-md-4">
			<label class="form-label">Tanggal Pantau</label>
			<input type="date" name="captured_date" class="form-control" value="<?= date('Y-m-d') ?>">
		</div>
	</div>
	<div class="mt-3"><button class="btn btn-primary">Simpan Harga Kompetitor</button></div>
</form>

<script>
document.getElementById('productSelect').addEventListener('change', function () {
	document.getElementById('productIdInput').value = this.value;
	document.getElementById('compForm').style.display = this.value ? '' : 'none';
});
</script>
