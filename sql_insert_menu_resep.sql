-- SQL SCRIPT UNTUK INPUT MENU DAN RESEP BBC RESTO
-- Jalankan script ini untuk menambahkan menu baru dan resep

-- =====================================================
-- 1. BUAT MENU DINE IN BARU 
-- =====================================================

INSERT IGNORE INTO menu (nama_menu, kode_menu, harga_jual, jenis_menu_id, kategori_menu_id, status_aktif) VALUES
('Nasi Liwet Komplit', 'NL-001', 35000, 1, 1, 1),
('Ayam Goreng Kalasan', 'AY-001', 28000, 1, 1, 1),
('Gurame Bakar Sambal Dabu', 'IK-001', 45000, 1, 1, 1),
('Bebek Goreng Kremes', 'BE-001', 38000, 1, 1, 1),
('Sate Ayam (10 tusuk)', 'ST-001', 25000, 1, 1, 1),
('Sup Buntut Sapi', 'SP-001', 42000, 1, 1, 1),
('Es Teh Manis', 'MN-001', 8000, 1, 1, 1),
('Kopi Susu Gula Aren', 'MN-002', 15000, 1, 1, 1),
('Jus Alpukat', 'MN-003', 18000, 1, 1, 1);

-- =====================================================
-- 2. BUAT MENU CATERING BARU
-- =====================================================

INSERT IGNORE INTO menu (nama_menu, kode_menu, harga_jual, jenis_menu_id, kategori_menu_id, status_aktif) VALUES
('Catering Paket A (50 porsi)', 'CT-A', 2000000, 2, 2, 1),
('Catering Paket B (100 porsi)', 'CT-B', 3500000, 2, 2, 1);

-- =====================================================
-- 3. INPUT RESEP NASI BOX PAKET A
-- =====================================================

INSERT INTO resep_menu (menu_id, bahan_baku_id, jumlah, satuan_id, dikonfirmasi)
SELECT 
    m.id as menu_id,
    bb.id as bahan_baku_id,
    resep.jumlah,
    bb.satuan_id,
    1 as dikonfirmasi
FROM menu m
CROSS JOIN (
    SELECT 'Beras' as nama_bahan, 150 as jumlah UNION ALL
    SELECT 'Ayam Broiler', 100 UNION ALL
    SELECT 'Ikan Gurame', 80 UNION ALL
    SELECT 'Telur Ayam', 0.5 UNION ALL
    SELECT 'Minyak Goreng', 50 UNION ALL
    SELECT 'Bawang Merah', 25 UNION ALL
    SELECT 'Bawang Putih', 15 UNION ALL
    SELECT 'Cabai Merah Keriting', 20 UNION ALL
    SELECT 'Tomat', 30 UNION ALL
    SELECT 'Garam Dapur', 5 UNION ALL
    SELECT 'Kemangi', 0.1 UNION ALL
    SELECT 'Kotak Catering Mika', 1
) resep
JOIN bahan_baku bb ON bb.nama_bahan = resep.nama_bahan
WHERE m.kode_menu = 'NB-A'
ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah);

-- =====================================================
-- 4. INPUT RESEP NASI BOX PAKET B
-- =====================================================

INSERT INTO resep_menu (menu_id, bahan_baku_id, jumlah, satuan_id, dikonfirmasi)
SELECT 
    m.id as menu_id,
    bb.id as bahan_baku_id,
    resep.jumlah,
    bb.satuan_id,
    1 as dikonfirmasi
FROM menu m
CROSS JOIN (
    SELECT 'Beras' as nama_bahan, 150 as jumlah UNION ALL
    SELECT 'Ayam Broiler', 100 UNION ALL
    SELECT 'Tempe', 0.5 UNION ALL
    SELECT 'Tahu Putih', 0.5 UNION ALL
    SELECT 'Minyak Goreng', 40 UNION ALL
    SELECT 'Bawang Merah', 20 UNION ALL
    SELECT 'Bawang Putih', 10 UNION ALL
    SELECT 'Cabai Merah Keriting', 15 UNION ALL
    SELECT 'Tomat', 25 UNION ALL
    SELECT 'Garam Dapur', 4 UNION ALL
    SELECT 'Kemangi', 0.1 UNION ALL
    SELECT 'Kotak Catering Mika', 1
) resep
JOIN bahan_baku bb ON bb.nama_bahan = resep.nama_bahan
WHERE m.kode_menu = 'NB-B'
ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah);

-- =====================================================
-- 5. INPUT RESEP NASI BOX PAKET C
-- =====================================================

INSERT INTO resep_menu (menu_id, bahan_baku_id, jumlah, satuan_id, dikonfirmasi)
SELECT 
    m.id as menu_id,
    bb.id as bahan_baku_id,
    resep.jumlah,
    bb.satuan_id,
    1 as dikonfirmasi
FROM menu m
CROSS JOIN (
    SELECT 'Beras' as nama_bahan, 150 as jumlah UNION ALL
    SELECT 'Ayam Broiler', 80 UNION ALL
    SELECT 'Tempe', 0.3 UNION ALL
    SELECT 'Minyak Goreng', 35 UNION ALL
    SELECT 'Bawang Merah', 15 UNION ALL
    SELECT 'Bawang Putih', 8 UNION ALL
    SELECT 'Cabai Merah Keriting', 10 UNION ALL
    SELECT 'Tomat', 20 UNION ALL
    SELECT 'Garam Dapur', 3 UNION ALL
    SELECT 'Kemangi', 0.08 UNION ALL
    SELECT 'Kotak Catering Mika', 1
) resep
JOIN bahan_baku bb ON bb.nama_bahan = resep.nama_bahan
WHERE m.kode_menu = 'NB-C'
ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah);

