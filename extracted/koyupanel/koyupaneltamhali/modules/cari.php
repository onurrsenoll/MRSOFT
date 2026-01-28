<?php
/**
 * MR HASAR DANIŞMANLIK - CARİLER
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$cariler = [];

try {
    $db = getDB();
    
    $where = "1=1";
    if (!empty($_GET['tip'])) {
        $where .= " AND cari_type = '" . $db->quote($_GET['tip']) . "'";
    }
    
    $cariler = $db->query("SELECT * FROM caris WHERE $where ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $name = trim(mb_strtoupper($_POST['name'] ?? '', 'UTF-8'));
    $cari_type = $_POST['cari_type'] ?? 'DIGER';
    $phone = trim($_POST['phone'] ?? '');
    $iban = trim($_POST['iban'] ?? '');
    
    if ($name) {
        try {
            $stmt = $db->prepare("INSERT INTO caris (name, cari_type, phone, iban) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $cari_type, $phone, $iban]);
            header("Location: cari.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-address-book"></i> CARİLER</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ CARİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ CARİ EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">CARİ ADI</label>
                <input type="text" name="name" class="frm-in" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TİP</label>
                <select name="cari_type" class="frm-sel">
                    <option value="DIGER">DİĞER</option>
                    <option value="TEDARIKCI">TEDARİKÇİ</option>
                    <option value="MUSTERI">MÜŞTERİ</option>
                    <option value="ORTAK">ORTAK</option>
                </select>
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

<!-- FİLTRE -->
<form method="GET" class="filter">
    <div class="filter-grid">
        <div class="f-group">
            <label class="f-label">CARİ TİPİ</label>
            <select name="tip" class="f-select" onchange="this.form.submit()">
                <option value="">TÜMÜ</option>
                <option value="TEDARIKCI" <?= ($_GET['tip'] ?? '') == 'TEDARIKCI' ? 'selected' : '' ?>>TEDARİKÇİ</option>
                <option value="MUSTERI" <?= ($_GET['tip'] ?? '') == 'MUSTERI' ? 'selected' : '' ?>>MÜŞTERİ</option>
                <option value="ORTAK" <?= ($_GET['tip'] ?? '') == 'ORTAK' ? 'selected' : '' ?>>ORTAK</option>
                <option value="DIGER" <?= ($_GET['tip'] ?? '') == 'DIGER' ? 'selected' : '' ?>>DİĞER</option>
            </select>
        </div>
    </div>
</form>

<!-- CARİ LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> CARİ LİSTESİ (<?= count($cariler) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>CARİ ADI</th><th>TİP</th><th>TELEFON</th><th>BAKİYE</th></tr></thead>
            <tbody>
                <?php if (empty($cariler)): ?>
                <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text2)">CARİ BULUNAMADI</td></tr>
                <?php else: foreach ($cariler as $c): ?>
                <tr>
                    <td style="font-weight:700"><?= e($c['name']) ?></td>
                    <td><span class="badge"><?= e($c['cari_type']) ?></span></td>
                    <td><?= e($c['phone'] ?? '-') ?></td>
                    <td style="font-weight:700;color:<?= $c['balance'] >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                        ₺<?= number_format($c['balance'] ?? 0, 2, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
