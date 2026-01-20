<?php
/**
 * MR HASAR DANIŞMANLIK - Firma Bilgisi
 */
$companyName = getSetting($pdo, 'company_name', 'MR HASAR DANIŞMANLIK');
$companySlogan = getSetting($pdo, 'company_slogan', 'HERZAMAN FARKEDER');
$companyPhone = getSetting($pdo, 'company_phone', '');
$companyEmail = getSetting($pdo, 'company_email', '');
$companyAddress = getSetting($pdo, 'company_address', '');
?>
<div class="page-header"><h1><i class="bi bi-building me-2"></i>Firma Bilgisi</h1></div>
<div class="card"><div class="card-body">
<div class="row">
<div class="col-md-6">
<div class="text-center mb-4">
<div class="display-1 text-primary mb-3"><i class="bi bi-car-front-fill"></i></div>
<h2><?= e($companyName) ?></h2>
<p class="text-muted"><?= e($companySlogan) ?></p>
</div>
<table class="table table-borderless">
<tr><td><i class="bi bi-telephone me-2"></i>Telefon:</td><td><?= e($companyPhone) ?: '-' ?></td></tr>
<tr><td><i class="bi bi-envelope me-2"></i>E-posta:</td><td><?= e($companyEmail) ?: '-' ?></td></tr>
<tr><td><i class="bi bi-geo-alt me-2"></i>Adres:</td><td><?= e($companyAddress) ?: '-' ?></td></tr>
</table>
</div>
<div class="col-md-6">
<div class="card bg-light"><div class="card-body">
<h5>Sistem Bilgisi</h5>
<table class="table table-sm table-borderless">
<tr><td>Versiyon:</td><td><strong><?= APP_VERSION ?></strong></td></tr>
<tr><td>PHP:</td><td><?= phpversion() ?></td></tr>
<tr><td>Veritabanı:</td><td>MySQL</td></tr>
<tr><td>Sunucu:</td><td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td></tr>
</table>
</div></div>
</div>
</div>
<div class="mt-4"><a href="index.php?page=ayarlar" class="btn btn-primary"><i class="bi bi-gear me-1"></i>Ayarları Düzenle</a></div>
</div></div>
