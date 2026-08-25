<?php if (!isset($price_row)): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<?php endif; ?>

<div class="card card-stat p-4" style="max-width:560px;">
	<form method="post" action="<?= isset($price_row) ? base_url('competitor-price/update/' . $price_row['id']) : base_url('competitor-price/store') ?>">
		<?php if (isset($price_row)): ?>
			<div class="mb-3">
				<label class="form-label">Produk</label>
				<input type="text" class="form-control" value="<?= htmlspecialchars($price_row['product_name'] . ' (' . $price_row['product_code'] . ')') ?>" disabled>
			</div>
		<?php else: ?>
			<div class="mb-3">
				<label class="form-label">Produk</label>
				<select name="product_id" id="productSelect" class="form-select" style="width:100%" required>
					<option></option>
				</select>
				<div class="form-text">Ketik minimal 2 huruf untuk mencari produk (kode atau nama).</div>
			</div>
		<?php endif; ?>

		<div class="mb-3">
			<label class="form-label">Kompetitor</label>
			<select name="competitor_id" class="form-select" required>
				<option value="">-- Pilih Kompetitor --</option>
				<?php foreach ($competitors as $c): ?>
					<option value="<?= $c['id'] ?>" <?= (isset($price_row) && $price_row['competitor_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['competitor_name']) ?></option>
				<?php endforeach; ?>
			</select>
			<?php if (empty($competitors)): ?>
				<div class="form-text text-danger">Belum ada kompetitor aktif. Tambahkan lewat menu <a href="<?= base_url('competitors/create') ?>">Master Data → Kompetitor</a> terlebih dahulu.</div>
			<?php endif; ?>
		</div>

		<div class="mb-3">
			<label class="form-label">Harga</label>
			<input type="number" step="0.01" min="0" name="price" class="form-control" required value="<?= $price_row['price'] ?? '' ?>">
		</div>

		<div class="mb-3">
			<label class="form-label">Tanggal Pantau</label>
			<input type="date" name="captured_date" class="form-control" required value="<?= $price_row['captured_date'] ?? date('Y-m-d') ?>">
		</div>

		<button class="btn btn-primary" <?= empty($competitors) ? 'disabled' : '' ?>><?= isset($price_row) ? 'Simpan Perubahan' : 'Tambah Harga Kompetitor' ?></button>
		<a href="<?= base_url('competitor-price') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>

<?php if (!isset($price_row)): ?>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(function ($) {
	$('#productSelect').select2({
		theme: 'bootstrap-5',
		placeholder: 'Cari kode / nama produk...',
		allowClear: true,
		minimumInputLength: 2,
		width: '100%',
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
				return {
					results: data.map(function (p) { return { id: p.id, text: p.code + ' - ' + p.name }; })
				};
			}
		}
	});
});
</script>
<?php endif; ?>
