<?php
/**
 * MR HASAR DANIŞMANLIK VE FİLO YÖNETİMİ - KONFİGÜRASYON
 * HERZAMAN FARKEDER
 * V2.0 SON SÜRÜM
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
date_default_timezone_set('Europe/Istanbul');

// VERİTABANI AYARLARI
define('DB_HOST', 'localhost');
define('DB_NAME', 'mrhasard_mrhasar_db');
define('DB_USER', 'mrhasard_mrhasar_db');
define('DB_PASS', 'Bx&jsqf.55');
define('DB_CHARSET', 'utf8mb4');

// UYGULAMA AYARLARI
define('APP_NAME', 'MR HASAR DANIŞMANLIK VE FİLO YÖNETİMİ');
define('APP_SLOGAN', 'HERZAMAN FARKEDER');
define('APP_VERSION', '2.0.0');

// GÜVENLİK
define('SESSION_NAME', 'SAY_SESSION');
define('AVUKAT_HISSE_YUZDESI', 50.00);

/**
 * VERİTABANI BAĞLANTISI
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("<div style='background:#ef4444;color:#fff;padding:20px;font-family:Arial;text-align:center;margin:50px;border-radius:10px;'><h2>VERİTABANI BAĞLANTI HATASI</h2><p>" . $e->getMessage() . "</p></div>");
        }
    }
    return $pdo;
}

/**
 * GÜVENLİ SESSION BAŞLAT
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

/**
 * GİRİŞ KONTROLÜ
 */
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * ROL KONTROLÜ (Case-insensitive)
 */
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    if (is_string($roles)) $roles = [$roles];
    $userRole = strtoupper($_SESSION['user_role'] ?? '');
    foreach ($roles as $role) {
        if (strtoupper($role) === $userRole) return true;
    }
    return false;
}

/**
 * MENÜ YETKİ KONTROLÜ
 */
function hasPermission($menu_key) {
    if (!isLoggedIn()) return false;

    // Admin her zaman tam yetki (case-insensitive)
    $userRole = strtoupper($_SESSION['user_role'] ?? '');
    if ($userRole === 'ADMIN' || $userRole === 'YÖNETİCİ') return true;

    // Yetkileri session'dan al (cache)
    if (!isset($_SESSION['user_permissions'])) {
        loadUserPermissions();
    }

    return in_array($menu_key, $_SESSION['user_permissions'] ?? []);
}

/**
 * KULLANICI YETKİLERİNİ YÜKLE
 */
function loadUserPermissions() {
    if (!isLoggedIn()) return;
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT menu_key FROM user_permissions WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['user_permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $_SESSION['user_permissions'] = [];
    }
}

/**
 * YETKİLERİ YENİLE (Yetki değişikliğinde çağrılır)
 */
function refreshUserPermissions() {
    unset($_SESSION['user_permissions']);
    loadUserPermissions();
}

/**
 * SAYFA YETKİ KONTROLÜ (Sayfa başında kullanılır)
 */
function checkPagePermission($menu_key) {
    if (!hasPermission($menu_key)) {
        header('Location: dashboard.php?error=yetkisiz');
        exit;
    }
}

/**
 * GÜVENLİ ÇIKTI (XSS KORUMA)
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * DOSYA NUMARASI OLUŞTUR
 */
function generateDosyaNo() {
    $db = getDB();
    $year = date('Y');
    $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(dosya_no, 5) AS UNSIGNED)) as mx FROM cases WHERE dosya_no LIKE '{$year}%'");
    $row = $stmt->fetch();
    $max = $row['mx'] ?? 0;
    return $year . str_pad($max + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * DENETİM KAYDI
 */
function auditLog($entity, $id, $action) {
    if (!isLoggedIn()) return;
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, entity_type, entity_id, action, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $entity,
            $id,
            $action,
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    } catch (Exception $e) {
        // Sessizce devam et
    }
}