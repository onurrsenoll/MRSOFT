<?php
/**
 * MR HASAR DANIŞMANLIK - Evrak Türleri
 */
if (isset($_GET['delete'])) { $pdo->prepare("UPDATE document_types SET is_active = 0 WHERE id = ?")->execute([(int)$_GET['delete']]); header('Location: index.php?page=tanim_evrak'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $name = clean($_POST['name']);
    if ($id > 0) $pdo->prepare("UPDATE document_types SET name=? WHERE id=?")->execute([$name, $id]);
    else $pdo->prepare("INSERT INTO document_types (name) VALUES (?)")->execute([$name]);
    header('Location: index.php?page=tanim_evrak'); exit;
}
$items = $pdo->query("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name")->fetchAll();
?>
<div class="page-header"><h1><i class="bi bi-file-earmark me-2"></i>Evrak Türleri</h1>
<button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#itemModal"><i class="bi bi-plus-lg"></i> Yeni</button></div>
<div class="card"><div class="card-body"><table class="table table-hover datatable"><thead><tr><th>Evrak Türü</th><th>İşlem</th></tr></thead><tbody>
<?php foreach ($items as $i): ?><tr><td><?= e($i['name']) ?></td><td>
<button class="btn btn-sm btn-outline-warning btn-edit" data-id="<?= $i['id'] ?>" data-name="<?= e($i['name']) ?>"><i class="bi bi-pencil"></i></button>
<a href="?page=tanim_evrak&delete=<?= $i['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
</td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="modal fade" id="itemModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST">
<input type="hidden" name="id" id="itemId" value="0">
<div class="modal-header"><h5 class="modal-title">Evrak Türü</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><input type="text" class="form-control" name="name" id="itemName" required placeholder="Evrak Türü"></div>
<div class="modal-footer"><button type="submit" class="btn btn-primary">Kaydet</button></div>
</form></div></div></div>
<script>document.querySelectorAll('.btn-edit').forEach(b=>b.onclick=()=>{document.getElementById('itemId').value=b.dataset.id;document.getElementById('itemName').value=b.dataset.name;new bootstrap.Modal(document.getElementById('itemModal')).show();});</script>
