<?php
/**
 * MR HASAR DANIŞMANLIK - İş Ortakları Raporu
 */
$stmt = $pdo->query("SELECT p.*, (SELECT COUNT(*) FROM cases c JOIN referrers r ON c.referrer_id=r.id WHERE r.name=p.name) as case_count,
    (SELECT COALESCE(SUM(amount),0) FROM partner_transactions WHERE partner_id=p.id AND transaction_type IN ('ALACAK','TAHSİLAT')) as total_receivable,
    (SELECT COALESCE(SUM(amount),0) FROM partner_transactions WHERE partner_id=p.id AND transaction_type IN ('BORÇ','ÖDEME')) as total_payable
    FROM partners p WHERE p.is_active=1 ORDER BY p.name");
$partners = $stmt->fetchAll();
?>
<div class="page-header"><h1><i class="bi bi-pie-chart me-2"></i>İş Ortakları Raporu</h1></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0 datatable"><thead><tr>
<th>Ortak</th><th>Tür</th><th>Dosya Sayısı</th><th class="text-end">Alacak</th><th class="text-end">Borç</th><th class="text-end">Bakiye</th>
</tr></thead><tbody>
<?php foreach ($partners as $p): ?>
<tr><td><strong><?= e($p['name']) ?></strong></td>
<td><span class="badge bg-secondary"><?= e($p['type']) ?></span></td>
<td><?= $p['case_count'] ?></td>
<td class="text-end text-success"><?= formatMoney($p['total_receivable']) ?></td>
<td class="text-end text-danger"><?= formatMoney($p['total_payable']) ?></td>
<td class="text-end <?=$p['balance']>=0?'text-success':'text-danger'?>"><strong><?= formatMoney($p['balance']) ?></strong></td>
</tr><?php endforeach; ?>
</tbody></table></div></div></div>
