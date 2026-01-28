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

try {
    $db = getDB();
    $ortaklar = $db->query("SELECT * FROM caris WHERE cari_type = 'ORTAK' ORDER BY name")->fetchAll();
    
    if (!empty($_GET['ortak'])) {
        $ortak_id = intval($_GET['ortak']);
        $stmt = $db->prepare("SELECT * FROM caris WHERE id = ? AND cari_type = 'ORTAK'");
        $stmt->execute([$ortak_id]);
        $secilenOrtak = $stmt->fetch();
        
        if ($secilenOrtak) {
            $stmt = $db->prepare("
                SELECT f.*, c.dosya_no
                FROM finance_transactions f
                LEFT JOIN cases c ON f.case_id = c.id
                WHERE f.cari_id = ?
                ORDER BY f.txn_date DESC
            ");
            $stmt->execute([$ortak_id]);
            $hareketler = $stmt->fetchAll();
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ödeme ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'odeme') {
    $ortak_id = intval($_POST['ortak_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $txn_date = $_POST['txn_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    
    if ($ortak_id && $amount > 0) {
        try {
            $db->beginTransaction();
            
            // Hareket ekle
            $stmt = $db->prepare("INSERT INTO finance_transactions (txn_date, txn_type, amount, cari_id, description, created_by_user_id) VALUES (?, 'GIDER', ?, ?, ?, ?)");
            $stmt->execute([$txn_date, $amount, $ortak_id, $description ?: 'ORTAK ÖDEMESİ', $_SESSION['user_id']]);
            
            // Bakiyeyi güncelle
            $stmt = $db->prepare("UPDATE caris SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $ortak_id]);
            
            $db->commit();
            header("Location: ortak_hesaplari.php?ortak=$ortak_id&success=1");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
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
                        <?= e($o['name']) ?> (₺<?= number_format($o['balance'], 2, ',', '.') ?>)
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
                <div class="card-val" style="font-size:16px"><?= e($secilenOrtak['name']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon <?= $secilenOrtak['balance'] >= 0 ? 'green' : 'red' ?>"><i class="fas fa-lira-sign"></i></div>
            <div>
                <div class="card-title">GÜNCEL BAKİYE</div>
                <div class="card-val">₺<?= number_format($secilenOrtak['balance'], 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ÖDEME YAPMA -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-money-bill"></i> ÖDEME YAP</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="odeme">
        <input type="hidden" name="ortak_id" value="<?= $secilenOrtak['id'] ?>">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">TUTAR <span class="req">*</span></label>
                <input type="number" name="amount" class="frm-in" step="0.01" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TARİH</label>
                <input type="date" name="txn_date" class="frm-in" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">AÇIKLAMA</label>
                <input type="text" name="description" class="frm-in" placeholder="ÖDEME AÇIKLAMASI">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-check"></i> ÖDEME YAP</button>
            </div>
        </div>
    </form>
</div>

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
                    <td><?= date('d.m.Y', strtotime($h['txn_date'])) ?></td>
                    <td>
                        <span class="badge <?= $h['txn_type'] == 'GELIR' ? 'aktif' : 'kapali' ?>">
                            <?= e($h['txn_type']) ?>
                        </span>
                    </td>
                    <td><?= e($h['dosya_no'] ?? '-') ?></td>
                    <td><?= e($h['description'] ?? '-') ?></td>
                    <td style="font-weight:700;color:<?= $h['txn_type'] == 'GELIR' ? 'var(--green)' : 'var(--red)' ?>">
                        <?= $h['txn_type'] == 'GELIR' ? '+' : '-' ?>₺<?= number_format($h['amount'], 2, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
