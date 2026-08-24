<div class="row g-3 mb-4">
	<div class="col-md-3">
		<div class="card card-stat p-3">
			<small class="text-muted">Total Produk Aktif</small>
			<h3 class="fw-bold mb-0"><?= $total_products ?></h3>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card card-stat p-3">
			<small class="text-muted">Update Minggu Ini</small>
			<h3 class="fw-bold mb-0"><?= $updates_this_week ?></h3>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card card-stat p-3">
			<small class="text-muted">Email Terkirim (Bulan Ini)</small>
			<h3 class="fw-bold mb-0 text-success"><?= $emails_sent ?></h3>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card card-stat p-3">
			<small class="text-muted">Email Gagal (Bulan Ini)</small>
			<h3 class="fw-bold mb-0 text-danger"><?= $emails_failed ?></h3>
		</div>
	</div>
</div>

<div class="row g-3">
	<div class="col-md-5">
		<div class="card card-stat p-3">
			<h6 class="fw-bold mb-3">Tren Perubahan Harga (6 Bulan Terakhir)</h6>
			<canvas id="trendChart" height="220"></canvas>
		</div>
	</div>
	<div class="col-md-7">
		<div class="card card-stat p-3">
			<h6 class="fw-bold mb-3">Perubahan Harga Terbaru</h6>
			<div class="table-responsive">
				<table class="table table-sm align-middle">
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
						<tr><td colspan="4" class="text-center text-muted py-3">Belum ada perubahan harga.</td></tr>
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
