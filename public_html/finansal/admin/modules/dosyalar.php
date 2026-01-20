<?php
/**
 * MR HASAR DANIŞMANLIK - Dosya Listesi
 * HERZAMAN FARKEDER
 */

// Silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM cases WHERE id = ?");
    if ($stmt->execute([$id])) {
        logActivity($pdo, 'DELETE', 'cases', $id);
        setFlash('success', 'Dosya başarıyla silindi.');
    } else {
        setFlash('error', 'Silme işlemi başarısız.');
    }
    header('Location: index.php?page=dosyalar');
    exit;
}

// Filtreler
$filter_type = $_GET['type'] ?? '';
$filter_stage = $_GET['stage'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_search = $_GET['search'] ?? '';

// SQL oluştur
$where = ["1=1"];
$params = [];

if ($filter_type) {
    $where[] = "c.case_type = ?";
    $params[] = $filter_type;
}

if ($filter_stage) {
    $where[] = "c.stage_id = ?";
    $params[] = $filter_stage;
}

if ($filter_status) {
    $where[] = "c.status = ?";
    $params[] = $filter_status;
}

if ($filter_search) {
    $where[] = "(c.file_no LIKE ? OR c.client_name LIKE ? OR c.tc_no LIKE ? OR c.claim_no LIKE ?)";
    $search = "%{$filter_search}%";
    $params = array_merge($params, [$search, $search, $search, $search]);
}

$whereClause = implode(' AND ', $where);

// Dosyaları çek
$sql = "SELECT c.*,
               s.name as stage_name, s.color as stage_color,
               ic.name as insurance_name,
               l.name as lawyer_name,
               r.name as referrer_name
        FROM cases c
        LEFT JOIN stages s ON c.stage_id = s.id
        LEFT JOIN insurance_companies ic ON c.insurance_company_id = ic.id
        LEFT JOIN lawyers l ON c.lawyer_id = l.id
        LEFT JOIN referrers r ON c.referrer_id = r.id
        WHERE {$whereClause}
        ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

// Aşamalar (dropdown için)
$stages = getDropdownData($pdo, 'stages', 'id', 'name', 'is_active = 1', 'sort_order ASC');
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1><i class="bi bi-folder2-open me-2"></i>Dosya Listesi</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item active">Dosyalar</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="index.php?page=dosya_ekle" class="btn btn-primary">
                <i class="bi bi-folder-plus me-1"></i> Yeni Dosya Ekle
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="dosyalar">

            <div class="col-md-3">
                <label class="form-label">Arama</label>
                <input type="text" class="form-control" name="search" value="<?= e($filter_search) ?>"
                       placeholder="Dosya no, ad, TC, hasar no...">
            </div>

            <div class="col-md-2">
                <label class="form-label">Dosya Türü</label>
                <select class="form-select" name="type">
                    <option value="">Tümü</option>
                    <option value="ADK" <?= $filter_type === 'ADK' ? 'selected' : '' ?>>ADK</option>
                    <option value="BH" <?= $filter_type === 'BH' ? 'selected' : '' ?>>BH</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Aşama</label>
                <select class="form-select" name="stage">
                    <option value="">Tümü</option>
                    <?php foreach ($stages as $stage): ?>
                    <option value="<?= $stage['id'] ?>" <?= $filter_stage == $stage['id'] ? 'selected' : '' ?>>
                        <?= e($stage['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Durum</label>
                <select class="form-select" name="status">
                    <option value="">Tümü</option>
                    <option value="AKTIF" <?= $filter_status === 'AKTIF' ? 'selected' : '' ?>>Aktif</option>
                    <option value="BEKLEMEDE" <?= $filter_status === 'BEKLEMEDE' ? 'selected' : '' ?>>Beklemede</option>
                    <option value="TAMAMLANDI" <?= $filter_status === 'TAMAMLANDI' ? 'selected' : '' ?>>Tamamlandı</option>
                    <option value="KAPANDI" <?= $filter_status === 'KAPANDI' ? 'selected' : '' ?>>Kapandı</option>
                    <option value="İPTAL" <?= $filter_status === 'İPTAL' ? 'selected' : '' ?>>İptal</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filtrele
                </button>
                <a href="index.php?page=dosyalar" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Cases Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2"></i>Dosyalar (<?= count($cases) ?>)</span>
        <div>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToExcel('casesTable', 'dosyalar')">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover datatable mb-0" id="casesTable">
                <thead>
                    <tr>
                        <th>Dosya No</th>
                        <th>T.C. No</th>
                        <th>Adı Soyadı</th>
                        <th>Kaynak</th>
                        <th>Avukat</th>
                        <th>Tür</th>
                        <th>Başvuru</th>
                        <th>Sigorta</th>
                        <th>Hasar No</th>
                        <th>Açılış</th>
                        <th>Aşama</th>
                        <th class="text-center no-print">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">
                            Kayıt bulunamadı
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($cases as $case): ?>
                    <tr>
                        <td>
                            <a href="index.php?page=dosya_detay&id=<?= $case['id'] ?>" class="fw-bold text-primary">
                                <?= e($case['file_no']) ?>
                            </a>
                        </td>
                        <td><?= e($case['tc_no'] ?? '-') ?></td>
                        <td><?= e($case['client_name']) ?></td>
                        <td>
                            <?php if ($case['referrer_name']): ?>
                                <span class="badge bg-info"><?= e($case['source_type']) ?></span>
                                <small class="d-block text-muted"><?= e($case['referrer_name']) ?></small>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= e($case['source_type']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($case['lawyer_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $case['case_type'] === 'ADK' ? 'primary' : 'success' ?>">
                                <?= e($case['case_type']) ?>
                            </span>
                        </td>
                        <td><?= e($case['application_type'] ?? '-') ?></td>
                        <td><?= e($case['insurance_name'] ?? '-') ?></td>
                        <td><?= e($case['claim_no'] ?? '-') ?></td>
                        <td><?= formatDate($case['open_date']) ?></td>
                        <td>
                            <?php if ($case['stage_name']): ?>
                            <span class="stage-badge" style="background-color: <?= $case['stage_color'] ?>">
                                <?= e($case['stage_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center no-print">
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?page=dosya_detay&id=<?= $case['id'] ?>"
                                   class="btn btn-outline-primary btn-icon" title="Görüntüle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="index.php?page=dosya_ekle&id=<?= $case['id'] ?>"
                                   class="btn btn-outline-warning btn-icon" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?page=dosyalar&delete=<?= $case['id'] ?>"
                                   class="btn btn-outline-danger btn-icon btn-delete"
                                   data-name="<?= e($case['file_no']) ?>" title="Sil">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
