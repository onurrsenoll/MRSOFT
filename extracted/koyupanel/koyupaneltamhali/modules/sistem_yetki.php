<?php
/**
 * SAY HASAR DANIŞMANLIK - YETKİ YÖNETİMİ
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 */

require_once __DIR__ . '/../config.php';

if (!hasRole(['ADMIN'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$kullanicilar = [];
$seciliYetkiler = [];
$seciliKullanici = null;

// Tüm menüler
$menuler = [
    'ANASAYFA' => [
        'dashboard' => 'ANASAYFA'
    ],
    'DOSYA İŞLEMLERİ' => [
        'dosyalar' => 'DOSYALARI LİSTELE',
        'dosya_ekle' => 'YENİ DOSYA EKLE',
        'crm' => 'CRM TAKİP'
    ],
    'HESAPLAMA' => [
        'hesap_adk' => 'ARAÇ DEĞER KAYBI',
        'hesap_maluliyet' => 'MALULİYET HESAPLAMA'
    ],
    'SERVİSLER' => [
        'ihbar_foyu' => 'İHBAR FÖYÜ',
        'yonlendiren' => 'YÖNLENDİREN'
    ],
    'ORTAKLAR' => [
        'ortaklar' => 'LİSTELE / EKLE / GÜNCELLE',
        'ortak_hesaplari' => 'ORTAK HESAPLARI'
    ],
    'RAPORLAR' => [
        'rapor_dosya' => 'DOSYA DETAY RAPORU',
        'rapor_finansal' => 'FİNANSAL ÖZET',
        'rapor_ortak' => 'İŞ ORTAKLARI RAPORU'
    ],
    'TANIMLAMALAR' => [
        'tanim_personel' => 'PERSONEL',
        'tanim_sigorta' => 'SİGORTA ŞİRKETLERİ',
        'tanim_masraf' => 'MASRAF KALEMLERİ',
        'tanim_evrak' => 'EVRAK TÜRLERİ',
        'tanim_asama' => 'AŞAMA DURUMLARI'
    ],
    'MUHASEBE' => [
        'kasa' => 'KASALAR',
        'cari' => 'CARİLER',
        'maas_prim' => 'MAAŞ / PRİM',
        'gelir_gider' => 'GELİR / GİDER'
    ],
    'SİSTEM' => [
        'sistem_genel' => 'GENEL AYARLAR',
        'sistem_yetki' => 'YETKİ YÖNETİMİ',
        'sistem_kullanici' => 'KULLANICI YÖNETİMİ',
        'sistem_api' => 'API AYARLARI',
        'sistem_firma' => 'FİRMA BİLGİSİ'
    ],
    'DİĞER' => [
        'mesajlar' => 'MESAJLAR',
        'ajanda' => 'AJANDA'
    ]
];

try {
    $db = getDB();
    
    // Kullanıcıları getir
    $kullanicilar = $db->query("SELECT id, username, full_name, role FROM users WHERE is_active = 1 AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY full_name")->fetchAll();
    
    // Seçili kullanıcı varsa yetkilerini getir
    if (!empty($_GET['user_id'])) {
        $seciliKullanici = intval($_GET['user_id']);
        $stmt = $db->prepare("SELECT menu_key FROM user_permissions WHERE user_id = ?");
        $stmt->execute([$seciliKullanici]);
        $seciliYetkiler = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// YETKİ KAYDETME
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'kaydet') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $yetkiler = $_POST['yetkiler'] ?? [];
    
    if ($user_id) {
        try {
            $db->beginTransaction();
            
            // Önce tüm yetkileri sil
            $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Seçilen yetkileri ekle
            if (!empty($yetkiler)) {
                $stmt = $db->prepare("INSERT INTO user_permissions (user_id, menu_key) VALUES (?, ?)");
                foreach ($yetkiler as $yetki) {
                    $stmt->execute([$user_id, $yetki]);
                }
            }
            
            $db->commit();
            header("Location: sistem_yetki.php?user_id=$user_id&success=1");
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

// TÜMÜNÜ SEÇ
if (isset($_GET['tumunu_sec']) && $seciliKullanici) {
    try {
        $db->beginTransaction();
        
        // Önce tüm yetkileri sil
        $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
        $stmt->execute([$seciliKullanici]);
        
        // Tüm yetkileri ekle
        $stmt = $db->prepare("INSERT INTO user_permissions (user_id, menu_key) VALUES (?, ?)");
        foreach ($menuler as $grup => $items) {
            foreach ($items as $key => $label) {
                $stmt->execute([$seciliKullanici, $key]);
            }
        }
        
        $db->commit();
        header("Location: sistem_yetki.php?user_id=$seciliKullanici&success=1");
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// TÜMÜNÜ KALDIR
if (isset($_GET['tumunu_kaldir']) && $seciliKullanici) {
    try {
        $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
        $stmt->execute([$seciliKullanici]);
        header("Location: sistem_yetki.php?user_id=$seciliKullanici&success=1");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-title"><i class="fas fa-user-shield"></i> YETKİ YÖNETİMİ</div>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> YETKİLER BAŞARIYLA KAYDEDİLDİ!</div>
<?php endif; ?>

<!-- KULLANICI SEÇİMİ -->
<div class="panel" style="margin-bottom:20px">
    <div class="panel-head">
        <div class="panel-title"><i class="fas fa-user"></i> KULLANICI SEÇ</div>
    </div>
    <div style="padding:20px">
        <form method="GET" style="display:flex;gap:15px;align-items:flex-end;flex-wrap:wrap">
            <div class="frm-grp" style="flex:1;min-width:250px">
                <label class="frm-lbl">KULLANICI</label>
                <select name="user_id" class="frm-sel" onchange="this.form.submit()">
                    <option value="">-- KULLANICI SEÇİN --</option>
                    <?php foreach ($kullanicilar as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $seciliKullanici == $k['id'] ? 'selected' : '' ?>>
                        <?= e($k['full_name']) ?> (<?= e($k['username']) ?>) - <?= e($k['role']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($seciliKullanici): ?>
            <a href="sistem_yetki.php?user_id=<?= $seciliKullanici ?>&tumunu_sec=1" class="btn btn-suc">
                <i class="fas fa-check-double"></i> TÜMÜNÜ SEÇ
            </a>
            <a href="sistem_yetki.php?user_id=<?= $seciliKullanici ?>&tumunu_kaldir=1" class="btn btn-dan" onclick="return confirm('Tüm yetkileri kaldırmak istediğinize emin misiniz?')">
                <i class="fas fa-times"></i> TÜMÜNÜ KALDIR
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($seciliKullanici): ?>
<!-- YETKİ TABLOSU -->
<form method="POST">
    <input type="hidden" name="action" value="kaydet">
    <input type="hidden" name="user_id" value="<?= $seciliKullanici ?>">
    
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">
                <i class="fas fa-list-check"></i> MENÜ YETKİLERİ
                <span style="color:#3b82f6;margin-left:10px">(<?= count($seciliYetkiler) ?> YETKİ SEÇİLİ)</span>
            </div>
            <button type="submit" class="btn btn-suc">
                <i class="fas fa-save"></i> YETKİLERİ KAYDET
            </button>
        </div>
        
        <div style="padding:20px;display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:20px">
            <?php foreach ($menuler as $grupAdi => $items): ?>
            <div style="background:#0f172a;border-radius:10px;padding:15px;border:1px solid #334155">
                <div style="font-weight:700;color:#3b82f6;margin-bottom:15px;padding-bottom:10px;border-bottom:1px solid #334155;display:flex;align-items:center;gap:10px">
                    <i class="fas fa-folder"></i> <?= $grupAdi ?>
                </div>
                <?php foreach ($items as $key => $label): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:8px 0;cursor:pointer;color:#f1f5f9">
                    <input type="checkbox" name="yetkiler[]" value="<?= $key ?>" 
                           <?= in_array($key, $seciliYetkiler) ? 'checked' : '' ?>
                           style="width:18px;height:18px;accent-color:#3b82f6">
                    <span><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="padding:20px;border-top:1px solid #334155;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-suc">
                <i class="fas fa-save"></i> YETKİLERİ KAYDET
            </button>
        </div>
    </div>
</form>
<?php else: ?>
<div class="panel">
    <div style="padding:60px;text-align:center;color:#64748b">
        <i class="fas fa-user-shield" style="font-size:60px;margin-bottom:20px;opacity:0.3"></i>
        <p style="font-size:16px">YETKİ DÜZENLEMEK İÇİN YUKARIDAN BİR KULLANICI SEÇİN</p>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>