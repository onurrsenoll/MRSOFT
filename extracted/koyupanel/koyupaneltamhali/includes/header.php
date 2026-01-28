<?php
/**
 * MR HASAR DANIŞMANLIK VE FİLO YÖNETİMİ - HEADER
 * HERZAMAN FARKEDER
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config.php';
}

startSecureSession();

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
<style>
.header{display:none!important}
.nav{position:fixed!important;top:0!important;left:0!important;right:0!important;z-index:9999!important;background:#1e293b!important;padding:10px 20px!important;border-bottom:2px solid #3b82f6!important;overflow:visible!important}
.nav-item{position:relative;cursor:pointer;display:inline-flex!important;align-items:center;padding:10px 15px;color:#f1f5f9;text-decoration:none;font-size:13px;font-weight:500;gap:8px}
.nav-item:hover{color:#3b82f6}
.dropdown{position:absolute;top:100%;left:0;min-width:240px;background:#1e293b!important;border:2px solid #3b82f6;border-radius:8px;box-shadow:0 15px 50px rgba(0,0,0,0.6);z-index:99999!important;padding:10px 0;margin-top:5px;opacity:0;visibility:hidden;transform:translateY(-10px);transition:all 0.2s ease}
.nav-item:hover>.dropdown{opacity:1;visibility:visible;transform:translateY(0)}
.drop-item{display:block;padding:14px 20px;color:#f1f5f9;text-decoration:none;font-size:13px}
.drop-item:hover{background:#3b82f6}
.drop-div{height:1px;background:#3b82f6;margin:8px 15px}
.drop-label{padding:8px 20px;color:#64748b;font-size:11px;font-weight:700}
body{padding-top:55px!important}
.content{margin-top:0!important;padding-top:25px!important;overflow:visible!important}
</style>
</head>
<body>
    <nav class="nav">
        <?php if (hasPermission('dashboard')): ?>
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-home"></i> ANASAYFA
        </a>
        <?php endif; ?>
        
        <?php if (hasPermission('dosyalar') || hasPermission('dosya_ekle') || hasPermission('crm')): ?>
        <div class="nav-item">
            <i class="fas fa-folder-open"></i> DOSYA İŞLEMLERİ
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:5px"></i>
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
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:5px"></i>
            <div class="dropdown">
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
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:5px"></i>
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
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:5px"></i>
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
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:5px"></i>
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
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:5px"></i>
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
            <i class="fas fa-chevron-down" style="font-size:8px;margin-left:5px"></i>
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
        
        <a href="../index.php?logout=1" class="nav-item" style="margin-left:auto;color:#ef4444">
            <i class="fas fa-sign-out-alt"></i> ÇIKIŞ
        </a>
    </nav>
    
    <div class="content">