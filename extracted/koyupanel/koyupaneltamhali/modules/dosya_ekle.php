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

try {
    $db = getDB();
    $sigortalar = $db->query("SELECT id, firma_adi as name FROM sigorta_sirketleri WHERE durum = 1 ORDER BY firma_adi")->fetchAll();
    $asamalar = $db->query("SELECT id, asama_adi as name FROM asama_durumlari WHERE durum = 1 ORDER BY sira")->fetchAll();
    $avukatlar = $db->query("SELECT id, full_name FROM users WHERE role = 'AVUKAT' AND is_active = 1")->fetchAll();
    $sorumlular = $db->query("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name")->fetchAll();
    $yonlendirenler = $db->query("SELECT id, ad_soyad, adk_ucret, bh_ucret, mdk_ucret, diger_ucret FROM yonlendirenler WHERE durum = 1 ORDER BY ad_soyad")->fetchAll();
} catch (Exception $e) {
    $error = 'DB HATASI: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $case_type = $_POST['case_type'] ?? '';
    $tc_kimlik = preg_replace('/[^0-9]/', '', $_POST['tc_kimlik'] ?? '');
    $ad_soyad = trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8'));
    $telefon = trim($_POST['telefon'] ?? '');
    $kaza_tarihi = $_POST['kaza_tarihi'] ?? '';
    $plaka = trim(mb_strtoupper($_POST['plaka'] ?? '', 'UTF-8'));
    $karsi_plaka = trim(mb_strtoupper($_POST['karsi_plaka'] ?? '', 'UTF-8'));
    $davali_sirket_id = intval($_POST['davali_sirket_id'] ?? 0);
    $sigorta_hasar_no = trim($_POST['sigorta_hasar_no'] ?? '');
    $current_stage_id = intval($_POST['current_stage_id'] ?? 0) ?: null;
    $source_type = $_POST['source_type'] ?? '';
    $source_name = trim(mb_strtoupper($_POST['source_name'] ?? '', 'UTF-8'));
    $yonlendiren_id = intval($_POST['yonlendiren_id'] ?? 0) ?: null;
    $sorumlu_user_id = intval($_POST['sorumlu_user_id'] ?? $_SESSION['user_id']);
    $assigned_lawyer_user_id = intval($_POST['assigned_lawyer_user_id'] ?? 0) ?: null;
    $uid = $_SESSION['user_id'] ?? 1;

    if (empty($case_type)) {
        $error = 'DOSYA TURU SECINIZ';
    } elseif (strlen($tc_kimlik) != 11) {
        $error = 'TC KIMLIK 11 HANE OLMALI';
    } elseif (empty($ad_soyad)) {
        $error = 'AD SOYAD GIRINIZ';
    } elseif (empty($telefon)) {
        $error = 'TELEFON GIRINIZ';
    } elseif (empty($kaza_tarihi)) {
        $error = 'KAZA TARIHI GIRINIZ';
    } elseif ($davali_sirket_id == 0) {
        $error = 'SIGORTA SIRKETI SECINIZ';
    } elseif (empty($source_type)) {
        $error = 'KAYNAK TIPI SECINIZ';
    } else {
        try {
            $db->beginTransaction();
            $dosya_no = generateDosyaNo();
            
            $sql = "INSERT INTO cases (dosya_no, case_type, tc_kimlik, ad_soyad, telefon, kaza_tarihi, plaka, karsi_plaka, davali_sirket_id, sigorta_hasar_no, current_stage_id, acilis_tarihi, source_type, source_name, yonlendiren_id, sorumlu_user_id, assigned_lawyer_user_id, changed_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$dosya_no, $case_type, $tc_kimlik, $ad_soyad, $telefon, $kaza_tarihi, $plaka ?: null, $karsi_plaka ?: null, $davali_sirket_id, $sigorta_hasar_no ?: null, $current_stage_id, $source_type, $source_name, $yonlendiren_id, $sorumlu_user_id, $assigned_lawyer_user_id, $uid]);
            
            $db->commit();
            $success = "DOSYA OLUSTURULDU: $dosya_no";
            $_POST = [];
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'KAYIT HATASI: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-plus-circle"></i> YENİ DOSYA EKLE</div>

<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<form method="POST" class="form-wrap">
<div class="form-sec">
<div class="sec-title"><i class="fas fa-folder"></i> DOSYA BILGILERI</div>
<div class="form-grid">
    <div class="frm-grp">
        <label class="frm-lbl">DOSYA TURU *</label>
        <select name="case_type" id="case_type" class="frm-sel" required onchange="toggleSections()">
            <option value="">SECINIZ</option>
            <option value="ADK">ADK</option>
            <option value="BEDENI">BEDENI</option>
            <option value="MDK">MDK</option>
            <option value="DIGER">DIGER</option>
        </select>
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">SIGORTA SIRKETI *</label>
        <select name="davali_sirket_id" class="frm-sel" required>
            <option value="">SECINIZ</option>
            <?php foreach ($sigortalar as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">KAZA TARIHI *</label>
        <input type="date" name="kaza_tarihi" class="frm-in" required>
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">SIGORTA HASAR NO</label>
        <input type="text" name="sigorta_hasar_no" class="frm-in">
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">ASAMA</label>
        <select name="current_stage_id" class="frm-sel">
            <option value="">SECINIZ</option>
            <?php foreach ($asamalar as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">AVUKAT</label>
        <select name="assigned_lawyer_user_id" class="frm-sel">
            <option value="">SECINIZ</option>
            <?php foreach ($avukatlar as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['full_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
</div>

<div class="form-sec">
<div class="sec-title"><i class="fas fa-user"></i> MAGDUR BILGILERI</div>
<div class="form-grid">
    <div class="frm-grp">
        <label class="frm-lbl">TC KIMLIK *</label>
        <input type="text" name="tc_kimlik" class="frm-in" maxlength="11" required>
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">AD SOYAD *</label>
        <input type="text" name="ad_soyad" class="frm-in" required>
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">TELEFON *</label>
        <input type="tel" name="telefon" class="frm-in" required>
    </div>
</div>
</div>

<div class="form-sec" id="adk-section" style="display:none">
<div class="sec-title"><i class="fas fa-car"></i> ARAC BILGILERI</div>
<div class="form-grid">
    <div class="frm-grp">
        <label class="frm-lbl">PLAKA</label>
        <input type="text" name="plaka" class="frm-in">
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">KARSI PLAKA</label>
        <input type="text" name="karsi_plaka" class="frm-in">
    </div>
</div>
</div>

<div class="form-sec">
<div class="sec-title"><i class="fas fa-directions"></i> KAYNAK</div>
<div class="form-grid">
    <div class="frm-grp">
        <label class="frm-lbl">KAYNAK TIPI *</label>
        <select name="source_type" class="frm-sel" required>
            <option value="">SECINIZ</option>
            <option value="SERVIS">SERVIS</option>
            <option value="YONLENDIREN">YONLENDIREN</option>
            <option value="OFIS_CRM">OFIS CRM</option>
            <option value="SAHA">SAHA</option>
            <option value="MR">MR</option>
        </select>
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">KAYNAK ADI</label>
        <input type="text" name="source_name" class="frm-in">
    </div>
    <div class="frm-grp">
        <label class="frm-lbl">SORUMLU</label>
        <select name="sorumlu_user_id" class="frm-sel">
            <?php foreach ($sorumlular as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($s['id'] == ($_SESSION['user_id'] ?? 0)) ? 'selected' : '' ?>><?= htmlspecialchars($s['full_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
</div>

<div class="form-act">
    <a href="dosyalar.php" class="btn btn-sec"><i class="fas fa-times"></i> IPTAL</a>
    <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
</div>
</form>

<script>
function toggleSections() {
    var t = document.getElementById('case_type').value;
    document.getElementById('adk-section').style.display = (t === 'ADK' || t === 'MDK') ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>