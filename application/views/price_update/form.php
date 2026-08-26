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
					<option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['vendor_name']) ?></option>
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

		<div class="card card-stat p-3">
			<h6 class="fw-bold">Modal & Margin</h6>
			<div class="row g-3">
				<div class="col-md-4">
					<label class="form-label">Modal (Rp)</label>
					<input type="number" step="0.01" name="modal" class="form-control input-calc" value="<?= $vc['modal'] ?>" required>
				</div>
				<div class="col-md-4">
					<label class="form-label">Margin (%)</label>
					<input type="number" step="0.01" name="margin_pct" class="form-control input-calc" value="<?= $vc['target_hpp_pct'] ?>" required>
				</div>
			</div>
			<small class="text-muted d-block mt-2"><i class="bi bi-info-circle"></i> SRP Suggest dihitung otomatis dari Modal &amp; Margin (Modal ÷ (1 &minus; Margin%)) dan ditampilkan di bawah tiap kolom Harga Kanal, bersama Markup % (dihitung dari harga kanal tsb terhadap Modal). Kanal <b>Offline</b> jadi acuan utama perhitungan Markup % secara keseluruhan (sesuai format spreadsheet acuan); jika Offline belum diisi, dihitung sementara dari SRP Suggest.</small>
		</div>

		<div class="row g-3 mt-3">
			<div class="col-md-8">
				<div class="card card-stat p-3 h-100">
					<h6 class="fw-bold">Harga Baru per Kanal</h6>
					<div class="row g-3">
					<?php foreach ($channels as $ch): $is_offline = ($ch['channel_code'] === 'OFFLINE'); ?>
						<div class="col-md-6">
							<label class="form-label"><?= htmlspecialchars($ch['channel_name']) ?></label>
							<input type="number" step="0.01" name="price_<?= $ch['channel_code'] ?>" class="form-control channel-price-input" data-channel="<?= htmlspecialchars($ch['channel_code']) ?>" data-biaya="<?= (float) ($ch['total_biaya'] ?? 0) ?>"
								value="<?= $vc['current_prices'][$ch['channel_code']] ?? '' ?>">
							<div class="row mt-1 gx-2">
								<div class="col-6"><small class="text-muted">SRP Suggest</small><div class="small fw-bold <?= $is_offline ? 'out-srp' : 'out-srp-channel' ?>">Rp 0</div></div>
								<div class="col-6"><small class="text-muted">Markup %</small><div class="small fw-bold <?= $is_offline ? 'out-markup' : 'out-markup-channel' ?>">0%</div></div>
							</div>
						</div>
					<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="card card-stat p-3 h-100">
					<h6 class="fw-bold">Harga Kompetitor</h6>
					<?php foreach ($competitors as $c): ?>
						<div class="mb-2">
							<label class="form-label small mb-1"><?= htmlspecialchars($c['competitor_name']) ?></label>
							<input type="number" step="0.01" name="competitor_price[<?= $c['id'] ?>]" class="form-control form-control-sm" placeholder="Rp" value="<?= $competitor_prices[$c['competitor_code']] ?? '' ?>">
						</div>
					<?php endforeach; ?>
					<?php if (empty($competitors)): ?>
						<div class="text-muted small">Belum ada kompetitor aktif. Tambahkan lewat menu <a href="<?= base_url('competitors/create') ?>">Master Data → Kompetitor</a>.</div>
					<?php endif; ?>
					<div class="form-text mt-1">Harga di atas ikut tersimpan (tanggal pantau = Tanggal Efektif di bawah) saat <b>Simpan Perubahan Harga</b> ditekan.</div>
					<a href="<?= base_url('competitor-price') ?>" class="small">Lihat riwayat harga kompetitor &raquo;</a>
				</div>
			</div>
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
	const margin = form.querySelector('[name=margin_pct]');
	const offlinePrice = form.querySelector('[name=price_OFFLINE]');
	const outSrp = form.querySelector('.out-srp');
	const outMarkup = form.querySelector('.out-markup');
	const channelInputs = Array.from(form.querySelectorAll('.channel-price-input')).filter(el => el.name !== 'price_OFFLINE');

	function updateChannelOutputs() {
		const modalVal = parseFloat(modal.value) || 0;
		const marginVal = parseFloat(margin.value) || 0;
		channelInputs.forEach(input => {
			const wrap = input.closest('.col-md-6');
			if (!wrap) return;
			const srpEl = wrap.querySelector('.out-srp-channel');
			const markupEl = wrap.querySelector('.out-markup-channel');
			if (srpEl) {
				// SRP Suggest per kanal = (Modal + Total Biaya kanal ini) / (1 - Margin%).
				// Kanal tanpa Biaya (Total Biaya = 0) otomatis sama dengan SRP Suggest global.
				const biaya = parseFloat(input.dataset.biaya) || 0;
				const srpChannel = (modalVal > 0 && marginVal > 0 && marginVal < 100) ? (modalVal + biaya) / (1 - (marginVal / 100)) : 0;
				srpEl.textContent = 'Rp ' + srpChannel.toLocaleString('id-ID');
			}
			if (markupEl) {
				const priceVal = parseFloat(input.value) || 0;
				markupEl.textContent = (modalVal > 0 && priceVal > 0) ? (((priceVal - modalVal) / modalVal) * 100).toFixed(2) + '%' : '0%';
			}
		});
	}

	function recalc() {
		const body = `modal=${modal.value}&margin_pct=${margin.value}&actual_price=${offlinePrice ? offlinePrice.value : ''}`;
		fetch(calcUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body
		}).then(r => r.json()).then(d => {
			outSrp.textContent = 'Rp ' + Number(d.srp_suggest || 0).toLocaleString('id-ID');
			outMarkup.textContent = d.markup_pct + '%';
			updateChannelOutputs();
		});
	}
	modal.addEventListener('input', recalc);
	margin.addEventListener('input', recalc);
	if (offlinePrice) offlinePrice.addEventListener('input', recalc);
	channelInputs.forEach(input => input.addEventListener('input', updateChannelOutputs));
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
