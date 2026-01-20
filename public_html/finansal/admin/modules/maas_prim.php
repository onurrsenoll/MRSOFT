<?php
/**
 * MR HASAR DANIŞMANLIK - Maaş ve Prim
 */
$stmt = $pdo->query("SELECT sp.*, p.name as personnel_name FROM salary_payments sp JOIN personnel p ON sp.personnel_id = p.id ORDER BY sp.payment_date DESC LIMIT 100");
$payments = $stmt->fetchAll();
?>
<div class="page-header"><div class="d-flex justify-content-between align-items-center">
<h1><i class="bi bi-cash-coin me-2"></i>Maaş / Prim</h1>
<button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Yeni Ödeme</button>
</div></div>
<div class="card"><div class="card-body">
<table class="table table-hover datatable"><thead><tr><th>Personel</th><th>Dönem</th><th>Tür</th><th>Brüt</th><th>Net</th><th>Tarih</th></tr></thead><tbody>
<?php foreach ($payments as $p): ?><tr>
<td><strong><?= e($p['personnel_name']) ?></strong></td>
<td><?= e($p['period'] ?? '-') ?></td>
<td><span class="badge bg-<?= $p['payment_type'] === 'MAAŞ' ? 'primary' : ($p['payment_type'] === 'PRİM' ? 'success' : 'warning') ?>"><?= e($p['payment_type']) ?></span></td>
<td><?= formatMoney($p['gross_amount']) ?></td>
<td><strong><?= formatMoney($p['net_amount']) ?></strong></td>
<td><?= formatDate($p['payment_date']) ?></td>
</tr><?php endforeach; ?>
</tbody></table></div></div>
