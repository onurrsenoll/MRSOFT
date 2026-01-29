<?php
/**
 * MR HASAR DANIŞMANLIK - GELİR / GİDER
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$hareketler = [];
$toplamGelir = 0;
$toplamGider = 0;

try {
    $db = getDB();

    // income_expense tablosunu kullan
    $where = "1=1";
    $params = [];

    if (!empty($_GET['tip'])) {
        $where .= " AND islem_tipi = ?";
        $params[] = $_GET['tip'];
    }

    if (!empty($_GET['tarih_bas'])) {
        $where .= " AND islem_tarihi >= ?";
        $params[] = $_GET['tarih_bas'];
    }

    if (!empty($_GET['tarih_bit'])) {
        $where .= " AND islem_tarihi <= ?";
        $params[] = $_GET['tarih_bit'];
    }

    $stmt = $db->prepare("
        SELECT *
        FROM income_expense
        WHERE $where
        ORDER BY islem_tarihi DESC, id DESC
        LIMIT 100
    ");
    $stmt->execute($params);
    $hareketler = $stmt->fetchAll();

    // Özet
    $toplamGelir = $db->query("SELECT COALESCE(SUM(tutar), 0) FROM income_expense WHERE islem_tipi = 'GELIR'")->fetchColumn();
    $toplamGider = $db->query("SELECT COALESCE(SUM(tutar), 0) FROM income_expense WHERE islem_tipi = 'GIDER'")->fetchColumn();

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Hareket ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $islem_tipi = $_POST['islem_tipi'] ?? '';
    $tutar = floatval($_POST['tutar'] ?? 0);
    $islem_tarihi = $_POST['islem_tarihi'] ?? date('Y-m-d');
    $kategori = trim($_POST['kategori'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');

    if ($islem_tipi && $tutar > 0) {
        try {
            $stmt = $db->prepare("INSERT INTO income_expense (islem_tipi, tutar, islem_tarihi, kategori, aciklama, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$islem_tipi, $tutar, $islem_tarihi, $kategori, $aciklama, $_SESSION['user_id']]);
            header("Location: gelir_gider.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'TİP VE TUTAR ZORUNLUDUR';
    }
}

// Silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        try {
            $stmt = $db->prepare("DELETE FROM income_expense WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: gelir_gider.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title">
    <i class="fas fa-exchange-alt"></i> GELİR / GİDER
    <button onclick="document.getElementById('hareketModal').style.display='flex'" class="btn btn-suc" style="margin-left:auto;font-size:12px">
        <i class="fas fa-plus"></i> YENİ HAREKET
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
            <div class="card-icon green"><i class="fas fa-arrow-down"></i></div>
            <div>
                <div class="card-title">TOPLAM GELİR</div>
                <div class="card-val">₺<?= number_format($toplamGelir ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon red"><i class="fas fa-arrow-up"></i></div>
            <div>
                <div class="card-title">TOPLAM GİDER</div>
                <div class="card-val">₺<?= number_format($toplamGider ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon <?= ($toplamGelir - $toplamGider) >= 0 ? 'blue' : 'red' ?>"><i class="fas fa-balance-scale"></i></div>
            <div>
                <div class="card-title">NET DURUM</div>
                <div class="card-val">₺<?= number_format(($toplamGelir ?? 0) - ($toplamGider ?? 0), 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- YENİ HAREKET MODAL -->
<div id="hareketModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:500px;border:2px solid #3b82f6">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-plus-circle"></i> YENİ HAREKET</h3>
            <button onclick="document.getElementById('hareketModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="ekle">
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">İŞLEM TİPİ <span class="req">*</span></label>
                    <select name="islem_tipi" class="frm-sel" required>
                        <option value="">SEÇİNİZ</option>
                        <option value="GELIR">GELİR</option>
                        <option value="GIDER">GİDER</option>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TUTAR <span class="req">*</span></label>
                    <input type="number" name="tutar" class="frm-in" step="0.01" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TARİH</label>
                    <input type="date" name="islem_tarihi" class="frm-in" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">KATEGORİ</label>
                    <select name="kategori" class="frm-sel">
                        <option value="">SEÇİNİZ</option>
                        <option value="DOSYA GELİRİ">DOSYA GELİRİ</option>
                        <option value="TAHSİLAT">TAHSİLAT</option>
                        <option value="MAAŞ">MAAŞ</option>
                        <option value="KİRA">KİRA</option>
                        <option value="FATURA">FATURA</option>
                        <option value="MASRAF">MASRAF</option>
                        <option value="DİĞER">DİĞER</option>
                    </select>
                </div>
                <div class="frm-grp" style="grid-column:span 2">
                    <label class="frm-lbl">AÇIKLAMA</label>
                    <input type="text" name="aciklama" class="frm-in">
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('hareketModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('hareketModal').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
</script>

<!-- FİLTRE -->
<form method="GET" class="filter">
    <div class="filter-grid">
        <div class="f-group">
            <label class="f-label">TİP</label>
            <select name="tip" class="f-select">
                <option value="">TÜMÜ</option>
                <option value="GELIR" <?= ($_GET['tip'] ?? '') == 'GELIR' ? 'selected' : '' ?>>GELİR</option>
                <option value="GIDER" <?= ($_GET['tip'] ?? '') == 'GIDER' ? 'selected' : '' ?>>GİDER</option>
            </select>
        </div>
        <div class="f-group">
            <label class="f-label">BAŞLANGIÇ</label>
            <input type="date" name="tarih_bas" class="f-select" value="<?= $_GET['tarih_bas'] ?? '' ?>">
        </div>
        <div class="f-group">
            <label class="f-label">BİTİŞ</label>
            <input type="date" name="tarih_bit" class="f-select" value="<?= $_GET['tarih_bit'] ?? '' ?>">
        </div>
        <div class="f-group" style="display:flex;align-items:flex-end;gap:8px">
            <button type="submit" class="btn btn-pri"><i class="fas fa-search"></i> FİLTRELE</button>
            <a href="gelir_gider.php" class="btn btn-sec"><i class="fas fa-times"></i></a>
        </div>
    </div>
</form>

<!-- HAREKET LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-history"></i> SON HAREKETLER (<?= count($hareketler) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>TARİH</th>
                    <th>TİP</th>
                    <th>KATEGORİ</th>
                    <th>AÇIKLAMA</th>
                    <th>TUTAR</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($hareketler)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text2)">HAREKET BULUNAMADI</td></tr>
                <?php else: foreach ($hareketler as $h): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($h['islem_tarihi'])) ?></td>
                    <td>
                        <span class="badge <?= $h['islem_tipi'] == 'GELIR' ? 'aktif' : 'kapali' ?>"><?= e($h['islem_tipi']) ?></span>
                    </td>
                    <td><?= e($h['kategori'] ?? '-') ?></td>
                    <td><?= e($h['aciklama'] ?? '-') ?></td>
                    <td style="font-weight:700;color:<?= $h['islem_tipi'] == 'GELIR' ? 'var(--green)' : 'var(--red)' ?>">
                        <?= $h['islem_tipi'] == 'GELIR' ? '+' : '-' ?>₺<?= number_format($h['tutar'], 2, ',', '.') ?>
                    </td>
                    <td>
                        <div class="icons">
                            <form method="POST" style="display:inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="action" value="sil">
                                <input type="hidden" name="id" value="<?= $h['id'] ?>">
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
