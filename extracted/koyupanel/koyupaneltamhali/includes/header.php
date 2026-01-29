<?php
/**
 * MR HASAR DANIŞMANLIK VE FİLO YÖNETİMİ - HEADER
 * HERZAMAN FARKEDER
 * v5.0 - Final Theme System (Light/Dark)
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config.php';
}

startSecureSession();

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Tema tercihini cookie'den al (varsayılan: dark)
$currentTheme = isset($_COOKIE['mr_theme']) ? $_COOKIE['mr_theme'] : 'dark';
$isLightMode = ($currentTheme === 'light');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="../assets/logos/logo-blue.png">
    <link rel="shortcut icon" type="image/png" href="../assets/logos/logo-blue.png">
    <link rel="apple-touch-icon" href="../assets/logos/logo-blue.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/icons.css" rel="stylesheet">
    <script>
        // Sayfa yüklenmeden önce tema ayarla (flash önleme)
        (function() {
            var theme = document.cookie.match(/mr_theme=([^;]+)/);
            theme = theme ? theme[1] : 'dark';
            if (theme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>
<style>
/* ========== LOGO - Responsive ========== */
.nav-logo {
    display: flex;
    align-items: center;
    margin-right: clamp(10px, 1vw, 20px);
    text-decoration: none;
    flex-shrink: 0;
}

.nav-logo img {
    height: clamp(32px, 3vw, 47px);
    width: auto;
    transition: all 0.3s ease;
}

/* Dark mode - show white logo, hide blue */
.logo-white { display: block; }
.logo-blue { display: none; }

/* Light mode - show blue logo, hide white */
body.light-mode .logo-white { display: none; }
body.light-mode .logo-blue { display: block; }

/* ========== NAVIGATION ========== */
.header{display:none!important}

/* Navigation Bar - Responsive & Zoom-friendly */
.nav {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 999999 !important;
    padding: 0.5rem 1rem !important;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: clamp(2px, 0.3vw, 5px);
    overflow: visible !important;
    height: auto !important;
    min-height: 56px;
    flex-wrap: nowrap;
    background: var(--bg-card) !important;
}

.nav::-webkit-scrollbar {
    display: none;
}

/* Nav Items - Responsive */
.nav-item {
    position: relative;
    cursor: pointer;
    display: inline-flex !important;
    align-items: center;
    padding: clamp(6px, 0.6vw, 10px) clamp(8px, 0.8vw, 14px);
    text-decoration: none;
    font-size: clamp(9px, 0.75vw, 12px);
    font-weight: 600;
    gap: clamp(3px, 0.3vw, 6px);
    border-radius: clamp(6px, 0.5vw, 10px);
    transition: all 0.3s ease;
    color: var(--text2);
    white-space: nowrap;
    flex-shrink: 0;
    z-index: 999999;
}

.nav-item:hover {
    color: var(--mr-blue);
    background: rgba(59, 130, 246, 0.1);
}

.nav-item.active {
    background: var(--gradient-btn);
    color: #fff;
}

/* Dropdown Menu - Responsive */
.dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: clamp(200px, 20vw, 260px);
    background: var(--bg-card) !important;
    border: 1px solid var(--border);
    border-radius: clamp(10px, 1vw, 14px);
    z-index: 9999999 !important;
    padding: clamp(6px, 0.5vw, 10px) 0;
    margin-top: clamp(4px, 0.4vw, 8px);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.25s ease;
    box-shadow: 0 15px 50px rgba(0,0,0,0.5);
}

.nav-item:hover > .dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Dropdown Items - Responsive */
.drop-item {
    display: flex;
    align-items: center;
    gap: clamp(8px, 0.8vw, 12px);
    padding: clamp(6px, 0.6vw, 10px) clamp(10px, 1vw, 14px);
    text-decoration: none;
    font-size: clamp(10px, 0.8vw, 12px);
    font-weight: 600;
    transition: all 0.2s ease;
    margin: clamp(2px, 0.3vw, 4px) clamp(6px, 0.6vw, 10px);
    border-radius: clamp(6px, 0.5vw, 8px);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(14, 165, 233, 0.1));
    color: var(--mr-blue);
    border: 1px solid rgba(59, 130, 246, 0.2);
    white-space: nowrap;
}

