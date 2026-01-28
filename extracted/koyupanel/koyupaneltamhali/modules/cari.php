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

    $where = "is_active = 1";
    if (!empty($_GET['tip'])) {
        $where .= " AND cari_tipi = '" . $_GET['tip'] . "'";
    }

    $cariler = $db->query("
        SELECT c.*,
            COALESCE((SELECT SUM(CASE WHEN hareket_tipi = 'ALACAK' THEN tutar ELSE -tutar END) FROM cari_hareketler WHERE cari_id = c.id), 0) as bakiye
        FROM caris c
        WHERE $where
        ORDER BY c.ad_soyad
    ")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $ad_soyad = trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8'));
    $cari_tipi = $_POST['cari_tipi'] ?? 'DIGER';
    $telefon = trim($_POST['telefon'] ?? '');
    $iban = trim($_POST['iban'] ?? '');

    if ($ad_soyad) {
        try {
            $stmt = $db->prepare("INSERT INTO caris (ad_soyad, cari_tipi, telefon, iban) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ad_soyad, $cari_tipi, $telefon, $iban]);
            header("Location: cari.php?success=1");
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
            $stmt = $db->prepare("UPDATE caris SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: cari.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <i class="fas fa-address-book"></i> CARİLER
    <button onclick="document.getElementById('cariModal').style.display='flex'" class="btn btn-suc" style="margin-left:auto;font-size:12px">
        <i class="fas fa-plus"></i> YENİ CARİ
    </button>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ CARİ MODAL -->
<div id="cariModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:500px;border:2px solid #3b82f6">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-user-plus"></i> YENİ CARİ EKLE</h3>
            <button onclick="document.getElementById('cariModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="ekle">
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">CARİ ADI <span class="req">*</span></label>
                    <input type="text" name="ad_soyad" class="frm-in" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TİP</label>
                    <select name="cari_tipi" class="frm-sel">
                        <option value="DIGER">DİĞER</option>
                        <option value="TEDARIKCI">TEDARİKÇİ</option>
                        <option value="MUSTERI">MÜŞTERİ</option>
                        <option value="ORTAK">ORTAK</option>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TELEFON</label>
                    <input type="tel" name="telefon" class="frm-in">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">IBAN</label>
                    <input type="text" name="iban" class="frm-in" maxlength="34">
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('cariModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('cariModal').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
</script>

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
            <thead>
                <tr>
                    <th>CARİ ADI</th>
                    <th>TİP</th>
                    <th>TELEFON</th>
                    <th>BAKİYE</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cariler)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text2)">CARİ BULUNAMADI</td></tr>
                <?php else: foreach ($cariler as $c): ?>
                <tr>
                    <td style="font-weight:700"><?= e($c['ad_soyad']) ?></td>
                    <td><span class="badge"><?= e($c['cari_tipi']) ?></span></td>
                    <td><?= e($c['telefon'] ?? '-') ?></td>
                    <td style="font-weight:700;color:<?= ($c['bakiye'] ?? 0) >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                        ₺<?= number_format($c['bakiye'] ?? 0, 2, ',', '.') ?>
                    </td>
                    <td>
                        <div class="icons">
                            <form method="POST" style="display:inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="action" value="sil">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
