<div class="card card-stat p-4" style="max-width:560px;">
	<h6 class="fw-bold">Import Data Produk dari Excel/CSV</h6>
	<p class="text-muted small">Format kolom: <code>product_code, product_name, vendor_code, modal, target_hpp_pct</code>. Baris pertama dianggap header.</p>
	<form method="post" action="<?= base_url('reports/do_import') ?>" enctype="multipart/form-data">
		<div class="mb-3">
			<input type="file" name="import_file" class="form-control" accept=".xlsx,.xls,.csv" required>
		</div>
		<button class="btn btn-primary"><i class="bi bi-upload"></i> Import</button>
	</form>
	<hr>
	<a href="<?= base_url('reports/export') ?>" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export Laporan Riwayat Harga (Excel)</a>
</div>
