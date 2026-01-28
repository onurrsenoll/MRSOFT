<?php
/**
 * MR HASAR DANIŞMANLIK - ORTAKLAR
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$success = '';
$ortaklar = [];

try {
    $db = getDB();
    $ortaklar = $db->query("SELECT * FROM caris WHERE cari_type = 'ORTAK' ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'ekle') {
        $name = trim(mb_strtoupper($_POST['name'] ?? '', 'UTF-8'));
        $phone = trim($_POST['phone'] ?? '');
        $iban = trim($_POST['iban'] ?? '');
        
        if (empty($name)) {
            $error = 'ORTAK ADI ZORUNLUDUR';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO caris (name, cari_type, phone, iban) VALUES (?, 'ORTAK', ?, ?)");
                $stmt->execute([$name, $phone, $iban]);
                header("Location: ortaklar.php?success=1");
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
                $stmt = $db->prepare("DELETE FROM caris WHERE id = ? AND cari_type = 'ORTAK'");
                $stmt->execute([$id]);
                header("Location: ortaklar.php?success=1");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-users"></i> İŞ ORTAKLARI</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ ORTAK -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ ORTAK EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">ORTAK ADI <span class="req">*</span></label>
                <input type="text" name="name" class="frm-in" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TELEFON</label>
                <input type="tel" name="phone" class="frm-in">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">IBAN</label>
                <input type="text" name="iban" class="frm-in" maxlength="34">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> EKLE</button>
            </div>
        </div>
    </form>
</div>

<!-- ORTAK LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> ORTAK LİSTESİ (<?= count($ortaklar) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ORTAK ADI</th>
                    <th>TELEFON</th>
                    <th>IBAN</th>
                    <th>BAKİYE</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ortaklar)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text2)">ORTAK BULUNAMADI</td></tr>
                <?php else: foreach ($ortaklar as $o): ?>
                <tr>
                    <td style="font-weight:700"><?= e($o['name']) ?></td>
                    <td><?= e($o['phone'] ?? '-') ?></td>
                    <td style="font-size:10px"><?= e($o['iban'] ?? '-') ?></td>
                    <td style="font-weight:700;color:<?= $o['balance'] >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                        ₺<?= number_format($o['balance'] ?? 0, 2, ',', '.') ?>
                    </td>
                    <td>
                        <div class="icons">
                            <a href="ortak_hesaplari.php?ortak=<?= $o['id'] ?>" class="ic view" title="HESAP HAREKETLERİ"><i class="fas fa-calculator"></i></a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="action" value="sil">
                                <input type="hidden" name="id" value="<?= $o['id'] ?>">
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
