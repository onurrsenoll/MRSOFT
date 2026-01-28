<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$error = '';
$success = '';
$dosya = null;
$markalar = [];
$modeller = [];

$case_id = intval($_GET['case_id'] ?? 0);

if (!$case_id) {
    header('Location: dosyalar.php');
    exit;
}

try {
    $db = getDB();
    
    // Dosya bilgilerini getir
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ? AND case_type = 'ADK'");
    $stmt->execute([$case_id]);
    $dosya = $stmt->fetch();
    
    if (!$dosya) {
        header('Location: dosyalar.php');
        exit;
    }
    
    // Araç markaları
    $markalar = $db->query("SELECT DISTINCT marka FROM arac_veritabani ORDER BY marka")->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    $error = 'DB HATASI: ' . $e->getMessage();
}

// HESAPLAMA FORMU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $marka = $_POST['marka'] ?? '';
    $model = $_POST['model'] ?? '';
    $model_yili = intval($_POST['model_yili'] ?? 0);
    $kilometre = intval($_POST['kilometre'] ?? 0);
    $kaza_tarihi = $_POST['kaza_tarihi'] ?? $dosya['kaza_tarihi'];
    $hasar_tutari = floatval($_POST['hasar_tutari'] ?? 0);
    $arac_degeri = floatval($_POST['arac_degeri'] ?? 0);
    $tramer_kaydi = $_POST['tramer_kaydi'] ?? 'YOK';
    $parca_degisim = $_POST['parca_degisim'] ?? [];
    $boya_islem = $_POST['boya_islem'] ?? [];
    $ozel_faktor = floatval($_POST['ozel_faktor'] ?? 0);
    
    if (empty($marka) || empty($model) || $model_yili == 0) {
        $error = 'MARKA, MODEL VE YIL ZORUNLUDUR';
    } elseif ($arac_degeri <= 0) {
        $error = 'ARAC DEGERI GIRINIZ';
    } else {
        try {
            // HESAPLAMA MOTORU
            $arac_yasi = date('Y') - $model_yili;
            
            // Temel değer kaybı oranı (araç yaşına göre)
            if ($arac_yasi <= 1) {
                $temel_oran = 0.12;
            } elseif ($arac_yasi <= 3) {
                $temel_oran = 0.10;
            } elseif ($arac_yasi <= 5) {
                $temel_oran = 0.08;
            } elseif ($arac_yasi <= 7) {
                $temel_oran = 0.06;
            } elseif ($arac_yasi <= 10) {
                $temel_oran = 0.04;
            } else {
                $temel_oran = 0.02;
            }
            
            // Kilometre faktörü
            if ($kilometre <= 50000) {
                $km_faktor = 1.2;
            } elseif ($kilometre <= 100000) {
                $km_faktor = 1.0;
            } elseif ($kilometre <= 150000) {
                $km_faktor = 0.8;
            } else {
                $km_faktor = 0.6;
            }
            
            // Hasar oranı faktörü
            $hasar_orani = ($arac_degeri > 0) ? ($hasar_tutari / $arac_degeri) : 0;
            if ($hasar_orani <= 0.10) {
                $hasar_faktor = 0.8;
            } elseif ($hasar_orani <= 0.25) {
                $hasar_faktor = 1.0;
            } elseif ($hasar_orani <= 0.40) {
                $hasar_faktor = 1.2;
            } else {
                $hasar_faktor = 1.4;
            }
            
            // Tramer kaydı faktörü
            $tramer_faktor = ($tramer_kaydi == 'VAR') ? 1.15 : 1.0;
            
            // Parça değişim faktörü
            $parca_faktor = 1.0;
            if (!empty($parca_degisim)) {
                $parca_faktor += (count($parca_degisim) * 0.03);
            }
            
            // Boya işlem faktörü
            $boya_faktor = 1.0;
            if (!empty($boya_islem)) {
                $boya_faktor += (count($boya_islem) * 0.02);
            }
            
            // Özel faktör (kullanıcı girişi)
            $ozel_carpan = 1 + ($ozel_faktor / 100);
            
            // TOPLAM DEĞER KAYBI HESAPLA
            $deger_kaybi = $arac_degeri * $temel_oran * $km_faktor * $hasar_faktor * $tramer_faktor * $parca_faktor * $boya_faktor * $ozel_carpan;
            
            // Üst limit kontrolü (araç değerinin %25'ini geçemez)
            $ust_limit = $arac_degeri * 0.25;
            if ($deger_kaybi > $ust_limit) {
                $deger_kaybi = $ust_limit;
            }
            
            // Alt limit kontrolü
            if ($deger_kaybi < 0) {
                $deger_kaybi = 0;
            }
            
            // Veritabanına kaydet
            $stmt = $db->prepare("
                INSERT INTO adk_calculations (
                    case_id, marka, model, model_yili, kilometre, kaza_tarihi,
                    hasar_tutari, arac_degeri, tramer_kaydi, parca_degisim, boya_islem,
                    temel_oran, km_faktor, hasar_faktor, tramer_faktor, parca_faktor, boya_faktor,
                    ozel_faktor, deger_kaybi, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $case_id, $marka, $model, $model_yili, $kilometre, $kaza_tarihi,
                $hasar_tutari, $arac_degeri, $tramer_kaydi,
                json_encode($parca_degisim), json_encode($boya_islem),
                $temel_oran, $km_faktor, $hasar_faktor, $tramer_faktor, $parca_faktor, $boya_faktor,
                $ozel_faktor, $deger_kaybi, $_SESSION['user_id']
            ]);
            
            $hesap_id = $db->lastInsertId();
            
            $success = "HESAPLAMA TAMAMLANDI! DEGER KAYBI: ₺" . number_format($deger_kaybi, 2, ',', '.');
            
        } catch (Exception $e) {
            $error = 'HESAPLAMA HATASI: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.calc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 900px) { .calc-grid { grid-template-columns: 1fr; } }

.calc-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; }
.calc-title { font-size: 14px; font-weight: 700; color: #3b82f6; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

.frm-grp { margin-bottom: 15px; }
.frm-lbl { display: block; font-size: 11px; font-weight: 600; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; }
.frm-in, .frm-sel { width: 100%; padding: 12px 15px; background: #0f172a; border: 2px solid #334155; border-radius: 8px; color: #f1f5f9; font-size: 14px; }
.frm-in:focus, .frm-sel:focus { outline: none; border-color: #3b82f6; }

.checkbox-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (max-width: 600px) { .checkbox-group { grid-template-columns: repeat(2, 1fr); } }
.checkbox-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #0f172a; border-radius: 6px; cursor: pointer; font-size: 12px; color: #cbd5e1; }
.checkbox-item:hover { background: #1e293b; }
.checkbox-item input { accent-color: #3b82f6; }

.result-box { background: linear-gradient(135deg, #10b981, #06b6d4); border-radius: 12px; padding: 25px; text-align: center; margin-top: 20px; }
.result-label { font-size: 14px; color: rgba(255,255,255,0.8); margin-bottom: 5px; }
.result-value { font-size: 36px; font-weight: 800; color: #fff; }

.btn-hesapla { background: linear-gradient(135deg, #10b981, #06b6d4); color: #fff; border: none; padding: 15px 30px; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 20px; }
.btn-hesapla:hover { opacity: 0.9; }

.info-box { background: #0f172a; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 20px; }
.info-box-title { font-weight: 700; color: #3b82f6; margin-bottom: 5px; }
.info-box-text { font-size: 13px; color: #94a3b8; }
</style>

<div class="page-title"><i class="fas fa-calculator"></i> ADK HESAPLAMA</div>

<!-- DOSYA BİLGİSİ -->
<div class="info-box">
    <div class="info-box-title"><?= htmlspecialchars($dosya['dosya_no']) ?> - <?= htmlspecialchars($dosya['ad_soyad']) ?></div>
    <div class="info-box-text">Plaka: <?= htmlspecialchars($dosya['plaka'] ?? '-') ?> | Kaza Tarihi: <?= date('d.m.Y', strtotime($dosya['kaza_tarihi'])) ?></div>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<div class="result-box">
    <div class="result-label">HESAPLANAN DEGER KAYBI</div>
    <div class="result-value">₺<?= number_format($deger_kaybi ?? 0, 2, ',', '.') ?></div>
</div>
<div style="margin-top:20px;display:flex;gap:15px;justify-content:center">
    <a href="dosya_detay.php?id=<?= $case_id ?>" class="btn btn-pri"><i class="fas fa-arrow-left"></i> DOSYAYA DON</a>
    <a href="adk_rapor.php?id=<?= $hesap_id ?? 0 ?>" class="btn btn-suc"><i class="fas fa-file-pdf"></i> RAPOR OLUSTUR</a>
</div>
<?php else: ?>

<form method="POST">
<div class="calc-grid">
    <!-- SOL KOLON - ARAC BİLGİLERİ -->
    <div class="calc-card">
        <div class="calc-title"><i class="fas fa-car"></i> ARAC BILGILERI</div>
        
        <div class="form-row">
            <div class="frm-grp">
                <label class="frm-lbl">MARKA *</label>
                <select name="marka" id="marka" class="frm-sel" required onchange="getModeller()">
                    <option value="">SECINIZ</option>
                    <?php foreach ($markalar as $m): ?>
                    <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">MODEL *</label>
                <select name="model" id="model" class="frm-sel" required>
                    <option value="">ONCE MARKA SECINIZ</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="frm-grp">
                <label class="frm-lbl">MODEL YILI *</label>
                <select name="model_yili" class="frm-sel" required>
                    <option value="">SECINIZ</option>
                    <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">KILOMETRE</label>
                <input type="number" name="kilometre" class="frm-in" placeholder="0">
            </div>
        </div>
        
        <div class="form-row">
            <div class="frm-grp">
                <label class="frm-lbl">ARAC DEGERI (TL) *</label>
                <input type="number" name="arac_degeri" class="frm-in" placeholder="0" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">HASAR TUTARI (TL)</label>
                <input type="number" name="hasar_tutari" class="frm-in" placeholder="0">
            </div>
        </div>
        
        <div class="frm-grp">
            <label class="frm-lbl">TRAMER KAYDI</label>
            <select name="tramer_kaydi" class="frm-sel">
                <option value="YOK">TRAMER KAYDI YOK</option>
                <option value="VAR">TRAMER KAYDI VAR</option>
            </select>
        </div>
    </div>
    
    <!-- SAG KOLON - HASAR DETAYLARI -->
    <div class="calc-card">
        <div class="calc-title"><i class="fas fa-tools"></i> HASAR DETAYLARI</div>
        
        <div class="frm-grp">
            <label class="frm-lbl">DEGISEN PARCALAR</label>
            <div class="checkbox-group">
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="on_tampon"> On Tampon</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="arka_tampon"> Arka Tampon</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="kaput"> Kaput</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="bagaj"> Bagaj</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="on_camurluk"> On Camurluk</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="arka_camurluk"> Arka Camurluk</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="kapi"> Kapi</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="far"> Far</label>
                <label class="checkbox-item"><input type="checkbox" name="parca_degisim[]" value="stop"> Stop</label>
            </div>
        </div>
        
        <div class="frm-grp">
            <label class="frm-lbl">BOYA ISLEMLERI</label>
            <div class="checkbox-group">
                <label class="checkbox-item"><input type="checkbox" name="boya_islem[]" value="lokal_boya"> Lokal Boya</label>
                <label class="checkbox-item"><input type="checkbox" name="boya_islem[]" value="parca_boya"> Parca Boya</label>
                <label class="checkbox-item"><input type="checkbox" name="boya_islem[]" value="komple_boya"> Komple Boya</label>
            </div>
        </div>
        
        <div class="frm-grp">
            <label class="frm-lbl">OZEL FAKTOR (%)</label>
            <input type="number" name="ozel_faktor" class="frm-in" placeholder="0" min="-50" max="50" value="0">
            <small style="color:#64748b;font-size:11px">Premium marka, ozel versiyon vb. icin +/- duzeltme</small>
        </div>
    </div>
</div>

<button type="submit" class="btn-hesapla"><i class="fas fa-calculator"></i> HESAPLA</button>
</form>

<?php endif; ?>

<script>
function getModeller() {
    var marka = document.getElementById('marka').value;
    var modelSelect = document.getElementById('model');
    
    if (!marka) {
        modelSelect.innerHTML = '<option value="">ONCE MARKA SECINIZ</option>';
        return;
    }
    
    modelSelect.innerHTML = '<option value="">YUKLENIYOR...</option>';
    
    fetch('ajax/get_modeller.php?marka=' + encodeURIComponent(marka))
        .then(response => response.json())
        .then(data => {
            modelSelect.innerHTML = '<option value="">SECINIZ</option>';
            data.forEach(function(model) {
                modelSelect.innerHTML += '<option value="' + model + '">' + model + '</option>';
            });
        })
        .catch(error => {
            modelSelect.innerHTML = '<option value="">HATA OLUSTU</option>';
        });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>