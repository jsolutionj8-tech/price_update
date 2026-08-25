<div class="alert alert-info d-flex align-items-start gap-2">
	<i class="bi bi-info-circle-fill mt-1"></i>
	<div>Kode marketplace <code>OFFLINE</code> dipakai sebagai acuan utama perhitungan <b>Markup %</b> dan <b>Margin %</b> pada modul Update Harga. Pastikan minimal satu marketplace memakai kode ini.</div>
</div>

<div class="card card-stat p-3 mb-3">
	<form method="get" class="row g-2">
		<div class="col-md-6">
			<input type="text" name="keyword" class="form-control" placeholder="Cari kode / nama marketplace..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
		</div>
		<div class="col-6 col-md-3"><button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Cari</button></div>
		<div class="col-6 col-md-3">
			<a href="<?= base_url('marketplaces/create') ?>" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Marketplace</a>
		</div>
	</form>
</div>

<div class="card card-stat p-3">
	<div class="table-responsive">
		<table class="table align-middle">
			<thead><tr><th>Urutan</th><th>Kode</th><th>Nama Marketplace</th><th>Status</th><th></th></tr></thead>
			<tbody>
			<?php foreach ($marketplaces as $m): ?>
				<tr>
					<td><?= (int) $m['sort_order'] ?></td>
					<td><span class="badge bg-light text-dark border"><?= htmlspecialchars($m['channel_code']) ?></span></td>
					<td><?= htmlspecialchars($m['channel_name']) ?></td>
					<td><?= status_badge($m['is_active'] ? 'active' : 'inactive') ?></td>
					<td class="text-end text-nowrap">
						<a href="<?= base_url('marketplaces/edit/' . $m['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
						<?php if ($m['is_active']): ?>
							<a href="<?= base_url('marketplaces/delete/' . $m['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan marketplace ini?')"><i class="bi bi-trash"></i></a>
						<?php else: ?>
							<a href="<?= base_url('marketplaces/activate/' . $m['id']) ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Aktifkan kembali marketplace ini?')"><i class="bi bi-arrow-counterclockwise"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (empty($marketplaces)): ?>
				<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada marketplace ditemukan.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
