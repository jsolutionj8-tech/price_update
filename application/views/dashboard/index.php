<div class="row row-cards mb-3">
	<div class="col-6 col-md-3">
		<div class="card">
			<div class="card-body d-flex align-items-center gap-3">
				<span class="stat-icon tone-accent"><i class="bi bi-box-seam"></i></span>
				<div>
					<div class="subheader">Total Produk Aktif</div>
					<div class="h1 mb-0"><?= number_format($total_products, 0, ',', '.') ?></div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card">
			<div class="card-body d-flex align-items-center gap-3">
				<span class="stat-icon tone-accent"><i class="bi bi-currency-exchange"></i></span>
				<div>
					<div class="subheader">Update Minggu Ini</div>
					<div class="h1 mb-0"><?= number_format($updates_this_week, 0, ',', '.') ?></div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card">
			<div class="card-body d-flex align-items-center gap-3">
				<span class="stat-icon tone-good"><i class="bi bi-envelope-check"></i></span>
				<div>
					<div class="subheader">Email Terkirim (Bulan Ini)</div>
					<div class="h1 mb-0"><?= number_format($emails_sent, 0, ',', '.') ?></div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card">
			<div class="card-body d-flex align-items-center gap-3">
				<span class="stat-icon tone-bad"><i class="bi bi-envelope-exclamation"></i></span>
				<div>
					<div class="subheader">Email Gagal (Bulan Ini)</div>
					<div class="h1 mb-0"><?= number_format($emails_failed, 0, ',', '.') ?></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row row-cards">
	<div class="col-md-5">
		<div class="card">
			<div class="card-body">
				<h3 class="card-title">Tren Perubahan Harga (6 Bulan Terakhir)</h3>
				<canvas id="trendChart" height="220"></canvas>
			</div>
		</div>
	</div>
	<div class="col-md-7">
		<div class="card">
			<div class="card-body">
				<h3 class="card-title">Perubahan Harga Terbaru</h3>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Produk</th><th>Tanggal Efektif</th><th>Status</th><th></th></tr></thead>
					<tbody>
					<?php foreach ($recent_batches as $b): ?>
						<tr>
							<td><?= htmlspecialchars($b['product_name']) ?></td>
							<td><?= tgl_indo($b['effective_date']) ?></td>
							<td><?= status_badge($b['notify_status']) ?></td>
							<td><a href="<?= base_url('price-history/detail/' . $b['id']) ?>" class="btn btn-sm btn-outline-secondary">Detail</a></td>
						</tr>
					<?php endforeach; ?>
					<?php if (empty($recent_batches)): ?>
						<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada perubahan harga.</td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const trendData = <?= json_encode($trend) ?>;
new Chart(document.getElementById('trendChart'), {
	type: 'line',
	data: {
		labels: trendData.map(d => d.ym),
		datasets: [{ label: 'Jumlah Update Harga', data: trendData.map(d => d.total), borderColor: '#2E74B5', backgroundColor: 'rgba(46,116,181,.15)', fill: true, tension: .3 }]
	},
	options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
