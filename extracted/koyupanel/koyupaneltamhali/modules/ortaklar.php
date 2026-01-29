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

    // Ortakları ve bakiyelerini çek
    $ortaklar = $db->query("
        SELECT c.*,
            COALESCE((SELECT SUM(CASE WHEN hareket_tipi = 'ALACAK' THEN tutar ELSE -tutar END) FROM cari_hareketler WHERE cari_id = c.id), 0) as bakiye
        FROM caris c
        WHERE c.cari_tipi = 'ORTAK' AND c.is_active = 1
        ORDER BY c.ad_soyad
    ")->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'ekle') {
        $ad_soyad = trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8'));
        $telefon = trim($_POST['telefon'] ?? '');
        $iban = trim($_POST['iban'] ?? '');
        $komisyon = floatval($_POST['komisyon'] ?? 0);

        if (empty($ad_soyad)) {
            $error = 'ORTAK ADI ZORUNLUDUR';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO caris (ad_soyad, cari_tipi, telefon, iban, komisyon_orani) VALUES (?, 'ORTAK', ?, ?, ?)");
                $stmt->execute([$ad_soyad, $telefon, $iban, $komisyon]);
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
                $stmt = $db->prepare("UPDATE caris SET is_active = 0 WHERE id = ? AND cari_tipi = 'ORTAK'");
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

<div class="page-title">
    <i class="fas fa-users"></i> İŞ ORTAKLARI
    <button onclick="document.getElementById('ortakModal').style.display='flex'" class="btn btn-suc" style="margin-left:auto;font-size:12px">
        <i class="fas fa-plus"></i> YENİ ORTAK
    </button>
</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ ORTAK MODAL -->
<div id="ortakModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#1e293b;border-radius:12px;width:100%;max-width:500px;border:2px solid #3b82f6">
        <div style="padding:20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;color:#f1f5f9;font-size:16px"><i class="fas fa-user-plus"></i> YENİ ORTAK EKLE</h3>
            <button onclick="document.getElementById('ortakModal').style.display='none'" style="background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" style="padding:20px">
            <input type="hidden" name="action" value="ekle">
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">ORTAK ADI <span class="req">*</span></label>
                    <input type="text" name="ad_soyad" class="frm-in" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TELEFON</label>
                    <input type="tel" name="telefon" class="frm-in">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">IBAN</label>
                    <input type="text" name="iban" class="frm-in" maxlength="34">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">KOMİSYON ORANI (%)</label>
                    <input type="number" name="komisyon" class="frm-in" step="0.01" min="0" max="100" value="0">
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('ortakModal').style.display='none'" class="btn btn-sec"><i class="fas fa-times"></i> İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('ortakModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

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
                    <th>KOMİSYON</th>
                    <th>BAKİYE</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ortaklar)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text2)">ORTAK BULUNAMADI</td></tr>
                <?php else: foreach ($ortaklar as $o): ?>
                <tr>
                    <td style="font-weight:700"><?= e($o['ad_soyad']) ?></td>
                    <td><?= e($o['telefon'] ?? '-') ?></td>
                    <td style="font-size:10px"><?= e($o['iban'] ?? '-') ?></td>
                    <td>%<?= number_format($o['komisyon_orani'] ?? 0, 2) ?></td>
                    <td style="font-weight:700;color:<?= ($o['bakiye'] ?? 0) >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                        ₺<?= number_format($o['bakiye'] ?? 0, 2, ',', '.') ?>
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
