-- ═══════════════════════════════════════════════════════════════
-- MR HASAR DANIŞMANLIK - VERİTABANI ŞEMASI
-- HERZAMAN FARKEDER
-- Tarih: 20 Ocak 2026
-- ═══════════════════════════════════════════════════════════════

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ═══════════════════════════════════════════════════════════════
-- KULLANICILAR
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin','manager','user','lawyer') DEFAULT 'user',
    permissions TEXT,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan admin (şifre: admin123)
INSERT INTO users (username, email, password, name, role) VALUES
('admin', 'admin@mrhasar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMİN', 'admin'),
('demirhan', 'demirhan@mrhasar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DEMİRHAN EMİR', 'lawyer');

-- ═══════════════════════════════════════════════════════════════
-- KULLANICI YETKİLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS user_permissions;
CREATE TABLE user_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    permission VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- SİGORTA ŞİRKETLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS insurance_companies;
CREATE TABLE insurance_companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(20),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO insurance_companies (name, short_name) VALUES
('AKSİGORTA', 'AKS'),
('ALLİANZ SİGORTA', 'ALZ'),
('ANADOLU SİGORTA', 'AND'),
('AXA SİGORTA', 'AXA'),
('BNP PARIBAS CARDİF', 'BNP'),
('COFACE SİGORTA', 'COF'),
('CORPUS SİGORTA', 'COR'),
('DEMİR SİGORTA', 'DMR'),
('DUBAI STARR SİGORTA', 'DSS'),
('ERGO SİGORTA', 'ERG'),
('ETHICA SİGORTA', 'ETH'),
('EULER HERMES', 'EUL'),
('EUREKO SİGORTA', 'EUR'),
('GENERALI SİGORTA', 'GEN'),
('GROUPAMA SİGORTA', 'GRP'),
('GULF SİGORTA', 'GLF'),
('GÜNEŞ SİGORTA', 'GNS'),
('HALK SİGORTA', 'HLK'),
('HDI SİGORTA', 'HDI'),
('HEPIYI SİGORTA', 'HEP'),
('KORU SİGORTA', 'KOR'),
('LİBERTY SİGORTA', 'LBR'),
('MAGDEBURGER SİGORTA', 'MAG'),
('MAPFRE SİGORTA', 'MPF'),
('NEOVA SİGORTA', 'NEO'),
('ORIENT SİGORTA', 'ORT'),
('QUICK SİGORTA', 'QCK'),
('RAY SİGORTA', 'RAY'),
('REINSURANCE SİGORTA', 'REI'),
('SABANCI SİGORTA', 'SBN'),
('SBN SİGORTA', 'SBN2'),
('SOMPO SİGORTA', 'SMP'),
('ŞEKER SİGORTA', 'SKR'),
('TURİNG SİGORTA', 'TUR'),
('TURK P&I SİGORTA', 'TPI'),
('TÜRK NİPPON SİGORTA', 'TNS'),
('TÜRKİYE SİGORTA', 'TSG'),
('UNICO SİGORTA', 'UNC'),
('VAKIF EMEKLİLİK', 'VKF'),
('YAPI KREDİ SİGORTA', 'YKS'),
('ZIRAAT SİGORTA', 'ZRT'),
('ZURICH SİGORTA', 'ZRH'),
('DOGA SİGORTA', 'DOG'),
('FİBA SİGORTA', 'FBA'),
('ACIBADEM SİGORTA', 'ACB'),
('BEREKET SİGORTA', 'BRK'),
('DOĞA SİGORTA', 'DGA'),
('IŞIK SİGORTA', 'ISK'),
('QNB SİGORTA', 'QNB'),
('T.SİGORTA', 'TSG2');

-- ═══════════════════════════════════════════════════════════════
-- AŞAMALAR
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS stages;
CREATE TABLE stages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(20) DEFAULT '#3b82f6',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO stages (name, color, sort_order) VALUES
('DOSYA AÇILDI', '#3b82f6', 1),
('EVRAK BEKLENİYOR', '#f59e0b', 2),
('EVRAKLAR TAMAM', '#22c55e', 3),
('HESAPLAMA YAPILDI', '#8b5cf6', 4),
('BAŞVURU YAPILDI', '#06b6d4', 5),
('CEVAP BEKLENİYOR', '#f59e0b', 6),
('İTİRAZ EDİLDİ', '#ef4444', 7),
('TAHKİM AŞAMASINDA', '#7c3aed', 8),
('ÖDEME BEKLENİYOR', '#eab308', 9),
('ÖDEME ALINDI', '#22c55e', 10);

