<?php
/**
 * MR HASAR DANIŞMANLIK - CRM Modülü
 * HERZAMAN FARKEDER
 */

// Silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM crm_leads WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Lead silindi.');
    header('Location: index.php?page=crm');
    exit;
}

// Dosyaya dönüştür
if (isset($_GET['convert']) && is_numeric($_GET['convert'])) {
    $leadId = (int)$_GET['convert'];
    $stmt = $pdo->prepare("SELECT * FROM crm_leads WHERE id = ?");
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();

    if ($lead) {
        header("Location: index.php?page=dosya_ekle&lead_id=$leadId");
        exit;
    }
}

// Yeni lead veya güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'name' => clean($_POST['name'] ?? ''),
        'phone' => clean($_POST['phone'] ?? ''),
        'phone2' => clean($_POST['phone2'] ?? ''),
        'email' => clean($_POST['email'] ?? ''),
        'city' => clean($_POST['city'] ?? ''),
        'district' => clean($_POST['district'] ?? ''),
        'accident_date' => $_POST['accident_date'] ?: null,
        'accident_summary' => clean($_POST['accident_summary'] ?? ''),
        'vehicle_info' => clean($_POST['vehicle_info'] ?? ''),
        'source' => clean($_POST['source'] ?? 'DİĞER'),
        'status' => clean($_POST['status'] ?? 'BEKLEMEDE'),
        'next_contact_date' => $_POST['next_contact_date'] ?: null,
        'notes' => clean($_POST['notes'] ?? ''),
    ];

    if (empty($data['name'])) {
        setFlash('error', 'Ad soyad zorunludur.');
    } else {
        if ($id > 0) {
            $sql = "UPDATE crm_leads SET name=?, phone=?, phone2=?, email=?, city=?, district=?,
                    accident_date=?, accident_summary=?, vehicle_info=?, source=?, status=?,
                    next_contact_date=?, notes=? WHERE id=?";
            $pdo->prepare($sql)->execute([
                $data['name'], $data['phone'], $data['phone2'], $data['email'], $data['city'], $data['district'],
                $data['accident_date'], $data['accident_summary'], $data['vehicle_info'], $data['source'], $data['status'],
                $data['next_contact_date'], $data['notes'], $id
            ]);
            setFlash('success', 'Lead güncellendi.');
        } else {
            $sql = "INSERT INTO crm_leads (name, phone, phone2, email, city, district, accident_date,
                    accident_summary, vehicle_info, source, status, next_contact_date, notes, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([
                $data['name'], $data['phone'], $data['phone2'], $data['email'], $data['city'], $data['district'],
                $data['accident_date'], $data['accident_summary'], $data['vehicle_info'], $data['source'], $data['status'],
                $data['next_contact_date'], $data['notes'], $_SESSION['user_id']
            ]);
            setFlash('success', 'Yeni lead eklendi.');
        }
        header('Location: index.php?page=crm');
        exit;
    }
}

