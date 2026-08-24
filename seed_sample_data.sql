-- =====================================================================
-- DATA CONTOH (opsional) — sesuai sampel "Product Price Change List"
-- Jalankan SETELAH database_schema.sql
-- =====================================================================
USE db_price_update;

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
