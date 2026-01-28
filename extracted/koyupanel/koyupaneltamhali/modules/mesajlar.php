<?php
/**
 * MR HASAR DANIŞMANLIK - MESAJLAR
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';
startSecureSession();



$error = '';
$success = '';
$kullanicilar = [];
$gelenler = [];
$gidenler = [];
$okunmamis = 0;

try {
    $db = getDB();
    $current_user_id = $_SESSION['user_id'];
    
    // Kullanıcılar (kendisi hariç)
    $stmt = $db->prepare("SELECT id, full_name, role FROM users WHERE is_active = 1 AND id != ? ORDER BY full_name");
    $stmt->execute([$current_user_id]);
    $kullanicilar = $stmt->fetchAll();
    
    // MESAJ GÖNDER
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'gonder') {
        $alici_id = intval($_POST['alici_id'] ?? 0);
        $konu = trim($_POST['konu'] ?? '');
        $mesaj = trim($_POST['mesaj'] ?? '');
        
        if ($alici_id && $mesaj) {
            $dosya_adi = null;
            $dosya_yolu = null;
            
            // Dosya yükleme
            if (isset($_FILES['dosya']) && $_FILES['dosya']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
                $filename = $_FILES['dosya']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $new_filename = 'msg_' . time() . '_' . uniqid() . '.' . $ext;
                    $upload_path = __DIR__ . '/../uploads/mesajlar/';
                    
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['dosya']['tmp_name'], $upload_path . $new_filename)) {
                        $dosya_adi = $filename;
                        $dosya_yolu = 'mesajlar/' . $new_filename;
                    }
                } else {
                    $error = 'GEÇERSİZ DOSYA TÜRÜ';
                }
            }
            
            if (empty($error)) {
                $stmt = $db->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, konu, mesaj, dosya_adi, dosya_yolu) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$current_user_id, $alici_id, $konu ?: null, $mesaj, $dosya_adi, $dosya_yolu]);
                header("Location: mesajlar.php?success=1");
                exit;
            }
        } else {
            $error = 'ALICI VE MESAJ ZORUNLUDUR';
        }
    }
    
    // OKUNDU İŞARETLE
    if (isset($_GET['oku'])) {
        $mesaj_id = intval($_GET['oku']);
        $stmt = $db->prepare("UPDATE mesajlar SET okundu = 1, okunma_tarihi = NOW() WHERE id = ? AND alici_id = ?");
        $stmt->execute([$mesaj_id, $current_user_id]);
    }
    
    // TÜMÜNÜ OKUNDU İŞARETLE
    if (isset($_GET['tumunu_oku'])) {
        $stmt = $db->prepare("UPDATE mesajlar SET okundu = 1, okunma_tarihi = NOW() WHERE alici_id = ? AND okundu = 0");
        $stmt->execute([$current_user_id]);
        header("Location: mesajlar.php?success=3");
        exit;
    }
    
    // SİL
    if (isset($_GET['sil'])) {
        $mesaj_id = intval($_GET['sil']);
        $stmt = $db->prepare("DELETE FROM mesajlar WHERE id = ? AND (gonderen_id = ? OR alici_id = ?)");
        $stmt->execute([$mesaj_id, $current_user_id, $current_user_id]);
        header("Location: mesajlar.php?success=2");
        exit;
    }
    
    // Aktif sekme
    $tab = $_GET['tab'] ?? 'gelen';
    
    // Gelen mesajlar
    $stmt = $db->prepare("
        SELECT m.*, u.full_name as gonderen_adi 
        FROM mesajlar m 
        JOIN users u ON m.gonderen_id = u.id 
        WHERE m.alici_id = ? 
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$current_user_id]);
    $gelenler = $stmt->fetchAll();
    
    // Giden mesajlar
    $stmt = $db->prepare("
        SELECT m.*, u.full_name as alici_adi 
        FROM mesajlar m 
        JOIN users u ON m.alici_id = u.id 
        WHERE m.gonderen_id = ? 
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$current_user_id]);
    $gidenler = $stmt->fetchAll();
    
    // Okunmamış sayısı
    $stmt = $db->prepare("SELECT COUNT(*) FROM mesajlar WHERE alici_id = ? AND okundu = 0");
    $stmt->execute([$current_user_id]);
    $okunmamis = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-envelope"></i> MESAJLAR</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> 
    <?php
    $msgs = ['1' => 'MESAJ GÖNDERİLDİ!', '2' => 'MESAJ SİLİNDİ!', '3' => 'TÜM MESAJLAR OKUNDU!'];
    echo $msgs[$_GET['success']] ?? 'İŞLEM BAŞARILI';
    ?>
</div>
<?php endif; ?>

<!-- ÖZET KARTLAR -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-inbox"></i></div>
            <div>
                <div class="card-title">GELEN</div>
                <div class="card-val"><?= count($gelenler) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon <?= $okunmamis > 0 ? 'red' : 'green' ?>"><i class="fas fa-envelope<?= $okunmamis > 0 ? '' : '-open' ?>"></i></div>
            <div>
                <div class="card-title">OKUNMAMIŞ</div>
                <div class="card-val"><?= $okunmamis ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-paper-plane"></i></div>
            <div>
                <div class="card-title">GÖNDERİLEN</div>
                <div class="card-val"><?= count($gidenler) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- YENİ MESAJ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-pen"></i> YENİ MESAJ OLUŞTUR</div>
    </div>
    <form method="POST" enctype="multipart/form-data" style="padding:20px">
        <input type="hidden" name="action" value="gonder">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">ALICI <span class="req">*</span></label>
                <select name="alici_id" class="frm-sel" required>
                    <option value="">SEÇİNİZ...</option>
                    <?php foreach ($kullanicilar as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= e($k['full_name']) ?> (<?= e($k['role']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">KONU</label>
                <input type="text" name="konu" class="frm-in" placeholder="Mesaj konusu...">
            </div>
            <div class="frm-grp" style="grid-column: span 2">
                <label class="frm-lbl">MESAJ <span class="req">*</span></label>
                <textarea name="mesaj" class="frm-ta" rows="3" required placeholder="Mesajınızı yazın..."></textarea>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">DOSYA EKİ</label>
                <input type="file" name="dosya" class="frm-in" style="padding:8px">
                <small style="color:#64748b;font-size:11px">jpg, png, pdf, doc, xls, zip (max 10MB)</small>
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-pri"><i class="fas fa-paper-plane"></i> GÖNDER</button>
            </div>
        </div>
    </form>
</div>

<!-- SEKMELER -->
<div style="display:flex;gap:10px;margin-bottom:20px">
    <a href="?tab=gelen" class="btn <?= $tab == 'gelen' ? 'btn-pri' : 'btn-sec' ?>">
        <i class="fas fa-inbox"></i> GELEN KUTUSU
        <?php if ($okunmamis > 0): ?>
        <span style="background:#ef4444;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;margin-left:5px"><?= $okunmamis ?></span>
        <?php endif; ?>
    </a>
    <a href="?tab=giden" class="btn <?= $tab == 'giden' ? 'btn-pri' : 'btn-sec' ?>">
        <i class="fas fa-paper-plane"></i> GÖNDERİLENLER
    </a>
    <?php if ($okunmamis > 0): ?>
    <a href="?tumunu_oku=1" class="btn btn-suc" style="margin-left:auto" onclick="return confirm('Tüm mesajları okundu olarak işaretlemek istiyor musunuz?')">
        <i class="fas fa-check-double"></i> TÜMÜNÜ OKUNDU İŞARETLE
    </a>
    <?php endif; ?>
</div>

<!-- MESAJ LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title">
            <i class="fas fa-<?= $tab == 'gelen' ? 'inbox' : 'paper-plane' ?>"></i> 
            <?= $tab == 'gelen' ? 'GELEN MESAJLAR' : 'GÖNDERİLEN MESAJLAR' ?>
        </div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:30px"></th>
                    <th><?= $tab == 'gelen' ? 'GÖNDEREN' : 'ALICI' ?></th>
                    <th>KONU</th>
                    <th>MESAJ</th>
                    <th>DOSYA</th>
                    <th>TARİH</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $liste = $tab == 'gelen' ? $gelenler : $gidenler;
                if (empty($liste)): 
                ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2)">
                    <i class="fas fa-envelope-open" style="font-size:40px;margin-bottom:15px;opacity:0.3;display:block"></i>
                    MESAJ BULUNAMADI
                </td></tr>
                <?php else: foreach ($liste as $m): ?>
                <tr style="<?= ($tab == 'gelen' && !$m['okundu']) ? 'background:rgba(59,130,246,0.1);font-weight:600' : '' ?>">
                    <td>
                        <?php if ($tab == 'gelen' && !$m['okundu']): ?>
                        <span style="width:10px;height:10px;background:#3b82f6;border-radius:50%;display:inline-block" title="Okunmadı"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <i class="fas fa-user" style="color:var(--blue);margin-right:8px"></i>
                        <?= e($tab == 'gelen' ? $m['gonderen_adi'] : $m['alici_adi']) ?>
                    </td>
                    <td><?= e($m['konu'] ?: '-') ?></td>
                    <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= e(mb_substr($m['mesaj'], 0, 80)) ?><?= mb_strlen($m['mesaj']) > 80 ? '...' : '' ?>
                    </td>
                    <td>
                        <?php if ($m['dosya_adi']): ?>
                        <a href="../uploads/<?= e($m['dosya_yolu']) ?>" target="_blank" class="btn btn-sec" style="padding:5px 10px;font-size:11px">
                            <i class="fas fa-paperclip"></i> <?= e(mb_substr($m['dosya_adi'], 0, 15)) ?>...
                        </a>
                        <?php else: ?>
                        <span style="color:#64748b">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px">
                        <?= date('d.m.Y H:i', strtotime($m['created_at'])) ?>
                        <?php if ($tab == 'gelen' && $m['okundu'] && $m['okunma_tarihi']): ?>
                        <div style="color:#22c55e;font-size:10px"><i class="fas fa-check-double"></i> Okundu</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="icons">
                            <button type="button" class="ic view" title="GÖRÜNTÜLE" onclick='showMesaj(<?= json_encode($m) ?>, "<?= e($tab == 'gelen' ? $m['gonderen_adi'] : $m['alici_adi']) ?>")'>
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($tab == 'gelen' && !$m['okundu']): ?>
                            <a href="?oku=<?= $m['id'] ?>&tab=gelen" class="ic doc" title="OKUNDU İŞARETLE">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>
                            <a href="?sil=<?= $m['id'] ?>&tab=<?= $tab ?>" class="ic del" title="SİL" onclick="return confirm('Mesajı silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MESAJ GÖRÜNTÜLEME MODAL -->
<div id="mesajModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center">
    <div style="background:#1e293b;border-radius:12px;padding:30px;width:600px;max-width:90%;border:2px solid #3b82f6;max-height:80vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="color:#3b82f6;display:flex;align-items:center;gap:10px;margin:0">
                <i class="fas fa-envelope-open"></i> <span id="modal_konu">MESAJ</span>
            </h3>
            <button onclick="closeModal('mesajModal')" style="background:none;border:none;color:#94a3b8;font-size:24px;cursor:pointer">&times;</button>
        </div>
        <div style="margin-bottom:15px;padding-bottom:15px;border-bottom:1px solid #334155">
            <div style="color:#94a3b8;font-size:12px;margin-bottom:5px">
                <i class="fas fa-user"></i> <span id="modal_kisi"></span>
            </div>
            <div style="color:#64748b;font-size:11px">
                <i class="fas fa-clock"></i> <span id="modal_tarih"></span>
            </div>
        </div>
        <div id="modal_mesaj" style="color:#f1f5f9;line-height:1.8;white-space:pre-wrap"></div>
        <div id="modal_dosya" style="margin-top:20px;display:none">
            <a id="modal_dosya_link" href="#" target="_blank" class="btn btn-sec">
                <i class="fas fa-download"></i> <span id="modal_dosya_adi"></span>
            </a>
        </div>
    </div>
</div>

<script>
function showMesaj(data, kisi) {
    document.getElementById('modal_konu').textContent = data.konu || 'KONU YOK';
    document.getElementById('modal_kisi').textContent = kisi;
    document.getElementById('modal_tarih').textContent = data.created_at;
    document.getElementById('modal_mesaj').textContent = data.mesaj;
    
    if (data.dosya_adi) {
        document.getElementById('modal_dosya').style.display = 'block';
        document.getElementById('modal_dosya_link').href = '../uploads/' + data.dosya_yolu;
        document.getElementById('modal_dosya_adi').textContent = data.dosya_adi;
    } else {
        document.getElementById('modal_dosya').style.display = 'none';
    }
    
    document.getElementById('mesajModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal('mesajModal');
});

document.getElementById('mesajModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal('mesajModal');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

