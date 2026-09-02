<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-6">
			<input type="text" name="keyword" class="form-control" placeholder="Cari nama vendor..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-6 col-md-3"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search me-1"></i>Cari</button></div>
		<div class="col-6 col-md-3">
			<a href="<?= base_url('vendors/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Tambah Vendor</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>No</th><th>Nama Vendor</th><th>Kategori</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php $no = $pagination['total'] > 0 ? (($pagination['page'] - 1) * $pagination['per_page']) + 1 : 1; ?>
			<?php foreach ($vendors as $v): ?>
				<tr>
					<td><?= $no++ ?></td>
					<td><?= htmlspecialchars($v['vendor_name'] ?: '-') ?></td>
					<td><?= htmlspecialchars($v['vendor_category'] ?? '-') ?></td>
					<td><?= status_badge($v['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('vendors/edit/' . $v['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('vendors/delete/' . $v['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus vendor ini? Tindakan ini tidak dapat dibatalkan.')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($vendors)): ?>
				<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada vendor ditemukan.</td></tr>
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
			Menampilkan <?= $start ?>–<?= $end ?> dari <?= number_format($pagination['total'], 0, ',', '.') ?> vendor
		</div>
		<nav aria-label="Navigasi halaman vendor">
			<ul class="pagination pagination-sm mb-0 flex-wrap">
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('vendors') . '?' . http_build_query(array_merge($qs_base, array('page' => 1))) ?>">&laquo;&laquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('vendors') . '?' . http_build_query(array_merge($qs_base, array('page' => $prev_page))) ?>">&laquo;</a>
				</li>
				<li class="page-item disabled"><span class="page-link">Halaman <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></span></li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('vendors') . '?' . http_build_query(array_merge($qs_base, array('page' => $next_page))) ?>">&raquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('vendors') . '?' . http_build_query(array_merge($qs_base, array('page' => $pagination['total_pages']))) ?>">&raquo;&raquo;</a>
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
