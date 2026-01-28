<?php
/**
 * SAY HASAR DANIŞMANLIK - YÖNLENDİRENLER
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 */

require_once __DIR__ . '/../config.php';

$error = '';
$success = '';
$yonlendirenler = [];

try {
    $db = getDB();
    
    // Kasalar
    $kasalar = $db->query("SELECT id, name, balance FROM cashboxes WHERE is_active = 1 ORDER BY name")->fetchAll();
    
    // EKLEME İŞLEMİ
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'ekle') {
        $ad_soyad = trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8'));
        $telefon = trim($_POST['telefon'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $adk_ucret = floatval($_POST['adk_ucret'] ?? 0);
        $bh_ucret = floatval($_POST['bh_ucret'] ?? 0);
        $mdk_ucret = floatval($_POST['mdk_ucret'] ?? 0);
        $diger_ucret = floatval($_POST['diger_ucret'] ?? 0);
        
        if ($ad_soyad) {
            $stmt = $db->prepare("INSERT INTO yonlendirenler (ad_soyad, telefon, email, adk_ucret, bh_ucret, mdk_ucret, diger_ucret, bakiye, durum) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)");
            $stmt->execute([$ad_soyad, $telefon, $email, $adk_ucret, $bh_ucret, $mdk_ucret, $diger_ucret]);
            header("Location: yonlendiren.php?success=1");
            exit;
        }
    }
    
    // DÜZENLEME İŞLEMİ
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'duzenle') {
        $id = intval($_POST['id'] ?? 0);
        $ad_soyad = trim(mb_strtoupper($_POST['ad_soyad'] ?? '', 'UTF-8'));
        $telefon = trim($_POST['telefon'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $adk_ucret = floatval($_POST['adk_ucret'] ?? 0);
        $bh_ucret = floatval($_POST['bh_ucret'] ?? 0);
        $mdk_ucret = floatval($_POST['mdk_ucret'] ?? 0);
        $diger_ucret = floatval($_POST['diger_ucret'] ?? 0);
        $durum = intval($_POST['durum'] ?? 1);
        
        if ($id && $ad_soyad) {
            $stmt = $db->prepare("UPDATE yonlendirenler SET ad_soyad=?, telefon=?, email=?, adk_ucret=?, bh_ucret=?, mdk_ucret=?, diger_ucret=?, durum=? WHERE id=?");
            $stmt->execute([$ad_soyad, $telefon, $email, $adk_ucret, $bh_ucret, $mdk_ucret, $diger_ucret, $durum, $id]);
            header("Location: yonlendiren.php?success=3");
            exit;
        }
    }
    
    // ÖDEME İŞLEMİ
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'odeme') {
        $yonlendiren_id = intval($_POST['yonlendiren_id'] ?? 0);
        $tutar = floatval($_POST['tutar'] ?? 0);
        $cashbox_id = intval($_POST['cashbox_id'] ?? 0);
        $aciklama = trim($_POST['aciklama'] ?? '');
        
        if ($yonlendiren_id && $tutar > 0 && $cashbox_id) {
            $db->beginTransaction();
            
            try {
                // Ödeme kaydı
                $stmt = $db->prepare("INSERT INTO yonlendiren_odemeler (yonlendiren_id, tutar, odeme_tipi, cashbox_id, aciklama, created_by) VALUES (?, ?, 'ODEME', ?, ?, ?)");
                $stmt->execute([$yonlendiren_id, $tutar, $cashbox_id, $aciklama, $_SESSION['user_id']]);
                
                // Yönlendiren bakiyesini düş
                $stmt = $db->prepare("UPDATE yonlendirenler SET bakiye = bakiye - ? WHERE id = ?");
                $stmt->execute([$tutar, $yonlendiren_id]);
                
                // Kasadan düş
                $stmt = $db->prepare("UPDATE cashboxes SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$tutar, $cashbox_id]);
                
                // Finance transaction
                $stmt = $db->prepare("INSERT INTO finance_transactions (cashbox_id, transaction_type, amount, description, reference_type, reference_id, created_by_user_id, transaction_date) VALUES (?, 'GIDER', ?, ?, 'yonlendiren_odeme', ?, ?, CURDATE())");
                $stmt->execute([$cashbox_id, $tutar, "YÖNLENDİREN ÖDEMESİ: $aciklama", $yonlendiren_id, $_SESSION['user_id']]);
                
                $db->commit();
                header("Location: yonlendiren.php?success=4");
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $error = $e->getMessage();
            }
        } else {
            $error = 'TÜM ALANLARI DOLDURUNUZ';
        }
    }
    
    // SİLME İŞLEMİ
    if (isset($_GET['sil']) && hasRole(['ADMIN'])) {
        $stmt = $db->prepare("DELETE FROM yonlendirenler WHERE id = ?");
        $stmt->execute([intval($_GET['sil'])]);
        header("Location: yonlendiren.php?success=2");
        exit;
    }
    
    // LİSTELEME
    $sql = "SELECT y.*, 
            (SELECT COUNT(*) FROM cases c WHERE c.source_type = 'YONLENDIREN' AND c.yonlendiren_id = y.id) as dosya_sayisi
            FROM yonlendirenler y WHERE 1=1";
    $params = [];
    
    if (!empty($_GET['ara'])) {
        $sql .= " AND y.ad_soyad LIKE ?";
        $params[] = '%' . $_GET['ara'] . '%';
    }
    if (isset($_GET['durum']) && $_GET['durum'] !== '') {
        $sql .= " AND y.durum = ?";
        $params[] = intval($_GET['durum']);
    }
    $sql .= " ORDER BY y.id DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $yonlendirenler = $stmt->fetchAll();
    
    // Toplam borç
    $toplamBorc = array_sum(array_column($yonlendirenler, 'bakiye'));
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-directions"></i> YÖNLENDİRENLER</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> 
    <?php
    $msgs = ['1' => 'YÖNLENDİREN EKLENDİ!', '2' => 'YÖNLENDİREN SİLİNDİ!', '3' => 'YÖNLENDİREN GÜNCELLENDİ!', '4' => 'ÖDEME YAPILDI!'];
    echo $msgs[$_GET['success']] ?? 'İŞLEM BAŞARILI';
    ?>
</div>
<?php endif; ?>

<!-- ÖZET KARTLAR -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-users"></i></div>
            <div>
                <div class="card-title">TOPLAM YÖNLENDİREN</div>
                <div class="card-val"><?= count($yonlendirenler) ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon red"><i class="fas fa-lira-sign"></i></div>
            <div>
                <div class="card-title">TOPLAM BORÇ</div>
                <div class="card-val">₺<?= number_format($toplamBorc, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- FİLTRE -->
<div class="filter">
    <form method="GET" class="filter-grid">
        <div class="f-grp">
            <label class="f-lbl">YÖNLENDİREN ADI</label>
            <input type="text" name="ara" class="f-in" placeholder="Ara..." value="<?= e($_GET['ara'] ?? '') ?>">
        </div>
        <div class="f-grp">
            <label class="f-lbl">DURUM</label>
            <select name="durum" class="f-sel">
                <option value="">TÜMÜ</option>
                <option value="1" <?= (isset($_GET['durum']) && $_GET['durum'] == '1') ? 'selected' : '' ?>>AKTİF</option>
                <option value="0" <?= (isset($_GET['durum']) && $_GET['durum'] == '0') ? 'selected' : '' ?>>PASİF</option>
            </select>
        </div>
        <div class="f-grp" style="align-self:end">
            <button type="submit" class="btn btn-pri"><i class="fas fa-search"></i> FİLTRELE</button>
        </div>
    </form>
</div>

<!-- YENİ EKLE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-plus"></i> YENİ YÖNLENDİREN EKLE</div>
    </div>
    <form method="POST" style="padding:20px">
        <input type="hidden" name="action" value="ekle">
        <div class="form-grid">
            <div class="frm-grp">
                <label class="frm-lbl">AD SOYAD / FİRMA <span class="req">*</span></label>
                <input type="text" name="ad_soyad" class="frm-in" required placeholder="YÖNLENDİREN ADI">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">TELEFON</label>
                <input type="text" name="telefon" class="frm-in" placeholder="05XX XXX XX XX">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">E-POSTA</label>
                <input type="email" name="email" class="frm-in" placeholder="email@example.com">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">ADK ÜCRETİ (₺)</label>
                <input type="number" name="adk_ucret" class="frm-in" value="0" min="0" step="0.01">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">BH ÜCRETİ (₺)</label>
                <input type="number" name="bh_ucret" class="frm-in" value="0" min="0" step="0.01">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">MDK ÜCRETİ (₺)</label>
                <input type="number" name="mdk_ucret" class="frm-in" value="0" min="0" step="0.01">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">DİĞER ÜCRETİ (₺)</label>
                <input type="number" name="diger_ucret" class="frm-in" value="0" min="0" step="0.01">
            </div>
            <div class="frm-grp" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn-suc"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </div>
    </form>
</div>

<!-- LİSTE -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-list"></i> YÖNLENDİREN LİSTESİ (<?= count($yonlendirenler) ?>)</div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>AD SOYAD / FİRMA</th>
                    <th>TELEFON</th>
                    <th>ÜCRETLER</th>
                    <th>DOSYA</th>
                    <th>BAKİYE (BORÇ)</th>
                    <th>DURUM</th>
                    <th>İŞLEM</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yonlendirenler as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td style="font-weight:700">
                        <i class="fas fa-user" style="color:var(--blue);margin-right:10px"></i><?= e($row['ad_soyad']) ?>
                        <?php if ($row['email']): ?>
                        <div style="font-size:11px;color:#64748b;margin-top:3px"><?= e($row['email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($row['telefon'] ?: '-') ?></td>
                    <td style="font-size:11px">
                        <div><span style="color:#3b82f6">ADK:</span> ₺<?= number_format($row['adk_ucret'] ?? 0, 0) ?></div>
                        <div><span style="color:#22c55e">BH:</span> ₺<?= number_format($row['bh_ucret'] ?? 0, 0) ?></div>
                        <div><span style="color:#f97316">MDK:</span> ₺<?= number_format($row['mdk_ucret'] ?? 0, 0) ?></div>
                        <div><span style="color:#8b5cf6">DİĞER:</span> ₺<?= number_format($row['diger_ucret'] ?? 0, 0) ?></div>
                    </td>
                    <td><span class="badge aktif"><?= $row['dosya_sayisi'] ?? 0 ?></span></td>
                    <td>
                        <?php if (($row['bakiye'] ?? 0) > 0): ?>
                        <span style="color:var(--red);font-weight:700">₺<?= number_format($row['bakiye'], 2, ',', '.') ?></span>
                        <?php else: ?>
                        <span style="color:var(--green)">₺0,00</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['durum']): ?>
                            <span class="badge aktif">AKTİF</span>
                        <?php else: ?>
                            <span class="badge kapali">PASİF</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="icons">
                            <!-- DÜZENLE -->
                            <button type="button" class="ic edit" title="DÜZENLE" onclick='openDuzenleModal(<?= json_encode($row) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <!-- ÖDEME YAP -->
                            <?php if (($row['bakiye'] ?? 0) > 0): ?>
                            <button type="button" class="ic doc" title="ÖDEME YAP" onclick="openOdemeModal(<?= $row['id'] ?>, '<?= e($row['ad_soyad']) ?>', <?= $row['bakiye'] ?>)">
                                <i class="fas fa-lira-sign"></i>
                            </button>
                            <?php endif; ?>
                            <!-- DETAY -->
                            <a href="yonlendiren_detay.php?id=<?= $row['id'] ?>" class="ic view" title="DETAY">
                                <i class="fas fa-eye"></i>
                            </a>
                            <!-- SİL -->
                            <?php if (hasRole(['ADMIN'])): ?>
                            <a href="?sil=<?= $row['id'] ?>" class="ic del" title="SİL" onclick="return confirm('Silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($yonlendirenler)): ?>
                <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text2)">
                    <i class="fas fa-directions" style="font-size:30px;margin-bottom:10px;opacity:0.3;display:block"></i>
                    YÖNLENDİREN BULUNAMADI
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DÜZENLE MODAL -->
<div id="duzenleModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center">
    <div style="background:#1e293b;border-radius:12px;padding:30px;width:600px;max-width:90%;border:2px solid #3b82f6;max-height:90vh;overflow-y:auto">
        <h3 style="margin-bottom:20px;color:#3b82f6;display:flex;align-items:center;gap:10px">
            <i class="fas fa-edit"></i> YÖNLENDİREN DÜZENLE
        </h3>
        <form method="POST">
            <input type="hidden" name="action" value="duzenle">
            <input type="hidden" name="id" id="duz_id">
            <div class="form-grid">
                <div class="frm-grp">
                    <label class="frm-lbl">AD SOYAD / FİRMA <span class="req">*</span></label>
                    <input type="text" name="ad_soyad" id="duz_ad_soyad" class="frm-in" required>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">TELEFON</label>
                    <input type="text" name="telefon" id="duz_telefon" class="frm-in">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">E-POSTA</label>
                    <input type="email" name="email" id="duz_email" class="frm-in">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">DURUM</label>
                    <select name="durum" id="duz_durum" class="frm-sel">
                        <option value="1">AKTİF</option>
                        <option value="0">PASİF</option>
                    </select>
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">ADK ÜCRETİ (₺)</label>
                    <input type="number" name="adk_ucret" id="duz_adk_ucret" class="frm-in" min="0" step="0.01">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">BH ÜCRETİ (₺)</label>
                    <input type="number" name="bh_ucret" id="duz_bh_ucret" class="frm-in" min="0" step="0.01">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">MDK ÜCRETİ (₺)</label>
                    <input type="number" name="mdk_ucret" id="duz_mdk_ucret" class="frm-in" min="0" step="0.01">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">DİĞER ÜCRETİ (₺)</label>
                    <input type="number" name="diger_ucret" id="duz_diger_ucret" class="frm-in" min="0" step="0.01">
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-sec" onclick="closeModal('duzenleModal')">İPTAL</button>
                <button type="submit" class="btn btn-pri"><i class="fas fa-save"></i> KAYDET</button>
            </div>
        </form>
    </div>
</div>

<!-- ÖDEME MODAL -->
<div id="odemeModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center">
    <div style="background:#1e293b;border-radius:12px;padding:30px;width:450px;max-width:90%;border:2px solid #22c55e">
        <h3 style="margin-bottom:20px;color:#22c55e;display:flex;align-items:center;gap:10px">
            <i class="fas fa-lira-sign"></i> ÖDEME YAP
        </h3>
        <p style="margin-bottom:15px;color:#94a3b8">
            <strong id="odeme_yonlendiren_adi" style="color:#f1f5f9"></strong> için ödeme yapılacak.
            <br>Mevcut Borç: <strong id="odeme_borc" style="color:#ef4444"></strong>
        </p>
        <form method="POST">
            <input type="hidden" name="action" value="odeme">
            <input type="hidden" name="yonlendiren_id" id="odeme_yonlendiren_id">
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">ÖDEME TUTARI (₺) <span class="req">*</span></label>
                <input type="number" name="tutar" id="odeme_tutar" class="frm-in" required min="0.01" step="0.01" placeholder="0.00">
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">ÇIKIŞ KASASI <span class="req">*</span></label>
                <select name="cashbox_id" class="frm-sel" required>
                    <option value="">-- KASA SEÇİNİZ --</option>
                    <?php foreach ($kasalar as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= e($k['name']) ?> (₺<?= number_format($k['balance'], 2, ',', '.') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="frm-grp" style="margin-bottom:15px">
                <label class="frm-lbl">AÇIKLAMA</label>
                <input type="text" name="aciklama" class="frm-in" placeholder="Ödeme açıklaması...">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-sec" onclick="closeModal('odemeModal')">İPTAL</button>
                <button type="submit" class="btn btn-suc"><i class="fas fa-check"></i> ÖDEME YAP</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDuzenleModal(data) {
    document.getElementById('duz_id').value = data.id;
    document.getElementById('duz_ad_soyad').value = data.ad_soyad || '';
    document.getElementById('duz_telefon').value = data.telefon || '';
    document.getElementById('duz_email').value = data.email || '';
    document.getElementById('duz_durum').value = data.durum || '1';
    document.getElementById('duz_adk_ucret').value = data.adk_ucret || 0;
    document.getElementById('duz_bh_ucret').value = data.bh_ucret || 0;
    document.getElementById('duz_mdk_ucret').value = data.mdk_ucret || 0;
    document.getElementById('duz_diger_ucret').value = data.diger_ucret || 0;
    document.getElementById('duzenleModal').style.display = 'flex';
}

function openOdemeModal(id, ad, borc) {
    document.getElementById('odeme_yonlendiren_id').value = id;
    document.getElementById('odeme_yonlendiren_adi').textContent = ad;
    document.getElementById('odeme_borc').textContent = '₺' + borc.toFixed(2).replace('.', ',');
    document.getElementById('odeme_tutar').value = borc;
    document.getElementById('odeme_tutar').max = borc;
    document.getElementById('odemeModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('duzenleModal');
        closeModal('odemeModal');
    }
});

document.querySelectorAll('#duzenleModal, #odemeModal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>