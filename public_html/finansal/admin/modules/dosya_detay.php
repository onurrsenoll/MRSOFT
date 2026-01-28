<?php
/**
 * MR HASAR DANIŞMANLIK - Dosya Detay
 * HERZAMAN FARKEDER
 */

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php?page=dosyalar');
    exit;
}

// Dosya bilgilerini çek
$stmt = $pdo->prepare("SELECT c.*,
                              s.name as stage_name, s.color as stage_color,
                              ic.name as insurance_name,
                              l.name as lawyer_name,
                              r.name as referrer_name,
                              u.name as created_by_name
                       FROM cases c
                       LEFT JOIN stages s ON c.stage_id = s.id
                       LEFT JOIN insurance_companies ic ON c.insurance_company_id = ic.id
                       LEFT JOIN lawyers l ON c.lawyer_id = l.id
                       LEFT JOIN referrers r ON c.referrer_id = r.id
                       LEFT JOIN users u ON c.created_by = u.id
                       WHERE c.id = ?");
$stmt->execute([$id]);
$case = $stmt->fetch();

if (!$case) {
    setFlash('error', 'Dosya bulunamadı.');
    header('Location: index.php?page=dosyalar');
    exit;
}

// Evrakları çek
$stmt = $pdo->prepare("SELECT cd.*, dt.name as doc_type_name, u.name as uploaded_by_name
                       FROM case_documents cd
                       LEFT JOIN document_types dt ON cd.doc_type_id = dt.id
                       LEFT JOIN users u ON cd.uploaded_by = u.id
                       WHERE cd.case_id = ?
                       ORDER BY cd.created_at DESC");
$stmt->execute([$id]);
$documents = $stmt->fetchAll();

// Masrafları çek
$stmt = $pdo->prepare("SELECT ce.*, et.name as expense_type_name
                       FROM case_expenses ce
                       LEFT JOIN expense_types et ON ce.expense_type_id = et.id
                       WHERE ce.case_id = ?
                       ORDER BY ce.expense_date DESC");
$stmt->execute([$id]);
$expenses = $stmt->fetchAll();
$totalExpenses = array_sum(array_column($expenses, 'amount'));

// Ödemeleri çek
$stmt = $pdo->prepare("SELECT * FROM case_payments WHERE case_id = ? ORDER BY payment_date DESC");
$stmt->execute([$id]);
$payments = $stmt->fetchAll();
$totalPayments = array_sum(array_column(array_filter($payments, fn($p) => $p['payment_type'] === 'TAHSİLAT'), 'amount'));

// Aktiviteleri çek
$stmt = $pdo->prepare("SELECT ca.*, u.name as user_name
                       FROM case_activities ca
                       LEFT JOIN users u ON ca.created_by = u.id
                       WHERE ca.case_id = ?
                       ORDER BY ca.created_at DESC
                       LIMIT 20");
$stmt->execute([$id]);
$activities = $stmt->fetchAll();

// Evrak türleri (dropdown için)
$documentTypes = getDropdownData($pdo, 'document_types');
$expenseTypes = getDropdownData($pdo, 'expense_types');

// Evrak yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload_document' && isset($_FILES['document'])) {
        $docTypeId = (int)$_POST['doc_type_id'];
        $description = clean($_POST['description'] ?? '');

        $result = uploadFile($_FILES['document'], DOCUMENTS_PATH);
        if ($result['success']) {
            $stmt = $pdo->prepare("INSERT INTO case_documents (case_id, doc_type_id, file_name, file_path, file_size, description, uploaded_by)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $docTypeId, $result['original_name'], $result['filepath'], $result['size'], $description, $_SESSION['user_id']]);
            setFlash('success', 'Evrak başarıyla yüklendi.');
        } else {
            setFlash('error', $result['error']);
        }
        header("Location: index.php?page=dosya_detay&id=$id#evraklar");
        exit;
    }

    if ($_POST['action'] === 'add_expense') {
        $expenseTypeId = (int)$_POST['expense_type_id'];
        $amount = floatval($_POST['amount']);
        $expenseDate = $_POST['expense_date'] ?: date('Y-m-d');
        $description = clean($_POST['expense_description'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO case_expenses (case_id, expense_type_id, amount, expense_date, description, created_by)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $expenseTypeId, $amount, $expenseDate, $description, $_SESSION['user_id']]);
        setFlash('success', 'Masraf başarıyla eklendi.');
        header("Location: index.php?page=dosya_detay&id=$id#masraflar");
        exit;
    }

    if ($_POST['action'] === 'add_payment') {
        $paymentType = clean($_POST['payment_type']);
        $amount = floatval($_POST['payment_amount']);
        $paymentDate = $_POST['payment_date'] ?: date('Y-m-d');
        $paymentMethod = clean($_POST['payment_method']);
        $description = clean($_POST['payment_description'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO case_payments (case_id, payment_type, amount, payment_date, payment_method, description, created_by)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $paymentType, $amount, $paymentDate, $paymentMethod, $description, $_SESSION['user_id']]);
        setFlash('success', 'Ödeme başarıyla eklendi.');
        header("Location: index.php?page=dosya_detay&id=$id#odemeler");
        exit;
    }

    if ($_POST['action'] === 'add_note') {
        $note = clean($_POST['note']);
        $stmt = $pdo->prepare("INSERT INTO case_activities (case_id, activity_type, description, created_by)
                               VALUES (?, 'NOT', ?, ?)");
        $stmt->execute([$id, $note, $_SESSION['user_id']]);
        setFlash('success', 'Not eklendi.');
        header("Location: index.php?page=dosya_detay&id=$id");
        exit;
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1>
                <i class="bi bi-folder2-open me-2"></i>
                <?= e($case['file_no']) ?>
                <span class="badge bg-<?= $case['case_type'] === 'ADK' ? 'primary' : 'success' ?> ms-2">
                    <?= e($case['case_type']) ?>
                </span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=dosyalar">Dosyalar</a></li>
                    <li class="breadcrumb-item active"><?= e($case['file_no']) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="index.php?page=dosya_ekle&id=<?= $id ?>" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Düzenle
            </a>
            <button class="btn btn-outline-secondary" onclick="printElement('printArea')">
                <i class="bi bi-printer me-1"></i> Yazdır
            </button>
        </div>
    </div>
</div>

<div id="printArea">
<div class="row">
    <!-- Sol Kolon - Dosya Bilgileri -->
    <div class="col-lg-8">
        <!-- Özet Kartlar -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Hesaplanan</h6>
                        <h4 class="mb-0"><?= formatMoney($case['calculated_amount']) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Tahsilat</h6>
                        <h4 class="mb-0"><?= formatMoney($totalPayments) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Masraf</h6>
                        <h4 class="mb-0"><?= formatMoney($totalExpenses) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Müvekkil Bilgileri -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>Müvekkil Bilgileri
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Ad Soyad:</td>
                                <td><strong><?= e($case['client_name']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">T.C. Kimlik:</td>
                                <td><?= e($case['tc_no'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Doğum Tarihi:</td>
                                <td><?= formatDate($case['birth_date']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Telefon:</td>
                                <td><?= e($case['phone'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">E-posta:</td>
                                <td><?= e($case['email'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">İl/İlçe:</td>
                                <td><?= e(($case['city'] ?? '') . ($case['district'] ? '/' . $case['district'] : '')) ?: '-' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">IBAN:</td>
                                <td><?= e($case['iban'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Banka:</td>
                                <td><?= e($case['bank_name'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Araç ve Kaza Bilgileri -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-car-front me-2"></i>Araç ve Kaza Bilgileri
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Araç Bilgileri</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Plaka:</td>
                                <td><strong><?= e($case['vehicle_plate'] ?? '-') ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Marka/Model:</td>
                                <td><?= e(($case['vehicle_brand'] ?? '') . ' ' . ($case['vehicle_model'] ?? '')) ?: '-' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Yıl:</td>
                                <td><?= e($case['vehicle_year'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Renk:</td>
                                <td><?= e($case['vehicle_color'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Kaza Bilgileri</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Tarih/Saat:</td>
                                <td><?= formatDate($case['accident_date']) ?> <?= e($case['accident_time'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Yer:</td>
                                <td><?= e($case['accident_location'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">İl/İlçe:</td>
                                <td><?= e(($case['accident_city'] ?? '') . ($case['accident_district'] ? '/' . $case['accident_district'] : '')) ?: '-' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kusur Oranı:</td>
                                <td>%<?= e($case['fault_rate'] ?? 0) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php if ($case['accident_description']): ?>
                <div class="mt-3">
                    <h6 class="text-muted">Kaza Açıklaması</h6>
                    <p class="mb-0"><?= nl2br(e($case['accident_description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#evraklar">
                    <i class="bi bi-file-earmark me-1"></i>Evraklar (<?= count($documents) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#masraflar">
                    <i class="bi bi-receipt me-1"></i>Masraflar (<?= count($expenses) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#odemeler">
                    <i class="bi bi-cash-coin me-1"></i>Ödemeler (<?= count($payments) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#aktiviteler">
                    <i class="bi bi-clock-history me-1"></i>Aktiviteler
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Evraklar Tab -->
            <div class="tab-pane fade show active" id="evraklar">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body">
                        <!-- Evrak Yükle -->
                        <form method="POST" enctype="multipart/form-data" class="mb-4">
                            <input type="hidden" name="action" value="upload_document">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select" name="doc_type_id" required>
                                        <option value="">Evrak Türü</option>
                                        <?php foreach ($documentTypes as $dt): ?>
                                        <option value="<?= $dt['id'] ?>"><?= e($dt['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="file" class="form-control" name="document" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="description" placeholder="Açıklama">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-upload"></i> Yükle
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Evrak Listesi -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tür</th>
                                        <th>Dosya Adı</th>
                                        <th>Boyut</th>
                                        <th>Yükleyen</th>
                                        <th>Tarih</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($documents)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Evrak bulunmuyor</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td><?= e($doc['doc_type_name'] ?? '-') ?></td>
                                        <td><?= e($doc['file_name']) ?></td>
                                        <td><?= number_format($doc['file_size'] / 1024, 1) ?> KB</td>
                                        <td><?= e($doc['uploaded_by_name'] ?? '-') ?></td>
                                        <td><?= formatDateTime($doc['created_at']) ?></td>
                                        <td>
                                            <a href="<?= e($doc['file_path']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Masraflar Tab -->
            <div class="tab-pane fade" id="masraflar">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body">
                        <!-- Masraf Ekle -->
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="action" value="add_expense">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select" name="expense_type_id" required>
                                        <option value="">Masraf Türü</option>
                                        <?php foreach ($expenseTypes as $et): ?>
                                        <option value="<?= $et['id'] ?>"><?= e($et['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="amount" placeholder="Tutar" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" name="expense_date" placeholder="Tarih">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="expense_description" placeholder="Açıklama">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus-lg"></i> Ekle
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Masraf Listesi -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tür</th>
                                        <th>Tutar</th>
                                        <th>Tarih</th>
                                        <th>Açıklama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($expenses)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Masraf bulunmuyor</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($expenses as $exp): ?>
                                    <tr>
                                        <td><?= e($exp['expense_type_name'] ?? '-') ?></td>
                                        <td><strong><?= formatMoney($exp['amount']) ?></strong></td>
                                        <td><?= formatDate($exp['expense_date']) ?></td>
                                        <td><?= e($exp['description'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($expenses)): ?>
                                <tfoot>
                                    <tr class="table-light">
                                        <td><strong>TOPLAM</strong></td>
                                        <td><strong><?= formatMoney($totalExpenses) ?></strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ödemeler Tab -->
            <div class="tab-pane fade" id="odemeler">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body">
                        <!-- Ödeme Ekle -->
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="action" value="add_payment">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <select class="form-select" name="payment_type" required>
                                        <option value="TAHSİLAT">Tahsilat</option>
                                        <option value="ÖDEME">Ödeme</option>
                                        <option value="KOMİSYON">Komisyon</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="payment_amount" placeholder="Tutar" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control datepicker" name="payment_date" placeholder="Tarih">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="payment_method">
                                        <option value="NAKİT">Nakit</option>
                                        <option value="HAVALE">Havale</option>
                                        <option value="KART">Kart</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="payment_description" placeholder="Açıklama">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-plus-lg"></i> Ekle
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Ödeme Listesi -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tür</th>
                                        <th>Tutar</th>
                                        <th>Tarih</th>
                                        <th>Yöntem</th>
                                        <th>Açıklama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($payments)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">Ödeme bulunmuyor</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($payments as $pay): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $pay['payment_type'] === 'TAHSİLAT' ? 'success' : 'warning' ?>">
                                                <?= e($pay['payment_type']) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= formatMoney($pay['amount']) ?></strong></td>
                                        <td><?= formatDate($pay['payment_date']) ?></td>
                                        <td><?= e($pay['payment_method']) ?></td>
                                        <td><?= e($pay['description'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktiviteler Tab -->
            <div class="tab-pane fade" id="aktiviteler">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body">
                        <!-- Not Ekle -->
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="action" value="add_note">
                            <div class="input-group">
                                <input type="text" class="form-control" name="note" placeholder="Not ekle..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i> Ekle
                                </button>
                            </div>
                        </form>

                        <!-- Timeline -->
                        <div class="timeline">
                            <?php foreach ($activities as $act): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?= formatDateTime($act['created_at']) ?></div>
                                <div class="timeline-content">
                                    <strong><?= e($act['user_name'] ?? 'Sistem') ?></strong>
                                    <span class="badge bg-secondary ms-2"><?= e($act['activity_type']) ?></span>
                                    <p class="mb-0 mt-1"><?= e($act['description']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sağ Kolon -->
    <div class="col-lg-4">
        <!-- Durum Kartı -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Dosya Durumu
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if ($case['stage_name']): ?>
                    <span class="stage-badge fs-6" style="background-color: <?= $case['stage_color'] ?>">
                        <?= e($case['stage_name']) ?>
                    </span>
                    <?php else: ?>
                    <span class="badge bg-secondary">Aşama Belirlenmedi</span>
                    <?php endif; ?>
                </div>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted">Durum:</td>
                        <td>
                            <?php
                                $caseStatusClass = 'secondary';
                                if ($case['status'] === 'AKTIF') $caseStatusClass = 'success';
                                elseif ($case['status'] === 'BEKLEMEDE') $caseStatusClass = 'warning';
                                elseif ($case['status'] === 'TAMAMLANDI') $caseStatusClass = 'info';
                                elseif ($case['status'] === 'KAPANDI') $caseStatusClass = 'secondary';
                                elseif ($case['status'] === 'İPTAL') $caseStatusClass = 'danger';
                            ?>
                            <span class="badge bg-<?= $caseStatusClass ?>">
                                <?= e($case['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Açılış:</td>
                        <td><?= formatDate($case['open_date']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kaynak:</td>
                        <td><?= e($case['source_type']) ?></td>
                    </tr>
                    <?php if ($case['referrer_name']): ?>
                    <tr>
                        <td class="text-muted">Yönlendiren:</td>
                        <td><?= e($case['referrer_name']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Sigorta Bilgileri -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-shield-check me-2"></i>Sigorta Bilgileri
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted">Şirket:</td>
                        <td><?= e($case['insurance_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Poliçe No:</td>
                        <td><?= e($case['policy_no'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Hasar No:</td>
                        <td><?= e($case['claim_no'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Başvuru:</td>
                        <td><?= e($case['application_type'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Avukat:</td>
                        <td><?= e($case['lawyer_name'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notlar -->
        <?php if ($case['notes']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-sticky me-2"></i>Notlar
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(e($case['notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Hızlı İşlemler -->
        <div class="card no-print">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Hızlı İşlemler
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($case['case_type'] === 'ADK'): ?>
                    <a href="index.php?page=adk_hesaplama&case_id=<?= $id ?>" class="btn btn-outline-primary">
                        <i class="bi bi-calculator me-2"></i>ADK Hesapla
                    </a>
                    <?php else: ?>
                    <a href="index.php?page=maluliyet_hesaplama&case_id=<?= $id ?>" class="btn btn-outline-primary">
                        <i class="bi bi-calculator me-2"></i>Maluliyet Hesapla
                    </a>
                    <?php endif; ?>
                    <a href="index.php?page=ajanda&case_id=<?= $id ?>" class="btn btn-outline-warning">
                        <i class="bi bi-calendar-plus me-2"></i>Hatırlatma Ekle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
