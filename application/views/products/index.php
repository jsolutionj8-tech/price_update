<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-4">
			<input type="text" name="keyword" class="form-control" placeholder="Cari kode / nama produk..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-md-2">
			<select name="brand_id" class="form-select">
				<option value="">Semua Brand</option>
				<?php foreach ($brands as $b): ?>
					<option value="<?= $b['id'] ?>" <?= ($filters['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['brand_name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-2">
			<select name="category_id" class="form-select">
				<option value="">Semua Kategori</option>
				<?php foreach ($categories as $cat): ?>
					<option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-6 col-md-1"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i></button></div>
		<div class="col-6 col-md-3 text-end">
			<a href="<?= base_url('products/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Tambah Produk</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>No</th><th>Kode Produk</th><th>Nama Produk</th><th>Brand</th><th>Kategori</th><th></th></tr></thead>
			<tbody>
			<?php $no = (($pagination['page'] - 1) * $pagination['per_page']) + 1; ?>
			<?php foreach ($products as $p): ?>
				<tr>
					<td><?= $no++ ?></td>
					<td><?= htmlspecialchars($p['product_code']) ?></td>
					<td><?= htmlspecialchars($p['product_name']) ?></td>
					<td><?= htmlspecialchars($p['brand_name'] ?? '-') ?></td>
					<td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
					<td class="text-end">
						<a href="<?= base_url('price-update/form/' . $p['id']) ?>" class="btn btn-sm btn-primary"><i class="bi bi-currency-exchange me-1"></i>Update Harga</a>
						<a href="<?= base_url('products/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('products/delete/' . $p['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan produk ini?')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($products)): ?>
				<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada produk ditemukan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php
		$qs_base = array();
		if (!empty($filters['keyword'])) $qs_base['keyword'] = $filters['keyword'];
		if (!empty($filters['brand_id'])) $qs_base['brand_id'] = $filters['brand_id'];
		if (!empty($filters['category_id'])) $qs_base['category_id'] = $filters['category_id'];

		$start = $pagination['total'] > 0 ? (($pagination['page'] - 1) * $pagination['per_page']) + 1 : 0;
		$end   = min($pagination['page'] * $pagination['per_page'], $pagination['total']);
		$prev_page = max(1, $pagination['page'] - 1);
		$next_page = min($pagination['total_pages'], $pagination['page'] + 1);
	?>
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
		<div class="text-muted small">
			Menampilkan <?= $start ?>–<?= $end ?> dari <?= number_format($pagination['total'], 0, ',', '.') ?> produk
		</div>
		<nav aria-label="Navigasi halaman produk">
			<ul class="pagination pagination-sm mb-0 flex-wrap">
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('products') . '?' . http_build_query(array_merge($qs_base, array('page' => 1))) ?>">&laquo;&laquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('products') . '?' . http_build_query(array_merge($qs_base, array('page' => $prev_page))) ?>">&laquo;</a>
				</li>
				<li class="page-item disabled"><span class="page-link">Halaman <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></span></li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('products') . '?' . http_build_query(array_merge($qs_base, array('page' => $next_page))) ?>">&raquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('products') . '?' . http_build_query(array_merge($qs_base, array('page' => $pagination['total_pages']))) ?>">&raquo;&raquo;</a>
				</li>
			</ul>
		</nav>
		<form method="get" class="d-flex align-items-center gap-1">
			<?php foreach ($qs_base as $k => $v): ?><input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>"><?php endforeach; ?>
			<label class="small text-muted mb-0">Ke halaman</label>
			<input type="number" name="page" min="1" max="<?= $pagination['total_pages'] ?>" value="<?= $pagination['page'] ?>" class="form-control form-control-sm" style="width:80px;">
			<button class="btn btn-sm btn-outline-secondary">Go</button>
		</form>
	</div>
</div>
