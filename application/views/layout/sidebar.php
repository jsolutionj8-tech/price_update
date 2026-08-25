<?php $seg = $this->uri->segment(1) ?: 'dashboard'; $role = $logged_in_user['role'] ?? ''; ?>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="app-sidebar" id="appSidebar">
	<div class="brand"><i class="bi bi-tags-fill"></i> Update Harga</div>
	<nav class="pt-2">
		<a href="<?= base_url('dashboard') ?>" class="<?= $seg === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>

		<div class="nav-section-title">Master Data</div>
		<a href="<?= base_url('products') ?>" class="<?= $seg === 'products' ? 'active' : '' ?>"><i class="bi bi-box-seam me-2"></i>Produk</a>
		<a href="<?= base_url('categories') ?>" class="<?= $seg === 'categories' ? 'active' : '' ?>"><i class="bi bi-tags me-2"></i>Kategori Barang</a>
		<a href="<?= base_url('vendors') ?>" class="<?= $seg === 'vendors' ? 'active' : '' ?>"><i class="bi bi-truck me-2"></i>Vendor</a>
		<a href="<?= base_url('competitors') ?>" class="<?= $seg === 'competitors' ? 'active' : '' ?>"><i class="bi bi-shop-window me-2"></i>Kompetitor</a>
		<a href="<?= base_url('marketplaces') ?>" class="<?= $seg === 'marketplaces' ? 'active' : '' ?>"><i class="bi bi-bag-check me-2"></i>Marketplace</a>

		<div class="nav-section-title">Transaksi</div>
		<a href="<?= base_url('price-update') ?>" class="<?= $seg === 'price-update' ? 'active' : '' ?>"><i class="bi bi-currency-exchange me-2"></i>Update Harga</a>
		<a href="<?= base_url('competitor-price') ?>" class="<?= $seg === 'competitor-price' ? 'active' : '' ?>"><i class="bi bi-graph-up-arrow me-2"></i>Harga Kompetitor</a>
		<a href="<?= base_url('price-history') ?>" class="<?= $seg === 'price-history' ? 'active' : '' ?>"><i class="bi bi-clock-history me-2"></i>Riwayat Perubahan</a>

		<?php if (in_array($role, array('ADMIN', 'EDITOR'), TRUE)): ?>
		<div class="nav-section-title">Administrasi</div>
		<?php endif; ?>
		<?php if ($role === 'ADMIN'): ?>
		<a href="<?= base_url('users') ?>" class="<?= $seg === 'users' ? 'active' : '' ?>"><i class="bi bi-people me-2"></i>Manajemen User</a>
		<a href="<?= base_url('notification-groups') ?>" class="<?= $seg === 'notification-groups' ? 'active' : '' ?>"><i class="bi bi-bell me-2"></i>Grup Notifikasi</a>
		<?php endif; ?>
		<?php if (in_array($role, array('ADMIN', 'EDITOR'), TRUE)): ?>
		<a href="<?= base_url('reports/import') ?>" class="<?= $seg === 'reports' ? 'active' : '' ?>"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import / Export</a>
		<?php endif; ?>
		<a href="<?= base_url('logout') ?>" class="mt-3 border-top border-secondary-subtle"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
	</nav>
</div>
<div class="app-main">
	<div class="app-topbar">
		<div class="d-flex align-items-center gap-2">
			<button type="button" class="btn-menu-toggle" id="sidebarToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button>
			<h4 class="mb-0 fw-bold text-dark"><?= isset($title) ? $title : '' ?></h4>
		</div>
		<div class="text-end">
			<div class="fw-semibold"><?= isset($logged_in_user['name']) ? htmlspecialchars($logged_in_user['name']) : '' ?></div>
			<small class="text-muted"><?= isset($logged_in_user['role']) ? htmlspecialchars($logged_in_user['role']) : '' ?></small>
		</div>
	</div>
	<?php $flash_success = $this->session->flashdata('success'); $flash_error = $this->session->flashdata('error'); ?>
	<?php if ($flash_success): ?>
		<div class="alert alert-success alert-dismissible fade show"><?= $flash_success ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
	<?php endif; ?>
	<?php if ($flash_error): ?>
		<div class="alert alert-danger alert-dismissible fade show"><?= $flash_error ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
	<?php endif; ?>
	<?php $pending_notify_count = $pending_notify_count ?? 0; ?>
	<?php if ($pending_notify_count > 0 && in_array($role, array('ADMIN', 'EDITOR'), TRUE)): ?>
		<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
			<div><i class="bi bi-envelope-exclamation-fill me-1"></i><?= $pending_notify_count ?> perubahan harga menunggu dikirim notifikasi.</div>
			<form method="post" action="<?= base_url('price-update/send-pending') ?>" class="d-inline">
				<button class="btn btn-sm btn-warning" onclick="return confirm('Kirim satu email notifikasi untuk <?= $pending_notify_count ?> perubahan harga sekarang?')"><i class="bi bi-send-fill"></i> Kirim Notifikasi Sekarang</button>
			</form>
		</div>
	<?php endif; ?>
