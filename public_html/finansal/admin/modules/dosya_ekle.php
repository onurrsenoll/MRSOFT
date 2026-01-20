<?php
/**
 * MR HASAR DANIŞMANLIK - Dosya Ekle/Düzenle
 * HERZAMAN FARKEDER
 */

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$case = null;

// Düzenleme modunda mevcut veriyi çek
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$id]);
    $case = $stmt->fetch();

    if (!$case) {
        setFlash('error', 'Dosya bulunamadı.');
        header('Location: index.php?page=dosyalar');
        exit;
    }
}

// Form gönderildi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verileri al
    $data = [
        'case_type' => clean($_POST['case_type'] ?? ''),
        'source_type' => clean($_POST['source_type'] ?? 'OFİS'),
        'referrer_id' => $_POST['referrer_id'] ?: null,
        'client_name' => clean($_POST['client_name'] ?? ''),
        'tc_no' => clean($_POST['tc_no'] ?? ''),
        'birth_date' => $_POST['birth_date'] ?: null,
        'phone' => clean($_POST['phone'] ?? ''),
        'email' => clean($_POST['email'] ?? ''),
        'address' => clean($_POST['address'] ?? ''),
        'city' => clean($_POST['city'] ?? ''),
        'district' => clean($_POST['district'] ?? ''),
        'iban' => clean($_POST['iban'] ?? ''),
        'bank_name' => clean($_POST['bank_name'] ?? ''),
        'vehicle_plate' => strtoupper(clean($_POST['vehicle_plate'] ?? '')),
        'vehicle_brand' => clean($_POST['vehicle_brand'] ?? ''),
        'vehicle_model' => clean($_POST['vehicle_model'] ?? ''),
        'vehicle_year' => $_POST['vehicle_year'] ?: null,
        'vehicle_color' => clean($_POST['vehicle_color'] ?? ''),
        'vehicle_chassis' => clean($_POST['vehicle_chassis'] ?? ''),
        'vehicle_engine' => clean($_POST['vehicle_engine'] ?? ''),
        'insurance_company_id' => $_POST['insurance_company_id'] ?: null,
        'policy_no' => clean($_POST['policy_no'] ?? ''),
        'claim_no' => clean($_POST['claim_no'] ?? ''),
        'accident_date' => $_POST['accident_date'] ?: null,
        'accident_time' => $_POST['accident_time'] ?: null,
        'accident_location' => clean($_POST['accident_location'] ?? ''),
        'accident_city' => clean($_POST['accident_city'] ?? ''),
        'accident_district' => clean($_POST['accident_district'] ?? ''),
        'accident_description' => clean($_POST['accident_description'] ?? ''),
        'fault_rate' => floatval($_POST['fault_rate'] ?? 0),
        'lawyer_id' => $_POST['lawyer_id'] ?: null,
        'stage_id' => $_POST['stage_id'] ?: null,
        'application_type' => clean($_POST['application_type'] ?? ''),
        'status' => clean($_POST['status'] ?? 'AKTIF'),
        'open_date' => $_POST['open_date'] ?: date('Y-m-d'),
        'notes' => clean($_POST['notes'] ?? ''),
    ];

    // Validasyon
    $errors = [];

    if (empty($data['case_type'])) {
        $errors[] = 'Dosya türü seçiniz.';
    }

    if (empty($data['client_name'])) {
        $errors[] = 'Müvekkil adı zorunludur.';
    }

    if (!empty($data['tc_no']) && !validateTC($data['tc_no'])) {
        $errors[] = 'Geçersiz TC Kimlik numarası.';
    }

    if (empty($errors)) {
        try {
            if ($isEdit) {
                // Güncelle
                $sql = "UPDATE cases SET
                        case_type = ?, source_type = ?, referrer_id = ?,
                        client_name = ?, tc_no = ?, birth_date = ?, phone = ?, email = ?,
                        address = ?, city = ?, district = ?, iban = ?, bank_name = ?,
                        vehicle_plate = ?, vehicle_brand = ?, vehicle_model = ?,
                        vehicle_year = ?, vehicle_color = ?, vehicle_chassis = ?, vehicle_engine = ?,
                        insurance_company_id = ?, policy_no = ?, claim_no = ?,
                        accident_date = ?, accident_time = ?, accident_location = ?,
                        accident_city = ?, accident_district = ?, accident_description = ?, fault_rate = ?,
                        lawyer_id = ?, stage_id = ?, application_type = ?, status = ?, open_date = ?, notes = ?
                        WHERE id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['case_type'], $data['source_type'], $data['referrer_id'],
                    $data['client_name'], $data['tc_no'], $data['birth_date'], $data['phone'], $data['email'],
                    $data['address'], $data['city'], $data['district'], $data['iban'], $data['bank_name'],
                    $data['vehicle_plate'], $data['vehicle_brand'], $data['vehicle_model'],
                    $data['vehicle_year'], $data['vehicle_color'], $data['vehicle_chassis'], $data['vehicle_engine'],
                    $data['insurance_company_id'], $data['policy_no'], $data['claim_no'],
                    $data['accident_date'], $data['accident_time'], $data['accident_location'],
                    $data['accident_city'], $data['accident_district'], $data['accident_description'], $data['fault_rate'],
                    $data['lawyer_id'], $data['stage_id'], $data['application_type'], $data['status'], $data['open_date'], $data['notes'],
                    $id
                ]);

                logActivity($pdo, 'UPDATE', 'cases', $id, $case, $data);
                setFlash('success', 'Dosya başarıyla güncellendi.');
            } else {
                // Yeni dosya no oluştur
                $fileNo = generateFileNo($pdo, $data['case_type']);

                $sql = "INSERT INTO cases
                        (file_no, case_type, source_type, referrer_id,
                         client_name, tc_no, birth_date, phone, email,
                         address, city, district, iban, bank_name,
                         vehicle_plate, vehicle_brand, vehicle_model,
                         vehicle_year, vehicle_color, vehicle_chassis, vehicle_engine,
                         insurance_company_id, policy_no, claim_no,
                         accident_date, accident_time, accident_location,
                         accident_city, accident_district, accident_description, fault_rate,
                         lawyer_id, stage_id, application_type, status, open_date, notes, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $fileNo, $data['case_type'], $data['source_type'], $data['referrer_id'],
                    $data['client_name'], $data['tc_no'], $data['birth_date'], $data['phone'], $data['email'],
                    $data['address'], $data['city'], $data['district'], $data['iban'], $data['bank_name'],
                    $data['vehicle_plate'], $data['vehicle_brand'], $data['vehicle_model'],
                    $data['vehicle_year'], $data['vehicle_color'], $data['vehicle_chassis'], $data['vehicle_engine'],
                    $data['insurance_company_id'], $data['policy_no'], $data['claim_no'],
                    $data['accident_date'], $data['accident_time'], $data['accident_location'],
                    $data['accident_city'], $data['accident_district'], $data['accident_description'], $data['fault_rate'],
                    $data['lawyer_id'], $data['stage_id'], $data['application_type'], $data['status'], $data['open_date'], $data['notes'],
                    $_SESSION['user_id']
                ]);

                $newId = $pdo->lastInsertId();
                logActivity($pdo, 'CREATE', 'cases', $newId, null, $data);
                setFlash('success', 'Dosya başarıyla oluşturuldu. Dosya No: ' . $fileNo);
                header('Location: index.php?page=dosya_detay&id=' . $newId);
                exit;
            }

            header('Location: index.php?page=dosyalar');
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Bir hata oluştu: ' . $e->getMessage());
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}

