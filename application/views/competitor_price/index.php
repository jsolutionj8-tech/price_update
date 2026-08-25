<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-6">
			<input type="text" name="keyword" class="form-control" placeholder="Cari kode / nama produk / nama kompetitor..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-6 col-md-3"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
		<div class="col-6 col-md-3">
			<a href="<?= base_url('competitor-price/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Harga Kompetitor</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Produk</th><th>Kompetitor</th><th>Harga</th><th>Tanggal Pantau</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($prices as $p): ?>
				<tr>
					<td>
						<?= htmlspecialchars($p['product_name']) ?>
						<div class="text-muted small"><?= htmlspecialchars($p['product_code']) ?></div>
					</td>
					<td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['competitor_code']) ?></span> <?= htmlspecialchars($p['competitor_name']) ?></td>
					<td class="fw-semibold"><?= rupiah($p['price']) ?></td>
					<td><?= tgl_indo($p['captured_date']) ?></td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('competitor-price/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('competitor-price/delete/' . $p['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data harga kompetitor ini?')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($prices)): ?>
				<tr><td colspan="5" class="text-center text-muted py-3">Belum ada data harga kompetitor.</td></tr>
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
			Menampilkan <?= $start ?>–<?= $end ?> dari <?= number_format($pagination['total'], 0, ',', '.') ?> data
		</div>
		<nav aria-label="Navigasi halaman harga kompetitor">
			<ul class="pagination pagination-sm mb-0 flex-wrap">
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('competitor-price') . '?' . http_build_query(array_merge($qs_base, array('page' => 1))) ?>">&laquo;&laquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('competitor-price') . '?' . http_build_query(array_merge($qs_base, array('page' => $prev_page))) ?>">&laquo;</a>
				</li>
				<li class="page-item disabled"><span class="page-link">Halaman <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></span></li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('competitor-price') . '?' . http_build_query(array_merge($qs_base, array('page' => $next_page))) ?>">&raquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('competitor-price') . '?' . http_build_query(array_merge($qs_base, array('page' => $pagination['total_pages']))) ?>">&raquo;&raquo;</a>
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
