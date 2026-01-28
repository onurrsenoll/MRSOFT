<?php
/**
 * MR HASAR DANIŞMANLIK - DOSYA EVRAK YÖNETİMİ
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: dosyalar.php');
    exit;
}

$error = '';

try {
    $db = getDB();
    
    // Dosya bilgisi
    $stmt = $db->prepare("SELECT dosya_no, ad_soyad FROM cases WHERE id = ?");
    $stmt->execute([$id]);
    $dosya = $stmt->fetch();
    
    if (!$dosya) {
        header('Location: dosyalar.php');
        exit;
    }
    
    // Mevcut evraklar
    $stmt = $db->prepare("
        SELECT d.*, u.full_name as yukleyen
        FROM case_documents d
        LEFT JOIN users u ON d.uploaded_by_user_id = u.id
        WHERE d.case_id = ?
        ORDER BY d.uploaded_at DESC
    ");
    $stmt->execute([$id]);
    $evraklar = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Evrak yükleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'yukle') {
    $document_type = trim($_POST['document_type'] ?? '');
    
    if (!empty($_FILES['file']['name']) && $document_type) {
        $file = $_FILES['file'];
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowedTypes)) {
            $error = 'GEÇERSİZ DOSYA TÜRÜ';
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $error = 'DOSYA 10MB\'DAN BÜYÜK OLAMAZ';
        } else {
            try {
                // Dosya adı oluştur
                $newName = $dosya['dosya_no'] . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                    $stmt = $db->prepare("INSERT INTO case_documents (case_id, document_type, file_name, file_path, file_size, uploaded_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $document_type, $file['name'], $newName, $file['size'], $_SESSION['user_id']]);
                    header("Location: dosya_evrak.php?id=$id&success=1");
                    exit;
                } else {
                    $error = 'DOSYA YÜKLENEMEDI';
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    } else {
        $error = 'DOSYA VE EVRAK TİPİ ZORUNLUDUR';
    }
}

// Evrak silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'sil') {
    $evrak_id = intval($_POST['evrak_id'] ?? 0);
    if ($evrak_id) {
        try {
            // Dosyayı sil
            $stmt = $db->prepare("SELECT file_path FROM case_documents WHERE id = ? AND case_id = ?");
            $stmt->execute([$evrak_id, $id]);
            $evrak = $stmt->fetch();
            
            if ($evrak) {
                @unlink(__DIR__ . '/../uploads/' . $evrak['file_path']);
                $stmt = $db->prepare("DELETE FROM case_documents WHERE id = ?");
                $stmt->execute([$evrak_id]);
            }
            header("Location: dosya_evrak.php?id=$id&success=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-file-upload"></i> EVRAK YÖNETİMİ: <?= e($dosya['dosya_no']) ?></div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> İŞLEM BAŞARILI</div>
<?php endif; ?>

<!-- EVRAK YÜKLEME FORMU -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-upload"></i> YENİ EVRAK YÜKLE</div>
    </div>
    <form method="POST" enctype="multipart/form-data" style="padding:20px">
        <input type="hidden" name="action" value="yukle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">EVRAK TİPİ <span class="req">*</span></label>
                <select name="document_type" class="frm-sel" required>
                    <option value="">SEÇİNİZ</option>
                    <option value="KAZA_TUTANAGI">KAZA TUTANAĞI</option>
                    <option value="RUHSAT">RUHSAT</option>
                    <option value="EHLIYET">EHLİYET</option>
                    <option value="KIMLIK">KİMLİK</option>
                    <option value="VEKALETNAME">VEKALETNAME</option>
                    <option value="EKSPER_RAPORU">EKSPER RAPORU</option>
                    <option value="SAGLIK_RAPORU">SAĞLIK RAPORU</option>
                    <option value="FATURA">FATURA</option>
                    <option value="BANKA_DEKONTU">BANKA DEKONTU</option>
                    <option value="DIGER">DİĞER</option>
                </select>
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">DOSYA <span class="req">*</span></label>
                <input type="file" name="file" class="frm-in" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end;gap:10px">
                <button type="submit" class="btn btn-suc"><i class="fas fa-upload"></i> YÜKLE</button>
                <a href="dosya_detay.php?id=<?= $id ?>" class="btn btn-sec"><i class="fas fa-arrow-left"></i> GERİ</a>
            </div>
        </div>
    </form>
</div>

<!-- EVRAK LİSTESİ -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> EVRAK LİSTESİ (<?= count($evraklar) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>EVRAK TİPİ</th><th>DOSYA ADI</th><th>BOYUT</th><th>YÜKLEYEN</th><th>TARİH</th><th>İŞLEM</th></tr></thead>
            <tbody>
                <?php if (empty($evraklar)): ?>
                <tr><td colspan="6" style="text-align:center;padding:20px;color:var(--text2)">EVRAK BULUNAMADI</td></tr>
                <?php else: foreach ($evraklar as $ev): ?>
                <tr>
                    <td><span class="badge aktif"><?= e($ev['document_type']) ?></span></td>
                    <td><?= e($ev['file_name']) ?></td>
                    <td><?= number_format($ev['file_size'] / 1024, 1) ?> KB</td>
                    <td><?= e($ev['yukleyen']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($ev['uploaded_at'])) ?></td>
                    <td>
                        <div class="icons">
                            <a href="../uploads/<?= e($ev['file_path']) ?>" target="_blank" class="ic view" title="GÖRÜNTÜLE"><i class="fas fa-eye"></i></a>
                            <a href="../uploads/<?= e($ev['file_path']) ?>" download class="ic doc" title="İNDİR"><i class="fas fa-download"></i></a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="action" value="sil">
                                <input type="hidden" name="evrak_id" value="<?= $ev['id'] ?>">
                                <button type="submit" class="ic del"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
