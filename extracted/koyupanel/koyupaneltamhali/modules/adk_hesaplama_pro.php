<?php
/**
 * MR HASAR DANIŞMANLIK - ADK HESAPLAMA PRO
 * AI Destekli Gelişmiş Araç Değer Kaybı Hesaplama Modülü
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

require_once __DIR__ . '/../config.php';
startSecureSession();

$error = '';
$success = '';
$dosya = null;
$markalar = [];
$hesaplama_sonucu = null;

$case_id = intval($_GET['case_id'] ?? 0);
$standalone = isset($_GET['standalone']); // Bağımsız hesaplama modu

try {
    $db = getDB();

    // Dosya bilgilerini getir (case_id varsa)
    if ($case_id > 0) {
        $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$case_id]);
        $dosya = $stmt->fetch();
    }

    // Araç markaları
    try {
        $markalar = $db->query("SELECT DISTINCT marka FROM arac_veritabani ORDER BY marka")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $markalar = ['AUDI', 'BMW', 'CHEVROLET', 'CITROEN', 'DACIA', 'FIAT', 'FORD', 'HONDA', 'HYUNDAI', 'KIA', 'MAZDA', 'MERCEDES', 'NISSAN', 'OPEL', 'PEUGEOT', 'RENAULT', 'SEAT', 'SKODA', 'TOYOTA', 'VOLKSWAGEN', 'VOLVO'];
    }

} catch (Exception $e) {
    $error = 'DB HATASI: ' . $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<style>
/* ========================================
   MR HASAR ADK PRO - KURUMSAL TASARIM
   Lacivert - Mavi - Beyaz Renk Paleti
   ======================================== */

:root {
    --mr-primary: #1e3a5f;
    --mr-secondary: #2563eb;
    --mr-accent: #0ea5e9;
    --mr-success: #10b981;
    --mr-warning: #f59e0b;
    --mr-danger: #ef4444;
    --mr-dark: #0f172a;
    --mr-light: #f1f5f9;
    --mr-gradient: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    --mr-gradient-accent: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
}

.adk-pro-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* HEADER */
.adk-header {
    background: var(--mr-gradient);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
}

.adk-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.adk-header-content {
    position: relative;
    z-index: 1;
}

.adk-title {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.adk-title i {
    font-size: 32px;
    color: var(--mr-accent);
}

.adk-subtitle {
    font-size: 14px;
    color: rgba(255,255,255,0.8);
}

.adk-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.2);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    margin-top: 15px;
}

/* TABS */
.adk-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    background: var(--mr-dark);
    padding: 8px;
    border-radius: 12px;
}

.adk-tab {
    flex: 1;
    padding: 15px 20px;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #94a3b8;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.adk-tab:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

.adk-tab.active {
    background: var(--mr-gradient-accent);
    color: #fff;
    box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
}

.adk-tab i {
    font-size: 18px;
}

/* TAB CONTENT */
.adk-tab-content {
    display: none;
}

.adk-tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* CARDS */
.adk-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
}

.adk-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #334155;
}

.adk-card-icon {
    width: 45px;
    height: 45px;
    background: var(--mr-gradient-accent);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}

.adk-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #f1f5f9;
}

.adk-card-subtitle {
    font-size: 12px;
    color: #64748b;
}

/* FORM ELEMENTS */
.adk-form-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 1200px) {
    .adk-form-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .adk-form-grid { grid-template-columns: 1fr; }
}

.adk-form-group {
    display: flex;
    flex-direction: column;
}

.adk-form-group.full-width {
    grid-column: span 4;
}

@media (max-width: 1200px) {
    .adk-form-group.full-width { grid-column: span 2; }
}

@media (max-width: 768px) {
    .adk-form-group.full-width { grid-column: span 1; }
}

.adk-label {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.adk-label .required {
    color: var(--mr-danger);
}

.adk-label .ai-badge {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
}

.adk-input, .adk-select {
    width: 100%;
    padding: 14px 16px;
    background: var(--mr-dark);
    border: 2px solid #334155;
    border-radius: 10px;
    color: #f1f5f9;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.adk-input:focus, .adk-select:focus {
    outline: none;
    border-color: var(--mr-accent);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
}

.adk-input::placeholder {
    color: #64748b;
}

.adk-input-group {
    position: relative;
}

.adk-input-group .adk-input {
    padding-right: 100px;
}

.adk-input-addon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
}

