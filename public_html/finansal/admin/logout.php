<?php
/**
 * MR HASAR DANIŞMANLIK - Çıkış
 * HERZAMAN FARKEDER
 */

require_once 'config.php';

// Aktivite log
if (isLoggedIn()) {
    logActivity($pdo, 'LOGOUT', 'users', $_SESSION['user_id']);
}

// Oturumu sonlandır
session_unset();
session_destroy();

// Giriş sayfasına yönlendir
header('Location: login.php');
exit;
