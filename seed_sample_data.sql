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

-- Master menu (dipakai halaman Administrasi -> Hak Akses, lihat application/models/Menu_access_model.php)
INSERT INTO menus (menu_key, menu_label, menu_group, menu_icon, sort_order) VALUES
('products',            'Produk',              'Master Data',   'bi-box-seam',              10),
('brands',              'Brand',               'Master Data',   'bi-award',                 15),
('categories',          'Kategori Barang',     'Master Data',   'bi-tags',                  20),
('vendors',             'Vendor',              'Master Data',   'bi-truck',                 30),
('competitors',         'Kompetitor',          'Master Data',   'bi-shop-window',           40),
('costs',               'Master Biaya',        'Master Data',   'bi-cash-coin',             45),
('marketplaces',        'Sales Channel',       'Master Data',   'bi-bag-check',             50),
('price-update',        'Update Harga',        'Draft Pricing', 'bi-currency-exchange',     60),
('competitor-price',    'Harga Kompetitor',    'Draft Pricing', 'bi-graph-up-arrow',        70),
('price-history',       'Riwayat Perubahan',   'Draft Pricing', 'bi-clock-history',         80),
('users',               'Manajemen User',      'Administrasi',  'bi-people',                90),
('notification-groups', 'Grup Notifikasi',     'Administrasi',  'bi-bell',                  100),
('reports',             'Import / Export',     'Administrasi',  'bi-file-earmark-arrow-up', 110);

-- Hak akses default per role (ADMIN tidak perlu baris — selalu akses penuh).
-- EDITOR: semua menu kecuali Manajemen User & Grup Notifikasi. VIEWER: hanya Riwayat Perubahan.
INSERT INTO role_menu_access (role_id, menu_id, can_access)
SELECT r.id, m.id,
  CASE
    WHEN r.role_code = 'EDITOR' AND m.menu_key IN ('users','notification-groups') THEN 0
    WHEN r.role_code = 'EDITOR' THEN 1
    WHEN r.role_code = 'VIEWER' AND m.menu_key = 'price-history' THEN 1
    ELSE 0
  END
FROM roles r
CROSS JOIN menus m
WHERE r.role_code IN ('EDITOR', 'VIEWER');

-- Master Marketplace / kanal penjualan. Kode OFFLINE WAJIB ada karena dipakai
-- sebagai acuan perhitungan Markup%/Margin% (lihat Price_update controller & Price_calculator).
INSERT INTO price_channels (channel_code, channel_name, sort_order, is_active) VALUES
('OFFLINE', 'Toko Offline', 10, 1),
('WEBSITE', 'Website Resmi', 20, 1),
('TOKOPEDIA', 'Tokopedia', 30, 1),
('SHOPEE', 'Shopee', 40, 1),
('LAZADA', 'Lazada', 50, 1);

-- Master Biaya contoh — silakan sesuaikan lewat menu Master Data > Master Biaya, lalu kaitkan
-- ke sales channel yang relevan lewat menu Master Data > Sales Channel.
INSERT INTO costs (cost_name, amount, is_active) VALUES
('Biaya Admin', 5000.00, 1),
('Biaya Komisi Marketplace', 15000.00, 1),
('Biaya Packing', 2000.00, 1);

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
