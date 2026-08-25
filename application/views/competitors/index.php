<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-6">
			<input type="text" name="keyword" class="form-control" placeholder="Cari kode / nama kompetitor..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-6 col-md-3"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
		<div class="col-6 col-md-3">
			<a href="<?= base_url('competitors/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Kompetitor</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Kode</th><th>Nama Kompetitor</th><th>Website</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($competitors as $c): ?>
				<tr>
					<td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['competitor_code']) ?></span></td>
					<td><?= htmlspecialchars($c['competitor_name']) ?></td>
					<td>
						<?php if (!empty($c['website_url'])): ?>
							<a href="<?= htmlspecialchars($c['website_url']) ?>" target="_blank" rel="noopener">Kunjungi <i class="bi bi-box-arrow-up-right small"></i></a>
						<?php else: ?>
							<span class="text-muted">-</span>
						<?php endif; ?>
					</td>
					<td><?= status_badge($c['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('competitors/edit/' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<?php if ($c['is_active']): ?>
							<a href="<?= base_url('competitors/delete/' . $c['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan kompetitor ini?')"><i class="bi bi-trash"></i></a>
						<?php else: ?>
							<a href="<?= base_url('competitors/activate/' . $c['id']) ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Aktifkan kembali kompetitor ini?')"><i class="bi bi-arrow-counterclockwise"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($competitors)): ?>
				<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada kompetitor ditemukan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
