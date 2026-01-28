<?php
/**
 * SAY HASAR DANIŞMANLIK - KULLANICI YÖNETİMİ
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

try {
    $db = getDB();
    $kullanicilar = $db->query("SELECT * FROM users WHERE is_deleted = 0 ORDER BY role, full_name")->fetchAll();
    $roller = ['ADMIN', 'AVUKAT', 'PERSONEL', 'MUHASEBE', 'SEKRETER'];
} catch (Exception $e) {
    $error = $e->getMessage();
}

// KULLANICI DÜZENLEME
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'duzenle') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $full_name = trim(mb_strtoupper($_POST['full_name'] ?? '', 'UTF-8'));
    $role = $_POST['role'] ?? '';
    
    if ($user_id && $username && $full_name && $role) {
        try {
            $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $full_name, $role, $user_id]);
            header("Location: sistem_kullanici.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// ŞİFRE SIFIRLAMA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sifre') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';
    
    if ($user_id && $new_password) {
        try {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
            header("Location: sistem_kullanici.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// DURUM DEĞİŞTİR (AKTİF/PASİF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'durum') {
    $user_id = intval($_POST['user_id'] ?? 0);
    if ($user_id && $user_id != $_SESSION['user_id']) {
        try {
            $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$user_id]);
            header("Location: sistem_kullanici.php?success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// KULLANICI SİLME
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $admin_sifre = $_POST['admin_sifre'] ?? '';
    $yeni_sorumlu_id = intval($_POST['yeni_sorumlu_id'] ?? 0);
    
    if ($user_id && $user_id != $_SESSION['user_id'] && $admin_sifre) {
        try {
            // Admin şifresini doğrula
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($admin_sifre, $admin['password_hash'])) {
                // Kullanıcının dosyalarını aktar
                if ($yeni_sorumlu_id > 0) {
                    $stmt = $db->prepare("UPDATE cases SET sorumlu_user_id = ? WHERE sorumlu_user_id = ?");
                    $stmt->execute([$yeni_sorumlu_id, $user_id]);
                    
                    $stmt = $db->prepare("UPDATE cases SET assigned_lawyer_user_id = ? WHERE assigned_lawyer_user_id = ?");
                    $stmt->execute([$yeni_sorumlu_id, $user_id]);
                }
                
                // Kullanıcıyı soft delete yap
                $stmt = $db->prepare("UPDATE users SET is_deleted = 1, is_active = 0 WHERE id = ?");
                $stmt->execute([$user_id]);
                
                header("Location: sistem_kullanici.php?success=2");
                exit;
            } else {
                $error = 'ADMİN ŞİFRESİ HATALI!';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-users-cog"></i> KULLANICI YÖNETİMİ</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> 
    <?= $_GET['success'] == '2' ? 'KULLANICI SİLİNDİ VE DOSYALAR AKTARILDI!' : 'İŞLEM BAŞARILI' ?>
</div>
<?php endif; ?>

<!-- KULLANICI LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> KULLANICI LİSTESİ (<?= count($kullanicilar) ?>)</div>
        <a href="tanim_personel.php" class="btn btn-suc"><i class="fas fa-plus"></i> YENİ KULLANICI</a>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>KULLANICI ADI</th>
                    <th>AD SOYAD</th>
                    <th>ROL</th>
                    <th>DURUM</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kullanicilar as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td style="font-weight:700"><i class="fas fa-user" style="color:var(--blue);margin-right:8px"></i><?= e($u['username']) ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td>
                        <span class="badge <?= $u['role'] == 'ADMIN' ? 'aktif' : 'bedeni' ?>">
                            <?= e($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $u['is_active'] ? 'aktif' : 'kapali' ?>">
                            <?= $u['is_active'] ? 'AKTİF' : 'PASİF' ?>
                        </span>
                    </td>
                    <td>
                        <div class="icons">
                            <!-- DÜZENLE -->
                            <button type="button" class="ic edit" onclick="openDuzenleModal(<?= $u['id'] ?>, '<?= e($u['username']) ?>', '<?= e($u['full_name']) ?>', '<?= e($u['role']) ?>')" title="DÜZENLE">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- ŞİFRE SIFIRLA -->
                            <button type="button" class="ic view" onclick="openSifreModal(<?= $u['id'] ?>, '<?= e($u['full_name']) ?>')" title="ŞİFRE SIFIRLA">
                                <i class="fas fa-key"></i>
                            </button>
                            
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <!-- DURUM DEĞİŞTİR -->
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="durum">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="ic <?= $u['is_active'] ? 'del' : 'doc' ?>" title="<?= $u['is_active'] ? 'PASİF YAP' : 'AKTİF YAP' ?>">
                                    <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                                </button>
                            </form>
                            
                            <!-- SİL -->
                            <button type="button" class="ic del" onclick="openSilModal(<?= $u['id'] ?>, '<?= e($u['full_name']) ?>')" title="KULLANICIYI SİL">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DÜZENLE MODAL -->
<div id="duzenleModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center">
    <div style="background:#1e293b;border-radius:12px;padding:30px;width:450px;max-width:90%;border:2px solid #3b82f6">
        <h3 style="margin-bottom:20px;color:#3b82f6;display:flex;align-items:center;gap:10px">
            <i class="fas fa-user-edit"></i> KULLANICI DÜZENLE
        </h3>
        <form method="POST">
            <input type="hidden" name="action" value="duzenle">
            <input type="hidden" name="user_id" id="duzenle_user_id">
            
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">KULLANICI ADI <span class="req">*</span></label>
                <input type="text" name="username" id="duzenle_username" class="frm-in" required>
            </div>
            
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">AD SOYAD <span class="req">*</span></label>
                <input type="text" name="full_name" id="duzenle_full_name" class="frm-in" required>
            </div>
            
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">ROL <span class="req">*</span></label>
                <select name="role" id="duzenle_role" class="frm-sel" required>
                    <?php foreach ($roller as $r): ?>
                    <option value="<?= $r ?>"><?= $r ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-sec" onclick="closeModal('duzenleModal')">İPTAL</button>
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>

<!-- ŞİFRE SIFIRLA MODAL -->
<div id="sifreModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center">
    <div style="background:#1e293b;border-radius:12px;padding:30px;width:400px;max-width:90%;border:2px solid #f59e0b">
        <h3 style="margin-bottom:20px;color:#f59e0b;display:flex;align-items:center;gap:10px">
            <i class="fas fa-key"></i> ŞİFRE SIFIRLA
        </h3>
        <p style="margin-bottom:15px;color:#94a3b8" id="sifre_user_name"></p>
        <form method="POST">
            <input type="hidden" name="action" value="sifre">
            <input type="hidden" name="user_id" id="sifre_user_id">
            
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">YENİ ŞİFRE <span class="req">*</span></label>
                <input type="password" name="new_password" class="frm-in" required minlength="4" placeholder="Yeni şifre girin">
            </div>
            
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-sec" onclick="closeModal('sifreModal')">İPTAL</button>
                <button type="submit" class="btn btn-war"><i class="fas fa-key"></i> ŞİFREYİ DEĞİŞTİR</button>
            </div>
        </form>
    </div>
</div>

<!-- SİL MODAL -->
<div id="silModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center">
    <div style="background:#1e293b;border-radius:12px;padding:30px;width:500px;max-width:90%;border:2px solid #ef4444">
        <h3 style="margin-bottom:20px;color:#ef4444;display:flex;align-items:center;gap:10px">
            <i class="fas fa-exclamation-triangle"></i> KULLANICI SİL
        </h3>
        <p style="margin-bottom:15px;color:#94a3b8">
            <strong id="sil_user_name" style="color:#ef4444"></strong> kullanıcısını silmek üzeresiniz!
        </p>
        <div class="alert alert-warning" style="margin-bottom:15px">
            <i class="fas fa-info-circle"></i> Bu kullanıcının üzerindeki dosyalar başka bir kullanıcıya aktarılacaktır.
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="sil">
            <input type="hidden" name="user_id" id="sil_user_id">
            
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">DOSYALARI AKTAR <span class="req">*</span></label>
                <select name="yeni_sorumlu_id" class="frm-sel" required>
                    <option value="">-- YENİ SORUMLU SEÇ --</option>
                    <?php foreach ($kullanicilar as $k): ?>
                        <?php if ($k['is_active']): ?>
                        <option value="<?= $k['id'] ?>"><?= e($k['full_name']) ?> (<?= $k['role'] ?>)</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">ADMİN ŞİFRENİZ <span class="req">*</span></label>
                <input type="password" name="admin_sifre" class="frm-in" required placeholder="Onaylamak için şifrenizi girin">
            </div>
            
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-sec" onclick="closeModal('silModal')">İPTAL</button>
                <button type="submit" class="btn btn-dan"><i class="fas fa-trash"></i> KULLANICIYI SİL</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDuzenleModal(id, username, fullname, role) {
    document.getElementById('duzenle_user_id').value = id;
    document.getElementById('duzenle_username').value = username;
    document.getElementById('duzenle_full_name').value = fullname;
    document.getElementById('duzenle_role').value = role;
    document.getElementById('duzenleModal').style.display = 'flex';
}

function openSifreModal(id, fullname) {
    document.getElementById('sifre_user_id').value = id;
    document.getElementById('sifre_user_name').innerHTML = '<strong>' + fullname + '</strong> için yeni şifre belirleyin:';
    document.getElementById('sifreModal').style.display = 'flex';
}

function openSilModal(id, fullname) {
    document.getElementById('sil_user_id').value = id;
    document.getElementById('sil_user_name').textContent = fullname;
    // Dropdown'dan kendisini çıkar
    var select = document.querySelector('#silModal select[name="yeni_sorumlu_id"]');
    for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].value == id) {
            select.options[i].style.display = 'none';
        } else {
            select.options[i].style.display = 'block';
        }
    }
    document.getElementById('silModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// ESC tuşu ile modal kapat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('duzenleModal');
        closeModal('sifreModal');
        closeModal('silModal');
    }
});

// Modal dışına tıklayınca kapat
document.querySelectorAll('#duzenleModal, #sifreModal, #silModal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>