<?php
/**
 * MR HASAR DANIŞMANLIK - Ajanda
 * HERZAMAN FARKEDER
 */

$tab = $_GET['tab'] ?? 'calendar';

// Etkinlik ekle/güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = clean($_POST['title']);
    $description = clean($_POST['description'] ?? '');
    $eventDate = $_POST['event_date'];
    $eventTime = $_POST['event_time'] ?: null;
    $eventType = clean($_POST['event_type'] ?? 'HATIRLATMA');
    $caseId = (int)($_POST['case_id'] ?? 0) ?: null;

    if (empty($title) || empty($eventDate)) {
        setFlash('error', 'Başlık ve tarih zorunludur.');
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE calendar_events SET title=?, description=?, event_date=?, event_time=?, event_type=?, case_id=? WHERE id=?");
            $stmt->execute([$title, $description, $eventDate, $eventTime, $eventType, $caseId, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO calendar_events (title, description, event_date, event_time, event_type, case_id, user_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $eventDate, $eventTime, $eventType, $caseId, $_SESSION['user_id'], $_SESSION['user_id']]);
        }
        setFlash('success', 'Etkinlik kaydedildi.');
        header('Location: index.php?page=ajanda');
        exit;
    }
}

// Silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = ? AND (user_id = ? OR created_by = ?)");
    $stmt->execute([(int)$_GET['delete'], $_SESSION['user_id'], $_SESSION['user_id']]);
    setFlash('success', 'Etkinlik silindi.');
    header('Location: index.php?page=ajanda');
    exit;
}

// Tamamlandı işaretle
if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $stmt = $pdo->prepare("UPDATE calendar_events SET is_completed = 1 WHERE id = ?");
    $stmt->execute([(int)$_GET['complete']]);
    header('Location: index.php?page=ajanda');
    exit;
}

