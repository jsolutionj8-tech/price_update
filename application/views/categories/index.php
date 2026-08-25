<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-6">
			<input type="text" name="keyword" class="form-control" placeholder="Cari nama kategori..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-6 col-md-3"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
		<div class="col-6 col-md-3">
			<a href="<?= base_url('categories/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Kategori</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>ID</th><th>Nama Kategori</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($categories as $c): ?>
				<tr>
					<td><?= (int) $c['id'] ?></td>
					<td><?= htmlspecialchars($c['category_name']) ?></td>
					<td><?= status_badge($c['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('categories/edit/' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<?php if ($c['is_active']): ?>
							<a href="<?= base_url('categories/delete/' . $c['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan kategori ini?')"><i class="bi bi-trash"></i></a>
						<?php else: ?>
							<a href="<?= base_url('categories/activate/' . $c['id']) ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Aktifkan kembali kategori ini?')"><i class="bi bi-arrow-counterclockwise"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($categories)): ?>
				<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada kategori ditemukan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
