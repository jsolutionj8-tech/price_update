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
		.app-sidebar { background: var(--navy); width: 230px; position: fixed; top:0; left:0; bottom:0; z-index:1045; overflow-y:auto; -webkit-overflow-scrolling:touch; transition: transform .25s ease; }
		.app-sidebar a { color: #cfd9e8; display:block; padding:.65rem 1.1rem; text-decoration:none; font-size:.92rem; }
		.app-sidebar a.active, .app-sidebar a:hover { background: var(--accent); color:#fff; }
		.app-sidebar .brand { color:#fff; font-weight:700; padding:1.1rem; border-bottom:1px solid rgba(255,255,255,.15); font-size:1.05rem; }
		.app-sidebar .nav-section-title { color:#8fa3c4; text-transform:uppercase; font-size:.72rem; letter-spacing:.06em; font-weight:600; padding:1rem 1.1rem .35rem; }
		.app-main { margin-left:230px; padding:1.6rem 2rem; transition: margin-left .25s ease; }
		.app-topbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem; margin-bottom:1.4rem; position:sticky; top:0; background:#F4F6F9; z-index:5; padding:.4rem 0; }
		.card-stat { border:none; border-radius:.6rem; box-shadow:0 2px 8px rgba(0,0,0,.06); }
		.table thead { background: var(--navy); color:#fff; }
		.btn-primary { background: var(--accent); border-color: var(--accent); }
		.btn-primary:hover { background: var(--navy); border-color: var(--navy); }
		.btn-menu-toggle { display:none; background:#fff; border:1px solid #d7dee8; border-radius:.4rem; width:38px; height:38px; align-items:center; justify-content:center; font-size:1.2rem; color:var(--navy); flex:none; }
		.sidebar-backdrop { display:none; position:fixed; inset:0; background:rgba(15,25,45,.45); z-index:1040; }
		.sidebar-backdrop.show { display:block; }

		@media (max-width: 991.98px) {
			.app-sidebar { transform: translateX(-100%); box-shadow:0 0 28px rgba(0,0,0,.3); }
			.app-sidebar.show { transform: translateX(0); }
			.app-main { margin-left:0; padding:1.1rem 1.1rem 2rem; }
			.btn-menu-toggle { display:inline-flex; }
		}
		@media (max-width: 575.98px) {
			.app-main { padding:1rem .75rem 2rem; }
			.app-topbar { align-items:flex-start; }
		}
	</style>
</head>
<body>
