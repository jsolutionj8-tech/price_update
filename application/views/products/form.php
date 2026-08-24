<div class="card card-stat p-4" style="max-width:640px;">
	<form method="post" action="<?= isset($product) ? base_url('products/update/' . $product['id']) : base_url('products/store') ?>">
		<?php if (!isset($product)): ?>
		<div class="mb-3">
			<label class="form-label">Kode Produk</label>
			<input type="text" name="product_code" class="form-control" required>
		</div>
		<?php else: ?>
			<div class="mb-3"><label class="form-label">Kode Produk</label><input type="text" class="form-control" value="<?= htmlspecialchars($product['product_code']) ?>" disabled></div>
		<?php endif; ?>
		<div class="mb-3">
			<label class="form-label">Nama Produk</label>
			<input type="text" name="product_name" class="form-control" required value="<?= htmlspecialchars($product['product_name'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Brand</label>
			<select name="brand_id" class="form-select" required>
				<?php foreach ($brands as $b): ?>
					<option value="<?= $b['id'] ?>" <?= (isset($product) && $product['brand_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['brand_name']) ?></option>
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

<?php if (isset($product)): ?>
<div class="card card-stat p-3 mt-3">
	<h6 class="fw-bold">Cost per Vendor</h6>
	<table class="table table-sm">
		<thead><tr><th>Vendor</th><th>Modal</th><th>Target HPP</th><th>SRP Suggest</th><th>Markup</th><th>Margin</th></tr></thead>
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
