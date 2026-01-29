<?php
/**
 * MR HASAR DANIŞMANLIK - CRM TAKİP
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$success = '';
$kayitlar = [];

try {
    $db = getDB();
    $sorumlular = $db->query("SELECT id, full_name FROM users WHERE is_active = 1")->fetchAll();
    
    // Filtreler
    $where = "1=1";
    $params = [];
    
    if (!empty($_GET['durum'])) {
        $where .= " AND durum = ?";
        $params[] = $_GET['durum'];
    }
    
    if (!empty($_GET['sorumlu'])) {
        $where .= " AND sorumlu_user_id = ?";
        $params[] = $_GET['sorumlu'];
    }
    
    $stmt = $db->prepare("
        SELECT c.*, u.full_name as sorumlu_adi
        FROM crm_records c
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        WHERE $where
        ORDER BY c.sonraki_tarih ASC, c.created_at DESC
    ");
    $stmt->execute($params);
    $kayitlar = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Yeni kayıt ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $ad_soyad = trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8'));
    $telefon = trim($_POST['telefon'] ?? '');
    $tc_kimlik = preg_replace('/[^0-9]/', '', $_POST['tc_kimlik'] ?? '');
    $konu = $_POST['konu'] ?? '';
    $sorumlu_user_id = intval($_POST['sorumlu_user_id'] ?? $_SESSION['user_id']);
    $sonraki_tarih = $_POST['sonraki_tarih'] ?? null;
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($ad_soyad) || empty($telefon)) {
        $error = 'ADI SOYADI VE TELEFON ZORUNLUDUR';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO crm_records (ad_soyad, telefon, tc_kimlik, konu, sorumlu_user_id, sonraki_tarih, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ad_soyad, $telefon, $tc_kimlik ?: null, $konu, $sorumlu_user_id, $sonraki_tarih ?: null, $notes]);
            $success = 'CRM KAYDI EKLENDİ';
            header("Location: crm.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Durum güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'durum') {
    $crm_id = intval($_POST['crm_id'] ?? 0);
    $durum = $_POST['durum'] ?? '';
    
    if ($crm_id && $durum) {
        try {
            $stmt = $db->prepare("UPDATE crm_records SET durum = ? WHERE id = ?");
            $stmt->execute([$durum, $crm_id]);
            header("Location: crm.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Dosyaya dönüştürme
if (isset($_GET['convert']) && intval($_GET['convert']) > 0) {
    $crm_id = intval($_GET['convert']);
    $stmt = $db->prepare("SELECT * FROM crm_records WHERE id = ?");
    $stmt->execute([$crm_id]);
    $crm = $stmt->fetch();
    
    if ($crm) {
        // Dosya ekleme sayfasına yönlendir
        header("Location: dosya_ekle.php?from_crm=$crm_id&tc=" . urlencode($crm['tc_kimlik']) . "&ad=" . urlencode($crm['ad_soyad']) . "&tel=" . urlencode($crm['telefon']));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
/* CRM MODAL - TEMA UYUMLU */
.crm-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(5px);
}
.crm-modal {
    background: var(--bg-card);
    border-radius: 12px;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow: auto;
    border: 2px solid var(--mr-blue);
    box-shadow: var(--shadow-lg);
}
.crm-modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.crm-modal-header h3 {
    margin: 0;
    color: var(--text);
    font-size: 16px;
}
.crm-modal-header h3 i {
    color: var(--mr-blue);
    margin-right: 10px;
}
.crm-modal-close {
    background: none;
    border: none;
    color: var(--text3);
    font-size: 24px;
    cursor: pointer;
    transition: var(--transition);
}
.crm-modal-close:hover {
    color: var(--danger);
}
.crm-modal-body {
    padding: 20px;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}
