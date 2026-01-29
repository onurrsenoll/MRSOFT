<?php
/**
 * MR HASAR DANIŞMANLIK - DOSYA DETAY RAPORU
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$rapor = [];

try {
    $db = getDB();
    
    // Genel istatistikler
    $rapor['toplam'] = $db->query("SELECT COUNT(*) FROM cases")->fetchColumn();
    $rapor['aktif'] = $db->query("SELECT COUNT(*) FROM cases WHERE status = 'AKTIF'")->fetchColumn();
    $rapor['kapali'] = $db->query("SELECT COUNT(*) FROM cases WHERE status = 'KAPALI'")->fetchColumn();
    $rapor['adk'] = $db->query("SELECT COUNT(*) FROM cases WHERE case_type = 'ADK'")->fetchColumn();
    $rapor['bedeni'] = $db->query("SELECT COUNT(*) FROM cases WHERE case_type = 'BEDENI'")->fetchColumn();
    
    // Aylık açılan dosyalar
    $rapor['aylik'] = $db->query("
        SELECT DATE_FORMAT(acilis_tarihi, '%Y-%m') as ay, COUNT(*) as sayi
        FROM cases
        WHERE acilis_tarihi >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(acilis_tarihi, '%Y-%m')
        ORDER BY ay DESC
    ")->fetchAll();
    
    // Sigorta şirketlerine göre
    $rapor['sigorta'] = $db->query("
        SELECT i.name, COUNT(*) as sayi
        FROM cases c
        JOIN insurers i ON c.davali_sirket_id = i.id
        GROUP BY c.davali_sirket_id
        ORDER BY sayi DESC
        LIMIT 10
    ")->fetchAll();
    
    // Aşamalara göre
    $rapor['asama'] = $db->query("
        SELECT s.name, COUNT(*) as sayi
        FROM cases c
        JOIN stage_definitions s ON c.current_stage_id = s.id
        WHERE c.status = 'AKTIF'
        GROUP BY c.current_stage_id
        ORDER BY sayi DESC
    ")->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-file-alt"></i> DOSYA DETAY RAPORU</div>

<!-- GENEL İSTATİSTİKLER -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-folder"></i></div>
            <div>
                <div class="card-title">TOPLAM DOSYA</div>
                <div class="card-val"><?= number_format($rapor['toplam'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-check"></i></div>
            <div>
                <div class="card-title">AKTİF</div>
                <div class="card-val"><?= number_format($rapor['aktif'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-times"></i></div>
            <div>
                <div class="card-title">KAPALI</div>
                <div class="card-val"><?= number_format($rapor['kapali'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon purple"><i class="fas fa-car"></i></div>
            <div>
                <div class="card-title">ADK</div>
                <div class="card-val"><?= number_format($rapor['adk'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon cyan"><i class="fas fa-user-injured"></i></div>
            <div>
                <div class="card-title">BEDENİ</div>
                <div class="card-val"><?= number_format($rapor['bedeni'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px">
    <!-- AYLIK DAĞILIM -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><i class="fas fa-calendar"></i> AYLIK DOSYA DAĞILIMI</div>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>AY</th><th>DOSYA SAYISI</th></tr></thead>
                <tbody>
                    <?php foreach ($rapor['aylik'] ?? [] as $a): ?>
                    <tr>
                        <td><?= date('F Y', strtotime($a['ay'] . '-01')) ?></td>
                        <td style="font-weight:700;color:var(--blue)"><?= number_format($a['sayi']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- SİGORTA ŞİRKETLERİ -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><i class="fas fa-building"></i> SİGORTA ŞİRKETLERİNE GÖRE</div>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>SİGORTA ŞİRKETİ</th><th>DOSYA SAYISI</th></tr></thead>
                <tbody>
                    <?php foreach ($rapor['sigorta'] ?? [] as $s): ?>
                    <tr>
                        <td><?= e($s['name']) ?></td>
                        <td style="font-weight:700;color:var(--blue)"><?= number_format($s['sayi']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- AŞAMALARA GÖRE -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><i class="fas fa-tasks"></i> AKTİF DOSYALAR - AŞAMA DAĞILIMI</div>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>AŞAMA</th><th>DOSYA SAYISI</th></tr></thead>
                <tbody>
                    <?php foreach ($rapor['asama'] ?? [] as $a): ?>
                    <tr>
                        <td><span class="badge aktif"><?= e($a['name']) ?></span></td>
                        <td style="font-weight:700;color:var(--blue)"><?= number_format($a['sayi']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
