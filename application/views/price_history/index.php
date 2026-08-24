<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-3">
			<select name="product_id" class="form-select">
				<option value="">Semua Produk</option>
				<?php foreach ($products as $p): ?>
					<option value="<?= $p['id'] ?>" <?= ($filters['product_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['product_name']) ?></option>
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
		<div class="col-md-2"><input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>" placeholder="Dari"></div>
		<div class="col-md-2"><input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>" placeholder="Sampai"></div>
		<div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Filter</button></div>
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
					<td><a href="<?= base_url('price-history/detail/' . $b['id']) ?>" class="btn btn-sm btn-outline-secondary">Detail</a></td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($batches)): ?>
				<tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat perubahan harga.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php if ($total_pages > 1): ?>
	<nav><ul class="pagination pagination-sm mb-0">
		<?php for ($p = 1; $p <= $total_pages; $p++): ?>
			<li class="page-item <?= $p == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a></li>
		<?php endfor; ?>
	</ul></nav>
	<?php endif; ?>
</div>
