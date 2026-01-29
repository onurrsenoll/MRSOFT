-- MR HASAR DANIŞMANLIK
-- Personel Yönetimi v2.0 - Veritabanı Güncellemesi
-- Bu scripti phpMyAdmin veya MySQL komut satırında çalıştırın

-- Kullanıcı tipi kolonu (PERSONEL / HARICI)
ALTER TABLE users ADD COLUMN IF NOT EXISTS user_type VARCHAR(20) DEFAULT 'PERSONEL';

-- Maaş ve prim kolonları
ALTER TABLE users ADD COLUMN IF NOT EXISTS maas DECIMAL(12,2) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS prim_adk DECIMAL(10,2) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS prim_bedeni DECIMAL(10,2) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS prim_diger DECIMAL(10,2) DEFAULT 0;

-- Telefon kolonu
ALTER TABLE users ADD COLUMN IF NOT EXISTS telefon VARCHAR(20) DEFAULT NULL;

-- Mevcut kullanıcıları PERSONEL olarak işaretle
UPDATE users SET user_type = 'PERSONEL' WHERE user_type IS NULL OR user_type = '';

-- İndeksler
CREATE INDEX IF NOT EXISTS idx_users_user_type ON users(user_type);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- Başarılı mesajı
SELECT 'Personel tablosu başarıyla güncellendi!' as Mesaj;
