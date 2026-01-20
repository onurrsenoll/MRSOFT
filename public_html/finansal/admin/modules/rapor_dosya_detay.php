<?php
/**
 * MR HASAR DANIŞMANLIK - Dosya Detay Raporu
 */
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT c.*, s.name as stage_name, s.color as stage_color, ic.name as insurance_name,
    (SELECT COALESCE(SUM(amount),0) FROM case_payments WHERE case_id=c.id AND payment_type='TAHSİLAT') as total_collection,
    (SELECT COALESCE(SUM(amount),0) FROM case_expenses WHERE case_id=c.id) as total_expense
    FROM cases c LEFT JOIN stages s ON c.stage_id=s.id LEFT JOIN insurance_companies ic ON c.insurance_company_id=ic.id
    WHERE c.created_at BETWEEN ? AND ? ORDER BY c.created_at DESC");
$stmt->execute([$startDate, $endDate . ' 23:59:59']);
$cases = $stmt->fetchAll();
?>
<div class="page-header"><div class="d-flex justify-content-between align-items-center">
<h1><i class="bi bi-file-earmark-bar-graph me-2"></i>Dosya Detay Raporu</h1>
<button class="btn btn-outline-success" onclick="exportToExcel('reportTable','dosya_rapor')"><i class="bi bi-file-excel me-1"></i>Excel</button>
</div></div>
<div class="card mb-4"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-center"><input type="hidden" name="page" value="rapor_dosya_detay">
<div class="col-auto"><input type="text" class="form-control form-control-sm datepicker" name="start_date" value="<?= formatDate($startDate,'d.m.Y') ?>"></div>
<div class="col-auto"><input type="text" class="form-control form-control-sm datepicker" name="end_date" value="<?= formatDate($endDate,'d.m.Y') ?>"></div>
<div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Filtrele</button></div>
</form></div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0" id="reportTable"><thead><tr>
<th>Dosya No</th><th>Müvekkil</th><th>Tür</th><th>Sigorta</th><th>Aşama</th><th class="text-end">Tahsilat</th><th class="text-end">Masraf</th><th class="text-end">Net</th>
</tr></thead><tbody>
<?php $totalC=$totalE=0; foreach ($cases as $c): $net=$c['total_collection']-$c['total_expense']; $totalC+=$c['total_collection']; $totalE+=$c['total_expense']; ?>
<tr><td><a href="index.php?page=dosya_detay&id=<?=$c['id']?>"><?= e($c['file_no']) ?></a></td>
<td><?= e($c['client_name']) ?></td>
<td><span class="badge bg-<?=$c['case_type']==='ADK'?'primary':'success'?>"><?= e($c['case_type']) ?></span></td>
<td><?= e($c['insurance_name']??'-') ?></td>
<td><?php if($c['stage_name']):?><span class="stage-badge" style="background:<?=$c['stage_color']?>"><?= e($c['stage_name']) ?></span><?php else:?>-<?php endif;?></td>
<td class="text-end text-success"><?= formatMoney($c['total_collection']) ?></td>
<td class="text-end text-danger"><?= formatMoney($c['total_expense']) ?></td>
<td class="text-end <?=$net>=0?'text-success':'text-danger'?>"><?= formatMoney($net) ?></td>
</tr><?php endforeach; ?>
</tbody><tfoot><tr class="table-light"><td colspan="5"><strong>TOPLAM</strong></td>
<td class="text-end"><strong><?= formatMoney($totalC) ?></strong></td>
<td class="text-end"><strong><?= formatMoney($totalE) ?></strong></td>
<td class="text-end"><strong><?= formatMoney($totalC-$totalE) ?></strong></td></tr></tfoot>
</table></div></div></div>
