<?php
/**
 * MR HASAR DANIŞMANLIK - PİYASA ARAŞTIRMA API
 * AI Destekli Araç Piyasa Değeri Araştırma
 */

require_once __DIR__ . '/../../config.php';
startSecureSession();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

try {
    $db = getDB();

    // Form verilerini al
    $marka = trim($_POST['marka'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $model_yili = intval($_POST['model_yili'] ?? 0);
    $kilometre = intval(str_replace(['.', ',', ' '], '', $_POST['kilometre'] ?? '0'));

    // Validasyon
    if (empty($marka) || empty($model) || $model_yili == 0) {
        echo json_encode(['success' => false, 'message' => 'Marka, model ve yıl zorunludur']);
        exit;
    }

    // KM aralığını belirle
    if ($kilometre <= 50000) {
        $km_aralik = '0-50k';
    } elseif ($kilometre <= 100000) {
        $km_aralik = '50k-100k';
    } elseif ($kilometre <= 150000) {
        $km_aralik = '100k-150k';
    } else {
        $km_aralik = '150k+';
    }

    // Önce cache'de ara (son 7 gün içinde)
    $stmt = $db->prepare("
        SELECT * FROM adk_piyasa_cache
        WHERE marka = ? AND model = ? AND model_yili = ? AND km_aralik = ?
        AND gecerlilik_bitis > NOW()
        ORDER BY arastirma_tarihi DESC
        LIMIT 1
    ");
    $stmt->execute([$marka, $model, $model_yili, $km_aralik]);
    $cache = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cache) {
        // Cache'den döndür
        echo json_encode([
            'success' => true,
            'source' => 'cache',
            'cache_id' => $cache['id'],
            'marka' => $marka,
            'model' => $model,
            'model_yili' => $model_yili,
            'km_aralik' => $km_aralik,
            'min_fiyat' => floatval($cache['min_fiyat']),
            'max_fiyat' => floatval($cache['max_fiyat']),
            'ortalama_fiyat' => floatval($cache['ortalama_fiyat']),
            'ilan_sayisi' => intval($cache['ilan_sayisi']),
            'ilanlar' => json_decode($cache['ilanlar'], true) ?: [],
            'arastirma_tarihi' => $cache['arastirma_tarihi']
        ]);
        exit;
    }

    // ===========================================
    // YENİ PİYASA ARAŞTIRMASI
    // Simüle edilmiş veri (gerçek API entegrasyonu için genişletilebilir)
    // ===========================================

    // Temel değer hesaplama (marka ve yıla göre)
    $arac_yasi = date('Y') - $model_yili;

    // Marka premium katsayıları
    $marka_katsayi = 1.0;
    $premium_markalar = ['MERCEDES', 'BMW', 'AUDI', 'PORSCHE', 'LEXUS', 'VOLVO', 'LAND ROVER', 'JAGUAR'];
    $ust_segment = ['VOLKSWAGEN', 'TOYOTA', 'HONDA', 'MAZDA', 'SKODA', 'SEAT', 'PEUGEOT', 'CITROEN'];

    $marka_upper = mb_strtoupper($marka, 'UTF-8');
    if (in_array($marka_upper, $premium_markalar)) {
        $marka_katsayi = 1.35;
    } elseif (in_array($marka_upper, $ust_segment)) {
        $marka_katsayi = 1.15;
    }

    // Temel değer (2024 model ortalama araç değeri baz alınarak)
    $temel_deger = 850000; // TL

    // Yaş düşüşü (yıllık ortalama %12-15 değer kaybı)
    $yas_faktoru = pow(0.87, $arac_yasi);

    // KM faktörü
    $km_faktoru = 1.0;
    if ($kilometre > 150000) $km_faktoru = 0.85;
    elseif ($kilometre > 100000) $km_faktoru = 0.90;
    elseif ($kilometre > 50000) $km_faktoru = 0.95;

    // Hesaplanmış ortalama değer
    $ortalama = $temel_deger * $marka_katsayi * $yas_faktoru * $km_faktoru;

    // Min-Max aralığı (%15 sapma)
    $min_fiyat = round($ortalama * 0.85, -3);
    $max_fiyat = round($ortalama * 1.15, -3);
    $ortalama_fiyat = round($ortalama, -3);

    // Simüle edilmiş emsal ilanlar
    $ilanlar = [];
    $platformlar = ['sahibinden.com', 'arabam.com', 'letgo.com', 'otomerkezi.net'];

    for ($i = 0; $i < rand(5, 12); $i++) {
        $ilan_fiyat = $ortalama * (0.85 + (rand(0, 30) / 100));
        $ilan_km = $kilometre + rand(-20000, 30000);
        if ($ilan_km < 0) $ilan_km = rand(5000, 30000);

        $ilanlar[] = [
            'platform' => $platformlar[array_rand($platformlar)],
            'baslik' => $marka . ' ' . $model . ' ' . $model_yili,
            'fiyat' => round($ilan_fiyat, -2),
            'km' => $ilan_km,
            'il' => ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya'][array_rand(['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya'])],
            'tarih' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days'))
        ];
    }

    // Fiyata göre sırala
    usort($ilanlar, function($a, $b) {
        return $a['fiyat'] - $b['fiyat'];
    });

    // Cache'e kaydet (7 gün geçerli)
    $stmt = $db->prepare("
        INSERT INTO adk_piyasa_cache
        (marka, model, model_yili, km_aralik, min_fiyat, max_fiyat, ortalama_fiyat, ilan_sayisi, ilanlar, gecerlilik_bitis)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
    ");
    $stmt->execute([
        $marka, $model, $model_yili, $km_aralik,
        $min_fiyat, $max_fiyat, $ortalama_fiyat,
        count($ilanlar), json_encode($ilanlar)
    ]);

    $cache_id = $db->lastInsertId();

    // Başarılı sonuç
    echo json_encode([
        'success' => true,
        'source' => 'fresh',
        'cache_id' => $cache_id,
        'marka' => $marka,
        'model' => $model,
        'model_yili' => $model_yili,
        'km_aralik' => $km_aralik,
        'min_fiyat' => $min_fiyat,
        'max_fiyat' => $max_fiyat,
        'ortalama_fiyat' => $ortalama_fiyat,
        'ilan_sayisi' => count($ilanlar),
        'ilanlar' => $ilanlar,
        'arastirma_tarihi' => date('Y-m-d H:i:s'),
        'metodoloji' => [
            'aciklama' => 'Piyasa değeri, marka segmenti, araç yaşı ve kilometre faktörleri baz alınarak hesaplanmıştır.',
            'marka_katsayi' => $marka_katsayi,
            'yas_faktoru' => round($yas_faktoru, 3),
            'km_faktoru' => $km_faktoru
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Piyasa araştırma hatası: ' . $e->getMessage()
    ]);
}
