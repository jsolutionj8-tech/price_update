<?php
/**
 * Partial: satu tab-pane vendor pada form Update Harga.
 * Dipakai baik oleh price_update/form.php (render awal, semua vendor) maupun
 * Price_update::add_vendor() (AJAX, saat vendor baru ditambahkan tanpa reload halaman).
 * Variabel wajib: $product, $vc, $channels, $competitors, $competitor_prices, $active.
 */
?>
<div class="tab-pane fade <?= $active ? 'show active' : '' ?>" id="vendor<?= $vc['vendor_id'] ?>">
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
		<small class="text-muted d-block mt-2"><i class="bi bi-info-circle"></i> RRP Suggest dihitung otomatis dari Modal &amp; Margin (Modal ÷ (1 &minus; Margin%)) dan ditampilkan di bawah tiap kolom Harga Kanal, bersama Markup % (dihitung dari harga kanal tsb terhadap Modal). Kanal <b>Offline</b> jadi acuan utama perhitungan Markup % secara keseluruhan (sesuai format spreadsheet acuan); jika Offline belum diisi, dihitung sementara dari RRP Suggest.</small>
	</div>

	<div class="row g-3 mt-3">
		<div class="col-md-8">
			<div class="card card-stat p-3 h-100">
				<h6 class="fw-bold">Harga Baru per Channel</h6>
				<div class="row g-3">
				<?php foreach ($channels as $ch): $is_offline = ($ch['channel_code'] === 'OFFLINE'); ?>
					<div class="col-md-6">
						<div class="border border-brand rounded-3 p-3 h-100">
							<div class="fw-bold mb-2"><?= htmlspecialchars($ch['channel_name']) ?></div>
							<div class="input-group input-group-lg mb-2">
								<span class="input-group-text bg-brand-lt border-0 fw-bold">Rp</span>
								<input type="number" step="0.01" name="price_<?= $ch['channel_code'] ?>" class="form-control bg-brand-lt border-0 fw-bold channel-price-input" data-channel="<?= htmlspecialchars($ch['channel_code']) ?>" data-biaya="<?= (float) ($ch['total_biaya_nominal'] ?? 0) ?>" data-biaya-pct="<?= (float) ($ch['total_biaya_percent'] ?? 0) ?>" placeholder="0"
									value="<?= $vc['current_prices'][$ch['channel_code']] ?? '' ?>">
							</div>
							<div class="row g-2 text-center">
								<div class="col-4">
									<div class="bg-brand-lt rounded-3 py-2">
										<div class="small text-muted">RRP</div>
										<div class="fw-bold text-nowrap <?= $is_offline ? 'out-srp' : 'out-srp-channel' ?>">—</div>
									</div>
								</div>
								<div class="col-4">
									<div class="bg-brand-lt rounded-3 py-2">
										<div class="small text-muted">Markup</div>
										<div class="fw-bold text-nowrap <?= $is_offline ? 'out-markup' : 'out-markup-channel' ?>">—</div>
									</div>
								</div>
								<div class="col-4">
									<div class="bg-brand-lt rounded-3 py-2">
										<div class="small text-muted">Margin</div>
										<div class="fw-bold text-nowrap <?= $is_offline ? 'out-margin' : 'out-margin-channel' ?>">—</div>
									</div>
								</div>
							</div>
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
						<label class="form-label small mb-1">
							<?php if (!empty($c['website_url'])): ?>
								<a href="<?= htmlspecialchars($c['website_url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($c['competitor_name']) ?> <i class="bi bi-box-arrow-up-right small"></i></a>
							<?php else: ?>
								<?= htmlspecialchars($c['competitor_name']) ?>
							<?php endif; ?>
						</label>
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
