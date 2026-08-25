-- =====================================================================
-- DATA CONTOH (opsional) — sesuai sampel "Product Price Change List"
-- Jalankan SETELAH database_schema.sql
-- =====================================================================
USE db_price_update;

-- Role dasar aplikasi (wajib ada sebelum insert user, lihat application/libraries/Auth_lib.php)
INSERT INTO roles (role_code, role_name, description) VALUES
('ADMIN', 'Administrator', 'Akses penuh: seluruh modul termasuk manajemen user & grup notifikasi'),
('EDITOR', 'Editor', 'Mengelola produk, kompetitor, marketplace, dan update harga'),
('VIEWER', 'Viewer', 'Hanya dapat melihat dashboard, riwayat perubahan, dan laporan');

-- Master Marketplace / kanal penjualan. Kode OFFLINE WAJIB ada karena dipakai
-- sebagai acuan perhitungan Markup%/Margin% (lihat Price_update controller & Price_calculator).
INSERT INTO price_channels (channel_code, channel_name, sort_order, is_active) VALUES
('OFFLINE', 'Toko Offline', 10, 1),
('WEBSITE', 'Website Resmi', 20, 1),
('TOKOPEDIA', 'Tokopedia', 30, 1),
('SHOPEE', 'Shopee', 40, 1),
('LAZADA', 'Lazada', 50, 1);

-- Master Kompetitor contoh — silakan ganti nama sesuai kompetitor riil melalui menu Master > Kompetitor.
INSERT INTO competitors (competitor_code, competitor_name, website_url, is_active) VALUES
('KOMPETITOR_A', 'Kompetitor A', NULL, 1),
('KOMPETITOR_B', 'Kompetitor B', NULL, 1),
('KOMPETITOR_C', 'Kompetitor C', NULL, 1);

INSERT INTO brands (brand_name) VALUES ('Espolon');

INSERT INTO vendors (vendor_code, vendor_name) VALUES
('VENDOR-A','Vendor A'), ('VENDOR-B','Vendor B'), ('VENDOR-C','Vendor C');

INSERT INTO products (product_code, product_name, brand_id) VALUES
('25021270202','Espolon Blanco Tequila 750mL', 1),
('25021270202-2','Espolon Blanco Tequila 750mL (di SKU ada -2)', 1),
('25041273802','Espolon Reposado Tequila 750mL', 1);

-- Akun admin pertama — email: admin@example.com / password: password
-- GANTI PASSWORD INI SEGERA SETELAH LOGIN PERTAMA KALI.
INSERT INTO users (role_id, full_name, email, password_hash, is_active) VALUES
(1, 'Admin Utama', 'admin@example.com', '$2y$10$oRwpCD71wxLaQ73vQOxiauHpwE2Z6LsZzb9dO.fD2qu81fOfxP4Hy', 1);

-- Contoh grup notifikasi
INSERT INTO notification_groups (group_name, description, is_active) VALUES
('Manajemen', 'Menerima semua notifikasi perubahan harga di seluruh kanal', 1),
('Tim Online', 'Hanya menerima notifikasi perubahan harga kanal Online/Website/Marketplace', 1);
