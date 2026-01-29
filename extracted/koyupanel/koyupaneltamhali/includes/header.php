<?php
/**
 * MR HASAR DANIŞMANLIK VE FİLO YÖNETİMİ - HEADER
 * HERZAMAN FARKEDER
 * v4.0 - Neumorphism Theme Support (Light/Dark)
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config.php';
}

startSecureSession();

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Tema tercihini cookie'den al
$currentTheme = isset($_COOKIE['mr_theme']) ? $_COOKIE['mr_theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?= htmlspecialchars($currentTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <script>
        // Sayfa yüklenmeden önce tema ayarla (flash önleme)
        (function() {
            var theme = document.cookie.match(/mr_theme=([^;]+)/);
            theme = theme ? theme[1] : 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
<style>
/* ========== NAVIGATION - NEUMORPHISM ========== */
.header{display:none!important}

/* Navigation Bar */
.nav {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 9999 !important;
    padding: 8px 20px !important;
    display: flex;
    align-items: center;
    gap: 5px;
    overflow: visible !important;
    transition: all 0.3s ease;
}

/* Dark Theme Nav */
[data-theme="dark"] .nav {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    border-bottom: 1px solid rgba(59, 130, 246, 0.3) !important;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
}

/* Light Theme Nav - Neumorphism */
[data-theme="light"] .nav {
    background: linear-gradient(135deg, #e8eef5 0%, #dce4ed 100%) !important;
    border-bottom: none !important;
    box-shadow:
        0 4px 15px rgba(163, 177, 198, 0.4),
        0 -2px 10px rgba(255, 255, 255, 0.8);
}

/* Nav Items */
.nav-item {
    position: relative;
    cursor: pointer;
    display: inline-flex !important;
    align-items: center;
    padding: 10px 14px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    gap: 6px;
    border-radius: 12px;
    transition: all 0.3s ease;
}

/* Dark Theme Nav Items */
[data-theme="dark"] .nav-item {
    color: #cbd5e1;
}
[data-theme="dark"] .nav-item:hover {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

/* Light Theme Nav Items - Neumorphism */
[data-theme="light"] .nav-item {
    color: #1e3a5f;
}
[data-theme="light"] .nav-item:hover {
    color: #2563eb;
    background: #e4ebf5;
    box-shadow:
        4px 4px 8px rgba(163, 177, 198, 0.4),
        -4px -4px 8px rgba(255, 255, 255, 0.9);
}

/* Dropdown Menu */
.dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 260px;
    border-radius: 16px;
    z-index: 99999 !important;
    padding: 12px 0;
    margin-top: 8px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.25s ease;
}

/* Dark Theme Dropdown */
[data-theme="dark"] .dropdown {
    background: linear-gradient(145deg, #1e293b, #0f172a);
    border: 1px solid rgba(59, 130, 246, 0.3);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
}

/* Light Theme Dropdown - Neumorphism */
[data-theme="light"] .dropdown {
    background: #e4ebf5;
    border: none;
    box-shadow:
        10px 10px 30px rgba(163, 177, 198, 0.5),
        -10px -10px 30px rgba(255, 255, 255, 0.9);
}

.nav-item:hover > .dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Dropdown Items */
.drop-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
    margin: 4px 10px;
    border-radius: 10px;
}

/* Dark Theme Drop Items */
[data-theme="dark"] .drop-item {
    color: #cbd5e1;
}
[data-theme="dark"] .drop-item:hover {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
}

/* Light Theme Drop Items - Neumorphism */
[data-theme="light"] .drop-item {
    color: #1e3a5f;
}
[data-theme="light"] .drop-item:hover {
    background: #e4ebf5;
    color: #2563eb;
    box-shadow:
        inset 3px 3px 6px rgba(163, 177, 198, 0.4),
        inset -3px -3px 6px rgba(255, 255, 255, 0.9);
}

/* Dropdown Divider */
.drop-div {
    height: 1px;
    margin: 10px 20px;
}
[data-theme="dark"] .drop-div {
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
}
[data-theme="light"] .drop-div {
    background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.3), transparent);
}

/* Dropdown Label */
.drop-label {
    padding: 8px 20px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}
[data-theme="dark"] .drop-label {
    color: #64748b;
}
[data-theme="light"] .drop-label {
    color: #64748b;
}

