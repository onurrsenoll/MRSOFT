<?php
/**
 * MR HASAR DANIŞMANLIK - Ortak Hesap Hareketleri
 * HERZAMAN FARKEDER
 */

$partnerId = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;
$partners = getDropdownData($pdo, 'partners', 'id', 'name', 'is_active = 1');
$selectedPartner = null;
$transactions = [];

if ($partnerId) {
    $stmt = $pdo->prepare("SELECT * FROM partners WHERE id = ?");
    $stmt->execute([$partnerId]);
    $selectedPartner = $stmt->fetch();

    if ($selectedPartner) {
        $stmt = $pdo->prepare("SELECT pt.*, c.file_no, u.name as user_name
                               FROM partner_transactions pt
                               LEFT JOIN cases c ON pt.case_id = c.id
                               LEFT JOIN users u ON pt.created_by = u.id
                               WHERE pt.partner_id = ?
                               ORDER BY pt.transaction_date DESC, pt.created_at DESC");
        $stmt->execute([$partnerId]);
        $transactions = $stmt->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedPartner) {
    $type = clean($_POST['transaction_type']);
    $amount = floatval($_POST['amount']);
    $description = clean($_POST['description'] ?? '');
    $date = $_POST['transaction_date'] ?: date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO partner_transactions (partner_id, transaction_type, amount, description, transaction_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$partnerId, $type, $amount, $description, $date, $_SESSION['user_id']]);

    $multiplier = in_array($type, ['ALACAK', 'TAHSİLAT']) ? 1 : -1;
    $stmt = $pdo->prepare("UPDATE partners SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$amount * $multiplier, $partnerId]);

    setFlash('success', 'İşlem kaydedildi.');
    header("Location: index.php?page=ortak_hesaplari&partner_id=$partnerId");
    exit;
}
?>

<div class="page-header">
    <h1><i class="bi bi-wallet2 me-2"></i>Ortak Hesap Hareketleri</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="ortak_hesaplari">
            <div class="col-md-4">
                <select class="form-select" name="partner_id" onchange="this.form.submit()">
                    <option value="">Ortak Seçiniz</option>
                    <?php foreach ($partners as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $partnerId == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedPartner): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-<?= $selectedPartner['balance'] >= 0 ? 'success' : 'danger' ?> text-white">
            <div class="card-body">
                <h6 class="text-white-50"><?= e($selectedPartner['name']) ?></h6>
                <h3><?= formatMoney($selectedPartner['balance']) ?></h3>
                <small>Güncel Bakiye</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-2">
                <select class="form-select" name="transaction_type" required>
                    <option value="ALACAK">Alacak</option>
                    <option value="BORÇ">Borç</option>
                    <option value="ÖDEME">Ödeme</option>
                    <option value="TAHSİLAT">Tahsilat</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="amount" placeholder="Tutar" step="0.01" required>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control datepicker" name="transaction_date" placeholder="Tarih">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="description" placeholder="Açıklama">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Ekle</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Tür</th>
                        <th>Dosya</th>
                        <th>Açıklama</th>
                        <th class="text-end">Tutar</th>
                        <th>İşleyen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?= formatDate($t['transaction_date']) ?></td>
                        <td>
                            <span class="badge bg-<?= in_array($t['transaction_type'], ['ALACAK', 'TAHSİLAT']) ? 'success' : 'danger' ?>">
                                <?= e($t['transaction_type']) ?>
                            </span>
                        </td>
                        <td><?= e($t['file_no'] ?? '-') ?></td>
                        <td><?= e($t['description'] ?? '-') ?></td>
                        <td class="text-end"><?= formatMoney($t['amount']) ?></td>
                        <td><?= e($t['user_name'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        Lütfen bir ortak seçiniz.
    </div>
</div>
<?php endif; ?>
