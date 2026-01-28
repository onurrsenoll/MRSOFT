<?php
/**
 * MR HASAR DANIŞMANLIK - VERİTABANI DÜZELTME SCRIPT'I
 * Bu dosyayı çalıştırarak users tablosunu düzeltin ve admin yetkilerini verin.
 * Çalıştırdıktan sonra bu dosyayı SİLİN!
 */

require_once __DIR__ . '/config.php';

echo "<html><head><meta charset='UTF-8'><title>VERİTABANI DÜZELTME</title>";
echo "<style>body{font-family:Arial;background:#1a1a2e;color:#fff;padding:40px}";
echo ".ok{color:#10b981;}.err{color:#ef4444;}.warn{color:#f59e0b;}";
echo "h1{color:#3b82f6;}pre{background:#111;padding:15px;border-radius:8px;overflow-x:auto}</style></head><body>";
echo "<h1>MR HASAR - VERİTABANI DÜZELTME</h1>";

try {
    $db = getDB();
    $messages = [];

    // 1. Users tablosunun yapısını kontrol et
    echo "<h2>1. USERS TABLOSU KONTROLÜ</h2>";
    $columns = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>" . implode(", ", $columns) . "</pre>";

    // 2. full_name sütunu yoksa ekle
    if (!in_array('full_name', $columns)) {
        echo "<p class='warn'>⚠ full_name sütunu yok, ekleniyor...</p>";
        $db->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(200) NULL AFTER username");

        // name sütunundan kopyala
        if (in_array('name', $columns)) {
            $db->exec("UPDATE users SET full_name = name WHERE full_name IS NULL OR full_name = ''");
            echo "<p class='ok'>✓ full_name sütunu eklendi ve name'den kopyalandı</p>";
        } else {
            // username'den kopyala
            $db->exec("UPDATE users SET full_name = username WHERE full_name IS NULL OR full_name = ''");
            echo "<p class='ok'>✓ full_name sütunu eklendi ve username'den kopyalandı</p>";
        }
    } else {
        echo "<p class='ok'>✓ full_name sütunu zaten mevcut</p>";

        // Boş olanları doldur
        if (in_array('name', $columns)) {
            $db->exec("UPDATE users SET full_name = name WHERE (full_name IS NULL OR full_name = '') AND name IS NOT NULL AND name != ''");
        }
    }

    // 3. password_hash sütunu yoksa ekle
    if (!in_array('password_hash', $columns)) {
        echo "<h2>2. PASSWORD_HASH SÜTUNU KONTROLÜ</h2>";
        echo "<p class='warn'>⚠ password_hash sütunu yok, ekleniyor...</p>";
        $db->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NULL AFTER password");

        // password sütunundaki değerleri kopyala (eğer hash ise)
        if (in_array('password', $columns)) {
            $db->exec("UPDATE users SET password_hash = password WHERE password_hash IS NULL AND password LIKE '\$2y\$%'");
            echo "<p class='ok'>✓ password_hash sütunu eklendi</p>";
        }
    } else {
        echo "<h2>2. PASSWORD_HASH SÜTUNU KONTROLÜ</h2>";
        echo "<p class='ok'>✓ password_hash sütunu zaten mevcut</p>";
    }

    // 4. is_deleted sütunu yoksa ekle
    if (!in_array('is_deleted', $columns)) {
        echo "<h2>3. IS_DELETED SÜTUNU KONTROLÜ</h2>";
        $db->exec("ALTER TABLE users ADD COLUMN is_deleted TINYINT(1) DEFAULT 0");
        echo "<p class='ok'>✓ is_deleted sütunu eklendi</p>";
    }

    // 5. Kullanıcıları listele
    echo "<h2>4. MEVCUT KULLANICILAR</h2>";
    $users = $db->query("SELECT id, username, full_name, name, role, is_active FROM users")->fetchAll();
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Name</th><th>Role</th><th>Active</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>" . $u['id'] . "</td>";
        echo "<td>" . htmlspecialchars($u['username'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($u['full_name'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($u['name'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($u['role'] ?? '-') . "</td>";
        echo "<td>" . ($u['is_active'] ? 'EVET' : 'HAYIR') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 6. Admin kullanıcısını ADMIN rolüne çevir
    echo "<h2>5. ADMIN KULLANICI AYARLARI</h2>";
    $adminUsers = $db->query("SELECT id, username, role FROM users WHERE UPPER(role) = 'ADMIN' OR UPPER(username) = 'ADMIN'")->fetchAll();

    if (empty($adminUsers)) {
        echo "<p class='warn'>⚠ ADMIN rolüne sahip kullanıcı bulunamadı!</p>";

        // İlk kullanıcıyı admin yap
        $firstUser = $db->query("SELECT id, username FROM users ORDER BY id LIMIT 1")->fetch();
        if ($firstUser) {
            $db->prepare("UPDATE users SET role = 'ADMIN' WHERE id = ?")->execute([$firstUser['id']]);
            echo "<p class='ok'>✓ {$firstUser['username']} kullanıcısı ADMIN yapıldı</p>";
        }
    } else {
        foreach ($adminUsers as $admin) {
            if ($admin['role'] !== 'ADMIN') {
                $db->prepare("UPDATE users SET role = 'ADMIN' WHERE id = ?")->execute([$admin['id']]);
            }
            echo "<p class='ok'>✓ {$admin['username']} ADMIN olarak ayarlandı</p>";
        }
    }

    // 7. user_permissions tablosu kontrolü
    echo "<h2>6. YETKİ TABLOSU KONTROLÜ</h2>";
    $tables = $db->query("SHOW TABLES LIKE 'user_permissions'")->fetchAll();
    if (empty($tables)) {
        echo "<p class='warn'>⚠ user_permissions tablosu yok, oluşturuluyor...</p>";
        $db->exec("CREATE TABLE user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            menu_key VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_perm (user_id, menu_key)
        )");
        echo "<p class='ok'>✓ user_permissions tablosu oluşturuldu</p>";
    } else {
        echo "<p class='ok'>✓ user_permissions tablosu mevcut</p>";
    }

    // 8. Admin kullanıcılarına tüm yetkileri ver
    $menuKeys = [
        'dashboard', 'dosyalar', 'dosya_ekle', 'crm',
        'hesap_adk', 'hesap_maluliyet',
        'ihbar_foyu', 'yonlendiren',
        'ortaklar', 'ortak_hesaplari',
        'tanim_personel', 'tanim_sigorta', 'tanim_masraf', 'tanim_evrak', 'tanim_asama',
        'kasa', 'cari', 'maas_prim', 'gelir_gider',
        'rapor_dosya', 'rapor_finansal', 'rapor_ortak',
        'sistem_genel', 'sistem_yetki', 'sistem_kullanici', 'sistem_api', 'sistem_firma',
        'mesajlar', 'ajanda'
    ];

    $adminUsers = $db->query("SELECT id, username FROM users WHERE UPPER(role) = 'ADMIN'")->fetchAll();
    foreach ($adminUsers as $admin) {
        // Önce mevcut yetkileri temizle
        $db->prepare("DELETE FROM user_permissions WHERE user_id = ?")->execute([$admin['id']]);

        // Tüm yetkileri ekle
        $stmt = $db->prepare("INSERT IGNORE INTO user_permissions (user_id, menu_key) VALUES (?, ?)");
        foreach ($menuKeys as $key) {
            $stmt->execute([$admin['id'], $key]);
        }
        echo "<p class='ok'>✓ {$admin['username']} kullanıcısına tüm yetkiler verildi</p>";
    }

    // 9. Test şifresi ayarla (isteğe bağlı)
    echo "<h2>7. ŞİFRE SIFIRLAMA (OPSİYONEL)</h2>";
    echo "<form method='post' style='background:#222;padding:20px;border-radius:8px;margin:20px 0;'>";
    echo "<p>Bir kullanıcının şifresini sıfırlamak için:</p>";
    echo "<label>Kullanıcı Adı: <input type='text' name='reset_username' style='padding:8px;'></label><br><br>";
    echo "<label>Yeni Şifre: <input type='password' name='new_password' style='padding:8px;'></label><br><br>";
    echo "<button type='submit' name='reset_password' style='background:#3b82f6;color:#fff;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;'>ŞİFRE SIFIRLA</button>";
    echo "</form>";

    if (isset($_POST['reset_password']) && !empty($_POST['reset_username']) && !empty($_POST['new_password'])) {
        $username = strtoupper(trim($_POST['reset_username']));
        $newPassword = $_POST['new_password'];
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Hangi sütun varsa onu güncelle
        $stmt = $db->prepare("UPDATE users SET password = ?, password_hash = ? WHERE UPPER(username) = ?");
        $stmt->execute([$hash, $hash, $username]);

        if ($stmt->rowCount() > 0) {
            echo "<p class='ok'>✓ {$username} kullanıcısının şifresi güncellendi!</p>";
        } else {
            echo "<p class='err'>✗ {$username} kullanıcısı bulunamadı!</p>";
        }
    }

    echo "<h2 style='color:#10b981'>✓ DÜZELTMELER TAMAMLANDI!</h2>";
    echo "<p>Şimdi <a href='index.php' style='color:#3b82f6'>GİRİŞ SAYFASI</a>'na gidebilirsiniz.</p>";
    echo "<p class='err'><strong>ÖNEMLİ:</strong> Bu dosyayı (db_fix.php) güvenlik nedeniyle SİLİN!</p>";

} catch (Exception $e) {
    echo "<p class='err'>HATA: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
