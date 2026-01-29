<?php
/**
 * MR HASAR DANIŞMANLIK - PERSONEL YÖNETİMİ v2.0
 * Kapsamlı Personel ve Kullanıcı Yönetimi
 * Maaş, Prim, Yetki Entegrasyonu
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$personeller = [];
$editPersonel = null;

try {
    $db = getDB();

    // Personelleri getir
    $personeller = $db->query("
        SELECT u.*,
               COALESCE(u.maas, 0) as maas,
               COALESCE(u.prim_adk, 0) as prim_adk,
               COALESCE(u.prim_bedeni, 0) as prim_bedeni,
               COALESCE(u.prim_diger, 0) as prim_diger
        FROM users u
        ORDER BY u.user_type DESC, u.role, u.full_name
    ")->fetchAll();

    // Düzenleme için personel getir
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editPersonel = $stmt->fetch();
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

// EKLEME
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] == 'ekle') {
        $username = trim(mb_strtoupper($_POST['username'] ?? '', 'UTF-8'));
        $full_name = trim(mb_strtoupper($_POST['full_name'] ?? '', 'UTF-8'));
        $role = $_POST['role'] ?? '';
        $password = $_POST['password'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $user_type = $_POST['user_type'] ?? 'PERSONEL';
        $maas = floatval($_POST['maas'] ?? 0);
        $prim_adk = floatval($_POST['prim_adk'] ?? 0);
        $prim_bedeni = floatval($_POST['prim_bedeni'] ?? 0);
        $prim_diger = floatval($_POST['prim_diger'] ?? 0);
        $telefon = trim($_POST['telefon'] ?? '');

        // Email boşsa benzersiz placeholder oluştur
        if (empty($email)) {
            $email = strtolower($username) . '@mrhasar.local';
        }

        if ($username && $full_name && $role && $password) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    INSERT INTO users (username, password_hash, role, full_name, email, user_type, maas, prim_adk, prim_bedeni, prim_diger, telefon)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$username, $hash, $role, $full_name, $email, $user_type, $maas, $prim_adk, $prim_bedeni, $prim_diger, $telefon]);
                header("Location: tanim_personel.php?success=eklendi");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = 'KULLANICI ADI, AD SOYAD, ROL VE ŞİFRE ZORUNLUDUR';
        }
    }

    // GÜNCELLEME
    elseif ($_POST['action'] == 'guncelle') {
        $id = intval($_POST['id'] ?? 0);
        $username = trim(mb_strtoupper($_POST['username'] ?? '', 'UTF-8'));
        $full_name = trim(mb_strtoupper($_POST['full_name'] ?? '', 'UTF-8'));
        $role = $_POST['role'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $user_type = $_POST['user_type'] ?? 'PERSONEL';
        $maas = floatval($_POST['maas'] ?? 0);
        $prim_adk = floatval($_POST['prim_adk'] ?? 0);
        $prim_bedeni = floatval($_POST['prim_bedeni'] ?? 0);
        $prim_diger = floatval($_POST['prim_diger'] ?? 0);
        $telefon = trim($_POST['telefon'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $new_password = $_POST['new_password'] ?? '';

        if ($id && $username && $full_name && $role) {
            try {
                if (!empty($new_password)) {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        UPDATE users SET
                            username = ?, full_name = ?, role = ?, email = ?, user_type = ?,
                            maas = ?, prim_adk = ?, prim_bedeni = ?, prim_diger = ?, telefon = ?,
                            is_active = ?, password_hash = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $full_name, $role, $email, $user_type, $maas, $prim_adk, $prim_bedeni, $prim_diger, $telefon, $is_active, $hash, $id]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE users SET
                            username = ?, full_name = ?, role = ?, email = ?, user_type = ?,
                            maas = ?, prim_adk = ?, prim_bedeni = ?, prim_diger = ?, telefon = ?,
                            is_active = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $full_name, $role, $email, $user_type, $maas, $prim_adk, $prim_bedeni, $prim_diger, $telefon, $is_active, $id]);
                }
                header("Location: tanim_personel.php?success=guncellendi");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = 'ZORUNLU ALANLAR EKSİK';
        }
    }

    // SİLME
    elseif ($_POST['action'] == 'sil') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            try {
                // Admin kendini silemesin
                if ($id == $_SESSION['user_id']) {
                    $error = 'KENDİNİZİ SİLEMEZSİNİZ';
                } else {
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    header("Location: tanim_personel.php?success=silindi");
                    exit;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    // DURUM DEĞİŞTİR
    elseif ($_POST['action'] == 'toggle_durum') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            try {
                $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: tanim_personel.php?success=durum");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Personel Modülü Özel Stiller */
