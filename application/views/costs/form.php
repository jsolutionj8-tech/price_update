<div class="card card-stat p-4" style="max-width:480px;">
	<form method="post" action="<?= isset($cost) ? base_url('costs/update/' . $cost['id']) : base_url('costs/store') ?>">
		<div class="mb-3">
			<label class="form-label">Nama Biaya</label>
			<input type="text" name="cost_name" class="form-control" required autofocus placeholder="mis. Biaya Admin, Biaya Packing" value="<?= htmlspecialchars($cost['cost_name'] ?? '') ?>">
		</div>
		<?php $cost_type = $cost['cost_type'] ?? 'nominal'; ?>
		<div class="mb-3">
			<label class="form-label">Tipe Biaya</label>
			<div class="btn-group d-flex" role="group">
				<input type="radio" class="btn-check" name="cost_type" id="typeNominal" value="nominal" <?= $cost_type === 'nominal' ? 'checked' : '' ?>>
				<label class="btn btn-outline-primary w-50" for="typeNominal">Nominal (Rp)</label>
				<input type="radio" class="btn-check" name="cost_type" id="typePercent" value="percent" <?= $cost_type === 'percent' ? 'checked' : '' ?>>
				<label class="btn btn-outline-primary w-50" for="typePercent">Persentase (%)</label>
			</div>
		</div>
		<div class="mb-3">
			<label class="form-label" id="amountLabel"><?= $cost_type === 'percent' ? 'Persentase (%)' : 'Amount (Rp)' ?></label>
			<?php $amount_initial = $cost_type === 'percent' ? ($cost['amount'] ?? 0) : number_format((float) ($cost['amount'] ?? 0), 0, ',', '.'); ?>
			<input type="text" inputmode="decimal" autocomplete="off" id="amountInput" name="amount" class="form-control" required value="<?= htmlspecialchars($amount_initial) ?>">
			<div class="form-text" id="amountHelp"></div>
		</div>
		<script>
		(function () {
			var nominal = document.getElementById('typeNominal');
			var percent = document.getElementById('typePercent');
			var label = document.getElementById('amountLabel');
			var input = document.getElementById('amountInput');
			var help = document.getElementById('amountHelp');

			function digitsOnly(v) { return String(v).replace(/[^\d]/g, ''); }
			function formatNominal(v) {
				var d = digitsOnly(v);
				return d === '' ? '' : Number(d).toLocaleString('id-ID');
			}

			function applyLabels() {
				var isPercent = percent.checked;
				label.textContent = isPercent ? 'Persentase (%)' : 'Amount (Rp)';
				help.textContent = isPercent
					? 'Dihitung sebagai % dari Modal saat biaya ini dikaitkan ke Sales Channel di menu Update Harga.'
					: 'Nilai tetap dalam Rupiah, langsung ditambahkan ke Modal saat dikaitkan ke Sales Channel. Titik ribuan otomatis mengikuti saat mengetik (mis. 7000 → 7.000) — jangan ketik titik/koma sendiri.';
			}

			// Ganti tipe: format ulang nilai yang sudah diketik supaya tidak salah baca
			// (mis. pindah dari Nominal "7.000" ke Persentase harus jadi "7000", bukan "7,000").
			function reformatForMode() {
				if (percent.checked) {
					input.value = digitsOnly(input.value);
				} else {
					input.value = formatNominal(input.value);
				}
			}

			input.addEventListener('input', function () {
				if (!percent.checked) input.value = formatNominal(input.value);
			});
			nominal.addEventListener('change', function () { applyLabels(); reformatForMode(); });
			percent.addEventListener('change', function () { applyLabels(); reformatForMode(); });
			applyLabels();

			input.closest('form').addEventListener('submit', function () {
				// Kirim angka polos ke server: Nominal buang titik ribuan, Persentase tetap desimal.
				if (!percent.checked) input.value = digitsOnly(input.value);
			});
		})();
		</script>
		<?php if (isset($cost)): ?>
		<div class="form-check mb-3">
			<input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $cost['is_active'] ? 'checked' : '' ?>>
			<label class="form-check-label" for="isActive">Aktif</label>
		</div>
		<?php endif; ?>
		<button class="btn btn-primary"><?= isset($cost) ? 'Simpan Perubahan' : 'Tambah Biaya' ?></button>
		<a href="<?= base_url('costs') ?>" class="btn btn-outline-secondary">Batal</a>
	</form>
</div>
