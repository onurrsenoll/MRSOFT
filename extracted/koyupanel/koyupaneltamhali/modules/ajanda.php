<?php
/**
 * MR HASAR DANIŞMANLIK - AJANDA
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';
startSecureSession();

$error = '';
$success = '';

try {
    $db = getDB();
    $current_user_id = $_SESSION['user_id'] ?? 1;

    // NOT EKLE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] == 'ekle') {
            $baslik = trim($_POST['baslik'] ?? '');
            $aciklama = trim($_POST['aciklama'] ?? '');
            $tarih = $_POST['tarih'] ?? date('Y-m-d');
            $saat = $_POST['saat'] ?? null;
            $oncelik = $_POST['oncelik'] ?? 'NORMAL';
            $tur = $_POST['tur'] ?? 'NOT';

            if ($baslik) {
                $stmt = $db->prepare("INSERT INTO ajanda_notlari (user_id, baslik, aciklama, tarih, saat, oncelik, tur) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$current_user_id, $baslik, $aciklama ?: null, $tarih, $saat ?: null, $oncelik, $tur]);
                header("Location: ajanda.php?success=eklendi");
                exit;
            } else {
                $error = 'BAŞLIK ZORUNLUDUR';
            }
        }

        // NOT TAMAMLA
        if ($_POST['action'] == 'tamamla') {
            $not_id = intval($_POST['not_id'] ?? 0);
            if ($not_id) {
                $stmt = $db->prepare("UPDATE ajanda_notlari SET durum = 'TAMAMLANDI', tamamlanma_tarihi = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$not_id, $current_user_id]);
                header("Location: ajanda.php?success=tamamlandi");
                exit;
            }
        }

        // NOT SİL
        if ($_POST['action'] == 'sil') {
            $not_id = intval($_POST['not_id'] ?? 0);
            if ($not_id) {
                $stmt = $db->prepare("DELETE FROM ajanda_notlari WHERE id = ? AND user_id = ?");
                $stmt->execute([$not_id, $current_user_id]);
                header("Location: ajanda.php?success=silindi");
                exit;
            }
        }
    }

    // Filtreleme
    $filtre = $_GET['filtre'] ?? 'aktif';

    // Bugünkü notlar
    $stmt = $db->prepare("SELECT * FROM ajanda_notlari WHERE user_id = ? AND tarih = CURDATE() AND durum = 'BEKLIYOR' ORDER BY saat ASC, oncelik DESC");
    $stmt->execute([$current_user_id]);
    $bugunNotlar = $stmt->fetchAll();

    // Yaklaşan notlar (önümüzdeki 7 gün)
    $stmt = $db->prepare("SELECT * FROM ajanda_notlari WHERE user_id = ? AND tarih > CURDATE() AND tarih <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND durum = 'BEKLIYOR' ORDER BY tarih ASC, saat ASC");
    $stmt->execute([$current_user_id]);
    $yaklasanNotlar = $stmt->fetchAll();

    // Gecikmiş notlar
    $stmt = $db->prepare("SELECT * FROM ajanda_notlari WHERE user_id = ? AND tarih < CURDATE() AND durum = 'BEKLIYOR' ORDER BY tarih DESC");
    $stmt->execute([$current_user_id]);
    $gecikmisNotlar = $stmt->fetchAll();

    // Tamamlanan notlar (son 30 gün)
    $stmt = $db->prepare("SELECT * FROM ajanda_notlari WHERE user_id = ? AND durum = 'TAMAMLANDI' AND tamamlanma_tarihi >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY tamamlanma_tarihi DESC LIMIT 20");
    $stmt->execute([$current_user_id]);
    $tamamlananNotlar = $stmt->fetchAll();

    // Tüm notlar (aktif)
    if ($filtre == 'tum') {
        $stmt = $db->prepare("SELECT * FROM ajanda_notlari WHERE user_id = ? ORDER BY tarih DESC, created_at DESC");
    } else {
        $stmt = $db->prepare("SELECT * FROM ajanda_notlari WHERE user_id = ? AND durum = 'BEKLIYOR' ORDER BY tarih ASC, saat ASC");
    }
    $stmt->execute([$current_user_id]);
    $tumNotlar = $stmt->fetchAll();

    // Yaklaşan CRM takipleri
    $stmt = $db->prepare("
        SELECT c.*, u.full_name as sorumlu_adi
        FROM crm_records c
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        WHERE c.durum = 'TAKIPTE' AND c.sonraki_tarih IS NOT NULL
        ORDER BY c.sonraki_tarih ASC
        LIMIT 10
    ");
    $stmt->execute();
    $crmTakip = $stmt->fetchAll();

    // Son eklenen dosyalar
    $sonDosyalar = $db->query("
        SELECT c.*, u.full_name as sorumlu_adi
        FROM cases c
        LEFT JOIN users u ON c.sorumlu_user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // İstatistikler
    $bugunDosya = $db->query("SELECT COUNT(*) FROM cases WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $bugunCRM = $db->query("SELECT COUNT(*) FROM crm_records WHERE DATE(sonraki_tarih) = CURDATE() AND durum = 'TAKIPTE'")->fetchColumn();
    $gecikmisCRM = $db->query("SELECT COUNT(*) FROM crm_records WHERE sonraki_tarih < NOW() AND durum = 'TAKIPTE'")->fetchColumn();
    $bugunNotSayisi = count($bugunNotlar);
    $gecikmisNotSayisi = count($gecikmisNotlar);

} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.ajanda-grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
@media (max-width: 1200px) { .ajanda-grid { grid-template-columns: 1fr; } }

.not-form { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; }
.not-form-title { font-size: 14px; font-weight: 700; color: #10b981; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }

.not-card { background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 15px; margin-bottom: 10px; transition: all 0.2s; }
.not-card:hover { border-color: #3b82f6; transform: translateX(5px); }
.not-card.gecikti { border-left: 4px solid #ef4444; }
.not-card.bugun { border-left: 4px solid #f59e0b; }
.not-card.yaklasan { border-left: 4px solid #3b82f6; }
.not-card.tamamlandi { opacity: 0.6; border-left: 4px solid #22c55e; }

.not-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
.not-baslik { font-weight: 700; color: #f1f5f9; font-size: 14px; }
.not-tarih { font-size: 11px; color: #64748b; }
.not-aciklama { font-size: 12px; color: #94a3b8; margin-bottom: 10px; line-height: 1.5; }
.not-footer { display: flex; justify-content: space-between; align-items: center; }
.not-meta { display: flex; gap: 10px; }

.badge-oncelik { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
.badge-oncelik.ACIL { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.badge-oncelik.YUKSEK { background: rgba(249, 115, 22, 0.2); color: #f97316; }
.badge-oncelik.NORMAL { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
.badge-oncelik.DUSUK { background: rgba(100, 116, 139, 0.2); color: #64748b; }

.badge-tur { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
.badge-tur.NOT { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
.badge-tur.HATIRLATMA { background: rgba(236, 72, 153, 0.2); color: #ec4899; }
.badge-tur.GOREV { background: rgba(20, 184, 166, 0.2); color: #14b8a6; }
.badge-tur.TOPLANTI { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }

.not-actions { display: flex; gap: 5px; }
.not-btn { padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
.not-btn-ok { background: #22c55e; color: #fff; }
.not-btn-del { background: #ef4444; color: #fff; }
.not-btn:hover { opacity: 0.8; }

.section-title { font-size: 13px; font-weight: 700; color: #94a3b8; margin: 20px 0 10px; display: flex; align-items: center; gap: 8px; }
.section-title i { color: #3b82f6; }
.section-title .count { background: #3b82f6; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 11px; }

.tab-buttons { display: flex; gap: 10px; margin-bottom: 20px; }
.tab-btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; }
.tab-btn.active { background: #3b82f6; color: #fff; }
.tab-btn:not(.active) { background: #334155; color: #94a3b8; }
</style>

<div class="page-title"><i class="fas fa-calendar-alt"></i> AJANDA</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i>
    <?php
    $msgs = ['eklendi' => 'NOT EKLENDİ!', 'tamamlandi' => 'NOT TAMAMLANDI!', 'silindi' => 'NOT SİLİNDİ!'];
    echo $msgs[$_GET['success']] ?? 'İŞLEM BAŞARILI';
    ?>
</div>
<?php endif; ?>

<!-- ÖZET KARTLAR -->
<div class="cards">
    <div class="card">
        <div class="card-head">
            <div class="card-icon blue"><i class="fas fa-folder-plus"></i></div>
            <div>
                <div class="card-title">BUGÜN AÇILAN DOSYA</div>
                <div class="card-val"><?= $bugunDosya ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon orange"><i class="fas fa-sticky-note"></i></div>
            <div>
                <div class="card-title">BUGÜNKÜ NOTLAR</div>
                <div class="card-val"><?= $bugunNotSayisi ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon <?= $gecikmisNotSayisi > 0 ? 'red' : 'green' ?>"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="card-title">GECİKMİŞ NOT</div>
                <div class="card-val"><?= $gecikmisNotSayisi ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-icon purple"><i class="fas fa-phone"></i></div>
            <div>
                <div class="card-title">BUGÜN ARANACAK</div>
                <div class="card-val"><?= $bugunCRM ?? 0 ?></div>
            </div>
        </div>
    </div>
</div>

<div class="ajanda-grid">
    <!-- SOL PANEL - YENİ NOT EKLE -->
    <div>
        <div class="not-form">
            <div class="not-form-title"><i class="fas fa-plus-circle"></i> YENİ NOT / HATIRLATMA EKLE</div>
            <form method="POST">
                <input type="hidden" name="action" value="ekle">

                <div class="frm-grp" style="margin-bottom:15px">
                    <label class="frm-lbl">BAŞLIK <span class="req">*</span></label>
                    <input type="text" name="baslik" class="frm-in" required placeholder="Not başlığı...">
                </div>

                <div class="frm-grp" style="margin-bottom:15px">
                    <label class="frm-lbl">AÇIKLAMA</label>
                    <textarea name="aciklama" class="frm-ta" rows="3" placeholder="Detaylı açıklama (opsiyonel)..."></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:15px">
                    <div class="frm-grp">
                        <label class="frm-lbl">TARİH</label>
                        <input type="date" name="tarih" class="frm-in" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">SAAT</label>
                        <input type="time" name="saat" class="frm-in">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:15px">
                    <div class="frm-grp">
                        <label class="frm-lbl">TÜR</label>
                        <select name="tur" class="frm-sel">
                            <option value="NOT">NOT</option>
                            <option value="HATIRLATMA">HATIRLATMA</option>
                            <option value="GOREV">GÖREV</option>
                            <option value="TOPLANTI">TOPLANTI</option>
                        </select>
                    </div>
                    <div class="frm-grp">
                        <label class="frm-lbl">ÖNCELİK</label>
                        <select name="oncelik" class="frm-sel">
                            <option value="NORMAL">NORMAL</option>
                            <option value="DUSUK">DÜŞÜK</option>
                            <option value="YUKSEK">YÜKSEK</option>
                            <option value="ACIL">ACİL</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-suc" style="width:100%">
                    <i class="fas fa-plus"></i> NOT EKLE
                </button>
            </form>
        </div>

        <!-- YAKLAŞAN CRM TAKİPLERİ -->
        <div class="panel" style="margin-top:20px">
            <div class="panel-head">
                <div class="panel-title"><i class="fas fa-phone-alt"></i> YAKLAŞAN ARAMALAR</div>
                <a href="crm.php" class="btn btn-sec" style="padding:5px 10px;font-size:11px"><i class="fas fa-list"></i></a>
            </div>
            <div style="max-height:300px;overflow-y:auto">
                <?php if (empty($crmTakip)): ?>
                <div style="padding:20px;text-align:center;color:#64748b;font-size:12px">ARAMA YOK</div>
                <?php else: foreach ($crmTakip as $c): ?>
                <div style="padding:10px 15px;border-bottom:1px solid #334155;<?= strtotime($c['sonraki_tarih']) < time() ? 'background:rgba(239,68,68,0.1)' : '' ?>">
                    <div style="font-weight:600;color:#f1f5f9;font-size:13px"><?= e($c['ad_soyad']) ?></div>
                    <div style="font-size:11px;color:#64748b;margin-top:3px">
                        <i class="fas fa-phone" style="color:#3b82f6"></i> <?= e($c['telefon']) ?>
                        <span style="margin-left:10px"><i class="fas fa-clock"></i> <?= date('d.m H:i', strtotime($c['sonraki_tarih'])) ?></span>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- SAĞ PANEL - NOTLAR LİSTESİ -->
    <div>
        <!-- GECİKMİŞ NOTLAR -->
        <?php if (!empty($gecikmisNotlar)): ?>
        <div class="section-title" style="color:#ef4444;margin-top:0">
            <i class="fas fa-exclamation-circle" style="color:#ef4444"></i> GECİKMİŞ NOTLAR
            <span class="count" style="background:#ef4444"><?= count($gecikmisNotlar) ?></span>
        </div>
        <?php foreach ($gecikmisNotlar as $not): ?>
        <div class="not-card gecikti">
            <div class="not-header">
                <div class="not-baslik"><?= e($not['baslik']) ?></div>
                <div class="not-tarih" style="color:#ef4444"><?= date('d.m.Y', strtotime($not['tarih'])) ?><?= $not['saat'] ? ' ' . substr($not['saat'], 0, 5) : '' ?></div>
            </div>
            <?php if ($not['aciklama']): ?>
            <div class="not-aciklama"><?= e($not['aciklama']) ?></div>
            <?php endif; ?>
            <div class="not-footer">
                <div class="not-meta">
                    <span class="badge-tur <?= $not['tur'] ?>"><?= $not['tur'] ?></span>
                    <span class="badge-oncelik <?= $not['oncelik'] ?>"><?= $not['oncelik'] ?></span>
                </div>
                <div class="not-actions">
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="tamamla">
                        <input type="hidden" name="not_id" value="<?= $not['id'] ?>">
                        <button type="submit" class="not-btn not-btn-ok" title="Tamamla"><i class="fas fa-check"></i></button>
                    </form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Notu silmek istediğinize emin misiniz?')">
                        <input type="hidden" name="action" value="sil">
                        <input type="hidden" name="not_id" value="<?= $not['id'] ?>">
                        <button type="submit" class="not-btn not-btn-del" title="Sil"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- BUGÜNKÜ NOTLAR -->
        <?php if (!empty($bugunNotlar)): ?>
        <div class="section-title" style="color:#f59e0b">
            <i class="fas fa-calendar-day" style="color:#f59e0b"></i> BUGÜN
            <span class="count" style="background:#f59e0b"><?= count($bugunNotlar) ?></span>
        </div>
        <?php foreach ($bugunNotlar as $not): ?>
        <div class="not-card bugun">
            <div class="not-header">
                <div class="not-baslik"><?= e($not['baslik']) ?></div>
                <div class="not-tarih"><?= $not['saat'] ? substr($not['saat'], 0, 5) : 'Tüm gün' ?></div>
            </div>
            <?php if ($not['aciklama']): ?>
            <div class="not-aciklama"><?= e($not['aciklama']) ?></div>
            <?php endif; ?>
            <div class="not-footer">
                <div class="not-meta">
                    <span class="badge-tur <?= $not['tur'] ?>"><?= $not['tur'] ?></span>
                    <span class="badge-oncelik <?= $not['oncelik'] ?>"><?= $not['oncelik'] ?></span>
                </div>
                <div class="not-actions">
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="tamamla">
                        <input type="hidden" name="not_id" value="<?= $not['id'] ?>">
                        <button type="submit" class="not-btn not-btn-ok" title="Tamamla"><i class="fas fa-check"></i></button>
                    </form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Notu silmek istediğinize emin misiniz?')">
                        <input type="hidden" name="action" value="sil">
                        <input type="hidden" name="not_id" value="<?= $not['id'] ?>">
                        <button type="submit" class="not-btn not-btn-del" title="Sil"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- YAKLAŞAN NOTLAR -->
        <?php if (!empty($yaklasanNotlar)): ?>
        <div class="section-title">
            <i class="fas fa-clock"></i> YAKLAŞAN (7 GÜN)
            <span class="count"><?= count($yaklasanNotlar) ?></span>
        </div>
        <?php foreach ($yaklasanNotlar as $not): ?>
        <div class="not-card yaklasan">
            <div class="not-header">
                <div class="not-baslik"><?= e($not['baslik']) ?></div>
                <div class="not-tarih"><?= date('d.m.Y', strtotime($not['tarih'])) ?><?= $not['saat'] ? ' ' . substr($not['saat'], 0, 5) : '' ?></div>
            </div>
            <?php if ($not['aciklama']): ?>
            <div class="not-aciklama"><?= e($not['aciklama']) ?></div>
            <?php endif; ?>
            <div class="not-footer">
                <div class="not-meta">
                    <span class="badge-tur <?= $not['tur'] ?>"><?= $not['tur'] ?></span>
                    <span class="badge-oncelik <?= $not['oncelik'] ?>"><?= $not['oncelik'] ?></span>
                </div>
                <div class="not-actions">
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="tamamla">
                        <input type="hidden" name="not_id" value="<?= $not['id'] ?>">
                        <button type="submit" class="not-btn not-btn-ok" title="Tamamla"><i class="fas fa-check"></i></button>
                    </form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Notu silmek istediğinize emin misiniz?')">
                        <input type="hidden" name="action" value="sil">
                        <input type="hidden" name="not_id" value="<?= $not['id'] ?>">
                        <button type="submit" class="not-btn not-btn-del" title="Sil"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- TAMAMLANAN NOTLAR -->
        <?php if (!empty($tamamlananNotlar)): ?>
        <div class="section-title" style="color:#22c55e">
            <i class="fas fa-check-circle" style="color:#22c55e"></i> TAMAMLANAN (SON 30 GÜN)
            <span class="count" style="background:#22c55e"><?= count($tamamlananNotlar) ?></span>
        </div>
        <?php foreach ($tamamlananNotlar as $not): ?>
        <div class="not-card tamamlandi">
            <div class="not-header">
                <div class="not-baslik" style="text-decoration:line-through"><?= e($not['baslik']) ?></div>
                <div class="not-tarih" style="color:#22c55e"><i class="fas fa-check"></i> <?= date('d.m.Y', strtotime($not['tamamlanma_tarihi'])) ?></div>
            </div>
            <div class="not-footer">
                <div class="not-meta">
                    <span class="badge-tur <?= $not['tur'] ?>"><?= $not['tur'] ?></span>
                </div>
                <form method="POST" style="display:inline" onsubmit="return confirm('Notu silmek istediğinize emin misiniz?')">
                    <input type="hidden" name="action" value="sil">
                    <input type="hidden" name="not_id" value="<?= $not['id'] ?>">
                    <button type="submit" class="not-btn not-btn-del" title="Sil"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- BOŞ DURUM -->
        <?php if (empty($gecikmisNotlar) && empty($bugunNotlar) && empty($yaklasanNotlar) && empty($tamamlananNotlar)): ?>
        <div style="text-align:center;padding:60px 20px;color:#64748b">
            <i class="fas fa-calendar-check" style="font-size:60px;margin-bottom:20px;opacity:0.3;display:block"></i>
            <div style="font-size:16px;font-weight:600;margin-bottom:10px">AJANDANIZ BOŞ</div>
            <div style="font-size:13px">Soldaki formdan yeni not veya hatırlatma ekleyebilirsiniz.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
