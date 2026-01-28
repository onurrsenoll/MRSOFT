<?php
/**
 * SAY HASAR DANIŞMANLIK - DOSYA ÖDEME EKLEME
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 */

require_once __DIR__ . '/../config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: dosyalar.php');
    exit;
}

$error = '';

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
    
    // Kasalar
    $kasalar = $db->query("SELECT id, name, balance FROM cashboxes WHERE is_active = 1 ORDER BY name")->fetchAll();
    
    // Mevcut ödemeler
    $stmt = $db->prepare("
        SELECT p.*, k.name as kasa_adi 
        FROM case_payments p
        LEFT JOIN cashboxes k ON p.cashbox_id = k.id
        WHERE p.case_id = ? 
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute([$id]);
    $odemeler = $stmt->fetchAll();
    
    $toplamTahsilat = 0;
    $toplamMusteriOdeme = 0;
    foreach ($odemeler as $o) {
        if ($o['payment_type'] == 'GELEN_TAHSILAT') $toplamTahsilat += $o['amount'];
        if ($o['payment_type'] == 'MUSTERIYE_ODEME') $toplamMusteriOdeme += $o['amount'];
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ödeme ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $payment_type = $_POST['payment_type'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    $cashbox_id = intval($_POST['cashbox_id'] ?? 0);
    
    if ($payment_type && $amount > 0) {
        try {
            $db->beginTransaction();
            
            // Ödeme kaydı
            $stmt = $db->prepare("INSERT INTO case_payments (case_id, payment_type, amount, payment_date, description, cashbox_id, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $payment_type, $amount, $payment_date, $description, $cashbox_id ?: null, $_SESSION['user_id']]);
            $payment_id = $db->lastInsertId();
            
            // Eğer kasa seçildiyse, kasa işlemi yap
            if ($cashbox_id > 0) {
                if ($payment_type == 'GELEN_TAHSILAT') {
                    // GELEN TAHSİLAT = Kasaya GİRİŞ (bakiye artar)
                    $transaction_type = 'GELIR';
                    $balance_change = $amount; // Pozitif
                    $desc_prefix = "TAHSİLAT GİRİŞİ";
                } else {
                    // MÜŞTERİYE ÖDEME veya DİĞER = Kasadan ÇIKIŞ (bakiye azalır)
                    $transaction_type = 'GIDER';
                    $balance_change = -$amount; // Negatif
                    $desc_prefix = "ÖDEME ÇIKIŞI";
                }
                
                // Finance transaction kaydı
                $stmt = $db->prepare("
                    INSERT INTO finance_transactions (cashbox_id, transaction_type, amount, description, reference_type, reference_id, created_by_user_id, transaction_date) 
                    VALUES (?, ?, ?, ?, 'case_payment', ?, ?, ?)
                ");
                $stmt->execute([
                    $cashbox_id,
                    $transaction_type,
                    $amount,
                    "$desc_prefix: {$dosya['dosya_no']} - $description",
                    $payment_id,
                    $_SESSION['user_id'],
                    $payment_date
                ]);
                
                // Kasa bakiyesini güncelle
                $stmt = $db->prepare("UPDATE cashboxes SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$balance_change, $cashbox_id]);
            }
            
            $db->commit();
            header("Location: dosya_odeme.php?id=$id&success=1");
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = 'ÖDEME TİPİ VE TUTAR ZORUNLUDUR';
    }
}

// Ödeme silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil') {
    $odeme_id = intval($_POST['odeme_id'] ?? 0);
    if ($odeme_id) {
        try {
            $db->beginTransaction();
            
            // Önce ödeme bilgisini al (kasa iadesi için)
            $stmt = $db->prepare("SELECT amount, cashbox_id, payment_type FROM case_payments WHERE id = ? AND case_id = ?");
            $stmt->execute([$odeme_id, $id]);
            $odemeBilgi = $stmt->fetch();
            
            if ($odemeBilgi) {
                // Eğer kasayla ilişkiliyse, kasa işlemini geri al
                if ($odemeBilgi['cashbox_id']) {
                    if ($odemeBilgi['payment_type'] == 'GELEN_TAHSILAT') {
                        // Tahsilat silindi = Kasadan düş
                        $balance_change = -$odemeBilgi['amount'];
                    } else {
                        // Ödeme silindi = Kasaya ekle
                        $balance_change = $odemeBilgi['amount'];
                    }
                    
                    // Kasa bakiyesini güncelle
                    $stmt = $db->prepare("UPDATE cashboxes SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$balance_change, $odemeBilgi['cashbox_id']]);
                    
                    // İlgili finance transaction'ı sil
                    $stmt = $db->prepare("DELETE FROM finance_transactions WHERE reference_type = 'case_payment' AND reference_id = ?");
                    $stmt->execute([$odeme_id]);
                }
                
                // Ödemeyi sil
                $stmt = $db->prepare("DELETE FROM case_payments WHERE id = ? AND case_id = ?");
                $stmt->execute([$odeme_id, $id]);
            }
            
            $db->commit();
            header("Location: dosya_odeme.php?id=$id&success=2");
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-lira-sign"></i> ÖDEME EKLE: <?= e($dosya['dosya_no']) ?></div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> 
    <?= $_GET['success'] == '2' ? 'ÖDEME SİLİNDİ VE KASA GÜNCELLENDİ!' : 'ÖDEME EKLENDİ!' ?>
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
            <div class="card-icon green"><i class="fas fa-arrow-down"></i></div>
            <div>
                <div class="card-title">GELEN TAHSİLAT</div>
                <div class="card-val">₺<?= number_format($toplamTahsilat, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-arrow-up"></i></div>
            <div>
                <div class="card-title">MÜŞTERİYE ÖDENEN</div>
                <div class="card-val">₺<?= number_format($toplamMusteriOdeme, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ÖDEME EKLEME FORMU -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ ÖDEME EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">ÖDEME TİPİ <span class="req">*</span></label>
                <select name="payment_type" id="payment_type" class="frm-sel" required onchange="updateKasaLabel()">
                    <option value="">SEÇİNİZ</option>
                    <option value="GELEN_TAHSILAT">GELEN TAHSİLAT (SİGORTADAN)</option>
                    <option value="MUSTERIYE_ODEME">MÜŞTERİYE ÖDEME</option>
                    <option value="DIGER">DİĞER ÖDEME</option>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TUTAR (₺) <span class="req">*</span></label>
                <input type="number" name="amount" class="frm-in" step="0.01" min="0.01" required placeholder="0.00">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl" id="kasa_label">KASA SEÇİNİZ</label>
                <select name="cashbox_id" class="frm-sel">
                    <option value="">-- KASA SEÇİNİZ (OPSİYONEL) --</option>
                    <?php foreach ($kasalar as $k): ?>
                    <option value="<?= $k['id'] ?>">
                        <?= e($k['name']) ?> (₺<?= number_format($k['balance'], 2, ',', '.') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#64748b;font-size:11px;margin-top:5px;display:block" id="kasa_info">
                    <i class="fas fa-info-circle"></i> Tahsilat = Kasaya giriş, Ödeme = Kasadan çıkış
                </small>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TARİH</label>
                <input type="date" name="payment_date" class="frm-in" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="frm-grp" style="grid-column: span 2">
                <label class="frm-lbl">AÇIKLAMA</label>
                <input type="text" name="description" class="frm-in" placeholder="Ödeme açıklaması...">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end;gap:10px">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
                <a href="dosya_detay.php?id=<?= $id ?>" class="btn btn-sec"><i class="fas fa-arrow-left"></i> GERİ</a>
            </div>
        </div>
    </form>
</div>

<!-- ÖDEME LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> ÖDEME LİSTESİ (<?= count($odemeler) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ÖDEME TİPİ</th>
                    <th>TUTAR</th>
                    <th>KASA</th>
                    <th>TARİH</th>
                    <th>AÇIKLAMA</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($odemeler)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text2)">
                    <i class="fas fa-lira-sign" style="font-size:30px;margin-bottom:10px;opacity:0.3;display:block"></i>
                    ÖDEME BULUNAMADI
                </td></tr>
                <?php else: foreach ($odemeler as $o): ?>
                <tr>
                    <td>
                        <span class="badge <?= $o['payment_type'] == 'GELEN_TAHSILAT' ? 'aktif' : 'kapali' ?>">
                            <?php
                            $tipLabel = [
                                'GELEN_TAHSILAT' => 'GELEN TAHSİLAT',
                                'MUSTERIYE_ODEME' => 'MÜŞTERİYE ÖDEME',
                                'DIGER' => 'DİĞER'
                            ];
                            echo $tipLabel[$o['payment_type']] ?? $o['payment_type'];
                            ?>
                        </span>
                    </td>
                    <td style="color:<?= $o['payment_type'] == 'GELEN_TAHSILAT' ? 'var(--green)' : 'var(--orange)' ?>;font-weight:700">
                        <?= $o['payment_type'] == 'GELEN_TAHSILAT' ? '+' : '-' ?>₺<?= number_format($o['amount'], 2, ',', '.') ?>
                    </td>
                    <td>
                        <?php if ($o['kasa_adi']): ?>
                        <span class="badge <?= $o['payment_type'] == 'GELEN_TAHSILAT' ? 'aktif' : 'bedeni' ?>">
                            <i class="fas fa-<?= $o['payment_type'] == 'GELEN_TAHSILAT' ? 'arrow-down' : 'arrow-up' ?>"></i> 
                            <?= e($o['kasa_adi']) ?>
                        </span>
                        <?php else: ?>
                        <span style="color:#64748b">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d.m.Y', strtotime($o['payment_date'])) ?></td>
                    <td><?= e($o['description'] ?? '-') ?></td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Ödemeyi silmek istediğinize emin misiniz?<?= $o['cashbox_id'] ? ' Kasa bakiyesi güncellenecek.' : '' ?>')">
                            <input type="hidden" name="action" value="sil">
                            <input type="hidden" name="odeme_id" value="<?= $o['id'] ?>">
                            <button type="submit" class="ic del" title="SİL"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateKasaLabel() {
    var type = document.getElementById('payment_type').value;
    var label = document.getElementById('kasa_label');
    var info = document.getElementById('kasa_info');
    
    if (type == 'GELEN_TAHSILAT') {
        label.innerHTML = 'GİRİŞ KASASI <span style="color:#22c55e">(TAHSİLAT)</span>';
        info.innerHTML = '<i class="fas fa-arrow-down" style="color:#22c55e"></i> Tutar seçilen kasaya GİRİŞ yapacak';
    } else if (type == 'MUSTERIYE_ODEME' || type == 'DIGER') {
        label.innerHTML = 'ÇIKIŞ KASASI <span style="color:#f97316">(ÖDEME)</span>';
        info.innerHTML = '<i class="fas fa-arrow-up" style="color:#f97316"></i> Tutar seçilen kasadan ÇIKIŞ yapacak';
    } else {
        label.innerHTML = 'KASA SEÇİNİZ';
        info.innerHTML = '<i class="fas fa-info-circle"></i> Tahsilat = Kasaya giriş, Ödeme = Kasadan çıkış';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>