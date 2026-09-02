<div class="card card-stat bg-brand p-3 mb-3">
	<div class="d-flex justify-content-between align-items-center">
		<div>
			<h5 class="fw-bold mb-0 text-white fs-3"><?= htmlspecialchars($product['product_name']) ?></h5>
			<div class="text-white-50 product-header-sub">Kode: <?= htmlspecialchars($product['product_code']) ?> &nbsp;|&nbsp; Brand: <?= htmlspecialchars($product['brand_name'] ?? '-') ?></div>
		</div>
		<a href="<?= base_url('price-update') ?>" class="btn btn-sm btn-outline-light">Kembali</a>
	</div>
</div>

<?php if (!empty($available_vendors) || !empty($vendor_costs)): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<div class="card card-stat p-3 mb-3" id="addVendorCard">
	<div class="d-flex align-items-end gap-2 flex-wrap">
		<?php if (!empty($available_vendors)): ?>
		<form method="post" action="<?= base_url('price-update/add-vendor/' . $product['id']) ?>" id="addVendorForm" class="d-flex align-items-end gap-2 mb-0">
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
		<?php endif; ?>
		<button type="button" id="cancelActiveVendorBtn" class="btn btn-outline-danger"<?= empty($vendor_costs) ? ' style="display:none"' : '' ?>><i class="bi bi-x-circle me-1"></i>Batalkan Vendor Ini</button>
	</div>
</div>
<?php endif; ?>

<ul class="nav nav-tabs mb-0" style="margin-bottom:-1.5rem !important;">
	<?php $i = 0; foreach ($vendor_costs as $vc): $i++; ?>
	<li class="nav-item"><button class="nav-link <?= $i === 1 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#vendor<?= $vc['vendor_id'] ?>"><?= htmlspecialchars($vc['vendor_name']) ?></button></li>
	<?php endforeach; ?>
</ul>
<?php if (empty($vendor_costs)): ?>
	<div class="alert alert-warning" id="noVendorWarning">Belum ada data vendor untuk produk ini. Tambahkan vendor terlebih dahulu di bawah ini.</div>
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

