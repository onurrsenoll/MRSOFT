-- =====================================================
-- MR HASAR DANIŞMANLIK - TOPLU VERİTABANI DÜZELTMESİ
-- Bu SQL dosyasını phpMyAdmin'de çalıştırın
-- Tarih: 2026-01-28
-- =====================================================

-- =====================================================
-- 1. USERS TABLOSU DÜZELTMELERİ
-- =====================================================
ALTER TABLE users ADD COLUMN IF NOT EXISTS full_name VARCHAR(200) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_deleted TINYINT(1) DEFAULT 0;

UPDATE users SET full_name = name WHERE (full_name IS NULL OR full_name = '') AND name IS NOT NULL;
UPDATE users SET full_name = username WHERE (full_name IS NULL OR full_name = '');
UPDATE users SET password_hash = password WHERE password_hash IS NULL AND password IS NOT NULL;

-- =====================================================
-- 2. CASES TABLOSU DÜZELTMELERİ
-- =====================================================
ALTER TABLE cases ADD COLUMN IF NOT EXISTS davali_sirket_id INT NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS current_stage_id INT NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS sorumlu_user_id INT NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS assigned_lawyer_user_id INT NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS yonlendiren_id INT NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS source_type VARCHAR(50) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS source_name VARCHAR(200) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS sigorta_hasar_no VARCHAR(100) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS plaka VARCHAR(20) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS karsi_plaka VARCHAR(20) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS kaza_tarihi DATE NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS acilis_tarihi DATE NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS changed_by_user_id INT NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS is_deleted TINYINT(1) DEFAULT 0;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'AKTIF';
ALTER TABLE cases ADD COLUMN IF NOT EXISTS case_type VARCHAR(20) DEFAULT 'ADK';
ALTER TABLE cases ADD COLUMN IF NOT EXISTS dosya_no VARCHAR(50) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS tc_kimlik VARCHAR(11) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS ad_soyad VARCHAR(200) NULL;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS telefon VARCHAR(20) NULL;

-- Mevcut verileri kopyala (eğer farklı isimli sütunlar varsa)
UPDATE cases SET davali_sirket_id = insurance_company_id WHERE davali_sirket_id IS NULL AND insurance_company_id IS NOT NULL;
UPDATE cases SET current_stage_id = stage_id WHERE current_stage_id IS NULL AND stage_id IS NOT NULL;
UPDATE cases SET sorumlu_user_id = assigned_user_id WHERE sorumlu_user_id IS NULL AND assigned_user_id IS NOT NULL;
UPDATE cases SET sorumlu_user_id = user_id WHERE sorumlu_user_id IS NULL AND user_id IS NOT NULL;
UPDATE cases SET acilis_tarihi = created_at WHERE acilis_tarihi IS NULL;
UPDATE cases SET kaza_tarihi = accident_date WHERE kaza_tarihi IS NULL AND accident_date IS NOT NULL;
UPDATE cases SET tc_kimlik = client_tc WHERE tc_kimlik IS NULL AND client_tc IS NOT NULL;
UPDATE cases SET ad_soyad = client_name WHERE ad_soyad IS NULL AND client_name IS NOT NULL;
UPDATE cases SET telefon = client_phone WHERE telefon IS NULL AND client_phone IS NOT NULL;
UPDATE cases SET plaka = vehicle_plate WHERE plaka IS NULL AND vehicle_plate IS NOT NULL;

-- =====================================================
-- 3. SİGORTA ŞİRKETLERİ TABLOSU / VIEW
-- =====================================================
DROP VIEW IF EXISTS sigorta_sirketleri;
DROP TABLE IF EXISTS sigorta_sirketleri;

CREATE TABLE IF NOT EXISTS sigorta_sirketleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firma_adi VARCHAR(200) NOT NULL,
    durum TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Mevcut insurance_companies verilerini kopyala
INSERT IGNORE INTO sigorta_sirketleri (id, firma_adi, durum)
SELECT id, name, is_active FROM insurance_companies WHERE NOT EXISTS (SELECT 1 FROM sigorta_sirketleri);

-- Örnek veri ekle (eğer boşsa)
INSERT IGNORE INTO sigorta_sirketleri (firma_adi, durum) VALUES
('AXA SİGORTA', 1),
('ALLİANZ SİGORTA', 1),
('ANADOLU SİGORTA', 1),
('AKSİGORTA', 1),
('HDI SİGORTA', 1),
('MAPFRE SİGORTA', 1),
('SOMPO SİGORTA', 1),
('TÜRK NİPPON SİGORTA', 1),
('GÜNEŞ SİGORTA', 1),
('EUREKO SİGORTA', 1);

