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

		/* Rebrand warna Tabler ke identitas Atambah.com: biru -> navy navbar, kuning -> oranye logo (#E34F05,
		   diambil langsung dari pixel logo assets/images/atambah-logo.png). Tabler membaca warna tema lewat
		   variabel CSS ini di hampir semua komponennya (tombol, badge, link, border, dst), jadi override di
		   :root sudah cukup tanpa perlu ubah tiap kelas satu-satu. */
		:root {
			--tblr-primary: #3D5C6C;
			--tblr-primary-rgb: 61, 92, 108;
			--tblr-warning: #E34F05;
			--tblr-warning-rgb: 227, 79, 5;
			--tblr-yellow: #E34F05;
			--tblr-yellow-rgb: 227, 79, 5;
			--tblr-success: #E34F05;
			--tblr-success-rgb: 227, 79, 5;
			--tblr-green: #E34F05;
			--tblr-green-rgb: 227, 79, 5;
		}

		/* Badge .bg-success/.bg-warning ("Aktif"/"Sent"/"Pending"/dst dari status_badge()) hanya set warna
		   latar, bukan warna tulisan — defaultnya abu-abu (kurang kontras di atas oranye). Dipaksa putih. */
		.badge.bg-success, .badge.bg-warning { color: #fff; }

		/* Select2 (dropdown pencarian vendor/brand/kategori) pakai tema Bootstrap sendiri yang warna birunya
		   hardcode (bukan variabel), jadi tidak ikut ke-override otomatis di atas — disesuaikan manual di sini. */
		.select2-container--bootstrap-5.select2-container--open .select2-selection,
		.select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field:focus {
			border-color: #3D5C6C !important;
			box-shadow: 0 0 0 .25rem rgba(61, 92, 108, .25) !important;
		}
		.select2-container--bootstrap-5 .select2-dropdown { border-color: #3D5C6C !important; }
		.select2-container--bootstrap-5 .select2-results__option--selected,
		.select2-container--bootstrap-5 .select2-results__option[aria-selected="true"]:not(.select2-results__option--highlighted) {
			background-color: #3D5C6C !important;
		}

		/* Logo Atambah.com di header konten utama, menggantikan judul halaman ($title) */
		.page-header-logo { height: 2rem; width: auto; display: block; }
		.navbar-vertical .nav-section-title {
			display: block; color: #7f93b8; text-transform: uppercase; font-size: .68rem;
			letter-spacing: .08em; font-weight: 700; padding: 1rem 1rem .35rem;
		}

		/* Perbesar tulisan menu sidebar ~10% dari ukuran default Tabler */
		.navbar-vertical .nav-link { font-size: 1.1em; }

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
		.stat-icon.tone-good { background: rgba(227,79,5,.14); color: #E34F05; }
		.stat-icon.tone-bad { background: rgba(214,44,64,.12); color: #d63939; }

		/* Wider content area for data-heavy tables */
		.page-body > .container-xl { max-width: 100%; }

		/* Warna teal-biru navbar atambah.com — dipakai sbg latar sidebar navigasi supaya konsisten dgn branding. */
		.navbar-vertical.navbar[data-bs-theme="dark"] { background-color: #3D5C6C !important; }

		/* Latar biru atambah solid, dipakai di kartu Nama Produk & kartu Harga per Channel di Update Harga. */
		.bg-brand, .channel-price-card { background-color: #3D5C6C; }
		/* Markup positif biru brand — merah (.text-danger) menang saat minus. RRP sengaja tidak diwarnai. */
		.text-markup-positive { color: #3D5C6C; }

		/* Tab pilihan vendor di Update Harga: latar oranye penuh (identitas Atambah), tulisan putih tebal. */
		.nav-tabs .nav-link {
			background-color: #E34F05; color: #fff; font-weight: 700; border: none; margin-right: .35rem;
		}
		.nav-tabs .nav-link.active { background-color: #E34F05; color: #fff; }
	</style>
</head>
<body>
<div class="page">