// Format ribuan (titik) utk input Modal/Harga Jual/Harga Kompetitor (.rupiah-input) — inputnya
// type=text (bukan type=number) krn browser tidak bisa menampilkan pemisah ribuan di input
// number. Yang dikirim ke server tetap angka mentah tanpa titik (lihat wireRupiahInput,
// getFormDataRupiahSafe, & listener 'submit' di bawah yg membersihkan titik sebelum submit).
function rupiahDigits(str) {
	return String(str || '').replace(/\D/g, '');
}
function rupiahNumber(str) {
	const digits = rupiahDigits(str);
	return digits ? parseInt(digits, 10) : 0;
}
function formatRupiahDigits(digits) {
	return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
function wireRupiahInput(el) {
	el.addEventListener('input', function () {
		const digitsBeforeCursor = rupiahDigits(el.value.slice(0, el.selectionStart));
		const digits = rupiahDigits(el.value);
		el.value = formatRupiahDigits(digits);
		const newPos = formatRupiahDigits(digitsBeforeCursor).length;
		el.setSelectionRange(newPos, newPos);
	});
}
// Dipakai sebelum new FormData(form) (preview email) — sementara ganti nilai .rupiah-input jadi
// angka mentah spy FormData yg dikirim ke server benar, lalu kembalikan tampilan formatnya.
function getFormDataRupiahSafe(form) {
	const rupiahEls = Array.from(form.querySelectorAll('.rupiah-input'));
	const originals = rupiahEls.map(el => el.value);
	rupiahEls.forEach(el => { el.value = rupiahDigits(el.value); });
	const fd = new FormData(form);
	rupiahEls.forEach((el, i) => { el.value = originals[i]; });
	return fd;
}

function wireForm(form) {
	Array.from(form.querySelectorAll('.rupiah-input')).forEach(wireRupiahInput);
	form.addEventListener('submit', function () {
		form.querySelectorAll('.rupiah-input').forEach(el => { el.value = rupiahDigits(el.value); });
	});

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

	// Sama seperti setPctText tapi hanya mengatur warna (textContent-nya sudah diisi manual
	// di pemanggil, mis. figur Rupiah Profit) — dipakai supaya Profit ikut merah/biru brand
	// seperti Markup, bukan cuma teks persentase.
	function setValueColor(el, value, canCompute, positiveClass) {
		if (!el) return;
		el.classList.remove('text-danger');
		if (positiveClass) el.classList.remove(positiveClass);
		if (!canCompute) return;
		if (value < 0) {
			el.classList.add('text-danger');
		} else if (positiveClass) {
			el.classList.add(positiveClass);
		}
	}

	// RRP (Recommended Selling Price) per kanal = "Suggested Selling Price" — Harga Jual Minimum
	// (Modal/(1-Margin%) + Biaya Tetap kanal) / (1 - Biaya Persentase kanal), dibulatkan NAIK ke
	// kelipatan Rp5.000 terdekat spy jadi harga jual yg "rapi". Biaya per kanal diambil dari
	// Master Biaya (Sales Channel) via data-biaya/data-biaya-pct di tiap input Harga Jual. Mis.
	// Modal 800.000 & Margin 10%, kanal Biaya Persentase 2,8% & Biaya Tetap 9.220 -> Harga Jual
	// Minimum 923.981 -> dibulatkan naik -> RRP 925.000.
	function calcRrp(input, modalVal, marginVal) {
		const biayaNominal = parseFloat(input.dataset.biaya) || 0;
		const biayaPct = parseFloat(input.dataset.biayaPct) || 0;
		const biayaPctFactor = 1 - (biayaPct / 100);
		const canRrp = modalVal > 0 && marginVal > 0 && marginVal < 100 && biayaPctFactor > 0;
		if (!canRrp) return null;
		const hargaJualMinimum = (modalVal / (1 - marginVal / 100) + biayaNominal) / biayaPctFactor;
		return Math.ceil(hargaJualMinimum / 5000) * 5000;
	}

	function updateChannelOutputs() {
		const modalVal = rupiahNumber(modal.value);
		const marginVal = parseFloat(margin.value) || 0;

		form.querySelectorAll('.channel-price-input').forEach(input => {
			const wrap = input.closest('.col-md-6');
			if (!wrap) return;
			const rrpEl = wrap.querySelector('.out-channel-srp');
			if (!rrpEl) return;
			const rrpVal = calcRrp(input, modalVal, marginVal);
			rrpEl.textContent = rrpVal !== null ? 'Rp ' + Math.round(rrpVal).toLocaleString('id-ID') : '—';
		});
		form.querySelectorAll('.out-channel-srp-caption').forEach(el => {
			el.textContent = 'Minimum untuk mencapai target margin';
		});

		channelInputs.forEach(input => {
			const wrap = input.closest('.col-md-6');
			if (!wrap) return;
			const srpEl = wrap.querySelector('.out-srp-channel');
			const markupEl = wrap.querySelector('.out-markup-channel');
			const marginEl = wrap.querySelector('.out-margin-channel');
			const biayaNominal = parseFloat(input.dataset.biaya) || 0;
			const biayaPct = parseFloat(input.dataset.biayaPct) || 0; // dalam %, mis. 7.5 untuk 7,5%
			const priceVal = rupiahNumber(input.value);
			const canPct = modalVal > 0 && priceVal > 0;
			// Total Biaya kanal dari Harga Jual aktual = biaya nominal (Rp) + (biaya persen % x Harga Jual).
			// Laba Bersih (Profit) = Harga Jual - Total Biaya - Modal, mis. Modal 800.000 & Harga Jual
			// 1.000.000 (tanpa Biaya kanal) -> Profit 200.000.
			const totalBiaya = biayaNominal + (priceVal * biayaPct / 100);
			const labaBersih = priceVal - totalBiaya - modalVal;
			if (srpEl) {
				srpEl.textContent = canPct ? 'Rp ' + Math.round(labaBersih).toLocaleString('id-ID') : '—';
				setValueColor(srpEl, labaBersih, canPct, 'text-markup-positive');
			}
			// Markup % = Laba Bersih / (Modal + Total Biaya) * 100
			const totalModal = modalVal + totalBiaya;
			setPctText(markupEl, canPct && totalModal > 0 ? (labaBersih / totalModal) * 100 : 0, canPct && totalModal > 0, 'text-markup-positive');
			// Margin % = Laba Bersih / Harga Jual * 100
			setPctText(marginEl, canPct ? (labaBersih / priceVal) * 100 : 0, canPct, 'text-markup-positive');
		});
	}

	function recalc() {
		const body = `modal=${rupiahNumber(modal.value)}&margin_pct=${margin.value}&actual_price=${offlinePrice ? rupiahNumber(offlinePrice.value) : ''}`;
		fetch(calcUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body
		}).then(r => r.json()).then(d => {
			const hasSrp = Number(d.srp_suggest) > 0;
			// Profit (Rp) = Modal x Markup% — sama dgn Harga Jual Aktual - Modal (Markup% sudah dihitung
			// backend dari selisih itu), mis. Modal 800.000 & Harga Jual 1.000.000 -> Profit 200.000.
			const modalVal = rupiahNumber(modal.value);
			const profitRupiah = modalVal * (Number(d.markup_pct) / 100);
			outSrp.textContent = hasSrp ? 'Rp ' + Math.round(profitRupiah).toLocaleString('id-ID') : '—';
			setValueColor(outSrp, profitRupiah, hasSrp, 'text-markup-positive');
			setPctText(outMarkup, Number(d.markup_pct), hasSrp, 'text-markup-positive');
			setPctText(outMargin, Number(d.margin_pct), hasSrp, 'text-markup-positive');
			updateChannelOutputs();
		});
	}
	modal.addEventListener('input', recalc);
	margin.addEventListener('input', recalc);
	if (offlinePrice) offlinePrice.addEventListener('input', recalc);
	channelInputs.forEach(input => input.addEventListener('input', updateChannelOutputs));
	recalc();

	// Enter jangan langsung submit form — pindah fokus berurutan: Modal -> Margin -> tiap
	// input Harga Jual (urutan kanal spt tampil di layar, termasuk Offline). Form baru
	// submit setelah Enter ditekan di input Harga Jual yang TERAKHIR.
	// Dipasang di FORM (delegasi) dgn capture:true, bukan per-input — Safari tidak selalu
	// memicu 'keydown' scr konsisten di <input type=number> sebelum submission implisit
	// jalan duluan (beda dgn Chrome), sedangkan capture phase di form pasti kebagian
	// duluan sebelum browser memutuskan submit form-nya.
	const allPriceInputs = Array.from(form.querySelectorAll('.channel-price-input'));
	const enterChain = [modal, margin, ...allPriceInputs];
	form.addEventListener('keydown', function (e) {
		if (e.key !== 'Enter' && e.keyCode !== 13) return;
		const idx = enterChain.indexOf(e.target);
		if (idx === -1) return;

		e.preventDefault();
		e.stopPropagation();
		const next = enterChain[idx + 1];
		if (next) {
			next.focus();
			next.select();
			return;
		}
		const submitBtn = form.querySelector('button[type=submit]');
		if (submitBtn) submitBtn.click();
	}, true);

	// Enter di salah satu input Harga Kompetitor pindah ke input kompetitor berikutnya
	// (bukan bagian dari rantai Modal/Margin/Harga Jual di atas — di Enter terakhir cukup
	// berhenti, tidak ikut submit form). Sama spt di atas, dipasang di FORM dgn capture:true
	// supaya konsisten jalan di Safari maupun Chrome.
	const competitorInputs = Array.from(form.querySelectorAll('.competitor-price-input'));
	form.addEventListener('keydown', function (e) {
		if (e.key !== 'Enter' && e.keyCode !== 13) return;
		const idx = competitorInputs.indexOf(e.target);
		if (idx === -1) return;

		e.preventDefault();
		e.stopPropagation();
		const next = competitorInputs[idx + 1];
		if (next) {
			next.focus();
			next.select();
		}
	}, true);

	form.querySelector('.btn-preview').addEventListener('click', () => {
		const fd = getFormDataRupiahSafe(form);
		fetch(previewUrl, { method: 'POST', body: fd })
			.then(r => r.text())
			.then(html => { document.getElementById('previewBody').innerHTML = html; previewTrigger.click(); });
	});
}

