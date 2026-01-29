<?php
/**
 * MR HASAR DANIŞMANLIK - ORTAK HESAPLARI
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$success = '';
$ortaklar = [];
$hareketler = [];
$secilenOrtak = null;
$bakiye = 0;

try {
    $db = getDB();

    // Ortakları ve bakiyelerini çek
    $ortaklar = $db->query("
        SELECT c.*,
            COALESCE((SELECT SUM(CASE WHEN hareket_tipi = 'ALACAK' THEN tutar ELSE -tutar END) FROM cari_hareketler WHERE cari_id = c.id), 0) as bakiye
        FROM caris c
        WHERE c.cari_tipi = 'ORTAK' AND c.is_active = 1
        ORDER BY c.ad_soyad
    ")->fetchAll();

    if (!empty($_GET['ortak'])) {
        $ortak_id = intval($_GET['ortak']);
        $stmt = $db->prepare("
            SELECT c.*,
                COALESCE((SELECT SUM(CASE WHEN hareket_tipi = 'ALACAK' THEN tutar ELSE -tutar END) FROM cari_hareketler WHERE cari_id = c.id), 0) as bakiye
            FROM caris c
            WHERE c.id = ? AND c.cari_tipi = 'ORTAK'
        ");
        $stmt->execute([$ortak_id]);
        $secilenOrtak = $stmt->fetch();

        if ($secilenOrtak) {
            $bakiye = $secilenOrtak['bakiye'];

            // Hesap hareketlerini çek
            $stmt = $db->prepare("
                SELECT h.*, d.dosya_no
                FROM cari_hareketler h
                LEFT JOIN cases d ON h.dosya_id = d.id
                WHERE h.cari_id = ?
                ORDER BY h.islem_tarihi DESC, h.id DESC
            ");
            $stmt->execute([$ortak_id]);
            $hareketler = $stmt->fetchAll();
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ödeme ekleme (BORC = ortağa ödeme yapıldı, bakiyeden düş)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'odeme') {
    $ortak_id = intval($_POST['ortak_id'] ?? 0);
    $tutar = floatval($_POST['tutar'] ?? 0);
    $islem_tarihi = $_POST['islem_tarihi'] ?? date('Y-m-d');
    $aciklama = trim($_POST['aciklama'] ?? '');

    if ($ortak_id && $tutar > 0) {
        try {
            $stmt = $db->prepare("INSERT INTO cari_hareketler (cari_id, hareket_tipi, tutar, aciklama, islem_tarihi, created_by) VALUES (?, 'BORC', ?, ?, ?, ?)");
            $stmt->execute([$ortak_id, $tutar, $aciklama ?: 'ORTAK ÖDEMESİ', $islem_tarihi, $_SESSION['user_id']]);
            header("Location: ortak_hesaplari.php?ortak=$ortak_id&success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'ORTAK VE TUTAR SEÇİNİZ';
    }
}

// Alacak ekleme (ALACAK = ortağa alacak eklendi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'alacak') {
    $ortak_id = intval($_POST['ortak_id'] ?? 0);
    $tutar = floatval($_POST['tutar'] ?? 0);
    $islem_tarihi = $_POST['islem_tarihi'] ?? date('Y-m-d');
    $aciklama = trim($_POST['aciklama'] ?? '');

    if ($ortak_id && $tutar > 0) {
        try {
            $stmt = $db->prepare("INSERT INTO cari_hareketler (cari_id, hareket_tipi, tutar, aciklama, islem_tarihi, created_by) VALUES (?, 'ALACAK', ?, ?, ?, ?)");
            $stmt->execute([$ortak_id, $tutar, $aciklama ?: 'ALACAK EKLENDİ', $islem_tarihi, $_SESSION['user_id']]);
            header("Location: ortak_hesaplari.php?ortak=$ortak_id&success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'ORTAK VE TUTAR SEÇİNİZ';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-calculator"></i> ORTAK HESAPLARI</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- ORTAK SEÇİMİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-user"></i> ORTAK SEÇ</div>
    </div>
    <div style="padding:20px">
        <form method="GET" class="form-grid">
            <div class="frm-grp">
                <select name="ortak" class="frm-sel" onchange="this.form.submit()">
                    <option value="">ORTAK SEÇİNİZ...</option>
                    <?php foreach ($ortaklar as $o): ?>
                    <option value="<?= $o['id'] ?>" <?= ($secilenOrtak && $secilenOrtak['id'] == $o['id']) ? 'selected' : '' ?>>
                        <?= e($o['ad_soyad']) ?> (₺<?= number_format($o['bakiye'], 2, ',', '.') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($secilenOrtak): ?>
<!-- ORTAK BİLGİLERİ -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-user"></i></div>
            <div>
                <div class="card-title">ORTAK</div>
                <div class="card-val" style="font-size:16px"><?= e($secilenOrtak['ad_soyad']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon <?= $bakiye >= 0 ? 'green' : 'red' ?>"><i class="fas fa-lira-sign"></i></div>
            <div>
                <div class="card-title">GÜNCEL BAKİYE</div>
                <div class="card-val">₺<?= number_format($bakiye, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon purple"><i class="fas fa-percent"></i></div>
            <div>
                <div class="card-title">KOMİSYON ORANI</div>
                <div class="card-val">%<?= number_format($secilenOrtak['komisyon_orani'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- İŞLEM BUTONLARI -->
<div style="display:flex;gap:10px;margin-bottom:20px">
    <button onclick="document.getElementById('odemeModal').style.display='flex'" class="btn btn-pri"><i class="fas fa-minus-circle"></i> ÖDEME YAP</button>
    <button onclick="document.getElementById('alacakModal').style.display='flex'" class="btn btn-suc"><i class="fas fa-plus-circle"></i> ALACAK EKLE</button>
</div>

<!-- ÖDEME MODAL -->
<div id="odemeModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:450px;border:2px solid #3b82f6">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-money-bill"></i> ÖDEME YAP</h3>
            <button onclick="document.getElementById('odemeModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="odeme">
            <input type="hidden" name="ortak_id" value="<?= $secilenOrtak['id'] ?>">
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">TUTAR <span class="req">*</span></label>
                <input type="number" name="tutar" class="frm-in" step="0.01" required>
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">TARİH</label>
                <input type="date" name="islem_tarihi" class="frm-in" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">AÇIKLAMA</label>
                <input type="text" name="aciklama" class="frm-in" placeholder="ÖDEME AÇIKLAMASI">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('odemeModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-pri"><i class="fas fa-check"></i> ÖDEME YAP</button>
            </div>
        </form>
    </div>
</div>

<!-- ALACAK MODAL -->
<div id="alacakModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:450px;border:2px solid #22c55e">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-plus-circle"></i> ALACAK EKLE</h3>
            <button onclick="document.getElementById('alacakModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="alacak">
            <input type="hidden" name="ortak_id" value="<?= $secilenOrtak['id'] ?>">
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">TUTAR <span class="req">*</span></label>
                <input type="number" name="tutar" class="frm-in" step="0.01" required>
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">TARİH</label>
                <input type="date" name="islem_tarihi" class="frm-in" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">AÇIKLAMA</label>
                <input type="text" name="aciklama" class="frm-in" placeholder="ALACAK AÇIKLAMASI">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('alacakModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-check"></i> ALACAK EKLE</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('odemeModal').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
document.getElementById('alacakModal').addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
</script>

<!-- HESAP HAREKETLERİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-history"></i> HESAP HAREKETLERİ</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>TARİH</th>
                    <th>TİP</th>
                    <th>DOSYA NO</th>
                    <th>AÇIKLAMA</th>
                    <th>TUTAR</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($hareketler)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text2)">HAREKET BULUNAMADI</td></tr>
                <?php else: foreach ($hareketler as $h): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($h['islem_tarihi'])) ?></td>
                    <td>
                        <span class="badge <?= $h['hareket_tipi'] == 'ALACAK' ? 'aktif' : 'kapali' ?>">
                            <?= e($h['hareket_tipi']) ?>
                        </span>
                    </td>
                    <td><?= e($h['dosya_no'] ?? '-') ?></td>
                    <td><?= e($h['aciklama'] ?? '-') ?></td>
                    <td style="font-weight:700;color:<?= $h['hareket_tipi'] == 'ALACAK' ? 'var(--green)' : 'var(--red)' ?>">
                        <?= $h['hareket_tipi'] == 'ALACAK' ? '+' : '-' ?>₺<?= number_format($h['tutar'], 2, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
