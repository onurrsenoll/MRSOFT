-- MR HASAR DANIŞMANLIK - CASES TABLOSU YENİ KOLONLAR
-- Dosya ekleme formu için gerekli alan güncellemeleri
-- Tarih: 2026-01-28

-- =============================================
-- DOSYA BİLGİLERİ
-- =============================================
ALTER TABLE cases ADD COLUMN IF NOT EXISTS dosya_turu ENUM('TRAFIK', 'KASKO') DEFAULT 'TRAFIK' AFTER dosya_no;

-- =============================================
-- MÜŞTERİ BİLGİLERİ
-- =============================================
ALTER TABLE cases ADD COLUMN IF NOT EXISTS vergi_no VARCHAR(11) NULL AFTER tc_kimlik;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS unvan VARCHAR(255) NULL AFTER ad_soyad;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS telefon2 VARCHAR(20) NULL AFTER telefon;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL AFTER telefon2;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS adres TEXT NULL AFTER email;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS iban VARCHAR(34) NULL AFTER adres;

-- =============================================
-- ARAÇ BİLGİLERİ
-- =============================================
ALTER TABLE cases ADD COLUMN IF NOT EXISTS arac_marka VARCHAR(50) NULL AFTER plaka;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS arac_model VARCHAR(50) NULL AFTER arac_marka;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS model_yili YEAR NULL AFTER arac_model;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS arac_renk VARCHAR(30) NULL AFTER model_yili;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS sasi_no VARCHAR(20) NULL AFTER arac_renk;

-- =============================================
-- KAZA BİLGİLERİ
-- =============================================
ALTER TABLE cases ADD COLUMN IF NOT EXISTS kaza_il VARCHAR(50) NULL AFTER kaza_yeri;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS kaza_ilce VARCHAR(50) NULL AFTER kaza_il;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS kusur_orani INT DEFAULT 100 AFTER kaza_ilce;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS kaza_aciklama TEXT NULL AFTER kusur_orani;

-- =============================================
-- KARŞI TARAF BİLGİLERİ
-- =============================================
ALTER TABLE cases ADD COLUMN IF NOT EXISTS karsi_police_no VARCHAR(50) NULL AFTER karsi_sigorta;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS karsi_police_baslangic DATE NULL AFTER karsi_police_no;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS karsi_police_bitis DATE NULL AFTER karsi_police_baslangic;

-- =============================================
-- TRAMER VE GEÇMİŞ BİLGİLERİ
-- =============================================
ALTER TABLE cases ADD COLUMN IF NOT EXISTS tramer_kaydi ENUM('EVET', 'HAYIR', 'BILINMIYOR') DEFAULT 'BILINMIYOR' AFTER karsi_police_bitis;
ALTER TABLE cases ADD COLUMN IF NOT EXISTS onceki_hasar TEXT NULL AFTER tramer_kaydi;

-- =============================================
-- İNDEKSLER
-- =============================================
CREATE INDEX IF NOT EXISTS idx_cases_dosya_turu ON cases(dosya_turu);
CREATE INDEX IF NOT EXISTS idx_cases_kaza_il ON cases(kaza_il);
CREATE INDEX IF NOT EXISTS idx_cases_arac_marka ON cases(arac_marka);

-- =============================================
-- EVRAK TÜRLERİ TABLOSUNA GEREKLİ EVRAKLAR
-- =============================================
INSERT IGNORE INTO evrak_turleri (ad, aciklama, zorunlu, aktif) VALUES
('Kaza Tespit Tutanağı', 'Trafik kazası tespit tutanağı veya alkol raporu', 1, 1),
('Ruhsat Fotokopisi', 'Araç ruhsatının ön ve arka yüz fotokopisi', 1, 1),
('Ehliyet Fotokopisi', 'Sürücü ehliyetinin ön ve arka yüz fotokopisi', 1, 1),
('Kimlik Fotokopisi', 'TC kimlik kartı veya nüfus cüzdanı fotokopisi', 1, 1),
('Ekspertiz Raporu', 'Sigorta şirketi ekspertiz raporu', 1, 1),
('Onarım Faturası', 'Araç onarım faturası veya proforma fatura', 0, 1),
('Hasar Fotoğrafları', 'Kaza sonrası araç hasar fotoğrafları', 1, 1),
('Vekaletname', 'Noter onaylı vekaletname', 0, 1),
('Trafik Poliçesi', 'Zorunlu trafik sigortası poliçesi', 1, 1),
('Kasko Poliçesi', 'Kasko sigortası poliçesi (varsa)', 0, 1),
('IBAN Belgesi', 'Banka hesap bilgileri / IBAN belgesi', 1, 1),
('Tramer Kaydı', 'Tramer hasar kaydı sorgulama belgesi', 0, 1),
('Servis Formu', 'Yetkili servis iş emri formu', 0, 1),
('Değer Kaybı Raporu', 'Araç değer kaybı hesaplama raporu', 0, 1),
('Tanık İfadesi', 'Kaza tanık ifade tutanağı', 0, 1),
('Kaza Krokisi', 'Kaza yerinin krokisi', 0, 1),
('Sağlık Raporu', 'Yaralanma varsa sağlık raporu', 0, 1),
('Tamir Teklifi', 'Onarım için alınan teklifler', 0, 1);

-- Tablo yoksa evrak_turleri tablosunu oluştur
CREATE TABLE IF NOT EXISTS evrak_turleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    aciklama TEXT NULL,
    zorunlu TINYINT(1) DEFAULT 0,
    aktif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