.user-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}
.user-type-badge.personel {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.1));
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
}
.user-type-badge.harici {
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(139, 92, 246, 0.1));
    color: #a855f7;
    border: 1px solid rgba(168, 85, 247, 0.3);
}
.action-btns {
    display: flex;
    gap: 5px;
}
.action-btns .btn-sm {
    padding: 6px 10px;
    font-size: 11px;
    border-radius: 6px;
}
.btn-edit { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
.btn-edit:hover { background: #3b82f6; color: #fff; }
.btn-del { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.btn-del:hover { background: #ef4444; color: #fff; }
.btn-perm { background: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3); }
.btn-perm:hover { background: #a855f7; color: #fff; }
.btn-toggle { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
.btn-toggle:hover { background: #f59e0b; color: #fff; }

.salary-info {
    font-size: 11px;
    color: var(--text3);
}
.salary-info strong {
    color: var(--mr-blue);
}

/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: var(--bg-card);
    border-radius: 16px;
    width: 95%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid var(--border);
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
}
.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid var(--border);
}
.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--mr-blue);
}
.modal-close {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: none;
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    cursor: pointer;
    font-size: 14px;
}
.modal-close:hover { background: #ef4444; color: #fff; }
.modal-body { padding: 20px; }

.form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}
@media (max-width: 768px) {
    .form-grid-3 { grid-template-columns: 1fr; }
}

.section-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--mr-blue);
    margin: 20px 0 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
    text-transform: uppercase;
}
.section-title:first-child { margin-top: 0; }

.toggle-switch {
    display: flex;
    align-items: center;
    gap: 10px;
}
.toggle-switch input[type="checkbox"] {
    width: 44px;
    height: 24px;
    appearance: none;
    background: var(--border);
    border-radius: 12px;
    position: relative;
    cursor: pointer;
    transition: 0.3s;
}
.toggle-switch input[type="checkbox"]:checked {
    background: #22c55e;
}
.toggle-switch input[type="checkbox"]::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: 0.3s;
}
.toggle-switch input[type="checkbox"]:checked::before {
    left: 23px;
}
</style>

<div class="page-title"><i class="fas fa-users-cog"></i> PERSONEL VE KULLANICI YÖNETİMİ</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i>
    <?php
    switch($_GET['success']) {
        case 'eklendi': echo 'YENİ KULLANICI EKLENDİ'; break;
        case 'guncellendi': echo 'KULLANICI GÜNCELLENDİ'; break;
        case 'silindi': echo 'KULLANICI SİLİNDİ'; break;
        case 'durum': echo 'DURUM DEĞİŞTİRİLDİ'; break;
        default: echo 'İŞLEM BAŞARILI';
    }
    ?>
</div>
<?php endif; ?>

