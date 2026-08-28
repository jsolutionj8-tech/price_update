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
		<h1 class="navbar-brand navbar-brand-autodark">
			<a href="<?= base_url('dashboard') ?>" class="d-flex align-items-center gap-2 text-decoration-none">
				<span class="navbar-brand-mark"><i class="bi bi-tags-fill"></i></span>
				<span class="fw-bold">Update Harga</span>
			</a>
		</h1>
		<div class="collapse navbar-collapse" id="sidebar-menu">
			<ul class="navbar-nav pt-lg-3">
				<li class="nav-item">
					<a class="nav-link <?= $seg === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
						<span class="nav-link-icon"><i class="bi bi-speedometer2"></i></span>
						<span class="nav-link-title">Dashboard</span>
					</a>
				</li>

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
					<h2 class="page-title"><?= isset($title) ? htmlspecialchars($title) : '' ?></h2>
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
				<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
					<div><i class="bi bi-envelope-exclamation-fill me-1"></i><?= $pending_notify_count ?> perubahan harga menunggu dikirim notifikasi.</div>
					<form method="post" action="<?= base_url('price-update/send-pending') ?>" class="d-inline">
						<button class="btn btn-sm btn-warning" onclick="return confirm('Kirim satu email notifikasi untuk <?= $pending_notify_count ?> perubahan harga sekarang?')"><i class="bi bi-send-fill"></i> Kirim Notifikasi Sekarang</button>
					</form>
				</div>
			<?php endif; ?>
