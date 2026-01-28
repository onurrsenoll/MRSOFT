<?php
/**
 * MR HASAR DANIŞMANLIK - BH HESAPLAMA
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';
startSecureSession();

$db = getDB();
$error = '';
$success = '';

$case_id = intval($_GET['case_id'] ?? 0);

// Dosya bilgilerini al
$dosya = null;
if ($case_id > 0) {
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$case_id]);
    $dosya = $stmt->fetch(PDO::FETCH_ASSOC);
}

// PMF Yaşam Tablosu (TRH-2010 erkek)
$pmfTablo = [
    0 => 72.90, 5 => 68.29, 10 => 63.38, 15 => 58.46, 20 => 53.57,
    25 => 48.70, 30 => 43.85, 35 => 39.04, 40 => 34.29, 45 => 29.64,
    50 => 25.13, 55 => 20.82, 60 => 16.79, 65 => 13.11, 70 => 9.87,
    75 => 7.14, 80 => 4.94, 85 => 3.31, 90 => 2.17, 95 => 1.42, 100 => 0.50
];

// Yaşa göre kalan ömür hesapla
function getKalanOmur($yas, $pmfTablo) {
    if ($yas >= 100) return 0.5;
    if ($yas < 0) return 72.90;
    
    $altYas = floor($yas / 5) * 5;
    $ustYas = $altYas + 5;
    
    if (!isset($pmfTablo[$altYas])) $altYas = 0;
    if (!isset($pmfTablo[$ustYas])) $ustYas = 100;
    
    $altDeger = $pmfTablo[$altYas];
    $ustDeger = $pmfTablo[$ustYas];
    
    $oran = ($yas - $altYas) / 5;
    return $altDeger - (($altDeger - $ustDeger) * $oran);
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $dogum_tarihi = $_POST['dogum_tarihi'] ?? '';
        $kaza_tarihi = $_POST['kaza_tarihi'] ?? '';
        $maluliyet_orani = floatval($_POST['maluliyet_orani'] ?? 0);
        $kusur_orani = floatval($_POST['kusur_orani'] ?? 100);
        $gelir = floatval(str_replace(['.', ','], ['', '.'], $_POST['gelir'] ?? 0));
        $gelir_turu = $_POST['gelir_turu'] ?? 'aylik';
        $gecici_gun = intval($_POST['gecici_gun'] ?? 0);
        $bakim_gideri = floatval(str_replace(['.', ','], ['', '.'], $_POST['bakim_gideri'] ?? 0));
        $tedavi_gideri = floatval(str_replace(['.', ','], ['', '.'], $_POST['tedavi_gideri'] ?? 0));
        
        // Yaş hesapla
        $dogum = new DateTime($dogum_tarihi);
        $kaza = new DateTime($kaza_tarihi);
        $yas = $kaza->diff($dogum)->y;
        
        // Yıllık gelir
        $yillikGelir = ($gelir_turu == 'aylik') ? $gelir * 12 : $gelir;
        
        // Kalan ömür
        $kalanOmur = getKalanOmur($yas, $pmfTablo);
        
        // Aktif dönem (60 yaşına kadar)
        $aktifYil = max(0, 60 - $yas);
        if ($aktifYil > $kalanOmur) $aktifYil = $kalanOmur;
        
        // Pasif dönem
        $pasifYil = $kalanOmur - $aktifYil;
        
        // Hesaplamalar
        // 1. Aktif dönem tazminatı
        $aktifDonem = $yillikGelir * $aktifYil * ($maluliyet_orani / 100) * ($kusur_orani / 100);
        
        // 2. Pasif dönem tazminatı (asgari ücret baz alınır)
        $asgariUcretYillik = 17002 * 12; // 2024 asgari ücret
        $pasifDonem = $asgariUcretYillik * $pasifYil * ($maluliyet_orani / 100) * ($kusur_orani / 100);
        
        // 3. Geçici iş göremezlik
        $gunlukGelir = $yillikGelir / 365;
        $geciciIsGoremezlik = $gunlukGelir * $gecici_gun * ($kusur_orani / 100);
        
        // 4. Bakım tazminatı
        $bakimTazminati = $bakim_gideri * $kalanOmur * 12 * ($kusur_orani / 100);
        
        // 5. Toplam
        $toplamTazminat = $aktifDonem + $pasifDonem + $geciciIsGoremezlik + $bakimTazminati + $tedavi_gideri;
        
        // Kaydet
        $stmt = $db->prepare("INSERT INTO bh_calculations 
            (case_id, dogum_tarihi, kaza_tarihi, maluliyet_orani, kusur_orani, gelir, gelir_turu,
             gecici_is_goremezlik_gun, bakim_gideri, tedavi_gideri, aktif_donem, pasif_donem,
             gecici_is_goremezlik, bakim_tazminati, toplam_tazminat, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $case_id, $dogum_tarihi, $kaza_tarihi, $maluliyet_orani, $kusur_orani,
            $gelir, $gelir_turu, $gecici_gun, $bakim_gideri, $tedavi_gideri,
            $aktifDonem, $pasifDonem, $geciciIsGoremezlik, $bakimTazminati, $toplamTazminat,
            $_SESSION['user_id']
        ]);
        
        $hesaplama_id = $db->lastInsertId();
        $success = "Hesaplama başarıyla kaydedildi! Toplam Tazminat: " . number_format($toplamTazminat, 2, ',', '.') . " TL";
        
    } catch (Exception $e) {
        $error = "Hata: " . $e->getMessage();
    }
}

// Önceki hesaplamalar
$oncekiHesaplamalar = [];
if ($case_id > 0) {
    $stmt = $db->prepare("SELECT * FROM bh_calculations WHERE case_id = ? ORDER BY created_at DESC");
    $stmt->execute([$case_id]);
    $oncekiHesaplamalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.bh-container { max-width: 1200px; margin: 0 auto; }
.bh-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
.bh-card-title { font-size: 16px; font-weight: 700; color: #10b981; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
.bh-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
.bh-field { display: flex; flex-direction: column; gap: 5px; }
.bh-field label { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
.bh-field input, .bh-field select { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #fff; font-size: 14px; }
.bh-field input:focus, .bh-field select:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
.btn-hesapla { background: linear-gradient(135deg, #10b981, #06b6d4); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 20px; }
.btn-hesapla:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); }
.result-box { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(6, 182, 212, 0.1)); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 20px; text-align: center; margin-top: 20px; }
.result-label { font-size: 12px; color: #94a3b8; text-transform: uppercase; }
.result-value { font-size: 32px; font-weight: 800; color: #10b981; margin-top: 5px; }
.alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.hesaplama-list { margin-top: 20px; }
.hesaplama-item { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
.hesaplama-item:hover { border-color: #10b981; }
.hesaplama-tutar { font-size: 18px; font-weight: 700; color: #10b981; }
.hesaplama-tarih { font-size: 12px; color: #64748b; }
.btn-rapor { background: #334155; color: #f1f5f9; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 12px; }
.btn-rapor:hover { background: #475569; }
@media (max-width: 768px) { .bh-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-title">
    <i class="fas fa-wheelchair"></i> BEDENİ HASAR HESAPLAMA
    <?php if ($dosya): ?>
    <span style="font-size:14px;color:#64748b;margin-left:15px;">Dosya: <?= htmlspecialchars($dosya['dosya_no']) ?></span>
    <?php endif; ?>
</div>

<?php if ($error): ?><div class="alert-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert-success"><?= $success ?></div><?php endif; ?>

<div class="bh-container">
    <form method="POST">
        <!-- KİŞİ BİLGİLERİ -->
        <div class="bh-card">
            <div class="bh-card-title"><i class="fas fa-user"></i> KİŞİ BİLGİLERİ</div>
            <div class="bh-grid">
                <div class="bh-field">
                    <label>Doğum Tarihi</label>
                    <input type="date" name="dogum_tarihi" required value="<?= $dosya['dogum_tarihi'] ?? '' ?>">
                </div>
                <div class="bh-field">
                    <label>Kaza Tarihi</label>
                    <input type="date" name="kaza_tarihi" required value="<?= $dosya['kaza_tarihi'] ?? '' ?>">
                </div>
                <div class="bh-field">
                    <label>Maluliyet Oranı (%)</label>
                    <input type="number" name="maluliyet_orani" step="0.01" min="0" max="100" required placeholder="Örn: 15.5">
                </div>
            </div>
        </div>

        <!-- GELİR BİLGİLERİ -->
        <div class="bh-card">
            <div class="bh-card-title"><i class="fas fa-money-bill-wave"></i> GELİR BİLGİLERİ</div>
            <div class="bh-grid">
                <div class="bh-field">
                    <label>Gelir (TL)</label>
                    <input type="text" name="gelir" required placeholder="Örn: 25.000">
                </div>
                <div class="bh-field">
                    <label>Gelir Türü</label>
                    <select name="gelir_turu">
                        <option value="aylik">Aylık</option>
                        <option value="yillik">Yıllık</option>
                    </select>
                </div>
                <div class="bh-field">
                    <label>Kusur Oranı (%)</label>
                    <input type="number" name="kusur_orani" step="0.01" min="0" max="100" value="100" required>
                </div>
            </div>
        </div>

        <!-- EK GİDERLER -->
        <div class="bh-card">
            <div class="bh-card-title"><i class="fas fa-plus-circle"></i> EK GİDERLER</div>
            <div class="bh-grid">
                <div class="bh-field">
                    <label>Geçici İş Göremezlik (Gün)</label>
                    <input type="number" name="gecici_gun" min="0" value="0">
                </div>
                <div class="bh-field">
                    <label>Aylık Bakım Gideri (TL)</label>
                    <input type="text" name="bakim_gideri" placeholder="Örn: 5.000" value="0">
                </div>
                <div class="bh-field">
                    <label>Tedavi Gideri (TL)</label>
                    <input type="text" name="tedavi_gideri" placeholder="Örn: 50.000" value="0">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-hesapla">
            <i class="fas fa-calculator"></i> HESAPLA VE KAYDET
        </button>
    </form>

    <!-- ÖNCEKİ HESAPLAMALAR -->
    <?php if (!empty($oncekiHesaplamalar)): ?>
    <div class="bh-card" style="margin-top:30px;">
        <div class="bh-card-title"><i class="fas fa-history"></i> ÖNCEKİ HESAPLAMALAR</div>
        <div class="hesaplama-list">
            <?php foreach ($oncekiHesaplamalar as $h): ?>
            <div class="hesaplama-item">
                <div>
                    <div class="hesaplama-tutar">₺<?= number_format($h['toplam_tazminat'], 2, ',', '.') ?></div>
                    <div class="hesaplama-tarih">
                        <?= date('d.m.Y H:i', strtotime($h['created_at'])) ?> | 
                        Maluliyet: %<?= $h['maluliyet_orani'] ?> | 
                        Kusur: %<?= $h['kusur_orani'] ?>
                    </div>
                </div>
                <a href="bh_rapor.php?id=<?= $h['id'] ?>" class="btn-rapor">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