-- ═══════════════════════════════════════════════════════════════
-- AVUKATLAR
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS lawyers;
CREATE TABLE lawyers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    baro VARCHAR(50),
    baro_no VARCHAR(20),
    tc_no VARCHAR(11),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    iban VARCHAR(50),
    bank_name VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO lawyers (name, baro, phone) VALUES
('DEMİRHAN EMİR', 'İZMİR BAROSU', '');

-- ═══════════════════════════════════════════════════════════════
-- YÖNLENDİRENLER
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS referrers;
CREATE TABLE referrers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('YÖNLENDİREN','SERVİS') DEFAULT 'YÖNLENDİREN',
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    adk_commission_rate DECIMAL(5,2) DEFAULT 0 COMMENT 'ADK Komisyon %',
    bh_commission_rate DECIMAL(5,2) DEFAULT 0 COMMENT 'BH Komisyon %',
    per_file_fee DECIMAL(10,2) DEFAULT 0 COMMENT 'Dosya başı ücret',
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- İŞ ORTAKLARI
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS partners;
CREATE TABLE partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('AVUKAT','EKSPER','DİĞER') DEFAULT 'DİĞER',
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    iban VARCHAR(50),
    bank_name VARCHAR(50),
    adk_share_rate DECIMAL(5,2) DEFAULT 50 COMMENT 'ADK Pay Oranı %',
    bh_share_rate DECIMAL(5,2) DEFAULT 50 COMMENT 'BH Pay Oranı %',
    cash_id INT UNSIGNED COMMENT 'İlişkili Kasa',
    balance DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- MASRAF TÜRLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS expense_types;
CREATE TABLE expense_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO expense_types (name) VALUES
('HARÇ'),
('BİLİRKİŞİ ÜCRETİ'),
('POSTA/KARGO'),
('NOTER'),
('ULAŞIM'),
('YAKIT'),
('TELEFON'),
('KIRTASİYE'),
('VEKALETNAME'),
('TRAFİK BELGESİ'),
('EKSPERTİZ'),
('RUHSAT CEZASI'),
('SGK HİZMET DÖKÜMÜ'),
('SAĞLIK RAPORU'),
('TIBBİ EVRAK'),
('BANKA MASRAFI'),
('AVUKAT ÜCRETİ'),
('EKSPER ÜCRETİ'),
('DANIŞMANLIK'),
('REKLAM'),
('OFİS GİDERİ'),
('DİĞER');

-- ═══════════════════════════════════════════════════════════════
-- EVRAK TÜRLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS document_types;
CREATE TABLE document_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO document_types (name) VALUES
('KAZA TESPİT TUTANAĞI'),
('TRAFİK BELGESİ'),
('RUHSAT FOTOKOPİSİ'),
('EHLİYET FOTOKOPİSİ'),
('KİMLİK FOTOKOPİSİ'),
('HASAR FOTOĞRAFLARI'),
('EKSPERTİZ RAPORU'),
('FATURA'),
('VEKALETNAME'),
('SULH SÖZLEŞMESİ'),
('TAHKİM BAŞVURUSU'),
('TAHKİM KARARI'),
('DAVA DİLEKÇESİ'),
('MAHKEME KARARI'),
('ÖDEME MAKBUZU'),
('BANKA DEKONTU'),
('TIBBİ RAPOR'),
('EPİKRİZ'),
('KTT'),
('GAMR'),
('MALULİYET RAPORU'),
('SGK HİZMET DÖKÜMÜ'),
('ÖLÜM BELGESİ'),
('VERASETİ İLAM'),
('VUKUATLI NÜFUS'),
('İKAMETGAH'),
('DİĞER');

