-- MR HASAR DANIŞMANLIK - ADK PRO HESAPLAMA TABLOLARI
-- AI Destekli Gelişmiş Araç Değer Kaybı Hesaplama Modülü
-- Tarih: 2026-01-28

-- =============================================
-- ADK HESAPLAMALARI TABLOSU
-- =============================================
CREATE TABLE IF NOT EXISTS adk_hesaplamalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NULL,

    -- Araç Bilgileri
    marka VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    model_yili YEAR NOT NULL,
    kilometre INT DEFAULT 0,
    plaka VARCHAR(20) NULL,
    sasi_no VARCHAR(20) NULL,

    -- Değer Bilgileri
    arac_degeri DECIMAL(15,2) NOT NULL DEFAULT 0,
    hasar_tutari DECIMAL(15,2) DEFAULT 0,
    kaza_tarihi DATE NULL,

    -- Hasar Detayları
    gecmis_hasar ENUM('VAR', 'YOK') DEFAULT 'YOK',
    kusur_orani INT DEFAULT 100,
    parcalar JSON NULL COMMENT 'Hasarlı parçalar ve işlem türleri',

    -- Piyasa Araştırması
    piyasa_min DECIMAL(15,2) NULL,
    piyasa_max DECIMAL(15,2) NULL,
    piyasa_ortalama DECIMAL(15,2) NULL,
    emsal_ilanlar JSON NULL COMMENT 'Emsal ilan detayları',

    -- Hesaplama Sonuçları
    deger_kaybi DECIMAL(15,2) NOT NULL DEFAULT 0,
    deger_kaybi_orani DECIMAL(5,2) DEFAULT 0,
    hesaplama_yontemi VARCHAR(50) DEFAULT 'TBK_YARGITAY',

    -- Katsayılar (log için)
    yas_katsayi DECIMAL(5,3) DEFAULT 1.000,
    km_katsayi DECIMAL(5,3) DEFAULT 1.000,
    hasar_katsayi DECIMAL(5,3) DEFAULT 1.000,
    parca_katsayi DECIMAL(5,3) DEFAULT 1.000,
    gecmis_hasar_katsayi DECIMAL(5,3) DEFAULT 1.000,

    -- Rapor Bilgileri
    rapor_no VARCHAR(50) NULL,
    rapor_tarihi DATETIME NULL,

    -- Talep Sahibi Bilgileri
    talep_sahibi_ad VARCHAR(255) NULL,
    talep_sahibi_tc VARCHAR(11) NULL,
    talep_sahibi_telefon VARCHAR(20) NULL,

    -- Karşı Taraf Bilgileri
    karsi_plaka VARCHAR(20) NULL,
    karsi_sigorta VARCHAR(255) NULL,
    karsi_police_no VARCHAR(50) NULL,

    -- Sistem Bilgileri
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_case_id (case_id),
    INDEX idx_marka_model (marka, model),
    INDEX idx_created_at (created_at),
    INDEX idx_rapor_no (rapor_no),

    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- PİYASA ARAŞTIRMA CACHE TABLOSU
