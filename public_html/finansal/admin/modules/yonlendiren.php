<?php
/**
 * MR HASAR DANIŞMANLIK - Yönlendirenler
 * HERZAMAN FARKEDER
 */

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE referrers SET is_active = 0 WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Yönlendiren pasife alındı.');
    header('Location: index.php?page=yonlendiren');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'type' => clean($_POST['type'] ?? 'YÖNLENDİREN'),
        'name' => clean($_POST['name']),
        'contact_person' => clean($_POST['contact_person'] ?? ''),
        'phone' => clean($_POST['phone'] ?? ''),
        'email' => clean($_POST['email'] ?? ''),
        'adk_commission_rate' => floatval($_POST['adk_commission_rate'] ?? 0),
        'bh_commission_rate' => floatval($_POST['bh_commission_rate'] ?? 0),
        'per_file_fee' => floatval($_POST['per_file_fee'] ?? 0),
        'notes' => clean($_POST['notes'] ?? ''),
    ];

    if (empty($data['name'])) {
        setFlash('error', 'Ad zorunludur.');
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE referrers SET type=?, name=?, contact_person=?, phone=?, email=?, adk_commission_rate=?, bh_commission_rate=?, per_file_fee=?, notes=? WHERE id=?");
            $stmt->execute([$data['type'], $data['name'], $data['contact_person'], $data['phone'], $data['email'], $data['adk_commission_rate'], $data['bh_commission_rate'], $data['per_file_fee'], $data['notes'], $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO referrers (type, name, contact_person, phone, email, adk_commission_rate, bh_commission_rate, per_file_fee, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['type'], $data['name'], $data['contact_person'], $data['phone'], $data['email'], $data['adk_commission_rate'], $data['bh_commission_rate'], $data['per_file_fee'], $data['notes']]);
        }
        setFlash('success', 'Yönlendiren kaydedildi.');
        header('Location: index.php?page=yonlendiren');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM referrers WHERE is_active = 1 ORDER BY name");
$referrers = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-signpost-2 me-2"></i>Yönlendirenler</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
            <i class="bi bi-plus-lg me-1"></i> Yeni Ekle
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Tür</th>
                        <th>İletişim</th>
                        <th>Telefon</th>
                        <th>ADK %</th>
                        <th>BH %</th>
                        <th>Dosya Başı</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrers as $r): ?>
                    <tr>
                        <td><strong><?= e($r['name']) ?></strong></td>
                        <td><span class="badge bg-<?= $r['type'] === 'SERVİS' ? 'info' : 'secondary' ?>"><?= e($r['type']) ?></span></td>
                        <td><?= e($r['contact_person'] ?? '-') ?></td>
                        <td><?= e($r['phone'] ?? '-') ?></td>
                        <td>%<?= $r['adk_commission_rate'] ?></td>
                        <td>%<?= $r['bh_commission_rate'] ?></td>
                        <td><?= formatMoney($r['per_file_fee']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning btn-edit" data-item='<?= json_encode($r) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?page=yonlendiren&delete=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-name="<?= e($r['name']) ?>">
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

<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="itemId" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Yeni Yönlendiren</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Ad *</label>
                            <input type="text" class="form-control" name="name" id="itemName" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tür</label>
                            <select class="form-select" name="type" id="itemType">
                                <option value="YÖNLENDİREN">Yönlendiren</option>
                                <option value="SERVİS">Servis</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">İletişim Kişisi</label>
                            <input type="text" class="form-control" name="contact_person" id="itemContact">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="phone" id="itemPhone">
                        </div>
                        <div class="col-12">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" id="itemEmail">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ADK Komisyon %</label>
                            <input type="number" class="form-control" name="adk_commission_rate" id="itemAdkRate" value="0" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BH Komisyon %</label>
                            <input type="number" class="form-control" name="bh_commission_rate" id="itemBhRate" value="0" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dosya Başı (₺)</label>
                            <input type="number" class="form-control" name="per_file_fee" id="itemFee" value="0" step="100">
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
        document.getElementById('modalTitle').textContent = 'Yönlendiren Düzenle';
        document.getElementById('itemName').value = item.name || '';
        document.getElementById('itemType').value = item.type || 'YÖNLENDİREN';
        document.getElementById('itemContact').value = item.contact_person || '';
        document.getElementById('itemPhone').value = item.phone || '';
        document.getElementById('itemEmail').value = item.email || '';
        document.getElementById('itemAdkRate').value = item.adk_commission_rate || 0;
        document.getElementById('itemBhRate').value = item.bh_commission_rate || 0;
        document.getElementById('itemFee').value = item.per_file_fee || 0;
        document.getElementById('itemNotes').value = item.notes || '';
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    });
});
</script>