<!-- YENİ EKLE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-user-plus"></i> YENİ KULLANICI EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">

        <div class="section-title"><i class="fas fa-user"></i> TEMEL BİLGİLER</div>
        <div class="form-grid-3">
            <div class="frm-grp">
                <label class="frm-lbl">KULLANICI TİPİ</label>
                <select name="user_type" class="frm-sel" required>
                    <option value="PERSONEL">PERSONEL (Şirket Çalışanı)</option>
                    <option value="HARICI">HARİCİ KULLANICI</option>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">KULLANICI ADI</label>
                <input type="text" name="username" class="frm-in" required placeholder="Giriş için kullanılacak">
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
                <label class="frm-lbl">TELEFON <small style="color:var(--text3)">(Opsiyonel)</small></label>
                <input type="tel" name="telefon" class="frm-in" placeholder="05XX XXX XX XX">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ROL / YETKİ SEVİYESİ</label>
                <select name="role" class="frm-sel" required>
                    <option value="">SEÇİNİZ</option>
                    <option value="ADMIN">ADMİN (Tam Yetki)</option>
                    <option value="OPERASYON">OPERASYON</option>
                    <option value="AVUKAT">AVUKAT</option>
                    <option value="MUHASEBE">MUHASEBE</option>
                    <option value="EKSPER">EKSPER</option>
                    <option value="IZLEYICI">İZLEYİCİ (Sadece Görüntüleme)</option>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ŞİFRE</label>
                <input type="password" name="password" class="frm-in" required placeholder="En az 6 karakter">
            </div>
        </div>

        <div class="section-title"><i class="fas fa-money-bill-wave"></i> MAAŞ VE PRİM BİLGİLERİ</div>
        <div class="form-grid-3">
            <div class="frm-grp">
                <label class="frm-lbl">AYLIK MAAŞ (₺)</label>
                <input type="number" name="maas" class="frm-in" value="0" min="0" step="0.01">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ADK DOSYA PRİMİ (₺)</label>
                <input type="number" name="prim_adk" class="frm-in" value="0" min="0" step="0.01" placeholder="Her ADK dosyası için">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">BEDENİ HASAR PRİMİ (₺)</label>
                <input type="number" name="prim_bedeni" class="frm-in" value="0" min="0" step="0.01" placeholder="Her bedeni dosya için">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">DİĞER DOSYA PRİMİ (₺)</label>
                <input type="number" name="prim_diger" class="frm-in" value="0" min="0" step="0.01" placeholder="Diğer dosya türleri için">
            </div>
        </div>

        <div style="margin-top:20px; display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> KAYDET</button>
            <a href="sistem_yetki.php" class="btn btn-secondary"><i class="fas fa-user-shield"></i> YETKİ YÖNETİMİ</a>
        </div>
    </form>
</div>

