<div class="card card-stat p-4 mb-3" style="max-width:560px;">
	<h6 class="fw-bold mb-3">Koneksi Shopify</h6>
	<?php if (!empty($settings['access_token'])): ?>
		<div class="alert alert-success">
			<i class="bi bi-check-circle me-1"></i>Terhubung ke <b><?= htmlspecialchars($settings['shop_domain']) ?></b>
			<div class="small text-muted mt-1">Scope: <?= htmlspecialchars($settings['scope']) ?> &nbsp;|&nbsp; Sejak: <?= !empty($settings['connected_at']) ? date('d M Y H:i', strtotime($settings['connected_at'])) : '-' ?></div>
		</div>
		<form method="post" action="<?= base_url('shopify/disconnect') ?>" onsubmit="return confirm('Putuskan koneksi ke Shopify? Anda perlu menghubungkan ulang lewat OAuth untuk memakainya lagi.')">
			<button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-plug me-1"></i>Putuskan Koneksi</button>
		</form>
	<?php else: ?>
		<div class="alert alert-warning"><i class="bi bi-info-circle me-1"></i>Belum terhubung ke Shopify.</div>
	<?php endif; ?>
</div>

<div class="card card-stat p-4" style="max-width:560px;">
	<h6 class="fw-bold mb-3">Kredensial App Shopify</h6>
	<p class="text-muted small">Ambil dari Shopify Partner/Dev Dashboard &rarr; App settings. Client Secret tidak pernah ditampilkan ulang di sini setelah disimpan.</p>
	<form method="post" action="<?= base_url('shopify/save-credentials') ?>">
		<div class="mb-3">
			<label class="form-label">Shop Domain</label>
			<input type="text" name="shop_domain" class="form-control" required value="<?= htmlspecialchars($settings['shop_domain'] ?? '') ?>" placeholder="nama-toko.myshopify.com">
		</div>
		<div class="mb-3">
			<label class="form-label">Client ID (API Key)</label>
			<input type="text" name="client_id" class="form-control" required value="<?= htmlspecialchars($settings['client_id'] ?? '') ?>">
		</div>
		<div class="mb-3">
			<label class="form-label">Client Secret (kosongkan jika tidak diubah)</label>
			<input type="password" name="client_secret" class="form-control" autocomplete="new-password" placeholder="<?= !empty($settings['id']) ? '••••••••' : '' ?>">
		</div>
		<button class="btn btn-primary">Simpan Kredensial</button>
		<?php if (!empty($settings['id']) && empty($settings['access_token'])): ?>
			<a href="<?= base_url('shopify/connect') ?>" class="btn btn-outline-success"><i class="bi bi-shop me-1"></i>Hubungkan ke Shopify</a>
		<?php endif; ?>
	</form>
</div>
