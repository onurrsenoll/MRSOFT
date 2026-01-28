<?php
/**
 * SAY HASAR DANIŞMANLIK - DOSYA MASRAF EKLEME
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 */

require_once __DIR__ . '/../config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: dosyalar.php');
    exit;
}

$error = '';
$success = '';

try {
    $db = getDB();
    
    // Dosya bilgisi
    $stmt = $db->prepare("SELECT dosya_no, ad_soyad FROM cases WHERE id = ?");
    $stmt->execute([$id]);
    $dosya = $stmt->fetch();
    
    if (!$dosya) {
        header('Location: dosyalar.php');
        exit;
    }
    
    // Masraf tipleri (YENİ TABLO)
    $masrafTipleri = $db->query("SELECT id, kalem_adi as name FROM masraf_kalemleri WHERE durum = 1 ORDER BY kalem_adi")->fetchAll();
    
    // Kasalar
    $kasalar = $db->query("SELECT id, name, balance FROM cashboxes WHERE is_active = 1 ORDER BY name")->fetchAll();
    
    // Mevcut masraflar
    $stmt = $db->prepare("
        SELECT e.*, t.kalem_adi as masraf_turu, k.name as kasa_adi
        FROM case_expenses e
        LEFT JOIN masraf_kalemleri t ON e.expense_type_id = t.id
        LEFT JOIN cashboxes k ON e.cashbox_id = k.id
        WHERE e.case_id = ?
        ORDER BY e.expense_date DESC
    ");
    $stmt->execute([$id]);
    $masraflar = $stmt->fetchAll();
    
    $toplamMasraf = array_sum(array_column($masraflar, 'amount'));
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Masraf ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $expense_type_id = intval($_POST['expense_type_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    $cashbox_id = intval($_POST['cashbox_id'] ?? 0);
    
    if ($expense_type_id && $amount > 0) {
        try {
            $db->beginTransaction();
            
            // Masraf kaydı
            $stmt = $db->prepare("INSERT INTO case_expenses (case_id, expense_type_id, amount, expense_date, description, cashbox_id, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $expense_type_id, $amount, $expense_date, $description, $cashbox_id ?: null, $_SESSION['user_id']]);
            $expense_id = $db->lastInsertId();
            
            // Eğer kasa seçildiyse, kasadan düş
            if ($cashbox_id > 0) {
                // Finance transaction kaydı (GİDER)
                $stmt = $db->prepare("
                    INSERT INTO finance_transactions (cashbox_id, transaction_type, amount, description, reference_type, reference_id, created_by_user_id, transaction_date) 
                    VALUES (?, 'GIDER', ?, ?, 'case_expense', ?, ?, ?)
                ");
                $stmt->execute([
                    $cashbox_id,
                    $amount,
                    "DOSYA MASRAFI: {$dosya['dosya_no']} - $description",
                    $expense_id,
                    $_SESSION['user_id'],
                    $expense_date
                ]);
                
                // Kasa bakiyesini güncelle
                $stmt = $db->prepare("UPDATE cashboxes SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$amount, $cashbox_id]);
            }
            
            $db->commit();
            header("Location: dosya_masraf.php?id=$id&success=1");
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = 'MASRAF TİPİ VE TUTAR ZORUNLUDUR';
    }
}

// Masraf silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil') {
    $masraf_id = intval($_POST['masraf_id'] ?? 0);
    if ($masraf_id) {
        try {
            $db->beginTransaction();
            
            // Önce masraf bilgisini al (kasa iadesi için)
            $stmt = $db->prepare("SELECT amount, cashbox_id FROM case_expenses WHERE id = ? AND case_id = ?");
            $stmt->execute([$masraf_id, $id]);
            $masrafBilgi = $stmt->fetch();
            
            if ($masrafBilgi) {
                // Eğer kasadan çıkmışsa, kasaya iade et
                if ($masrafBilgi['cashbox_id']) {
                    // Kasa bakiyesini geri ekle
                    $stmt = $db->prepare("UPDATE cashboxes SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$masrafBilgi['amount'], $masrafBilgi['cashbox_id']]);
                    
                    // İlgili finance transaction'ı sil
                    $stmt = $db->prepare("DELETE FROM finance_transactions WHERE reference_type = 'case_expense' AND reference_id = ?");
                    $stmt->execute([$masraf_id]);
                }
                
                // Masrafı sil
                $stmt = $db->prepare("DELETE FROM case_expenses WHERE id = ? AND case_id = ?");
                $stmt->execute([$masraf_id, $id]);
            }
            
            $db->commit();
            header("Location: dosya_masraf.php?id=$id&success=2");
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-receipt"></i> MASRAF EKLE: <?= e($dosya['dosya_no']) ?></div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> 
    <?= $_GET['success'] == '2' ? 'MASRAF SİLİNDİ VE KASA İADE EDİLDİ!' : 'MASRAF EKLENDİ!' ?>
</div>
<?php endif; ?>

<!-- ÜST BİLGİ -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-folder"></i></div>
            <div>
                <div class="card-title">DOSYA</div>
                <div class="card-val" style="font-size:14px"><?= e($dosya['dosya_no']) ?> - <?= e($dosya['ad_soyad']) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon red"><i class="fas fa-receipt"></i></div>
            <div>
                <div class="card-title">TOPLAM MASRAF</div>
                <div class="card-val">₺<?= number_format($toplamMasraf, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- MASRAF EKLEME FORMU -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ MASRAF EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">MASRAF TİPİ <span class="req">*</span></label>
                <select name="expense_type_id" class="frm-sel" required>
                    <option value="">SEÇİNİZ</option>
                    <?php foreach ($masrafTipleri as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TUTAR (₺) <span class="req">*</span></label>
                <input type="number" name="amount" class="frm-in" step="0.01" min="0.01" required placeholder="0.00">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ÖDEME KASASI</label>
                <select name="cashbox_id" class="frm-sel">
                    <option value="">-- KASA SEÇİNİZ (OPSİYONEL) --</option>
                    <?php foreach ($kasalar as $k): ?>
                    <option value="<?= $k['id'] ?>">
                        <?= e($k['name']) ?> (₺<?= number_format($k['balance'], 2, ',', '.') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#64748b;font-size:11px;margin-top:5px;display:block">
                    <i class="fas fa-info-circle"></i> Kasa seçerseniz tutar otomatik kasadan düşer
                </small>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TARİH</label>
                <input type="date" name="expense_date" class="frm-in" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="frm-grp" style="grid-column: span 2">
                <label class="frm-lbl">AÇIKLAMA</label>
                <input type="text" name="description" class="frm-in" placeholder="Masraf açıklaması...">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end;gap:10px">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
                <a href="dosya_detay.php?id=<?= $id ?>" class="btn btn-sec"><i class="fas fa-arrow-left"></i> GERİ</a>
            </div>
        </div>
    </form>
</div>

<!-- MASRAF LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> MASRAF LİSTESİ (<?= count($masraflar) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>MASRAF TİPİ</th>
                    <th>TUTAR</th>
                    <th>KASA</th>
                    <th>TARİH</th>
                    <th>AÇIKLAMA</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($masraflar)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text2)">
                    <i class="fas fa-receipt" style="font-size:30px;margin-bottom:10px;opacity:0.3;display:block"></i>
                    MASRAF BULUNAMADI
                </td></tr>
                <?php else: foreach ($masraflar as $m): ?>
                <tr>
                    <td><?= e($m['masraf_turu'] ?? '-') ?></td>
                    <td style="color:var(--red);font-weight:700">₺<?= number_format($m['amount'], 2, ',', '.') ?></td>
                    <td>
                        <?php if ($m['kasa_adi']): ?>
                        <span class="badge aktif"><i class="fas fa-cash-register"></i> <?= e($m['kasa_adi']) ?></span>
                        <?php else: ?>
                        <span style="color:#64748b">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d.m.Y', strtotime($m['expense_date'])) ?></td>
                    <td><?= e($m['description'] ?? '-') ?></td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Masrafı silmek istediğinize emin misiniz?<?= $m['cashbox_id'] ? ' Tutar kasaya iade edilecek.' : '' ?>')">
                            <input type="hidden" name="action" value="sil">
                            <input type="hidden" name="masraf_id" value="<?= $m['id'] ?>">
                            <button type="submit" class="ic del" title="SİL"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>