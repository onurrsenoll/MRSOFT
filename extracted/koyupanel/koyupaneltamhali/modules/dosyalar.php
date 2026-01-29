<?php
/**
 * MR HASAR DANIŞMANLIK - DOSYA LİSTELEME
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';
startSecureSession();

$dosyalar = [];
$totalRecords = 0;
$totalPages = 0;
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$error = '';
$isAdmin = function_exists('hasRole') ? hasRole(['ADMIN']) : true;

try {
    $db = getDB();
    
    // DOSYA SİLME İŞLEMİ
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil' && $isAdmin) {
        $dosya_id = intval($_POST['dosya_id'] ?? 0);
        $admin_sifre = $_POST['admin_sifre'] ?? '';
        
        if ($dosya_id && $admin_sifre) {
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($admin_sifre, $admin['password_hash'])) {
                $stmt = $db->prepare("UPDATE cases SET is_deleted = 1 WHERE id = ?");
                $stmt->execute([$dosya_id]);
                header("Location: dosyalar.php?success=silindi");
                exit;
            } else {
                $error = 'ADMİN ŞİFRESİ HATALI!';
            }
        }
    }
    
    // GERİ YÜKLEME İŞLEMİ
    if (isset($_GET['geri_yukle']) && $isAdmin) {
        $dosya_id = intval($_GET['geri_yukle']);
        $stmt = $db->prepare("UPDATE cases SET is_deleted = 0 WHERE id = ?");
        $stmt->execute([$dosya_id]);
        header("Location: dosyalar.php?show=silinen&success=geri");
        exit;
    }
    
    // Filtreler
    $where = "1=1";
    $params = [];
    
    $showDeleted = isset($_GET['show']) && $_GET['show'] == 'silinen' && $isAdmin;
    if ($showDeleted) {
        $where .= " AND c.is_deleted = 1";
    } else {
        $where .= " AND (c.is_deleted = 0 OR c.is_deleted IS NULL)";
    }
    
    if (!empty($_GET['dosya_no'])) {
        $where .= " AND c.dosya_no LIKE ?";
        $params[] = '%' . $_GET['dosya_no'] . '%';
    }
    
    if (!empty($_GET['ad_soyad'])) {
        $where .= " AND c.ad_soyad LIKE ?";
        $params[] = '%' . $_GET['ad_soyad'] . '%';
    }
    
    if (!empty($_GET['tc_kimlik'])) {
        $where .= " AND c.tc_kimlik LIKE ?";
        $params[] = '%' . $_GET['tc_kimlik'] . '%';
    }
    
    if (!empty($_GET['case_type'])) {
        $where .= " AND c.case_type = ?";
        $params[] = $_GET['case_type'];
    }
    
    if (!empty($_GET['status'])) {
        $where .= " AND c.status = ?";
        $params[] = $_GET['status'];
    }
    
    // Toplam kayıt sayısı
    $countStmt = $db->prepare("SELECT COUNT(*) FROM cases c WHERE $where");
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $perPage);
    $offset = ($page - 1) * $perPage;
    
    // Dosyaları getir
    $sql = "
        SELECT c.*,
               i.firma_adi as sigorta_adi,
               s.asama_adi as asama_adi,
               u.full_name as sorumlu_adi,
               a.full_name as avukat_adi
        FROM cases c
        LEFT JOIN sigorta_sirketleri i ON c.davali_sirket_id = i.id
        LEFT JOIN asama_durumlari s ON c.current_stage_id = s.id
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        LEFT JOIN users a ON c.assigned_lawyer_user_id = a.id
        WHERE $where
        ORDER BY c.created_at DESC
        LIMIT $perPage OFFSET $offset
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $dosyalar = $stmt->fetchAll();
    
    // Silinmiş dosya sayısı (admin için)
    if ($isAdmin) {
        $deletedCount = $db->query("SELECT COUNT(*) FROM cases WHERE is_deleted = 1")->fetchColumn();
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <i class="fas fa-<?= $showDeleted ? 'trash' : 'list' ?>"></i> 
    <?= $showDeleted ? 'SİLİNMİŞ DOSYALAR' : 'DOSYA LİSTELEME' ?>
</div>

<?php if ($error): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i> HATA: <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> 
    <?php if ($_GET['success'] == 'silindi'): ?>
        DOSYA BAŞARIYLA SİLİNDİ!
    <?php elseif ($_GET['success'] == 'geri'): ?>
        DOSYA GERİ YÜKLENDİ!
    <?php else: ?>
        İŞLEM BAŞARILI!
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- FILTRE FORMU -->
<form method="GET" class="filter">
    <div class="filter-grid">
        <div class="f-grp">
            <label class="f-lbl">DOSYA NO</label>
            <input type="text" name="dosya_no" class="f-in" value="<?= htmlspecialchars($_GET['dosya_no'] ?? '') ?>" placeholder="DOSYA NO">
        </div>
        <div class="f-grp">
            <label class="f-lbl">TC KİMLİK</label>
            <input type="text" name="tc_kimlik" class="f-in" value="<?= htmlspecialchars($_GET['tc_kimlik'] ?? '') ?>" placeholder="TC KİMLİK">
        </div>
        <div class="f-grp">
            <label class="f-lbl">ADI SOYADI</label>
            <input type="text" name="ad_soyad" class="f-in" value="<?= htmlspecialchars($_GET['ad_soyad'] ?? '') ?>" placeholder="ADI SOYADI">
        </div>
        <div class="f-grp">
            <label class="f-lbl">DOSYA TÜRÜ</label>
            <select name="case_type" class="f-sel">
                <option value="">TÜMÜ</option>
                <option value="ADK" <?= ($_GET['case_type'] ?? '') == 'ADK' ? 'selected' : '' ?>>ADK</option>
                <option value="BEDENI" <?= ($_GET['case_type'] ?? '') == 'BEDENI' ? 'selected' : '' ?>>BEDENİ</option>
                <option value="MDK" <?= ($_GET['case_type'] ?? '') == 'MDK' ? 'selected' : '' ?>>MDK</option>
                <option value="DIGER" <?= ($_GET['case_type'] ?? '') == 'DIGER' ? 'selected' : '' ?>>DİĞER</option>
            </select>
        </div>
        <div class="f-grp">
            <label class="f-lbl">DURUM</label>
            <select name="status" class="f-sel">
                <option value="">TÜMÜ</option>
                <option value="AKTIF" <?= ($_GET['status'] ?? '') == 'AKTIF' ? 'selected' : '' ?>>AKTİF</option>
                <option value="KAPALI" <?= ($_GET['status'] ?? '') == 'KAPALI' ? 'selected' : '' ?>>KAPALI</option>
            </select>
        </div>
        <div class="f-grp" style="display:flex;align-items:flex-end;gap:10px">
            <button type="submit" class="btn btn-pri" style="flex:1">
                <i class="fas fa-search"></i> ARA
            </button>
            <a href="dosyalar.php" class="btn btn-sec">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>
</form>

<!-- DOSYA LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title">
            <span style="color:var(--blue);font-size:16px;font-weight:800"><?= number_format($totalRecords) ?></span> KAYIT BULUNDU
        </div>
        <div style="display:flex;gap:10px">
            <?php if ($isAdmin): ?>
                <?php if ($showDeleted): ?>
                    <a href="dosyalar.php" class="btn btn-sec">
                        <i class="fas fa-list"></i> TÜM DOSYALAR
                    </a>
                <?php else: ?>
                    <a href="dosyalar.php?show=silinen" class="btn btn-dan">
                        <i class="fas fa-trash"></i> SİLİNENLER (<?= $deletedCount ?? 0 ?>)
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="dosya_ekle.php" class="btn btn-suc">
                <i class="fas fa-plus"></i> YENİ DOSYA EKLE
            </a>
        </div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>DOSYA NO</th>
                    <th>ADI SOYADI</th>
                    <th>DOSYA TÜRÜ</th>
                    <th>DAVALI ŞİRKET</th>
                    <th>DOSYA AŞAMASI</th>
                    <th>AVUKAT</th>
                    <th>AÇILIŞ TARİHİ</th>
                    <th>DURUM</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dosyalar)): ?>
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--text2)">
                        <i class="fas fa-search" style="font-size:40px;margin-bottom:15px;display:block;opacity:0.5"></i>
                        KAYIT BULUNAMADI
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($dosyalar as $dosya): ?>
                <tr class="clickable-row" onclick="window.location='dosya_detay.php?id=<?= $dosya['id'] ?>'" <?= $showDeleted ? 'style="opacity:0.7"' : '' ?>>
                    <td><a href="dosya_detay.php?id=<?= $dosya['id'] ?>" class="dosya-no" onclick="event.stopPropagation()"><?= htmlspecialchars($dosya['dosya_no']) ?></a></td>
                    <td><a href="dosya_detay.php?id=<?= $dosya['id'] ?>" class="name-link" onclick="event.stopPropagation()"><?= htmlspecialchars($dosya['ad_soyad']) ?></a></td>
                    <td>
                        <span class="badge <?= strtolower($dosya['case_type'] ?? 'adk') ?>">
                            <?= htmlspecialchars($dosya['case_type'] ?? '-') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($dosya['sigorta_adi'] ?? '-') ?></td>
                    <td>
                        <span class="badge aktif"><?= htmlspecialchars($dosya['asama_adi'] ?? '-') ?></span>
                    </td>
                    <td><?= htmlspecialchars($dosya['avukat_adi'] ?? '-') ?></td>
                    <td><?= $dosya['acilis_tarihi'] ? date('d.m.Y', strtotime($dosya['acilis_tarihi'])) : '-' ?></td>
                    <td>
                        <span class="badge <?= strtolower($dosya['status'] ?? 'aktif') ?>">
                            <?= htmlspecialchars($dosya['status'] ?? 'AKTİF') ?>
                        </span>
                    </td>
                    <td>
                        <div class="icons">
                            <?php if ($showDeleted): ?>
                                <a href="dosyalar.php?show=silinen&geri_yukle=<?= $dosya['id'] ?>" class="ic doc" title="GERİ YÜKLE" onclick="return confirm('Dosyayı geri yüklemek istiyor musunuz?')">
                                    <i class="fas fa-undo"></i>
                                </a>
                            <?php else: ?>
                                <a href="dosya_detay.php?id=<?= $dosya['id'] ?>" class="ic view" title="DETAY">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="dosya_duzenle.php?id=<?= $dosya['id'] ?>" class="ic edit" title="DÜZENLE">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="dosya_evrak.php?id=<?= $dosya['id'] ?>" class="ic doc" title="EVRAKLAR">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                                <?php if ($isAdmin): ?>
                                <button type="button" class="ic del" onclick="openSilModal(<?= $dosya['id'] ?>, '<?= htmlspecialchars($dosya['dosya_no']) ?>', '<?= htmlspecialchars($dosya['ad_soyad']) ?>')" title="SİL">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>" class="<?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($isAdmin): ?>
<!-- SİL MODAL -->
<div id="silModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center">
    <div style="background:#1e293b;border-radius:12px;padding:30px;width:450px;max-width:90%;border:2px solid #ef4444">
        <h3 style="margin-bottom:20px;color:#ef4444;display:flex;align-items:center;gap:10px">
            <i class="fas fa-exclamation-triangle"></i> DOSYA SİL
        </h3>
        <p style="margin-bottom:10px;color:#94a3b8">Aşağıdaki dosyayı silmek üzeresiniz:</p>
        <div style="background:#0f172a;padding:15px;border-radius:8px;margin-bottom:20px">
            <div style="color:#3b82f6;font-weight:700;font-size:16px" id="sil_dosya_no"></div>
            <div style="color:#f1f5f9;margin-top:5px" id="sil_ad_soyad"></div>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="sil">
            <input type="hidden" name="dosya_id" id="sil_dosya_id">
            
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">ADMİN ŞİFRENİZ <span class="req">*</span></label>
                <input type="password" name="admin_sifre" class="frm-in" required placeholder="Onaylamak için şifrenizi girin">
            </div>
            
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-sec" onclick="closeSilModal()">İPTAL</button>
                <button type="submit" class="btn btn-dan"><i class="fas fa-trash"></i> DOSYAYI SİL</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSilModal(id, dosyaNo, adSoyad) {
    document.getElementById('sil_dosya_id').value = id;
    document.getElementById('sil_dosya_no').textContent = dosyaNo;
    document.getElementById('sil_ad_soyad').textContent = adSoyad;
    document.getElementById('silModal').style.display = 'flex';
}

function closeSilModal() {
    document.getElementById('silModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSilModal();
});

document.getElementById('silModal').addEventListener('click', function(e) {
    if (e.target === this) closeSilModal();
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>