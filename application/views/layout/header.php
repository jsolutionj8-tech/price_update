<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= isset($title) ? $title . ' - ' : '' ?>Sistem Update Harga</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
	<style>
		:root { --navy: #1F3864; --accent: #2E74B5; }
		body { background:#F4F6F9; font-family: 'Segoe UI', Arial, sans-serif; }
		.app-sidebar { background: var(--navy); min-height: 100vh; width: 230px; position: fixed; top:0; left:0; }
		.app-sidebar a { color: #cfd9e8; display:block; padding:.65rem 1.1rem; text-decoration:none; font-size:.92rem; }
		.app-sidebar a.active, .app-sidebar a:hover { background: var(--accent); color:#fff; }
		.app-sidebar .brand { color:#fff; font-weight:700; padding:1.1rem; border-bottom:1px solid rgba(255,255,255,.15); font-size:1.05rem; }
		.app-main { margin-left:230px; padding:1.6rem 2rem; }
		.app-topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; }
		.card-stat { border:none; border-radius:.6rem; box-shadow:0 2px 8px rgba(0,0,0,.06); }
		.table thead { background: var(--navy); color:#fff; }
		.btn-primary { background: var(--accent); border-color: var(--accent); }
		.btn-primary:hover { background: var(--navy); border-color: var(--navy); }
	</style>
</head>
<body>
