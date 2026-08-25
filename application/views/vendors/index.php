<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-6">
			<input type="text" name="keyword" class="form-control" placeholder="Cari kode / nama vendor..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-6 col-md-3"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
		<div class="col-6 col-md-3">
			<a href="<?= base_url('vendors/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Vendor</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Kode</th><th>Nama Vendor</th><th>Kontak</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($vendors as $v): ?>
				<tr>
					<td><span class="badge bg-light text-dark border"><?= htmlspecialchars($v['vendor_code']) ?></span></td>
					<td><?= htmlspecialchars($v['vendor_name'] ?: '-') ?></td>
					<td><?= htmlspecialchars($v['contact_info'] ?: '-') ?></td>
					<td><?= status_badge($v['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('vendors/edit/' . $v['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<?php if ($v['is_active']): ?>
							<a href="<?= base_url('vendors/delete/' . $v['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan vendor ini?')"><i class="bi bi-trash"></i></a>
						<?php else: ?>
							<a href="<?= base_url('vendors/activate/' . $v['id']) ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Aktifkan kembali vendor ini?')"><i class="bi bi-arrow-counterclockwise"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($vendors)): ?>
				<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada vendor ditemukan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
