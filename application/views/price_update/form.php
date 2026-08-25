<div class="card card-stat p-3 mb-3">
	<div class="d-flex justify-content-between align-items-start">
		<div>
			<h5 class="fw-bold mb-0"><?= htmlspecialchars($product['product_name']) ?></h5>
			<small class="text-muted">Kode: <?= htmlspecialchars($product['product_code']) ?> &nbsp;|&nbsp; Brand: <?= htmlspecialchars($product['brand_name'] ?? '-') ?></small>
		</div>
		<a href="<?= base_url('price-update') ?>" class="btn btn-sm btn-outline-secondary">Kembali</a>
	</div>
</div>

<ul class="nav nav-tabs mb-3">
	<?php $i = 0; foreach ($vendor_costs as $vc): $i++; ?>
	<li class="nav-item"><button class="nav-link <?= $i === 1 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#vendor<?= $vc['vendor_id'] ?>"><?= htmlspecialchars($vc['vendor_code']) ?></button></li>
	<?php endforeach; ?>
</ul>
<?php if (empty($vendor_costs)): ?>
	<div class="alert alert-warning">Belum ada data vendor untuk produk ini. Tambahkan vendor terlebih dahulu di bawah ini.</div>
<?php endif; ?>

<?php if (!empty($available_vendors)): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<div class="card card-stat p-3 mb-3">
	<form method="post" action="<?= base_url('price-update/add-vendor/' . $product['id']) ?>" class="d-flex align-items-end gap-2">
		<div style="min-width:280px;">
			<label class="form-label mb-1">Tambah Vendor</label>
			<select name="vendor_id" id="vendorSelect" class="form-select" style="width:100%" required>
				<option value="">-- Pilih Vendor --</option>
				<?php foreach ($available_vendors as $v): ?>
					<option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['vendor_code'] . ' - ' . $v['vendor_name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<button type="submit" class="btn btn-outline-primary"><i class="bi bi-plus-lg"></i> Tambah</button>
	</form>
</div>
<?php endif; ?>

<div class="tab-content">
<?php $i = 0; foreach ($vendor_costs as $vc): $i++; ?>
	<div class="tab-pane fade <?= $i === 1 ? 'show active' : '' ?>" id="vendor<?= $vc['vendor_id'] ?>">
	<form class="price-form" method="post" action="<?= base_url('price-update/save') ?>">
		<input type="hidden" name="product_id" value="<?= $product['id'] ?>">
		<input type="hidden" name="vendor_id" value="<?= $vc['vendor_id'] ?>">

		<div class="row g-3">
			<div class="col-md-6">
				<div class="card card-stat p-3 h-100">
					<h6 class="fw-bold">Modal & HPP</h6>
					<div class="mb-2">
						<label class="form-label">Modal (Rp)</label>
						<input type="number" step="0.01" name="modal" class="form-control input-calc" value="<?= $vc['modal'] ?>" required>
					</div>
					<div class="mb-2">
						<label class="form-label">Target HPP (%)</label>
						<input type="number" step="0.01" name="target_hpp_pct" class="form-control input-calc" value="<?= $vc['target_hpp_pct'] ?>" required>
					</div>
					<div class="row mt-2">
						<div class="col-4"><small class="text-muted">SRP Suggest</small><div class="fw-bold out-srp">Rp 0</div></div>
						<div class="col-4"><small class="text-muted">Markup %</small><div class="fw-bold out-markup">0%</div></div>
						<div class="col-4"><small class="text-muted">Margin %</small><div class="fw-bold out-margin">0%</div></div>
					</div>
					<small class="text-muted d-block mt-2"><i class="bi bi-info-circle"></i> Markup % &amp; Margin % dihitung dari harga kanal <b>Offline</b> terhadap Modal (sesuai format spreadsheet acuan). Jika Offline belum diisi, dihitung sementara dari SRP Suggest.</small>
				</div>
			</div>

			<div class="col-md-6">
				<div class="card card-stat p-3 h-100">
					<h6 class="fw-bold">Harga Baru per Kanal</h6>
					<?php foreach ($channels as $ch): ?>
					<div class="mb-2">
						<label class="form-label"><?= htmlspecialchars($ch['channel_name']) ?></label>
						<input type="number" step="0.01" name="price_<?= $ch['channel_code'] ?>" class="form-control"
							value="<?= $vc['current_prices'][$ch['channel_code']] ?? '' ?>">
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="card card-stat p-3 mt-3">
			<h6 class="fw-bold">Harga Kompetitor (Referensi)</h6>
			<div class="row">
			<?php foreach ($competitors as $c): ?>
				<div class="col-md-4"><small class="text-muted"><?= htmlspecialchars($c['competitor_name']) ?></small>
					<div class="fw-semibold"><?= rupiah($competitor_prices[$c['competitor_code']] ?? 0) ?></div>
				</div>
			<?php endforeach; ?>
			</div>
			<a href="<?= base_url('competitor-price') ?>" class="small">Perbarui harga kompetitor &raquo;</a>
		</div>

		<div class="row g-3 mt-1">
			<div class="col-md-4">
				<label class="form-label">Tanggal Efektif</label>
				<input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
			</div>
			<div class="col-md-8">
				<label class="form-label">Catatan (opsional)</label>
				<input type="text" name="notes" class="form-control" placeholder="Alasan/keterangan perubahan harga...">
			</div>
		</div>

		<div class="mt-3">
			<button type="button" class="btn btn-outline-primary btn-preview"><i class="bi bi-envelope"></i> Preview Email</button>
			<button type="submit" class="btn btn-primary" onclick="return confirm('Simpan perubahan harga ini? Notifikasi belum langsung terkirim &mdash; klik &quot;Kirim Notifikasi Sekarang&quot; setelah semua produk selesai diupdate.')"><i class="bi bi-save"></i> Simpan Perubahan Harga</button>
			<div class="form-text mt-1"><i class="bi bi-info-circle"></i> Notifikasi email dikirim belakangan lewat tombol <b>"Kirim Notifikasi Sekarang"</b> di bagian atas halaman, sehingga beberapa produk yang diupdate berurutan cukup mengirim satu email gabungan.</div>
		</div>
	</form>
	</div>
<?php endforeach; ?>
</div>

<!-- Modal Preview Email -->
<div class="modal fade" id="previewModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Preview Email Notifikasi</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body" id="previewBody">Memuat...</div>
		</div>
	</div>
</div>
<!-- Trigger tersembunyi: Tabler/Bootstrap membuka modal lewat atribut data-bs-toggle,
     bukan lewat objek JS global `bootstrap` (Tabler tidak mengekspornya ke window). -->
<button type="button" id="previewModalTrigger" class="d-none" data-bs-toggle="modal" data-bs-target="#previewModal" aria-hidden="true"></button>

<script>
document.addEventListener('DOMContentLoaded', function () {
const calcUrl = "<?= base_url('price-update/calculate') ?>";
const previewUrl = "<?= base_url('price-update/preview-email') ?>";
const previewTrigger = document.getElementById('previewModalTrigger');

document.querySelectorAll('.price-form').forEach(form => {
	const modal = form.querySelector('[name=modal]');
	const hpp = form.querySelector('[name=target_hpp_pct]');
	const offlinePrice = form.querySelector('[name=price_OFFLINE]');
	const outSrp = form.querySelector('.out-srp');
	const outMarkup = form.querySelector('.out-markup');
	const outMargin = form.querySelector('.out-margin');

	function recalc() {
		const body = `modal=${modal.value}&target_hpp_pct=${hpp.value}&actual_price=${offlinePrice ? offlinePrice.value : ''}`;
		fetch(calcUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body
		}).then(r => r.json()).then(d => {
			outSrp.textContent = 'Rp ' + Number(d.srp_suggest).toLocaleString('id-ID');
			outMarkup.textContent = d.markup_pct + '%';
			outMargin.textContent = d.margin_pct + '%';
		});
	}
	modal.addEventListener('input', recalc);
	hpp.addEventListener('input', recalc);
	if (offlinePrice) offlinePrice.addEventListener('input', recalc);
	recalc();

	form.querySelector('.btn-preview').addEventListener('click', () => {
		const fd = new FormData(form);
		fetch(previewUrl, { method: 'POST', body: fd })
			.then(r => r.text())
			.then(html => { document.getElementById('previewBody').innerHTML = html; previewTrigger.click(); });
	});
});
});
</script>

<?php if (!empty($available_vendors)): ?>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(function ($) {
	$('#vendorSelect').select2({
		theme: 'bootstrap-5',
		width: '100%',
		placeholder: '-- Pilih Vendor --',
		allowClear: true
	});
});
</script>
<?php endif; ?>
