<?php
/**
 * MR HASAR DANIŞMANLIK - KASALAR
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$kasalar = [];

try {
    $db = getDB();
    $kasalar = $db->query("SELECT * FROM cashboxes WHERE is_active = 1 ORDER BY name")->fetchAll();
    
    // Toplam bakiye
    $toplamBakiye = array_sum(array_column($kasalar, 'balance'));
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Kasa ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $name = trim(mb_strtoupper($_POST['name'] ?? '', 'UTF-8'));
    if ($name) {
        try {
            $stmt = $db->prepare("INSERT INTO cashboxes (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: kasa.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-cash-register"></i> KASALAR</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- TOPLAM BAKİYE -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon green"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="card-title">TOPLAM NAKİT</div>
                <div class="card-val">₺<?= number_format($toplamBakiye ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- YENİ KASA -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ KASA EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">KASA ADI</label>
                <input type="text" name="name" class="frm-in" required>
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> EKLE</button>
            </div>
        </div>
    </form>
</div>

<!-- KASA LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> KASA LİSTESİ</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>KASA ADI</th>
                    <th>BAKİYE</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kasalar as $k): ?>
                <tr>
                    <td style="font-weight:700"><i class="fas fa-cash-register" style="color:var(--blue);margin-right:10px"></i><?= e($k['name']) ?></td>
                    <td style="font-weight:700;font-size:14px;color:<?= $k['balance'] >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                        ₺<?= number_format($k['balance'], 2, ',', '.') ?>
                    </td>
                    <td>
                        <a href="gelir_gider.php?kasa=<?= $k['id'] ?>" class="btn btn-sec btn-sm"><i class="fas fa-exchange-alt"></i> HAREKET</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
