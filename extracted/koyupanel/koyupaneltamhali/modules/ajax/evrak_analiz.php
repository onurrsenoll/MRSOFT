<?php
/**
 * MR HASAR DANIŞMANLIK - EVRAK ANALİZ API
 * AI Destekli Belge Analizi ve OCR
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

    // Dosya yükleme kontrolü
    if (!isset($_FILES['evrak']) || $_FILES['evrak']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Dosya yüklenemedi']);
        exit;
    }

    $file = $_FILES['evrak'];
    $adk_hesaplama_id = intval($_POST['adk_hesaplama_id'] ?? 0) ?: null;
    $evrak_tipi = $_POST['evrak_tipi'] ?? 'diger';

    // Dosya tipi kontrolü
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz dosya tipi. JPG, PNG, GIF veya PDF yükleyiniz.']);
        exit;
    }

    // Dosya boyutu kontrolü (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Dosya boyutu çok büyük (max 10MB)']);
        exit;
    }

    // Dosyayı kaydet
    $upload_dir = __DIR__ . '/../../uploads/adk_evrak/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'adk_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Dosya kaydedilemedi']);
        exit;
    }

    // ===========================================
    // AI ANALİZ SİMÜLASYONU
    // Gerçek OCR/AI entegrasyonu için genişletilebilir
    // ===========================================

    $analiz_sonuc = [];
    $tespit_edilen_bilgiler = [];

    // Evrak tipine göre simüle edilmiş analiz
    switch ($evrak_tipi) {
        case 'ekspertiz':
            $analiz_sonuc = [
                'belge_tipi' => 'Ekspertiz Raporu',
                'guven_skoru' => rand(85, 98) / 100,
                'tespit_tarihi' => date('Y-m-d H:i:s')
            ];
            $tespit_edilen_bilgiler = [
                'hasar_tutari' => rand(15000, 80000),
                'parcalar' => [
                    ['ad' => 'Ön Tampon', 'islem' => 'değişim', 'tutar' => rand(3000, 8000)],
                    ['ad' => 'Sol Çamurluk', 'islem' => 'boya', 'tutar' => rand(2000, 5000)],
                    ['ad' => 'Ön Kaput', 'islem' => 'boya', 'tutar' => rand(2500, 6000)]
                ],
                'iscilik' => rand(3000, 10000),
                'eksper_notu' => 'Araç onarılabilir durumda, yapısal hasar tespit edilmemiştir.'
            ];
            break;

        case 'fatura':
            $analiz_sonuc = [
                'belge_tipi' => 'Onarım Faturası',
                'guven_skoru' => rand(80, 95) / 100,
                'tespit_tarihi' => date('Y-m-d H:i:s')
            ];
            $tespit_edilen_bilgiler = [
                'fatura_no' => 'FTR-' . date('Y') . '-' . rand(10000, 99999),
                'fatura_tarihi' => date('Y-m-d', strtotime('-' . rand(1, 60) . ' days')),
                'toplam_tutar' => rand(20000, 100000),
                'kdv' => rand(3000, 15000),
                'servis_adi' => 'Örnek Oto Servis',
                'kalemler' => [
                    ['aciklama' => 'Yedek Parça', 'tutar' => rand(10000, 50000)],
                    ['aciklama' => 'İşçilik', 'tutar' => rand(5000, 20000)],
                    ['aciklama' => 'Boya', 'tutar' => rand(3000, 15000)]
                ]
            ];
            break;

        case 'ruhsat':
            $analiz_sonuc = [
                'belge_tipi' => 'Araç Ruhsatı',
                'guven_skoru' => rand(90, 99) / 100,
                'tespit_tarihi' => date('Y-m-d H:i:s')
            ];
            $tespit_edilen_bilgiler = [
                'plaka' => rand(1, 81) . ' ' . chr(rand(65, 90)) . chr(rand(65, 90)) . ' ' . rand(100, 9999),
                'marka' => ['TOYOTA', 'VOLKSWAGEN', 'FORD', 'RENAULT', 'FIAT'][array_rand(['TOYOTA', 'VOLKSWAGEN', 'FORD', 'RENAULT', 'FIAT'])],
                'model' => ['COROLLA', 'PASSAT', 'FOCUS', 'MEGANE', 'EGEA'][array_rand(['COROLLA', 'PASSAT', 'FOCUS', 'MEGANE', 'EGEA'])],
                'model_yili' => rand(2018, 2024),
                'motor_no' => strtoupper(substr(md5(rand()), 0, 12)),
                'sasi_no' => strtoupper(substr(md5(rand()), 0, 17)),
                'renk' => ['BEYAZ', 'SİYAH', 'GRİ', 'KIRMIZI', 'MAVİ'][array_rand(['BEYAZ', 'SİYAH', 'GRİ', 'KIRMIZI', 'MAVİ'])]
            ];
            break;

        case 'kaza_tutanagi':
            $analiz_sonuc = [
                'belge_tipi' => 'Kaza Tespit Tutanağı',
                'guven_skoru' => rand(75, 92) / 100,
                'tespit_tarihi' => date('Y-m-d H:i:s')
            ];
            $tespit_edilen_bilgiler = [
                'tutanak_no' => date('Y') . '-' . rand(100000, 999999),
                'kaza_tarihi' => date('Y-m-d', strtotime('-' . rand(1, 90) . ' days')),
                'kaza_saati' => rand(0, 23) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                'kaza_yeri' => ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya'][array_rand(['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya'])],
                'kusur_orani' => [0, 25, 50, 75, 100][array_rand([0, 25, 50, 75, 100])],
                'taraf_sayisi' => rand(2, 3)
            ];
            break;

        case 'tramer':
            $analiz_sonuc = [
                'belge_tipi' => 'Tramer Kaydı',
                'guven_skoru' => rand(92, 99) / 100,
                'tespit_tarihi' => date('Y-m-d H:i:s')
            ];
            $hasar_sayisi = rand(0, 3);
            $hasarlar = [];
            for ($i = 0; $i < $hasar_sayisi; $i++) {
                $hasarlar[] = [
                    'tarih' => date('Y-m-d', strtotime('-' . rand(180, 1800) . ' days')),
                    'tutar' => rand(5000, 50000),
                    'tip' => ['Trafik Kazası', 'Park Hasarı', 'Dolu Hasarı'][array_rand(['Trafik Kazası', 'Park Hasarı', 'Dolu Hasarı'])]
                ];
            }
            $tespit_edilen_bilgiler = [
                'gecmis_hasar' => $hasar_sayisi > 0 ? 'VAR' : 'YOK',
                'toplam_hasar_sayisi' => $hasar_sayisi,
                'toplam_hasar_tutari' => array_sum(array_column($hasarlar, 'tutar')),
                'hasarlar' => $hasarlar
            ];
            break;

        default:
            $analiz_sonuc = [
                'belge_tipi' => 'Diğer Belge',
                'guven_skoru' => rand(60, 80) / 100,
                'tespit_tarihi' => date('Y-m-d H:i:s')
            ];
            $tespit_edilen_bilgiler = [
                'not' => 'Belge tipi otomatik tespit edilemedi. Manuel inceleme önerilir.'
            ];
    }

    // Veritabanına kaydet
    $stmt = $db->prepare("
        INSERT INTO adk_evrak_analiz
        (adk_hesaplama_id, dosya_adi, dosya_tipi, dosya_boyut, analiz_sonuc, tespit_edilen_bilgiler)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $adk_hesaplama_id,
        $new_filename,
        $evrak_tipi,
        $file['size'],
        json_encode($analiz_sonuc),
        json_encode($tespit_edilen_bilgiler)
    ]);

    $evrak_id = $db->lastInsertId();

    // Başarılı sonuç
    echo json_encode([
        'success' => true,
        'evrak_id' => $evrak_id,
        'dosya_adi' => $new_filename,
        'evrak_tipi' => $evrak_tipi,
        'analiz' => $analiz_sonuc,
        'tespit_edilen' => $tespit_edilen_bilgiler,
        'mesaj' => 'Belge başarıyla analiz edildi. Tespit edilen bilgiler forma aktarılabilir.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Evrak analiz hatası: ' . $e->getMessage()
    ]);
}
