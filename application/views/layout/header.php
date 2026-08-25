<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= isset($title) ? $title . ' - ' : '' ?>Sistem Update Harga</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
	<style>
		body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

		/* Brand mark + section titles inside the vertical sidebar */
		.navbar-brand-mark {
			width: 2rem; height: 2rem; border-radius: .55rem; background: rgba(255,255,255,.14);
			display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex: none;
		}
		.navbar-vertical .nav-section-title {
			display: block; color: #7f93b8; text-transform: uppercase; font-size: .68rem;
			letter-spacing: .08em; font-weight: 700; padding: 1rem 1rem .35rem;
		}

		/* Logged-in user chip in the page header */
		.user-avatar {
			width: 2.25rem; height: 2.25rem; border-radius: 50%; background: var(--tblr-primary, #2E74B5); color: #fff;
			display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex: none;
		}

		/* Dashboard stat tiles */
		.stat-icon {
			width: 2.75rem; height: 2.75rem; border-radius: .65rem; display: inline-flex; align-items: center;
			justify-content: center; font-size: 1.2rem; flex: none;
		}
		.stat-icon.tone-accent { background: rgba(46,116,181,.12); color: var(--tblr-primary, #2E74B5); }
		.stat-icon.tone-good { background: rgba(45,181,105,.14); color: #2fb344; }
		.stat-icon.tone-bad { background: rgba(214,44,64,.12); color: #d63939; }

		/* Wider content area for data-heavy tables */
		.page-body > .container-xl { max-width: 100%; }
	</style>
</head>
<body>
<div class="page">
