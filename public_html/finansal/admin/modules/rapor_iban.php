<?php
/**
 * MR HASAR DANIŞMANLIK - Mağdur IBAN Listesi
 */
$stmt = $pdo->query("SELECT file_no, client_name, tc_no, phone, iban, bank_name, calculated_amount, paid_amount FROM cases WHERE status='AKTIF' AND iban IS NOT NULL AND iban != '' ORDER BY file_no");
$clients = $stmt->fetchAll();
?>
<div class="page-header"><div class="d-flex justify-content-between align-items-center">
<h1><i class="bi bi-bank me-2"></i>Mağdur IBAN Listesi</h1>
<button class="btn btn-outline-success" onclick="exportToExcel('ibanTable','iban_listesi')"><i class="bi bi-file-excel me-1"></i>Excel</button>
</div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0 datatable" id="ibanTable"><thead><tr>
<th>Dosya No</th><th>Ad Soyad</th><th>T.C.</th><th>Telefon</th><th>Banka</th><th>IBAN</th><th class="text-end">Hesaplanan</th><th class="text-end">Ödenen</th>
</tr></thead><tbody>
<?php foreach ($clients as $c): ?>
<tr><td><?= e($c['file_no']) ?></td><td><?= e($c['client_name']) ?></td><td><?= e($c['tc_no']??'-') ?></td>
<td><?= e($c['phone']??'-') ?></td><td><?= e($c['bank_name']??'-') ?></td><td><code><?= e($c['iban']) ?></code></td>
<td class="text-end"><?= formatMoney($c['calculated_amount']) ?></td><td class="text-end"><?= formatMoney($c['paid_amount']) ?></td>
</tr><?php endforeach; ?>
</tbody></table></div></div></div>
