<?php
/**
 * Partial: satu tab-pane vendor pada form Update Harga.
 * Dipakai baik oleh price_update/form.php (render awal, semua vendor) maupun
 * Price_update::add_vendor() (AJAX, saat vendor baru ditambahkan tanpa reload halaman).
 * Variabel wajib: $product, $vc, $channels, $competitors, $competitor_prices, $active.
 */
?>
<div class="tab-pane fade <?= $active ? 'show active' : '' ?>" id="vendor<?= $vc['vendor_id'] ?>" data-vendor-id="<?= $vc['vendor_id'] ?>" data-vendor-name="<?= htmlspecialchars($vc['vendor_name']) ?>">
<form class="price-form" method="post" action="<?= base_url('price-update/save') ?>">
	<input type="hidden" name="product_id" value="<?= $product['id'] ?>">
	<input type="hidden" name="vendor_id" value="<?= $vc['vendor_id'] ?>">

	<div class="card card-stat p-3">
		<div class="row g-3">
			<div class="col-md-4">
				<label class="form-label">Modal (Rp)</label>
				<input type="text" inputmode="numeric" name="modal" class="form-control input-calc rupiah-input" placeholder="0" value="<?= $vc['modal'] > 0 ? number_format((int) round($vc['modal']), 0, ',', '.') : '' ?>" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">Margin (%)</label>
				<input type="number" step="0.01" name="margin_pct" class="form-control input-calc" placeholder="0" value="<?= $vc['target_hpp_pct'] > 0 ? (float) $vc['target_hpp_pct'] : '' ?>" required>
			</div>
		</div>
		<small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Profit, Markup % &amp; Margin % di bawah tiap kolom Harga Kanal dihitung otomatis dari <b>Laba Bersih</b> (Harga Jual &minus; Total Biaya kanal &minus; Modal), mis. Modal 800.000 &amp; Harga Jual 1.000.000 (tanpa Biaya kanal) &rarr; Profit 200.000: Markup % = Laba Bersih ÷ (Modal + Total Biaya) × 100, Margin % = Laba Bersih ÷ Harga Jual × 100 — Biaya bertipe Persentase di Master Biaya dihitung dari Harga Jual, bukan Modal, sesuai cara marketplace memotong komisi. Kanal <b>Offline</b> jadi acuan utama perhitungan Markup % secara keseluruhan (sesuai format spreadsheet acuan); jika Offline belum diisi, dihitung sementara dari SRP Suggest (Modal ÷ (1 &minus; Margin%)).</small>
	</div>

	<div class="row g-3 mt-3">
		<div class="col-md-8">
			<div class="card card-stat channel-price-section p-3 h-100">
				<div class="row g-3">
				<?php foreach ($channels as $ch): $is_offline = ($ch['channel_code'] === 'OFFLINE'); ?>
					<div class="col-md-6">
						<div class="channel-price-card rounded-3 p-3 h-100">
							<div class="fw-bold mb-2 text-white d-flex align-items-center gap-2">
								<span class="channel-icon"><i class="bi <?= channel_icon($ch['channel_code']) ?>"></i></span>
								<?= htmlspecialchars($ch['channel_name']) ?>
							</div>
							<div class="bg-light rounded-3 p-3 mb-2">
								<div class="mb-1">
									<span class="small text-muted fw-bold text-uppercase" style="font-size:.7rem;">Recommended Selling Price</span>
								</div>
								<div class="fw-bold text-markup-positive fs-3 out-channel-srp">—</div>
								<div class="text-muted out-channel-srp-caption mt-2" style="font-size:.7rem;">Minimum untuk mencapai target margin</div>
							</div>

							<label class="small text-white-50 mb-1 d-block">Harga Jual Aktual</label>
							<div class="input-group input-group-lg mb-2">
								<span class="input-group-text bg-white border-0 fw-bold pe-1">Rp</span>
								<input type="text" inputmode="numeric" name="price_<?= $ch['channel_code'] ?>" class="form-control bg-white border-0 fw-bold text-markup-positive channel-price-input rupiah-input ps-1" data-channel="<?= htmlspecialchars($ch['channel_code']) ?>" data-biaya="<?= (float) ($ch['total_biaya_nominal'] ?? 0) ?>" data-biaya-pct="<?= (float) ($ch['total_biaya_percent'] ?? 0) ?>" placeholder="0"
									value="<?= isset($vc['current_prices'][$ch['channel_code']]) ? number_format((int) round($vc['current_prices'][$ch['channel_code']]), 0, ',', '.') : '' ?>">
							</div>
							<div class="row g-2 text-center">
								<div class="col-4">
									<div class="bg-white rounded-3 py-2 h-100 d-flex flex-column">
										<div class="small text-muted">Profit</div>
										<div class="flex-fill d-flex align-items-center justify-content-center">
											<div class="fw-bold text-nowrap rrp-figure <?= $is_offline ? 'out-srp' : 'out-srp-channel' ?>">—</div>
										</div>
									</div>
								</div>
								<div class="col-4">
									<div class="bg-white rounded-3 py-2 h-100 d-flex flex-column">
										<div class="small text-muted">Markup</div>
										<div class="flex-fill d-flex align-items-center justify-content-center">
											<div class="fw-bold text-nowrap <?= $is_offline ? 'out-markup' : 'out-markup-channel' ?>">—</div>
										</div>
									</div>
								</div>
								<div class="col-4">
									<div class="bg-white rounded-3 py-2 h-100 d-flex flex-column">
										<div class="small text-muted">Margin</div>
										<div class="flex-fill d-flex align-items-center justify-content-center">
											<div class="fw-bold text-nowrap <?= $is_offline ? 'out-margin' : 'out-margin-channel' ?>">—</div>
										</div>
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
			<div class="card card-stat competitor-price-section p-3 h-100">
				<?php foreach ($competitors as $c): ?>
					<div class="mb-2">
						<label class="form-label small mb-1">
							<?php if (!empty($c['website_url'])): ?>
								<a href="<?= htmlspecialchars($c['website_url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($c['competitor_name']) ?> <i class="bi bi-box-arrow-up-right small"></i></a>
							<?php else: ?>
								<?= htmlspecialchars($c['competitor_name']) ?>
							<?php endif; ?>
						</label>
						<div class="input-group input-group-sm">
							<span class="input-group-text">Rp</span>
							<input type="text" inputmode="numeric" name="competitor_price[<?= $c['id'] ?>]" class="form-control competitor-price-input rupiah-input" value="<?= isset($competitor_prices[$c['competitor_code']]) ? number_format((int) round($competitor_prices[$c['competitor_code']]), 0, ',', '.') : '' ?>">
						</div>
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
		<button type="button" class="btn btn-outline-primary btn-preview"><i class="bi bi-envelope me-1"></i>Preview Email</button>
		<button type="submit" class="btn btn-primary" onclick="return confirm('Simpan perubahan harga ini? Notifikasi belum langsung terkirim &mdash; klik &quot;Kirim Notifikasi Sekarang&quot; setelah semua produk selesai diupdate.')"><i class="bi bi-save me-1"></i>Simpan Perubahan Harga</button>
		<div class="form-text mt-3"><i class="bi bi-info-circle me-1"></i>Notifikasi email dikirim belakangan lewat tombol <b>"Kirim Notifikasi Sekarang"</b> di bagian atas halaman, sehingga beberapa produk yang diupdate berurutan cukup mengirim satu email gabungan.</div>
	</div>
</form>
</div>
