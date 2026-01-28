<?php
/**
 * MR HASAR DANIŞMANLIK - MAAŞ / PRİM
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$odemeler = [];
$personeller = [];

try {
    $db = getDB();

    // Personel listesi
    $personeller = $db->query("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name")->fetchAll();

    // Filtre
    $where = "1=1";
    $params = [];

    if (!empty($_GET['personel'])) {
        $where .= " AND s.user_id = ?";
        $params[] = $_GET['personel'];
    }

    if (!empty($_GET['tip'])) {
        $where .= " AND s.odeme_tipi = ?";
        $params[] = $_GET['tip'];
    }

    if (!empty($_GET['ay'])) {
        $where .= " AND DATE_FORMAT(s.odeme_tarihi, '%Y-%m') = ?";
        $params[] = $_GET['ay'];
    }

    $stmt = $db->prepare("
        SELECT s.*, u.full_name as personel_adi
        FROM salary_payments s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE $where
        ORDER BY s.odeme_tarihi DESC, s.id DESC
        LIMIT 100
    ");
    $stmt->execute($params);
    $odemeler = $stmt->fetchAll();

    // Özet
    $toplamMaas = $db->query("SELECT COALESCE(SUM(tutar), 0) FROM salary_payments WHERE odeme_tipi = 'MAAS'")->fetchColumn();
    $toplamPrim = $db->query("SELECT COALESCE(SUM(tutar), 0) FROM salary_payments WHERE odeme_tipi = 'PRIM'")->fetchColumn();
    $toplamAvans = $db->query("SELECT COALESCE(SUM(tutar), 0) FROM salary_payments WHERE odeme_tipi = 'AVANS'")->fetchColumn();

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ödeme ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $odeme_tipi = $_POST['odeme_tipi'] ?? '';
    $tutar = floatval($_POST['tutar'] ?? 0);
    $odeme_tarihi = $_POST['odeme_tarihi'] ?? date('Y-m-d');
    $aciklama = trim($_POST['aciklama'] ?? '');

    if ($user_id && $odeme_tipi && $tutar > 0) {
        try {
            $stmt = $db->prepare("INSERT INTO salary_payments (user_id, odeme_tipi, tutar, odeme_tarihi, aciklama, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $odeme_tipi, $tutar, $odeme_tarihi, $aciklama, $_SESSION['user_id']]);
            header("Location: maas_prim.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'PERSONEL, TİP VE TUTAR ZORUNLUDUR';
    }
}

// Silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        try {
            $stmt = $db->prepare("DELETE FROM salary_payments WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: maas_prim.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <i class="fas fa-money-check-alt"></i> MAAŞ / PRİM
    <button onclick="document.getElementById('odemeModal').style.display='flex'" class="btn btn-suc" style="margin-left:auto;font-size:12px">
        <i class="fas fa-plus"></i> YENİ ÖDEME
    </button>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- ÖZET KARTLARI -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="card-title">TOPLAM MAAŞ</div>
                <div class="card-val">₺<?= number_format($toplamMaas ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-gift"></i></div>
            <div>
                <div class="card-title">TOPLAM PRİM</div>
                <div class="card-val">₺<?= number_format($toplamPrim ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon purple"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <div class="card-title">TOPLAM AVANS</div>
                <div class="card-val">₺<?= number_format($toplamAvans ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- YENİ ÖDEME MODAL -->
<div id="odemeModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:500px;border:2px solid #3b82f6">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-plus-circle"></i> YENİ ÖDEME</h3>
            <button onclick="document.getElementById('odemeModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="ekle">
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">PERSONEL <span class="req">*</span></label>
                    <select name="user_id" class="frm-sel" required>
                        <option value="">SEÇİNİZ</option>
                        <?php foreach ($personeller as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">ÖDEME TİPİ <span class="req">*</span></label>
                    <select name="odeme_tipi" class="frm-sel" required>
                        <option value="">SEÇİNİZ</option>
                        <option value="MAAS">MAAŞ</option>
                        <option value="PRIM">PRİM</option>
                        <option value="AVANS">AVANS</option>
                        <option value="IKRAMIYE">İKRAMİYE</option>
                        <option value="DIGER">DİĞER</option>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TUTAR <span class="req">*</span></label>
                    <input type="number" name="tutar" class="frm-in" step="0.01" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TARİH</label>
                    <input type="date" name="odeme_tarihi" class="frm-in" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="frm-grp" style="grid-column:span 2">
                    <label class="frm-lbl">AÇIKLAMA</label>
                    <input type="text" name="aciklama" class="frm-in">
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('odemeModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('odemeModal').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
</script>

<!-- FİLTRE -->
<form method="GET" class="filter">
    <div class="filter-grid">
        <div class="f-group">
            <label class="f-label">PERSONEL</label>
            <select name="personel" class="f-select">
                <option value="">TÜMÜ</option>
                <?php foreach ($personeller as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($_GET['personel'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="f-group">
            <label class="f-label">TİP</label>
            <select name="tip" class="f-select">
                <option value="">TÜMÜ</option>
                <option value="MAAS" <?= ($_GET['tip'] ?? '') == 'MAAS' ? 'selected' : '' ?>>MAAŞ</option>
                <option value="PRIM" <?= ($_GET['tip'] ?? '') == 'PRIM' ? 'selected' : '' ?>>PRİM</option>
                <option value="AVANS" <?= ($_GET['tip'] ?? '') == 'AVANS' ? 'selected' : '' ?>>AVANS</option>
            </select>
        </div>
        <div class="f-group">
            <label class="f-label">AY</label>
            <input type="month" name="ay" class="f-select" value="<?= $_GET['ay'] ?? '' ?>">
        </div>
        <div class="f-group" style="display:flex;align-items:flex-end;gap:8px">
            <button type="submit" class="btn btn-pri"><i class="fas fa-search"></i> FİLTRELE</button>
            <a href="maas_prim.php" class="btn btn-sec"><i class="fas fa-times"></i></a>
        </div>
    </div>
</form>

<!-- ÖDEME LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> ÖDEME LİSTESİ (<?= count($odemeler) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>TARİH</th>
                    <th>PERSONEL</th>
                    <th>TİP</th>
                    <th>AÇIKLAMA</th>
                    <th>TUTAR</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($odemeler)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text2)">ÖDEME BULUNAMADI</td></tr>
                <?php else: foreach ($odemeler as $o): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($o['odeme_tarihi'])) ?></td>
                    <td style="font-weight:700"><?= e($o['personel_adi'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= $o['odeme_tipi'] == 'MAAS' ? '' : ($o['odeme_tipi'] == 'PRIM' ? 'aktif' : 'kapali') ?>">
                            <?= e($o['odeme_tipi']) ?>
                        </span>
                    </td>
                    <td><?= e($o['aciklama'] ?? '-') ?></td>
                    <td style="font-weight:700;color:var(--red)">₺<?= number_format($o['tutar'], 2, ',', '.') ?></td>
                    <td>
                        <div class="icons">
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
