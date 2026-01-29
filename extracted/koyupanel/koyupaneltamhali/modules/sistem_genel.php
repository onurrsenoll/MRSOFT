<?php
/**
 * MR HASAR DANIŞMANLIK - SİSTEM GENEL AYARLAR
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

try {
    $db = getDB();
    
    // İstatistikler
    $stats = [
        'users' => $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
        'cases' => $db->query("SELECT COUNT(*) FROM cases")->fetchColumn(),
        'insurers' => $db->query("SELECT COUNT(*) FROM insurers WHERE is_active = 1")->fetchColumn(),
        'stages' => $db->query("SELECT COUNT(*) FROM stage_definitions WHERE is_active = 1")->fetchColumn(),
    ];
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-cog"></i> SİSTEM GENEL AYARLAR</div>

<!-- SİSTEM BİLGİLERİ -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-users"></i></div>
            <div>
                <div class="card-title">AKTİF KULLANICI</div>
                <div class="card-val"><?= $stats['users'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-folder"></i></div>
            <div>
                <div class="card-title">TOPLAM DOSYA</div>
                <div class="card-val"><?= $stats['cases'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-building"></i></div>
            <div>
                <div class="card-title">SİGORTA ŞİRKETİ</div>
                <div class="card-val"><?= $stats['insurers'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon purple"><i class="fas fa-tasks"></i></div>
            <div>
                <div class="card-title">AŞAMA TANIMI</div>
                <div class="card-val"><?= $stats['stages'] ?? 0 ?></div>
            </div>
        </div>
    </div>
</div>

<!-- HIZLI ERİŞİM -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-bolt"></i> HIZLI ERİŞİM</div>
    </div>
    <div style="padding:20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px">
        <a href="sistem_kullanici.php" class="btn btn-pri" style="justify-content:center;padding:20px">
            <i class="fas fa-users-cog"></i> KULLANICI YÖNETİMİ
        </a>
        <a href="sistem_asama.php" class="btn btn-war" style="justify-content:center;padding:20px">
            <i class="fas fa-tasks"></i> AŞAMA YÖNETİMİ
        </a>
        <a href="tanim_sigorta.php" class="btn btn-suc" style="justify-content:center;padding:20px">
            <i class="fas fa-building"></i> SİGORTA ŞİRKETLERİ
        </a>
        <a href="tanim_personel.php" class="btn btn-sec" style="justify-content:center;padding:20px">
            <i class="fas fa-id-card"></i> PERSONEL
        </a>
    </div>
</div>

<!-- SİSTEM BİLGİSİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-info-circle"></i> SİSTEM BİLGİSİ</div>
    </div>
    <div style="padding:20px">
        <table style="width:100%">
            <tr><td style="padding:10px;color:var(--text2);width:200px">UYGULAMA:</td><td style="padding:10px;font-weight:700"><?= APP_NAME ?></td></tr>
            <tr><td style="padding:10px;color:var(--text2)">VERSİYON:</td><td style="padding:10px"><?= APP_VERSION ?></td></tr>
            <tr><td style="padding:10px;color:var(--text2)">PHP VERSİYONU:</td><td style="padding:10px"><?= phpversion() ?></td></tr>
            <tr><td style="padding:10px;color:var(--text2)">VERİTABANI:</td><td style="padding:10px"><?= DB_NAME ?></td></tr>
            <tr><td style="padding:10px;color:var(--text2)">SUNUCU:</td><td style="padding:10px"><?= $_SERVER['SERVER_SOFTWARE'] ?? '-' ?></td></tr>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
