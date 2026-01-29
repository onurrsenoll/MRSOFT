<?php
/**
 * MR HASAR DANIŞMANLIK - İŞ ORTAKLARI RAPORU
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$rapor = [];

try {
    $db = getDB();
    
    // Ortaklar ve bakiyeleri
    $rapor['ortaklar'] = $db->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM cases WHERE source_name = c.name) as dosya_sayisi
        FROM caris c 
        WHERE c.cari_type = 'ORTAK' AND c.is_active = 1
        ORDER BY c.balance DESC
    ")->fetchAll();
    
    // Toplam borç
    $rapor['toplam_borc'] = $db->query("SELECT COALESCE(SUM(balance), 0) FROM caris WHERE cari_type = 'ORTAK' AND balance > 0")->fetchColumn();
    
    // Toplam alacak
    $rapor['toplam_alacak'] = $db->query("SELECT COALESCE(ABS(SUM(balance)), 0) FROM caris WHERE cari_type = 'ORTAK' AND balance < 0")->fetchColumn();
    
    // Net durum
    $rapor['net'] = $db->query("SELECT COALESCE(SUM(balance), 0) FROM caris WHERE cari_type = 'ORTAK'")->fetchColumn();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-handshake"></i> İŞ ORTAKLARI RAPORU</div>

<!-- ÖZET KARTLAR -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-users"></i></div>
            <div>
                <div class="card-title">TOPLAM ORTAK</div>
                <div class="card-val"><?= count($rapor['ortaklar'] ?? []) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-arrow-down"></i></div>
            <div>
                <div class="card-title">TOPLAM ALACAK</div>
                <div class="card-val">₺<?= number_format($rapor['toplam_borc'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon red"><i class="fas fa-arrow-up"></i></div>
            <div>
                <div class="card-title">TOPLAM BORÇ</div>
                <div class="card-val">₺<?= number_format($rapor['toplam_alacak'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon <?= ($rapor['net'] ?? 0) >= 0 ? 'cyan' : 'orange' ?>"><i class="fas fa-balance-scale"></i></div>
            <div>
                <div class="card-title">NET DURUM</div>
                <div class="card-val">₺<?= number_format($rapor['net'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ORTAK DETAY RAPORU -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> ORTAK DETAY RAPORU</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ORTAK ADI</th>
                    <th>TELEFON</th>
                    <th>DOSYA SAYISI</th>
                    <th>BAKİYE</th>
                    <th>DURUM</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rapor['ortaklar'])): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text2)">ORTAK BULUNAMADI</td></tr>
                <?php else: foreach ($rapor['ortaklar'] as $o): ?>
                <tr>
                    <td style="font-weight:700"><?= e($o['name']) ?></td>
                    <td><?= e($o['phone'] ?? '-') ?></td>
                    <td style="font-weight:700;color:var(--blue)"><?= $o['dosya_sayisi'] ?></td>
                    <td style="font-weight:700;font-size:14px;color:<?= $o['balance'] >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                        ₺<?= number_format($o['balance'], 2, ',', '.') ?>
                    </td>
                    <td>
                        <?php if ($o['balance'] > 0): ?>
                        <span class="badge aktif">ALACAKLI</span>
                        <?php elseif ($o['balance'] < 0): ?>
                        <span class="badge kapali">BORÇLU</span>
                        <?php else: ?>
                        <span class="badge">SIFIR</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="ortak_hesaplari.php?ortak=<?= $o['id'] ?>" class="btn btn-sec" style="padding:5px 10px">
                            <i class="fas fa-history"></i> HAREKETLER
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
