<?php
/**
 * MR HASAR DANIŞMANLIK - Config Dosyası
 * HERZAMAN FARKEDER
 */

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlama (production'da kapatılmalı)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Veritabanı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'mrhasard_finansal');
define('DB_USER', 'mrhasard_finansal');
define('DB_PASS', 'Smyr2022!');
define('DB_CHARSET', 'utf8mb4');

// Uygulama ayarları
define('APP_NAME', 'MR HASAR DANIŞMANLIK');
define('APP_SLOGAN', 'HERZAMAN FARKEDER');
define('APP_VERSION', 'v11.0');
define('APP_URL', 'https://mrhasardanismanlik.com/finansal/admin/');

// Dosya yolları
define('BASE_PATH', __DIR__ . '/');
define('UPLOADS_PATH', BASE_PATH . 'uploads/');
define('DOCUMENTS_PATH', UPLOADS_PATH . 'documents/');
define('TEMP_PATH', UPLOADS_PATH . 'temp/');

// Dosya boyutu limitleri
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx']);

// Veritabanı bağlantısı (PDO)
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// ═══════════════════════════════════════════════════════════════
// YARDIMCI FONKSİYONLAR
// ═══════════════════════════════════════════════════════════════

/**
 * Güvenli input temizleme
 */
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * XSS korumalı çıktı
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF token oluştur
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Oturum kontrolü
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Yetki kontrolü
 */
function hasPermission($permission) {
    if (!isLoggedIn()) return false;
    if ($_SESSION['user_role'] === 'admin') return true;

    $permissions = $_SESSION['user_permissions'] ?? [];
    return in_array($permission, $permissions);
}

/**
 * Kullanıcı rolü kontrolü
 */
function hasRole($role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['user_role'] === $role;
}

/**
 * Giris sayfasina yonlendir
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

/**
 * Para formatı
 */
function formatMoney($amount, $currency = '₺') {
    return number_format($amount, 2, ',', '.') . ' ' . $currency;
}

/**
 * Tarih formatı
 */
function formatDate($date, $format = 'd.m.Y') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Tarih saat formatı
 */
function formatDateTime($datetime, $format = 'd.m.Y H:i') {
    if (empty($datetime)) return '-';
    return date($format, strtotime($datetime));
}

/**
 * TC Kimlik doğrulama
 */
function validateTC($tc) {
    if (strlen($tc) != 11) return false;
    if ($tc[0] == '0') return false;
    if (!ctype_digit($tc)) return false;

    $odds = $tc[0] + $tc[2] + $tc[4] + $tc[6] + $tc[8];
    $evens = $tc[1] + $tc[3] + $tc[5] + $tc[7];
    $digit10 = (($odds * 7) - $evens) % 10;
    $digit11 = ($odds + $evens + $tc[9]) % 10;

    return ($tc[9] == $digit10 && $tc[10] == $digit11);
}

/**
 * Dosya numarası oluştur
 */
function generateFileNo($pdo, $type = 'ADK') {
    $prefix = ($type === 'ADK') ? 'ADK' : 'BH';
    $year = date('Y');

    $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(file_no, -4) AS UNSIGNED)) as max_no
                           FROM cases
                           WHERE file_no LIKE ?");
    $stmt->execute([$prefix . '-' . $year . '-%']);
    $result = $stmt->fetch();

    $nextNo = ($result['max_no'] ?? 0) + 1;
    return $prefix . '-' . $year . '-' . str_pad($nextNo, 4, '0', STR_PAD_LEFT);
}

/**
 * Aktivite log kaydet
 */
function logActivity($pdo, $action, $tableName = null, $recordId = null, $oldData = null, $newData = null) {
    if (!isLoggedIn()) return;

    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $tableName,
        $recordId,
        $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
        $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

/**
 * Flash mesaj ayarla
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Flash mesaj göster
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sayfalama hesapla
 */
function paginate($totalItems, $currentPage = 1, $perPage = 20) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $totalItems,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Dosya yükleme
 */
function uploadFile($file, $destination, $allowedTypes = null) {
    if ($allowedTypes === null) {
        $allowedTypes = ALLOWED_EXTENSIONS;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Dosya yükleme hatası'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Dosya boyutu çok büyük'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'Geçersiz dosya türü'];
    }

    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $destination . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Dosya kaydedilemedi'];
    }

    return [
        'success' => true,
        'filename' => $filename,
        'filepath' => $filepath,
        'original_name' => $file['name'],
        'size' => $file['size']
    ];
}

/**
 * Okunmamış mesaj sayısı
 */
function getUnreadMessageCount($pdo) {
    if (!isLoggedIn()) return 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE to_user = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}

/**
 * Bugünkü hatırlatmalar
 */
function getTodayReminders($pdo) {
    if (!isLoggedIn()) return [];

    $stmt = $pdo->prepare("SELECT * FROM calendar_events
                           WHERE event_date = CURDATE()
                           AND (user_id = ? OR user_id IS NULL)
                           AND is_completed = 0
                           ORDER BY event_time ASC");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll();
}

/**
 * Dashboard istatistikleri
 */
function getDashboardStats($pdo) {
    $stats = [];

    // Toplam dosya sayısı
    $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status != 'İPTAL'");
    $stats['total_cases'] = $stmt->fetchColumn();

    // Aktif dosya sayısı
    $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE status = 'AKTIF'");
    $stats['active_cases'] = $stmt->fetchColumn();

    // Bu ay açılan dosyalar
    $stmt = $pdo->query("SELECT COUNT(*) FROM cases WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $stats['monthly_cases'] = $stmt->fetchColumn();

    // Toplam tahsilat
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM case_payments WHERE payment_type = 'TAHSİLAT'");
    $stats['total_collection'] = $stmt->fetchColumn();

    // Bu ay tahsilat
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM case_payments WHERE payment_type = 'TAHSİLAT' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $stats['monthly_collection'] = $stmt->fetchColumn();

    // Bekleyen lead sayısı
    $stmt = $pdo->query("SELECT COUNT(*) FROM crm_leads WHERE status = 'BEKLEMEDE'");
    $stats['pending_leads'] = $stmt->fetchColumn();

    return $stats;
}

/**
 * Dropdown için veri çek
 */
function getDropdownData($pdo, $table, $valueField = 'id', $textField = 'name', $where = 'is_active = 1', $order = 'name ASC') {
    $sql = "SELECT {$valueField}, {$textField} FROM {$table}";
    if ($where) $sql .= " WHERE {$where}";
    if ($order) $sql .= " ORDER BY {$order}";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Ayar değeri al
 */
function getSetting($pdo, $key, $default = null) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

/**
 * Ayar değeri kaydet
 */
function setSetting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}
