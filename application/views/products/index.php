<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-4">
			<input type="text" name="keyword" class="form-control" placeholder="Cari kode / nama produk..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-md-3">
			<select name="brand_id" class="form-select">
				<option value="">Semua Brand</option>
				<?php foreach ($brands as $b): ?>
					<option value="<?= $b['id'] ?>" <?= ($filters['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['brand_name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
		<div class="col-md-3 text-end">
			<a href="<?= base_url('products/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Produk</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Kode Produk</th><th>Nama Produk</th><th>Brand</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($products as $p): ?>
				<tr>
					<td><?= htmlspecialchars($p['product_code']) ?></td>
					<td><?= htmlspecialchars($p['product_name']) ?></td>
					<td><?= htmlspecialchars($p['brand_name'] ?? '-') ?></td>
					<td class="text-end">
						<a href="<?= base_url('price-update/form/' . $p['id']) ?>" class="btn btn-sm btn-primary"><i class="bi bi-currency-exchange"></i> Update Harga</a>
						<a href="<?= base_url('products/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('products/delete/' . $p['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan produk ini?')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($products)): ?>
				<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada produk ditemukan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