@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-title">
    <i class="fas fa-headset"></i> CRM
    <button onclick="document.getElementById('crmModal').style.display='flex'" class="btn btn-suc" style="margin-left:auto;font-size:12px">
        <i class="fas fa-plus"></i> YENİ KAYIT
    </button>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if ($success || isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ KAYIT MODAL -->
<div id="crmModal" class="crm-modal-overlay">
    <div class="crm-modal">
        <div class="crm-modal-header">
            <h3><i class="fas fa-plus-circle"></i> YENİ CRM KAYDI</h3>
            <button onclick="document.getElementById('crmModal').style.display='none'" class="crm-modal-close">&times;</button>
        </div>
        <form method="POST" class="crm-modal-body">
            <input type="hidden" name="action" value="ekle">
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">ADI SOYADI <span class="req">*</span></label>
                    <input type="text" name="ad_soyad" class="frm-in" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TELEFON <span class="req">*</span></label>
                    <input type="tel" name="telefon" class="frm-in" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">T.C. KİMLİK</label>
                    <input type="text" name="tc_kimlik" class="frm-in" maxlength="11">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">KONU</label>
                    <select name="konu" class="frm-sel">
                        <option value="">SEÇİNİZ</option>
                        <option value="ADK">ADK - ARAÇ DEĞER KAYBI</option>
                        <option value="BEDENI">BEDENİ HASAR</option>
                        <option value="GENEL">GENEL BİLGİ</option>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">SORUMLU</label>
                    <select name="sorumlu_user_id" class="frm-sel">
                        <?php foreach ($sorumlular as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id'] == $_SESSION['user_id'] ? 'selected' : '' ?>><?= e($s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">SONRAKİ ARAMA TARİHİ</label>
                    <input type="datetime-local" name="sonraki_tarih" class="frm-in">
                </div>
                <div class="frm-grp" style="grid-column:span 2">
                    <label class="frm-lbl">NOTLAR</label>
                    <textarea name="notes" class="frm-ta" rows="2"></textarea>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('crmModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('crmModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<!-- FİLTRE -->
<form method="GET" class="filter">
    <div class="filter-grid">
        <div class="f-group">
            <label class="f-label">DURUM</label>
            <select name="durum" class="f-select">
                <option value="">TÜMÜ</option>
                <option value="TAKIPTE" <?= ($_GET['durum'] ?? '') == 'TAKIPTE' ? 'selected' : '' ?>>TAKİPTE</option>
                <option value="OLUMLU" <?= ($_GET['durum'] ?? '') == 'OLUMLU' ? 'selected' : '' ?>>OLUMLU</option>
                <option value="OLUMSUZ" <?= ($_GET['durum'] ?? '') == 'OLUMSUZ' ? 'selected' : '' ?>>OLUMSUZ</option>
            </select>
        </div>
        <div class="f-group">
            <label class="f-label">SORUMLU</label>
            <select name="sorumlu" class="f-select">
                <option value="">TÜMÜ</option>
                <?php foreach ($sorumlular as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($_GET['sorumlu'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="f-group" style="display:flex;align-items:flex-end;gap:8px">
            <button type="submit" class="btn btn-pri"><i class="fas fa-search"></i> FİLTRELE</button>
            <a href="crm.php" class="btn btn-sec"><i class="fas fa-times"></i></a>
        </div>
    </div>
</form>

<!-- KAYIT LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> CRM KAYITLARI (<?= count($kayitlar) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ADI SOYADI</th>
                    <th>TELEFON</th>
                    <th>KONU</th>
                    <th>DURUM</th>
                    <th>SORUMLU</th>
                    <th>SONRAKİ ARAMA</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kayitlar)): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text2)">KAYIT BULUNAMADI</td></tr>
                <?php else: foreach ($kayitlar as $k): ?>
                <tr>
                    <td style="font-weight:700"><?= e($k['ad_soyad']) ?></td>
                    <td><?= e($k['telefon']) ?></td>
                    <td><?= e($k['konu'] ?? '-') ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="durum">
                            <input type="hidden" name="crm_id" value="<?= $k['id'] ?>">
                            <select name="durum" class="f-select" style="width:auto;padding:4px 8px;font-size:9px" onchange="this.form.submit()">
                                <option value="TAKIPTE" <?= $k['durum'] == 'TAKIPTE' ? 'selected' : '' ?>>TAKİPTE</option>
                                <option value="OLUMLU" <?= $k['durum'] == 'OLUMLU' ? 'selected' : '' ?>>OLUMLU</option>
                                <option value="OLUMSUZ" <?= $k['durum'] == 'OLUMSUZ' ? 'selected' : '' ?>>OLUMSUZ</option>
                            </select>
                        </form>
                    </td>
                    <td><?= e($k['sorumlu_adi'] ?? '-') ?></td>
                    <td style="<?= $k['sonraki_tarih'] && strtotime($k['sonraki_tarih']) < time() ? 'color:var(--red);font-weight:700' : '' ?>">
                        <?= $k['sonraki_tarih'] ? date('d.m.Y H:i', strtotime($k['sonraki_tarih'])) : '-' ?>
                    </td>
                    <td>
                        <div class="icons">
                            <a href="crm.php?convert=<?= $k['id'] ?>" class="ic doc" title="DOSYAYA DÖNÜŞTÜR"><i class="fas fa-folder-plus"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
