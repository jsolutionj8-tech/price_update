<div class="alert alert-info d-flex align-items-start gap-2">
	<i class="bi bi-info-circle-fill mt-1"></i>
	<div>Sales channel bernama <code>Offline</code> dipakai sebagai acuan utama perhitungan <b>Markup %</b> dan <b>Margin %</b> pada modul Update Harga. Pastikan minimal satu sales channel memakai nama ini.</div>
</div>

<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-6">
			<input type="text" name="keyword" class="form-control" placeholder="Cari nama sales channel..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-6 col-md-3"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search me-1"></i>Cari</button></div>
		<div class="col-6 col-md-3">
			<a href="<?= base_url('marketplaces/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Tambah Sales Channel</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>No</th><th>Nama Sales Channel</th><th>Biaya Tambahan</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php $no = $pagination['total'] > 0 ? (($pagination['page'] - 1) * $pagination['per_page']) + 1 : 1; ?>
			<?php foreach ($marketplaces as $m): ?>
				<tr>
					<td><?= $no++ ?></td>
					<td><?= htmlspecialchars($m['channel_name']) ?></td>
					<td>
						<?php if (!empty($m['costs'])): ?>
							<?php foreach ($m['costs'] as $cst): ?>
								<span class="badge bg-light text-dark border"><?= htmlspecialchars($cst['cost_name']) ?> (<?= cost_amount_fmt($cst) ?>)</span>
							<?php endforeach; ?>
						<?php else: ?>
							<span class="text-muted">-</span>
						<?php endif; ?>
					</td>
					<td><?= status_badge($m['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('marketplaces/edit/' . $m['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<a href="<?= base_url('marketplaces/delete/' . $m['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus sales channel ini? Tindakan ini tidak dapat dibatalkan.')"><i class="bi bi-trash"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($marketplaces)): ?>
				<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada sales channel ditemukan.</td></tr>
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
			Menampilkan <?= $start ?>–<?= $end ?> dari <?= number_format($pagination['total'], 0, ',', '.') ?> sales channel
		</div>
		<nav aria-label="Navigasi halaman sales channel">
			<ul class="pagination pagination-sm mb-0 flex-wrap">
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('marketplaces') . '?' . http_build_query(array_merge($qs_base, array('page' => 1))) ?>">&laquo;&laquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('marketplaces') . '?' . http_build_query(array_merge($qs_base, array('page' => $prev_page))) ?>">&laquo;</a>
				</li>
				<li class="page-item disabled"><span class="page-link">Halaman <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></span></li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('marketplaces') . '?' . http_build_query(array_merge($qs_base, array('page' => $next_page))) ?>">&raquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('marketplaces') . '?' . http_build_query(array_merge($qs_base, array('page' => $pagination['total_pages']))) ?>">&raquo;&raquo;</a>
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
