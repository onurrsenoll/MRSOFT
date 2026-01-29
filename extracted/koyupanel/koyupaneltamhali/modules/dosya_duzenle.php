<?php
/**
 * MR HASAR DANIŞMANLIK - DOSYA DÜZENLEME
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: dosyalar.php');
    exit;
}

$error = '';
$success = '';

try {
    $db = getDB();
    
    // Dosya bilgilerini getir
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$id]);
    $dosya = $stmt->fetch();
    
    if (!$dosya) {
        header('Location: dosyalar.php');
        exit;
    }
    
    // Detay bilgileri
    if ($dosya['case_type'] == 'ADK') {
        $stmt = $db->prepare("SELECT * FROM adk_detail WHERE case_id = ?");
        $stmt->execute([$id]);
        $detay = $stmt->fetch();
    } else {
        $stmt = $db->prepare("SELECT * FROM bedeni_detail WHERE case_id = ?");
        $stmt->execute([$id]);
        $detay = $stmt->fetch();
    }
    
    // Select verileri
    $sigortalar = $db->query("SELECT id, name FROM insurers WHERE is_active = 1 ORDER BY name")->fetchAll();
    $asamalar = $db->query("SELECT id, name FROM stage_definitions WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
    $avukatlar = $db->query("SELECT id, full_name FROM users WHERE role = 'AVUKAT' AND is_active = 1")->fetchAll();
    $sorumlular = $db->query("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name")->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Form gönderildi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dosya) {
    $ad_soyad = trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8'));
    $telefon = trim($_POST['telefon'] ?? '');
    $davali_sirket_id = intval($_POST['davali_sirket_id'] ?? 0);
    $sigorta_hasar_no = trim($_POST['sigorta_hasar_no'] ?? '');
    $current_stage_id = intval($_POST['current_stage_id'] ?? 0) ?: null;
    $assigned_lawyer_user_id = intval($_POST['assigned_lawyer_user_id'] ?? 0) ?: null;
    $sorumlu_user_id = intval($_POST['sorumlu_user_id'] ?? 0);
    $status = $_POST['status'] ?? 'AKTIF';
    
    if (empty($ad_soyad)) {
        $error = 'ADI SOYADI GİRİNİZ';
    } else {
        try {
            $db->beginTransaction();
            
            // Aşama değişti mi kontrol et
            $asamaDegisti = ($current_stage_id != $dosya['current_stage_id']);
            
            // Dosyayı güncelle
            $stmt = $db->prepare("
                UPDATE cases SET 
                    ad_soyad = ?, telefon = ?, davali_sirket_id = ?, sigorta_hasar_no = ?,
                    current_stage_id = ?, assigned_lawyer_user_id = ?, sorumlu_user_id = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $ad_soyad, $telefon, $davali_sirket_id, $sigorta_hasar_no ?: null,
                $current_stage_id, $assigned_lawyer_user_id, $sorumlu_user_id, $status, $id
            ]);
            
            // Aşama değiştiyse geçmişe kaydet
            if ($asamaDegisti && $current_stage_id) {
                $stmt = $db->prepare("INSERT INTO case_stage_history (case_id, stage_id, changed_by_user_id) VALUES (?, ?, ?)");
                $stmt->execute([$id, $current_stage_id, $_SESSION['user_id']]);
            }
            
            // ADK detay güncelle
            if ($dosya['case_type'] == 'ADK') {
                $plaka = trim(mb_strtoupper($_POST['plaka'] ?? '', 'UTF-8'));
                $karsi_plaka = trim(mb_strtoupper($_POST['karsi_plaka'] ?? '', 'UTF-8'));
                
                $stmt = $db->prepare("UPDATE adk_detail SET plaka = ?, karsi_plaka = ? WHERE case_id = ?");
                $stmt->execute([$plaka, $karsi_plaka, $id]);
            }
            
            // Bedeni detay güncelle
            if ($dosya['case_type'] == 'BEDENI') {
                $dogum_tarihi = $_POST['dogum_tarihi'] ?? null;
                $meslek = trim($_POST['meslek'] ?? '');
                $gelir = floatval($_POST['gelir'] ?? 0);
                $adres = trim($_POST['adres'] ?? '');
                
                $stmt = $db->prepare("UPDATE bedeni_detail SET dogum_tarihi = ?, meslek = ?, gelir = ?, adres = ? WHERE case_id = ?");
                $stmt->execute([$dogum_tarihi ?: null, $meslek, $gelir, $adres, $id]);
            }
            
            auditLog('cases', $id, 'UPDATE');
            $db->commit();
            
            $success = 'DOSYA BAŞARIYLA GÜNCELLENDİ';
            
            // Yeni verileri çek
            $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
            $stmt->execute([$id]);
            $dosya = $stmt->fetch();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <i class="fas fa-edit"></i> DOSYA DÜZENLE: <?= e($dosya['dosya_no']) ?>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
<?php endif; ?>

<form method="POST" class="form-wrap">
    <!-- DOSYA BİLGİLERİ -->
    <div class="form-sec">
        <div class="sec-title"><i class="fas fa-folder"></i> DOSYA BİLGİLERİ</div>
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">DOSYA NO</label>
                <input type="text" class="frm-in" value="<?= e($dosya['dosya_no']) ?>" disabled>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">DOSYA TÜRÜ</label>
                <input type="text" class="frm-in" value="<?= e($dosya['case_type']) ?>" disabled>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">SİGORTA ŞİRKETİ</label>
                <select name="davali_sirket_id" class="frm-sel">
                    <?php foreach ($sigortalar as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $dosya['davali_sirket_id'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">HASAR NO</label>
                <input type="text" name="sigorta_hasar_no" class="frm-in" value="<?= e($dosya['sigorta_hasar_no']) ?>">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">AŞAMA</label>
                <select name="current_stage_id" class="frm-sel">
                    <option value="">SEÇİNİZ</option>
                    <?php foreach ($asamalar as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $dosya['current_stage_id'] == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">DURUM</label>
                <select name="status" class="frm-sel">
                    <option value="AKTIF" <?= $dosya['status'] == 'AKTIF' ? 'selected' : '' ?>>AKTİF</option>
                    <option value="KAPALI" <?= $dosya['status'] == 'KAPALI' ? 'selected' : '' ?>>KAPALI</option>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">AVUKAT</label>
                <select name="assigned_lawyer_user_id" class="frm-sel">
                    <option value="">SEÇİNİZ</option>
                    <?php foreach ($avukatlar as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $dosya['assigned_lawyer_user_id'] == $a['id'] ? 'selected' : '' ?>><?= e($a['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">SORUMLU</label>
                <select name="sorumlu_user_id" class="frm-sel">
                    <?php foreach ($sorumlular as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $dosya['sorumlu_user_id'] == $s['id'] ? 'selected' : '' ?>><?= e($s['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    
    <!-- MAĞDUR BİLGİLERİ -->
    <div class="form-sec">
        <div class="sec-title"><i class="fas fa-user"></i> MAĞDUR BİLGİLERİ</div>
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">T.C. KİMLİK</label>
                <input type="text" class="frm-in" value="<?= e($dosya['tc_kimlik']) ?>" disabled>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ADI SOYADI <span class="req">*</span></label>
                <input type="text" name="ad_soyad" class="frm-in" value="<?= e($dosya['ad_soyad']) ?>" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TELEFON</label>
                <input type="tel" name="telefon" class="frm-in" value="<?= e($dosya['telefon']) ?>">
            </div>
        </div>
    </div>
    
    <?php if ($dosya['case_type'] == 'ADK'): ?>
    <!-- ADK DETAY -->
    <div class="form-sec">
        <div class="sec-title"><i class="fas fa-car"></i> ARAÇ BİLGİLERİ</div>
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">PLAKA</label>
                <input type="text" name="plaka" class="frm-in" value="<?= e($detay['plaka'] ?? '') ?>">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">KARŞI PLAKA</label>
                <input type="text" name="karsi_plaka" class="frm-in" value="<?= e($detay['karsi_plaka'] ?? '') ?>">
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($dosya['case_type'] == 'BEDENI'): ?>
    <!-- BEDENİ DETAY -->
    <div class="form-sec">
        <div class="sec-title"><i class="fas fa-user-injured"></i> EK BİLGİLER</div>
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">DOĞUM TARİHİ</label>
                <input type="date" name="dogum_tarihi" class="frm-in" value="<?= e($detay['dogum_tarihi'] ?? '') ?>">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">MESLEK</label>
                <input type="text" name="meslek" class="frm-in" value="<?= e($detay['meslek'] ?? '') ?>">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">GELİR</label>
                <input type="number" name="gelir" class="frm-in" value="<?= e($detay['gelir'] ?? '') ?>">
            </div>
            <div class="frm-grp" style="grid-column:span 2">
                <label class="frm-lbl">ADRES</label>
                <textarea name="adres" class="frm-ta"><?= e($detay['adres'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="form-act">
        <a href="dosya_detay.php?id=<?= $id ?>" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</a>
        <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
