<?php
/**
 * MR HASAR DANIŞMANLIK - PERSONEL YÖNETİMİ
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$personeller = [];

try {
    $db = getDB();
    $personeller = $db->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
    $username = trim(mb_strtoupper($_POST['username'] ?? '', 'UTF-8'));
    $full_name = trim(mb_strtoupper($_POST['full_name'] ?? '', 'UTF-8'));
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');

    // Email boşsa benzersiz placeholder oluştur
    if (empty($email)) {
        $email = strtolower($username) . '@mrhasar.local';
    }

    if ($username && $full_name && $role && $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password_hash, role, full_name, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hash, $role, $full_name, $email]);
            header("Location: tanim_personel.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'TÜM ALANLAR ZORUNLUDUR';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-id-card"></i> PERSONEL YÖNETİMİ</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- YENİ EKLE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ PERSONEL EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">KULLANICI ADI</label>
                <input type="text" name="username" class="frm-in" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">AD SOYAD</label>
                <input type="text" name="full_name" class="frm-in" required>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">E-POSTA <small style="color:var(--text3)">(Opsiyonel)</small></label>
                <input type="email" name="email" class="frm-in" placeholder="ornek@email.com">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ROL</label>
                <select name="role" class="frm-sel" required>
                    <option value="">SEÇİNİZ</option>
                    <option value="ADMIN">ADMİN</option>
                    <option value="OPERASYON">OPERASYON</option>
                    <option value="AVUKAT">AVUKAT</option>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ŞİFRE</label>
                <input type="password" name="password" class="frm-in" required>
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> EKLE</button>
            </div>
        </div>
    </form>
</div>

<!-- LİSTE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> PERSONEL LİSTESİ</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>KULLANICI</th><th>AD SOYAD</th><th>ROL</th><th>DURUM</th></tr></thead>
            <tbody>
                <?php foreach ($personeller as $p): ?>
                <tr>
                    <td style="font-weight:700"><?= e($p['username']) ?></td>
                    <td><?= e($p['full_name']) ?></td>
                    <td><span class="badge <?= strtolower($p['role']) ?>"><?= e($p['role']) ?></span></td>
                    <td><span class="badge <?= $p['is_active'] ? 'aktif' : 'kapali' ?>"><?= $p['is_active'] ? 'AKTİF' : 'PASİF' ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
