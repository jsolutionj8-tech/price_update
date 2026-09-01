<?php
	$qs_base = array();
	if (!empty($filters['keyword'])) $qs_base['keyword'] = $filters['keyword'];
	if (!empty($filters['brand_id'])) $qs_base['brand_id'] = $filters['brand_id'];
	if (!empty($filters['category_id'])) $qs_base['category_id'] = $filters['category_id'];
	if (!empty($filters['status'])) $qs_base['status'] = $filters['status'];
	if (!empty($filters['date_from'])) $qs_base['date_from'] = $filters['date_from'];
	if (!empty($filters['date_to'])) $qs_base['date_to'] = $filters['date_to'];

	// Tombol Detail hanya aktif untuk ADMIN/EDITOR (halaman detail menampilkan aksi
	// "Kirim Ulang Notifikasi") — VIEWER tetap melihat daftar riwayat ini, hanya
	// tombolnya dinonaktifkan. Lihat guard yang sama di Price_history::detail().
	$can_view_detail = in_array($logged_in_user['role'] ?? '', array('ADMIN', 'EDITOR'), TRUE);
?>

<div class="card card-stat p-3 mb-3">
	<!-- Filter brand/kategori/kode-nama disamakan dgn menu Master Data -> Produk. -->
	<form method="get" class="row g-2">
		<div class="col-md-3">
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
		<div class="col-md-2">
			<select name="status" class="form-select">
				<option value="">Semua Status</option>
				<?php foreach (array('pending','processing','sent','partial','failed') as $s): ?>
					<option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-3">
			<button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
		</div>
		<div class="col-md-3"><input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>" placeholder="Dari"></div>
		<div class="col-md-3"><input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>" placeholder="Sampai"></div>
		<div class="col-md-6 d-flex gap-2 justify-content-end">
			<a href="<?= base_url('price-history/export') . '?' . http_build_query($qs_base) ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
			<a href="<?= base_url('price-history/export-pdf') . '?' . http_build_query($qs_base) ?>" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Tanggal Efektif</th><th>Produk</th><th>Vendor</th><th>Diubah Oleh</th><th>Status Email</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($batches as $b): ?>
				<tr>
					<td><?= tgl_indo($b['effective_date']) ?></td>
					<td><?= htmlspecialchars($b['product_name']) ?> <small class="text-muted d-block"><?= htmlspecialchars($b['product_code']) ?></small></td>
					<td><?= htmlspecialchars($b['vendor_code']) ?></td>
					<td><?= htmlspecialchars($b['changed_by_name']) ?></td>
					<td class="notify-status-cell"><?= status_badge($b['notify_status']) ?></td>
					<td>
						<?php if ($can_view_detail): ?>
							<a href="<?= base_url('price-history/detail/' . $b['id']) ?>" class="btn btn-sm btn-outline-secondary">Detail</a>
						<?php else: ?>
							<button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Hanya ADMIN/EDITOR yang bisa melihat detail">Detail</button>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($batches)): ?>
				<tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat perubahan harga.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php
		$start = $pagination['total'] > 0 ? (($pagination['page'] - 1) * $pagination['per_page']) + 1 : 0;
		$end   = min($pagination['page'] * $pagination['per_page'], $pagination['total']);
		$prev_page = max(1, $pagination['page'] - 1);
		$next_page = min($pagination['total_pages'], $pagination['page'] + 1);
	?>
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
		<div class="text-muted small">
			Menampilkan <?= $start ?>–<?= $end ?> dari <?= number_format($pagination['total'], 0, ',', '.') ?> riwayat
		</div>
		<nav aria-label="Navigasi halaman riwayat">
			<ul class="pagination pagination-sm mb-0 flex-wrap">
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('price-history') . '?' . http_build_query(array_merge($qs_base, array('page' => 1))) ?>">&laquo;&laquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('price-history') . '?' . http_build_query(array_merge($qs_base, array('page' => $prev_page))) ?>">&laquo;</a>
				</li>
				<li class="page-item disabled"><span class="page-link">Halaman <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></span></li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('price-history') . '?' . http_build_query(array_merge($qs_base, array('page' => $next_page))) ?>">&raquo;</a>
				</li>
				<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
					<a class="page-link" href="<?= base_url('price-history') . '?' . http_build_query(array_merge($qs_base, array('page' => $pagination['total_pages']))) ?>">&raquo;&raquo;</a>
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
