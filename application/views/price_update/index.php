<div class="card card-stat p-3 mb-3">
	<p class="text-muted mb-3">Pilih produk yang akan diperbarui harganya. Sistem akan menampilkan form input Modal, Target HPP, dan harga per kanal penjualan untuk tiap vendor.</p>
	<form method="get" class="row g-2">
		<div class="col-md-8">
			<input type="text" name="keyword" class="form-control" placeholder="Cari produk berdasarkan kode / nama..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-md-4">
			<button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button>
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
					<td class="text-end"><a href="<?= base_url('price-update/form/' . $p['id']) ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i> Update Harga</a></td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($products)): ?>
				<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada produk ditemukan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php
		$qs_base = array();
		if (!empty($filters['keyword'])) $qs_base['keyword'] = $filters['keyword'];

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
					<a class="page-link" href="<?= base_url('price-update') . '?' . http_build_query(array_merge($qs_base, array('page' => 1))) ?>">&laquo;&laquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('price-update') . '?' . http_build_query(array_merge($qs_base, array('page' => $prev_page))) ?>">&laquo;</a>
				</li>
				<li class="page-item disabled"><span class="page-link">Halaman <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></span></li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('price-update') . '?' . http_build_query(array_merge($qs_base, array('page' => $next_page))) ?>">&raquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('price-update') . '?' . http_build_query(array_merge($qs_base, array('page' => $pagination['total_pages']))) ?>">&raquo;&raquo;</a>
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
