<?php
/**
 * MR HASAR DANIŞMANLIK - ADK HESAPLAMA API
 * AI Destekli Gelişmiş Araç Değer Kaybı Hesaplama
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
    $case_id = intval($_POST['case_id'] ?? 0) ?: null;
    $marka = trim($_POST['marka'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $model_yili = intval($_POST['model_yili'] ?? 0);
    $kilometre = intval(str_replace(['.', ',', ' '], '', $_POST['kilometre'] ?? '0'));
    $arac_degeri = floatval(str_replace(['.', ',', ' ', '₺', 'TL'], ['', '.', '', '', ''], $_POST['arac_degeri'] ?? '0'));
    $hasar_tutari = floatval(str_replace(['.', ',', ' ', '₺', 'TL'], ['', '.', '', '', ''], $_POST['hasar_tutari'] ?? '0'));
    $gecmis_hasar = $_POST['gecmis_hasar'] ?? 'YOK';
    $kusur_orani = intval($_POST['kusur_orani'] ?? 100);
    $parcalar = $_POST['parcalar'] ?? [];

    // Validasyon
    if (empty($marka) || empty($model) || $model_yili == 0) {
        echo json_encode(['success' => false, 'message' => 'Marka, model ve yıl zorunludur']);
        exit;
    }

    if ($arac_degeri <= 0) {
        echo json_encode(['success' => false, 'message' => 'Araç değeri giriniz']);
        exit;
    }

    // ===========================================
    // GÜNCEL METODOLOJİ: TBK/YARGITAY YÖNTEMİ
    // Değer Kaybı = Kaza Öncesi Değer - Kaza Sonrası Değer
    // Katsayılar üzerinden hesaplama
    // ===========================================

    $arac_yasi = date('Y') - $model_yili;

    // YAŞ KATSAYISI
    $yas_katsayi = 1.0;
    $stmt = $db->prepare("SELECT katsayi FROM adk_katsayilar WHERE kategori = 'yas' AND ? BETWEEN min_deger AND max_deger AND aktif = 1 LIMIT 1");
    $stmt->execute([$arac_yasi]);
    $row = $stmt->fetch();
    if ($row) {
        $yas_katsayi = floatval($row['katsayi']);
    } else {
        // Varsayılan yaş katsayıları
        if ($arac_yasi <= 1) $yas_katsayi = 1.20;
        elseif ($arac_yasi <= 3) $yas_katsayi = 1.10;
        elseif ($arac_yasi <= 5) $yas_katsayi = 1.00;
        elseif ($arac_yasi <= 7) $yas_katsayi = 0.90;
        elseif ($arac_yasi <= 10) $yas_katsayi = 0.80;
        elseif ($arac_yasi <= 15) $yas_katsayi = 0.60;
        else $yas_katsayi = 0.40;
    }

    // KM KATSAYISI
    $km_katsayi = 1.0;
    $stmt = $db->prepare("SELECT katsayi FROM adk_katsayilar WHERE kategori = 'km' AND ? BETWEEN min_deger AND max_deger AND aktif = 1 LIMIT 1");
    $stmt->execute([$kilometre]);
    $row = $stmt->fetch();
    if ($row) {
        $km_katsayi = floatval($row['katsayi']);
    } else {
        // Varsayılan km katsayıları
        if ($kilometre <= 25000) $km_katsayi = 1.20;
        elseif ($kilometre <= 50000) $km_katsayi = 1.10;
        elseif ($kilometre <= 75000) $km_katsayi = 1.00;
        elseif ($kilometre <= 100000) $km_katsayi = 0.90;
        elseif ($kilometre <= 150000) $km_katsayi = 0.80;
        elseif ($kilometre <= 200000) $km_katsayi = 0.70;
        else $km_katsayi = 0.50;
    }

    // HASAR ORANI KATSAYISI
    $hasar_orani = ($arac_degeri > 0) ? ($hasar_tutari / $arac_degeri) : 0;
    if ($hasar_orani <= 0.05) $hasar_katsayi = 0.80;
    elseif ($hasar_orani <= 0.10) $hasar_katsayi = 0.90;
    elseif ($hasar_orani <= 0.20) $hasar_katsayi = 1.00;
    elseif ($hasar_orani <= 0.30) $hasar_katsayi = 1.10;
    elseif ($hasar_orani <= 0.40) $hasar_katsayi = 1.20;
    else $hasar_katsayi = 1.30;

    // GEÇMİŞ HASAR KATSAYISI
    $gecmis_hasar_katsayi = ($gecmis_hasar == 'VAR') ? 0.85 : 1.00;

    // PARÇA KATSAYISI
    $parca_katsayi = 1.0;
    $secili_parcalar = [];

    foreach ($parcalar as $parca_id => $parca_data) {
        if (!empty($parca_data['selected'])) {
            $islem = $parca_data['islem'] ?? 'boya';
            $secili_parcalar[] = [
                'parca' => $parca_id,
                'islem' => $islem
            ];

            // İşlem türüne göre katsayı ekle
            if ($islem == 'degisim') {
                $parca_katsayi += 0.025; // Her değişim için +%2.5
            } elseif ($islem == 'boya') {
                $parca_katsayi += 0.015; // Her boya için +%1.5
            } else {
                $parca_katsayi += 0.010; // Her onarım için +%1
            }
        }
    }

    // Parça katsayısı üst limiti
    if ($parca_katsayi > 1.50) $parca_katsayi = 1.50;

    // ===========================================
    // TEMEL DEĞER KAYBI HESAPLAMASI
    // Yargıtay içtihatlarına göre: %5-25 aralığında
    // ===========================================

    // Temel oran (Yargıtay kararlarına göre)
    $temel_oran = 0.08; // %8 baz oran

    // Tüm katsayıları çarp
    $toplam_katsayi = $yas_katsayi * $km_katsayi * $hasar_katsayi * $gecmis_hasar_katsayi * $parca_katsayi;

    // Değer kaybı hesapla
    $deger_kaybi = $arac_degeri * $temel_oran * $toplam_katsayi;

    // Kusur oranını uygula
    $deger_kaybi = $deger_kaybi * ($kusur_orani / 100);

    // Limitler
    $min_limit = $arac_degeri * 0.02; // Minimum %2
    $max_limit = $arac_degeri * 0.25; // Maksimum %25

    if ($deger_kaybi < $min_limit) $deger_kaybi = $min_limit;
    if ($deger_kaybi > $max_limit) $deger_kaybi = $max_limit;

    // Yuvarlama
    $deger_kaybi = round($deger_kaybi, -2); // En yakın 100'e yuvarla

    // Değer kaybı oranı
    $deger_kaybi_orani = ($arac_degeri > 0) ? (($deger_kaybi / $arac_degeri) * 100) : 0;

    // ===========================================
    // VERITABANINA KAYDET
    // ===========================================

    // Rapor numarası oluştur
    $rapor_no = 'ADK-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO adk_hesaplamalar (
        case_id, marka, model, model_yili, kilometre,
        arac_degeri, hasar_tutari, gecmis_hasar, kusur_orani, parcalar,
        deger_kaybi, deger_kaybi_orani, hesaplama_yontemi,
        yas_katsayi, km_katsayi, hasar_katsayi, parca_katsayi, gecmis_hasar_katsayi,
        rapor_no, rapor_tarihi, created_by
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, 'TBK_YARGITAY',
        ?, ?, ?, ?, ?,
        ?, NOW(), ?
    )";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        $case_id, $marka, $model, $model_yili, $kilometre,
        $arac_degeri, $hasar_tutari, $gecmis_hasar, $kusur_orani, json_encode($secili_parcalar),
        $deger_kaybi, $deger_kaybi_orani,
        $yas_katsayi, $km_katsayi, $hasar_katsayi, $parca_katsayi, $gecmis_hasar_katsayi,
        $rapor_no, $_SESSION['user_id'] ?? null
    ]);

    $hesaplama_id = $db->lastInsertId();

    // Başarılı sonuç
    echo json_encode([
        'success' => true,
        'hesaplama_id' => $hesaplama_id,
        'rapor_no' => $rapor_no,
        'deger_kaybi' => $deger_kaybi,
        'arac_degeri' => $arac_degeri,
        'oran' => $deger_kaybi_orani,
        'katsayilar' => [
            'yas' => $yas_katsayi,
            'km' => $km_katsayi,
            'hasar' => $hasar_katsayi,
            'parca' => $parca_katsayi,
            'gecmis_hasar' => $gecmis_hasar_katsayi,
            'toplam' => $toplam_katsayi
        ],
        'parcalar' => $secili_parcalar
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Hesaplama hatası: ' . $e->getMessage()
    ]);
}
