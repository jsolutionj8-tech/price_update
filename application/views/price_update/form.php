<div class="card card-stat bg-brand p-3 mb-3">
	<div class="d-flex justify-content-between align-items-start">
		<div>
			<h5 class="fw-bold mb-0 text-white fs-3"><?= htmlspecialchars($product['product_name']) ?></h5>
			<div class="text-white-50 product-header-sub">Kode: <?= htmlspecialchars($product['product_code']) ?> &nbsp;|&nbsp; Brand: <?= htmlspecialchars($product['brand_name'] ?? '-') ?></div>
		</div>
		<a href="<?= base_url('price-update') ?>" class="btn btn-sm btn-outline-light">Kembali</a>
	</div>
</div>

<ul class="nav nav-tabs mb-3">
	<?php $i = 0; foreach ($vendor_costs as $vc): $i++; ?>
	<li class="nav-item"><button class="nav-link <?= $i === 1 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#vendor<?= $vc['vendor_id'] ?>"><?= htmlspecialchars($vc['vendor_name']) ?></button></li>
	<?php endforeach; ?>
</ul>
<?php if (empty($vendor_costs)): ?>
	<div class="alert alert-warning">Belum ada data vendor untuk produk ini. Tambahkan vendor terlebih dahulu di bawah ini.</div>
<?php endif; ?>

<?php if (!empty($available_vendors)): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<div class="card card-stat p-3 mb-3" id="addVendorCard">
	<form method="post" action="<?= base_url('price-update/add-vendor/' . $product['id']) ?>" id="addVendorForm" class="d-flex align-items-end gap-2">
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
	<?= $this->load->view('price_update/_vendor_tab', array(
		'product' => $product,
		'vc' => $vc,
		'channels' => $channels,
		'competitors' => $competitors,
		'competitor_prices' => $competitor_prices,
		'active' => $i === 1,
	), TRUE) ?>
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

