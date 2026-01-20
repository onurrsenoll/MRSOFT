<?php
/**
 * MR HASAR DANIŞMANLIK - Cari Hesaplar
 */
$stmt = $pdo->query("SELECT * FROM cari_accounts WHERE is_active = 1 ORDER BY name");
$cariler = $stmt->fetchAll();
?>
<div class="page-header"><div class="d-flex justify-content-between align-items-center">
<h1><i class="bi bi-journal-text me-2"></i>Cari Hesaplar</h1>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cariModal"><i class="bi bi-plus-lg me-1"></i>Yeni Cari</button>
</div></div>
<div class="card"><div class="card-body">
<table class="table table-hover datatable"><thead><tr><th>Cari Adı</th><th>Tür</th><th>Telefon</th><th>Bakiye</th><th>İşlem</th></tr></thead><tbody>
<?php foreach ($cariler as $c): ?><tr>
<td><strong><?= e($c['name']) ?></strong></td>
<td><span class="badge bg-secondary"><?= e($c['type']) ?></span></td>
<td><?= e($c['phone'] ?? '-') ?></td>
<td class="<?= $c['balance'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= formatMoney($c['balance']) ?></td>
<td><button class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></button></td>
</tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="modal fade" id="cariModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5>Yeni Cari</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><p class="text-muted">Cari hesap formu...</p></div>
</div></div></div>
