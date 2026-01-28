<?php
/**
 * MR HASAR DANIŞMANLIK - FİRMA BİLGİLERİ
 * HERZAMAN FARKEDER
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$firmaSettings = [];

try {
    $db = getDB();

    // Firma ayarlarını getir
    $stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'firma_%'");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $firmaSettings[$row['setting_key']] = $row['setting_value'];
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

// AYARLARI KAYDET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'kaydet') {
    try {
        $db->beginTransaction();

        $settings = [
            'firma_adi' => trim(mb_strtoupper($_POST['firma_adi'] ?? '', 'UTF-8')),
            'firma_unvan' => trim(mb_strtoupper($_POST['firma_unvan'] ?? '', 'UTF-8')),
            'firma_vergi_dairesi' => trim(mb_strtoupper($_POST['firma_vergi_dairesi'] ?? '', 'UTF-8')),
            'firma_vergi_no' => trim($_POST['firma_vergi_no'] ?? ''),
            'firma_adres' => trim(mb_strtoupper($_POST['firma_adres'] ?? '', 'UTF-8')),
            'firma_il' => trim(mb_strtoupper($_POST['firma_il'] ?? '', 'UTF-8')),
            'firma_ilce' => trim(mb_strtoupper($_POST['firma_ilce'] ?? '', 'UTF-8')),
            'firma_telefon' => trim($_POST['firma_telefon'] ?? ''),
            'firma_fax' => trim($_POST['firma_fax'] ?? ''),
            'firma_email' => trim($_POST['firma_email'] ?? ''),
            'firma_web' => trim($_POST['firma_web'] ?? ''),
            'firma_yetkili' => trim(mb_strtoupper($_POST['firma_yetkili'] ?? '', 'UTF-8')),
            'firma_yetkili_tel' => trim($_POST['firma_yetkili_tel'] ?? ''),
            'firma_iban' => trim($_POST['firma_iban'] ?? ''),
            'firma_banka' => trim(mb_strtoupper($_POST['firma_banka'] ?? '', 'UTF-8')),
        ];

        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                                  ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }

        $db->commit();
        header("Location: sistem_firma.php?success=1");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-building"></i> FİRMA BİLGİLERİ</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> FİRMA BİLGİLERİ KAYDEDİLDİ!</div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="action" value="kaydet">

    <!-- FİRMA BİLGİLERİ -->
    <div class="form-wrap" style="margin-bottom:20px">
        <div class="form-sec">
            <div class="sec-title"><i class="fas fa-building"></i> FİRMA BİLGİLERİ</div>
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">FİRMA ADI <span class="req">*</span></label>
                    <input type="text" name="firma_adi" class="frm-in" value="<?= e($firmaSettings['firma_adi'] ?? 'MR HASAR DANIŞMANLIK') ?>" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">FİRMA ÜNVANI</label>
                    <input type="text" name="firma_unvan" class="frm-in" value="<?= e($firmaSettings['firma_unvan'] ?? '') ?>" placeholder="LTD. ŞTİ. / A.Ş.">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">VERGİ DAİRESİ</label>
                    <input type="text" name="firma_vergi_dairesi" class="frm-in" value="<?= e($firmaSettings['firma_vergi_dairesi'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">VERGİ NO</label>
                    <input type="text" name="firma_vergi_no" class="frm-in" value="<?= e($firmaSettings['firma_vergi_no'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- İLETİŞİM BİLGİLERİ -->
    <div class="form-wrap" style="margin-bottom:20px">
        <div class="form-sec">
            <div class="sec-title"><i class="fas fa-map-marker-alt"></i> İLETİŞİM BİLGİLERİ</div>
            <div class="form-grid">
                <div class="frm-grp" style="grid-column: span 2">
                    <label class="frm-lbl">ADRES</label>
                    <textarea name="firma_adres" class="frm-ta" rows="2"><?= e($firmaSettings['firma_adres'] ?? '') ?></textarea>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">İL</label>
                    <input type="text" name="firma_il" class="frm-in" value="<?= e($firmaSettings['firma_il'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">İLÇE</label>
                    <input type="text" name="firma_ilce" class="frm-in" value="<?= e($firmaSettings['firma_ilce'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TELEFON</label>
                    <input type="text" name="firma_telefon" class="frm-in" value="<?= e($firmaSettings['firma_telefon'] ?? '') ?>" placeholder="0212 XXX XX XX">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">FAX</label>
                    <input type="text" name="firma_fax" class="frm-in" value="<?= e($firmaSettings['firma_fax'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">E-POSTA</label>
                    <input type="email" name="firma_email" class="frm-in" value="<?= e($firmaSettings['firma_email'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">WEB SİTESİ</label>
                    <input type="text" name="firma_web" class="frm-in" value="<?= e($firmaSettings['firma_web'] ?? '') ?>" placeholder="www.example.com">
                </div>
            </div>
        </div>
    </div>

    <!-- YETKİLİ BİLGİLERİ -->
    <div class="form-wrap" style="margin-bottom:20px">
        <div class="form-sec">
            <div class="sec-title"><i class="fas fa-user-tie"></i> YETKİLİ BİLGİLERİ</div>
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">YETKİLİ AD SOYAD</label>
                    <input type="text" name="firma_yetkili" class="frm-in" value="<?= e($firmaSettings['firma_yetkili'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">YETKİLİ TELEFON</label>
                    <input type="text" name="firma_yetkili_tel" class="frm-in" value="<?= e($firmaSettings['firma_yetkili_tel'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- BANKA BİLGİLERİ -->
    <div class="form-wrap" style="margin-bottom:20px">
        <div class="form-sec">
            <div class="sec-title"><i class="fas fa-university"></i> BANKA BİLGİLERİ</div>
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">BANKA ADI</label>
                    <input type="text" name="firma_banka" class="frm-in" value="<?= e($firmaSettings['firma_banka'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">IBAN</label>
                    <input type="text" name="firma_iban" class="frm-in" value="<?= e($firmaSettings['firma_iban'] ?? '') ?>" placeholder="TR00 0000 0000 0000 0000 0000 00">
                </div>
            </div>
        </div>
        <div class="form-act">
            <a href="sistem_genel.php" class="btn btn-sec"><i class="fas fa-arrow-left"></i> GERİ</a>
            <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> BİLGİLERİ KAYDET</button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
