<?php
/**
 * MR HASAR DANIŞMANLIK - Finansal Özet Raporu
 * HERZAMAN FARKEDER
 */

// Tarih aralığı
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Toplam tahsilat
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM case_payments WHERE payment_type = 'TAHSİLAT' AND payment_date BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$totalCollection = $stmt->fetchColumn();

// Toplam masraf
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM case_expenses WHERE expense_date BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$totalExpense = $stmt->fetchColumn();

// Kasa giriş/çıkış
$stmt = $pdo->prepare("SELECT transaction_type, COALESCE(SUM(amount), 0) as total FROM cash_transactions WHERE transaction_date BETWEEN ? AND ? GROUP BY transaction_type");
$stmt->execute([$startDate, $endDate]);
$cashMovements = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Aylık tahsilat trendi
$stmt = $pdo->prepare("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total
                       FROM case_payments WHERE payment_type = 'TAHSİLAT'
                       AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                       GROUP BY month ORDER BY month");
$stmt->execute();
$monthlyTrend = $stmt->fetchAll();

// Dosya türüne göre tahsilat
$stmt = $pdo->prepare("SELECT c.case_type, COALESCE(SUM(cp.amount), 0) as total
                       FROM case_payments cp
                       JOIN cases c ON cp.case_id = c.id
                       WHERE cp.payment_type = 'TAHSİLAT' AND cp.payment_date BETWEEN ? AND ?
                       GROUP BY c.case_type");
$stmt->execute([$startDate, $endDate]);
$collectionByType = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Masraf kategorileri
$stmt = $pdo->prepare("SELECT et.name, COALESCE(SUM(ce.amount), 0) as total
                       FROM case_expenses ce
                       LEFT JOIN expense_types et ON ce.expense_type_id = et.id
                       WHERE ce.expense_date BETWEEN ? AND ?
                       GROUP BY ce.expense_type_id
                       ORDER BY total DESC LIMIT 10");
$stmt->execute([$startDate, $endDate]);
$expenseCategories = $stmt->fetchAll();

// Net kar/zarar
$netProfit = $totalCollection - $totalExpense;
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-bar-chart-line me-2"></i>Finansal Özet Raporu</h1>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="printElement('reportArea')">
                <i class="bi bi-printer me-1"></i> Yazdır
            </button>
        </div>
    </div>
</div>

<!-- Filtre -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="rapor_finansal">
            <div class="col-auto">
                <label class="form-label mb-0">Başlangıç:</label>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control form-control-sm datepicker" name="start_date" value="<?= formatDate($startDate, 'd.m.Y') ?>">
            </div>
            <div class="col-auto">
                <label class="form-label mb-0">Bitiş:</label>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control form-control-sm datepicker" name="end_date" value="<?= formatDate($endDate, 'd.m.Y') ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filtrele</button>
            </div>
        </form>
    </div>
</div>

<div id="reportArea">
    <!-- Özet Kartlar -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card success">
                <i class="bi bi-cash-stack stat-icon"></i>
                <div class="stat-value"><?= formatMoney($totalCollection) ?></div>
                <div class="stat-label">Toplam Tahsilat</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card danger">
                <i class="bi bi-receipt stat-icon"></i>
                <div class="stat-value"><?= formatMoney($totalExpense) ?></div>
                <div class="stat-label">Toplam Masraf</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <i class="bi bi-arrow-down-up stat-icon"></i>
                <div class="stat-value"><?= formatMoney($cashMovements['GİRİŞ'] ?? 0) ?></div>
                <div class="stat-label">Kasa Girişi</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card <?= $netProfit >= 0 ? 'primary' : 'warning' ?>">
                <i class="bi bi-graph-up stat-icon"></i>
                <div class="stat-value"><?= formatMoney($netProfit) ?></div>
                <div class="stat-label">Net Kar/Zarar</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Aylık Trend -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-graph-up me-2"></i>Aylık Tahsilat Trendi
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Dosya Türüne Göre -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-pie-chart me-2"></i>Dosya Türüne Göre
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="200"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <tr>
                                <td>ADK</td>
                                <td class="text-end"><?= formatMoney($collectionByType['ADK'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <td>BH</td>
                                <td class="text-end"><?= formatMoney($collectionByType['BH'] ?? 0) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Masraf Kategorileri -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list-ul me-2"></i>Masraf Kategorileri
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="text-end">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenseCategories as $ec): ?>
                            <tr>
                                <td><?= e($ec['name'] ?? 'Diğer') ?></td>
                                <td class="text-end"><?= formatMoney($ec['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td><strong>TOPLAM</strong></td>
                                <td class="text-end"><strong><?= formatMoney($totalExpense) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Özet -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calculator me-2"></i>Dönem Özeti
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td>Dönem:</td>
                            <td class="text-end"><?= formatDate($startDate) ?> - <?= formatDate($endDate) ?></td>
                        </tr>
                        <tr class="text-success">
                            <td>Toplam Tahsilat:</td>
                            <td class="text-end"><strong><?= formatMoney($totalCollection) ?></strong></td>
                        </tr>
                        <tr class="text-danger">
                            <td>Toplam Masraf:</td>
                            <td class="text-end"><strong><?= formatMoney($totalExpense) ?></strong></td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Net Kar/Zarar:</strong></td>
                            <td class="text-end <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                <strong><?= formatMoney($netProfit) ?></strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    const trendData = <?= json_encode($monthlyTrend) ?>;
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(t => t.month),
            datasets: [{
                label: 'Tahsilat',
                data: trendData.map(t => t.total),
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '₺' + v.toLocaleString('tr-TR') }
                }
            }
        }
    });

    // Type Chart
    const typeData = <?= json_encode($collectionByType) ?>;
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: ['ADK', 'BH'],
            datasets: [{
                data: [typeData['ADK'] || 0, typeData['BH'] || 0],
                backgroundColor: ['#3b82f6', '#22c55e']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>
