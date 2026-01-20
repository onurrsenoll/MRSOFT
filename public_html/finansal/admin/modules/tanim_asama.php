<?php
/**
 * MR HASAR DANIŞMANLIK - Aşama Durumları Tanımlama
 * HERZAMAN FARKEDER
 */

// Silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE stages SET is_active = 0 WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Aşama pasife alındı.');
    header('Location: index.php?page=tanim_asama');
    exit;
}

// Kayıt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = clean($_POST['name']);
    $color = clean($_POST['color'] ?? '#3b82f6');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (empty($name)) {
        setFlash('error', 'Aşama adı zorunludur.');
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE stages SET name=?, color=?, sort_order=? WHERE id=?");
            $stmt->execute([$name, $color, $sortOrder, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO stages (name, color, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, $color, $sortOrder]);
        }
        setFlash('success', 'Aşama kaydedildi.');
        header('Location: index.php?page=tanim_asama');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM stages WHERE is_active = 1 ORDER BY sort_order");
$stages = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-diagram-3 me-2"></i>Aşama Durumları</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item">Tanımlamalar</li>
                    <li class="breadcrumb-item active">Aşamalar</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
            <i class="bi bi-plus-lg me-1"></i> Yeni Ekle
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="60">Sıra</th>
                        <th>Aşama Adı</th>
                        <th>Renk</th>
                        <th>Önizleme</th>
                        <th class="text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stages as $s): ?>
                    <tr>
                        <td><?= $s['sort_order'] ?></td>
                        <td><strong><?= e($s['name']) ?></strong></td>
                        <td><code><?= e($s['color']) ?></code></td>
                        <td>
                            <span class="stage-badge" style="background-color: <?= $s['color'] ?>">
                                <?= e($s['name']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning btn-edit"
                                    data-item='<?= json_encode($s) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?page=tanim_asama&delete=<?= $s['id'] ?>"
                               class="btn btn-sm btn-outline-danger btn-delete" data-name="<?= e($s['name']) ?>">
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
                    <h5 class="modal-title" id="modalTitle">Yeni Aşama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Aşama Adı *</label>
                        <input type="text" class="form-control" name="name" id="itemName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Renk</label>
                        <input type="color" class="form-control form-control-color" name="color" id="itemColor" value="#3b82f6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sıra No</label>
                        <input type="number" class="form-control" name="sort_order" id="itemSort" value="0">
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
        document.getElementById('modalTitle').textContent = 'Aşama Düzenle';
        document.getElementById('itemName').value = item.name || '';
        document.getElementById('itemColor').value = item.color || '#3b82f6';
        document.getElementById('itemSort').value = item.sort_order || 0;
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    });
});

document.getElementById('itemModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('itemId').value = 0;
    document.getElementById('modalTitle').textContent = 'Yeni Aşama';
    this.querySelector('form').reset();
});
</script>
