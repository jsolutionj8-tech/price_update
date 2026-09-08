<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($event['event_name']) ?> — RSVP</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
	:root {
		--cream: #F7F2E9;
		--cream-deep: #EFE6D6;
		--ink: #201B14;
		--gold: #B8873B;
		--gold-soft: #E8D9BE;
		--muted: #8A7F6D;
		--danger: #B3413A;
		--radius: 14px;
	}
	* { box-sizing: border-box; }
	body {
		margin: 0;
		background: var(--cream);
		color: var(--ink);
		font-family: 'Inter', sans-serif;
		-webkit-font-smoothing: antialiased;
	}
	.rsvp-shell {
		max-width: 480px;
		margin: 0 auto;
		min-height: 100vh;
		background: var(--cream);
		display: flex;
		flex-direction: column;
	}
	.rsvp-topbar {
		padding: 22px 24px 0;
		text-align: center;
	}
	.rsvp-topbar .brand {
		font-family: 'Cormorant Garamond', serif;
		font-size: 20px;
		letter-spacing: .04em;
	}
	.rsvp-topbar .brand .x { color: var(--gold); margin: 0 4px; }
	hr.rule { border: none; border-top: 1px solid var(--cream-deep); margin: 16px 24px 0; }

	.step-indicator {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 6px;
		padding: 18px 24px 4px;
	}
	.step-dot {
		width: 26px; height: 26px; border-radius: 50%;
		display: flex; align-items: center; justify-content: center;
		font-size: 12px; font-weight: 600;
		border: 1px solid #D8CDB8;
		color: var(--muted);
		background: var(--cream);
		flex-shrink: 0;
	}
	.step-dot.active { background: var(--gold); border-color: var(--gold); color: #fff; }
	.step-dot.done { background: var(--ink); border-color: var(--ink); color: #fff; }
	.step-line { flex: 1; height: 1px; background: #D8CDB8; max-width: 28px; }

	.rsvp-steps { flex: 1; padding: 8px 24px 24px; }
	.rsvp-step { display: none; }
	.rsvp-step.active { display: block; }

	.step-eyebrow { color: var(--gold); font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
	.step-count { color: var(--muted); font-size: 12px; float: right; }
	h1.step-title {
		font-family: 'Cormorant Garamond', serif;
		font-size: 30px;
		font-weight: 600;
		margin: 6px 0 8px;
		line-height: 1.15;
	}
	.step-desc { color: var(--muted); font-size: 14px; line-height: 1.5; margin-bottom: 18px; }

	.schedule-card {
		border: 1.5px solid #D8CDB8;
		border-radius: var(--radius);
		padding: 16px 18px;
		display: flex;
		align-items: center;
		gap: 16px;
		margin-bottom: 12px;
		cursor: pointer;
		background: #fff;
		transition: border-color .15s, background .15s;
	}
	.schedule-card.selected { border-color: var(--gold); background: #FFFDF8; }
	.schedule-card .day-num { font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 600; line-height: 1; }
	.schedule-card .day-name { color: var(--gold); font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
	.schedule-card .day-date { color: var(--muted); font-size: 13px; margin-top: 2px; }

	label.field-label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; }
	label.field-label .req { color: var(--danger); }
	.field-control {
		width: 100%;
		border: 1.5px solid #D8CDB8;
		border-radius: 10px;
		padding: 12px 14px;
		font-size: 15px;
		font-family: inherit;
		background: #fff;
		color: var(--ink);
	}
	.field-control:focus { outline: none; border-color: var(--gold); }
	textarea.field-control { resize: vertical; min-height: 80px; }

	.guest-count-box { display: flex; align-items: center; gap: 12px; }
	.guest-count-box .field-control { text-align: center; }

	.deposit-box {
		background: var(--gold-soft);
		border-radius: var(--radius);
		padding: 16px 18px;
		margin-top: 18px;
	}
	.deposit-box .lbl { color: #6B5636; font-size: 12px; }
	.deposit-box .val { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 600; margin: 2px 0; }
	.deposit-box .hint { color: #6B5636; font-size: 12px; }

	.toggle-group { display: flex; gap: 10px; }
	.toggle-btn {
		flex: 1;
		border: 1.5px solid #D8CDB8;
		border-radius: 10px;
		padding: 12px;
		text-align: center;
		font-size: 14px;
		font-weight: 600;
		cursor: pointer;
		background: #fff;
		color: var(--ink);
	}
	.toggle-btn.selected { background: var(--ink); border-color: var(--ink); color: #fff; }

	.guest-name-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
	.guest-name-row .idx {
		width: 24px; height: 24px; border-radius: 50%; border: 1px solid var(--gold); color: var(--gold);
		display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0;
	}

	.member-callout {
		background: var(--ink); color: #fff; border-radius: var(--radius); padding: 16px 18px;
		display: flex; gap: 12px; align-items: flex-start; margin-bottom: 18px;
	}
	.member-callout .star { color: var(--gold); font-size: 20px; }
	.member-callout .t1 { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 600; }
	.member-callout .t2 { font-size: 12px; opacity: .75; margin-top: 4px; }

	.register-box { border: 1px solid var(--gold-soft); background: #FFFDF8; border-radius: 10px; padding: 12px 14px; font-size: 13px; margin-top: 14px; }
	.register-box a { color: var(--gold); font-weight: 600; }

	.consent-row { display: flex; align-items: flex-start; gap: 10px; margin-top: 16px; font-size: 13px; color: var(--muted); }
	.consent-row input { margin-top: 3px; }

	.pay-option { border: 1.5px solid #D8CDB8; border-radius: var(--radius); padding: 14px 16px; margin-bottom: 12px; background: #fff; }
	.pay-option .eyebrow { font-size: 11px; color: var(--gold); font-weight: 700; letter-spacing: .08em; }
	.pay-option .name { font-weight: 600; margin: 2px 0 8px; }
	.va-box { display: flex; align-items: center; justify-content: space-between; border: 1px dashed #D8CDB8; border-radius: 8px; padding: 10px 12px; font-family: monospace; font-size: 15px; }
	.va-box button { border: none; background: var(--ink); color: #fff; border-radius: 6px; padding: 6px 12px; font-size: 12px; cursor: pointer; }

	.rsvp-actions { padding: 14px 24px 28px; display: flex; gap: 10px; }
	.btn {
		flex: 1; border: none; border-radius: 12px; padding: 15px; font-size: 14px; font-weight: 700;
		letter-spacing: .02em; cursor: pointer; text-align: center;
	}
	.btn-primary { background: var(--ink); color: #fff; }
	.btn-primary:disabled { opacity: .4; cursor: not-allowed; }
	.btn-outline { background: #fff; border: 1.5px solid var(--ink); color: var(--ink); }

	.assist-line { text-align: center; font-size: 12px; color: var(--muted); margin-top: 6px; }
	.assist-line a { color: var(--gold); }

	.error-box { background: #FBE9E7; color: var(--danger); border-radius: 10px; padding: 12px 14px; font-size: 13px; margin-bottom: 14px; display: none; }
</style>
</head>
<body>
<div class="rsvp-shell">
	<div class="rsvp-topbar">
		<div class="brand"><?= htmlspecialchars($event['event_name']) ?></div>
	</div>
	<hr class="rule">

	<div class="step-indicator" id="stepIndicator"></div>

	<main class="rsvp-steps">
		<div class="error-box" id="errorBox"></div>

		<!-- STEP 1: Jadwal + jumlah tamu -->
		<section class="rsvp-step active" data-step="1">
			<span class="step-eyebrow">Reservasi</span><span class="step-count">1 / 4</span>
			<h1 class="step-title">Pilih jadwal reservasi</h1>
			<p class="step-desc"><?= htmlspecialchars($event['tagline'] ?? '') ?></p>

			<div id="scheduleList">
				<?php foreach ($schedules as $s): $ts = strtotime($s['event_date']); ?>
					<div class="schedule-card" data-schedule-id="<?= $s['id'] ?>" data-quota="<?= $s['quota'] !== null ? (int) $s['quota'] : '' ?>">
						<div class="day-num"><?= date('d', $ts) ?></div>
						<div>
							<div class="day-name"><?= strtoupper(date('l', $ts)) ?></div>
							<div class="day-date"><?= tgl_indo($s['event_date']) ?> &middot; <?= substr($s['event_time'], 0, 5) ?> WIB</div>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if (empty($schedules)): ?>
					<p class="step-desc">Belum ada jadwal tersedia untuk event ini.</p>
				<?php endif; ?>
			</div>

			<label class="field-label">Total guest <span class="req">*</span></label>
			<input type="number" id="guestCount" class="field-control" min="1" value="1">

			<div class="deposit-box">
				<div class="lbl">Deposit reservasi</div>
				<div class="val">IDR <span id="depositValue">0</span></div>
				<div class="hint">IDR <?= number_format($event['deposit_per_guest'], 0, ',', '.') ?> &times; jumlah tamu</div>
			</div>
		</section>

		<!-- STEP 2: Data pemesan & tamu -->
		<section class="rsvp-step" data-step="2">
			<span class="step-eyebrow">Data Tamu</span><span class="step-count">2 / 4</span>
			<h1 class="step-title">Data pemesan &amp; tamu</h1>
			<p class="step-desc">Kami akan mengirimkan konfirmasi ke kontak utama berikut.</p>

			<label class="field-label">Nama pemesan <span class="req">*</span></label>
			<input type="text" id="ordererName" class="field-control">
			<label class="field-label">Nomor handphone <span class="req">*</span></label>
			<input type="text" id="phone" class="field-control">
			<label class="field-label">Email <span class="req">*</span></label>
			<input type="email" id="email" class="field-control">

			<label class="field-label" style="margin-top:20px;">Nama setiap tamu</label>
			<div id="guestNameList"></div>

			<label class="field-label">Apakah ada alergi makanan?</label>
			<div class="toggle-group">
				<div class="toggle-btn selected" data-allergy="0">Tidak ada</div>
				<div class="toggle-btn" data-allergy="1">Ya, ada</div>
			</div>
			<div id="allergyNoteWrap" style="display:none;">
				<label class="field-label">Sebutkan alergi dan nama tamu <span class="req">*</span></label>
				<textarea id="allergyNote" class="field-control"></textarea>
			</div>
		</section>

		<!-- STEP 3: Membership -->
		<section class="rsvp-step" data-step="3">
			<span class="step-eyebrow">Membership</span><span class="step-count">3 / 4</span>
			<h1 class="step-title"><?= htmlspecialchars(explode(' ', $event['event_name'])[0] ?? 'Atambah') ?> Membership</h1>

			<?php if (!empty($event['membership_gift_text'])): ?>
			<div class="member-callout">
				<div class="star">&#10022;</div>
				<div>
					<div class="t1"><?= htmlspecialchars($event['membership_gift_text']) ?></div>
					<div class="t2">Tunjukkan Gift Barcode pada saat acara untuk mengambil hadiah.</div>
				</div>
			</div>
			<?php endif; ?>

			<label class="field-label">Apakah Anda member Atambah.com?</label>
			<div class="toggle-group">
				<div class="toggle-btn" data-member="yes">Yes</div>
				<div class="toggle-btn" data-member="no">No</div>
			</div>

			<?php if (!empty($event['membership_register_url'])): ?>
			<div class="register-box" id="registerBox" style="display:none;">
				Belum menjadi member? Daftar gratis untuk mendapatkan special gift.<br>
				<a href="<?= htmlspecialchars($event['membership_register_url']) ?>" target="_blank" rel="noopener">Daftar Member Atambah &#8599;</a>
			</div>
			<?php endif; ?>

			<div class="consent-row">
				<input type="checkbox" id="marketingConsent">
				<label for="marketingConsent">Saya bersedia menerima informasi acara, penawaran, dan promosi dari <?= htmlspecialchars($event['event_name']) ?> melalui WhatsApp atau email.</label>
			</div>
		</section>

		<!-- STEP 4: Pembayaran -->
		<section class="rsvp-step" data-step="4">
			<span class="step-eyebrow">Pembayaran</span><span class="step-count">4 / 4</span>
			<h1 class="step-title">Pembayaran deposit</h1>

			<div class="deposit-box" style="margin-top:0;">
				<div class="lbl">Total deposit</div>
				<div class="val">IDR <span id="depositValue2">0</span></div>
				<div class="hint">Deposit akan diperhitungkan pada tagihan akhir.</div>
			</div>

			<?php if (!empty($event['bank_account_number'])): ?>
			<div class="pay-option" style="margin-top:16px;">
				<div class="eyebrow">BANK TRANSFER</div>
				<div class="name"><?= htmlspecialchars($event['bank_name'] ?? 'Bank Transfer') ?><?= !empty($event['bank_account_name']) ? ' — a.n. ' . htmlspecialchars($event['bank_account_name']) : '' ?></div>
				<div class="va-box">
					<span id="vaNumber"><?= htmlspecialchars($event['bank_account_number']) ?></span>
					<button type="button" id="copyVaBtn">Copy</button>
				</div>
			</div>
			<?php endif; ?>

			<div class="consent-row">
				<input type="checkbox" id="paymentAgreement">
				<label for="paymentAgreement">Saya memahami bahwa reservasi dikonfirmasi setelah pembayaran berhasil diverifikasi.</label>
			</div>
		</section>
	</main>

	<div class="rsvp-actions">
		<button class="btn btn-outline" id="btnBack" style="display:none;">&larr; Kembali</button>
		<button class="btn btn-primary" id="btnNext">Lanjutkan &rarr;</button>
	</div>
	<?php if (!empty($event['rsvp_assistance_phone'])): ?>
	<p class="assist-line">RSVP Assistance &middot; <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $event['rsvp_assistance_phone']) ?>"><?= htmlspecialchars($event['rsvp_assistance_phone']) ?></a></p>
	<?php endif; ?>
</div>

<script>
(function () {
	const TOTAL_STEPS = 4;
	const depositPerGuest = <?= (float) $event['deposit_per_guest'] ?>;
	const submitUrl = <?= json_encode(base_url('rsvp/' . $event['slug'] . '/submit')) ?>;

	let currentStep = 1;
	let selectedScheduleId = null;
	let selectedQuota = null;
	let allergyFlag = 0;
	let memberFlag = null;

	const errorBox = document.getElementById('errorBox');
	function showError(msg) {
		errorBox.textContent = msg;
		errorBox.style.display = 'block';
		errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}
	function clearError() { errorBox.style.display = 'none'; }

	function renderStepIndicator() {
		const el = document.getElementById('stepIndicator');
		el.innerHTML = '';
		for (let i = 1; i <= TOTAL_STEPS; i++) {
			if (i > 1) {
				const line = document.createElement('div');
				line.className = 'step-line';
				el.appendChild(line);
			}
			const dot = document.createElement('div');
			dot.className = 'step-dot' + (i < currentStep ? ' done' : (i === currentStep ? ' active' : ''));
			dot.textContent = i < currentStep ? '✓' : i;
			el.appendChild(dot);
		}
	}

	function showStep(n) {
		document.querySelectorAll('.rsvp-step').forEach(function (s) {
			s.classList.toggle('active', parseInt(s.dataset.step, 10) === n);
		});
		document.getElementById('btnBack').style.display = n === 1 ? 'none' : 'block';
		document.getElementById('btnNext').textContent = n === TOTAL_STEPS ? 'Konfirmasi RSVP →' : 'Lanjutkan →';
		clearError();
		window.scrollTo({ top: 0, behavior: 'instant' });
	}

	// --- STEP 1: jadwal & jumlah tamu ---
	document.querySelectorAll('.schedule-card').forEach(function (card) {
		card.addEventListener('click', function () {
			document.querySelectorAll('.schedule-card').forEach(function (c) { c.classList.remove('selected'); });
			card.classList.add('selected');
			selectedScheduleId = card.dataset.scheduleId;
			selectedQuota = card.dataset.quota !== '' ? parseInt(card.dataset.quota, 10) : null;
		});
	});
	if (document.querySelectorAll('.schedule-card').length === 1) {
		document.querySelector('.schedule-card').click();
	}

	const guestCountInput = document.getElementById('guestCount');
	function updateDeposit() {
		const count = Math.max(1, parseInt(guestCountInput.value, 10) || 1);
		const total = count * depositPerGuest;
		const formatted = total.toLocaleString('id-ID');
		document.getElementById('depositValue').textContent = formatted;
		document.getElementById('depositValue2').textContent = formatted;
	}
	guestCountInput.addEventListener('input', updateDeposit);
	updateDeposit();

	// --- STEP 2: nama tamu dinamis + alergi ---
	function renderGuestNameInputs() {
		const count = Math.max(1, parseInt(guestCountInput.value, 10) || 1);
		const wrap = document.getElementById('guestNameList');
		const existing = Array.from(wrap.querySelectorAll('input')).map(function (i) { return i.value; });
		wrap.innerHTML = '';
		for (let i = 0; i < count; i++) {
			const row = document.createElement('div');
			row.className = 'guest-name-row';
			row.innerHTML = '<div class="idx">' + (i + 1) + '</div>';
			const input = document.createElement('input');
			input.type = 'text';
			input.className = 'field-control guest-name-input';
			input.placeholder = 'Sesuai identitas';
			input.value = existing[i] || (i === 0 ? document.getElementById('ordererName').value : '');
			row.appendChild(input);
			wrap.appendChild(row);
		}
	}

	document.querySelectorAll('[data-allergy]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			document.querySelectorAll('[data-allergy]').forEach(function (b) { b.classList.remove('selected'); });
			btn.classList.add('selected');
			allergyFlag = parseInt(btn.dataset.allergy, 10);
			document.getElementById('allergyNoteWrap').style.display = allergyFlag ? 'block' : 'none';
		});
	});

	// --- STEP 3: membership ---
	document.querySelectorAll('[data-member]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			document.querySelectorAll('[data-member]').forEach(function (b) { b.classList.remove('selected'); });
			btn.classList.add('selected');
			memberFlag = btn.dataset.member;
			const box = document.getElementById('registerBox');
			if (box) box.style.display = memberFlag === 'no' ? 'block' : 'none';
		});
	});

	// --- STEP 4: copy VA ---
	const copyBtn = document.getElementById('copyVaBtn');
	if (copyBtn) {
		copyBtn.addEventListener('click', function () {
			navigator.clipboard.writeText(document.getElementById('vaNumber').textContent.trim());
			copyBtn.textContent = 'Copied';
			setTimeout(function () { copyBtn.textContent = 'Copy'; }, 1500);
		});
	}

	// --- Validasi per step ---
	function validateStep(n) {
		if (n === 1) {
			if (!selectedScheduleId) return 'Pilih jadwal reservasi terlebih dahulu.';
			const count = parseInt(guestCountInput.value, 10) || 0;
			if (count < 1) return 'Total guest minimal 1.';
			if (selectedQuota !== null && count > selectedQuota) return 'Kuota jadwal ini tidak mencukupi (sisa ' + selectedQuota + ' tamu).';
			return null;
		}
		if (n === 2) {
			if (!document.getElementById('ordererName').value.trim()) return 'Nama pemesan wajib diisi.';
			if (!document.getElementById('phone').value.trim()) return 'Nomor handphone wajib diisi.';
			const email = document.getElementById('email').value.trim();
			if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return 'Email tidak valid.';
			const guestInputs = document.querySelectorAll('.guest-name-input');
			for (const inp of guestInputs) {
				if (!inp.value.trim()) return 'Lengkapi nama setiap tamu.';
			}
			if (allergyFlag && !document.getElementById('allergyNote').value.trim()) return 'Sebutkan alergi dan nama tamu.';
			return null;
		}
		if (n === 3) {
			if (!memberFlag) return 'Pilih status membership Anda.';
			return null;
		}
		if (n === 4) {
			if (!document.getElementById('paymentAgreement').checked) return 'Centang persetujuan sebelum konfirmasi RSVP.';
			return null;
		}
		return null;
	}

	function submitRsvp() {
		const btn = document.getElementById('btnNext');
		btn.disabled = true;
		btn.textContent = 'Memproses...';

		const guestNames = Array.from(document.querySelectorAll('.guest-name-input')).map(function (i) { return i.value.trim(); });
		const fd = new URLSearchParams();
		fd.append('schedule_id', selectedScheduleId);
		fd.append('orderer_name', document.getElementById('ordererName').value.trim());
		fd.append('phone', document.getElementById('phone').value.trim());
		fd.append('email', document.getElementById('email').value.trim());
		fd.append('guest_count', String(guestNames.length));
		guestNames.forEach(function (n) { fd.append('guest_name[]', n); });
		fd.append('has_allergy', String(allergyFlag));
		fd.append('allergy_note', allergyFlag ? document.getElementById('allergyNote').value.trim() : '');
		fd.append('is_member', memberFlag);
		fd.append('marketing_consent', document.getElementById('marketingConsent').checked ? '1' : '0');
		fd.append('payment_method', 'bank_transfer');

		fetch(submitUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: fd.toString(),
		}).then(function (r) { return r.json(); }).then(function (data) {
			if (!data.success) {
				btn.disabled = false;
				btn.textContent = 'Konfirmasi RSVP →';
				showError(data.message || 'Gagal menyimpan RSVP. Coba lagi.');
				return;
			}
			window.location.href = data.redirect_url;
		}).catch(function () {
			btn.disabled = false;
			btn.textContent = 'Konfirmasi RSVP →';
			showError('Gagal menghubungi server. Periksa koneksi internet Anda.');
		});
	}

	document.getElementById('btnNext').addEventListener('click', function () {
		const err = validateStep(currentStep);
		if (err) { showError(err); return; }

		if (currentStep === TOTAL_STEPS) {
			submitRsvp();
			return;
		}

		if (currentStep === 1) renderGuestNameInputs();

		currentStep++;
		showStep(currentStep);
		renderStepIndicator();
	});

	document.getElementById('btnBack').addEventListener('click', function () {
		currentStep--;
		showStep(currentStep);
		renderStepIndicator();
	});

	renderStepIndicator();
	showStep(1);
})();
</script>
</body>
</html>
