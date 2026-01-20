<?php
/**
 * MR HASAR DANIŞMANLIK - Sigorta Şirketleri Tanımlama
 * HERZAMAN FARKEDER
 */

// Silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE insurance_companies SET is_active = 0 WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Sigorta şirketi pasife alındı.');
    header('Location: index.php?page=tanim_sigorta');
    exit;
}

// Kayıt işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = clean($_POST['name']);
    $shortName = clean($_POST['short_name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $address = clean($_POST['address'] ?? '');

    if (empty($name)) {
        setFlash('error', 'Şirket adı zorunludur.');
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE insurance_companies SET name=?, short_name=?, phone=?, email=?, address=? WHERE id=?");
            $stmt->execute([$name, $shortName, $phone, $email, $address, $id]);
            setFlash('success', 'Sigorta şirketi güncellendi.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO insurance_companies (name, short_name, phone, email, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $shortName, $phone, $email, $address]);
            setFlash('success', 'Yeni sigorta şirketi eklendi.');
        }
        header('Location: index.php?page=tanim_sigorta');
        exit;
    }
}

// Liste
$stmt = $pdo->query("SELECT * FROM insurance_companies WHERE is_active = 1 ORDER BY name");
$companies = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-shield-check me-2"></i>Sigorta Şirketleri</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item">Tanımlamalar</li>
                    <li class="breadcrumb-item active">Sigorta Şirketleri</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sigortaModal">
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
                        <th>Şirket Adı</th>
                        <th>Kısa Ad</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th class="text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $c): ?>
                    <tr>
                        <td><strong><?= e($c['name']) ?></strong></td>
                        <td><?= e($c['short_name'] ?? '-') ?></td>
                        <td><?= e($c['phone'] ?? '-') ?></td>
                        <td><?= e($c['email'] ?? '-') ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning btn-edit"
                                    data-item='<?= json_encode($c) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?page=tanim_sigorta&delete=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-danger btn-delete" data-name="<?= e($c['name']) ?>">
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

<!-- Modal -->
<div class="modal fade" id="sigortaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="itemId" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Yeni Sigorta Şirketi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Şirket Adı *</label>
                        <input type="text" class="form-control" name="name" id="itemName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kısa Ad</label>
                        <input type="text" class="form-control" name="short_name" id="itemShortName" maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="text" class="form-control" name="phone" id="itemPhone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" class="form-control" name="email" id="itemEmail">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea class="form-control" name="address" id="itemAddress" rows="2"></textarea>
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
        document.getElementById('modalTitle').textContent = 'Sigorta Şirketi Düzenle';
        document.getElementById('itemName').value = item.name || '';
        document.getElementById('itemShortName').value = item.short_name || '';
        document.getElementById('itemPhone').value = item.phone || '';
        document.getElementById('itemEmail').value = item.email || '';
        document.getElementById('itemAddress').value = item.address || '';
        new bootstrap.Modal(document.getElementById('sigortaModal')).show();
    });
});

document.getElementById('sigortaModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('itemId').value = 0;
    document.getElementById('modalTitle').textContent = 'Yeni Sigorta Şirketi';
    this.querySelector('form').reset();
});
</script>
