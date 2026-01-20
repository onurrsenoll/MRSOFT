<?php
/**
 * MR HASAR DANIŞMANLIK - Gelir/Gider Takibi
 */
$stmt = $pdo->query("SELECT ie.*, c.name as cash_name FROM income_expense ie LEFT JOIN cashes c ON ie.cash_id = c.id ORDER BY ie.transaction_date DESC LIMIT 100");
$records = $stmt->fetchAll();
$totalIncome = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM income_expense WHERE type='GELİR'")->fetchColumn();
$totalExpense = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM income_expense WHERE type='GİDER'")->fetchColumn();
?>
<div class="page-header"><div class="d-flex justify-content-between align-items-center">
<h1><i class="bi bi-graph-up-arrow me-2"></i>Gelir / Gider</h1>
<button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Yeni Kayıt</button>
</div></div>

<div class="row mb-4">
<div class="col-md-4"><div class="stat-card success"><i class="bi bi-arrow-down-circle stat-icon"></i><div class="stat-value"><?= formatMoney($totalIncome) ?></div><div class="stat-label">Toplam Gelir</div></div></div>
<div class="col-md-4"><div class="stat-card danger"><i class="bi bi-arrow-up-circle stat-icon"></i><div class="stat-value"><?= formatMoney($totalExpense) ?></div><div class="stat-label">Toplam Gider</div></div></div>
<div class="col-md-4"><div class="stat-card <?= ($totalIncome-$totalExpense) >= 0 ? 'primary' : 'warning' ?>"><i class="bi bi-calculator stat-icon"></i><div class="stat-value"><?= formatMoney($totalIncome-$totalExpense) ?></div><div class="stat-label">Net</div></div></div>
</div>

<div class="card"><div class="card-body">
<table class="table table-hover datatable"><thead><tr><th>Tarih</th><th>Tür</th><th>Kategori</th><th>Açıklama</th><th>Kasa</th><th class="text-end">Tutar</th></tr></thead><tbody>
<?php foreach ($records as $r): ?><tr>
<td><?= formatDate($r['transaction_date']) ?></td>
<td><span class="badge bg-<?= $r['type'] === 'GELİR' ? 'success' : 'danger' ?>"><?= e($r['type']) ?></span></td>
<td><?= e($r['category'] ?? '-') ?></td>
<td><?= e($r['description'] ?? '-') ?></td>
<td><?= e($r['cash_name'] ?? '-') ?></td>
<td class="text-end text-<?= $r['type'] === 'GELİR' ? 'success' : 'danger' ?>"><?= formatMoney($r['amount']) ?></td>
</tr><?php endforeach; ?>
</tbody></table></div></div>
