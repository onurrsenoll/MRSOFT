<?php
/**
 * MR HASAR DANIŞMANLIK - Ana Yönlendirme
 * HERZAMAN FARKEDER
 */

require_once 'config.php';

// Giriş kontrolü
requireLogin();

// Sayfa yönlendirme
$page = $_GET['page'] ?? 'dashboard';

// İzin verilen sayfalar
$allowedPages = [
    // Ana sayfalar
    'dashboard',
    'dosyalar',
    'dosya_ekle',
    'dosya_detay',
    'crm',

    // Hesaplama
    'adk_hesaplama',
    'maluliyet_hesaplama',
    'ihbar_foyu',

    // Ortaklar
    'ortaklar',
    'ortak_hesaplari',
    'yonlendiren',

    // Tanımlamalar
    'tanim_sigorta',
    'tanim_asama',
    'tanim_masraf',
    'tanim_evrak',
    'tanim_personel',

    // Muhasebe
    'kasa',
    'cari',
    'gelir_gider',
    'maas_prim',

    // Raporlar
    'rapor_dosya_detay',
    'rapor_finansal',
    'rapor_ortaklar',
    'rapor_iban',

    // İletişim
    'mesajlar',
    'ajanda',

    // Sistem
    'ayarlar',
    'yetki_yonetimi',
    'kullanici_yonetimi',
    'api_ayarlari',
    'firma_bilgisi'
];

// Sayfa kontrolü
if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

// Modül dosyası
$moduleFile = 'modules/' . $page . '.php';

// Dosya var mı kontrol
if (!file_exists($moduleFile)) {
    $page = 'dashboard';
    $moduleFile = 'modules/dashboard.php';
}

// Header
include 'includes/header.php';

// Modülü yükle
include $moduleFile;

// Footer
include 'includes/footer.php';
