<?php
/**
 * MR HASAR DANIŞMANLIK - AJANDA
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';

try {
    $db = getDB();
    
    // Yaklaşan CRM takipleri
    $stmt = $db->prepare("
        SELECT c.*, u.full_name as sorumlu_adi
        FROM crm_records c
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        WHERE c.durum = 'TAKIPTE' AND c.sonraki_tarih IS NOT NULL
        ORDER BY c.sonraki_tarih ASC
        LIMIT 20
    ");
    $stmt->execute();
    $crmTakip = $stmt->fetchAll();
    
    // Son eklenen dosyalar
    $sonDosyalar = $db->query("
        SELECT c.*, u.full_name as sorumlu_adi
        FROM cases c
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 10
    ")->fetchAll();
    
    // İstatistikler
    $bugunDosya = $db->query("SELECT COUNT(*) FROM cases WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $bugunCRM = $db->query("SELECT COUNT(*) FROM crm_records WHERE DATE(sonraki_tarih) = CURDATE() AND durum = 'TAKIPTE'")->fetchColumn();
    $gecikmisCRM = $db->query("SELECT COUNT(*) FROM crm_records WHERE sonraki_tarih < NOW() AND durum = 'TAKIPTE'")->fetchColumn();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-calendar-alt"></i> AJANDA</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<!-- ÖZET KARTLAR -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-folder-plus"></i></div>
            <div>
                <div class="card-title">BUGÜN AÇILAN DOSYA</div>
                <div class="card-val"><?= $bugunDosya ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-phone"></i></div>
            <div>
                <div class="card-title">BUGÜN ARANACAK</div>
                <div class="card-val"><?= $bugunCRM ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="card-title">GECİKMİŞ TAKİP</div>
                <div class="card-val"><?= $gecikmisCRM ?? 0 ?></div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px">
    <!-- YAKLAŞAN CRM TAKİPLERİ -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><i class="fas fa-phone-alt"></i> YAKLAŞAN ARAMALAR</div>
            <a href="crm.php" class="btn btn-sec"><i class="fas fa-list"></i> TÜMÜ</a>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>TARİH</th><th>ADI SOYADI</th><th>TELEFON</th></tr></thead>
                <tbody>
                    <?php if (empty($crmTakip)): ?>
                    <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text2)">TAKİP YOK</td></tr>
                    <?php else: foreach ($crmTakip as $c): ?>
                    <tr style="<?= strtotime($c['sonraki_tarih']) < time() ? 'background:rgba(239,68,68,0.1)' : '' ?>">
                        <td style="<?= strtotime($c['sonraki_tarih']) < time() ? 'color:var(--red);font-weight:700' : '' ?>">
                            <?= date('d.m.Y H:i', strtotime($c['sonraki_tarih'])) ?>
                        </td>
                        <td><?= e($c['ad_soyad']) ?></td>
                        <td><?= e($c['telefon']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- SON DOSYALAR -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><i class="fas fa-folder-open"></i> SON EKLENEN DOSYALAR</div>
            <a href="dosyalar.php" class="btn btn-sec"><i class="fas fa-list"></i> TÜMÜ</a>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>TARİH</th><th>DOSYA NO</th><th>ADI SOYADI</th></tr></thead>
                <tbody>
                    <?php if (empty($sonDosyalar)): ?>
                    <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text2)">DOSYA YOK</td></tr>
                    <?php else: foreach ($sonDosyalar as $d): ?>
                    <tr>
                        <td><?= date('d.m.Y', strtotime($d['created_at'])) ?></td>
                        <td style="color:var(--blue);font-weight:700"><?= e($d['dosya_no']) ?></td>
                        <td><?= e($d['ad_soyad']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
