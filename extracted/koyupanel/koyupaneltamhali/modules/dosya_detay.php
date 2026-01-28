<?php
/**
 * MR HASAR DANIŞMANLIK - DOSYA DETAY
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';
startSecureSession();

$error = '';
$dosya = null;
$asamalar = [];
$hesaplamalar = [];

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: dosyalar.php');
    exit;
}

try {
    $db = getDB();
    
    // Dosya bilgilerini getir
    $stmt = $db->prepare("
        SELECT c.*,
               s.firma_adi as sigorta_adi,
               a.asama_adi as asama_adi,
               u.full_name as sorumlu_adi,
               av.full_name as avukat_adi,
               y.ad_soyad as yonlendiren_adi
        FROM cases c
        LEFT JOIN sigorta_sirketleri s ON c.davali_sirket_id = s.id
        LEFT JOIN asama_durumlari a ON c.current_stage_id = a.id
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        LEFT JOIN users av ON c.assigned_lawyer_user_id = av.id
        LEFT JOIN yonlendirenler y ON c.yonlendiren_id = y.id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $dosya = $stmt->fetch();
    
    if (!$dosya) {
        header('Location: dosyalar.php');
        exit;
    }
    
    // Aşamaları getir
    $asamalar = $db->query("SELECT id, asama_adi FROM asama_durumlari WHERE durum = 1 ORDER BY sira")->fetchAll();
    
    // ADK Hesaplamalarını getir
    if ($dosya['case_type'] == 'ADK') {
        $stmt = $db->prepare("SELECT * FROM adk_calculations WHERE case_id = ? ORDER BY created_at DESC");
        $stmt->execute([$id]);
        $hesaplamalar = $stmt->fetchAll();
    }
    
    // BH Hesaplamalarını getir
    if ($dosya['case_type'] == 'BEDENI') {
        $stmt = $db->prepare("SELECT * FROM bh_calculations WHERE case_id = ? ORDER BY created_at DESC");
        $stmt->execute([$id]);
        $hesaplamalar = $stmt->fetchAll();
    }
    
    // Aşama geçmişi
    $stmt = $db->prepare("
        SELECT h.*, a.asama_adi, u.full_name as degistiren
        FROM case_stage_history h
        LEFT JOIN asama_durumlari a ON h.stage_id = a.id
        LEFT JOIN users u ON h.changed_by_user_id = u.id
        WHERE h.case_id = ?
        ORDER BY h.changed_at DESC
    ");
    $stmt->execute([$id]);
    $asama_gecmisi = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'DB HATASI: ' . $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.detay-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
@media (max-width: 1200px) { .detay-grid { grid-template-columns: 1fr; } }

.info-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
.info-card-title { font-size: 14px; font-weight: 700; color: #3b82f6; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
.info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #1e293b; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #64748b; font-size: 12px; }
.info-value { color: #f1f5f9; font-weight: 600; font-size: 13px; text-align: right; }

.badge-big { padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 700; }
.badge-adk { background: rgba(236, 72, 153, 0.2); color: #ec4899; }
.badge-bedeni { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.badge-mdk { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }

.action-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
.btn-action { padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; }
.btn-hesapla { background: linear-gradient(135deg, #10b981, #06b6d4); color: #fff; }
.btn-edit { background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; }
.btn-evrak { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; }
.btn-back { background: #334155; color: #f1f5f9; }

.hesaplama-card { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 15px; margin-bottom: 10px; }
.hesaplama-card:hover { border-color: #3b82f6; }
.hesaplama-tarih { font-size: 11px; color: #64748b; }
.hesaplama-tutar { font-size: 18px; font-weight: 800; color: #10b981; margin-top: 5px; }
.hesaplama-link { color: #3b82f6; font-size: 12px; text-decoration: none; }

.timeline { position: relative; padding-left: 20px; }
.timeline::before { content: ''; position: absolute; left: 5px; top: 0; bottom: 0; width: 2px; background: #334155; }
.timeline-item { position: relative; padding-bottom: 15px; }
.timeline-item::before { content: ''; position: absolute; left: -20px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; border: 2px solid #1e293b; }
.timeline-content { background: #0f172a; padding: 10px 15px; border-radius: 8px; }
.timeline-title { font-weight: 600; color: #f1f5f9; font-size: 13px; }
.timeline-meta { font-size: 11px; color: #64748b; margin-top: 5px; }
</style>

<div class="page-title">
    <i class="fas fa-folder-open"></i> DOSYA DETAY: <?= htmlspecialchars($dosya['dosya_no']) ?>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- AKSIYON BUTONLARI -->
<div class="action-buttons">
    <a href="dosyalar.php" class="btn-action btn-back"><i class="fas fa-arrow-left"></i> GERİ</a>
    <a href="dosya_duzenle.php?id=<?= $id ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> DÜZENLE</a>
    <a href="dosya_evrak.php?id=<?= $id ?>" class="btn-action btn-evrak"><i class="fas fa-file-alt"></i> EVRAKLAR</a>
    
    <?php if ($dosya['case_type'] == 'ADK'): ?>
    <a href="adk_hesaplama.php?case_id=<?= $id ?>" class="btn-action btn-hesapla"><i class="fas fa-calculator"></i> ADK HESAPLA</a>
    <?php endif; ?>
    
    <?php if ($dosya['case_type'] == 'BEDENI'): ?>
    <a href="bh_hesaplama.php?case_id=<?= $id ?>" class="btn-action btn-hesapla"><i class="fas fa-calculator"></i> BH HESAPLA</a>
    <?php endif; ?>
</div>

<div class="detay-grid">
    <!-- SOL KOLON -->
    <div>
        <!-- DOSYA BİLGİLERİ -->
        <div class="info-card">
            <div class="info-card-title"><i class="fas fa-folder"></i> DOSYA BİLGİLERİ</div>
            <div class="info-row">
                <span class="info-label">DOSYA NO</span>
                <span class="info-value" style="color:#3b82f6"><?= htmlspecialchars($dosya['dosya_no']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">DOSYA TÜRÜ</span>
                <span class="info-value">
                    <?php
                    $badge = 'badge-mdk';
                    if ($dosya['case_type'] == 'ADK') $badge = 'badge-adk';
                    elseif ($dosya['case_type'] == 'BEDENI') $badge = 'badge-bedeni';
                    ?>
                    <span class="badge-big <?= $badge ?>"><?= htmlspecialchars($dosya['case_type']) ?></span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">KAZA TARİHİ</span>
                <span class="info-value"><?= $dosya['kaza_tarihi'] ? date('d.m.Y', strtotime($dosya['kaza_tarihi'])) : '-' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">AÇILIŞ TARİHİ</span>
                <span class="info-value"><?= $dosya['acilis_tarihi'] ? date('d.m.Y', strtotime($dosya['acilis_tarihi'])) : '-' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">DURUM</span>
                <span class="info-value"><span class="badge aktif"><?= htmlspecialchars($dosya['status'] ?? 'AKTİF') ?></span></span>
            </div>
            <div class="info-row">
                <span class="info-label">AŞAMA</span>
                <span class="info-value"><?= htmlspecialchars($dosya['asama_adi'] ?? '-') ?></span>
            </div>
        </div>
        
        <!-- MAĞDUR BİLGİLERİ -->
        <div class="info-card">
            <div class="info-card-title"><i class="fas fa-user"></i> MAĞDUR BİLGİLERİ</div>
            <div class="info-row">
                <span class="info-label">AD SOYAD</span>
                <span class="info-value"><?= htmlspecialchars($dosya['ad_soyad']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">TC KİMLİK</span>
                <span class="info-value"><?= htmlspecialchars($dosya['tc_kimlik']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">TELEFON</span>
                <span class="info-value"><?= htmlspecialchars($dosya['telefon']) ?></span>
            </div>
        </div>
        
        <!-- ARAÇ BİLGİLERİ (ADK/MDK) -->
        <?php if (in_array($dosya['case_type'], ['ADK', 'MDK'])): ?>
        <div class="info-card">
            <div class="info-card-title"><i class="fas fa-car"></i> ARAÇ BİLGİLERİ</div>
            <div class="info-row">
                <span class="info-label">PLAKA</span>
                <span class="info-value"><?= htmlspecialchars($dosya['plaka'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">KARŞI PLAKA</span>
                <span class="info-value"><?= htmlspecialchars($dosya['karsi_plaka'] ?? '-') ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- SİGORTA BİLGİLERİ -->
        <div class="info-card">
            <div class="info-card-title"><i class="fas fa-building"></i> SİGORTA BİLGİLERİ</div>
            <div class="info-row">
                <span class="info-label">DAVALI ŞİRKET</span>
                <span class="info-value"><?= htmlspecialchars($dosya['sigorta_adi'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">SİGORTA HASAR NO</span>
                <span class="info-value"><?= htmlspecialchars($dosya['sigorta_hasar_no'] ?? '-') ?></span>
            </div>
        </div>
        
        <!-- ATAMA BİLGİLERİ -->
        <div class="info-card">
            <div class="info-card-title"><i class="fas fa-users"></i> ATAMA BİLGİLERİ</div>
            <div class="info-row">
                <span class="info-label">SORUMLU</span>
                <span class="info-value"><?= htmlspecialchars($dosya['sorumlu_adi'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">AVUKAT</span>
                <span class="info-value"><?= htmlspecialchars($dosya['avukat_adi'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">KAYNAK</span>
                <span class="info-value"><?= htmlspecialchars($dosya['source_type'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">YÖNLENDİREN</span>
                <span class="info-value"><?= htmlspecialchars($dosya['yonlendiren_adi'] ?? '-') ?></span>
            </div>
        </div>
    </div>
    
    <!-- SAĞ KOLON -->
    <div>
        <!-- HESAPLAMALAR -->
        <div class="info-card">
            <div class="info-card-title">
                <i class="fas fa-calculator"></i> HESAPLAMALAR
                <?php if ($dosya['case_type'] == 'ADK'): ?>
                <a href="adk_hesaplama.php?case_id=<?= $id ?>" style="margin-left:auto;font-size:11px;color:#10b981">+ YENİ</a>
                <?php elseif ($dosya['case_type'] == 'BEDENI'): ?>
                <a href="bh_hesaplama.php?case_id=<?= $id ?>" style="margin-left:auto;font-size:11px;color:#10b981">+ YENİ</a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($hesaplamalar)): ?>
            <div style="text-align:center;padding:30px;color:#64748b">
                <i class="fas fa-calculator" style="font-size:30px;margin-bottom:10px;display:block;opacity:0.5"></i>
                Henüz hesaplama yapılmamış
            </div>
            <?php else: ?>
            <?php foreach ($hesaplamalar as $h): ?>
            <div class="hesaplama-card">
                <div class="hesaplama-tarih"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></div>
                <div class="hesaplama-tutar">₺<?= number_format($h['toplam_tazminat'] ?? $h['deger_kaybi'] ?? 0, 2, ',', '.') ?></div>
                <?php if ($dosya['case_type'] == 'ADK'): ?>
                <a href="adk_rapor.php?id=<?= $h['id'] ?>" class="hesaplama-link"><i class="fas fa-file-pdf"></i> Rapor Görüntüle</a>
                <?php else: ?>
                <a href="bh_rapor.php?id=<?= $h['id'] ?>" class="hesaplama-link"><i class="fas fa-file-pdf"></i> Rapor Görüntüle</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- AŞAMA GEÇMİŞİ -->
        <div class="info-card">
            <div class="info-card-title"><i class="fas fa-history"></i> AŞAMA GEÇMİŞİ</div>
            
            <?php if (empty($asama_gecmisi)): ?>
            <div style="text-align:center;padding:20px;color:#64748b;font-size:12px">
                Aşama geçmişi bulunmuyor
            </div>
            <?php else: ?>
            <div class="timeline">
                <?php foreach ($asama_gecmisi as $ag): ?>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title"><?= htmlspecialchars($ag['asama_adi'] ?? '-') ?></div>
                        <div class="timeline-meta">
                            <?= date('d.m.Y H:i', strtotime($ag['changed_at'])) ?> - <?= htmlspecialchars($ag['degistiren'] ?? '-') ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>