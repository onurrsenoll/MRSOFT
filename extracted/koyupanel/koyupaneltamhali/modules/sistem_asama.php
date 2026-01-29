<?php
/**
 * MR HASAR DANIŞMANLIK - AŞAMA YÖNETİMİ
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$asamalar = [];

try {
    $db = getDB();
    $asamalar = $db->query("SELECT * FROM stage_definitions ORDER BY sort_order")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $code = trim(strtolower($_POST['code'] ?? ''));
    $name = trim(mb_strtoupper($_POST['name'] ?? '', 'UTF-8'));
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if ($code && $name) {
        try {
            $stmt = $db->prepare("INSERT INTO stage_definitions (code, name, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$code, $name, $sort_order]);
            header("Location: sistem_asama.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-tasks"></i> AŞAMA YÖNETİMİ</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ AŞAMA -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ AŞAMA EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">KOD</label>
                <input type="text" name="code" class="frm-in" required placeholder="ornek_asama">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">AŞAMA ADI</label>
                <input type="text" name="name" class="frm-in" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">SIRA</label>
                <input type="number" name="sort_order" class="frm-in" value="0">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> EKLE</button>
            </div>
        </div>
    </form>
</div>

<!-- AŞAMA LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> AŞAMA LİSTESİ</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>SIRA</th><th>KOD</th><th>AŞAMA ADI</th><th>KAPSAM</th><th>DURUM</th></tr></thead>
            <tbody>
                <?php foreach ($asamalar as $a): ?>
                <tr>
                    <td style="font-weight:700;color:var(--blue)"><?= $a['sort_order'] ?></td>
                    <td style="font-size:10px;color:var(--text2)"><?= e($a['code']) ?></td>
                    <td style="font-weight:700"><?= e($a['name']) ?></td>
                    <td><span class="badge"><?= e($a['case_type_scope']) ?></span></td>
                    <td><span class="badge <?= $a['is_active'] ? 'aktif' : 'kapali' ?>"><?= $a['is_active'] ? 'AKTİF' : 'PASİF' ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