function wireForm(form) {
	const modal = form.querySelector('[name=modal]');
	const margin = form.querySelector('[name=margin_pct]');
	const offlinePrice = form.querySelector('[name=price_OFFLINE]');
	const outSrp = form.querySelector('.out-srp');
	const outMarkup = form.querySelector('.out-markup');
	const outMargin = form.querySelector('.out-margin');
	const channelInputs = Array.from(form.querySelectorAll('.channel-price-input')).filter(el => el.name !== 'price_OFFLINE');

	// Tampilkan persentase (Markup/Margin) dengan teks merah kalau nilainya minus (harga jual di bawah Modal).
	// positiveClass (opsional) dipakai saat nilainya positif, mis. Markup biru brand saat untung.
	function setPctText(el, pct, canCompute, positiveClass) {
		if (!el) return;
		el.classList.remove('text-danger');
		if (positiveClass) el.classList.remove(positiveClass);
		if (!canCompute) {
			el.textContent = '—';
			return;
		}
		el.textContent = pct.toFixed(2) + '%';
		if (pct < 0) {
			el.classList.add('text-danger');
		} else if (positiveClass) {
			el.classList.add(positiveClass);
		}
	}

	function updateChannelOutputs() {
		const modalVal = parseFloat(modal.value) || 0;
		const marginVal = parseFloat(margin.value) || 0;
		channelInputs.forEach(input => {
			const wrap = input.closest('.col-md-6');
			if (!wrap) return;
			const srpEl = wrap.querySelector('.out-srp-channel');
			const markupEl = wrap.querySelector('.out-markup-channel');
			const marginEl = wrap.querySelector('.out-margin-channel');
			const biayaNominal = parseFloat(input.dataset.biaya) || 0;
			const biayaPct = parseFloat(input.dataset.biayaPct) || 0; // dalam %, mis. 7.5 untuk 7,5%
			if (srpEl) {
				// RRP Suggest per kanal, dgn biaya persen dihitung dari HARGA JUAL (bukan Modal) —
				// sesuai cara marketplace memotong komisi (mis. Komisi 6,5% x harga jual, bukan x modal).
				// Diturunkan dari: Margin% = (RRP - TotalBiaya - Modal) / RRP, TotalBiaya = biayaNominal + biayaPct%*RRP
				// -> RRP = (Modal + biayaNominal) / (1 - Margin%/100 - biayaPct/100)
				const denom = 1 - (marginVal / 100) - (biayaPct / 100);
				const canSrp = modalVal > 0 && marginVal > 0 && denom > 0;
				srpEl.textContent = canSrp ? 'Rp ' + Math.round((modalVal + biayaNominal) / denom).toLocaleString('id-ID') : '—';
			}
			const priceVal = parseFloat(input.value) || 0;
			const canPct = modalVal > 0 && priceVal > 0;
			// Total Biaya kanal dari Harga Jual aktual = biaya nominal (Rp) + (biaya persen % x Harga Jual).
			// Laba Bersih = Harga Jual - Total Biaya - Modal.
			const totalBiaya = biayaNominal + (priceVal * biayaPct / 100);
			const labaBersih = priceVal - totalBiaya - modalVal;
			// Markup % = Laba Bersih / (Modal + Total Biaya) * 100
			const totalModal = modalVal + totalBiaya;
			setPctText(markupEl, canPct && totalModal > 0 ? (labaBersih / totalModal) * 100 : 0, canPct && totalModal > 0, 'text-markup-positive');
			// Margin % = Laba Bersih / Harga Jual * 100
			setPctText(marginEl, canPct ? (labaBersih / priceVal) * 100 : 0, canPct);
		});
	}

	function recalc() {
		const body = `modal=${modal.value}&margin_pct=${margin.value}&actual_price=${offlinePrice ? offlinePrice.value : ''}`;
		fetch(calcUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body
		}).then(r => r.json()).then(d => {
			const hasSrp = Number(d.srp_suggest) > 0;
			outSrp.textContent = hasSrp ? 'Rp ' + Math.round(Number(d.srp_suggest)).toLocaleString('id-ID') : '—';
			setPctText(outMarkup, Number(d.markup_pct), hasSrp, 'text-markup-positive');
			setPctText(outMargin, Number(d.margin_pct), hasSrp);
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
}

document.querySelectorAll('.price-form').forEach(wireForm);
window.priceUpdateWireForm = wireForm;
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

	// Tambah Vendor lewat AJAX: tab & form vendor baru disisipkan langsung tanpa reload halaman.
	const addVendorForm = document.getElementById('addVendorForm');
	const addVendorUrl = addVendorForm.getAttribute('action');
	const submitBtn = addVendorForm.querySelector('button[type=submit]');

	addVendorForm.addEventListener('submit', function (e) {
		e.preventDefault();
		const vendorId = $('#vendorSelect').val();
		if (!vendorId) return;

		submitBtn.disabled = true;
		fetch(addVendorUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: 'vendor_id=' + encodeURIComponent(vendorId)
		})
		.then(r => r.json())
		.then(function (d) {
			if (!d.success) {
				alert(d.message || 'Gagal menambah vendor.');
				return;
			}

			const warn = document.querySelector('.alert-warning');
			if (warn) warn.remove();

			const li = document.createElement('li');
			li.className = 'nav-item';
			const navBtn = document.createElement('button');
			navBtn.type = 'button';
			navBtn.className = 'nav-link';
			navBtn.setAttribute('data-bs-toggle', 'tab');
			navBtn.setAttribute('data-bs-target', '#vendor' + d.vendor_id);
			navBtn.textContent = d.vendor_name;
			li.appendChild(navBtn);
			document.querySelector('.nav-tabs').appendChild(li);

			const tabContent = document.querySelector('.tab-content');
			const existingFirstPane = tabContent.querySelector('.tab-pane');
			const wrapper = document.createElement('div');
			wrapper.innerHTML = d.tab_html.trim();
			const newPane = wrapper.firstElementChild;
			tabContent.appendChild(newPane);

			const newForm = newPane.querySelector('.price-form');
			if (window.priceUpdateWireForm) window.priceUpdateWireForm(newForm);

			// Salin Harga Kompetitor yang sudah diisi di tab vendor pertama supaya tidak
			// perlu diketik ulang — harga kompetitor memang bukan data per-vendor.
			if (existingFirstPane && existingFirstPane !== newPane) {
				existingFirstPane.querySelectorAll('[name^="competitor_price"]').forEach(function (src) {
					if (src.value === '') return;
					const target = newForm.querySelector('[name="' + src.name + '"]');
					if (target) target.value = src.value;
				});
			}

			$('#vendorSelect option[value="' + vendorId + '"]').remove();
			$('#vendorSelect').val('').trigger('change');
			if ($('#vendorSelect option[value!=""]').length === 0) {
				document.getElementById('addVendorCard').remove();
			}

			navBtn.click();
		})
		.catch(function () {
			alert('Gagal menambah vendor. Coba lagi.');
		})
		.finally(function () {
			submitBtn.disabled = false;
		});
	});
});
</script>
<?php endif; ?>