/* ADK PRO Special Button */
.drop-item.adk-pro {
    background: linear-gradient(135deg, #1e3a5f, #2563eb) !important;
    color: #fff !important;
    font-weight: 600;
    margin: 8px 10px;
}
.drop-item.adk-pro:hover {
    background: linear-gradient(135deg, #2563eb, #0ea5e9) !important;
    transform: scale(1.02);
    box-shadow: 0 5px 20px rgba(37, 99, 235, 0.4) !important;
}

/* ========== THEME TOGGLE ========== */
.theme-toggle {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: auto;
    margin-right: 15px;
    padding: 4px;
    border-radius: 25px;
    transition: all 0.3s ease;
}

[data-theme="dark"] .theme-toggle {
    background: rgba(30, 41, 59, 0.8);
    box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.3);
}

[data-theme="light"] .theme-toggle {
    background: #dce4ed;
    box-shadow:
        inset 3px 3px 6px rgba(163, 177, 198, 0.5),
        inset -3px -3px 6px rgba(255, 255, 255, 0.9);
}

.theme-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
    background: transparent;
}

/* Dark Theme Buttons */
[data-theme="dark"] .theme-btn {
    color: #64748b;
}
[data-theme="dark"] .theme-btn:hover {
    color: #f1f5f9;
}
[data-theme="dark"] .theme-btn.active {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

/* Light Theme Buttons - Neumorphism */
[data-theme="light"] .theme-btn {
    color: #64748b;
}
[data-theme="light"] .theme-btn:hover {
    color: #1e3a5f;
}
[data-theme="light"] .theme-btn.active {
    background: linear-gradient(135deg, #2563eb, #0ea5e9);
    color: #fff;
    box-shadow:
        4px 4px 10px rgba(163, 177, 198, 0.5),
        -4px -4px 10px rgba(255, 255, 255, 0.8);
}

/* Logout Button */
.nav-item.logout-btn {
    margin-left: 0;
}
[data-theme="dark"] .nav-item.logout-btn {
    color: #ef4444;
}
[data-theme="dark"] .nav-item.logout-btn:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
}
[data-theme="light"] .nav-item.logout-btn {
    color: #dc2626;
}
[data-theme="light"] .nav-item.logout-btn:hover {
    color: #b91c1c;
    background: #e4ebf5;
    box-shadow:
        4px 4px 8px rgba(163, 177, 198, 0.4),
        -4px -4px 8px rgba(255, 255, 255, 0.9);
}

/* Body Padding */
body {
    padding-top: 60px !important;
}

/* Content Area */
.content {
    margin-top: 0 !important;
    padding-top: 25px !important;
    overflow: visible !important;
}
</style>
</head>
<body data-theme="<?= htmlspecialchars($currentTheme) ?>">
    <nav class="nav">
        <?php if (hasPermission('dashboard')): ?>
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-home"></i> ANASAYFA
        </a>
        <?php endif; ?>

        <?php if (hasPermission('dosyalar') || hasPermission('dosya_ekle') || hasPermission('crm')): ?>
        <div class="nav-item">
            <i class="fas fa-folder-open"></i> DOSYA İŞLEMLERİ
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:4px"></i>
            <div class="dropdown">
                <?php if (hasPermission('dosyalar')): ?>
                <a href="dosyalar.php" class="drop-item">
                    <i class="fas fa-list"></i> DOSYALARI LİSTELE
                </a>
                <?php endif; ?>
                <?php if (hasPermission('dosya_ekle')): ?>
                <a href="dosya_ekle.php" class="drop-item">
                    <i class="fas fa-plus-circle"></i> YENİ DOSYA EKLE
                </a>
                <?php endif; ?>
                <?php if (hasPermission('crm')): ?>
                <div class="drop-div"></div>
                <a href="crm.php" class="drop-item">
                    <i class="fas fa-headset"></i> CRM
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasPermission('hesap_adk') || hasPermission('hesap_maluliyet')): ?>
        <div class="nav-item">
            <i class="fas fa-calculator"></i> HESAPLAMA
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:4px"></i>
            <div class="dropdown">
                <a href="adk_hesaplama_pro.php" class="drop-item adk-pro">
                    <i class="fas fa-robot"></i> ADK HESAPLAMA PRO
                </a>
                <div class="drop-div"></div>
                <?php if (hasPermission('hesap_adk')): ?>
                <a href="hesap_adk.php" class="drop-item">
                    <i class="fas fa-car-crash"></i> ARAÇ DEĞER KAYBI
                </a>
                <?php endif; ?>
                <?php if (hasPermission('hesap_maluliyet')): ?>
                <a href="hesap_maluliyet.php" class="drop-item">
                    <i class="fas fa-wheelchair"></i> MALULİYET HESAPLAMA
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasPermission('ihbar_foyu') || hasPermission('yonlendiren')): ?>
        <div class="nav-item">
            <i class="fas fa-concierge-bell"></i> SERVİSLER
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:4px"></i>
            <div class="dropdown">
                <?php if (hasPermission('ihbar_foyu')): ?>
                <a href="ihbar_foyu.php" class="drop-item">
                    <i class="fas fa-file-medical"></i> İHBAR FÖYÜ
                </a>
                <?php endif; ?>
                <?php if (hasPermission('yonlendiren')): ?>
                <a href="yonlendiren.php" class="drop-item">
                    <i class="fas fa-directions"></i> YÖNLENDİREN
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasPermission('ortaklar') || hasPermission('ortak_hesaplari')): ?>
        <div class="nav-item">
            <i class="fas fa-users"></i> ORTAKLAR
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:4px"></i>
            <div class="dropdown">
                <?php if (hasPermission('ortaklar')): ?>
                <a href="ortaklar.php" class="drop-item">
                    <i class="fas fa-list-alt"></i> LİSTELE / EKLE / GÜNCELLE
                </a>
                <?php endif; ?>
                <?php if (hasPermission('ortak_hesaplari')): ?>
                <a href="ortak_hesaplari.php" class="drop-item">
                    <i class="fas fa-calculator"></i> ORTAK HESAPLARI
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasPermission('tanim_personel') || hasPermission('tanim_sigorta') || hasPermission('tanim_masraf') || hasPermission('tanim_evrak') || hasPermission('tanim_asama')): ?>
        <div class="nav-item">
            <i class="fas fa-cogs"></i> TANIMLAMALAR
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:4px"></i>
            <div class="dropdown">
                <?php if (hasPermission('tanim_personel')): ?>
                <a href="tanim_personel.php" class="drop-item">
                    <i class="fas fa-id-card"></i> PERSONEL
                </a>
                <?php endif; ?>
                <?php if (hasPermission('tanim_sigorta')): ?>
                <a href="tanim_sigorta.php" class="drop-item">
                    <i class="fas fa-building"></i> SİGORTA ŞİRKETLERİ
                </a>
                <?php endif; ?>
                <?php if (hasPermission('tanim_masraf')): ?>
                <a href="tanim_masraf.php" class="drop-item">
                    <i class="fas fa-receipt"></i> MASRAF KALEMLERİ
                </a>
                <?php endif; ?>
                <?php if (hasPermission('tanim_evrak')): ?>
                <a href="tanim_evrak.php" class="drop-item">
                    <i class="fas fa-file-invoice"></i> EVRAK TÜRLERİ
                </a>
                <?php endif; ?>
                <?php if (hasPermission('tanim_asama')): ?>
                <a href="tanim_asama.php" class="drop-item">
                    <i class="fas fa-tasks"></i> AŞAMA DURUMLARI
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasPermission('kasa') || hasPermission('cari') || hasPermission('maas_prim') || hasPermission('gelir_gider') || hasPermission('rapor_dosya') || hasPermission('rapor_finansal') || hasPermission('rapor_ortak')): ?>
        <div class="nav-item">
            <i class="fas fa-wallet"></i> MUHASEBE
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:4px"></i>
            <div class="dropdown">
                <?php if (hasPermission('kasa')): ?>
                <a href="kasa.php" class="drop-item">
                    <i class="fas fa-cash-register"></i> KASALAR
                </a>
                <?php endif; ?>
                <?php if (hasPermission('cari')): ?>
                <a href="cari.php" class="drop-item">
                    <i class="fas fa-address-book"></i> CARİLER
                </a>
                <?php endif; ?>
                <?php if (hasPermission('maas_prim')): ?>
                <a href="maas_prim.php" class="drop-item">
                    <i class="fas fa-money-check-alt"></i> MAAŞ / PRİM
                </a>
                <?php endif; ?>
                <?php if (hasPermission('gelir_gider')): ?>
                <a href="gelir_gider.php" class="drop-item">
                    <i class="fas fa-exchange-alt"></i> GELİR / GİDER
                </a>
                <?php endif; ?>
                <?php if (hasPermission('rapor_dosya') || hasPermission('rapor_finansal') || hasPermission('rapor_ortak')): ?>
                <div class="drop-div"></div>
                <div class="drop-label"><i class="fas fa-chart-bar"></i> RAPORLAR</div>
                <?php endif; ?>
                <?php if (hasPermission('rapor_dosya')): ?>
                <a href="rapor_dosya.php" class="drop-item">
                    <i class="fas fa-file-alt"></i> DOSYA DETAY RAPORU
                </a>
                <?php endif; ?>
                <?php if (hasPermission('rapor_finansal')): ?>
                <a href="rapor_finansal.php" class="drop-item">
                    <i class="fas fa-coins"></i> FİNANSAL ÖZET
                </a>
                <?php endif; ?>
                <?php if (hasPermission('rapor_ortak')): ?>
                <a href="rapor_ortak.php" class="drop-item">
                    <i class="fas fa-handshake"></i> İŞ ORTAKLARI RAPORU
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasRole(['ADMIN'])): ?>
        <div class="nav-item">
            <i class="fas fa-sliders-h"></i> SİSTEM
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:4px"></i>
            <div class="dropdown">
                <?php if (hasPermission('sistem_genel')): ?>
                <a href="sistem_genel.php" class="drop-item">
                    <i class="fas fa-cog"></i> GENEL AYARLAR
                </a>
                <?php endif; ?>
                <?php if (hasPermission('sistem_yetki')): ?>
                <a href="sistem_yetki.php" class="drop-item">
                    <i class="fas fa-user-shield"></i> YETKİ YÖNETİMİ
                </a>
                <?php endif; ?>
                <?php if (hasPermission('sistem_kullanici')): ?>
                <a href="sistem_kullanici.php" class="drop-item">
                    <i class="fas fa-users-cog"></i> KULLANICI YÖNETİMİ
                </a>
                <?php endif; ?>
                <div class="drop-div"></div>
                <?php if (hasPermission('sistem_api')): ?>
                <a href="sistem_api.php" class="drop-item">
                    <i class="fas fa-plug"></i> API AYARLARI
                </a>
                <?php endif; ?>
                <?php if (hasPermission('sistem_firma')): ?>
                <a href="sistem_firma.php" class="drop-item">
                    <i class="fas fa-building"></i> FİRMA BİLGİSİ
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasPermission('mesajlar')): ?>
        <a href="mesajlar.php" class="nav-item">
            <i class="fas fa-envelope"></i> MESAJLAR
        </a>
        <?php endif; ?>

        <?php if (hasPermission('ajanda')): ?>
        <a href="ajanda.php" class="nav-item">
            <i class="fas fa-calendar-alt"></i> AJANDA
        </a>
        <?php endif; ?>

        <!-- THEME TOGGLE -->
        <div class="theme-toggle">
            <button type="button" class="theme-btn" id="lightThemeBtn" onclick="setTheme('light')" title="Açık Tema">
                <i class="fas fa-sun"></i>
            </button>
            <button type="button" class="theme-btn" id="darkThemeBtn" onclick="setTheme('dark')" title="Koyu Tema">
                <i class="fas fa-moon"></i>
            </button>
        </div>

        <a href="../index.php?logout=1" class="nav-item logout-btn">
            <i class="fas fa-sign-out-alt"></i> ÇIKIŞ
        </a>
    </nav>

    <div class="content">

<script>
// Tema değiştirme fonksiyonu
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.body.setAttribute('data-theme', theme);
    document.cookie = 'mr_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
    updateThemeButtons(theme);
}

// Tema butonlarını güncelle
function updateThemeButtons(theme) {
    var lightBtn = document.getElementById('lightThemeBtn');
    var darkBtn = document.getElementById('darkThemeBtn');

    if (theme === 'light') {
        lightBtn.classList.add('active');
        darkBtn.classList.remove('active');
    } else {
        darkBtn.classList.add('active');
        lightBtn.classList.remove('active');
    }
}

// Sayfa yüklendiğinde mevcut temayı uygula
document.addEventListener('DOMContentLoaded', function() {
    var theme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeButtons(theme);
});
</script>
