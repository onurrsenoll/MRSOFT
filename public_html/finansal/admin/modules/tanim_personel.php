<?php
/**
 * MR HASAR DANIŞMANLIK - Personel Yönetimi
 */
$stmt = $pdo->query("SELECT * FROM personnel WHERE is_active = 1 ORDER BY name");
$personnel = $stmt->fetchAll();
?>
<div class="page-header"><div class="d-flex justify-content-between align-items-center">
<h1><i class="bi bi-person-vcard me-2"></i>Personel</h1>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#personelModal"><i class="bi bi-plus-lg me-1"></i>Yeni Personel</button>
</div></div>
<div class="card"><div class="card-body">
<table class="table table-hover datatable"><thead><tr><th>Ad Soyad</th><th>Pozisyon</th><th>Telefon</th><th>Başlangıç</th><th>Maaş</th><th>İşlem</th></tr></thead><tbody>
<?php foreach ($personnel as $p): ?><tr>
<td><strong><?= e($p['name']) ?></strong></td>
<td><?= e($p['position'] ?? '-') ?></td>
<td><?= e($p['phone'] ?? '-') ?></td>
<td><?= formatDate($p['start_date']) ?></td>
<td><?= formatMoney($p['salary']) ?></td>
<td><button class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></button></td>
</tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="modal fade" id="personelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5>Yeni Personel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><p class="text-muted">Personel formu burada...</p></div>
</div></div></div>