-- ═══════════════════════════════════════════════════════════════
-- ANA DOSYALAR (CASES)
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS cases;
CREATE TABLE cases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_no VARCHAR(20) NOT NULL UNIQUE,
    case_type ENUM('ADK','BH') NOT NULL,
    source_type ENUM('OFİS','YÖNLENDİREN','SERVİS','MR') DEFAULT 'OFİS',
    referrer_id INT UNSIGNED,

    -- MÜVEKKİL BİLGİLERİ
    client_name VARCHAR(100) NOT NULL,
    tc_no VARCHAR(11),
    birth_date DATE,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    district VARCHAR(50),
    iban VARCHAR(50),
    bank_name VARCHAR(50),

    -- ARAÇ BİLGİLERİ
    vehicle_plate VARCHAR(15),
    vehicle_brand VARCHAR(50),
    vehicle_model VARCHAR(50),
    vehicle_year INT,
    vehicle_color VARCHAR(30),
    vehicle_chassis VARCHAR(50),
    vehicle_engine VARCHAR(50),

    -- SİGORTA BİLGİLERİ
    insurance_company_id INT UNSIGNED,
    policy_no VARCHAR(50),
    claim_no VARCHAR(50),

    -- KAZA BİLGİLERİ
    accident_date DATE,
    accident_time TIME,
    accident_location TEXT,
    accident_city VARCHAR(50),
    accident_district VARCHAR(50),
    accident_description TEXT,
    fault_rate DECIMAL(5,2) DEFAULT 0,

    -- AVUKAT / AŞAMA
    lawyer_id INT UNSIGNED,
    stage_id INT UNSIGNED,
    application_type VARCHAR(50) COMMENT 'SİGORTA/TAHKİM/DAVA',

    -- HESAPLAMA SONUÇLARI
    calculated_amount DECIMAL(15,2) DEFAULT 0,
    approved_amount DECIMAL(15,2) DEFAULT 0,
    paid_amount DECIMAL(15,2) DEFAULT 0,

    -- DURUM
    status ENUM('AKTIF','BEKLEMEDE','TAMAMLANDI','KAPANDI','İPTAL') DEFAULT 'AKTIF',
    open_date DATE,
    close_date DATE,
    notes TEXT,

    -- META
    created_by INT UNSIGNED,
    assigned_to INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (referrer_id) REFERENCES referrers(id) ON DELETE SET NULL,
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id) ON DELETE SET NULL,
    FOREIGN KEY (stage_id) REFERENCES stages(id) ON DELETE SET NULL,
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_file_no (file_no),
    INDEX idx_case_type (case_type),
    INDEX idx_status (status),
    INDEX idx_stage_id (stage_id),
    INDEX idx_accident_date (accident_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- DOSYA EVRAKLARI
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS case_documents;
CREATE TABLE case_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id INT UNSIGNED NOT NULL,
    doc_type_id INT UNSIGNED,
    doc_type VARCHAR(50),
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    description TEXT,
    uploaded_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (doc_type_id) REFERENCES document_types(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- DOSYA MASRAFLARI
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS case_expenses;
CREATE TABLE case_expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id INT UNSIGNED NOT NULL,
    expense_type_id INT UNSIGNED,
    expense_type VARCHAR(50),
    amount DECIMAL(15,2) NOT NULL,
    expense_date DATE,
    description TEXT,
    receipt_no VARCHAR(50),
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (expense_type_id) REFERENCES expense_types(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- DOSYA ÖDEMELERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS case_payments;
CREATE TABLE case_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id INT UNSIGNED NOT NULL,
    payment_type ENUM('TAHSİLAT','ÖDEME','KOMİSYON') DEFAULT 'TAHSİLAT',
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE,
    payment_method ENUM('NAKİT','HAVALE','KART') DEFAULT 'NAKİT',
    cash_id INT UNSIGNED,
    description TEXT,
    receipt_no VARCHAR(50),
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- DOSYA AKTİVİTELERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS case_activities;
CREATE TABLE case_activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id INT UNSIGNED NOT NULL,
    activity_type VARCHAR(50),
    description TEXT,
    old_value TEXT,
    new_value TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- CRM LEADLER
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS crm_leads;
CREATE TABLE crm_leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    phone2 VARCHAR(20),
    email VARCHAR(100),
    city VARCHAR(50),
    district VARCHAR(50),
    accident_date DATE,
    accident_summary TEXT,
    vehicle_info VARCHAR(100),
    source ENUM('REFERANS','REKLAM','WEB','TELEFON','DİĞER') DEFAULT 'DİĞER',
    status ENUM('BEKLEMEDE','İLGİLİ','DOSYA AÇILDI','İPTAL') DEFAULT 'BEKLEMEDE',
    next_contact_date DATETIME,
    assigned_to INT UNSIGNED,
    converted_case_id INT UNSIGNED,
    notes TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- CRM NOTLARI
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS crm_notes;
CREATE TABLE crm_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    contact_type ENUM('TELEFON','SMS','EMAIL','YÜZYÜZE','DİĞER') DEFAULT 'TELEFON',
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES crm_leads(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- KASALAR
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS cashes;
CREATE TABLE cashes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('NAKİT','BANKA','POS','DİĞER') DEFAULT 'NAKİT',
    bank_name VARCHAR(50),
    account_no VARCHAR(50),
    iban VARCHAR(50),
    balance DECIMAL(15,2) DEFAULT 0,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cashes (name, type) VALUES
('NAKİT KASA', 'NAKİT'),
('BANKA HESABI', 'BANKA');

-- ═══════════════════════════════════════════════════════════════
-- KASA HAREKETLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS cash_transactions;
CREATE TABLE cash_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cash_id INT UNSIGNED NOT NULL,
    transaction_type ENUM('GİRİŞ','ÇIKIŞ','TRANSFER') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    category VARCHAR(50),
    description TEXT,
    case_id INT UNSIGNED,
    partner_id INT UNSIGNED,
    related_id INT UNSIGNED COMMENT 'İlişkili kayıt ID',
    related_type VARCHAR(50) COMMENT 'İlişkili tablo adı',
    transaction_date DATE,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_id) REFERENCES cashes(id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- ORTAK HAREKETLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS partner_transactions;
CREATE TABLE partner_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    partner_id INT UNSIGNED NOT NULL,
    transaction_type ENUM('ALACAK','BORÇ','ÖDEME','TAHSİLAT') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    case_id INT UNSIGNED,
    description TEXT,
    transaction_date DATE,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- MESAJLAR
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS messages;
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_user INT UNSIGNED NOT NULL,
    to_user INT UNSIGNED NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME,
    parent_id INT UNSIGNED COMMENT 'Cevap ise üst mesaj',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES messages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- TALEPLER
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS requests;
CREATE TABLE requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_user INT UNSIGNED NOT NULL,
    to_user INT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('BEKLEMEDE','ONAYLANDI','REDDEDİLDİ','TAMAMLANDI') DEFAULT 'BEKLEMEDE',
    priority ENUM('NORMAL','YÜKSEK','ACİL') DEFAULT 'NORMAL',
    due_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- TAKVİM ETKİNLİKLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS calendar_events;
CREATE TABLE calendar_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    end_date DATE,
    end_time TIME,
    event_type ENUM('DURUŞMA','TOPLANTI','RANDEVU','HATIRLATMA','DİĞER') DEFAULT 'HATIRLATMA',
    case_id INT UNSIGNED,
    user_id INT UNSIGNED,
    reminder_before INT DEFAULT 0 COMMENT 'Dakika cinsinden',
    is_completed TINYINT(1) DEFAULT 0,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- AYARLAR
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('company_name', 'MR HASAR DANIŞMANLIK', 'company'),
('company_slogan', 'HERZAMAN FARKEDER', 'company'),
('company_phone', '', 'company'),
('company_email', '', 'company'),
('company_address', '', 'company'),
('company_tax_office', '', 'company'),
('company_tax_no', '', 'company'),
('adk_default_rate', '10', 'calculation'),
('bh_default_rate', '15', 'calculation'),
('pmf_discount_rate', '1.8', 'calculation'),
('file_no_prefix_adk', 'ADK', 'file'),
('file_no_prefix_bh', 'BH', 'file');

-- ═══════════════════════════════════════════════════════════════
-- YEDEKLEME LOG
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS backup_logs;
CREATE TABLE backup_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filesize BIGINT,
    backup_type ENUM('FULL','DB','FILES') DEFAULT 'DB',
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- AKTİVİTE LOGLARI
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT UNSIGNED,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- İHBAR FÖYÜ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS ihbar_foyleri;
CREATE TABLE ihbar_foyleri (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    foyu_no VARCHAR(20) NOT NULL UNIQUE,

    -- MÜVEKKİL BİLGİLERİ
    muvekkil_ad VARCHAR(100) NOT NULL,
    muvekkil_tc VARCHAR(11),
    muvekkil_telefon VARCHAR(20),
    muvekkil_email VARCHAR(100),
    muvekkil_adres TEXT,

    -- KAZA BİLGİLERİ
    kaza_tarihi DATE,
    kaza_saati TIME,
    kaza_yeri TEXT,
    kaza_ili VARCHAR(50),
    kaza_ilcesi VARCHAR(50),
    kaza_sekli VARCHAR(100),
    hava_durumu VARCHAR(50),
    yol_durumu VARCHAR(50),

    -- ARAÇ BİLGİLERİ (MÜVEKKİL)
    arac_plaka VARCHAR(15),
    arac_marka VARCHAR(50),
    arac_model VARCHAR(50),
    arac_model_yili INT,
    arac_renk VARCHAR(30),
    arac_motor_no VARCHAR(50),
    arac_sasi_no VARCHAR(50),

    -- SİGORTA BİLGİLERİ (MÜVEKKİL)
    sigorta_sirketi VARCHAR(100),
    police_no VARCHAR(50),
    police_bitis DATE,
    trafik_tescil VARCHAR(50),

    -- KARŞI TARAF BİLGİLERİ
    karsi_ad VARCHAR(100),
    karsi_tc VARCHAR(11),
    karsi_telefon VARCHAR(20),
    karsi_plaka VARCHAR(15),
    karsi_marka VARCHAR(50),
    karsi_model VARCHAR(50),
    karsi_sigorta VARCHAR(100),
    karsi_police_no VARCHAR(50),

    -- HASAR BİLGİLERİ
    hasar_tipi ENUM('ADK','BH','KARMA') DEFAULT 'ADK',
    hasar_aciklama TEXT,
    kusur_orani DECIMAL(5,2) DEFAULT 0,
    tahmini_hasar DECIMAL(15,2),

    -- YARALANMA BİLGİLERİ
    yaralanma_var TINYINT(1) DEFAULT 0,
    yaralanma_aciklama TEXT,
    hastane_adi VARCHAR(100),
    tedavi_suresi INT COMMENT 'Gün cinsinden',

    -- DURUM
    durum ENUM('TASLAK','TAMAMLANDI','DOSYAYA_DONUSTURULDU') DEFAULT 'TASLAK',
    converted_case_id INT UNSIGNED,

    -- META
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- CARİ HESAPLAR
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS cari_accounts;
CREATE TABLE cari_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('ALICI','SATICI','DİĞER') DEFAULT 'DİĞER',
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    tax_office VARCHAR(50),
    tax_no VARCHAR(20),
    balance DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- CARİ HAREKETLERİ
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS cari_transactions;
CREATE TABLE cari_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cari_id INT UNSIGNED NOT NULL,
    transaction_type ENUM('BORÇ','ALACAK') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    document_no VARCHAR(50),
    transaction_date DATE,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cari_id) REFERENCES cari_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- GELİR/GİDER
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS income_expense;
CREATE TABLE income_expense (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('GELİR','GİDER') NOT NULL,
    category VARCHAR(50),
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    transaction_date DATE,
    cash_id INT UNSIGNED,
    case_id INT UNSIGNED,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_id) REFERENCES cashes(id) ON DELETE SET NULL,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- PERSONEL
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS personnel;
CREATE TABLE personnel (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    name VARCHAR(100) NOT NULL,
    tc_no VARCHAR(11),
    birth_date DATE,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    position VARCHAR(50),
    department VARCHAR(50),
    start_date DATE,
    end_date DATE,
    salary DECIMAL(15,2) DEFAULT 0,
    iban VARCHAR(50),
    bank_name VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- MAAŞ/PRİM
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS salary_payments;
CREATE TABLE salary_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT UNSIGNED NOT NULL,
    payment_type ENUM('MAAŞ','PRİM','AVANS','DİĞER') DEFAULT 'MAAŞ',
    period VARCHAR(10) COMMENT 'YYYY-MM formatında',
    gross_amount DECIMAL(15,2) DEFAULT 0,
    net_amount DECIMAL(15,2) DEFAULT 0,
    sgk_deduction DECIMAL(15,2) DEFAULT 0,
    tax_deduction DECIMAL(15,2) DEFAULT 0,
    other_deduction DECIMAL(15,2) DEFAULT 0,
    payment_date DATE,
    description TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personnel_id) REFERENCES personnel(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- ARAÇ VERİTABANI (ADK İÇİN)
-- ═══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS adk_vehicle_brands;
CREATE TABLE adk_vehicle_brands (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS adk_vehicle_models;
CREATE TABLE adk_vehicle_models (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (brand_id) REFERENCES adk_vehicle_brands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Popüler araç markaları
INSERT INTO adk_vehicle_brands (name) VALUES
('AUDI'), ('BMW'), ('CHEVROLET'), ('CITROEN'), ('DACIA'),
('FIAT'), ('FORD'), ('HONDA'), ('HYUNDAI'), ('KIA'),
('MAZDA'), ('MERCEDES'), ('NISSAN'), ('OPEL'), ('PEUGEOT'),
('RENAULT'), ('SEAT'), ('SKODA'), ('TOYOTA'), ('VOLKSWAGEN'), ('VOLVO');

SET FOREIGN_KEY_CHECKS = 1;

-- ═══════════════════════════════════════════════════════════════
-- KURULUM TAMAMLANDI
-- ═══════════════════════════════════════════════════════════════
