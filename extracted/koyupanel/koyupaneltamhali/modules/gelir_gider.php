<?php
/**
 * MR HASAR DANIŞMANLIK - GELİR / GİDER
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$error = '';
$hareketler = [];

try {
    $db = getDB();
    $kasalar = $db->query("SELECT id, name FROM cashboxes WHERE is_active = 1")->fetchAll();
    
    // Filtre
    $where = "1=1";
    $params = [];
    
    if (!empty($_GET['kasa'])) {
        $where .= " AND f.cashbox_id = ?";
        $params[] = $_GET['kasa'];
    }
    
    if (!empty($_GET['tip'])) {
        $where .= " AND f.txn_type = ?";
        $params[] = $_GET['tip'];
    }
    
    $stmt = $db->prepare("
        SELECT f.*, k.name as kasa_adi, c.dosya_no
        FROM finance_transactions f
        LEFT JOIN cashboxes k ON f.cashbox_id = k.id
        LEFT JOIN cases c ON f.case_id = c.id
        WHERE $where
        ORDER BY f.txn_date DESC, f.id DESC
        LIMIT 100
    ");
    $stmt->execute($params);
    $hareketler = $stmt->fetchAll();
    
    // Özet
    $toplamGelir = $db->query("SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE txn_type = 'GELIR'")->fetchColumn();
    $toplamGider = $db->query("SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE txn_type = 'GIDER'")->fetchColumn();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Hareket ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $txn_type = $_POST['txn_type'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $cashbox_id = intval($_POST['cashbox_id'] ?? 0);
    $txn_date = $_POST['txn_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    
    if ($txn_type && $amount > 0 && $cashbox_id) {
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("INSERT INTO finance_transactions (txn_date, txn_type, amount, cashbox_id, description, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$txn_date, $txn_type, $amount, $cashbox_id, $description, $_SESSION['user_id']]);
            
            // Kasa bakiyesi güncelle
            $sign = $txn_type == 'GELIR' ? '+' : '-';
            $stmt = $db->prepare("UPDATE cashboxes SET balance = balance $sign ? WHERE id = ?");
            $stmt->execute([$amount, $cashbox_id]);
            
            $db->commit();
            header("Location: gelir_gider.php?success=1");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = 'TÜM ALANLAR ZORUNLUDUR';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-exchange-alt"></i> GELİR / GİDER</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- ÖZET -->
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
            <div class="card-icon blue"><i class="fas fa-balance-scale"></i></div>
            <div>
                <div class="card-title">NET</div>
                <div class="card-val">₺<?= number_format(($toplamGelir ?? 0) - ($toplamGider ?? 0), 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- YENİ HAREKET -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ HAREKET</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">TİP <span class="req">*</span></label>
                <select name="txn_type" class="frm-sel" required>
                    <option value="">SEÇİNİZ</option>
                    <option value="GELIR">GELİR</option>
                    <option value="GIDER">GİDER</option>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">KASA <span class="req">*</span></label>
                <select name="cashbox_id" class="frm-sel" required>
                    <option value="">SEÇİNİZ</option>
                    <?php foreach ($kasalar as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= e($k['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                <input type="text" name="description" class="frm-in">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </div>
    </form>
</div>

<!-- FİLTRE -->
<form method="GET" class="filter">
    <div class="filter-grid">
        <div class="f-group">
            <label class="f-label">KASA</label>
            <select name="kasa" class="f-select">
                <option value="">TÜMÜ</option>
                <?php foreach ($kasalar as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($_GET['kasa'] ?? '') == $k['id'] ? 'selected' : '' ?>><?= e($k['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="f-group">
            <label class="f-label">TİP</label>
            <select name="tip" class="f-select">
                <option value="">TÜMÜ</option>
                <option value="GELIR" <?= ($_GET['tip'] ?? '') == 'GELIR' ? 'selected' : '' ?>>GELİR</option>
                <option value="GIDER" <?= ($_GET['tip'] ?? '') == 'GIDER' ? 'selected' : '' ?>>GİDER</option>
            </select>
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
        <div class="panel-title"><i class="fas fa-history"></i> SON HAREKETLER</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>TARİH</th>
                    <th>TİP</th>
                    <th>KASA</th>
                    <th>DOSYA</th>
                    <th>AÇIKLAMA</th>
                    <th>TUTAR</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($hareketler)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text2)">HAREKET BULUNAMADI</td></tr>
                <?php else: foreach ($hareketler as $h): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($h['txn_date'])) ?></td>
                    <td>
                        <span class="badge <?= $h['txn_type'] == 'GELIR' ? 'aktif' : 'kapali' ?>"><?= e($h['txn_type']) ?></span>
                    </td>
                    <td><?= e($h['kasa_adi'] ?? '-') ?></td>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
