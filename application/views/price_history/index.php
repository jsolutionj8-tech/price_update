<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<?php
	$qs_base = array();
	if (!empty($filters['product_id'])) $qs_base['product_id'] = $filters['product_id'];
	if (!empty($filters['status'])) $qs_base['status'] = $filters['status'];
	if (!empty($filters['date_from'])) $qs_base['date_from'] = $filters['date_from'];
	if (!empty($filters['date_to'])) $qs_base['date_to'] = $filters['date_to'];

	// Tombol Detail hanya aktif untuk ADMIN/EDITOR (halaman detail menampilkan aksi
	// "Kirim Ulang Notifikasi") — VIEWER tetap melihat daftar riwayat ini, hanya
	// tombolnya dinonaktifkan. Lihat guard yang sama di Price_history::detail().
	$can_view_detail = in_array($logged_in_user['role'] ?? '', array('ADMIN', 'EDITOR'), TRUE);
?>

<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-3">
			<select name="product_id" id="productFilterSelect" class="form-select" style="width:100%">
				<option value="">Semua Produk</option>
				<?php if (!empty($selected_product)): ?>
					<option value="<?= $selected_product['id'] ?>" selected><?= htmlspecialchars($selected_product['product_code'] . ' - ' . $selected_product['product_name']) ?></option>
				<?php endif; ?>
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
		<div class="col-md-2"><input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>" placeholder="Dari"></div>
		<div class="col-md-2"><input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>" placeholder="Sampai"></div>
		<div class="col-md-3 d-flex gap-2">
			<button class="btn btn-outline-secondary flex-fill"><i class="bi bi-funnel"></i> Filter</button>
			<a href="<?= base_url('price-history/export') . '?' . http_build_query($qs_base) ?>" class="btn btn-outline-success flex-fill"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
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
					<td><?= status_badge($b['notify_status']) ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(function ($) {
	$('#productFilterSelect').select2({
		theme: 'bootstrap-5',
		width: '100%',
		placeholder: 'Semua Produk',
		allowClear: true,
		minimumInputLength: 2,
		language: {
			inputTooShort: function () { return 'Ketik minimal 2 huruf...'; },
			searching: function () { return 'Mencari...'; },
			noResults: function () { return 'Produk tidak ditemukan.'; }
		},
		ajax: {
			url: "<?= base_url('products/search') ?>",
			dataType: 'json',
			delay: 250,
			data: function (params) { return { q: params.term }; },
			processResults: function (data) {
				return { results: data.map(function (p) { return { id: p.id, text: p.code + ' - ' + p.name }; }) };
			}
		}
	});
});
</script>