// Bu ay etkinlikler
$currentMonth = date('Y-m');
$stmt = $pdo->prepare("SELECT ce.*, c.file_no
                       FROM calendar_events ce
                       LEFT JOIN cases c ON ce.case_id = c.id
                       WHERE ce.event_date LIKE ?
                       AND (ce.user_id = ? OR ce.user_id IS NULL)
                       ORDER BY ce.event_date, ce.event_time");
$stmt->execute(["$currentMonth%", $_SESSION['user_id']]);
$monthEvents = $stmt->fetchAll();

// Bugünkü etkinlikler
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT ce.*, c.file_no
                       FROM calendar_events ce
                       LEFT JOIN cases c ON ce.case_id = c.id
                       WHERE ce.event_date = ?
                       AND (ce.user_id = ? OR ce.user_id IS NULL)
                       ORDER BY ce.event_time");
$stmt->execute([$today, $_SESSION['user_id']]);
$todayEvents = $stmt->fetchAll();

// Yaklaşan etkinlikler (7 gün)
$stmt = $pdo->prepare("SELECT ce.*, c.file_no
                       FROM calendar_events ce
                       LEFT JOIN cases c ON ce.case_id = c.id
                       WHERE ce.event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                       AND ce.is_completed = 0
                       AND (ce.user_id = ? OR ce.user_id IS NULL)
                       ORDER BY ce.event_date, ce.event_time");
$stmt->execute([$_SESSION['user_id']]);
$upcomingEvents = $stmt->fetchAll();

// Dosyalar (dropdown için)
$stmt = $pdo->query("SELECT id, file_no, client_name FROM cases WHERE status = 'AKTIF' ORDER BY file_no DESC LIMIT 100");
$cases = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-calendar3 me-2"></i>Ajanda</h1>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
            <i class="bi bi-plus-lg me-1"></i> Yeni Etkinlik
        </button>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'calendar' ? 'active' : '' ?>" href="index.php?page=ajanda&tab=calendar">
            <i class="bi bi-calendar3 me-1"></i>Takvim
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'reminders' ? 'active' : '' ?>" href="index.php?page=ajanda&tab=reminders">
            <i class="bi bi-bell me-1"></i>Hatırlatmalar
            <?php if (count($upcomingEvents) > 0): ?>
            <span class="badge bg-warning"><?= count($upcomingEvents) ?></span>
            <?php endif; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'today' ? 'active' : '' ?>" href="index.php?page=ajanda&tab=today">
            <i class="bi bi-clock me-1"></i>Bugün
            <?php if (count($todayEvents) > 0): ?>
            <span class="badge bg-primary"><?= count($todayEvents) ?></span>
            <?php endif; ?>
        </a>
    </li>
</ul>

<?php if ($tab === 'calendar'): ?>
<!-- Takvim Görünümü -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= strftime('%B %Y', strtotime($currentMonth . '-01')) ?></h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Pzt</th>
                        <th class="text-center">Sal</th>
                        <th class="text-center">Çar</th>
                        <th class="text-center">Per</th>
                        <th class="text-center">Cum</th>
                        <th class="text-center text-muted">Cmt</th>
                        <th class="text-center text-muted">Paz</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $firstDay = date('N', strtotime($currentMonth . '-01'));
                    $daysInMonth = date('t', strtotime($currentMonth . '-01'));
                    $day = 1;
                    $eventsByDate = [];
                    foreach ($monthEvents as $e) {
                        $d = date('j', strtotime($e['event_date']));
                        $eventsByDate[$d][] = $e;
                    }

                    for ($week = 0; $week < 6; $week++):
                        if ($day > $daysInMonth) break;
                    ?>
                    <tr>
                        <?php for ($dow = 1; $dow <= 7; $dow++):
                            if (($week === 0 && $dow < $firstDay) || $day > $daysInMonth): ?>
                            <td></td>
                            <?php else:
                                $isToday = ($currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT)) === $today;
                                $dayEvents = $eventsByDate[$day] ?? [];
                            ?>
                            <td class="<?= $isToday ? 'bg-primary-light' : '' ?>" style="min-width: 100px; height: 80px; vertical-align: top;">
                                <div class="fw-bold <?= $isToday ? 'text-primary' : '' ?>"><?= $day ?></div>
                                <?php foreach (array_slice($dayEvents, 0, 2) as $de): ?>
                                <div class="small">
                                    <span class="badge bg-<?php
                                        echo match($de['event_type']) {
                                            'DURUŞMA' => 'danger',
                                            'TOPLANTI' => 'warning',
                                            'RANDEVU' => 'info',
                                            default => 'secondary'
                                        };
                                    ?>" style="font-size: 0.65rem;">
                                        <?= e(mb_substr($de['title'], 0, 15)) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                                <?php if (count($dayEvents) > 2): ?>
                                <small class="text-muted">+<?= count($dayEvents) - 2 ?> daha</small>
                                <?php endif; ?>
                            </td>
                            <?php $day++; endif; ?>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($tab === 'reminders'): ?>
<!-- Hatırlatmalar -->
<div class="card">
    <div class="card-header">Yaklaşan Etkinlikler (7 Gün)</div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php if (empty($upcomingEvents)): ?>
            <div class="list-group-item text-center text-muted py-4">Yaklaşan etkinlik yok</div>
            <?php else: ?>
            <?php foreach ($upcomingEvents as $e): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-<?php
                        echo match($e['event_type']) {
                            'DURUŞMA' => 'danger',
                            'TOPLANTI' => 'warning',
                            'RANDEVU' => 'info',
                            default => 'secondary'
                        };
                    ?> me-2"><?= e($e['event_type']) ?></span>
                    <strong><?= e($e['title']) ?></strong>
                    <?php if ($e['file_no']): ?>
                    <small class="text-muted ms-2">(<?= e($e['file_no']) ?>)</small>
                    <?php endif; ?>
                    <div class="small text-muted">
                        <?= formatDate($e['event_date']) ?> <?= $e['event_time'] ? date('H:i', strtotime($e['event_time'])) : '' ?>
                    </div>
                </div>
                <div>
                    <a href="index.php?page=ajanda&complete=<?= $e['id'] ?>" class="btn btn-sm btn-outline-success" title="Tamamlandı">
                        <i class="bi bi-check-lg"></i>
                    </a>
                    <a href="index.php?page=ajanda&delete=<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-name="<?= e($e['title']) ?>">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Bugün -->
<div class="card">
    <div class="card-header">Bugünkü Etkinlikler - <?= formatDate($today) ?></div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php if (empty($todayEvents)): ?>
            <div class="list-group-item text-center text-muted py-4">Bugün etkinlik yok</div>
            <?php else: ?>
            <?php foreach ($todayEvents as $e): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <div>
                        <span class="badge bg-<?php
                            echo match($e['event_type']) {
                                'DURUŞMA' => 'danger',
                                'TOPLANTI' => 'warning',
                                'RANDEVU' => 'info',
                                default => 'secondary'
                            };
                        ?> me-2"><?= $e['event_time'] ? date('H:i', strtotime($e['event_time'])) : $e['event_type'] ?></span>
                        <strong><?= e($e['title']) ?></strong>
                    </div>
                    <a href="index.php?page=ajanda&complete=<?= $e['id'] ?>" class="btn btn-sm btn-success">
                        <i class="bi bi-check-lg"></i> Tamamla
                    </a>
                </div>
                <?php if ($e['description']): ?>
                <p class="mb-0 mt-2 small text-muted"><?= e($e['description']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Etkinlik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Başlık *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tarih *</label>
                            <input type="text" class="form-control datepicker" name="event_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Saat</label>
                            <input type="text" class="form-control timepicker" name="event_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tür</label>
                        <select class="form-select" name="event_type">
                            <option value="HATIRLATMA">Hatırlatma</option>
                            <option value="DURUŞMA">Duruşma</option>
                            <option value="TOPLANTI">Toplantı</option>
                            <option value="RANDEVU">Randevu</option>
                            <option value="DİĞER">Diğer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İlişkili Dosya</label>
                        <select class="form-select select2" name="case_id">
                            <option value="">Seçiniz</option>
                            <?php foreach ($cases as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['file_no']) ?> - <?= e($c['client_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
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
