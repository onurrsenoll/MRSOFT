<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

$marka = $_GET['marka'] ?? '';

if (empty($marka)) {
    echo json_encode([]);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT DISTINCT model FROM arac_veritabani WHERE marka = ? ORDER BY model");
    $stmt->execute([$marka]);
    $modeller = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($modeller);
} catch (Exception $e) {
    echo json_encode([]);
}