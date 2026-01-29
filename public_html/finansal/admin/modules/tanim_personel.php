<?php
/**
 * MR HASAR DANIŞMANLIK - Personel Yönetimi
 * HERZAMAN FARKEDER
 */

// Silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE personnel SET is_active = 0 WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Personel pasife alındı.');
    header('Location: index.php?page=tanim_personel');
    exit;
}

// Ekleme/Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'name' => clean($_POST['name']),
        'tc_no' => clean($_POST['tc_no'] ?? ''),
        'birth_date' => !empty($_POST['birth_date']) ? $_POST['birth_date'] : null,
        'phone' => clean($_POST['phone'] ?? ''),
        'email' => clean($_POST['email'] ?? ''),
        'address' => clean($_POST['address'] ?? ''),
        'position' => clean($_POST['position'] ?? ''),
        'department' => clean($_POST['department'] ?? ''),
        'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        'salary' => floatval($_POST['salary'] ?? 0),
        'iban' => clean($_POST['iban'] ?? ''),
        'bank_name' => clean($_POST['bank_name'] ?? ''),
    ];

    if (empty($data['name'])) {
        setFlash('error', 'Ad Soyad zorunludur.');
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE personnel SET name=?, tc_no=?, birth_date=?, phone=?, email=?, address=?, position=?, department=?, start_date=?, end_date=?, salary=?, iban=?, bank_name=? WHERE id=?");
            $stmt->execute([
                $data['name'], $data['tc_no'], $data['birth_date'], $data['phone'], $data['email'],
                $data['address'], $data['position'], $data['department'], $data['start_date'],
                $data['end_date'], $data['salary'], $data['iban'], $data['bank_name'], $id
            ]);
            setFlash('success', 'Personel güncellendi.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO personnel (name, tc_no, birth_date, phone, email, address, position, department, start_date, end_date, salary, iban, bank_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'], $data['tc_no'], $data['birth_date'], $data['phone'], $data['email'],
                $data['address'], $data['position'], $data['department'], $data['start_date'],
                $data['end_date'], $data['salary'], $data['iban'], $data['bank_name']
            ]);
            setFlash('success', 'Personel eklendi.');
        }
        header('Location: index.php?page=tanim_personel');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM personnel WHERE is_active = 1 ORDER BY name");
$personnel = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-person-vcard me-2"></i>Personel</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#personelModal">
            <i class="bi bi-plus-lg me-1"></i> Yeni Personel
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Pozisyon</th>
                        <th>Departman</th>
                        <th>Telefon</th>
                        <th>Başlangıç</th>
                        <th>Maaş</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personnel as $p): ?>
                    <tr>
                        <td><strong><?= e($p['name']) ?></strong></td>
                        <td><?= e($p['position'] ?? '-') ?></td>
                        <td><?= e($p['department'] ?? '-') ?></td>
                        <td><?= e($p['phone'] ?? '-') ?></td>
                        <td><?= formatDate($p['start_date']) ?></td>
                        <td><?= formatMoney($p['salary']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning btn-edit" data-item='<?= json_encode($p) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?page=tanim_personel&delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-name="<?= e($p['name']) ?>">
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

<div class="modal fade" id="personelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="itemId" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Yeni Personel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" class="form-control" name="name" id="itemName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TC Kimlik No</label>
                            <input type="text" class="form-control" name="tc_no" id="itemTcNo" maxlength="11">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pozisyon</label>
                            <input type="text" class="form-control" name="position" id="itemPosition">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Departman</label>
                            <input type="text" class="form-control" name="department" id="itemDepartment">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="phone" id="itemPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" id="itemEmail">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Doğum Tarihi</label>
                            <input type="date" class="form-control" name="birth_date" id="itemBirthDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">İşe Başlama</label>
                            <input type="date" class="form-control" name="start_date" id="itemStartDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">İşten Ayrılma</label>
                            <input type="date" class="form-control" name="end_date" id="itemEndDate">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adres</label>
                            <textarea class="form-control" name="address" id="itemAddress" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maaş (₺)</label>
                            <input type="number" class="form-control" name="salary" id="itemSalary" value="0" step="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Banka</label>
                            <input type="text" class="form-control" name="bank_name" id="itemBankName">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">IBAN</label>
                            <input type="text" class="form-control" name="iban" id="itemIban" maxlength="32">
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
        document.getElementById('modalTitle').textContent = 'Personel Düzenle';
        document.getElementById('itemName').value = item.name || '';
        document.getElementById('itemTcNo').value = item.tc_no || '';
        document.getElementById('itemPosition').value = item.position || '';
        document.getElementById('itemDepartment').value = item.department || '';
        document.getElementById('itemPhone').value = item.phone || '';
        document.getElementById('itemEmail').value = item.email || '';
        document.getElementById('itemBirthDate').value = item.birth_date || '';
        document.getElementById('itemStartDate').value = item.start_date || '';
        document.getElementById('itemEndDate').value = item.end_date || '';
        document.getElementById('itemAddress').value = item.address || '';
        document.getElementById('itemSalary').value = item.salary || 0;
        document.getElementById('itemBankName').value = item.bank_name || '';
        document.getElementById('itemIban').value = item.iban || '';
        new bootstrap.Modal(document.getElementById('personelModal')).show();
    });
});

// Reset form when modal is closed
document.getElementById('personelModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('itemId').value = 0;
    document.getElementById('modalTitle').textContent = 'Yeni Personel';
    this.querySelector('form').reset();
});
</script>
