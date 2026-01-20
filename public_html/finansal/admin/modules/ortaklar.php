<?php
/**
 * MR HASAR DANIŞMANLIK - İş Ortakları
 * HERZAMAN FARKEDER
 */

// Silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE partners SET is_active = 0 WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Ortak pasife alındı.');
    header('Location: index.php?page=ortaklar');
    exit;
}

// Kayıt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'name' => clean($_POST['name']),
        'type' => clean($_POST['type'] ?? 'DİĞER'),
        'phone' => clean($_POST['phone'] ?? ''),
        'email' => clean($_POST['email'] ?? ''),
        'address' => clean($_POST['address'] ?? ''),
        'iban' => clean($_POST['iban'] ?? ''),
        'bank_name' => clean($_POST['bank_name'] ?? ''),
        'adk_share_rate' => floatval($_POST['adk_share_rate'] ?? 50),
        'bh_share_rate' => floatval($_POST['bh_share_rate'] ?? 50),
        'notes' => clean($_POST['notes'] ?? ''),
    ];

    if (empty($data['name'])) {
        setFlash('error', 'Ortak adı zorunludur.');
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE partners SET name=?, type=?, phone=?, email=?, address=?, iban=?, bank_name=?, adk_share_rate=?, bh_share_rate=?, notes=? WHERE id=?");
            $stmt->execute([$data['name'], $data['type'], $data['phone'], $data['email'], $data['address'], $data['iban'], $data['bank_name'], $data['adk_share_rate'], $data['bh_share_rate'], $data['notes'], $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO partners (name, type, phone, email, address, iban, bank_name, adk_share_rate, bh_share_rate, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['name'], $data['type'], $data['phone'], $data['email'], $data['address'], $data['iban'], $data['bank_name'], $data['adk_share_rate'], $data['bh_share_rate'], $data['notes']]);
        }
        setFlash('success', 'Ortak kaydedildi.');
        header('Location: index.php?page=ortaklar');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM partners WHERE is_active = 1 ORDER BY name");
$partners = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-person-badge me-2"></i>İş Ortakları</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item active">Ortaklar</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#partnerModal">
            <i class="bi bi-plus-lg me-1"></i> Yeni Ortak
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Ortak Adı</th>
                        <th>Tür</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th>ADK Pay %</th>
                        <th>BH Pay %</th>
                        <th>Bakiye</th>
                        <th class="text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partners as $p): ?>
                    <tr>
                        <td><strong><?= e($p['name']) ?></strong></td>
                        <td><span class="badge bg-secondary"><?= e($p['type']) ?></span></td>
                        <td><?= e($p['phone'] ?? '-') ?></td>
                        <td><?= e($p['email'] ?? '-') ?></td>
                        <td>%<?= $p['adk_share_rate'] ?></td>
                        <td>%<?= $p['bh_share_rate'] ?></td>
                        <td class="<?= $p['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= formatMoney($p['balance']) ?>
                        </td>
                        <td class="text-center">
                            <a href="index.php?page=ortak_hesaplari&partner_id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-info" title="Hesap Hareketleri">
                                <i class="bi bi-wallet2"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-warning btn-edit"
                                    data-item='<?= json_encode($p) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?page=ortaklar&delete=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-danger btn-delete" data-name="<?= e($p['name']) ?>">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="partnerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="itemId" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Yeni Ortak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Ortak Adı *</label>
                            <input type="text" class="form-control" name="name" id="itemName" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tür</label>
                            <select class="form-select" name="type" id="itemType">
                                <option value="AVUKAT">Avukat</option>
                                <option value="EKSPER">Eksper</option>
                                <option value="DİĞER">Diğer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="phone" id="itemPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" id="itemEmail">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adres</label>
                            <textarea class="form-control" name="address" id="itemAddress" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IBAN</label>
                            <input type="text" class="form-control" name="iban" id="itemIban">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banka Adı</label>
                            <input type="text" class="form-control" name="bank_name" id="itemBank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ADK Pay Oranı (%)</label>
                            <input type="number" class="form-control" name="adk_share_rate" id="itemAdkRate" value="50" min="0" max="100" step="0.1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">BH Pay Oranı (%)</label>
                            <input type="number" class="form-control" name="bh_share_rate" id="itemBhRate" value="50" min="0" max="100" step="0.1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notlar</label>
                            <textarea class="form-control" name="notes" id="itemNotes" rows="2"></textarea>
                        </div>
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

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const item = JSON.parse(this.dataset.item);
        document.getElementById('itemId').value = item.id;
        document.getElementById('modalTitle').textContent = 'Ortak Düzenle';
        document.getElementById('itemName').value = item.name || '';
        document.getElementById('itemType').value = item.type || 'DİĞER';
        document.getElementById('itemPhone').value = item.phone || '';
        document.getElementById('itemEmail').value = item.email || '';
        document.getElementById('itemAddress').value = item.address || '';
        document.getElementById('itemIban').value = item.iban || '';
        document.getElementById('itemBank').value = item.bank_name || '';
        document.getElementById('itemAdkRate').value = item.adk_share_rate || 50;
        document.getElementById('itemBhRate').value = item.bh_share_rate || 50;
        document.getElementById('itemNotes').value = item.notes || '';
        new bootstrap.Modal(document.getElementById('partnerModal')).show();
    });
});

document.getElementById('partnerModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('itemId').value = 0;
    document.getElementById('modalTitle').textContent = 'Yeni Ortak';
    this.querySelector('form').reset();
});
</script>
