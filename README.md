# Aplikasi Update Harga Produk & Notifikasi Email Otomatis

PHP CodeIgniter 3 + MySQL 8.0+. Dokumentasi lengkap ada di file `dokumentasi.html`
(buka langsung di browser).

## Quick Start
1. `composer install`
2. Buat database lalu import `database_schema.sql`, kemudian (opsional) `seed_sample_data.sql`
3. Salin konfigurasi: sesuaikan `application/config/database.php` dan `application/config/email.php`
4. Set `base_url` pada `application/config/config.php`
5. Arahkan document root web server ke folder ini, pastikan `.htaccess` aktif (mod_rewrite)
6. Login: admin@example.com / password (jika memakai seed_sample_data.sql) — segera ganti password

Lihat `dokumentasi.html` untuk instalasi detail, arsitektur, dan panduan penggunaan.