.adk-btn-sm {
    padding: 8px 12px;
    background: var(--mr-gradient-accent);
    border: none;
    border-radius: 6px;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
}

.adk-btn-sm:hover {
    transform: scale(1.05);
}

/* PARÇA SEÇİMİ */
.adk-parts-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
}

@media (max-width: 1200px) {
    .adk-parts-grid { grid-template-columns: repeat(4, 1fr); }
}

@media (max-width: 768px) {
    .adk-parts-grid { grid-template-columns: repeat(2, 1fr); }
}

.adk-part-item {
    position: relative;
}

.adk-part-item input {
    display: none;
}

.adk-part-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px 10px;
    background: var(--mr-dark);
    border: 2px solid #334155;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.adk-part-label:hover {
    border-color: var(--mr-accent);
    background: rgba(14, 165, 233, 0.1);
}

.adk-part-item input:checked + .adk-part-label {
    border-color: var(--mr-success);
    background: rgba(16, 185, 129, 0.15);
}

.adk-part-icon {
    font-size: 24px;
    color: #64748b;
}

.adk-part-item input:checked + .adk-part-label .adk-part-icon {
    color: var(--mr-success);
}

.adk-part-name {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
}

.adk-part-item input:checked + .adk-part-label .adk-part-name {
    color: var(--mr-success);
}

.adk-part-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 20px;
    height: 20px;
    background: var(--mr-success);
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #fff;
}

.adk-part-item input:checked + .adk-part-label + .adk-part-badge,
.adk-part-item input:checked ~ .adk-part-badge {
    display: flex;
}

/* İŞLEM TİPİ SEÇİMİ */
.adk-operation-select {
    display: flex;
    gap: 8px;
    margin-top: 5px;
}

