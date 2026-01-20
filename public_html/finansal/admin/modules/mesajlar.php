<?php
/**
 * MR HASAR DANIŞMANLIK - Mesajlar
 * HERZAMAN FARKEDER
 */

$tab = $_GET['tab'] ?? 'inbox';
$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;

// Mesaj gönder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $toUser = (int)$_POST['to_user'];
    $subject = clean($_POST['subject']);
    $message = clean($_POST['message']);
    $parentId = (int)($_POST['parent_id'] ?? 0) ?: null;

    $stmt = $pdo->prepare("INSERT INTO messages (from_user, to_user, subject, message, parent_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $toUser, $subject, $message, $parentId]);
    setFlash('success', 'Mesaj gönderildi.');
    header('Location: index.php?page=mesajlar&tab=sent');
    exit;
}

// Mesajı okundu olarak işaretle
if ($viewId) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE id = ? AND to_user = ?");
    $stmt->execute([$viewId, $_SESSION['user_id']]);
}

// Gelen mesajlar
$stmt = $pdo->prepare("SELECT m.*, u.name as from_name
                       FROM messages m
                       JOIN users u ON m.from_user = u.id
                       WHERE m.to_user = ?
                       ORDER BY m.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$inboxMessages = $stmt->fetchAll();

// Gönderilen mesajlar
$stmt = $pdo->prepare("SELECT m.*, u.name as to_name
                       FROM messages m
                       JOIN users u ON m.to_user = u.id
                       WHERE m.from_user = ?
                       ORDER BY m.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$sentMessages = $stmt->fetchAll();

// Görüntülenecek mesaj
$viewMessage = null;
if ($viewId) {
    $stmt = $pdo->prepare("SELECT m.*, uf.name as from_name, ut.name as to_name
                           FROM messages m
                           JOIN users uf ON m.from_user = uf.id
                           JOIN users ut ON m.to_user = ut.id
                           WHERE m.id = ? AND (m.from_user = ? OR m.to_user = ?)");
    $stmt->execute([$viewId, $_SESSION['user_id'], $_SESSION['user_id']]);
    $viewMessage = $stmt->fetch();
}

// Kullanıcılar (gönderim için)
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE is_active = 1 AND id != ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-envelope me-2"></i>Mesajlar</h1>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
            <i class="bi bi-pencil-square me-1"></i> Yeni Mesaj
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-<?= $viewMessage ? '6' : '12' ?>">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'inbox' ? 'active' : '' ?>" href="index.php?page=mesajlar&tab=inbox">
                            <i class="bi bi-inbox me-1"></i>Gelen Kutusu
                            <?php
                            $unread = count(array_filter($inboxMessages, fn($m) => !$m['is_read']));
                            if ($unread > 0): ?>
                            <span class="badge bg-danger"><?= $unread ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'sent' ? 'active' : '' ?>" href="index.php?page=mesajlar&tab=sent">
                            <i class="bi bi-send me-1"></i>Gönderilenler
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php
                    $messages = ($tab === 'inbox') ? $inboxMessages : $sentMessages;
                    if (empty($messages)): ?>
                    <div class="list-group-item text-center text-muted py-5">
                        Mesaj bulunmuyor
                    </div>
                    <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                    <a href="index.php?page=mesajlar&tab=<?= $tab ?>&view=<?= $m['id'] ?>"
                       class="list-group-item list-group-item-action <?= (!$m['is_read'] && $tab === 'inbox') ? 'bg-light' : '' ?> <?= $viewId == $m['id'] ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= e($tab === 'inbox' ? $m['from_name'] : $m['to_name']) ?></strong>
                                <?php if (!$m['is_read'] && $tab === 'inbox'): ?>
                                <span class="badge bg-primary ms-1">Yeni</span>
                                <?php endif; ?>
                                <div class="text-truncate" style="max-width: 300px;">
                                    <?= e($m['subject'] ?: '(Konu yok)') ?>
                                </div>
                            </div>
                            <small class="text-muted"><?= formatDateTime($m['created_at'], 'd.m H:i') ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($viewMessage): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><?= e($viewMessage['subject'] ?: '(Konu yok)') ?></span>
                <a href="index.php?page=mesajlar&tab=<?= $tab ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <strong>Kimden:</strong> <?= e($viewMessage['from_name']) ?><br>
                        <strong>Kime:</strong> <?= e($viewMessage['to_name']) ?>
                    </div>
                    <small class="text-muted"><?= formatDateTime($viewMessage['created_at']) ?></small>
                </div>
                <hr>
                <div class="message-content">
                    <?= nl2br(e($viewMessage['message'])) ?>
                </div>

                <?php if ($viewMessage['to_user'] == $_SESSION['user_id']): ?>
                <hr>
                <form method="POST">
                    <input type="hidden" name="send_message" value="1">
                    <input type="hidden" name="to_user" value="<?= $viewMessage['from_user'] ?>">
                    <input type="hidden" name="subject" value="RE: <?= e($viewMessage['subject']) ?>">
                    <input type="hidden" name="parent_id" value="<?= $viewMessage['id'] ?>">
                    <div class="mb-3">
                        <textarea class="form-control" name="message" rows="3" placeholder="Cevabınız..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-reply me-1"></i>Cevapla
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="send_message" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Mesaj</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kime *</label>
                        <select class="form-select select2" name="to_user" required>
                            <option value="">Seçiniz</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konu</label>
                        <input type="text" class="form-control" name="subject">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mesaj *</label>
                        <textarea class="form-control" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Gönder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
