<?php
/**
 * MR HASAR DANIŞMANLIK - Çıkış
 * Bağımsız logout dosyası
 */

// Oturumu başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Oturumu temizle
$_SESSION = array();

// Session cookie'sini sil
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Oturumu yok et
session_destroy();

// Login sayfasına yönlendir
header('Location: login.php');
exit;
