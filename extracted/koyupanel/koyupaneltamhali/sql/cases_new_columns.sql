-- MR HASAR DANIŞMANLIK - CASES TABLOSU YENİ KOLONLAR
-- Revize Tarih: 2026-01-28

-- DOSYA BİLGİLERİ
ALTER TABLE cases ADD COLUMN IF NOT EXISTS dosya_turu ENUM('TRAFIK', 'KASKO') DEFAULT 'TRAFIK' AFTER dosya_no;

-- MÜŞTERİ BİLGİLERİ
ALTER TABLE cases ADD COLUMN IF NOT EXISTS musteri_tipi ENUM('GERCEK', 'TUZEL') DEFAULT 'GERCEK' AFTER dosya_turu;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS vergi_no VARCHAR(11) NULL AFTER tc_kimlik;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS unvan VARCHAR(255) NULL AFTER ad_soyad;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS adres TEXT NULL AFTER telefon;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS iban VARCHAR(34) NULL AFTER adres;

-- ARAÇ BİLGİLERİ
ALTER TABLE cases ADD COLUMN IF NOT EXISTS arac_marka VARCHAR(50) NULL AFTER plaka;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS arac_model VARCHAR(50) NULL AFTER arac_marka;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS model_yili YEAR NULL AFTER arac_model;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS sasi_no VARCHAR(20) NULL AFTER model_yili;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS gecmis_hasar ENUM('VAR', 'YOK') DEFAULT 'YOK' AFTER sasi_no;

-- KAZA BİLGİLERİ
ALTER TABLE cases ADD COLUMN IF NOT EXISTS kaza_il VARCHAR(50) NULL AFTER kaza_yeri;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS kaza_ilce VARCHAR(50) NULL AFTER kaza_il;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS kusur_orani INT DEFAULT 100 AFTER kaza_ilce;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS kaza_aciklama TEXT NULL AFTER kusur_orani;

-- KARŞI TARAF BİLGİLERİ
ALTER TABLE cases ADD COLUMN IF NOT EXISTS karsi_police_no VARCHAR(50) NULL AFTER karsi_sigorta;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS karsi_police_baslangic DATE NULL AFTER karsi_police_no;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS karsi_police_bitis DATE NULL AFTER karsi_police_baslangic;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS servis_id INT NULL AFTER karsi_police_bitis;

ALTER TABLE cases ADD COLUMN IF NOT EXISTS eksper_id INT NULL AFTER servis_id;

-- KAYNAK BİLGİLERİ
ALTER TABLE cases ADD COLUMN IF NOT EXISTS personel_id INT NULL AFTER yonlendiren_id;

-- İNDEKSLER
CREATE INDEX IF NOT EXISTS idx_cases_dosya_turu ON cases(dosya_turu);

CREATE INDEX IF NOT EXISTS idx_cases_musteri_tipi ON cases(musteri_tipi);

CREATE INDEX IF NOT EXISTS idx_cases_kaza_il ON cases(kaza_il);

CREATE INDEX IF NOT EXISTS idx_cases_arac_marka ON cases(arac_marka);

CREATE INDEX IF NOT EXISTS idx_cases_servis_id ON cases(servis_id);

CREATE INDEX IF NOT EXISTS idx_cases_eksper_id ON cases(eksper_id);

-- =============================================
-- SERVİSLER TABLOSU
-- =============================================
CREATE TABLE IF NOT EXISTS servisler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firma_adi VARCHAR(255) NOT NULL,
    yetkili_ad VARCHAR(100) NULL,
    telefon VARCHAR(20) NULL,
    adres TEXT NULL,
    il VARCHAR(50) NULL,
    ilce VARCHAR(50) NULL,
    vergi_no VARCHAR(11) NULL,
    notlar TEXT NULL,
    durum TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- EKSPERLER TABLOSU
-- =============================================
CREATE TABLE IF NOT EXISTS eksperler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    sirket VARCHAR(255) NULL,
    sicil_no VARCHAR(50) NULL,
    notlar TEXT NULL,
    durum TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- EVRAK TÜRLERİ TABLOSU
-- =============================================
CREATE TABLE IF NOT EXISTS evrak_turleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    aciklama TEXT NULL,
    zorunlu TINYINT(1) DEFAULT 0,
    aktif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- GEREKLİ EVRAK TÜRLERİ
INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Kaza Tespit Tutanağı', 'Trafik kazası tespit tutanağı', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Ruhsat Fotokopisi', 'Araç ruhsatı fotokopisi', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Ehliyet Fotokopisi', 'Sürücü ehliyeti fotokopisi', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Kimlik Fotokopisi', 'TC kimlik fotokopisi', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Ekspertiz Raporu', 'Sigorta ekspertiz raporu', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Onarım Faturası', 'Araç onarım faturası', 0, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Hasar Fotoğrafları', 'Kaza sonrası fotoğraflar', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Vekaletname', 'Noter onaylı vekaletname', 0, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Trafik Poliçesi', 'Zorunlu trafik sigortası', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Kasko Poliçesi', 'Kasko sigortası poliçesi', 0, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('IBAN Belgesi', 'Banka hesap bilgileri', 1, 1);

INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES ('Tramer Kaydı', 'Tramer hasar kaydı', 0, 1);
