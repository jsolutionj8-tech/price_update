<?php
	$seg = $this->uri->segment(1) ?: 'dashboard';
	$role = $logged_in_user['role'] ?? '';
	$accessible_menus = $accessible_menus ?? array();
	// Dashboard & Keluar selalu tampil untuk siapa pun yang login; menu lain
	// mengikuti konfigurasi Administrasi -> Hak Akses (ADMIN selalu penuh).
	$can = function ($key) use ($role, $accessible_menus) {
		return $role === 'ADMIN' || in_array($key, $accessible_menus, TRUE);
	};
	$show_master_data = $can('products') || $can('categories') || $can('vendors') || $can('competitors') || $can('costs') || $can('marketplaces');
	$show_transaksi   = $can('price-update') || $can('price-history');
	$show_admin       = $role === 'ADMIN' || $can('users') || $can('notification-groups') || $can('reports');
?>
<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
	<div class="container-fluid">
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="sidebar-menu">
			<ul class="navbar-nav pt-lg-3">
				<?php if ($show_master_data): ?>
				<li class="nav-item"><span class="nav-section-title">Master Data</span></li>
				<?php endif; ?>
				<?php if ($can('products')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'products' ? 'active' : '' ?>" href="<?= base_url('products') ?>">
						<span class="nav-link-icon"><i class="bi bi-box-seam"></i></span>
						<span class="nav-link-title">Produk</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('categories')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'categories' ? 'active' : '' ?>" href="<?= base_url('categories') ?>">
						<span class="nav-link-icon"><i class="bi bi-tags"></i></span>
						<span class="nav-link-title">Kategori Barang</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('vendors')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'vendors' ? 'active' : '' ?>" href="<?= base_url('vendors') ?>">
						<span class="nav-link-icon"><i class="bi bi-truck"></i></span>
						<span class="nav-link-title">Vendor</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('competitors')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'competitors' ? 'active' : '' ?>" href="<?= base_url('competitors') ?>">
						<span class="nav-link-icon"><i class="bi bi-shop-window"></i></span>
						<span class="nav-link-title">Kompetitor</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('costs')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'costs' ? 'active' : '' ?>" href="<?= base_url('costs') ?>">
						<span class="nav-link-icon"><i class="bi bi-cash-coin"></i></span>
						<span class="nav-link-title">Master Biaya</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('marketplaces')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'marketplaces' ? 'active' : '' ?>" href="<?= base_url('marketplaces') ?>">
						<span class="nav-link-icon"><i class="bi bi-bag-check"></i></span>
						<span class="nav-link-title">Sales Channel</span>
					</a>
				</li>
				<?php endif; ?>

				<?php if ($show_transaksi): ?>
				<li class="nav-item"><span class="nav-section-title">Draft Pricing</span></li>
				<?php endif; ?>
				<?php if ($can('price-update')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'price-update' ? 'active' : '' ?>" href="<?= base_url('price-update') ?>">
						<span class="nav-link-icon"><i class="bi bi-currency-exchange"></i></span>
						<span class="nav-link-title">Update Harga</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('price-history')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'price-history' ? 'active' : '' ?>" href="<?= base_url('price-history') ?>">
						<span class="nav-link-icon"><i class="bi bi-clock-history"></i></span>
						<span class="nav-link-title">Riwayat Perubahan</span>
					</a>
				</li>
				<?php endif; ?>

				<?php if ($show_admin): ?>
				<li class="nav-item"><span class="nav-section-title">Administrasi</span></li>
				<?php endif; ?>
				<?php if ($can('users')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'users' ? 'active' : '' ?>" href="<?= base_url('users') ?>">
						<span class="nav-link-icon"><i class="bi bi-people"></i></span>
						<span class="nav-link-title">Manajemen User</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('notification-groups')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'notification-groups' ? 'active' : '' ?>" href="<?= base_url('notification-groups') ?>">
						<span class="nav-link-icon"><i class="bi bi-bell"></i></span>
						<span class="nav-link-title">Grup Notifikasi</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($can('reports')): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'reports' ? 'active' : '' ?>" href="<?= base_url('reports/import') ?>">
						<span class="nav-link-icon"><i class="bi bi-file-earmark-arrow-up"></i></span>
						<span class="nav-link-title">Import / Export</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($role === 'ADMIN'): ?>
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'access-control' ? 'active' : '' ?>" href="<?= base_url('access-control') ?>">
						<span class="nav-link-icon"><i class="bi bi-shield-lock"></i></span>
						<span class="nav-link-title">Hak Akses</span>
					</a>
				</li>
				<?php endif; ?>

				<li class="nav-item mt-2 border-top border-secondary-subtle pt-2">
					<a class="nav-link" href="<?= base_url('dokumentasi.html') ?>" target="_blank" rel="noopener">
						<span class="nav-link-icon"><i class="bi bi-book"></i></span>
						<span class="nav-link-title">Dokumentasi</span>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?= base_url('logout') ?>">
						<span class="nav-link-icon"><i class="bi bi-box-arrow-right"></i></span>
						<span class="nav-link-title">Keluar</span>
					</a>
				</li>
			</ul>
		</div>
	</div>
</aside>

<div class="page-wrapper">
	<div class="page-header d-print-none">
		<div class="container-fluid">
			<div class="row g-2 align-items-center">
				<div class="col">
					<a href="<?= base_url('dashboard') ?>">
						<img src="<?= base_url('assets/images/atambah-logo.png') ?>" alt="Atambah" class="page-header-logo">
					</a>
				</div>
				<div class="col-auto">
					<?php
						$user_name = $logged_in_user['name'] ?? '';
						$name_parts = $user_name !== '' ? preg_split('/\s+/', trim($user_name)) : array();
						$initials = '';
						if (!empty($name_parts)) {
							$initials = mb_strtoupper(mb_substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? mb_substr($name_parts[1], 0, 1) : ''));
						}
					?>
					<div class="d-flex align-items-center gap-2">
						<div class="text-end d-none d-sm-block">
							<div class="fw-semibold"><?= htmlspecialchars($user_name) ?></div>
							<div class="text-secondary small"><?= isset($logged_in_user['role']) ? htmlspecialchars($logged_in_user['role']) : '' ?></div>
						</div>
						<?php if ($initials !== ''): ?>
							<div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="page-body">
		<div class="container-xl">
			<?php $flash_success = $this->session->flashdata('success'); $flash_error = $this->session->flashdata('error'); ?>
			<?php if ($flash_success): ?>
				<div class="alert alert-success alert-dismissible fade show"><?= $flash_success ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
			<?php endif; ?>
			<?php if ($flash_error): ?>
				<div class="alert alert-danger alert-dismissible fade show"><?= $flash_error ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
			<?php endif; ?>
			<?php $pending_notify_count = $pending_notify_count ?? 0; ?>
			<?php if ($pending_notify_count > 0 && $can('price-update')): ?>
				<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2" id="pendingNotifyBanner">
					<div><i class="bi bi-envelope-exclamation-fill me-1"></i><span id="pendingNotifyCount"><?= $pending_notify_count ?></span> perubahan harga menunggu dikirim notifikasi.</div>
					<div class="d-flex gap-2">
						<button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#pendingNotifyModal"><i class="bi bi-eye me-1"></i>Lihat Detail</button>
						<form method="post" action="<?= base_url('price-update/send-pending') ?>" class="d-inline">
							<button class="btn btn-sm btn-warning" onclick="return confirm('Kirim satu email notifikasi untuk seluruh perubahan harga yang menunggu sekarang?')"><i class="bi bi-send-fill me-1"></i>Kirim Notifikasi Sekarang</button>
						</form>
					</div>
				</div>

				<div class="modal fade" id="pendingNotifyModal" tabindex="-1">
					<div class="modal-dialog modal-lg modal-dialog-scrollable">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Perubahan Harga Menunggu Notifikasi</h5>
								<button class="btn-close" data-bs-dismiss="modal"></button>
							</div>
							<div class="modal-body" id="pendingNotifyModalBody">Memuat...</div>
						</div>
					</div>
				</div>
				<script>
				document.addEventListener('DOMContentLoaded', function () {
					var modalEl = document.getElementById('pendingNotifyModal');
					var body = document.getElementById('pendingNotifyModalBody');
					var countEl = document.getElementById('pendingNotifyCount');
					var bannerEl = document.getElementById('pendingNotifyBanner');
					var loaded = false;

					function loadList() {
						body.innerHTML = 'Memuat...';
						fetch("<?= base_url('price-update/pending-list') ?>")
							.then(function (r) { return r.text(); })
							.then(function (html) { body.innerHTML = html; loaded = true; })
							.catch(function () { body.innerHTML = '<div class="text-danger">Gagal memuat data.</div>'; });
					}

					modalEl.addEventListener('show.bs.modal', function () {
						if (!loaded) loadList();
					});

					body.addEventListener('click', function (e) {
						var btn = e.target.closest('.btn-cancel-pending');
						if (!btn) return;

						if (!confirm('Batalkan notifikasi untuk "' + btn.dataset.productName + '"? Perubahan harga ini tidak akan ikut dikirim.')) return;

						btn.disabled = true;
						fetch("<?= base_url('price-update/cancel-pending/') ?>" + btn.dataset.batchId, {
							method: 'POST',
							headers: { 'X-Requested-With': 'XMLHttpRequest' }
						})
						.then(function (r) { return r.json(); })
						.then(function (d) {
							if (!d.success) {
								alert('Gagal membatalkan.');
								btn.disabled = false;
								return;
							}
							var row = btn.closest('tr');
							if (row) row.remove();
							if (countEl) countEl.textContent = d.pending_count;
							if (d.pending_count <= 0) {
								if (bannerEl) bannerEl.remove();
							}
							if (!body.querySelector('tbody tr')) {
								body.innerHTML = '<div class="text-center text-muted py-3">Tidak ada perubahan harga yang menunggu dikirim.</div>';
							}
						})
						.catch(function () {
							alert('Gagal membatalkan. Coba lagi.');
							btn.disabled = false;
						});
					});
				});
				</script>
			<?php endif; ?>