-- =====================================================
-- 6. INPUT RESEP NASI BOX PAKET D
-- =====================================================

INSERT INTO resep_menu (menu_id, bahan_baku_id, jumlah, satuan_id, dikonfirmasi)
SELECT 
    m.id as menu_id,
    bb.id as bahan_baku_id,
    resep.jumlah,
    bb.satuan_id,
    1 as dikonfirmasi
FROM menu m
CROSS JOIN (
    SELECT 'Beras' as nama_bahan, 150 as jumlah UNION ALL
    SELECT 'Ayam Broiler', 70 UNION ALL
    SELECT 'Tempe', 0.25 UNION ALL
    SELECT 'Minyak Goreng', 30 UNION ALL
    SELECT 'Bawang Merah', 12 UNION ALL
    SELECT 'Bawang Putih', 6 UNION ALL
    SELECT 'Cabai Merah Keriting', 8 UNION ALL
    SELECT 'Garam Dapur', 2.5 UNION ALL
    SELECT 'Kemangi', 0.05 UNION ALL
    SELECT 'Kotak Catering Mika', 1
) resep
JOIN bahan_baku bb ON bb.nama_bahan = resep.nama_bahan
WHERE m.kode_menu = 'NB-D'
ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah);

-- =====================================================
-- 7. INPUT RESEP NASI BOX PAKET E
-- =====================================================

INSERT INTO resep_menu (menu_id, bahan_baku_id, jumlah, satuan_id, dikonfirmasi)
SELECT 
    m.id as menu_id,
    bb.id as bahan_baku_id,
    resep.jumlah,
    bb.satuan_id,
    1 as dikonfirmasi
FROM menu m
CROSS JOIN (
    SELECT 'Beras' as nama_bahan, 150 as jumlah UNION ALL
    SELECT 'Ayam Broiler', 60 UNION ALL
    SELECT 'Minyak Goreng', 25 UNION ALL
    SELECT 'Bawang Merah', 10 UNION ALL
    SELECT 'Bawang Putih', 5 UNION ALL
    SELECT 'Garam Dapur', 2 UNION ALL
    SELECT 'Kemangi', 0.03 UNION ALL
    SELECT 'Kotak Catering Mika', 1
) resep
JOIN bahan_baku bb ON bb.nama_bahan = resep.nama_bahan
WHERE m.kode_menu = 'NB-E'
ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah);

-- =====================================================
-- 8. RESEP MENU DINE IN - Nasi Liwet Komplit
-- =====================================================

INSERT INTO resep_menu (menu_id, bahan_baku_id, jumlah, satuan_id, dikonfirmasi)
SELECT 
    m.id as menu_id,
    bb.id as bahan_baku_id,
    resep.jumlah,
    bb.satuan_id,
    1 as dikonfirmasi
FROM menu m
CROSS JOIN (
    SELECT 'Beras' as nama_bahan, 200 as jumlah UNION ALL
    SELECT 'Santan Kelapa Instan', 300 UNION ALL
    SELECT 'Ayam Kampung', 150 UNION ALL
    SELECT 'Daun Salam', 5 UNION ALL
    SELECT 'Serai', 10 UNION ALL
    SELECT 'Lengkuas', 15 UNION ALL
    SELECT 'Bawang Merah', 20 UNION ALL
    SELECT 'Bawang Putih', 15 UNION ALL
    SELECT 'Garam Dapur', 8
) resep
JOIN bahan_baku bb ON bb.nama_bahan = resep.nama_bahan
WHERE m.kode_menu = 'NL-001'
ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah);

-- =====================================================
-- 9. RESEP MENU DINE IN - Ayam Goreng Kalasan  
-- =====================================================

INSERT INTO resep_menu (menu_id, bahan_baku_id, jumlah, satuan_id, dikonfirmasi)
SELECT 
    m.id as menu_id,
    bb.id as bahan_baku_id,
    resep.jumlah,
    bb.satuan_id,
    1 as dikonfirmasi
FROM menu m
CROSS JOIN (
    SELECT 'Ayam Broiler' as nama_bahan, 250 as jumlah UNION ALL
    SELECT 'Kunyit', 10 UNION ALL
    SELECT 'Lengkuas', 8 UNION ALL
    SELECT 'Daun Salam', 3 UNION ALL
    SELECT 'Gula Merah', 15 UNION ALL
    SELECT 'Garam Dapur', 5 UNION ALL
    SELECT 'Minyak Goreng', 100
) resep
JOIN bahan_baku bb ON bb.nama_bahan = resep.nama_bahan
WHERE m.kode_menu = 'AY-001'
ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah);

-- =====================================================
-- LANJUTKAN DENGAN QUERY SISANYA...
-- (Script ini bisa dilanjutkan untuk semua menu lainnya)
-- =====================================================

-- Cek hasil input
SELECT 
    m.nama_menu,
    m.kode_menu,
    COUNT(rm.id) as jumlah_bahan,
    GROUP_CONCAT(CONCAT(bb.nama_bahan, ' (', rm.jumlah, ' ', s.singkatan, ')') SEPARATOR ', ') as resep_detail
FROM menu m
LEFT JOIN resep_menu rm ON m.id = rm.menu_id
LEFT JOIN bahan_baku bb ON rm.bahan_baku_id = bb.id
LEFT JOIN satuan s ON bb.satuan_id = s.id
WHERE m.kode_menu IN ('NB-A', 'NB-B', 'NB-C', 'NB-D', 'NB-E', 'NL-001', 'AY-001')
GROUP BY m.id
ORDER BY m.kode_menu;