<?php
/**
 * ADK API - Değer Kaybı Hesaplama API
 */

require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_markalar':
        getMarkalar();
        break;
    case 'get_modeller':
        getModeller();
        break;
    case 'hesapla':
        hesapla();
        break;
    case 'kaydet':
        kaydet();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Geçersiz işlem']);
}

// Markaları getir
function getMarkalar() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT DISTINCT marka, MAX(premium) as premium FROM arac_veritabani GROUP BY marka ORDER BY marka");
        $markalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $markalar]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Modelleri getir
function getModeller() {
    global $pdo;
    
    $marka = $_GET['marka'] ?? '';
    
    if (!$marka) {
        echo json_encode(['success' => false, 'error' => 'Marka belirtilmedi']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT model, premium FROM arac_veritabani WHERE marka = ? ORDER BY model");
        $stmt->execute([$marka]);
        $modeller = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $premium = !empty($modeller) && $modeller[0]['premium'] == 1;
        
        echo json_encode(['success' => true, 'data' => $modeller, 'premium' => $premium]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Hesapla
function hesapla() {
    $marka = $_POST['marka'] ?? '';
    $model = $_POST['model'] ?? '';
    $yil = intval($_POST['yil'] ?? 0);
    $km = intval($_POST['km'] ?? 0);
    $rayic = floatval($_POST['rayic'] ?? 0);
    $onarim = floatval($_POST['onarim'] ?? 0);
    $oncekiHasar = intval($_POST['onceki_hasar'] ?? 0);
    $bolge = $_POST['bolge'] ?? 'on';
    
    // Validasyon
    if (!$marka || !$model || !$yil || !$rayic || !$onarim) {
        echo json_encode(['success' => false, 'error' => 'Eksik bilgi']);
        return;
    }
    
    // Premium kontrol
    $premiumMarkalar = ['BMW', 'MERCEDES-BENZ', 'AUDI', 'PORSCHE', 'LEXUS', 'VOLVO', 
        'ALFA ROMEO', 'LAND ROVER', 'JAGUAR', 'MINI', 'TESLA', 'DS', 'CADILLAC', 
        'INFINITI', 'ACURA', 'MASERATI', 'BENTLEY', 'ROLLS-ROYCE', 'FERRARI', 
        'LAMBORGHINI', 'ASTON MARTIN', 'MCLAREN', 'LOTUS', 'POLESTAR', 'NIO', 
        'RIVIAN', 'LUCID', 'HUMMER', 'LINCOLN'];
    $isPremium = in_array(strtoupper($marka), $premiumMarkalar);
    
    // Araç yaşı
    $aracYasi = date('Y') - $yil;
    
    // Katsayılar
    $kmKatsayi = 1.0;
    if ($km > 200000) $kmKatsayi = 0.7;
    elseif ($km > 150000) $kmKatsayi = 0.8;
    elseif ($km > 100000) $kmKatsayi = 0.9;
    elseif ($km > 50000) $kmKatsayi = 0.95;
    
    $yasKatsayi = 1.0;
    if ($aracYasi > 10) $yasKatsayi = 0.6;
    elseif ($aracYasi > 7) $yasKatsayi = 0.75;
    elseif ($aracYasi > 5) $yasKatsayi = 0.85;
    elseif ($aracYasi > 3) $yasKatsayi = 0.95;
    
    $hasarKatsayi = 1.0;
    if ($oncekiHasar >= 4) $hasarKatsayi = 0.5;
    elseif ($oncekiHasar == 3) $hasarKatsayi = 0.65;
    elseif ($oncekiHasar == 2) $hasarKatsayi = 0.8;
    elseif ($oncekiHasar == 1) $hasarKatsayi = 0.9;
    
    $bolgeKatsayi = 1.0;
    if ($bolge == 'on') $bolgeKatsayi = 1.1;
    elseif ($bolge == 'arka') $bolgeKatsayi = 0.9;
    elseif ($bolge == 'yan') $bolgeKatsayi = 1.0;
    elseif ($bolge == 'tavan') $bolgeKatsayi = 1.15;
    
    $premiumCarpan = $isPremium ? 1.15 : 1.0;
    
    // Hesaplamalar
    // 1. Nisbi Yöntem (%25-%35)
    $nisbiMin = $onarim * 0.25;
    $nisbiMax = $onarim * 0.35;
    
    // 2. Piyasa Yöntemi
    $piyasaOran = 0.08; // %8
    $piyasaYontemi = $rayic * $piyasaOran * $kmKatsayi * $yasKatsayi;
    
    // 3. TSB Formülü
    $tsbFormul = ($onarim / $rayic) * $rayic * 0.30;
    
    // Tüm katsayıları uygula
    $toplamKatsayi = $kmKatsayi * $yasKatsayi * $hasarKatsayi * $bolgeKatsayi * $premiumCarpan;
    
    $nisbiMin *= $toplamKatsayi;
    $nisbiMax *= $toplamKatsayi;
    $piyasaYontemi *= $hasarKatsayi * $bolgeKatsayi * $premiumCarpan;
    $tsbFormul *= $toplamKatsayi;
    
    // Önerilen değer (ağırlıklı ortalama)
    $onerilen = ($nisbiMin * 0.2 + $nisbiMax * 0.3 + $piyasaYontemi * 0.25 + $tsbFormul * 0.25);
    $onerilenMin = $onerilen * 0.85;
    $onerilenMax = $onerilen * 1.15;
    
    // AI Analiz metni
    $analiz = "<strong>{$marka} {$model}</strong> ({$yil}) aracınız için değer kaybı analizi tamamlandı.<br><br>";
    $analiz .= "• Araç yaşı: <strong>{$aracYasi} yıl</strong><br>";
    $analiz .= "• Kilometre durumu: <strong>" . number_format($km, 0, ',', '.') . " km</strong><br>";
    $analiz .= "• Önceki hasar: <strong>{$oncekiHasar} adet</strong><br>";
    if ($isPremium) {
        $analiz .= "• <strong>Premium segment</strong> aracı için +%15 düzeltme uygulandı.<br>";
    }
    $analiz .= "<br>Yargıtay içtihatları ve STK kararları doğrultusunda 3 farklı yöntemle hesaplama yapılmıştır.";
    
    // Sonuç
    $result = [
        'success' => true,
        'arac' => [
            'marka' => $marka,
            'model' => $model,
            'yil' => $yil,
            'km' => $km,
            'premium' => $isPremium
        ],
        'giris' => [
            'rayic' => $rayic,
            'onarim' => $onarim,
            'onceki_hasar' => $oncekiHasar,
            'bolge' => $bolge
        ],
        'katsayilar' => [
            'km' => $kmKatsayi,
            'yas' => $yasKatsayi,
            'hasar' => $hasarKatsayi,
            'bolge' => $bolgeKatsayi,
            'premium' => $premiumCarpan
        ],
        'sonuc' => [
            'nisbi_min' => round($nisbiMin, 2),
            'nisbi_max' => round($nisbiMax, 2),
            'piyasa_yontemi' => round($piyasaYontemi, 2),
            'tsb_formul' => round($tsbFormul, 2),
            'onerilen_min' => round($onerilenMin, 2),
            'onerilen_max' => round($onerilenMax, 2),
            'onerilen' => round($onerilen, 2)
        ],
        'analiz' => $analiz
    ];
    
    echo json_encode($result);
}

// Kaydet
function kaydet() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Veri alınamadı']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO adk_hesaplamalar 
            (dosya_id, marka, model, model_yili, kilometre, rayic_deger, onarim_bedeli, 
             onceki_hasar, hasarli_bolge, nisbi_min, nisbi_max, piyasa_yontemi, tsb_formul,
             onerilen_min, onerilen_max, onerilen_deger, km_katsayi, yas_katsayi, 
             hasar_katsayi, bolge_katsayi) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $input['dosya_id'] ?? null,
            $input['marka'],
            $input['model'],
            $input['yil'],
            $input['km'] ?? 0,
            $input['rayic'],
            $input['onarim'],
            $input['onceki_hasar'] ?? 0,
            $input['bolge'] ?? 'on',
            $input['nisbi_min'],
            $input['nisbi_max'],
            $input['piyasa_yontemi'],
            $input['tsb_formul'],
            $input['onerilen_min'],
            $input['onerilen_max'],
            $input['onerilen'],
            $input['katsayilar']['km'] ?? 1,
            $input['katsayilar']['yas'] ?? 1,
            $input['katsayilar']['hasar'] ?? 1,
            $input['katsayilar']['bolge'] ?? 1
        ]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}