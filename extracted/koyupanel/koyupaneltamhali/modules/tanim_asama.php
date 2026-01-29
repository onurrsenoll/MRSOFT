<?php
/**
 * MR HASAR DANIŞMANLIK - AŞAMA DURUMLARI
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$success = '';
$asamalar = [];

try {
    $db = getDB();
    $asamalar = $db->query("SELECT * FROM stage_definitions ORDER BY sort_order, name")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'ekle') {
        $name = trim(mb_strtoupper($_POST['name'] ?? '', 'UTF-8'));
        $color = $_POST['color'] ?? '#3b82f6';
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            $error = 'AŞAMA ADI ZORUNLUDUR';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO stage_definitions (name, color, sort_order) VALUES (?, ?, ?)");
                $stmt->execute([$name, $color, $sort_order]);
                header("Location: tanim_asama.php?success=1");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    if ($_POST['action'] == 'sil') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            try {
                $stmt = $db->prepare("DELETE FROM stage_definitions WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: tanim_asama.php?success=1");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    if ($_POST['action'] == 'guncelle') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim(mb_strtoupper($_POST['name'] ?? '', 'UTF-8'));
        $color = $_POST['color'] ?? '#3b82f6';
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if ($id && !empty($name)) {
            try {
                $stmt = $db->prepare("UPDATE stage_definitions SET name = ?, color = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$name, $color, $sort_order, $id]);
                header("Location: tanim_asama.php?success=1");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <i class="fas fa-tasks"></i> AŞAMA DURUMLARI
    <button onclick="document.getElementById('ekleModal').style.display='flex'" class="btn btn-suc" style="margin-left:auto;font-size:12px">
        <i class="fas fa-plus"></i> YENİ AŞAMA
    </button>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- EKLEME MODAL -->
<div id="ekleModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:400px;border:2px solid #3b82f6">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-plus-circle"></i> YENİ AŞAMA</h3>
            <button onclick="document.getElementById('ekleModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="ekle">
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">AŞAMA ADI <span class="req">*</span></label>
                <input type="text" name="name" class="frm-in" required>
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">RENK</label>
                <input type="color" name="color" class="frm-in" value="#3b82f6" style="height:45px;padding:5px">
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">SIRA NO</label>
                <input type="number" name="sort_order" class="frm-in" value="0" min="0">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('ekleModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>

<!-- LİSTE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> AŞAMA DURUMLARI (<?= count($asamalar) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>SIRA</th>
                    <th>AŞAMA ADI</th>
                    <th>RENK</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($asamalar)): ?>
                <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text2)">KAYIT BULUNAMADI</td></tr>
                <?php else: foreach ($asamalar as $a): ?>
                <tr>
                    <td><?= $a['sort_order'] ?? 0 ?></td>
                    <td style="font-weight:700">
                        <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?= e($a['color'] ?? '#3b82f6') ?>;margin-right:8px"></span>
                        <?= e($a['name']) ?>
                    </td>
                    <td>
                        <span style="display:inline-block;width:30px;height:20px;border-radius:4px;background:<?= e($a['color'] ?? '#3b82f6') ?>"></span>
                    </td>
                    <td>
                        <div class="icons">
                            <button onclick="duzenle(<?= $a['id'] ?>, '<?= e($a['name']) ?>', '<?= e($a['color'] ?? '#3b82f6') ?>', <?= $a['sort_order'] ?? 0 ?>)" class="ic edit" style="border:none;cursor:pointer"><i class="fas fa-edit"></i></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="action" value="sil">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="ic del" style="border:none;cursor:pointer"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DÜZENLEME MODAL -->
<div id="duzenleModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:400px;border:2px solid #f59e0b">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-edit"></i> DÜZENLE</h3>
            <button onclick="document.getElementById('duzenleModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="guncelle">
            <input type="hidden" name="id" id="edit_id">
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">AŞAMA ADI <span class="req">*</span></label>
                <input type="text" name="name" id="edit_name" class="frm-in" required>
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">RENK</label>
                <input type="color" name="color" id="edit_color" class="frm-in" style="height:45px;padding:5px">
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">SIRA NO</label>
                <input type="number" name="sort_order" id="edit_sort_order" class="frm-in" min="0">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('duzenleModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-war"><i class="fas fa-save"></i> GÜNCELLE</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('ekleModal').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
document.getElementById('duzenleModal').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });

function duzenle(id, name, color, sort_order) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_color').value = color;
    document.getElementById('edit_sort_order').value = sort_order;
    document.getElementById('duzenleModal').style.display = 'flex';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
