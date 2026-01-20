<?php
/**
 * MR HASAR DANIŞMANLIK - Header
 * HERZAMAN FARKEDER
 */

// Okunmamış mesaj sayısı
$unreadMessages = getUnreadMessageCount($pdo);

// Bugünkü hatırlatmalar
$todayReminders = getTodayReminders($pdo);
$reminderCount = count($todayReminders);

// Aktif sayfa
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= APP_SLOGAN ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Flatpickr -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-brand">
                <i class="bi bi-car-front-fill me-2"></i>
                <span>MR HASAR</span>
            </a>
            <small class="text-muted d-block"><?= APP_SLOGAN ?></small>
        </div>

        <ul class="sidebar-nav">
            <!-- DOSYA İŞLEMLERİ -->
            <li class="sidebar-header-text">DOSYA İŞLEMLERİ</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                    <i class="bi bi-house-door"></i> <span>Anasayfa</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'dosyalar' ? 'active' : '' ?>" href="index.php?page=dosyalar">
                    <i class="bi bi-folder2-open"></i> <span>Dosyaları Listele</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'dosya_ekle' ? 'active' : '' ?>" href="index.php?page=dosya_ekle">
                    <i class="bi bi-folder-plus"></i> <span>Yeni Dosya Ekle</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'crm' ? 'active' : '' ?>" href="index.php?page=crm">
                    <i class="bi bi-people"></i> <span>CRM Takip</span>
                </a>
            </li>

            <!-- HESAPLAMA -->
            <li class="sidebar-header-text">HESAPLAMA</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'adk_hesaplama' ? 'active' : '' ?>" href="index.php?page=adk_hesaplama">
                    <i class="bi bi-calculator"></i> <span>Araç Değer Kaybı</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'maluliyet_hesaplama' ? 'active' : '' ?>" href="index.php?page=maluliyet_hesaplama">
                    <i class="bi bi-heart-pulse"></i> <span>Maluliyet Hesaplama</span>
                </a>
            </li>

            <!-- SERVİSLER -->
            <li class="sidebar-header-text">SERVİSLER</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'ihbar_foyu' ? 'active' : '' ?>" href="index.php?page=ihbar_foyu">
                    <i class="bi bi-file-earmark-text"></i> <span>İhbar Föyü</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'yonlendiren' ? 'active' : '' ?>" href="index.php?page=yonlendiren">
                    <i class="bi bi-signpost-2"></i> <span>Yönlendiren</span>
                </a>
            </li>

            <!-- ORTAKLAR -->
            <li class="sidebar-header-text">ORTAKLAR</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'ortaklar' ? 'active' : '' ?>" href="index.php?page=ortaklar">
                    <i class="bi bi-person-badge"></i> <span>İş Ortakları</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'ortak_hesaplari' ? 'active' : '' ?>" href="index.php?page=ortak_hesaplari">
                    <i class="bi bi-wallet2"></i> <span>Ortak Hesapları</span>
                </a>
            </li>

            <!-- TANIMLAMALAR -->
            <li class="sidebar-header-text">TANIMLAMALAR</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'tanim_personel' ? 'active' : '' ?>" href="index.php?page=tanim_personel">
                    <i class="bi bi-person-vcard"></i> <span>Personel</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'tanim_sigorta' ? 'active' : '' ?>" href="index.php?page=tanim_sigorta">
                    <i class="bi bi-shield-check"></i> <span>Sigorta Şirketleri</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'tanim_masraf' ? 'active' : '' ?>" href="index.php?page=tanim_masraf">
                    <i class="bi bi-receipt"></i> <span>Masraf Kalemleri</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'tanim_evrak' ? 'active' : '' ?>" href="index.php?page=tanim_evrak">
                    <i class="bi bi-file-earmark"></i> <span>Evrak Türleri</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'tanim_asama' ? 'active' : '' ?>" href="index.php?page=tanim_asama">
                    <i class="bi bi-diagram-3"></i> <span>Aşama Durumları</span>
                </a>
            </li>

            <!-- MUHASEBE -->
            <li class="sidebar-header-text">MUHASEBE</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'kasa' ? 'active' : '' ?>" href="index.php?page=kasa">
                    <i class="bi bi-safe"></i> <span>Kasalar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'cari' ? 'active' : '' ?>" href="index.php?page=cari">
                    <i class="bi bi-journal-text"></i> <span>Cariler</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'maas_prim' ? 'active' : '' ?>" href="index.php?page=maas_prim">
                    <i class="bi bi-cash-coin"></i> <span>Maaş / Prim</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'gelir_gider' ? 'active' : '' ?>" href="index.php?page=gelir_gider">
                    <i class="bi bi-graph-up-arrow"></i> <span>Gelir / Gider</span>
                </a>
            </li>

            <!-- RAPORLAR -->
            <li class="sidebar-header-text">RAPORLAR</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'rapor_dosya_detay' ? 'active' : '' ?>" href="index.php?page=rapor_dosya_detay">
                    <i class="bi bi-file-earmark-bar-graph"></i> <span>Dosya Detay Raporu</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'rapor_finansal' ? 'active' : '' ?>" href="index.php?page=rapor_finansal">
                    <i class="bi bi-bar-chart-line"></i> <span>Finansal Özet</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'rapor_ortaklar' ? 'active' : '' ?>" href="index.php?page=rapor_ortaklar">
                    <i class="bi bi-pie-chart"></i> <span>İş Ortakları Raporu</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'rapor_iban' ? 'active' : '' ?>" href="index.php?page=rapor_iban">
                    <i class="bi bi-bank"></i> <span>Mağdur IBAN Listesi</span>
                </a>
            </li>

            <!-- SİSTEM -->
            <li class="sidebar-header-text">SİSTEM</li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'ayarlar' ? 'active' : '' ?>" href="index.php?page=ayarlar">
                    <i class="bi bi-gear"></i> <span>Genel Ayarlar</span>
                </a>
            </li>
            <?php if (hasRole('admin')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'yetki_yonetimi' ? 'active' : '' ?>" href="index.php?page=yetki_yonetimi">
                    <i class="bi bi-shield-lock"></i> <span>Yetki Yönetimi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'kullanici_yonetimi' ? 'active' : '' ?>" href="index.php?page=kullanici_yonetimi">
                    <i class="bi bi-people-fill"></i> <span>Kullanıcı Yönetimi</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'firma_bilgisi' ? 'active' : '' ?>" href="index.php?page=firma_bilgisi">
                    <i class="bi bi-building"></i> <span>Firma Bilgisi</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar navbar navbar-expand-lg">
            <div class="container-fluid">
                <button class="btn btn-link sidebar-toggle" type="button">
                    <i class="bi bi-list"></i>
                </button>

                <div class="d-flex align-items-center ms-auto">
                    <!-- Hatırlatmalar -->
                    <div class="dropdown me-3">
                        <a href="#" class="btn btn-light position-relative" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <?php if ($reminderCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                <?= $reminderCount ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <h6 class="dropdown-header">Bugünkü Hatırlatmalar</h6>
                            <?php if (empty($todayReminders)): ?>
                                <span class="dropdown-item-text text-muted">Hatırlatma yok</span>
                            <?php else: ?>
                                <?php foreach (array_slice($todayReminders, 0, 5) as $reminder): ?>
                                <a class="dropdown-item" href="index.php?page=ajanda">
                                    <small class="text-muted"><?= e($reminder['event_time'] ?? '') ?></small>
                                    <div><?= e($reminder['title']) ?></div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="index.php?page=ajanda">
                                <small>Tümünü Gör</small>
                            </a>
                        </div>
                    </div>

                    <!-- Mesajlar -->
                    <div class="dropdown me-3">
                        <a href="#" class="btn btn-light position-relative" data-bs-toggle="dropdown">
                            <i class="bi bi-envelope"></i>
                            <?php if ($unreadMessages > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $unreadMessages ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <h6 class="dropdown-header">Mesajlar</h6>
                            <a class="dropdown-item" href="index.php?page=mesajlar">
                                <i class="bi bi-inbox me-2"></i>Gelen Kutusu
                                <?php if ($unreadMessages > 0): ?>
                                <span class="badge bg-danger ms-2"><?= $unreadMessages ?></span>
                                <?php endif; ?>
                            </a>
                            <a class="dropdown-item" href="index.php?page=mesajlar&tab=sent">
                                <i class="bi bi-send me-2"></i>Gönderilen
                            </a>
                        </div>
                    </div>

                    <!-- Kullanıcı Menüsü -->
                    <div class="dropdown">
                        <a href="#" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= e($_SESSION['user_name'] ?? 'Kullanıcı') ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <span class="dropdown-item-text text-muted small">
                                <?= e($_SESSION['user_email'] ?? '') ?>
                            </span>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="index.php?page=ayarlar">
                                <i class="bi bi-gear me-2"></i>Ayarlar
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="content-wrapper">
            <?php
            // Flash mesajı göster
            $flash = getFlash();
            if ($flash):
            ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
