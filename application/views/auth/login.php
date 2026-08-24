<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<title>Login - Sistem Update Harga</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body { background: linear-gradient(135deg,#1F3864,#2E74B5); min-height:100vh; display:flex; align-items:center; }
		.login-card { max-width:400px; margin:auto; border:none; border-radius:.8rem; box-shadow:0 10px 30px rgba(0,0,0,.25); }
	</style>
</head>
<body>
	<div class="card login-card p-4">
		<div class="text-center mb-3">
			<i class="bi bi-tags-fill" style="font-size:2rem;color:#2E74B5;"></i>
			<h5 class="fw-bold mt-2 mb-0">Sistem Update Harga</h5>
			<small class="text-muted">Silakan login untuk melanjutkan</small>
		</div>
		<?php if (!empty($error)): ?>
			<div class="alert alert-danger py-2"><?= $error ?></div>
		<?php endif; ?>
		<form method="post" action="<?= base_url('auth/do_login') ?>">
			<div class="mb-3">
				<label class="form-label">Email</label>
				<input type="email" name="email" class="form-control" required autofocus>
			</div>
			<div class="mb-3">
				<label class="form-label">Password</label>
				<input type="password" name="password" class="form-control" required>
			</div>
			<button type="submit" class="btn btn-primary w-100">Masuk</button>
		</form>
	</div>
</body>
</html>