.adk-operation-btn {
    flex: 1;
    padding: 6px 10px;
    background: var(--mr-dark);
    border: 1px solid #334155;
    border-radius: 6px;
    color: #64748b;
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.adk-operation-btn:hover {
    border-color: var(--mr-accent);
}

.adk-operation-btn.degisim.active {
    background: rgba(239, 68, 68, 0.2);
    border-color: var(--mr-danger);
    color: var(--mr-danger);
}

.adk-operation-btn.boya.active {
    background: rgba(245, 158, 11, 0.2);
    border-color: var(--mr-warning);
    color: var(--mr-warning);
}

.adk-operation-btn.onarim.active {
    background: rgba(16, 185, 129, 0.2);
    border-color: var(--mr-success);
    color: var(--mr-success);
}

/* AI ARAŞTIRMA PANEL */
.adk-ai-panel {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

.adk-ai-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.adk-ai-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
}

.adk-ai-title {
    font-size: 14px;
    font-weight: 700;
    color: #e879f9;
}

.adk-ai-subtitle {
    font-size: 11px;
    color: #a78bfa;
}

.adk-ai-results {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

@media (max-width: 768px) {
    .adk-ai-results { grid-template-columns: 1fr; }
}

.adk-ai-stat {
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
    padding: 15px;
    text-align: center;
}

.adk-ai-stat-value {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
}

.adk-ai-stat-label {
    font-size: 11px;
    color: #a78bfa;
    margin-top: 5px;
}

/* HESAPLAMA BUTONU */
.adk-calculate-btn {
    width: 100%;
    padding: 20px 30px;
    background: var(--mr-gradient);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    transition: all 0.3s;
    margin-top: 25px;
    position: relative;
    overflow: hidden;
}

.adk-calculate-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.adk-calculate-btn:hover::before {
    left: 100%;
}

.adk-calculate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(30, 58, 95, 0.4);
}

.adk-calculate-btn i {
    font-size: 22px;
}

/* SONUÇ PANELİ */
.adk-result-panel {
    background: var(--mr-gradient);
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-top: 30px;
}

.adk-result-panel::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.adk-result-label {
    font-size: 14px;
    color: rgba(255,255,255,0.8);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 10px;
}

.adk-result-value {
    font-size: 56px;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.adk-result-currency {
    font-size: 32px;
    margin-right: 5px;
}

.adk-result-details {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.adk-result-detail {
    text-align: center;
}

.adk-result-detail-value {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
}

.adk-result-detail-label {
    font-size: 11px;
    color: rgba(255,255,255,0.7);
    margin-top: 3px;
}

/* RAPOR BUTONLARI */
.adk-report-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.adk-report-btn {
    padding: 15px 30px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    border: none;
    text-decoration: none;
}

.adk-report-btn.primary {
    background: #fff;
    color: var(--mr-primary);
}

.adk-report-btn.secondary {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.3);
}

.adk-report-btn:hover {
    transform: translateY(-2px);
}

/* UPLOAD AREA */
.adk-upload-area {
    border: 2px dashed #334155;
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: var(--mr-dark);
}

.adk-upload-area:hover {
    border-color: var(--mr-accent);
    background: rgba(14, 165, 233, 0.05);
}

.adk-upload-area.dragover {
    border-color: var(--mr-success);
    background: rgba(16, 185, 129, 0.1);
}

.adk-upload-icon {
    font-size: 48px;
    color: #64748b;
    margin-bottom: 15px;
}

.adk-upload-title {
    font-size: 16px;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 8px;
}

.adk-upload-subtitle {
    font-size: 13px;
    color: #64748b;
}

.adk-upload-formats {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 15px;
}

.adk-format-badge {
    padding: 5px 12px;
    background: #334155;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
}

/* LOADING */
.adk-loading {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.9);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.adk-loading.active {
    display: flex;
}

.adk-loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #334155;
    border-top-color: var(--mr-accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.adk-loading-text {
    margin-top: 20px;
    font-size: 16px;
    color: #f1f5f9;
    font-weight: 600;
}

/* DOSYA BİLGİ KUTUSU */
.adk-case-info {
    background: rgba(14, 165, 233, 0.1);
    border: 1px solid rgba(14, 165, 233, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}

.adk-case-main {
    display: flex;
    align-items: center;
    gap: 15px;
}

.adk-case-icon {
    width: 50px;
    height: 50px;
    background: var(--mr-gradient-accent);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
}

.adk-case-no {
    font-size: 18px;
    font-weight: 800;
    color: #f1f5f9;
}

.adk-case-detail {
    font-size: 13px;
    color: #94a3b8;
}

.adk-case-badges {
    display: flex;
    gap: 10px;
}

.adk-case-badge {
    padding: 8px 15px;
    background: var(--mr-dark);
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.adk-case-badge.plaka {
    color: var(--mr-accent);
}

.adk-case-badge.tarih {
    color: var(--mr-warning);
}
</style>

<div class="adk-pro-container">

    <!-- HEADER -->
    <div class="adk-header">
        <div class="adk-header-content">
            <div class="adk-title">
                <i class="fas fa-calculator"></i>
                ADK HESAPLAMA PRO
            </div>
            <div class="adk-subtitle">AI Destekli Gelişmiş Araç Değer Kaybı Hesaplama Sistemi</div>
            <div class="adk-badge">
                <i class="fas fa-robot"></i>
                Yapay Zeka Destekli
            </div>
        </div>
    </div>

    <?php if ($dosya): ?>
    <!-- DOSYA BİLGİSİ -->
    <div class="adk-case-info">
        <div class="adk-case-main">
            <div class="adk-case-icon"><i class="fas fa-folder-open"></i></div>
            <div>
                <div class="adk-case-no"><?= htmlspecialchars($dosya['dosya_no']) ?></div>
                <div class="adk-case-detail"><?= htmlspecialchars($dosya['ad_soyad'] ?? $dosya['unvan'] ?? '-') ?></div>
            </div>
        </div>
        <div class="adk-case-badges">
            <div class="adk-case-badge plaka"><i class="fas fa-car"></i> <?= htmlspecialchars($dosya['plaka'] ?? '-') ?></div>
            <div class="adk-case-badge tarih"><i class="fas fa-calendar"></i> <?= $dosya['kaza_tarihi'] ? date('d.m.Y', strtotime($dosya['kaza_tarihi'])) : '-' ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="adk-tabs">
        <button class="adk-tab active" data-tab="hizli">
            <i class="fas fa-bolt"></i>
            HIZLI HESAPLAMA
        </button>
        <button class="adk-tab" data-tab="detayli">
            <i class="fas fa-file-alt"></i>
            DETAYLI HESAPLAMA
        </button>
        <button class="adk-tab" data-tab="evrak">
            <i class="fas fa-upload"></i>
            EVRAK ANALİZİ
        </button>
    </div>

    <!-- HIZLI HESAPLAMA TAB -->
    <div class="adk-tab-content active" id="tab-hizli">
        <form id="hizliHesaplamaForm" method="POST">
            <input type="hidden" name="hesaplama_tipi" value="hizli">
            <input type="hidden" name="case_id" value="<?= $case_id ?>">

            <!-- ARAÇ BİLGİLERİ -->
            <div class="adk-card">
                <div class="adk-card-header">
                    <div class="adk-card-icon"><i class="fas fa-car"></i></div>
                    <div>
                        <div class="adk-card-title">Araç Bilgileri</div>
                        <div class="adk-card-subtitle">Hesaplama için gerekli araç bilgilerini girin</div>
                    </div>
                </div>

                <div class="adk-form-grid">
                    <div class="adk-form-group">
                        <label class="adk-label">Marka <span class="required">*</span></label>
                        <select name="marka" id="marka" class="adk-select" required onchange="getModeller()">
                            <option value="">Seçiniz</option>
                            <?php foreach ($markalar as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= ($dosya && $dosya['arac_marka'] == $m) ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="adk-form-group">
                        <label class="adk-label">Model <span class="required">*</span></label>
                        <select name="model" id="model" class="adk-select" required>
                            <option value="">Önce marka seçin</option>
                        </select>
                    </div>

                    <div class="adk-form-group">
                        <label class="adk-label">Model Yılı <span class="required">*</span></label>
                        <select name="model_yili" id="model_yili" class="adk-select" required>
                            <option value="">Seçiniz</option>
                            <?php for ($y = date('Y') + 1; $y >= 1990; $y--): ?>
                            <option value="<?= $y ?>" <?= ($dosya && $dosya['model_yili'] == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="adk-form-group">
                        <label class="adk-label">Kilometre</label>
                        <input type="text" name="kilometre" id="kilometre" class="adk-input" placeholder="Örn: 150.000">
                    </div>
                </div>
            </div>

            <!-- DEĞER VE HASAR BİLGİLERİ -->
            <div class="adk-card">
                <div class="adk-card-header">
                    <div class="adk-card-icon"><i class="fas fa-coins"></i></div>
                    <div>
                        <div class="adk-card-title">Değer ve Hasar Bilgileri</div>
                        <div class="adk-card-subtitle">Araç değeri ve hasar tutarını girin</div>
                    </div>
                </div>

                <div class="adk-form-grid">
                    <div class="adk-form-group">
                        <label class="adk-label">
                            Araç Rayiç Değeri (TL) <span class="required">*</span>
                            <span class="ai-badge">AI</span>
                        </label>
                        <div class="adk-input-group">
                            <input type="text" name="arac_degeri" id="arac_degeri" class="adk-input" placeholder="0" required>
                            <div class="adk-input-addon">
                                <button type="button" class="adk-btn-sm" onclick="piyasaArastir()">
                                    <i class="fas fa-search"></i> Araştır
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="adk-form-group">
                        <label class="adk-label">Hasar/Onarım Tutarı (TL)</label>
                        <input type="text" name="hasar_tutari" id="hasar_tutari" class="adk-input" placeholder="0">
                    </div>

                    <div class="adk-form-group">
                        <label class="adk-label">Geçmiş Hasar Kaydı</label>
                        <select name="gecmis_hasar" class="adk-select">
                            <option value="YOK">Hasar Kaydı Yok</option>
                            <option value="VAR">Hasar Kaydı Var</option>
                        </select>
                    </div>

                    <div class="adk-form-group">
                        <label class="adk-label">Karşı Taraf Kusur Oranı</label>
                        <select name="kusur_orani" class="adk-select">
                            <option value="100">%100</option>
                            <option value="75">%75</option>
                            <option value="50">%50</option>
                            <option value="25">%25</option>
                        </select>
                    </div>
                </div>

                <!-- AI PİYASA ARAŞTIRMASI PANELİ -->
                <div class="adk-ai-panel" id="aiPanel" style="display:none;">
                    <div class="adk-ai-header">
                        <div class="adk-ai-icon"><i class="fas fa-robot"></i></div>
                        <div>
                            <div class="adk-ai-title">AI Piyasa Araştırması</div>
                            <div class="adk-ai-subtitle">Emsal araç ilanları analiz edildi</div>
                        </div>
                    </div>
                    <div class="adk-ai-results">
                        <div class="adk-ai-stat">
                            <div class="adk-ai-stat-value" id="aiMinFiyat">-</div>
                            <div class="adk-ai-stat-label">Minimum Fiyat</div>
                        </div>
                        <div class="adk-ai-stat">
                            <div class="adk-ai-stat-value" id="aiOrtFiyat">-</div>
                            <div class="adk-ai-stat-label">Ortalama Fiyat</div>
                        </div>
                        <div class="adk-ai-stat">
                            <div class="adk-ai-stat-value" id="aiMaxFiyat">-</div>
                            <div class="adk-ai-stat-label">Maksimum Fiyat</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HASAR BÖLGELER -->
            <div class="adk-card">
                <div class="adk-card-header">
                    <div class="adk-card-icon"><i class="fas fa-car-crash"></i></div>
                    <div>
                        <div class="adk-card-title">Hasar Bölgeleri</div>
                        <div class="adk-card-subtitle">Hasarlı parçaları ve işlem türlerini seçin</div>
                    </div>
                </div>

                <div class="adk-parts-grid">
                    <?php
                    $parcalar = [
                        ['id' => 'on_tampon', 'name' => 'Ön Tampon', 'icon' => 'fa-car'],
                        ['id' => 'arka_tampon', 'name' => 'Arka Tampon', 'icon' => 'fa-car'],
                        ['id' => 'kaput', 'name' => 'Kaput', 'icon' => 'fa-square'],
                        ['id' => 'bagaj', 'name' => 'Bagaj Kapağı', 'icon' => 'fa-square'],
                        ['id' => 'on_camurluk_sag', 'name' => 'Ön Çamurluk (Sağ)', 'icon' => 'fa-car-side'],
                        ['id' => 'on_camurluk_sol', 'name' => 'Ön Çamurluk (Sol)', 'icon' => 'fa-car-side'],
                        ['id' => 'arka_camurluk_sag', 'name' => 'Arka Çamurluk (Sağ)', 'icon' => 'fa-car-side'],
                        ['id' => 'arka_camurluk_sol', 'name' => 'Arka Çamurluk (Sol)', 'icon' => 'fa-car-side'],
                        ['id' => 'on_kapi_sag', 'name' => 'Ön Kapı (Sağ)', 'icon' => 'fa-door-open'],
                        ['id' => 'on_kapi_sol', 'name' => 'Ön Kapı (Sol)', 'icon' => 'fa-door-open'],
                        ['id' => 'arka_kapi_sag', 'name' => 'Arka Kapı (Sağ)', 'icon' => 'fa-door-open'],
                        ['id' => 'arka_kapi_sol', 'name' => 'Arka Kapı (Sol)', 'icon' => 'fa-door-open'],
                        ['id' => 'tavan', 'name' => 'Tavan', 'icon' => 'fa-square'],
                        ['id' => 'far_sag', 'name' => 'Far (Sağ)', 'icon' => 'fa-lightbulb'],
                        ['id' => 'far_sol', 'name' => 'Far (Sol)', 'icon' => 'fa-lightbulb'],
                        ['id' => 'stop_sag', 'name' => 'Stop (Sağ)', 'icon' => 'fa-lightbulb'],
                        ['id' => 'stop_sol', 'name' => 'Stop (Sol)', 'icon' => 'fa-lightbulb'],
                        ['id' => 'ayna_sag', 'name' => 'Ayna (Sağ)', 'icon' => 'fa-square'],
                    ];

                    foreach ($parcalar as $parca):
                    ?>
                    <div class="adk-part-item">
                        <input type="checkbox" id="parca_<?= $parca['id'] ?>" name="parcalar[<?= $parca['id'] ?>][selected]" value="1">
                        <label class="adk-part-label" for="parca_<?= $parca['id'] ?>">
                            <div class="adk-part-icon"><i class="fas <?= $parca['icon'] ?>"></i></div>
                            <div class="adk-part-name"><?= $parca['name'] ?></div>
                            <div class="adk-operation-select">
                                <button type="button" class="adk-operation-btn degisim" data-parca="<?= $parca['id'] ?>" data-islem="degisim">Değişim</button>
                                <button type="button" class="adk-operation-btn boya" data-parca="<?= $parca['id'] ?>" data-islem="boya">Boya</button>
                            </div>
                            <input type="hidden" name="parcalar[<?= $parca['id'] ?>][islem]" id="islem_<?= $parca['id'] ?>" value="">
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- HESAPLA BUTONU -->
            <button type="submit" class="adk-calculate-btn">
                <i class="fas fa-calculator"></i>
                DEĞER KAYBINI HESAPLA
            </button>
        </form>

        <!-- SONUÇ PANELİ (JavaScript ile doldurulacak) -->
        <div class="adk-result-panel" id="sonucPanel" style="display:none;">
            <div class="adk-result-label">HESAPLANAN DEĞER KAYBI</div>
            <div class="adk-result-value">
                <span class="adk-result-currency">₺</span>
                <span id="sonucDeger">0</span>
            </div>
            <div class="adk-result-details">
                <div class="adk-result-detail">
                    <div class="adk-result-detail-value" id="sonucAracDeger">-</div>
                    <div class="adk-result-detail-label">Araç Rayiç Değeri</div>
                </div>
                <div class="adk-result-detail">
                    <div class="adk-result-detail-value" id="sonucOran">-</div>
                    <div class="adk-result-detail-label">Değer Kaybı Oranı</div>
                </div>
                <div class="adk-result-detail">
                    <div class="adk-result-detail-value" id="sonucYontem">-</div>
                    <div class="adk-result-detail-label">Hesaplama Yöntemi</div>
                </div>
            </div>
            <div class="adk-report-buttons">
                <button type="button" class="adk-report-btn primary" onclick="ozetRaporIndir()">
                    <i class="fas fa-file-pdf"></i> Özet Rapor PDF
                </button>
                <button type="button" class="adk-report-btn secondary" onclick="detayliRaporIndir()">
                    <i class="fas fa-file-alt"></i> Detaylı Rapor PDF
                </button>
            </div>
        </div>
    </div>

    <!-- DETAYLI HESAPLAMA TAB -->
    <div class="adk-tab-content" id="tab-detayli">
        <div class="adk-card">
            <div class="adk-card-header">
                <div class="adk-card-icon"><i class="fas fa-file-alt"></i></div>
                <div>
                    <div class="adk-card-title">Detaylı Hesaplama</div>
                    <div class="adk-card-subtitle">Kapsamlı rapor için tüm bilgileri girin</div>
                </div>
            </div>
            <p style="color:#94a3b8;text-align:center;padding:40px;">
                Detaylı hesaplama modülü için lütfen önce Hızlı Hesaplama bölümünden temel hesaplamayı yapın,<br>
                ardından rapor oluşturma aşamasında detaylı bilgileri ekleyebilirsiniz.
            </p>
        </div>
    </div>

    <!-- EVRAK ANALİZİ TAB -->
    <div class="adk-tab-content" id="tab-evrak">
        <div class="adk-card">
            <div class="adk-card-header">
                <div class="adk-card-icon"><i class="fas fa-upload"></i></div>
                <div>
                    <div class="adk-card-title">AI Evrak Analizi</div>
                    <div class="adk-card-subtitle">Ekspertiz raporunu yükleyin, yapay zeka analiz etsin</div>
                </div>
            </div>

            <div class="adk-upload-area" id="uploadArea">
                <input type="file" id="evrakInput" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                <div class="adk-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="adk-upload-title">Ekspertiz Raporunu Yükleyin</div>
                <div class="adk-upload-subtitle">PDF veya görsel dosyalarını sürükleyip bırakın veya tıklayın</div>
                <div class="adk-upload-formats">
                    <span class="adk-format-badge">PDF</span>
                    <span class="adk-format-badge">JPG</span>
                    <span class="adk-format-badge">PNG</span>
                </div>
            </div>

            <div class="adk-ai-panel" id="evrakAnaliz" style="display:none;margin-top:20px;">
                <div class="adk-ai-header">
                    <div class="adk-ai-icon"><i class="fas fa-brain"></i></div>
                    <div>
                        <div class="adk-ai-title">Evrak Analiz Sonuçları</div>
                        <div class="adk-ai-subtitle">Yapay zeka tarafından tespit edilen bilgiler</div>
                    </div>
                </div>
                <div id="evrakSonuclar" style="color:#e2e8f0;padding:15px;">
                    <!-- Analiz sonuçları burada gösterilecek -->
                </div>
            </div>
        </div>
    </div>

</div>

<!-- LOADING -->
<div class="adk-loading" id="loading">
    <div class="adk-loading-spinner"></div>
    <div class="adk-loading-text">Hesaplanıyor...</div>
</div>

<script>
// TAB SİSTEMİ
document.querySelectorAll('.adk-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.adk-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.adk-tab-content').forEach(c => c.classList.remove('active'));

        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

// PARÇA İŞLEM SEÇİMİ
document.querySelectorAll('.adk-operation-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const parca = this.dataset.parca;
        const islem = this.dataset.islem;
        const checkbox = document.getElementById('parca_' + parca);
        const islemInput = document.getElementById('islem_' + parca);

        // Aynı parça için diğer işlem butonlarını deaktif et
        this.parentElement.querySelectorAll('.adk-operation-btn').forEach(b => {
            b.classList.remove('active');
        });

        // Bu butonu aktif et
        this.classList.add('active');

        // Checkbox'ı işaretle
        checkbox.checked = true;

        // İşlem tipini kaydet
        islemInput.value = islem;
    });
});

// MODEL YÜKLE
function getModeller() {
    const marka = document.getElementById('marka').value;
    const modelSelect = document.getElementById('model');

    if (!marka) {
        modelSelect.innerHTML = '<option value="">Önce marka seçin</option>';
        return;
    }

    modelSelect.innerHTML = '<option value="">Yükleniyor...</option>';

    fetch('ajax/get_modeller.php?marka=' + encodeURIComponent(marka))
        .then(response => response.json())
        .then(data => {
            modelSelect.innerHTML = '<option value="">Seçiniz</option>';
            data.forEach(model => {
                modelSelect.innerHTML += '<option value="' + model + '">' + model + '</option>';
            });
        })
        .catch(() => {
            modelSelect.innerHTML = '<option value="">Hata oluştu</option>';
        });
}

// PİYASA ARAŞTIR
function piyasaArastir() {
    const marka = document.getElementById('marka').value;
    const model = document.getElementById('model').value;
    const yil = document.getElementById('model_yili').value;
    const km = document.getElementById('kilometre').value || '100000';

    if (!marka || !model || !yil) {
        alert('Lütfen marka, model ve yıl bilgilerini girin.');
        return;
    }

    document.getElementById('loading').classList.add('active');
    document.querySelector('.adk-loading-text').textContent = 'Piyasa araştırılıyor...';

    fetch('ajax/piyasa_arastir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ marka, model, yil, km })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading').classList.remove('active');

        if (data.success) {
            document.getElementById('aiPanel').style.display = 'block';
            document.getElementById('aiMinFiyat').textContent = formatMoney(data.min);
            document.getElementById('aiOrtFiyat').textContent = formatMoney(data.ortalama);
            document.getElementById('aiMaxFiyat').textContent = formatMoney(data.max);
            document.getElementById('arac_degeri').value = data.ortalama;
        } else {
            alert(data.message || 'Araştırma yapılamadı');
        }
    })
    .catch(() => {
        document.getElementById('loading').classList.remove('active');
        alert('Bağlantı hatası');
    });
}

// FORM SUBMIT
document.getElementById('hizliHesaplamaForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    document.getElementById('loading').classList.add('active');
    document.querySelector('.adk-loading-text').textContent = 'Değer kaybı hesaplanıyor...';

    fetch('ajax/adk_hesapla.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading').classList.remove('active');

        if (data.success) {
            // Sonuçları göster
            document.getElementById('sonucPanel').style.display = 'block';
            document.getElementById('sonucDeger').textContent = formatNumber(data.deger_kaybi);
            document.getElementById('sonucAracDeger').textContent = '₺' + formatNumber(data.arac_degeri);
            document.getElementById('sonucOran').textContent = '%' + data.oran.toFixed(2);
            document.getElementById('sonucYontem').textContent = 'TBK/Yargıtay';

            // Sayfayı sonuçlara kaydır
            document.getElementById('sonucPanel').scrollIntoView({ behavior: 'smooth' });

            // Hesaplama ID'sini sakla
            window.hesaplamaId = data.hesaplama_id;
        } else {
            alert(data.message || 'Hesaplama yapılamadı');
        }
    })
    .catch(() => {
        document.getElementById('loading').classList.remove('active');
        alert('Bağlantı hatası');
    });
});