.drop-item:hover {
    background: var(--gradient-btn);
    color: #fff;
    border-color: transparent;
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.drop-item i {
    width: 28px;
    height: 28px;
    min-width: 28px;
    min-height: 28px;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(14, 165, 233, 0.2)) !important;
    border-radius: 6px;
    color: var(--mr-blue) !important;
    font-size: 12px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.drop-item:hover i {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}

/* Light mode dropdown */
body.light-mode .drop-item {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(14, 165, 233, 0.08));
    color: var(--mr-blue);
    border-color: rgba(59, 130, 246, 0.15);
}

body.light-mode .drop-item i {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(14, 165, 233, 0.15));
}

body.light-mode .drop-item:hover {
    background: var(--gradient-btn);
    color: #fff;
}

body.light-mode .drop-item:hover i {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}

/* Dropdown Divider */
.drop-div {
    height: 1px;
    margin: 10px 18px;
    background: var(--border);
}

/* Dropdown Label */
.drop-label {
    padding: 8px 18px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text3);
}

/* ADK PRO Special Button */
.drop-item.adk-pro {
    background: var(--gradient-btn) !important;
    color: #fff !important;
    font-weight: 600;
    margin: 8px 10px;
}
.drop-item.adk-pro:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 20px rgba(99, 102, 241, 0.4) !important;
}
.drop-item.adk-pro i {
    color: #fff;
}

/* ========== THEME TOGGLE ========== */
.theme-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
    margin-right: 15px;
}

.theme-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid var(--border);
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
    background: rgba(59, 130, 246, 0.1);
    color: var(--mr-blue);
}

.theme-btn:hover {
    background: rgba(59, 130, 246, 0.2);
    transform: scale(1.05);
}

body.light-mode .theme-btn {
    background: rgba(245, 158, 11, 0.15);
    border-color: rgba(245, 158, 11, 0.3);
    color: #f59e0b;
}

body.light-mode .theme-btn:hover {
    background: rgba(245, 158, 11, 0.25);
}

/* Logout Button */
.nav-item.logout-btn {
    margin-left: 0;
    color: #ef4444 !important;
}
.nav-item.logout-btn:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171 !important;
}

/* Body Padding */
body {
    padding-top: 65px !important;
}

/* Content Area */
.content {
    margin-top: 0 !important;
    padding-top: 25px !important;
    overflow: visible !important;
}
</style>
</head>
<body class="<?= $isLightMode ? 'light-mode' : '' ?>">
    <nav class="nav">
        <!-- LOGO - Tema'ya göre değişir -->
        <a href="dashboard.php" class="nav-logo" title="<?= APP_NAME ?>">
            <img src="../assets/logos/logo-white.png" alt="<?= APP_NAME ?>" class="logo-white">
            <img src="../assets/logos/logo-blue.png" alt="<?= APP_NAME ?>" class="logo-blue">
        </a>

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
            <button type="button" class="theme-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Tema Değiştir">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
        </div>

        <a href="../index.php?logout=1" class="nav-item logout-btn">
            <i class="fas fa-sign-out-alt"></i> ÇIKIŞ
        </a>
    </nav>

    <div class="content">

<script>
// Tema değiştirme fonksiyonu
function toggleTheme() {
    var body = document.body;
    var icon = document.getElementById('themeIcon');

    if (body.classList.contains('light-mode')) {
        body.classList.remove('light-mode');
        document.documentElement.classList.remove('light-mode');
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        document.cookie = 'mr_theme=dark;path=/;max-age=31536000;SameSite=Lax';
    } else {
        body.classList.add('light-mode');
        document.documentElement.classList.add('light-mode');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        document.cookie = 'mr_theme=light;path=/;max-age=31536000;SameSite=Lax';
    }
}

// Sayfa yüklendiğinde tema ikonunu güncelle
document.addEventListener('DOMContentLoaded', function() {
    var icon = document.getElementById('themeIcon');
    if (document.body.classList.contains('light-mode')) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
});
</script>
