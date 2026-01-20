<?php
/**
 * MR HASAR DANIŞMANLIK - Kasa Yönetimi
 * HERZAMAN FARKEDER
 */

// İşlem yapılıyorsa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_cash') {
        $name = clean($_POST['name']);
        $type = clean($_POST['type']);
        $bankName = clean($_POST['bank_name'] ?? '');
        $iban = clean($_POST['iban'] ?? '');
        $balance = floatval($_POST['opening_balance'] ?? 0);

        $stmt = $pdo->prepare("INSERT INTO cashes (name, type, bank_name, iban, balance) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $bankName, $iban, $balance]);
        setFlash('success', 'Kasa eklendi.');
        header('Location: index.php?page=kasa');
        exit;
    }

    if ($action === 'add_transaction') {
        $cashId = (int)$_POST['cash_id'];
        $type = clean($_POST['transaction_type']);
        $amount = floatval($_POST['amount']);
        $category = clean($_POST['category'] ?? '');
        $description = clean($_POST['description'] ?? '');
        $transactionDate = $_POST['transaction_date'] ?: date('Y-m-d');

        $stmt = $pdo->prepare("INSERT INTO cash_transactions (cash_id, transaction_type, amount, category, description, transaction_date, created_by)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cashId, $type, $amount, $category, $description, $transactionDate, $_SESSION['user_id']]);

        // Kasa bakiyesini güncelle
        $multiplier = ($type === 'GİRİŞ') ? 1 : -1;
        $stmt = $pdo->prepare("UPDATE cashes SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount * $multiplier, $cashId]);

        setFlash('success', 'İşlem kaydedildi.');
        header("Location: index.php?page=kasa&cash_id=$cashId");
        exit;
    }

    if ($action === 'transfer') {
        $fromCash = (int)$_POST['from_cash'];
        $toCash = (int)$_POST['to_cash'];
        $amount = floatval($_POST['transfer_amount']);
        $description = clean($_POST['transfer_description'] ?? 'Kasalar arası transfer');
        $transactionDate = date('Y-m-d');

        // Çıkış işlemi
        $stmt = $pdo->prepare("INSERT INTO cash_transactions (cash_id, transaction_type, amount, category, description, transaction_date, created_by)
                               VALUES (?, 'TRANSFER', ?, 'TRANSFER', ?, ?, ?)");
        $stmt->execute([$fromCash, $amount, 'Transfer çıkış - ' . $description, $transactionDate, $_SESSION['user_id']]);

        // Giriş işlemi
        $stmt->execute([$toCash, $amount, 'Transfer giriş - ' . $description, $transactionDate, $_SESSION['user_id']]);

        // Bakiyeleri güncelle
        $stmt = $pdo->prepare("UPDATE cashes SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $fromCash]);
        $stmt = $pdo->prepare("UPDATE cashes SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $toCash]);

        setFlash('success', 'Transfer tamamlandı.');
        header('Location: index.php?page=kasa');
        exit;
    }
}

// Kasaları çek
$stmt = $pdo->query("SELECT * FROM cashes WHERE is_active = 1 ORDER BY name");
$cashes = $stmt->fetchAll();

// Seçili kasa
$selectedCashId = isset($_GET['cash_id']) ? (int)$_GET['cash_id'] : ($cashes[0]['id'] ?? 0);
$selectedCash = null;
$transactions = [];

if ($selectedCashId) {
    foreach ($cashes as $c) {
        if ($c['id'] == $selectedCashId) {
            $selectedCash = $c;
            break;
        }
    }

    // Hareketleri çek
    $stmt = $pdo->prepare("SELECT ct.*, u.name as user_name
                           FROM cash_transactions ct
                           LEFT JOIN users u ON ct.created_by = u.id
                           WHERE ct.cash_id = ?
                           ORDER BY ct.transaction_date DESC, ct.created_at DESC
                           LIMIT 100");
    $stmt->execute([$selectedCashId]);
    $transactions = $stmt->fetchAll();
}

// Toplam bakiye
$totalBalance = array_sum(array_column($cashes, 'balance'));
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1><i class="bi bi-safe me-2"></i>Kasa Yönetimi</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item active">Kasalar</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kasaModal">
                <i class="bi bi-plus-lg me-1"></i> Yeni Kasa
            </button>
        </div>
    </div>
</div>

<!-- Toplam Bakiye -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 text-white-50">Toplam Bakiye</h6>
                    <h3 class="mb-0"><?= formatMoney($totalBalance) ?></h3>
                </div>
                <i class="bi bi-wallet2 display-4 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Kasa Tabları -->
<ul class="nav nav-tabs mb-0">
    <?php foreach ($cashes as $cash): ?>
    <li class="nav-item">
        <a class="nav-link <?= $selectedCashId == $cash['id'] ? 'active' : '' ?>"
           href="index.php?page=kasa&cash_id=<?= $cash['id'] ?>">
            <i class="bi bi-<?= $cash['type'] === 'NAKİT' ? 'cash' : ($cash['type'] === 'BANKA' ? 'bank' : 'credit-card') ?> me-1"></i>
            <?= e($cash['name']) ?>
            <span class="badge bg-<?= $cash['balance'] >= 0 ? 'success' : 'danger' ?> ms-1">
                <?= formatMoney($cash['balance']) ?>
            </span>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<?php if ($selectedCash): ?>
<div class="card border-top-0 rounded-top-0">
    <div class="card-body">
        <!-- İşlem Ekle -->
        <form method="POST" class="mb-4">
            <input type="hidden" name="action" value="add_transaction">
            <input type="hidden" name="cash_id" value="<?= $selectedCashId ?>">
            <div class="row g-3">
                <div class="col-md-2">
                    <select class="form-select" name="transaction_type" required>
                        <option value="GİRİŞ">Giriş</option>
                        <option value="ÇIKIŞ">Çıkış</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="amount" placeholder="Tutar" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="category" placeholder="Kategori">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="description" placeholder="Açıklama">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control datepicker" name="transaction_date" placeholder="Tarih">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Hareketler Tablosu -->
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Tür</th>
                        <th>Kategori</th>
                        <th>Açıklama</th>
                        <th class="text-end">Tutar</th>
                        <th>İşleyen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Hareket bulunmuyor</td></tr>
                    <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?= formatDate($t['transaction_date']) ?></td>
                        <td>
                            <span class="badge bg-<?= $t['transaction_type'] === 'GİRİŞ' ? 'success' : ($t['transaction_type'] === 'ÇIKIŞ' ? 'danger' : 'info') ?>">
                                <?= e($t['transaction_type']) ?>
                            </span>
                        </td>
                        <td><?= e($t['category'] ?? '-') ?></td>
                        <td><?= e($t['description'] ?? '-') ?></td>
                        <td class="text-end">
                            <strong class="text-<?= $t['transaction_type'] === 'GİRİŞ' ? 'success' : 'danger' ?>">
                                <?= $t['transaction_type'] === 'GİRİŞ' ? '+' : '-' ?><?= formatMoney($t['amount']) ?>
                            </strong>
                        </td>
                        <td><?= e($t['user_name'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        Henüz kasa tanımlanmamış. Yukarıdaki "Yeni Kasa" butonuna tıklayarak kasa ekleyebilirsiniz.
    </div>
</div>
<?php endif; ?>

<!-- Yeni Kasa Modal -->
<div class="modal fade" id="kasaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_cash">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Kasa Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kasa Adı *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kasa Türü</label>
                        <select class="form-select" name="type">
                            <option value="NAKİT">Nakit Kasa</option>
                            <option value="BANKA">Banka Hesabı</option>
                            <option value="POS">POS Cihazı</option>
                            <option value="DİĞER">Diğer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Banka Adı</label>
                        <input type="text" class="form-control" name="bank_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IBAN</label>
                        <input type="text" class="form-control" name="iban">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açılış Bakiyesi</label>
                        <input type="number" class="form-control" name="opening_balance" value="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="transfer">
                <div class="modal-header">
                    <h5 class="modal-title">Kasalar Arası Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kaynak Kasa *</label>
                        <select class="form-select" name="from_cash" required>
                            <?php foreach ($cashes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= formatMoney($c['balance']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hedef Kasa *</label>
                        <select class="form-select" name="to_cash" required>
                            <?php foreach ($cashes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tutar *</label>
                        <input type="number" class="form-control" name="transfer_amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <input type="text" class="form-control" name="transfer_description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Transfer Yap</button>
                </div>
            </form>
        </div>
    </div>
</div>
