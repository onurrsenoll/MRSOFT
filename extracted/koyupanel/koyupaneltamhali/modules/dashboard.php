<?php
/**
 * MR HASAR DANIŞMANLIK - DASHBOARD
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

// İstatistikler
$stats = [
    'total' => 0,
    'aktif' => 0,
    'kapali' => 0,
    'adk' => 0,
    'bedeni' => 0,
    'gelir' => 0
];
$sonDosyalar = [];
$error = '';

try {
    $db = getDB();
    
    // Toplam dosya
    $stats['total'] = $db->query("SELECT COUNT(*) FROM cases")->fetchColumn();
    
    // Aktif dosya
    $stats['aktif'] = $db->query("SELECT COUNT(*) FROM cases WHERE status = 'AKTIF'")->fetchColumn();
    
    // Kapalı dosya
    $stats['kapali'] = $db->query("SELECT COUNT(*) FROM cases WHERE status = 'KAPALI'")->fetchColumn();
    
    // ADK dosya
    $stats['adk'] = $db->query("SELECT COUNT(*) FROM cases WHERE case_type = 'ADK'")->fetchColumn();
    
    // Bedeni dosya
    $stats['bedeni'] = $db->query("SELECT COUNT(*) FROM cases WHERE case_type = 'BEDENI'")->fetchColumn();
    
    // Aylık gelir
    $stats['gelir'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE txn_type = 'GELIR' AND MONTH(txn_date) = MONTH(CURDATE()) AND YEAR(txn_date) = YEAR(CURDATE())")->fetchColumn();
    
    // Son 10 dosya
    $sonDosyalar = $db->query("
        SELECT c.*,
               i.name as sigorta_adi,
               s.name as asama_adi,
               COALESCE(u.full_name, u.name, u.username) as sorumlu_adi
        FROM cases c
        LEFT JOIN insurers i ON c.davali_sirket_id = i.id
        LEFT JOIN stage_definitions s ON c.current_stage_id = s.id
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 10
    ")->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <i class="fas fa-tachometer-alt"></i> KONTROL PANELİ
</div>

<?php if ($error): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i> VERİTABANI HATASI: <?= e($error) ?>
</div>
<?php endif; ?>

<!-- İSTATİSTİK KARTLARI -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-folder-open"></i></div>
            <div>
                <div class="card-title">TOPLAM DOSYA</div>
                <div class="card-val"><?= number_format($stats['total']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="card-title">AKTİF DOSYA</div>
                <div class="card-val"><?= number_format($stats['aktif']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-clock"></i></div>
            <div>
                <div class="card-title">KAPALI DOSYA</div>
                <div class="card-val"><?= number_format($stats['kapali']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon purple"><i class="fas fa-car-crash"></i></div>
            <div>
                <div class="card-title">ADK DOSYA</div>
                <div class="card-val"><?= number_format($stats['adk']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon cyan"><i class="fas fa-user-injured"></i></div>
            <div>
                <div class="card-title">BEDENİ HASAR</div>
                <div class="card-val"><?= number_format($stats['bedeni']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-lira-sign"></i></div>
            <div>
                <div class="card-title">AYLIK GELİR</div>
                <div class="card-val">₺<?= number_format($stats['gelir'], 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- HIZLI İŞLEMLER -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:30px">
    <a href="dosya_ekle.php" class="btn btn-suc" style="justify-content:center;padding:16px">
        <i class="fas fa-plus-circle"></i> YENİ DOSYA EKLE
    </a>
    <a href="dosyalar.php" class="btn btn-pri" style="justify-content:center;padding:16px">
        <i class="fas fa-list"></i> TÜM DOSYALAR
    </a>
    <a href="crm.php" class="btn btn-war" style="justify-content:center;padding:16px">
        <i class="fas fa-headset"></i> CRM TAKİP
    </a>
    <a href="rapor_finansal.php" class="btn btn-sec" style="justify-content:center;padding:16px">
        <i class="fas fa-chart-bar"></i> RAPORLAR
    </a>
</div>

<!-- SON DOSYALAR -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title">
            <i class="fas fa-history"></i> SON EKLENEN DOSYALAR
        </div>
        <a href="dosyalar.php" class="btn btn-sec">
            <i class="fas fa-list"></i> TÜMÜNÜ GÖR
        </a>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>DOSYA NO</th>
                    <th>T.C.</th>
                    <th>ADI SOYADI</th>
                    <th>TÜR</th>
                    <th>SİGORTA ŞİRKETİ</th>
                    <th>AŞAMA</th>
                    <th>SORUMLU</th>
                    <th>TARİH</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sonDosyalar)): ?>
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--text2)">
                        <i class="fas fa-folder-open" style="font-size:40px;margin-bottom:15px;display:block;opacity:0.5"></i>
                        HENÜZ DOSYA BULUNMUYOR<br>
                        <small style="margin-top:10px;display:block">Yeni dosya eklemek için yukarıdaki butonu kullanın</small>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($sonDosyalar as $dosya): ?>
                <tr>
                    <td style="color:var(--blue);font-weight:700"><?= e($dosya['dosya_no']) ?></td>
                    <td><?= substr($dosya['tc_kimlik'], 0, 3) ?>***<?= substr($dosya['tc_kimlik'], -3) ?></td>
                    <td><?= e($dosya['ad_soyad']) ?></td>
                    <td>
                        <span class="badge <?= strtolower($dosya['case_type']) ?>">
                            <?= e($dosya['case_type']) ?>
                        </span>
                    </td>
                    <td><?= e($dosya['sigorta_adi'] ?? '-') ?></td>
                    <td>
                        <span class="badge aktif"><?= e($dosya['asama_adi'] ?? '-') ?></span>
                    </td>
                    <td><?= e($dosya['sorumlu_adi'] ?? '-') ?></td>
                    <td><?= $dosya['acilis_tarihi'] ? date('d.m.Y', strtotime($dosya['acilis_tarihi'])) : '-' ?></td>
                    <td>
                        <div class="icons">
                            <a href="dosya_detay.php?id=<?= $dosya['id'] ?>" class="ic view" title="DETAY">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="dosya_duzenle.php?id=<?= $dosya['id'] ?>" class="ic edit" title="DÜZENLE">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="dosya_evrak.php?id=<?= $dosya['id'] ?>" class="ic doc" title="EVRAKLAR">
                                <i class="fas fa-file-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
