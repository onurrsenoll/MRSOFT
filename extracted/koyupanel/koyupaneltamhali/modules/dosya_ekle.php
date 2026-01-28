<?php
/**
 * MR HASAR DANIŞMANLIK - YENİ DOSYA EKLE
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';
startSecureSession();

$error = '';
$success = '';
$sigortalar = [];
$asamalar = [];
$avukatlar = [];
$sorumlular = [];
$yonlendirenler = [];
$markalar = [];
$personeller = [];
$servisler = [];
$eksperler = [];

try {
    $db = getDB();
    $sigortalar = $db->query("SELECT id, firma_adi as name FROM sigorta_sirketleri WHERE durum = 1 ORDER BY firma_adi")->fetchAll();
    $asamalar = $db->query("SELECT id, name FROM stage_definitions WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();
    $avukatlar = $db->query("SELECT id, full_name FROM users WHERE role = 'AVUKAT' AND is_active = 1")->fetchAll();
    $sorumlular = $db->query("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name")->fetchAll();
    $yonlendirenler = $db->query("SELECT id, ad_soyad FROM yonlendirenler WHERE durum = 1 ORDER BY ad_soyad")->fetchAll();
    $personeller = $db->query("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name")->fetchAll();

    // Servisler
    try {
        $servisler = $db->query("SELECT id, firma_adi FROM servisler WHERE durum = 1 ORDER BY firma_adi")->fetchAll();
    } catch (Exception $e) {
        $servisler = [];
    }

    // Eksperler
    try {
        $eksperler = $db->query("SELECT id, ad_soyad FROM eksperler WHERE durum = 1 ORDER BY ad_soyad")->fetchAll();
    } catch (Exception $e) {
        $eksperler = [];
    }

    // Araç markaları
    try {
        $markalar = $db->query("SELECT DISTINCT marka FROM arac_veritabani ORDER BY marka")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $markalar = [];
    }
} catch (Exception $e) {
    $error = 'DB HATASI: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    // Dosya Bilgileri
    $case_type = $_POST['case_type'] ?? '';
    $dosya_turu = $_POST['dosya_turu'] ?? 'TRAFIK';
    $current_stage_id = intval($_POST['current_stage_id'] ?? 0) ?: null;
    $assigned_lawyer_user_id = intval($_POST['assigned_lawyer_user_id'] ?? 0) ?: null;

    // Müşteri Bilgileri
    $musteri_tipi = $_POST['musteri_tipi'] ?? 'GERCEK';
    $tc_kimlik = ($musteri_tipi == 'GERCEK') ? preg_replace('/[^0-9]/', '', $_POST['tc_kimlik'] ?? '') : null;
    $vergi_no = ($musteri_tipi == 'TUZEL') ? trim($_POST['vergi_no'] ?? '') : null;
    $ad_soyad = ($musteri_tipi == 'GERCEK') ? trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8')) : null;
    $unvan = ($musteri_tipi == 'TUZEL') ? trim(mb_strtoupper($_POST['unvan'] ?? '', 'UTF-8')) : null;
    $telefon = trim($_POST['telefon'] ?? '');
    $adres = trim($_POST['adres'] ?? '');
    $iban = trim(mb_strtoupper(preg_replace('/\s+/', '', $_POST['iban'] ?? ''), 'UTF-8'));

    // Araç Bilgileri
    $plaka = trim(mb_strtoupper($_POST['plaka'] ?? '', 'UTF-8'));
    $arac_marka = trim(mb_strtoupper($_POST['arac_marka'] ?? '', 'UTF-8'));
    $arac_model = trim(mb_strtoupper($_POST['arac_model'] ?? '', 'UTF-8'));
    $model_yili = intval($_POST['model_yili'] ?? 0) ?: null;
    $sasi_no = trim(mb_strtoupper($_POST['sasi_no'] ?? '', 'UTF-8'));
    $gecmis_hasar = $_POST['gecmis_hasar'] ?? 'YOK';

    // Kaza Bilgileri
    $kaza_tarihi = $_POST['kaza_tarihi'] ?? '';
    $kaza_il = trim(mb_strtoupper($_POST['kaza_il'] ?? '', 'UTF-8'));
    $kaza_ilce = trim(mb_strtoupper($_POST['kaza_ilce'] ?? '', 'UTF-8'));
    $kusur_orani = intval($_POST['kusur_orani'] ?? 100);
    $kaza_aciklama = trim($_POST['kaza_aciklama'] ?? '');

    // Karşı Taraf Bilgileri
    $karsi_plaka = trim(mb_strtoupper($_POST['karsi_plaka'] ?? '', 'UTF-8'));
    $karsi_sigorta_id = intval($_POST['karsi_sigorta_id'] ?? 0) ?: null;
    $karsi_police_no = trim($_POST['karsi_police_no'] ?? '');
    $karsi_police_baslangic = $_POST['karsi_police_baslangic'] ?? null;
    $karsi_police_bitis = $_POST['karsi_police_bitis'] ?? null;
    $sigorta_hasar_no = trim($_POST['sigorta_hasar_no'] ?? '');
    $servis_id = intval($_POST['servis_id'] ?? 0) ?: null;
    $eksper_id = intval($_POST['eksper_id'] ?? 0) ?: null;

    // Kaynak Bilgileri
    $source_type = $_POST['source_type'] ?? '';
    $yonlendiren_id = intval($_POST['yonlendiren_id'] ?? 0) ?: null;
    $personel_id = intval($_POST['personel_id'] ?? 0) ?: null;
    $sorumlu_user_id = intval($_POST['sorumlu_user_id'] ?? $_SESSION['user_id']);

    $uid = $_SESSION['user_id'] ?? 1;

    // Validasyon
    if (empty($case_type)) {
        $error = 'DOSYA TİPİ SEÇİNİZ';
    } elseif ($musteri_tipi == 'GERCEK' && empty($tc_kimlik)) {
        $error = 'TC KİMLİK NO GİRİNİZ';
    } elseif ($musteri_tipi == 'GERCEK' && strlen($tc_kimlik) != 11) {
        $error = 'TC KİMLİK 11 HANE OLMALI';
    } elseif ($musteri_tipi == 'TUZEL' && empty($vergi_no)) {
        $error = 'VERGİ NO GİRİNİZ';
    } elseif ($musteri_tipi == 'GERCEK' && empty($ad_soyad)) {
        $error = 'AD SOYAD GİRİNİZ';
    } elseif ($musteri_tipi == 'TUZEL' && empty($unvan)) {
        $error = 'UNVAN GİRİNİZ';
    } elseif (empty($telefon)) {
        $error = 'TELEFON GİRİNİZ';
    } elseif (empty($kaza_tarihi)) {
        $error = 'KAZA TARİHİ GİRİNİZ';
    } elseif (empty($source_type)) {
        $error = 'KAYNAK TİPİ SEÇİNİZ';
    } else {
        try {
            $db->beginTransaction();
            $dosya_no = generateDosyaNo();

            $sql = "INSERT INTO cases (
                dosya_no, case_type, dosya_turu, current_stage_id, assigned_lawyer_user_id,
                musteri_tipi, tc_kimlik, vergi_no, ad_soyad, unvan, telefon, adres, iban,
                plaka, arac_marka, arac_model, model_yili, sasi_no, gecmis_hasar,
                kaza_tarihi, kaza_il, kaza_ilce, kusur_orani, kaza_aciklama,
                karsi_plaka, davali_sirket_id, karsi_police_no, karsi_police_baslangic, karsi_police_bitis, sigorta_hasar_no,
                servis_id, eksper_id,
                source_type, yonlendiren_id, personel_id, sorumlu_user_id, changed_by_user_id, acilis_tarihi
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?,
                ?, ?, ?, ?, ?, CURDATE()
            )";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $dosya_no, $case_type, $dosya_turu, $current_stage_id, $assigned_lawyer_user_id,
                $musteri_tipi, $tc_kimlik, $vergi_no, $ad_soyad, $unvan, $telefon, $adres ?: null, $iban ?: null,
                $plaka ?: null, $arac_marka ?: null, $arac_model ?: null, $model_yili, $sasi_no ?: null, $gecmis_hasar,
                $kaza_tarihi, $kaza_il ?: null, $kaza_ilce ?: null, $kusur_orani, $kaza_aciklama ?: null,
                $karsi_plaka ?: null, $karsi_sigorta_id, $karsi_police_no ?: null, $karsi_police_baslangic ?: null, $karsi_police_bitis ?: null, $sigorta_hasar_no ?: null,
                $servis_id, $eksper_id,
                $source_type, $yonlendiren_id, $personel_id, $sorumlu_user_id, $uid
            ]);

            $case_id = $db->lastInsertId();
            $db->commit();
            header("Location: dosya_detay.php?id={$case_id}&success=olusturuldu");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'KAYIT HATASI: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.form-section { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
.section-header { font-size: 14px; font-weight: 700; color: #3b82f6; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
.section-header i { font-size: 16px; }
.form-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 15px; }
.form-row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
.form-row-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px; }
@media (max-width: 1200px) { .form-row { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) { .form-row, .form-row-3, .form-row-2 { grid-template-columns: 1fr; } }
.frm-grp { display: flex; flex-direction: column; }
.frm-lbl { font-size: 11px; font-weight: 600; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; }
.frm-lbl .req { color: #ef4444; }
.frm-in, .frm-sel, .frm-ta { width: 100%; padding: 10px 12px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #f1f5f9; font-size: 13px; }
.frm-in:focus, .frm-sel:focus, .frm-ta:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
.frm-ta { resize: vertical; min-height: 80px; }

.toggle-group { display: flex; gap: 0; margin-bottom: 15px; }
.toggle-btn { flex: 1; padding: 12px 20px; background: #0f172a; border: 1px solid #334155; color: #94a3b8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-align: center; }
.toggle-btn:first-child { border-radius: 8px 0 0 8px; }
.toggle-btn:last-child { border-radius: 0 8px 8px 0; }
.toggle-btn.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }
.toggle-btn:hover:not(.active) { background: #1e293b; }

.radio-group { display: flex; gap: 15px; flex-wrap: wrap; }
.radio-item { display: flex; align-items: center; gap: 8px; padding: 8px 15px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; cursor: pointer; font-size: 12px; color: #cbd5e1; transition: all 0.2s; }
.radio-item:hover { border-color: #3b82f6; }
.radio-item input:checked + span { color: #3b82f6; font-weight: 600; }
.radio-item input { accent-color: #3b82f6; }

.form-actions { display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #334155; }
.btn { padding: 12px 25px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; border: none; text-decoration: none; }
.btn-suc { background: linear-gradient(135deg, #10b981, #06b6d4); color: #fff; }
.btn-sec { background: #334155; color: #f1f5f9; }
.btn:hover { opacity: 0.9; transform: translateY(-1px); }

.hidden { display: none !important; }
.info-tip { font-size: 10px; color: #64748b; margin-top: 3px; }
</style>

<div class="page-title"><i class="fas fa-plus-circle"></i> YENİ DOSYA EKLE</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<form method="POST">

<!-- DOSYA BİLGİLERİ -->
<div class="form-section">
    <div class="section-header"><i class="fas fa-folder"></i> DOSYA BİLGİLERİ</div>
    <div class="form-row">
        <div class="frm-grp">
            <label class="frm-lbl">DOSYA TİPİ <span class="req">*</span></label>
            <select name="case_type" id="case_type" class="frm-sel" required onchange="toggleSections()">
                <option value="">SEÇİNİZ</option>
                <option value="ADK">ADK (ARAÇ DEĞER KAYBI)</option>
                <option value="BEDENI">BEDENİ HASAR</option>
                <option value="MDK">MDK (MADDİ HASAR)</option>
                <option value="DIGER">DİĞER</option>
            </select>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">POLİÇE TÜRÜ <span class="req">*</span></label>
            <div class="radio-group" style="margin-top:5px">
                <label class="radio-item">
                    <input type="radio" name="dosya_turu" value="TRAFIK" checked>
                    <span>TRAFİK</span>
                </label>
                <label class="radio-item">
                    <input type="radio" name="dosya_turu" value="KASKO">
                    <span>KASKO</span>
                </label>
            </div>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">AŞAMA</label>
            <select name="current_stage_id" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($asamalar as $a): ?>
                <option value="<?= $a['id'] ?>"><?= e($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">AVUKAT</label>
            <select name="assigned_lawyer_user_id" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($avukatlar as $a): ?>
                <option value="<?= $a['id'] ?>"><?= e($a['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- MÜŞTERİ BİLGİLERİ -->
<div class="form-section">
    <div class="section-header"><i class="fas fa-user"></i> MÜŞTERİ / MAĞDUR BİLGİLERİ</div>

    <!-- Gerçek/Tüzel Seçimi -->
    <input type="hidden" name="musteri_tipi" id="musteri_tipi" value="GERCEK">
    <div class="toggle-group">
        <div class="toggle-btn active" data-type="GERCEK" onclick="setMusteriTipi('GERCEK')">
            <i class="fas fa-user"></i> GERÇEK KİŞİ
        </div>
        <div class="toggle-btn" data-type="TUZEL" onclick="setMusteriTipi('TUZEL')">
            <i class="fas fa-building"></i> TÜZEL KİŞİ
        </div>
    </div>

    <!-- Gerçek Kişi Alanları -->
    <div id="gercek-fields">
        <div class="form-row">
            <div class="frm-grp">
                <label class="frm-lbl">TC KİMLİK NO <span class="req">*</span></label>
                <input type="text" name="tc_kimlik" class="frm-in" maxlength="11" placeholder="11 haneli TC Kimlik No">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">AD SOYAD <span class="req">*</span></label>
                <input type="text" name="ad_soyad" class="frm-in" placeholder="Ad Soyad">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TELEFON <span class="req">*</span></label>
                <input type="tel" name="telefon" id="telefon_gercek" class="frm-in" placeholder="05XX XXX XX XX">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">IBAN</label>
                <input type="text" name="iban" id="iban_gercek" class="frm-in" maxlength="34" placeholder="TR00 0000 0000 0000 0000 0000 00">
            </div>
        </div>
        <div class="form-row-2">
            <div class="frm-grp">
                <label class="frm-lbl">ADRES</label>
                <textarea name="adres" id="adres_gercek" class="frm-ta" rows="2" placeholder="Açık adres..."></textarea>
            </div>
        </div>
    </div>

    <!-- Tüzel Kişi Alanları -->
    <div id="tuzel-fields" class="hidden">
        <div class="form-row">
            <div class="frm-grp">
                <label class="frm-lbl">VERGİ NO <span class="req">*</span></label>
                <input type="text" name="vergi_no" class="frm-in" maxlength="11" placeholder="Vergi Numarası">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">FİRMA UNVANI <span class="req">*</span></label>
                <input type="text" name="unvan" class="frm-in" placeholder="Firma Unvanı">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TELEFON <span class="req">*</span></label>
                <input type="tel" id="telefon_tuzel" class="frm-in" placeholder="05XX XXX XX XX">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">IBAN</label>
                <input type="text" id="iban_tuzel" class="frm-in" maxlength="34" placeholder="TR00 0000 0000 0000 0000 0000 00">
            </div>
        </div>
        <div class="form-row-2">
            <div class="frm-grp">
                <label class="frm-lbl">ADRES</label>
                <textarea id="adres_tuzel" class="frm-ta" rows="2" placeholder="Firma adresi..."></textarea>
            </div>
        </div>
    </div>
</div>

<!-- ARAÇ BİLGİLERİ -->
<div class="form-section" id="arac-section">
    <div class="section-header"><i class="fas fa-car"></i> ARAÇ BİLGİLERİ</div>
    <div class="form-row">
        <div class="frm-grp">
            <label class="frm-lbl">PLAKA <span class="req">*</span></label>
            <input type="text" name="plaka" class="frm-in" placeholder="34 ABC 123">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">MARKA</label>
            <select name="arac_marka" id="arac_marka" class="frm-sel" onchange="getModeller()">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($markalar as $m): ?>
                <option value="<?= e($m) ?>"><?= e($m) ?></option>
                <?php endforeach; ?>
                <option value="DIGER">DİĞER</option>
            </select>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">MODEL</label>
            <select name="arac_model" id="arac_model" class="frm-sel">
                <option value="">ÖNCE MARKA SEÇİNİZ</option>
            </select>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">MODEL YILI</label>
            <select name="model_yili" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php for ($y = date('Y') + 1; $y >= 1990; $y--): ?>
                <option value="<?= $y ?>"><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="frm-grp">
            <label class="frm-lbl">ŞASİ NO</label>
            <input type="text" name="sasi_no" class="frm-in" maxlength="17" placeholder="17 haneli şasi numarası">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">GEÇMİŞ HASAR KAYDI</label>
            <div class="radio-group" style="margin-top:5px">
                <label class="radio-item">
                    <input type="radio" name="gecmis_hasar" value="YOK" checked>
                    <span>YOK</span>
                </label>
                <label class="radio-item">
                    <input type="radio" name="gecmis_hasar" value="VAR">
                    <span>VAR</span>
                </label>
            </div>
            <span class="info-tip">Tramer/SBM sorgusu sonucu</span>
        </div>
    </div>
</div>

<!-- KAZA BİLGİLERİ -->
<div class="form-section">
    <div class="section-header"><i class="fas fa-car-crash"></i> KAZA BİLGİLERİ</div>
    <div class="form-row">
        <div class="frm-grp">
            <label class="frm-lbl">KAZA TARİHİ <span class="req">*</span></label>
            <input type="date" name="kaza_tarihi" class="frm-in" required>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">KAZA YERİ (İL)</label>
            <input type="text" name="kaza_il" class="frm-in" placeholder="İl">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">KAZA YERİ (İLÇE)</label>
            <input type="text" name="kaza_ilce" class="frm-in" placeholder="İlçe">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">KARŞI TARAF KUSUR %</label>
            <select name="kusur_orani" class="frm-sel">
                <option value="100">%100</option>
                <option value="75">%75</option>
                <option value="50">%50</option>
                <option value="25">%25</option>
                <option value="0">%0</option>
            </select>
        </div>
    </div>
    <div class="form-row-2">
        <div class="frm-grp">
            <label class="frm-lbl">KAZA AÇIKLAMASI</label>
            <textarea name="kaza_aciklama" class="frm-ta" rows="2" placeholder="Kaza hakkında kısa açıklama..."></textarea>
        </div>
    </div>
</div>

<!-- KARŞI TARAF BİLGİLERİ -->
<div class="form-section">
    <div class="section-header"><i class="fas fa-car-side"></i> KARŞI TARAF / SİGORTA BİLGİLERİ</div>
    <div class="form-row">
        <div class="frm-grp">
            <label class="frm-lbl">KARŞI ARAÇ PLAKASI</label>
            <input type="text" name="karsi_plaka" class="frm-in" placeholder="34 XYZ 789">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">KARŞI TARAF SİGORTA ŞİRKETİ</label>
            <select name="karsi_sigorta_id" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($sigortalar as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">POLİÇE NUMARASI</label>
            <input type="text" name="karsi_police_no" class="frm-in" placeholder="Poliçe no">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">SİGORTA HASAR NO</label>
            <input type="text" name="sigorta_hasar_no" class="frm-in" placeholder="Sigorta dosya no">
        </div>
    </div>
    <div class="form-row">
        <div class="frm-grp">
            <label class="frm-lbl">POLİÇE BAŞLANGIÇ TARİHİ</label>
            <input type="date" name="karsi_police_baslangic" class="frm-in">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">POLİÇE BİTİŞ TARİHİ</label>
            <input type="date" name="karsi_police_bitis" class="frm-in">
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">SERVİS</label>
            <select name="servis_id" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($servisler as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['firma_adi']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="frm-grp">
            <label class="frm-lbl">EKSPER</label>
            <select name="eksper_id" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($eksperler as $ex): ?>
                <option value="<?= $ex['id'] ?>"><?= e($ex['ad_soyad']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- KAYNAK BİLGİLERİ -->
<div class="form-section">
    <div class="section-header"><i class="fas fa-directions"></i> KAYNAK / SORUMLU BİLGİLERİ</div>
    <div class="form-row">
        <div class="frm-grp">
            <label class="frm-lbl">KAYNAK TİPİ <span class="req">*</span></label>
            <select name="source_type" id="source_type" class="frm-sel" required onchange="toggleKaynakSecim()">
                <option value="">SEÇİNİZ</option>
                <option value="YONLENDIREN">YÖNLENDİREN</option>
                <option value="OFIS">OFİS</option>
                <option value="SAHA">SAHA</option>
                <option value="SERVIS">SERVİS</option>
                <option value="MR">MR</option>
                <option value="REFERANS">REFERANS</option>
                <option value="INTERNET">İNTERNET</option>
            </select>
        </div>

        <!-- Yönlendiren Seçimi -->
        <div class="frm-grp hidden" id="yonlendiren-secim">
            <label class="frm-lbl">YÖNLENDİREN SEÇİNİZ <span class="req">*</span></label>
            <select name="yonlendiren_id" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($yonlendirenler as $y): ?>
                <option value="<?= $y['id'] ?>"><?= e($y['ad_soyad']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Personel Seçimi -->
        <div class="frm-grp hidden" id="personel-secim">
            <label class="frm-lbl">PERSONEL SEÇİNİZ <span class="req">*</span></label>
            <select name="personel_id" class="frm-sel">
                <option value="">SEÇİNİZ</option>
                <?php foreach ($personeller as $p): ?>
                <option value="<?= $p['id'] ?>"><?= e($p['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="frm-grp">
            <label class="frm-lbl">DOSYA SORUMLUSU</label>
            <select name="sorumlu_user_id" class="frm-sel">
                <?php foreach ($sorumlular as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($s['id'] == ($_SESSION['user_id'] ?? 0)) ? 'selected' : '' ?>><?= e($s['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- FORM BUTONLARI -->
<div class="form-actions">
    <a href="dosyalar.php" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</a>
    <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> DOSYA OLUŞTUR</button>
</div>

</form>

<script>
// Müşteri Tipi Toggle
function setMusteriTipi(tip) {
    document.getElementById('musteri_tipi').value = tip;

    // Toggle butonları
    document.querySelectorAll('.toggle-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.type === tip) {
            btn.classList.add('active');
        }
    });

    // Alanları göster/gizle
    if (tip === 'GERCEK') {
        document.getElementById('gercek-fields').classList.remove('hidden');
        document.getElementById('tuzel-fields').classList.add('hidden');
    } else {
        document.getElementById('gercek-fields').classList.add('hidden');
        document.getElementById('tuzel-fields').classList.remove('hidden');
    }
}

// Kaynak tipine göre seçim alanlarını göster
function toggleKaynakSecim() {
    var sourceType = document.getElementById('source_type').value;
    var yonlendirenDiv = document.getElementById('yonlendiren-secim');
    var personelDiv = document.getElementById('personel-secim');

    // Önce hepsini gizle
    yonlendirenDiv.classList.add('hidden');
    personelDiv.classList.add('hidden');

    // Kaynak tipine göre göster
    if (sourceType === 'YONLENDIREN') {
        yonlendirenDiv.classList.remove('hidden');
    } else if (sourceType === 'OFIS' || sourceType === 'SAHA') {
        personelDiv.classList.remove('hidden');
    }
}

// Araç bölümü görünürlüğü
function toggleSections() {
    var t = document.getElementById('case_type').value;
    var aracSection = document.getElementById('arac-section');
    if (t === 'BEDENI') {
        aracSection.style.opacity = '0.5';
    } else {
        aracSection.style.opacity = '1';
    }
}

// Araç modelleri
function getModeller() {
    var marka = document.getElementById('arac_marka').value;
    var modelSelect = document.getElementById('arac_model');

    if (!marka || marka == 'DIGER') {
        modelSelect.innerHTML = '<option value="">MODEL GİRİNİZ</option>';
        return;
    }

    modelSelect.innerHTML = '<option value="">YÜKLENİYOR...</option>';

    fetch('ajax/get_modeller.php?marka=' + encodeURIComponent(marka))
        .then(response => response.json())
        .then(data => {
            modelSelect.innerHTML = '<option value="">SEÇİNİZ</option>';
            data.forEach(function(model) {
                modelSelect.innerHTML += '<option value="' + model + '">' + model + '</option>';
            });
        })
        .catch(error => {
            modelSelect.innerHTML = '<option value="">HATA OLUŞTU</option>';
        });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