// Dropdown verileri
$insuranceCompanies = getDropdownData($pdo, 'insurance_companies');
$stages = getDropdownData($pdo, 'stages', 'id', 'name', 'is_active = 1', 'sort_order ASC');
$lawyers = getDropdownData($pdo, 'lawyers');
$referrers = getDropdownData($pdo, 'referrers');
$vehicleBrands = getDropdownData($pdo, 'adk_vehicle_brands');
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="bi bi-folder-plus me-2"></i>
                <?= $isEdit ? 'Dosya Düzenle' : 'Yeni Dosya Ekle' ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=dosyalar">Dosyalar</a></li>
                    <li class="breadcrumb-item active"><?= $isEdit ? 'Düzenle' : 'Yeni Ekle' ?></li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<form method="POST" action="" id="caseForm">
    <div class="row">
        <!-- Sol Kolon -->
        <div class="col-lg-8">
            <!-- Dosya Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-folder me-2"></i>Dosya Bilgileri
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Dosya Türü <span class="text-danger">*</span></label>
                            <select class="form-select" name="case_type" required>
                                <option value="">Seçiniz</option>
                                <option value="ADK" <?= ($case['case_type'] ?? '') === 'ADK' ? 'selected' : '' ?>>ADK (Araç Değer Kaybı)</option>
                                <option value="BH" <?= ($case['case_type'] ?? '') === 'BH' ? 'selected' : '' ?>>BH (Bedeni Hasar)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dosya Kaynağı</label>
                            <select class="form-select" name="source_type" id="sourceType">
                                <option value="OFİS" <?= ($case['source_type'] ?? '') === 'OFİS' ? 'selected' : '' ?>>OFİS</option>
                                <option value="YÖNLENDİREN" <?= ($case['source_type'] ?? '') === 'YÖNLENDİREN' ? 'selected' : '' ?>>YÖNLENDİREN</option>
                                <option value="SERVİS" <?= ($case['source_type'] ?? '') === 'SERVİS' ? 'selected' : '' ?>>SERVİS</option>
                                <option value="MR" <?= ($case['source_type'] ?? '') === 'MR' ? 'selected' : '' ?>>MR</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="referrerDiv" style="display: none;">
                            <label class="form-label">Yönlendiren</label>
                            <select class="form-select select2" name="referrer_id">
                                <option value="">Seçiniz</option>
                                <?php foreach ($referrers as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($case['referrer_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Başvuru Türü</label>
                            <select class="form-select" name="application_type">
                                <option value="">Seçiniz</option>
                                <option value="SİGORTA" <?= ($case['application_type'] ?? '') === 'SİGORTA' ? 'selected' : '' ?>>Sigorta</option>
                                <option value="TAHKİM" <?= ($case['application_type'] ?? '') === 'TAHKİM' ? 'selected' : '' ?>>Tahkim</option>
                                <option value="DAVA" <?= ($case['application_type'] ?? '') === 'DAVA' ? 'selected' : '' ?>>Dava</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dosya Açılış Tarihi</label>
                            <input type="text" class="form-control datepicker" name="open_date"
                                   value="<?= $case ? formatDate($case['open_date'], 'd.m.Y') : date('d.m.Y') ?>">
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Adı Soyadı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="client_name" required
                                   value="<?= e($case['client_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">T.C. Kimlik No</label>
                            <input type="text" class="form-control" name="tc_no" maxlength="11"
                                   data-validate="tc" value="<?= e($case['tc_no'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Doğum Tarihi</label>
                            <input type="text" class="form-control datepicker" name="birth_date"
                                   value="<?= $case && $case['birth_date'] ? formatDate($case['birth_date'], 'd.m.Y') : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="phone" data-format="phone"
                                   value="<?= e($case['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email"
                                   value="<?= e($case['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Adres</label>
                            <textarea class="form-control" name="address" rows="2"><?= e($case['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">İl</label>
                            <input type="text" class="form-control" name="city"
                                   value="<?= e($case['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">İlçe</label>
                            <input type="text" class="form-control" name="district"
                                   value="<?= e($case['district'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IBAN</label>
                            <input type="text" class="form-control" name="iban"
                                   value="<?= e($case['iban'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banka Adı</label>
                            <input type="text" class="form-control" name="bank_name"
                                   value="<?= e($case['bank_name'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Araç Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-car-front me-2"></i>Araç Bilgileri
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Plaka</label>
                            <input type="text" class="form-control text-uppercase" name="vehicle_plate"
                                   value="<?= e($case['vehicle_plate'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marka</label>
                            <select class="form-select select2" name="vehicle_brand">
                                <option value="">Seçiniz</option>
                                <?php foreach ($vehicleBrands as $brand): ?>
                                <option value="<?= e($brand['name']) ?>" <?= ($case['vehicle_brand'] ?? '') === $brand['name'] ? 'selected' : '' ?>>
                                    <?= e($brand['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" name="vehicle_model"
                                   value="<?= e($case['vehicle_model'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Model Yılı</label>
                            <select class="form-select" name="vehicle_year">
                                <option value="">Seçiniz</option>
                                <?php for ($y = date('Y'); $y >= 1990; $y--): ?>
                                <option value="<?= $y ?>" <?= ($case['vehicle_year'] ?? '') == $y ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Renk</label>
                            <input type="text" class="form-control" name="vehicle_color"
                                   value="<?= e($case['vehicle_color'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Şasi No</label>
                            <input type="text" class="form-control" name="vehicle_chassis"
                                   value="<?= e($case['vehicle_chassis'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Motor No</label>
                            <input type="text" class="form-control" name="vehicle_engine"
                                   value="<?= e($case['vehicle_engine'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kaza Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-exclamation-triangle me-2"></i>Kaza Bilgileri
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kaza Tarihi</label>
                            <input type="text" class="form-control datepicker" name="accident_date"
                                   value="<?= $case && $case['accident_date'] ? formatDate($case['accident_date'], 'd.m.Y') : '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kaza Saati</label>
                            <input type="text" class="form-control timepicker" name="accident_time"
                                   value="<?= e($case['accident_time'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">İl</label>
                            <input type="text" class="form-control" name="accident_city"
                                   value="<?= e($case['accident_city'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">İlçe</label>
                            <input type="text" class="form-control" name="accident_district"
                                   value="<?= e($case['accident_district'] ?? '') ?>">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Kaza Yeri</label>
                            <input type="text" class="form-control" name="accident_location"
                                   value="<?= e($case['accident_location'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kusur Oranı (%)</label>
                            <input type="number" class="form-control" name="fault_rate" min="0" max="100" step="1"
                                   value="<?= e($case['fault_rate'] ?? 0) ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Kaza Açıklaması</label>
                            <textarea class="form-control" name="accident_description" rows="3"><?= e($case['accident_description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-lg-4">
            <!-- Sigorta Bilgileri -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-shield-check me-2"></i>Sigorta Bilgileri
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Sigorta Şirketi</label>
                        <select class="form-select select2" name="insurance_company_id">
                            <option value="">Seçiniz</option>
                            <?php foreach ($insuranceCompanies as $ic): ?>
                            <option value="<?= $ic['id'] ?>" <?= ($case['insurance_company_id'] ?? '') == $ic['id'] ? 'selected' : '' ?>>
                                <?= e($ic['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Poliçe No</label>
                        <input type="text" class="form-control" name="policy_no"
                               value="<?= e($case['policy_no'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hasar No</label>
                        <input type="text" class="form-control" name="claim_no"
                               value="<?= e($case['claim_no'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Aşama ve Avukat -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-diagram-3 me-2"></i>Aşama ve Avukat
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Aşama Durumu</label>
                        <select class="form-select" name="stage_id">
                            <option value="">Seçiniz</option>
                            <?php foreach ($stages as $stage): ?>
                            <option value="<?= $stage['id'] ?>" <?= ($case['stage_id'] ?? '') == $stage['id'] ? 'selected' : '' ?>>
                                <?= e($stage['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Avukat</label>
                        <select class="form-select select2" name="lawyer_id">
                            <option value="">Seçiniz</option>
                            <?php foreach ($lawyers as $lawyer): ?>
                            <option value="<?= $lawyer['id'] ?>" <?= ($case['lawyer_id'] ?? '') == $lawyer['id'] ? 'selected' : '' ?>>
                                <?= e($lawyer['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-select" name="status">
                            <option value="AKTIF" <?= ($case['status'] ?? 'AKTIF') === 'AKTIF' ? 'selected' : '' ?>>Aktif</option>
                            <option value="BEKLEMEDE" <?= ($case['status'] ?? '') === 'BEKLEMEDE' ? 'selected' : '' ?>>Beklemede</option>
                            <option value="TAMAMLANDI" <?= ($case['status'] ?? '') === 'TAMAMLANDI' ? 'selected' : '' ?>>Tamamlandı</option>
                            <option value="KAPANDI" <?= ($case['status'] ?? '') === 'KAPANDI' ? 'selected' : '' ?>>Kapandı</option>
                            <option value="İPTAL" <?= ($case['status'] ?? '') === 'İPTAL' ? 'selected' : '' ?>>İptal</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notlar -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-sticky me-2"></i>Notlar
                </div>
                <div class="card-body">
                    <textarea class="form-control" name="notes" rows="4"
                              placeholder="Dosya ile ilgili notlar..."><?= e($case['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Butonlar -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>
                    <?= $isEdit ? 'Güncelle' : 'Dosya Oluştur' ?>
                </button>
                <a href="index.php?page=dosyalar" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-2"></i>İptal
                </a>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Yönlendiren seçimi
    const sourceType = document.getElementById('sourceType');
    const referrerDiv = document.getElementById('referrerDiv');

    function toggleReferrer() {
        const show = ['YÖNLENDİREN', 'SERVİS'].includes(sourceType.value);
        referrerDiv.style.display = show ? 'block' : 'none';
    }

    sourceType.addEventListener('change', toggleReferrer);
    toggleReferrer();
});
</script>
