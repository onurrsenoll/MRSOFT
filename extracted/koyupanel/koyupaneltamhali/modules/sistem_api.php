<?php
/**
 * MR HASAR DANIŞMANLIK - API AYARLARI
 * HERZAMAN FARKEDER
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$apiSettings = [];

try {
    $db = getDB();

    // API ayarlarını getir
    $stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'api_%'");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $apiSettings[$row['setting_key']] = $row['setting_value'];
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

// AYARLARI KAYDET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'kaydet') {
    try {
        $db->beginTransaction();

        $settings = [
            'api_enabled' => isset($_POST['api_enabled']) ? '1' : '0',
            'api_key' => trim($_POST['api_key'] ?? ''),
            'api_secret' => trim($_POST['api_secret'] ?? ''),
            'api_sms_provider' => trim($_POST['api_sms_provider'] ?? ''),
            'api_sms_username' => trim($_POST['api_sms_username'] ?? ''),
            'api_sms_password' => trim($_POST['api_sms_password'] ?? ''),
            'api_email_host' => trim($_POST['api_email_host'] ?? ''),
            'api_email_port' => trim($_POST['api_email_port'] ?? ''),
            'api_email_username' => trim($_POST['api_email_username'] ?? ''),
            'api_email_password' => trim($_POST['api_email_password'] ?? ''),
        ];

        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                                  ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }

        $db->commit();
        header("Location: sistem_api.php?success=1");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// API KEY OLUŞTUR
if (isset($_GET['generate_key'])) {
    $newKey = bin2hex(random_bytes(32));
    try {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('api_key', ?)
                              ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$newKey, $newKey]);
        header("Location: sistem_api.php?success=2");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-plug"></i> API AYARLARI</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i>
    <?= $_GET['success'] == '2' ? 'YENİ API ANAHTARI OLUŞTURULDU!' : 'AYARLAR KAYDEDİLDİ!' ?>
</div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="action" value="kaydet">

    <!-- GENEL API AYARLARI -->
    <div class="form-wrap" style="margin-bottom:20px">
        <div class="form-sec">
            <div class="sec-title"><i class="fas fa-key"></i> API ANAHTARLARI</div>
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">API DURUMU</label>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 0">
                        <input type="checkbox" name="api_enabled" value="1" <?= ($apiSettings['api_enabled'] ?? '0') == '1' ? 'checked' : '' ?> style="width:20px;height:20px">
                        <span>API'yi Etkinleştir</span>
                    </label>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">API ANAHTARI</label>
                    <div style="display:flex;gap:10px">
                        <input type="text" name="api_key" class="frm-in" value="<?= e($apiSettings['api_key'] ?? '') ?>" readonly style="flex:1">
                        <a href="sistem_api.php?generate_key=1" class="btn btn-pri" onclick="return confirm('Yeni API anahtarı oluşturulsun mu?')">
                            <i class="fas fa-sync"></i> YENİ
                        </a>
                    </div>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">API GİZLİ ANAHTAR</label>
                    <input type="password" name="api_secret" class="frm-in" value="<?= e($apiSettings['api_secret'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- SMS AYARLARI -->
    <div class="form-wrap" style="margin-bottom:20px">
        <div class="form-sec">
            <div class="sec-title"><i class="fas fa-sms"></i> SMS SERVİSİ AYARLARI</div>
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">SMS SAĞLAYICI</label>
                    <select name="api_sms_provider" class="frm-sel">
                        <option value="">-- SEÇİNİZ --</option>
                        <option value="netgsm" <?= ($apiSettings['api_sms_provider'] ?? '') == 'netgsm' ? 'selected' : '' ?>>NETGSM</option>
                        <option value="iletimerkezi" <?= ($apiSettings['api_sms_provider'] ?? '') == 'iletimerkezi' ? 'selected' : '' ?>>İLETİ MERKEZİ</option>
                        <option value="mutlucell" <?= ($apiSettings['api_sms_provider'] ?? '') == 'mutlucell' ? 'selected' : '' ?>>MUTLUCELL</option>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">SMS KULLANICI ADI</label>
                    <input type="text" name="api_sms_username" class="frm-in" value="<?= e($apiSettings['api_sms_username'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">SMS ŞİFRE</label>
                    <input type="password" name="api_sms_password" class="frm-in" value="<?= e($apiSettings['api_sms_password'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- E-POSTA AYARLARI -->
    <div class="form-wrap" style="margin-bottom:20px">
        <div class="form-sec">
            <div class="sec-title"><i class="fas fa-envelope"></i> E-POSTA (SMTP) AYARLARI</div>
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">SMTP SUNUCU</label>
                    <input type="text" name="api_email_host" class="frm-in" value="<?= e($apiSettings['api_email_host'] ?? '') ?>" placeholder="smtp.example.com">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">SMTP PORT</label>
                    <input type="text" name="api_email_port" class="frm-in" value="<?= e($apiSettings['api_email_port'] ?? '587') ?>" placeholder="587">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">E-POSTA ADRESİ</label>
                    <input type="email" name="api_email_username" class="frm-in" value="<?= e($apiSettings['api_email_username'] ?? '') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">E-POSTA ŞİFRESİ</label>
                    <input type="password" name="api_email_password" class="frm-in" value="<?= e($apiSettings['api_email_password'] ?? '') ?>">
                </div>
            </div>
        </div>
        <div class="form-act">
            <a href="sistem_genel.php" class="btn btn-sec"><i class="fas fa-arrow-left"></i> GERİ</a>
            <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> AYARLARI KAYDET</button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