-- =====================================================
-- 4. AŞAMA DURUMLARI TABLOSU / VIEW
-- =====================================================
DROP VIEW IF EXISTS asama_durumlari;
DROP TABLE IF EXISTS asama_durumlari;

CREATE TABLE IF NOT EXISTS asama_durumlari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asama_adi VARCHAR(100) NOT NULL,
    durum TINYINT(1) DEFAULT 1,
    sira INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Mevcut stages verilerini kopyala
INSERT IGNORE INTO asama_durumlari (id, asama_adi, durum, sira)
SELECT id, name, is_active, sort_order FROM stages WHERE NOT EXISTS (SELECT 1 FROM asama_durumlari);

-- Örnek veri ekle (eğer boşsa)
INSERT IGNORE INTO asama_durumlari (asama_adi, durum, sira) VALUES
('BAŞVURU ALINDI', 1, 1),
('EVRAK BEKLENİYOR', 1, 2),
('İNCELEMEDE', 1, 3),
('TAHKİM BAŞVURUSU', 1, 4),
('TAHKİM DEVAM', 1, 5),
('MAHKEME AŞAMASINDA', 1, 6),
('SONUÇLANDI - OLUMLU', 1, 7),
('SONUÇLANDI - OLUMSUZ', 1, 8),
('İPTAL EDİLDİ', 1, 9);

-- =====================================================
-- 5. YÖNLENDİRENLER TABLOSU
-- =====================================================
DROP VIEW IF EXISTS yonlendirenler;
DROP TABLE IF EXISTS yonlendirenler;

CREATE TABLE IF NOT EXISTS yonlendirenler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(200) NOT NULL,
    telefon VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    adk_ucret DECIMAL(10,2) DEFAULT 0,
    bh_ucret DECIMAL(10,2) DEFAULT 0,
    mdk_ucret DECIMAL(10,2) DEFAULT 0,
    diger_ucret DECIMAL(10,2) DEFAULT 0,
    durum TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Mevcut referrers verilerini kopyala
INSERT IGNORE INTO yonlendirenler (id, ad_soyad, telefon, durum)
SELECT id, name, phone, is_active FROM referrers WHERE NOT EXISTS (SELECT 1 FROM yonlendirenler);

-- =====================================================
-- 6. CRM RECORDS TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS crm_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(200) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    tc_kimlik VARCHAR(11) NULL,
    konu VARCHAR(50) NULL,
    sorumlu_user_id INT NULL,
    sonraki_tarih DATETIME NULL,
    notes TEXT NULL,
    durum VARCHAR(20) DEFAULT 'TAKIPTE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- crm_leads varsa verileri kopyala
INSERT IGNORE INTO crm_records (id, ad_soyad, telefon, tc_kimlik, sorumlu_user_id, notes, durum, created_at)
SELECT id,
       COALESCE(name, full_name, 'BİLİNMİYOR'),
       COALESCE(phone, ''),
       tc_no,
       assigned_user_id,
       notes,
       COALESCE(status, 'TAKIPTE'),
       created_at
FROM crm_leads WHERE NOT EXISTS (SELECT 1 FROM crm_records);

-- =====================================================
-- 7. CASHBOXES TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS cashboxes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cashes varsa verileri kopyala
INSERT IGNORE INTO cashboxes (id, name, balance, is_active)
SELECT id, name, COALESCE(balance, 0), COALESCE(is_active, 1) FROM cashes WHERE NOT EXISTS (SELECT 1 FROM cashboxes);

-- Varsayılan kasa ekle
INSERT IGNORE INTO cashboxes (name, balance, is_active) VALUES
('ANA KASA', 0, 1),
('BANKA HESABI', 0, 1);

-- =====================================================
-- 8. MESAJLAR TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS mesajlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gonderen_id INT NOT NULL,
    alici_id INT NOT NULL,
    konu VARCHAR(200) NULL,
    mesaj TEXT NOT NULL,
    dosya_adi VARCHAR(255) NULL,
    dosya_yolu VARCHAR(255) NULL,
    okundu TINYINT(1) DEFAULT 0,
    okunma_tarihi DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_alici (alici_id),
    INDEX idx_gonderen (gonderen_id)
);