<!-- LİSTE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> KULLANICI LİSTESİ (<?= count($personeller) ?> Kayıt)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>TİP</th>
                    <th>KULLANICI</th>
                    <th>AD SOYAD</th>
                    <th>ROL</th>
                    <th>MAAŞ / PRİM</th>
                    <th>DURUM</th>
                    <th style="width:200px">İŞLEMLER</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($personeller as $p): ?>
                <tr>
                    <td>
                        <span class="user-type-badge <?= strtolower($p['user_type'] ?? 'personel') ?>">
                            <i class="fas fa-<?= ($p['user_type'] ?? 'PERSONEL') == 'PERSONEL' ? 'id-badge' : 'user-tie' ?>"></i>
                            <?= e($p['user_type'] ?? 'PERSONEL') ?>
                        </span>
                    </td>
                    <td style="font-weight:700"><?= e($p['username']) ?></td>
                    <td>
                        <?= e($p['full_name']) ?>
                        <?php if (!empty($p['telefon'])): ?>
                        <br><small style="color:var(--text3)"><i class="fas fa-phone"></i> <?= e($p['telefon']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?= strtolower($p['role']) ?>"><?= e($p['role']) ?></span></td>
                    <td>
                        <div class="salary-info">
                            <strong>₺<?= number_format($p['maas'] ?? 0, 0, ',', '.') ?></strong> /ay
                            <?php if (($p['prim_adk'] ?? 0) > 0 || ($p['prim_bedeni'] ?? 0) > 0): ?>
                            <br>
                            <small>
                                <?php if (($p['prim_adk'] ?? 0) > 0): ?>ADK: ₺<?= number_format($p['prim_adk'], 0, ',', '.') ?> <?php endif; ?>
                                <?php if (($p['prim_bedeni'] ?? 0) > 0): ?>BH: ₺<?= number_format($p['prim_bedeni'], 0, ',', '.') ?><?php endif; ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $p['is_active'] ? 'aktif' : 'kapali' ?>">
                            <?= $p['is_active'] ? 'AKTİF' : 'PASİF' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-sm btn-edit" onclick="openEditModal(<?= $p['id'] ?>)" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="sistem_yetki.php?user_id=<?= $p['id'] ?>" class="btn-sm btn-perm" title="Yetkiler">
                                <i class="fas fa-user-shield"></i>
                            </a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Durumu değiştirmek istediğinize emin misiniz?')">
                                <input type="hidden" name="action" value="toggle_durum">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-sm btn-toggle" title="Durum Değiştir">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            </form>
                            <?php if ($p['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">
                                <input type="hidden" name="action" value="sil">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-sm btn-del" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DÜZENLEME MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-user-edit"></i> KULLANICI DÜZENLE</div>
            <button type="button" class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="guncelle">
                <input type="hidden" name="id" id="edit_id">

                <div class="section-title"><i class="fas fa-user"></i> TEMEL BİLGİLER</div>
                <div class="form-grid-3">
                    <div class="frm-grp">
                        <label class="frm-lbl">KULLANICI TİPİ</label>
                        <select name="user_type" id="edit_user_type" class="frm-sel" required>
                            <option value="PERSONEL">PERSONEL</option>
                            <option value="HARICI">HARİCİ KULLANICI</option>
                        </select>
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">KULLANICI ADI</label>
                        <input type="text" name="username" id="edit_username" class="frm-in" required>
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">AD SOYAD</label>
                        <input type="text" name="full_name" id="edit_full_name" class="frm-in" required>
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">E-POSTA</label>
                        <input type="email" name="email" id="edit_email" class="frm-in">
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">TELEFON</label>
                        <input type="tel" name="telefon" id="edit_telefon" class="frm-in">
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">ROL</label>
                        <select name="role" id="edit_role" class="frm-sel" required>
                            <option value="ADMIN">ADMİN</option>
                            <option value="OPERASYON">OPERASYON</option>
                            <option value="AVUKAT">AVUKAT</option>
                            <option value="MUHASEBE">MUHASEBE</option>
                            <option value="EKSPER">EKSPER</option>
                            <option value="IZLEYICI">İZLEYİCİ</option>
                        </select>
                    </div>
                </div>

                <div class="section-title"><i class="fas fa-money-bill-wave"></i> MAAŞ VE PRİM</div>
                <div class="form-grid-3">
                    <div class="frm-grp">
                        <label class="frm-lbl">AYLIK MAAŞ (₺)</label>
                        <input type="number" name="maas" id="edit_maas" class="frm-in" value="0" min="0" step="0.01">
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">ADK PRİMİ (₺)</label>
                        <input type="number" name="prim_adk" id="edit_prim_adk" class="frm-in" value="0" min="0" step="0.01">
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">BEDENİ PRİMİ (₺)</label>
                        <input type="number" name="prim_bedeni" id="edit_prim_bedeni" class="frm-in" value="0" min="0" step="0.01">
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">DİĞER PRİM (₺)</label>
                        <input type="number" name="prim_diger" id="edit_prim_diger" class="frm-in" value="0" min="0" step="0.01">
                    </div>
                </div>

                <div class="section-title"><i class="fas fa-cog"></i> DURUM VE ŞİFRE</div>
                <div class="form-grid-3">
                    <div class="frm-grp">
                        <label class="frm-lbl">DURUM</label>
                        <div class="toggle-switch">
                            <input type="checkbox" name="is_active" id="edit_is_active" checked>
                            <span>Aktif</span>
                        </div>
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">YENİ ŞİFRE <small style="color:var(--text3)">(Değiştirmek için)</small></label>
                        <input type="password" name="new_password" class="frm-in" placeholder="Boş bırakılırsa değişmez">
                    </div>
                </div>

                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> GÜNCELLE</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()"><i class="fas fa-times"></i> İPTAL</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Personel verileri
const personelData = <?= json_encode($personeller) ?>;

function openEditModal(id) {
    const p = personelData.find(x => x.id == id);
    if (!p) return;

    document.getElementById('edit_id').value = p.id;
    document.getElementById('edit_user_type').value = p.user_type || 'PERSONEL';
    document.getElementById('edit_username').value = p.username || '';
    document.getElementById('edit_full_name').value = p.full_name || '';
    document.getElementById('edit_email').value = p.email || '';
    document.getElementById('edit_telefon').value = p.telefon || '';
    document.getElementById('edit_role').value = p.role || '';
    document.getElementById('edit_maas').value = p.maas || 0;
    document.getElementById('edit_prim_adk').value = p.prim_adk || 0;
    document.getElementById('edit_prim_bedeni').value = p.prim_bedeni || 0;
    document.getElementById('edit_prim_diger').value = p.prim_diger || 0;
    document.getElementById('edit_is_active').checked = p.is_active == 1;

    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

// Modal dışına tıklayınca kapat
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// ESC tuşu ile kapat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
