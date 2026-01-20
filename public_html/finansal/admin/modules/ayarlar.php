<?php
/**
 * MR HASAR DANIŞMANLIK - Genel Ayarlar
 * HERZAMAN FARKEDER
 */

// Kayıt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = str_replace('setting_', '', $key);
            setSetting($pdo, $settingKey, clean($value));
        }
    }
    setFlash('success', 'Ayarlar kaydedildi.');
    header('Location: index.php?page=ayarlar');
    exit;
}

// Ayarları çek
$stmt = $pdo->query("SELECT * FROM settings");
$settingsRaw = $stmt->fetchAll();
$settings = [];
foreach ($settingsRaw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-gear me-2"></i>Genel Ayarlar</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item active">Ayarlar</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<form method="POST">
    <div class="row">
        <div class="col-lg-6">
            <!-- Firma Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-building me-2"></i>Firma Bilgileri
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Firma Adı</label>
                        <input type="text" class="form-control" name="setting_company_name"
                               value="<?= e($settings['company_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slogan</label>
                        <input type="text" class="form-control" name="setting_company_slogan"
                               value="<?= e($settings['company_slogan'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="text" class="form-control" name="setting_company_phone"
                               value="<?= e($settings['company_phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" class="form-control" name="setting_company_email"
                               value="<?= e($settings['company_email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea class="form-control" name="setting_company_address" rows="2"><?= e($settings['company_address'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vergi Dairesi</label>
                                <input type="text" class="form-control" name="setting_company_tax_office"
                                       value="<?= e($settings['company_tax_office'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vergi No</label>
                                <input type="text" class="form-control" name="setting_company_tax_no"
                                       value="<?= e($settings['company_tax_no'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Hesaplama Ayarları -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-calculator me-2"></i>Hesaplama Ayarları
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">ADK Varsayılan Komisyon (%)</label>
                        <input type="number" class="form-control" name="setting_adk_default_rate"
                               value="<?= e($settings['adk_default_rate'] ?? '10') ?>" min="0" max="100" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">BH Varsayılan Komisyon (%)</label>
                        <input type="number" class="form-control" name="setting_bh_default_rate"
                               value="<?= e($settings['bh_default_rate'] ?? '15') ?>" min="0" max="100" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PMF İskonto Oranı (%)</label>
                        <input type="number" class="form-control" name="setting_pmf_discount_rate"
                               value="<?= e($settings['pmf_discount_rate'] ?? '1.8') ?>" min="0" max="10" step="0.1">
                    </div>
                </div>
            </div>

            <!-- Dosya Ayarları -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-folder me-2"></i>Dosya Ayarları
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ADK Dosya No Ön Eki</label>
                                <input type="text" class="form-control" name="setting_file_no_prefix_adk"
                                       value="<?= e($settings['file_no_prefix_adk'] ?? 'ADK') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">BH Dosya No Ön Eki</label>
                                <input type="text" class="form-control" name="setting_file_no_prefix_bh"
                                       value="<?= e($settings['file_no_prefix_bh'] ?? 'BH') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-lg me-2"></i>Ayarları Kaydet
        </button>
    </div>
</form>
