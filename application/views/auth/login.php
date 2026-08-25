<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<title>Login - Sistem Update Harga</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
	<style>
		body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: linear-gradient(135deg, #1F3864 0%, #2E74B5 100%); }
		.login-mark {
			width: 3.25rem; height: 3.25rem; border-radius: .8rem; background: rgba(255,255,255,.14);
			display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff;
		}
	</style>
</head>
<body class="d-flex flex-column">
	<div class="page page-center">
		<div class="container container-tight py-4">
			<div class="text-center mb-4">
				<span class="login-mark"><i class="bi bi-tags-fill"></i></span>
				<h1 class="h2 text-white mt-3 mb-1">Sistem Update Harga</h1>
				<div class="text-white-50">Update Harga Produk &amp; Notifikasi Email Otomatis</div>
			</div>
			<div class="card card-md">
				<div class="card-body">
					<h2 class="h3 text-center mb-4">Masuk ke akun Anda</h2>
					<?php if (!empty($error)): ?>
						<div class="alert alert-danger py-2"><?= $error ?></div>
					<?php endif; ?>
					<form method="post" action="<?= base_url('auth/do_login') ?>" autocomplete="off">
						<div class="mb-3">
							<label class="form-label">Email</label>
							<input type="email" name="email" class="form-control" placeholder="nama@perusahaan.com" required autofocus>
						</div>
						<div class="mb-2">
							<label class="form-label">Password</label>
							<input type="password" name="password" class="form-control" placeholder="Kata sandi" required>
						</div>
						<div class="form-footer">
							<button type="submit" class="btn btn-primary w-100">Masuk</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
</body>
</html>
