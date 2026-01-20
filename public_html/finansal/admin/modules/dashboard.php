<?php
/**
 * MR HASAR DANIŞMANLIK - Dashboard
 * HERZAMAN FARKEDER
 */

// İstatistikleri al
$stats = getDashboardStats($pdo);

// Son 10 dosya
$stmt = $pdo->query("SELECT c.*, s.name as stage_name, s.color as stage_color,
                            ic.name as insurance_name
                     FROM cases c
                     LEFT JOIN stages s ON c.stage_id = s.id
                     LEFT JOIN insurance_companies ic ON c.insurance_company_id = ic.id
                     ORDER BY c.created_at DESC LIMIT 10");
$recentCases = $stmt->fetchAll();

// Son 5 CRM lead
$stmt = $pdo->query("SELECT * FROM crm_leads ORDER BY created_at DESC LIMIT 5");
$recentLeads = $stmt->fetchAll();

// Aşamalara göre dosya dağılımı
$stmt = $pdo->query("SELECT s.name, s.color, COUNT(c.id) as count
                     FROM stages s
                     LEFT JOIN cases c ON s.id = c.stage_id AND c.status = 'AKTIF'
                     WHERE s.is_active = 1
                     GROUP BY s.id
                     ORDER BY s.sort_order");
$stageStats = $stmt->fetchAll();

// Bu ayki tahsilatlar
$stmt = $pdo->query("SELECT DATE(payment_date) as date, SUM(amount) as total
                     FROM case_payments
                     WHERE payment_type = 'TAHSİLAT'
                     AND MONTH(payment_date) = MONTH(CURDATE())
                     AND YEAR(payment_date) = YEAR(CURDATE())
                     GROUP BY DATE(payment_date)
                     ORDER BY date");
$monthlyPayments = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-house-door me-2"></i>Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">Anasayfa</li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="text-muted">
                <i class="bi bi-calendar3 me-1"></i>
                <?= date('d.m.Y H:i') ?>
            </span>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card primary">
            <i class="bi bi-folder2-open stat-icon"></i>
            <div class="stat-value"><?= number_format($stats['total_cases']) ?></div>
            <div class="stat-label">Toplam Dosya</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card success">
            <i class="bi bi-folder-check stat-icon"></i>
            <div class="stat-value"><?= number_format($stats['active_cases']) ?></div>
            <div class="stat-label">Aktif Dosya</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card info">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-value"><?= formatMoney($stats['monthly_collection']) ?></div>
            <div class="stat-label">Bu Ay Tahsilat</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card warning">
            <i class="bi bi-people stat-icon"></i>
            <div class="stat-value"><?= number_format($stats['pending_leads']) ?></div>
            <div class="stat-label">Bekleyen Lead</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Aşama Dağılımı -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pie-chart me-2"></i>Aşama Dağılımı</span>
            </div>
            <div class="card-body">
                <canvas id="stageChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Aylık Tahsilat Grafiği -->
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2"></i>Bu Ay Tahsilatlar</span>
            </div>
            <div class="card-body">
                <canvas id="paymentChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Son Dosyalar -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder2-open me-2"></i>Son Eklenen Dosyalar</span>
                <a href="index.php?page=dosyalar" class="btn btn-sm btn-outline-primary">
                    Tümünü Gör
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Dosya No</th>
                                <th>Müvekkil</th>
                                <th>Tür</th>
                                <th>Sigorta</th>
                                <th>Aşama</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCases)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Henüz dosya bulunmuyor
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentCases as $case): ?>
                            <tr class="cursor-pointer" onclick="location.href='index.php?page=dosya_detay&id=<?= $case['id'] ?>'">
                                <td>
                                    <strong><?= e($case['file_no']) ?></strong>
                                </td>
                                <td><?= e($case['client_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $case['case_type'] === 'ADK' ? 'primary' : 'success' ?>">
                                        <?= e($case['case_type']) ?>
                                    </span>
                                </td>
                                <td><?= e($case['insurance_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($case['stage_name']): ?>
                                    <span class="stage-badge" style="background-color: <?= $case['stage_color'] ?>">
                                        <?= e($case['stage_name']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatDate($case['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Son Leadler -->
    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Son CRM Leadler</span>
                <a href="index.php?page=crm" class="btn btn-sm btn-outline-primary">
                    Tümünü Gör
                </a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($recentLeads)): ?>
                    <li class="list-group-item text-center text-muted py-4">
                        Henüz lead bulunmuyor
                    </li>
                    <?php else: ?>
                    <?php foreach ($recentLeads as $lead): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= e($lead['name']) ?></strong>
                                <div class="small text-muted">
                                    <i class="bi bi-telephone me-1"></i><?= e($lead['phone'] ?? '-') ?>
                                </div>
                            </div>
                            <span class="badge bg-<?php
                                echo match($lead['status']) {
                                    'BEKLEMEDE' => 'warning',
                                    'İLGİLİ' => 'info',
                                    'DOSYA AÇILDI' => 'success',
                                    'İPTAL' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?= e($lead['status']) ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Hızlı İşlemler -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Hızlı İşlemler
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php?page=dosya_ekle" class="btn btn-primary">
                        <i class="bi bi-folder-plus me-2"></i>Yeni Dosya Ekle
                    </a>
                    <a href="index.php?page=crm" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i>Yeni Lead Ekle
                    </a>
                    <a href="index.php?page=adk_hesaplama" class="btn btn-outline-primary">
                        <i class="bi bi-calculator me-2"></i>ADK Hesapla
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aşama Dağılımı Grafiği
    const stageData = <?= json_encode($stageStats) ?>;
    if (stageData.length > 0) {
        new Chart(document.getElementById('stageChart'), {
            type: 'doughnut',
            data: {
                labels: stageData.map(s => s.name),
                datasets: [{
                    data: stageData.map(s => s.count),
                    backgroundColor: stageData.map(s => s.color),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // Aylık Tahsilat Grafiği
    const paymentData = <?= json_encode($monthlyPayments) ?>;
    new Chart(document.getElementById('paymentChart'), {
        type: 'bar',
        data: {
            labels: paymentData.map(p => {
                const date = new Date(p.date);
                return date.getDate() + '/' + (date.getMonth() + 1);
            }),
            datasets: [{
                label: 'Tahsilat',
                data: paymentData.map(p => p.total),
                backgroundColor: '#4299e1',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₺' + value.toLocaleString('tr-TR');
                        }
                    }
                }
            }
        }
    });
});
</script>
