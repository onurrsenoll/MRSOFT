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

<!-- Bu bölümler kaldırıldı: Hızlı İşlemler ve Son Dosyalar -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
