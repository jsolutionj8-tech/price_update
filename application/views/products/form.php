<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<div class="card card-stat p-4" style="max-width:640px;">
	<form method="post" action="<?= isset($product) ? base_url('products/update/' . $product['id']) : base_url('products/store') ?>">
		<div class="mb-3">
			<label class="form-label">Kode Produk</label>
			<input type="text" name="product_code" class="form-control" required value="<?= htmlspecialchars($product['product_code'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Nama Produk</label>
			<input type="text" name="product_name" class="form-control" required value="<?= htmlspecialchars($product['product_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Brand</label>
			<select name="brand_id" id="brandSelect" class="form-select" style="width:100%" required>
				<?php foreach ($brands as $b): ?>
					<option value="<?= $b['id'] ?>" <?= (isset($product) && $product['brand_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['brand_name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="mb-3">
			<label class="form-label">Kategori Barang</label>
			<select name="category_id" id="categorySelect" class="form-select" style="width:100%">
				<option value="">-- Tanpa Kategori --</option>
				<?php foreach ($categories as $cat): ?>
					<option value="<?= $cat['id'] ?>" <?= (isset($product) && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="mb-3">
			<label class="form-label">Satuan</label>
			<input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($product['unit'] ?? 'pcs') ?>">
		</div>
		<button class="btn btn-primary"><?= isset($product) ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
		<a href="<?= base_url('products') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(function ($) {
	$('#brandSelect, #categorySelect').select2({
		theme: 'bootstrap-5',
		width: '100%'
	});
});
</script>

<?php if (isset($product)): ?>
<div class="card card-stat p-3 mt-3">
	<h6 class="fw-bold">Cost per Vendor</h6>
	<table class="table table-sm">
		<thead><tr><th>Vendor</th><th>Modal</th><th>Margin (Target)</th><th>SRP Suggest</th><th>Markup</th><th>Margin (Aktual)</th></tr></thead>
		<tbody>
		<?php foreach ($costs as $c): ?>
			<tr>
				<td><?= htmlspecialchars($c['vendor_code']) ?></td>
				<td><?= rupiah($c['modal']) ?></td>
				<td><?= percent_fmt($c['target_hpp_pct']) ?></td>
				<td><?= rupiah($c['srp_suggest']) ?></td>
				<td><?= percent_fmt($c['srp_markup_pct']) ?></td>
				<td><?= percent_fmt($c['srp_margin_pct']) ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if (empty($costs)): ?>
			<tr><td colspan="6" class="text-center text-muted">Belum ada data harga. Gunakan menu "Update Harga" untuk mengisi.</td></tr>
		<?php endif; ?>
		</tbody>
	</table>
</div>
<?php endif; ?>