-- =============================================
CREATE TABLE IF NOT EXISTS adk_piyasa_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marka VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    model_yili YEAR NOT NULL,
    km_aralik VARCHAR(20) NOT NULL COMMENT '0-50k, 50k-100k, 100k-150k, 150k+',

    min_fiyat DECIMAL(15,2) NULL,
    max_fiyat DECIMAL(15,2) NULL,
    ortalama_fiyat DECIMAL(15,2) NULL,
    ilan_sayisi INT DEFAULT 0,
    ilanlar JSON NULL,

    arastirma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    gecerlilik_bitis DATETIME NULL,

    INDEX idx_arac (marka, model, model_yili),
    INDEX idx_gecerlilik (gecerlilik_bitis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =============================================
-- ADK KATSAYILAR TABLOSU
-- =============================================
CREATE TABLE IF NOT EXISTS adk_katsayilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(50) NOT NULL COMMENT 'yas, km, hasar_bolge, parca_islem',
    kod VARCHAR(50) NOT NULL,
    aciklama VARCHAR(255) NULL,
    min_deger INT NULL,
    max_deger INT NULL,
    katsayi DECIMAL(5,3) NOT NULL DEFAULT 1.000,
    aktif TINYINT(1) DEFAULT 1,

    UNIQUE KEY uk_kategori_kod (kategori, kod),
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- YAŞ KATSAYILARI
INSERT INTO adk_katsayilar (kategori, kod, aciklama, min_deger, max_deger, katsayi) VALUES
('yas', '0-1', '0-1 yaş arası', 0, 1, 1.200),
('yas', '2-3', '2-3 yaş arası', 2, 3, 1.100),
('yas', '4-5', '4-5 yaş arası', 4, 5, 1.000),
('yas', '6-7', '6-7 yaş arası', 6, 7, 0.900),
('yas', '8-10', '8-10 yaş arası', 8, 10, 0.800),
('yas', '11-15', '11-15 yaş arası', 11, 15, 0.600),
('yas', '16+', '16 yaş ve üzeri', 16, 99, 0.400);

-- KM KATSAYILARI
INSERT INTO adk_katsayilar (kategori, kod, aciklama, min_deger, max_deger, katsayi) VALUES
('km', '0-25k', '0-25.000 km', 0, 25000, 1.200),
('km', '25k-50k', '25.000-50.000 km', 25001, 50000, 1.100),
('km', '50k-75k', '50.000-75.000 km', 50001, 75000, 1.000),
('km', '75k-100k', '75.000-100.000 km', 75001, 100000, 0.900),
('km', '100k-150k', '100.000-150.000 km', 100001, 150000, 0.800),
('km', '150k-200k', '150.000-200.000 km', 150001, 200000, 0.700),
('km', '200k+', '200.000 km üzeri', 200001, 9999999, 0.500);

-- PARÇA İŞLEM KATSAYILARI
INSERT INTO adk_katsayilar (kategori, kod, aciklama, katsayi) VALUES
('parca_islem', 'degisim', 'Parça değişimi', 1.050),
('parca_islem', 'boya', 'Boya işlemi', 1.020),
('parca_islem', 'onarim', 'Onarım işlemi', 1.010);

-- HASAR BÖLGE KATSAYILARI
INSERT INTO adk_katsayilar (kategori, kod, aciklama, katsayi) VALUES
('hasar_bolge', 'on', 'Ön bölge hasarı', 1.100),
('hasar_bolge', 'arka', 'Arka bölge hasarı', 1.050),
('hasar_bolge', 'yan', 'Yan bölge hasarı', 1.080),
('hasar_bolge', 'tavan', 'Tavan hasarı', 1.150),
('hasar_bolge', 'sase', 'Şase hasarı', 1.300);

-- GEÇMİŞ HASAR KATSAYISI
INSERT INTO adk_katsayilar (kategori, kod, aciklama, katsayi) VALUES
('gecmis_hasar', 'var', 'Geçmiş hasar kaydı var', 0.850),
('gecmis_hasar', 'yok', 'Geçmiş hasar kaydı yok', 1.000);

-- =============================================
-- EVRAK ANALİZ LOGLARİ TABLOSU
-- =============================================
CREATE TABLE IF NOT EXISTS adk_evrak_analiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adk_hesaplama_id INT NULL,

    dosya_adi VARCHAR(255) NOT NULL,
    dosya_tipi VARCHAR(50) NOT NULL,
    dosya_boyut INT DEFAULT 0,

    analiz_sonuc JSON NULL COMMENT 'AI analiz sonuçları',
    tespit_edilen_bilgiler JSON NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (adk_hesaplama_id) REFERENCES adk_hesaplamalar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;
