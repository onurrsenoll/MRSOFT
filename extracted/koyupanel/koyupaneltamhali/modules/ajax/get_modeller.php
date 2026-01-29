<?php
/**
 * MR HASAR DANIŞMANLIK - Model ve Paket Getir API
 * Marka seçimine göre model ve paketleri döndürür
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

$marka = $_GET['marka'] ?? '';
$model = $_GET['model'] ?? '';
$format = $_GET['format'] ?? 'simple'; // simple veya detailed

if (empty($marka)) {
    echo json_encode([]);
    exit;
}

try {
    $db = getDB();

    // Sadece model isteniyor (paket seçimi için)
    if (!empty($model)) {
        $stmt = $db->prepare("
            SELECT DISTINCT paket
            FROM arac_veritabani
            WHERE marka = ? AND model = ? AND paket IS NOT NULL AND paket != ''
            ORDER BY paket
        ");
        $stmt->execute([$marka, $model]);
        $paketler = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($paketler);
        exit;
    }

    // Detaylı format - model + paket birlikte
    if ($format === 'detailed') {
        $stmt = $db->prepare("
            SELECT model, paket, segment, yakit_tipi
            FROM arac_veritabani
            WHERE marka = ? AND aktif = 1
            ORDER BY model, paket
        ");
        $stmt->execute([$marka]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Model bazlı gruplama
        $grouped = [];
        foreach ($results as $row) {
            $modelName = $row['model'];
            if (!isset($grouped[$modelName])) {
                $grouped[$modelName] = [
                    'model' => $modelName,
                    'segment' => $row['segment'],
                    'paketler' => []
                ];
            }
            if (!empty($row['paket'])) {
                $grouped[$modelName]['paketler'][] = [
                    'paket' => $row['paket'],
                    'yakit_tipi' => $row['yakit_tipi']
                ];
            }
        }

        echo json_encode(array_values($grouped));
        exit;
    }

    // Basit format - sadece model listesi
    $stmt = $db->prepare("
        SELECT DISTINCT model
        FROM arac_veritabani
        WHERE marka = ? AND aktif = 1
        ORDER BY model
    ");
    $stmt->execute([$marka]);
    $modeller = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($modeller);

} catch (Exception $e) {
    // Hata durumunda boş array döndür
    echo json_encode([]);
}