document.querySelectorAll('.price-form').forEach(wireForm);
window.priceUpdateWireForm = wireForm;

// Batalkan Vendor: hapus data Modal/Margin vendor yang sedang aktif (tab terbuka) utk
// produk ini, lalu reload agar tab, dropdown "Tambah Vendor", & select2 kembali sinkron
// dgn state server. Tombolnya ditaruh di sebelah "+Tambah" (bukan per-tab), jadi cukup
// baca vendor dari .tab-pane yang sedang aktif saat diklik.
const removeVendorUrl = "<?= base_url('price-update/remove-vendor/' . $product['id']) ?>";
const cancelVendorBtn = document.getElementById('cancelActiveVendorBtn');
if (cancelVendorBtn) {
	cancelVendorBtn.addEventListener('click', function () {
		const activePane = document.querySelector('.tab-content .tab-pane.active');
		if (!activePane) return;
		const vendorId = activePane.dataset.vendorId;
		const vendorName = activePane.dataset.vendorName;
		if (!vendorId) return;
		if (!confirm('Batalkan vendor "' + vendorName + '" untuk produk ini? Data Modal & Margin yang sudah diisi untuk vendor ini akan dihapus, termasuk notifikasi yang belum terkirim.')) return;

		cancelVendorBtn.disabled = true;
		fetch(removeVendorUrl, {
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
				alert(d.message || 'Gagal membatalkan vendor.');
				cancelVendorBtn.disabled = false;
				return;
			}
			window.location.reload();
		})
		.catch(function () {
			alert('Gagal membatalkan vendor. Coba lagi.');
			cancelVendorBtn.disabled = false;
		});
	});
}
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

			const warn = document.getElementById('noVendorWarning');
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
				addVendorForm.remove();
			}

			const cancelBtn = document.getElementById('cancelActiveVendorBtn');
			if (cancelBtn) cancelBtn.style.display = '';

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