// Not ekleme
if (isset($_POST['add_note'])) {
    $leadId = (int)$_POST['lead_id'];
    $note = clean($_POST['note']);
    $contactType = clean($_POST['contact_type'] ?? 'TELEFON');

    $stmt = $pdo->prepare("INSERT INTO crm_notes (lead_id, note, contact_type, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$leadId, $note, $contactType, $_SESSION['user_id']]);
    setFlash('success', 'Not eklendi.');
    header("Location: index.php?page=crm&view=$leadId");
    exit;
}

// Filtreler
$filter_status = $_GET['status'] ?? '';
$filter_source = $_GET['source'] ?? '';

$where = ["1=1"];
$params = [];

if ($filter_status) {
    $where[] = "status = ?";
    $params[] = $filter_status;
}

if ($filter_source) {
    $where[] = "source = ?";
    $params[] = $filter_source;
}

$whereClause = implode(' AND ', $where);

// Lead listesi
$stmt = $pdo->prepare("SELECT l.*, u.name as created_by_name
                       FROM crm_leads l
                       LEFT JOIN users u ON l.created_by = u.id
                       WHERE $whereClause
                       ORDER BY l.created_at DESC");
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Görüntüleme modu
$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$viewLead = null;
$viewNotes = [];

if ($viewId) {
    $stmt = $pdo->prepare("SELECT * FROM crm_leads WHERE id = ?");
    $stmt->execute([$viewId]);
    $viewLead = $stmt->fetch();

    if ($viewLead) {
        $stmt = $pdo->prepare("SELECT n.*, u.name as user_name FROM crm_notes n
                               LEFT JOIN users u ON n.created_by = u.id
                               WHERE n.lead_id = ? ORDER BY n.created_at DESC");
        $stmt->execute([$viewId]);
        $viewNotes = $stmt->fetchAll();
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-people me-2"></i>CRM Takip</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item active">CRM</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leadModal">
            <i class="bi bi-person-plus me-1"></i> Yeni Lead
        </button>
    </div>
</div>

<div class="row">
    <!-- Lead Listesi -->
    <div class="col-lg-<?= $viewLead ? '7' : '12' ?>">
        <!-- Filtreler -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="crm">
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="status">
                            <option value="">Tüm Durumlar</option>
                            <option value="BEKLEMEDE" <?= $filter_status === 'BEKLEMEDE' ? 'selected' : '' ?>>Beklemede</option>
                            <option value="İLGİLİ" <?= $filter_status === 'İLGİLİ' ? 'selected' : '' ?>>İlgili</option>
                            <option value="DOSYA AÇILDI" <?= $filter_status === 'DOSYA AÇILDI' ? 'selected' : '' ?>>Dosya Açıldı</option>
                            <option value="İPTAL" <?= $filter_status === 'İPTAL' ? 'selected' : '' ?>>İptal</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" name="source">
                            <option value="">Tüm Kaynaklar</option>
                            <option value="REFERANS" <?= $filter_source === 'REFERANS' ? 'selected' : '' ?>>Referans</option>
                            <option value="REKLAM" <?= $filter_source === 'REKLAM' ? 'selected' : '' ?>>Reklam</option>
                            <option value="WEB" <?= $filter_source === 'WEB' ? 'selected' : '' ?>>Web</option>
                            <option value="TELEFON" <?= $filter_source === 'TELEFON' ? 'selected' : '' ?>>Telefon</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Filtrele</button>
                        <a href="index.php?page=crm" class="btn btn-sm btn-outline-secondary">Temizle</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lead Kartları -->
        <div class="row">
            <?php if (empty($leads)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        Henüz lead bulunmuyor.
                    </div>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($leads as $lead): ?>
            <div class="col-md-<?= $viewLead ? '12' : '4' ?> mb-3">
                <div class="card h-100 hover-shadow <?= $viewId == $lead['id'] ? 'border-primary' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1"><?= e($lead['name']) ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-telephone me-1"></i><?= e($lead['phone'] ?? '-') ?>
                                </small>
                            </div>
                            <?php
                                $statusClass = 'secondary';
                                if ($lead['status'] === 'BEKLEMEDE') $statusClass = 'warning';
                                elseif ($lead['status'] === 'İLGİLİ') $statusClass = 'info';
                                elseif ($lead['status'] === 'DOSYA AÇILDI') $statusClass = 'success';
                                elseif ($lead['status'] === 'İPTAL') $statusClass = 'danger';
                            ?>
                            <span class="badge bg-<?= $statusClass ?>"><?= e($lead['status']) ?></span>
                        </div>

                        <?php if ($lead['accident_date']): ?>
                        <small class="d-block mt-2 text-muted">
                            <i class="bi bi-calendar3 me-1"></i>Kaza: <?= formatDate($lead['accident_date']) ?>
                        </small>
                        <?php endif; ?>

                        <?php if ($lead['vehicle_info']): ?>
                        <small class="d-block text-muted">
                            <i class="bi bi-car-front me-1"></i><?= e($lead['vehicle_info']) ?>
                        </small>
                        <?php endif; ?>

                        <div class="mt-3 d-flex gap-1">
                            <a href="index.php?page=crm&view=<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-warning edit-lead"
                                    data-lead='<?= json_encode($lead) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if ($lead['status'] !== 'DOSYA AÇILDI'): ?>
                            <a href="index.php?page=crm&convert=<?= $lead['id'] ?>" class="btn btn-sm btn-outline-success"
                               title="Dosyaya Dönüştür">
                                <i class="bi bi-folder-plus"></i>
                            </a>
                            <?php endif; ?>
                            <a href="index.php?page=crm&delete=<?= $lead['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"
                               data-name="<?= e($lead['name']) ?>">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <small class="text-muted">
                            <i class="bi bi-tag me-1"></i><?= e($lead['source']) ?>
                            <span class="float-end"><?= formatDate($lead['created_at']) ?></span>
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lead Detay -->
    <?php if ($viewLead): ?>
    <div class="col-lg-5">
        <div class="card sticky-top" style="top: 80px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person me-2"></i><?= e($viewLead['name']) ?></span>
                <a href="index.php?page=crm" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted">Telefon:</td>
                        <td><?= e($viewLead['phone'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Telefon 2:</td>
                        <td><?= e($viewLead['phone2'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">E-posta:</td>
                        <td><?= e($viewLead['email'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">İl/İlçe:</td>
                        <td><?= e(($viewLead['city'] ?? '') . '/' . ($viewLead['district'] ?? '')) ?: '-' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kaza Tarihi:</td>
                        <td><?= formatDate($viewLead['accident_date']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Araç:</td>
                        <td><?= e($viewLead['vehicle_info'] ?? '-') ?></td>
                    </tr>
                </table>

                <?php if ($viewLead['accident_summary']): ?>
                <div class="alert alert-light mt-3">
                    <strong>Kaza Özeti:</strong><br>
                    <?= nl2br(e($viewLead['accident_summary'])) ?>
                </div>
                <?php endif; ?>

                <!-- Not Ekle -->
                <form method="POST" class="mt-4">
                    <input type="hidden" name="add_note" value="1">
                    <input type="hidden" name="lead_id" value="<?= $viewLead['id'] ?>">
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="text" class="form-control" name="note" placeholder="Not ekle..." required>
                        </div>
                        <div class="col-4">
                            <select class="form-select" name="contact_type">
                                <option value="TELEFON">Telefon</option>
                                <option value="SMS">SMS</option>
                                <option value="EMAIL">E-mail</option>
                                <option value="YÜZYÜZE">Yüz yüze</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-plus-lg me-1"></i>Not Ekle
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Notlar Timeline -->
                <div class="mt-4">
                    <h6>Görüşme Geçmişi</h6>
                    <?php if (empty($viewNotes)): ?>
                    <p class="text-muted small">Henüz not yok.</p>
                    <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($viewNotes as $note): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?= formatDateTime($note['created_at']) ?></div>
                            <div class="timeline-content">
                                <span class="badge bg-secondary"><?= e($note['contact_type']) ?></span>
                                <p class="mb-0 mt-1"><?= e($note['note']) ?></p>
                                <small class="text-muted"><?= e($note['user_name'] ?? '-') ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Lead Modal -->
<div class="modal fade" id="leadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="leadId" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadModalTitle">Yeni Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" class="form-control" name="name" id="leadName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon</label>
                            <input type="text" class="form-control" name="phone" id="leadPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon 2</label>
                            <input type="text" class="form-control" name="phone2" id="leadPhone2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" id="leadEmail">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">İl</label>
                            <input type="text" class="form-control" name="city" id="leadCity">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">İlçe</label>
                            <input type="text" class="form-control" name="district" id="leadDistrict">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kaza Tarihi</label>
                            <input type="text" class="form-control datepicker" name="accident_date" id="leadAccidentDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Araç Bilgisi</label>
                            <input type="text" class="form-control" name="vehicle_info" id="leadVehicle" placeholder="Marka Model Yıl">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kaynak</label>
                            <select class="form-select" name="source" id="leadSource">
                                <option value="REFERANS">Referans</option>
                                <option value="REKLAM">Reklam</option>
                                <option value="WEB">Web</option>
                                <option value="TELEFON">Telefon</option>
                                <option value="DİĞER">Diğer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="status" id="leadStatus">
                                <option value="BEKLEMEDE">Beklemede</option>
                                <option value="İLGİLİ">İlgili</option>
                                <option value="DOSYA AÇILDI">Dosya Açıldı</option>
                                <option value="İPTAL">İptal</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Kaza Özeti</label>
                            <textarea class="form-control" name="accident_summary" id="leadAccidentSummary" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sonraki Görüşme</label>
                            <input type="text" class="form-control datetimepicker" name="next_contact_date" id="leadNextContact">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notlar</label>
                            <textarea class="form-control" name="notes" id="leadNotes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-lead').forEach(btn => {
    btn.addEventListener('click', function() {
        const lead = JSON.parse(this.dataset.lead);
        document.getElementById('leadId').value = lead.id;
        document.getElementById('leadModalTitle').textContent = 'Lead Düzenle';
        document.getElementById('leadName').value = lead.name || '';
        document.getElementById('leadPhone').value = lead.phone || '';
        document.getElementById('leadPhone2').value = lead.phone2 || '';
        document.getElementById('leadEmail').value = lead.email || '';
        document.getElementById('leadCity').value = lead.city || '';
        document.getElementById('leadDistrict').value = lead.district || '';
        document.getElementById('leadVehicle').value = lead.vehicle_info || '';
        document.getElementById('leadSource').value = lead.source || 'DİĞER';
        document.getElementById('leadStatus').value = lead.status || 'BEKLEMEDE';
        document.getElementById('leadAccidentSummary').value = lead.accident_summary || '';
        document.getElementById('leadNotes').value = lead.notes || '';

        new bootstrap.Modal(document.getElementById('leadModal')).show();
    });
});

// Modal sıfırla
document.getElementById('leadModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('leadId').value = 0;
    document.getElementById('leadModalTitle').textContent = 'Yeni Lead';
    this.querySelector('form').reset();
});
</script>
