<?php
/**
 * MR HASAR DANIŞMANLIK - FİNANSAL ÖZET RAPORU
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$rapor = [];

try {
    $db = getDB();
    
    // Genel finansal özet
    $rapor['toplam_gelir'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE txn_type = 'GELIR'")->fetchColumn();
    $rapor['toplam_gider'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE txn_type = 'GIDER'")->fetchColumn();
    $rapor['net'] = $rapor['toplam_gelir'] - $rapor['toplam_gider'];
    
    // Bu ay
    $rapor['bu_ay_gelir'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE txn_type = 'GELIR' AND MONTH(txn_date) = MONTH(CURDATE()) AND YEAR(txn_date) = YEAR(CURDATE())")->fetchColumn();
    $rapor['bu_ay_gider'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE txn_type = 'GIDER' AND MONTH(txn_date) = MONTH(CURDATE()) AND YEAR(txn_date) = YEAR(CURDATE())")->fetchColumn();
    
    // Toplam tahsilatlar (dosyalardan)
    $rapor['toplam_tahsilat'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM case_payments WHERE payment_type = 'GELEN_TAHSILAT'")->fetchColumn();
    $rapor['toplam_musteri_odeme'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM case_payments WHERE payment_type = 'MUSTERIYE_ODEME'")->fetchColumn();
    $rapor['toplam_masraf'] = $db->query("SELECT COALESCE(SUM(amount), 0) FROM case_expenses")->fetchColumn();
    
    // Aylık gelir/gider
    $rapor['aylik'] = $db->query("
        SELECT 
            DATE_FORMAT(txn_date, '%Y-%m') as ay,
            SUM(CASE WHEN txn_type = 'GELIR' THEN amount ELSE 0 END) as gelir,
            SUM(CASE WHEN txn_type = 'GIDER' THEN amount ELSE 0 END) as gider
        FROM finance_transactions
        WHERE txn_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(txn_date, '%Y-%m')
        ORDER BY ay DESC
    ")->fetchAll();
    
    // Kasa bakiyeleri
    $rapor['kasalar'] = $db->query("SELECT name, balance FROM cashboxes WHERE is_active = 1")->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-coins"></i> FİNANSAL ÖZET RAPORU</div>

<!-- GENEL ÖZET -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-arrow-down"></i></div>
            <div>
                <div class="card-title">TOPLAM GELİR</div>
                <div class="card-val">₺<?= number_format($rapor['toplam_gelir'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon red"><i class="fas fa-arrow-up"></i></div>
            <div>
                <div class="card-title">TOPLAM GİDER</div>
                <div class="card-val">₺<?= number_format($rapor['toplam_gider'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon <?= ($rapor['net'] ?? 0) >= 0 ? 'blue' : 'red' ?>"><i class="fas fa-balance-scale"></i></div>
            <div>
                <div class="card-title">NET KAR</div>
                <div class="card-val">₺<?= number_format($rapor['net'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon cyan"><i class="fas fa-calendar"></i></div>
            <div>
                <div class="card-title">BU AY GELİR</div>
                <div class="card-val">₺<?= number_format($rapor['bu_ay_gelir'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- DOSYA FİNANSAL ÖZET -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <div class="card-title">TOPLAM TAHSİLAT</div>
                <div class="card-val">₺<?= number_format($rapor['toplam_tahsilat'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-user"></i></div>
            <div>
                <div class="card-title">MÜŞTERİYE ÖDENEN</div>
                <div class="card-val">₺<?= number_format($rapor['toplam_musteri_odeme'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon red"><i class="fas fa-receipt"></i></div>
            <div>
                <div class="card-title">TOPLAM MASRAF</div>
                <div class="card-val">₺<?= number_format($rapor['toplam_masraf'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px">
    <!-- AYLIK DAĞILIM -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><i class="fas fa-chart-bar"></i> AYLIK GELİR/GİDER</div>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>AY</th><th>GELİR</th><th>GİDER</th><th>NET</th></tr></thead>
                <tbody>
                    <?php foreach ($rapor['aylik'] ?? [] as $a): ?>
                    <tr>
                        <td><?= date('F Y', strtotime($a['ay'] . '-01')) ?></td>
                        <td style="color:var(--green);font-weight:700">₺<?= number_format($a['gelir'], 0, ',', '.') ?></td>
                        <td style="color:var(--red);font-weight:700">₺<?= number_format($a['gider'], 0, ',', '.') ?></td>
                        <td style="font-weight:700;color:<?= ($a['gelir'] - $a['gider']) >= 0 ? 'var(--blue)' : 'var(--red)' ?>">
                            ₺<?= number_format($a['gelir'] - $a['gider'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- KASA BAKİYELERİ -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><i class="fas fa-wallet"></i> KASA BAKİYELERİ</div>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>KASA</th><th>BAKİYE</th></tr></thead>
                <tbody>
                    <?php foreach ($rapor['kasalar'] ?? [] as $k): ?>
                    <tr>
                        <td><i class="fas fa-cash-register" style="color:var(--blue);margin-right:10px"></i><?= e($k['name']) ?></td>
                        <td style="font-weight:700;color:<?= $k['balance'] >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                            ₺<?= number_format($k['balance'], 2, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
