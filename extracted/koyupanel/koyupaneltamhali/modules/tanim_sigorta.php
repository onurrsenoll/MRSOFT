<?php
/**
 * MR HASAR DANIŞMANLIK - SİGORTA ŞİRKETLERİ
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$sigortalar = [];

try {
    $db = getDB();
    $sigortalar = $db->query("SELECT * FROM insurers ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $name = trim(mb_strtoupper($_POST['name'] ?? '', 'UTF-8'));
    if ($name) {
        try {
            $stmt = $db->prepare("INSERT INTO insurers (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: tanim_sigorta.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        try {
            $stmt = $db->prepare("UPDATE insurers SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: tanim_sigorta.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-building"></i> SİGORTA ŞİRKETLERİ</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ EKLE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ SİGORTA ŞİRKETİ EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">ŞİRKET ADI</label>
                <input type="text" name="name" class="frm-in" required>
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> EKLE</button>
            </div>
        </div>
    </form>
</div>

<!-- LİSTE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> SİGORTA ŞİRKETLERİ (<?= count($sigortalar) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>ŞİRKET ADI</th><th>DURUM</th><th>İŞLEM</th></tr></thead>
            <tbody>
                <?php foreach ($sigortalar as $s): ?>
                <tr>
                    <td style="font-weight:700"><?= e($s['name']) ?></td>
                    <td><span class="badge <?= $s['is_active'] ? 'aktif' : 'kapali' ?>"><?= $s['is_active'] ? 'AKTİF' : 'PASİF' ?></span></td>
                    <td>
                        <?php if ($s['is_active']): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Pasif yapmak istediğinize emin misiniz?')">
                            <input type="hidden" name="action" value="sil">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-sec" style="padding:5px 10px"><i class="fas fa-ban"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
