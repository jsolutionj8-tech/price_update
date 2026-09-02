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

<!-- Flatpickr dipakai utk kalender tanggal Dari/Sampai supaya tampilannya seragam persis di
     semua browser (kalender native Safari adalah widget OS, ukurannya tidak bisa diatur CSS
     & beda dgn kalender kustom Chrome). -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">

<div class="card card-stat p-3 mb-3">
	<!-- Filter brand/kategori/kode-nama disamakan dgn menu Master Data -> Produk. -->
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
		<div class="col-md-2">
			<select name="status" class="form-select">
				<option value="">Semua Status</option>
				<?php foreach (array('pending','sent') as $s): ?>
					<option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-2">
			<button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
		</div>

		<div class="col-md-4" style="display:grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap:.5rem;">
			<input type="text" name="date_from" class="form-control flatpickr-date" autocomplete="off" value="<?= htmlspecialchars($filters['date_from'] ?: date('Y-m-d')) ?>" placeholder="Dari">
			<input type="text" name="date_to" class="form-control flatpickr-date" autocomplete="off" value="<?= htmlspecialchars($filters['date_to'] ?: date('Y-m-d')) ?>" placeholder="Sampai">
		</div>
		<div class="col-md-6"></div>
		<div class="col-md-2 d-flex gap-2">
			<a href="<?= base_url('price-history/export') . '?' . http_build_query($qs_base) ?>" class="btn btn-sm btn-outline-success flex-fill"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
			<a href="<?= base_url('price-history/export-pdf') . '?' . http_build_query($qs_base) ?>" class="btn btn-sm btn-outline-danger flex-fill"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<?php if ($can_view_detail): ?>
	<form method="post" action="<?= base_url('price-history/resend-bulk') ?>" id="bulkResendForm">
		<div class="d-flex justify-content-between align-items-center mb-2">
			<div class="form-check">
				<input type="checkbox" class="form-check-input" id="selectAllBatches">
				<label class="form-check-label small" for="selectAllBatches">Pilih Semua</label>
			</div>
			<button type="submit" class="btn btn-sm btn-outline-primary" id="bulkResendBtn" disabled onclick="return confirm('Kirim ulang notifikasi utk item yang dipilih?')"><i class="bi bi-arrow-repeat me-1"></i>Kirim Ulang Notifikasi Terpilih</button>
		</div>
	<?php endif; ?>
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr>
				<?php if ($can_view_detail): ?><th style="width:2rem;"></th><?php endif; ?>
				<th>Tanggal Efektif</th><th>Produk</th><th>Vendor</th><th>Diubah Oleh</th><th>Status Email</th><th></th>
			</tr></thead>
			<tbody>
			<?php foreach ($batches as $b): ?>
				<tr>
					<?php if ($can_view_detail): ?>
						<td><input type="checkbox" class="form-check-input batch-checkbox" name="batch_ids[]" value="<?= $b['id'] ?>"></td>
					<?php endif; ?>
					<td><?= tgl_indo($b['effective_date']) ?></td>
					<td><?= htmlspecialchars($b['product_name']) ?> <small class="text-muted d-block"><?= htmlspecialchars($b['product_code']) ?></small></td>
					<td><?= htmlspecialchars($b['vendor_code']) ?></td>
					<td><?= htmlspecialchars($b['changed_by_name']) ?></td>
					<td class="notify-status-cell"><?= status_badge($b['notify_status']) ?></td>
					<td class="d-flex gap-1">
						<?php if ($can_view_detail): ?>
							<a href="<?= base_url('price-history/detail/' . $b['id']) ?>" class="btn btn-sm btn-outline-secondary">Detail</a>
							<a href="<?= base_url('price-update/form/' . $b['product_id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit Update Harga produk ini"><i class="bi bi-pencil-square"></i></a>
						<?php else: ?>
							<button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Hanya ADMIN/EDITOR yang bisa melihat detail">Detail</button>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($batches)): ?>
				<tr><td colspan="<?= $can_view_detail ? 7 : 6 ?>" class="text-center text-muted py-3">Belum ada riwayat perubahan harga.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php if ($can_view_detail): ?>
	</form>
	<?php endif; ?>

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

<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	flatpickr('.flatpickr-date', {
		dateFormat: 'Y-m-d',
		altInput: true,
		altFormat: 'd-m-Y',
		allowInput: true
	});

	// Centang "Pilih Semua" & aktifkan tombol "Kirim Ulang Notifikasi Terpilih" hanya
	// kalau minimal satu baris dicentang.
	const selectAll = document.getElementById('selectAllBatches');
	const checkboxes = Array.from(document.querySelectorAll('.batch-checkbox'));
	const bulkBtn = document.getElementById('bulkResendBtn');
	if (selectAll && bulkBtn) {
		function updateBulkBtn() {
			const anyChecked = checkboxes.some(cb => cb.checked);
			bulkBtn.disabled = !anyChecked;
			selectAll.checked = checkboxes.length > 0 && checkboxes.every(cb => cb.checked);
		}
		selectAll.addEventListener('change', function () {
			checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
			updateBulkBtn();
		});
		checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBtn));
		updateBulkBtn();
	}
});
</script>