// UPLOAD AREA
const uploadArea = document.getElementById('uploadArea');
const evrakInput = document.getElementById('evrakInput');

uploadArea.addEventListener('click', () => evrakInput.click());

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');

    if (e.dataTransfer.files.length) {
        evrakInput.files = e.dataTransfer.files;
        evrakAnaliz(e.dataTransfer.files[0]);
    }
});

evrakInput.addEventListener('change', () => {
    if (evrakInput.files.length) {
        evrakAnaliz(evrakInput.files[0]);
    }
});

function evrakAnaliz(file) {
    document.getElementById('loading').classList.add('active');
    document.querySelector('.adk-loading-text').textContent = 'Evrak analiz ediliyor...';

    const formData = new FormData();
    formData.append('evrak', file);

    fetch('ajax/evrak_analiz.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading').classList.remove('active');

        if (data.success) {
            document.getElementById('evrakAnaliz').style.display = 'block';

            let html = '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:15px;">';

            if (data.plaka) html += '<div><strong>Plaka:</strong> ' + data.plaka + '</div>';
            if (data.marka) html += '<div><strong>Marka:</strong> ' + data.marka + '</div>';
            if (data.model) html += '<div><strong>Model:</strong> ' + data.model + '</div>';
            if (data.yil) html += '<div><strong>Yıl:</strong> ' + data.yil + '</div>';
            if (data.km) html += '<div><strong>Kilometre:</strong> ' + formatNumber(data.km) + '</div>';
            if (data.hasar_tutari) html += '<div><strong>Hasar Tutarı:</strong> ₺' + formatNumber(data.hasar_tutari) + '</div>';

            html += '</div>';

            if (data.parcalar && data.parcalar.length > 0) {
                html += '<div style="margin-top:15px;"><strong>Tespit Edilen Parçalar:</strong><ul style="margin-top:10px;">';
                data.parcalar.forEach(p => {
                    html += '<li>' + p.ad + ' - ' + p.islem + '</li>';
                });
                html += '</ul></div>';
            }

            document.getElementById('evrakSonuclar').innerHTML = html;

            // Formu otomatik doldur
            if (data.marka) document.getElementById('marka').value = data.marka;
            if (data.model) setTimeout(() => { getModeller(); setTimeout(() => document.getElementById('model').value = data.model, 500); }, 100);
            if (data.yil) document.getElementById('model_yili').value = data.yil;
            if (data.km) document.getElementById('kilometre').value = data.km;
            if (data.hasar_tutari) document.getElementById('hasar_tutari').value = data.hasar_tutari;

        } else {
            alert(data.message || 'Evrak analiz edilemedi');
        }
    })
    .catch(() => {
        document.getElementById('loading').classList.remove('active');
        alert('Bağlantı hatası');
    });
}

// RAPOR İNDİR
function ozetRaporIndir() {
    if (!window.hesaplamaId) {
        alert('Önce hesaplama yapın');
        return;
    }
    window.open('ajax/adk_rapor_pdf.php?id=' + window.hesaplamaId + '&tip=ozet', '_blank');
}

function detayliRaporIndir() {
    if (!window.hesaplamaId) {
        alert('Önce hesaplama yapın');
        return;
    }
    window.open('ajax/adk_rapor_pdf.php?id=' + window.hesaplamaId + '&tip=detayli', '_blank');
}

// YARDIMCI FONKSİYONLAR
function formatMoney(num) {
    return '₺' + new Intl.NumberFormat('tr-TR').format(num);
}

function formatNumber(num) {
    return new Intl.NumberFormat('tr-TR').format(num);
}

// Sayfa yüklendiğinde marka seçiliyse modelleri yükle
<?php if ($dosya && $dosya['arac_marka']): ?>
setTimeout(function() {
    getModeller();
    <?php if ($dosya['arac_model']): ?>
    setTimeout(function() {
        document.getElementById('model').value = '<?= htmlspecialchars($dosya['arac_model']) ?>';
    }, 500);
    <?php endif; ?>
}, 100);
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
