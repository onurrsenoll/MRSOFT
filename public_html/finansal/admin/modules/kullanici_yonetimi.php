<?php
/**
 * MR HASAR DANIŞMANLIK - Kullanıcı Yönetimi
 * HERZAMAN FARKEDER
 */

// Admin kontrolü
if (!hasRole('admin')) {
    setFlash('error', 'Bu sayfaya erişim yetkiniz yok.');
    header('Location: index.php');
    exit;
}

// Silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id == $_SESSION['user_id']) {
        setFlash('error', 'Kendinizi silemezsiniz.');
    } else {
        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Kullanıcı pasife alındı.');
    }
    header('Location: index.php?page=kullanici_yonetimi');
    exit;
}

// Kayıt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $name = clean($_POST['name']);
    $phone = clean($_POST['phone'] ?? '');
    $role = clean($_POST['role'] ?? 'user');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($name)) {
        setFlash('error', 'Kullanıcı adı, e-posta ve ad zorunludur.');
    } else {
        if ($id > 0) {
            $sql = "UPDATE users SET username=?, email=?, name=?, phone=?, role=?";
            $params = [$username, $email, $name, $phone, $role];

            if (!empty($password)) {
                $sql .= ", password=?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id=?";
            $params[] = $id;

            $pdo->prepare($sql)->execute($params);
            setFlash('success', 'Kullanıcı güncellendi.');
        } else {
            if (empty($password)) {
                setFlash('error', 'Yeni kullanıcı için şifre zorunludur.');
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $name, $phone, $role]);
                setFlash('success', 'Yeni kullanıcı eklendi.');
            }
        }
        header('Location: index.php?page=kullanici_yonetimi');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM users WHERE is_active = 1 ORDER BY name");
$users = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-people-fill me-2"></i>Kullanıcı Yönetimi</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item active">Kullanıcılar</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="bi bi-person-plus me-1"></i> Yeni Kullanıcı
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Kullanıcı Adı</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th>Rol</th>
                        <th>Son Giriş</th>
                        <th class="text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= e($u['name']) ?></strong></td>
                        <td><?= e($u['username']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['phone'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?php
                                echo match($u['role']) {
                                    'admin' => 'danger',
                                    'manager' => 'warning',
                                    'lawyer' => 'info',
                                    default => 'secondary'
                                };
                            ?>">
                                <?= strtoupper($u['role']) ?>
                            </span>
                        </td>
                        <td><?= $u['last_login'] ? formatDateTime($u['last_login']) : '-' ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning btn-edit"
                                    data-user='<?= json_encode($u) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="index.php?page=kullanici_yonetimi&delete=<?= $u['id'] ?>"
                               class="btn btn-sm btn-outline-danger btn-delete" data-name="<?= e($u['name']) ?>">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="userId" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Yeni Kullanıcı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad *</label>
                        <input type="text" class="form-control" name="name" id="userName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı *</label>
                        <input type="text" class="form-control" name="username" id="userUsername" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta *</label>
                        <input type="email" class="form-control" name="email" id="userEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="text" class="form-control" name="phone" id="userPhone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role" id="userRole">
                            <option value="user">Kullanıcı</option>
                            <option value="lawyer">Avukat</option>
                            <option value="manager">Yönetici</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre <small class="text-muted" id="passwordHint">(Yeni kullanıcı için zorunlu)</small></label>
                        <input type="password" class="form-control" name="password" id="userPassword">
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
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const user = JSON.parse(this.dataset.user);
        document.getElementById('userId').value = user.id;
        document.getElementById('modalTitle').textContent = 'Kullanıcı Düzenle';
        document.getElementById('userName').value = user.name || '';
        document.getElementById('userUsername').value = user.username || '';
        document.getElementById('userEmail').value = user.email || '';
        document.getElementById('userPhone').value = user.phone || '';
        document.getElementById('userRole').value = user.role || 'user';
        document.getElementById('userPassword').value = '';
        document.getElementById('passwordHint').textContent = '(Boş bırakılırsa değişmez)';
        new bootstrap.Modal(document.getElementById('userModal')).show();
    });
});

document.getElementById('userModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('userId').value = 0;
    document.getElementById('modalTitle').textContent = 'Yeni Kullanıcı';
    document.getElementById('passwordHint').textContent = '(Yeni kullanıcı için zorunlu)';
    this.querySelector('form').reset();
});
</script>