-- =====================================================
-- 9. INSURERS VIEW (Dashboard için)
-- =====================================================
DROP VIEW IF EXISTS insurers;
CREATE VIEW insurers AS
SELECT id, firma_adi as name, durum as is_active
FROM sigorta_sirketleri;

-- =====================================================
-- 10. STAGE_DEFINITIONS VIEW (Dashboard için)
-- =====================================================
DROP VIEW IF EXISTS stage_definitions;
CREATE VIEW stage_definitions AS
SELECT id, asama_adi as name, durum as is_active, sira as sort_order
FROM asama_durumlari;

-- =====================================================
-- 11. FINANCE_TRANSACTIONS TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS finance_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cashbox_id INT NULL,
    case_id INT NULL,
    txn_type VARCHAR(20) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT NULL,
    txn_date DATE NOT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- income_expense varsa verileri kopyala
INSERT IGNORE INTO finance_transactions (id, cashbox_id, txn_type, amount, description, txn_date, created_at)
SELECT id, cashbox_id, type, amount, description, transaction_date, created_at
FROM income_expense WHERE NOT EXISTS (SELECT 1 FROM finance_transactions);

-- =====================================================
-- 12. USER_PERMISSIONS TABLOSU
-- =====================================================
DROP TABLE IF EXISTS user_permissions;
CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_key VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_perm (user_id, menu_key)
);

-- Admin kullanıcılara tüm yetkileri ver
INSERT IGNORE INTO user_permissions (user_id, menu_key)
SELECT u.id, m.menu_key
FROM users u
CROSS JOIN (
    SELECT 'dashboard' as menu_key UNION ALL
    SELECT 'dosyalar' UNION ALL
    SELECT 'dosya_ekle' UNION ALL
    SELECT 'crm' UNION ALL
    SELECT 'hesap_adk' UNION ALL
    SELECT 'hesap_maluliyet' UNION ALL
    SELECT 'ihbar_foyu' UNION ALL
    SELECT 'yonlendiren' UNION ALL
    SELECT 'ortaklar' UNION ALL
    SELECT 'ortak_hesaplari' UNION ALL
    SELECT 'tanim_personel' UNION ALL
    SELECT 'tanim_sigorta' UNION ALL
    SELECT 'tanim_masraf' UNION ALL
    SELECT 'tanim_evrak' UNION ALL
    SELECT 'tanim_asama' UNION ALL
    SELECT 'kasa' UNION ALL
    SELECT 'cari' UNION ALL
    SELECT 'maas_prim' UNION ALL
    SELECT 'gelir_gider' UNION ALL
    SELECT 'rapor_dosya' UNION ALL
    SELECT 'rapor_finansal' UNION ALL
    SELECT 'rapor_ortak' UNION ALL
    SELECT 'sistem_genel' UNION ALL
    SELECT 'sistem_yetki' UNION ALL
    SELECT 'sistem_kullanici' UNION ALL
    SELECT 'sistem_api' UNION ALL
    SELECT 'sistem_firma' UNION ALL
    SELECT 'mesajlar' UNION ALL
    SELECT 'ajanda'
) m
WHERE UPPER(u.role) = 'ADMIN';

-- =====================================================
-- 13. ADK_CALCULATIONS TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS adk_calculations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    marka VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    model_yili INT NULL,
    km INT NULL,
    hasar_tarihi DATE NULL,
    arac_bedeli DECIMAL(15,2) DEFAULT 0,
    hasar_tutari DECIMAL(15,2) DEFAULT 0,
    deger_kaybi DECIMAL(15,2) DEFAULT 0,
    toplam_tazminat DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_case (case_id)
);

-- =====================================================
-- 14. BH_CALCULATIONS TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS bh_calculations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    maluliyet_orani DECIMAL(5,2) DEFAULT 0,
    dogum_tarihi DATE NULL,
    kaza_tarihi DATE NULL,
    gelir DECIMAL(15,2) DEFAULT 0,
    toplam_tazminat DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_case (case_id)
);

-- =====================================================
-- 15. CASE_STAGE_HISTORY TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS case_stage_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    stage_id INT NOT NULL,
    changed_by_user_id INT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    INDEX idx_case (case_id)
);

-- =====================================================
-- 16. AUDIT_LOGS TABLOSU
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TAMAMLANDI!
-- =====================================================
SELECT 'TOPLU DÜZELTME TAMAMLANDI!' as SONUC;
