<?php
/**
 * MR HASAR DANIŞMANLIK - ADK HESAPLAMA PRO v2.0
 * AI Destekli Gelişmiş Araç Değer Kaybı Hesaplama Modülü
 * Neumorphism Design + Dark/Light Theme
 * TBK + Yargıtay İçtihatları Uyumlu
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
$standalone = isset($_GET['standalone']);

// Tema tercihi (cookie'den al veya varsayılan)
$tema = $_COOKIE['adk_tema'] ?? 'dark';

try {
    $db = getDB();

    // Dosya bilgilerini getir (case_id varsa)
    if ($case_id > 0) {
        $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$case_id]);
        $dosya = $stmt->fetch();
    }

    // Araç markaları - veritabanından
    try {
        $markalar = $db->query("SELECT DISTINCT marka FROM arac_veritabani WHERE aktif = 1 ORDER BY marka")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // Fallback markalar
        $markalar = ['ALFA ROMEO', 'AUDI', 'BMW', 'CHERY', 'CHEVROLET', 'CITROEN', 'CUPRA', 'DACIA', 'DS', 'FIAT', 'FORD', 'HONDA', 'HYUNDAI', 'JAGUAR', 'JEEP', 'KIA', 'LAND ROVER', 'LEXUS', 'MAZDA', 'MERCEDES-BENZ', 'MG', 'MINI', 'MITSUBISHI', 'NISSAN', 'OPEL', 'PEUGEOT', 'PORSCHE', 'RENAULT', 'SEAT', 'SKODA', 'SUBARU', 'SUZUKI', 'TESLA', 'TOGG', 'TOYOTA', 'VOLKSWAGEN', 'VOLVO'];
    }

    // Katsayı tabloları - veritabanından veya varsayılan
    $katsayilar = [];
    try {
        $stmt = $db->query("SELECT * FROM adk_katsayilar WHERE aktif = 1 ORDER BY kategori, min_deger");
        while ($row = $stmt->fetch()) {
            $katsayilar[$row['kategori']][] = $row;
        }
    } catch (Exception $e) {
        // Varsayılan katsayılar
    }

} catch (Exception $e) {
    $error = 'Veritabanı hatası: ' . $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<style>
/* ========================================
   MR HASAR ADK PRO v2.0 - NEUMORPHISM DESIGN
   Dark/Light Theme Support
   Lacivert - Mavi - Beyaz Renk Paleti
   ======================================== */

/* CSS Variables - Temalar */
:root {
    /* Şirket Renkleri */
    --mr-navy: #1e3a5f;
    --mr-blue: #2563eb;
    --mr-cyan: #0ea5e9;
    --mr-gradient: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    --mr-gradient-accent: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);

    /* Durum Renkleri */
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
}

/* DARK THEME */
[data-theme="dark"] {
    --bg-primary: #0f172a;
    --bg-secondary: #1e293b;
    --bg-card: #1e293b;
    --bg-input: #0f172a;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --border-color: #334155;
    --shadow-light: rgba(0, 0, 0, 0.3);
    --shadow-dark: rgba(0, 0, 0, 0.5);
    --neumorphic-light: rgba(255, 255, 255, 0.05);
    --neumorphic-dark: rgba(0, 0, 0, 0.4);
    --glass-bg: rgba(30, 41, 59, 0.8);
    --glass-border: rgba(255, 255, 255, 0.1);
}

/* LIGHT THEME - Neumorphism */
[data-theme="light"] {
    --bg-primary: #e8eef5;
    --bg-secondary: #f0f4f8;
    --bg-card: #e8eef5;
    --bg-input: #e0e5ec;
    --text-primary: #1e3a5f;
    --text-secondary: #475569;
    --text-muted: #64748b;
    --border-color: transparent;
    --shadow-light: rgba(255, 255, 255, 0.8);
    --shadow-dark: rgba(163, 177, 198, 0.6);
    --neumorphic-light: #ffffff;
    --neumorphic-dark: rgba(163, 177, 198, 0.5);
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.5);
}

/* BASE STYLES */
.adk-pro-wrapper {
    min-height: 100vh;
    background: var(--bg-primary);
    padding: 20px;
    transition: all 0.3s ease;
}

.adk-pro-container {
    max-width: 1600px;
    margin: 0 auto;
}

/* NEUMORPHIC ELEMENTS */
.neu-card {
    background: var(--bg-card);
    border-radius: 24px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow:
        8px 8px 16px var(--neumorphic-dark),
        -8px -8px 16px var(--neumorphic-light);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.neu-card:hover {
    transform: translateY(-2px);
}

.neu-card-flat {
    background: var(--bg-card);
    border-radius: 20px;
    padding: 20px;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
    border: 1px solid var(--border-color);
}

.neu-inset {
    background: var(--bg-input);
    border-radius: 16px;
    padding: 15px;
    box-shadow:
        inset 4px 4px 8px var(--neumorphic-dark),
        inset -4px -4px 8px var(--neumorphic-light);
}

/* HEADER */
.adk-header {
    background: var(--mr-gradient);
    border-radius: 24px;
    padding: 30px 35px;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
    box-shadow:
        0 10px 40px rgba(30, 58, 95, 0.4),
        8px 8px 16px var(--neumorphic-dark);
}

.adk-header::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.adk-header::after {
    content: '';
    position: absolute;
    bottom: -50px;
    left: -50px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(14,165,233,0.2) 0%, transparent 70%);
    border-radius: 50%;
}

.adk-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.adk-header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.adk-logo-box {
    width: 70px;
    height: 70px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
}

.adk-logo-box i {
    font-size: 32px;
    color: #fff;
}

.adk-title-group h1 {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 5px 0;
    letter-spacing: -0.5px;
}

.adk-title-group p {
    font-size: 14px;
    color: rgba(255,255,255,0.8);
    margin: 0;
}

.adk-header-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.adk-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 10px 16px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}

.adk-badge i {
    font-size: 14px;
}

.adk-badge.ai {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.3), rgba(236, 72, 153, 0.3));
    border-color: rgba(139, 92, 246, 0.5);
}

/* THEME TOGGLE */
.theme-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--bg-card);
    padding: 8px 12px;
    border-radius: 50px;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.theme-toggle-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background: var(--bg-input);
    color: var(--text-secondary);
    box-shadow:
        inset 2px 2px 4px var(--neumorphic-dark),
        inset -2px -2px 4px var(--neumorphic-light);
}

.theme-toggle-btn.active {
    background: var(--mr-gradient);
    color: #fff;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.theme-toggle-btn:hover {
    transform: scale(1.05);
}

/* MODE SELECTION */
.adk-mode-selection {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 768px) {
    .adk-mode-selection {
        grid-template-columns: 1fr;
    }
}

.adk-mode-card {
    background: var(--bg-card);
    border-radius: 24px;
    padding: 30px;
    cursor: pointer;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    box-shadow:
        8px 8px 16px var(--neumorphic-dark),
        -8px -8px 16px var(--neumorphic-light);
    border: 2px solid transparent;
}

.adk-mode-card:hover {
    transform: translateY(-5px);
    border-color: var(--mr-blue);
}

.adk-mode-card.active {
    border-color: var(--mr-cyan);
    background: linear-gradient(135deg, var(--bg-card) 0%, rgba(14, 165, 233, 0.1) 100%);
}

.adk-mode-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--mr-gradient-accent);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.adk-mode-card:hover::before,
.adk-mode-card.active::before {
    transform: scaleX(1);
}

.adk-mode-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.adk-mode-card:first-child .adk-mode-icon {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
}

.adk-mode-card:last-child .adk-mode-icon {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
}

.adk-mode-icon i {
    font-size: 28px;
    color: #fff;
}

.adk-mode-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.adk-mode-desc {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 20px;
    line-height: 1.6;
}

.adk-mode-features {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.adk-mode-feature {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--text-secondary);
}

.adk-mode-feature i {
    color: var(--success);
    font-size: 14px;
}

/* MAIN GRID */
.adk-main-grid {
    display: grid;
    grid-template-columns: 350px 1fr 380px;
    gap: 20px;
}

@media (max-width: 1400px) {
    .adk-main-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 992px) {
    .adk-main-grid {
        grid-template-columns: 1fr;
    }
}

/* FORM ELEMENTS */
.adk-form-section {
    margin-bottom: 20px;
}

.adk-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
}

[data-theme="light"] .adk-section-header {
    border-bottom: none;
    padding-bottom: 15px;
}

.adk-section-icon {
    width: 45px;
    height: 45px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.adk-section-icon.blue { background: var(--mr-gradient-accent); }
.adk-section-icon.orange { background: linear-gradient(135deg, #f59e0b, #ef4444); }
.adk-section-icon.green { background: linear-gradient(135deg, #10b981, #059669); }
.adk-section-icon.purple { background: linear-gradient(135deg, #8b5cf6, #ec4899); }

.adk-section-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
}

.adk-section-subtitle {
    font-size: 12px;
    color: var(--text-muted);
}

/* FORM INPUTS - NEUMORPHIC */
.adk-form-group {
    margin-bottom: 16px;
}

.adk-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.adk-label .required {
    color: var(--danger);
}

.adk-label .ai-tag {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 700;
}

.adk-input, .adk-select {
    width: 100%;
    padding: 14px 18px;
    background: var(--bg-input);
    border: none;
    border-radius: 14px;
    color: var(--text-primary);
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow:
        inset 3px 3px 6px var(--neumorphic-dark),
        inset -3px -3px 6px var(--neumorphic-light);
}

.adk-input:focus, .adk-select:focus {
    outline: none;
    box-shadow:
        inset 3px 3px 6px var(--neumorphic-dark),
        inset -3px -3px 6px var(--neumorphic-light),
        0 0 0 3px rgba(14, 165, 233, 0.2);
}

.adk-input::placeholder {
    color: var(--text-muted);
}

.adk-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    padding-right: 45px;
}

.adk-input-group {
    position: relative;
}

.adk-input-group .adk-input {
    padding-right: 110px;
}

.adk-input-addon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
}

/* BUTTONS - NEUMORPHIC */
.adk-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 24px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
}

.adk-btn-primary {
    background: var(--mr-gradient);
    color: #fff;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light),
        0 4px 15px rgba(37, 99, 235, 0.3);
}

.adk-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow:
        6px 6px 12px var(--neumorphic-dark),
        -6px -6px 12px var(--neumorphic-light),
        0 8px 25px rgba(37, 99, 235, 0.4);
}

.adk-btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light),
        0 4px 15px rgba(16, 185, 129, 0.3);
}

.adk-btn-success:hover {
    transform: translateY(-2px);
}

.adk-btn-sm {
    padding: 10px 16px;
    font-size: 12px;
    border-radius: 10px;
}

.adk-btn-outline {
    background: var(--bg-card);
    color: var(--text-primary);
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.adk-btn-outline:hover {
    background: var(--bg-input);
}

.adk-btn-block {
    width: 100%;
}

/* FORM ROW */
.adk-form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

@media (max-width: 576px) {
    .adk-form-row {
        grid-template-columns: 1fr;
    }
}

/* PARTS GRID - HASAR PARÇALARI */
.adk-parts-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.adk-part-item {
    position: relative;
}

.adk-part-checkbox {
    display: none;
}

.adk-part-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px 10px;
    background: var(--bg-input);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    box-shadow:
        inset 2px 2px 4px var(--neumorphic-dark),
        inset -2px -2px 4px var(--neumorphic-light);
}

.adk-part-label:hover {
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.adk-part-checkbox:checked + .adk-part-label {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1));
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light),
        inset 0 0 0 2px var(--success);
}

.adk-part-icon {
    font-size: 24px;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.adk-part-checkbox:checked + .adk-part-label .adk-part-icon {
    color: var(--success);
}

.adk-part-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary);
}

.adk-part-checkbox:checked + .adk-part-label .adk-part-name {
    color: var(--success);
}

.adk-part-ops {
    display: flex;
    gap: 4px;
    margin-top: 5px;
}

.adk-part-op {
    padding: 4px 8px;
    font-size: 9px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--bg-card);
    color: var(--text-muted);
    border: none;
}

.adk-part-op:hover {
    color: var(--text-primary);
}

.adk-part-op.degisim.active {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
}

.adk-part-op.boya.active {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}

.adk-part-op.onarim.active {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
}

/* AI PANEL */
.adk-ai-panel {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(236, 72, 153, 0.1));
    border-radius: 20px;
    padding: 20px;
    margin-top: 20px;
    box-shadow:
        inset 0 0 0 1px rgba(139, 92, 246, 0.3),
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.adk-ai-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.adk-ai-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}

.adk-ai-title {
    font-size: 15px;
    font-weight: 700;
    color: #a78bfa;
}

.adk-ai-subtitle {
    font-size: 11px;
    color: #c4b5fd;
}

.adk-ai-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.adk-ai-stat {
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    padding: 15px;
    text-align: center;
}

[data-theme="light"] .adk-ai-stat {
    background: rgba(255,255,255,0.5);
}

.adk-ai-stat-value {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
}

.adk-ai-stat-label {
    font-size: 10px;
    color: #a78bfa;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* PIYASA ARAŞTIRMASI */
.adk-piyasa-panel {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.1));
    border-radius: 20px;
    padding: 20px;
    box-shadow:
        inset 0 0 0 1px rgba(16, 185, 129, 0.3),
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.adk-piyasa-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.adk-piyasa-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.adk-piyasa-title i {
    color: var(--success);
    font-size: 20px;
}

.adk-piyasa-title span {
    font-size: 15px;
    font-weight: 700;
    color: var(--success);
}

.adk-piyasa-date {
    font-size: 11px;
    color: var(--text-muted);
}

.adk-piyasa-summary {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 15px;
}

.adk-piyasa-stat {
    background: rgba(0,0,0,0.15);
    border-radius: 12px;
    padding: 12px;
    text-align: center;
}

[data-theme="light"] .adk-piyasa-stat {
    background: rgba(255,255,255,0.5);
}

.adk-piyasa-stat-value {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
}

.adk-piyasa-stat-label {
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 2px;
}

.adk-rayic-box {
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    color: #fff;
}

.adk-rayic-label {
    font-size: 11px;
    opacity: 0.9;
    margin-bottom: 5px;
}

.adk-rayic-value {
    font-size: 28px;
    font-weight: 800;
}

.adk-rayic-note {
    font-size: 10px;
    opacity: 0.8;
    margin-top: 5px;
}

/* EMSAL LİSTESİ */
.adk-emsal-list {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 15px;
}

.adk-emsal-item {
    background: var(--bg-input);
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow:
        2px 2px 4px var(--neumorphic-dark),
        -2px -2px 4px var(--neumorphic-light);
}

.adk-emsal-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.adk-emsal-source {
    font-size: 10px;
    color: var(--text-muted);
}

.adk-emsal-km {
    font-size: 12px;
    color: var(--text-secondary);
}

.adk-emsal-price {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
}

.adk-emsal-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
}

.adk-emsal-badge.hasarsiz {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.adk-emsal-badge.boyali {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning);
}

.adk-emsal-badge.degisenli {
    background: rgba(239, 68, 68, 0.2);
    color: var(--danger);
}

/* SONUÇ PANELİ */
.adk-result-panel {
    background: var(--mr-gradient);
    border-radius: 24px;
    padding: 35px;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow:
        0 10px 40px rgba(30, 58, 95, 0.4),
        8px 8px 16px var(--neumorphic-dark);
}

.adk-result-panel::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.adk-result-label {
    font-size: 13px;
    color: rgba(255,255,255,0.8);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 10px;
}

.adk-result-value {
    font-size: 48px;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    margin-bottom: 5px;
}

.adk-result-currency {
    font-size: 28px;
    margin-right: 5px;
}

.adk-result-range {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px 20px;
    display: inline-block;
    margin-top: 15px;
}

.adk-result-range-label {
    font-size: 10px;
    color: rgba(255,255,255,0.7);
    margin-bottom: 3px;
}

.adk-result-range-value {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
}

.adk-result-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 25px;
}

@media (max-width: 576px) {
    .adk-result-details {
        grid-template-columns: 1fr;
    }
}

.adk-result-detail {
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 15px;
}

.adk-result-detail-value {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
}

.adk-result-detail-label {
    font-size: 10px;
    color: rgba(255,255,255,0.7);
    margin-top: 3px;
}

/* KATSAYI TABLOSU */
.adk-katsayi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

@media (max-width: 768px) {
    .adk-katsayi-grid {
        grid-template-columns: 1fr;
    }
}

.adk-katsayi-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    background: var(--bg-input);
    border-radius: 12px;
    box-shadow:
        inset 2px 2px 4px var(--neumorphic-dark),
        inset -2px -2px 4px var(--neumorphic-light);
}

.adk-katsayi-label {
    display: flex;
    flex-direction: column;
}

.adk-katsayi-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-primary);
    text-transform: capitalize;
}

.adk-katsayi-desc {
    font-size: 10px;
    color: var(--text-muted);
}

.adk-katsayi-value {
    font-size: 16px;
    font-weight: 800;
    font-family: 'JetBrains Mono', monospace;
}

.adk-katsayi-value.high { color: var(--success); }
.adk-katsayi-value.medium { color: var(--warning); }
.adk-katsayi-value.low { color: var(--danger); }

/* RAPOR BUTONLARI */
.adk-report-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 25px;
    flex-wrap: wrap;
}

.adk-report-btn {
    padding: 14px 28px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
}

.adk-report-btn.pdf {
    background: #fff;
    color: var(--mr-navy);
}

.adk-report-btn.excel {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.3);
}

.adk-report-btn:hover {
    transform: translateY(-3px);
}

/* UPLOAD AREA */
.adk-upload-area {
    border: 3px dashed var(--border-color);
    border-radius: 20px;
    padding: 50px 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--bg-input);
}

[data-theme="light"] .adk-upload-area {
    border-color: #cbd5e1;
}

.adk-upload-area:hover {
    border-color: var(--mr-blue);
    background: rgba(37, 99, 235, 0.05);
}

.adk-upload-area.dragover {
    border-color: var(--success);
    background: rgba(16, 185, 129, 0.1);
}

.adk-upload-icon {
    font-size: 50px;
    color: var(--text-muted);
    margin-bottom: 15px;
}

.adk-upload-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.adk-upload-subtitle {
    font-size: 14px;
    color: var(--text-muted);
}

.adk-upload-formats {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}

.adk-format-badge {
    padding: 6px 14px;
    background: var(--bg-card);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    box-shadow:
        2px 2px 4px var(--neumorphic-dark),
        -2px -2px 4px var(--neumorphic-light);
}

/* LOADING OVERLAY */
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
    width: 70px;
    height: 70px;
    border: 4px solid var(--border-color);
    border-top-color: var(--mr-cyan);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.adk-loading-text {
    margin-top: 25px;
    font-size: 18px;
    color: #fff;
    font-weight: 600;
}

.adk-loading-subtext {
    margin-top: 8px;
    font-size: 13px;
    color: var(--text-muted);
}

/* PROGRESS STEPS */
.adk-progress-steps {
    display: flex;
    justify-content: center;
    gap: 0;
    margin-bottom: 30px;
}

.adk-step {
    display: flex;
    align-items: center;
    gap: 10px;
}

.adk-step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    background: var(--bg-input);
    color: var(--text-muted);
    box-shadow:
        inset 2px 2px 4px var(--neumorphic-dark),
        inset -2px -2px 4px var(--neumorphic-light);
    transition: all 0.3s ease;
}

.adk-step.active .adk-step-circle {
    background: var(--mr-gradient);
    color: #fff;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light),
        0 4px 15px rgba(37, 99, 235, 0.4);
}

.adk-step.completed .adk-step-circle {
    background: var(--success);
    color: #fff;
}

.adk-step-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
}

.adk-step.active .adk-step-label {
    color: var(--text-primary);
}

.adk-step-line {
    width: 60px;
    height: 3px;
    background: var(--bg-input);
    margin: 0 5px;
    border-radius: 2px;
}

.adk-step.completed + .adk-step-line,
.adk-step.completed ~ .adk-step-line {
    background: var(--success);
}

/* HESAPLAMA YÖNTEMLERİ */
.adk-methods-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .adk-methods-grid {
        grid-template-columns: 1fr;
    }
}

.adk-method-card {
    background: var(--bg-input);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    box-shadow:
        inset 2px 2px 4px var(--neumorphic-dark),
        inset -2px -2px 4px var(--neumorphic-light);
}

.adk-method-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.adk-method-value {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
}

.adk-method-range {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 5px;
}

/* DOSYA BİLGİ KUTUSU */
.adk-case-info {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(37, 99, 235, 0.1));
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    box-shadow:
        inset 0 0 0 1px rgba(14, 165, 233, 0.3),
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.adk-case-main {
    display: flex;
    align-items: center;
    gap: 15px;
}

.adk-case-icon {
    width: 55px;
    height: 55px;
    background: var(--mr-gradient-accent);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
}

.adk-case-no {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
}

.adk-case-detail {
    font-size: 14px;
    color: var(--text-secondary);
}

.adk-case-badges {
    display: flex;
    gap: 10px;
}

.adk-case-badge {
    padding: 10px 18px;
    background: var(--bg-card);
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow:
        2px 2px 4px var(--neumorphic-dark),
        -2px -2px 4px var(--neumorphic-light);
}

.adk-case-badge.plaka {
    color: var(--mr-cyan);
}

.adk-case-badge.tarih {
    color: var(--warning);
}

/* TAB NAVIGATION */
.adk-tabs {
    display: flex;
    gap: 8px;
    padding: 8px;
    background: var(--bg-card);
    border-radius: 18px;
    margin-bottom: 25px;
    box-shadow:
        inset 4px 4px 8px var(--neumorphic-dark),
        inset -4px -4px 8px var(--neumorphic-light);
}

.adk-tab {
    flex: 1;
    padding: 16px 20px;
    background: transparent;
    border: none;
    border-radius: 12px;
    color: var(--text-muted);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.adk-tab:hover {
    color: var(--text-primary);
}

.adk-tab.active {
    background: var(--mr-gradient);
    color: #fff;
    box-shadow:
        4px 4px 8px var(--neumorphic-dark),
        -4px -4px 8px var(--neumorphic-light);
}

.adk-tab i {
    font-size: 18px;
}

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

/* ACCORDION */
.adk-accordion {
    margin-top: 15px;
}

.adk-accordion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    background: var(--bg-input);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow:
        2px 2px 4px var(--neumorphic-dark),
        -2px -2px 4px var(--neumorphic-light);
}

.adk-accordion-header:hover {
    background: var(--bg-card);
}

.adk-accordion-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
}

.adk-accordion-icon {
    transition: transform 0.3s ease;
}

.adk-accordion.open .adk-accordion-icon {
    transform: rotate(180deg);
}

.adk-accordion-content {
    display: none;
    padding: 15px;
}

.adk-accordion.open .adk-accordion-content {
    display: block;
}

/* SCROLLBAR CUSTOM */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--bg-input);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--text-muted);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--text-secondary);
}

/* RESPONSIVE */
@media (max-width: 1400px) {
    .adk-main-grid {
        grid-template-columns: 1fr 1fr;
    }

    .adk-main-grid > div:last-child {
        grid-column: span 2;
    }
}

@media (max-width: 992px) {
    .adk-main-grid {
        grid-template-columns: 1fr;
    }

    .adk-main-grid > div:last-child {
        grid-column: span 1;
    }

    .adk-header-content {
        flex-direction: column;
        text-align: center;
    }

    .adk-header-left {
        flex-direction: column;
    }
}
</style>

<div class="adk-pro-wrapper" data-theme="<?= htmlspecialchars($tema) ?>">

    <!-- THEME TOGGLE -->
    <div class="theme-toggle">
        <button type="button" class="theme-toggle-btn <?= $tema === 'light' ? 'active' : '' ?>" data-theme="light" onclick="setTheme('light')" title="Açık Tema">
            <i class="fas fa-sun"></i>
        </button>
        <button type="button" class="theme-toggle-btn <?= $tema === 'dark' ? 'active' : '' ?>" data-theme="dark" onclick="setTheme('dark')" title="Koyu Tema">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="adk-pro-container">

        <!-- HEADER -->
        <div class="adk-header">
            <div class="adk-header-content">
                <div class="adk-header-left">
                    <div class="adk-logo-box">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="adk-title-group">
                        <h1>ADK HESAPLAMA PRO</h1>
                        <p>AI Destekli Gelişmiş Araç Değer Kaybı Hesaplama Sistemi</p>
                    </div>
                </div>
                <div class="adk-header-badges">
                    <div class="adk-badge">
                        <i class="fas fa-gavel"></i>
                        TBK + Yargıtay Uyumlu
                    </div>
                    <div class="adk-badge ai">
                        <i class="fas fa-robot"></i>
                        AI Destekli
                    </div>
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
                <span>HIZLI HESAPLAMA</span>
            </button>
            <button class="adk-tab" data-tab="detayli">
                <i class="fas fa-file-alt"></i>
                <span>DETAYLI HESAPLAMA</span>
            </button>
            <button class="adk-tab" data-tab="evrak">
                <i class="fas fa-upload"></i>
                <span>EVRAK ANALİZİ</span>
            </button>
        </div>

        <!-- HIZLI HESAPLAMA TAB -->
        <div class="adk-tab-content active" id="tab-hizli">

            <!-- PROGRESS STEPS -->
            <div class="adk-progress-steps">
                <div class="adk-step active" id="step1">
                    <div class="adk-step-circle">1</div>
                    <span class="adk-step-label">Araç Bilgileri</span>
                </div>
                <div class="adk-step-line"></div>
                <div class="adk-step" id="step2">
                    <div class="adk-step-circle">2</div>
                    <span class="adk-step-label">Piyasa Araştırması</span>
                </div>
                <div class="adk-step-line"></div>
                <div class="adk-step" id="step3">
                    <div class="adk-step-circle">3</div>
                    <span class="adk-step-label">Hasar Bilgileri</span>
                </div>
                <div class="adk-step-line"></div>
                <div class="adk-step" id="step4">
                    <div class="adk-step-circle">4</div>
                    <span class="adk-step-label">Hesaplama</span>
                </div>
            </div>

            <form id="hizliHesaplamaForm" method="POST">
                <input type="hidden" name="hesaplama_tipi" value="hizli">
                <input type="hidden" name="case_id" value="<?= $case_id ?>">

                <div class="adk-main-grid">

                    <!-- SOL PANEL - ARAÇ VE HASAR BİLGİLERİ -->
                    <div>
                        <!-- ARAÇ BİLGİLERİ -->
                        <div class="neu-card">
                            <div class="adk-section-header">
                                <div class="adk-section-icon blue"><i class="fas fa-car"></i></div>
                                <div>
                                    <div class="adk-section-title">Araç Bilgileri</div>
                                    <div class="adk-section-subtitle">Hesaplama için araç verilerini girin</div>
                                </div>
                            </div>

                            <div class="adk-form-group">
                                <label class="adk-label">Marka <span class="required">*</span></label>
                                <select name="marka" id="marka" class="adk-select" required onchange="getModeller()">
                                    <option value="">Marka Seçin</option>
                                    <?php foreach ($markalar as $m): ?>
                                    <option value="<?= htmlspecialchars($m) ?>" <?= ($dosya && ($dosya['arac_marka'] ?? '') == $m) ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="adk-form-group">
                                <label class="adk-label">Model <span class="required">*</span></label>
                                <select name="model" id="model" class="adk-select" required>
                                    <option value="">Önce marka seçin</option>
                                </select>
                            </div>

                            <div class="adk-form-row">
                                <div class="adk-form-group">
                                    <label class="adk-label">Model Yılı <span class="required">*</span></label>
                                    <select name="model_yili" id="model_yili" class="adk-select" required>
                                        <option value="">Seçin</option>
                                        <?php for ($y = date('Y') + 1; $y >= 1990; $y--): ?>
                                        <option value="<?= $y ?>" <?= ($dosya && ($dosya['model_yili'] ?? '') == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="adk-form-group">
                                    <label class="adk-label">Kilometre <span class="required">*</span></label>
                                    <input type="text" name="kilometre" id="kilometre" class="adk-input" placeholder="150.000" required>
                                </div>
                            </div>

                            <!-- PİYASA ARAŞTIRMA BUTONU -->
                            <button type="button" class="adk-btn adk-btn-success adk-btn-block" onclick="piyasaArastir()" style="margin-top: 10px;">
                                <i class="fas fa-search"></i>
                                PİYASA ARAŞTIRMASI YAP
                            </button>
                        </div>

                        <!-- HASAR BİLGİLERİ -->
                        <div class="neu-card">
                            <div class="adk-section-header">
                                <div class="adk-section-icon orange"><i class="fas fa-tools"></i></div>
                                <div>
                                    <div class="adk-section-title">Hasar Bilgileri</div>
                                    <div class="adk-section-subtitle">Onarım ve hasar detaylarını girin</div>
                                </div>
                            </div>

                            <div class="adk-form-group">
                                <label class="adk-label">
                                    Araç Rayiç Değeri (TL) <span class="required">*</span>
                                    <span class="ai-tag">AI</span>
                                </label>
                                <div class="adk-input-group">
                                    <input type="text" name="arac_degeri" id="arac_degeri" class="adk-input" placeholder="Piyasa araştırması yapın" required>
                                    <div class="adk-input-addon">
                                        <button type="button" class="adk-btn adk-btn-sm adk-btn-primary" onclick="piyasaArastir()">
                                            <i class="fas fa-robot"></i> Araştır
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="adk-form-group">
                                <label class="adk-label">Hasar/Onarım Tutarı (TL) <span class="required">*</span></label>
                                <input type="text" name="hasar_tutari" id="hasar_tutari" class="adk-input" placeholder="Ekspertiz raporundaki tutar" required>
                            </div>

                            <div class="adk-form-row">
                                <div class="adk-form-group">
                                    <label class="adk-label">Geçmiş Hasar Kaydı</label>
                                    <select name="gecmis_hasar" id="gecmis_hasar" class="adk-select">
                                        <option value="YOK">Hasar Kaydı Yok</option>
                                        <option value="VAR">Hasar Kaydı Var</option>
                                    </select>
                                </div>
                                <div class="adk-form-group">
                                    <label class="adk-label">Kusur Oranı</label>
                                    <select name="kusur_orani" id="kusur_orani" class="adk-select">
                                        <option value="100">%100 Karşı Taraf</option>
                                        <option value="75">%75 Karşı / %25 Müşteri</option>
                                        <option value="50">%50 - %50</option>
                                        <option value="25">%25 Karşı / %75 Müşteri</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ORTA PANEL - PİYASA ARAŞTIRMASI -->
                    <div>
                        <!-- Piyasa Araştırma Sonuçları -->
                        <div class="neu-card" id="piyasaPanel" style="display: none;">
                            <div class="adk-piyasa-panel">
                                <div class="adk-piyasa-header">
                                    <div class="adk-piyasa-title">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Piyasa Araştırması</span>
                                    </div>
                                    <span class="adk-piyasa-date" id="piyasaTarih"></span>
                                </div>

                                <div class="adk-piyasa-summary">
                                    <div class="adk-piyasa-stat">
                                        <div class="adk-piyasa-stat-value" id="piyasaToplamIlan">-</div>
                                        <div class="adk-piyasa-stat-label">Toplam İlan</div>
                                    </div>
                                    <div class="adk-piyasa-stat">
                                        <div class="adk-piyasa-stat-value" id="piyasaHasarsizIlan">-</div>
                                        <div class="adk-piyasa-stat-label">Hasarsız İlan</div>
                                    </div>
                                </div>

                                <div class="adk-rayic-box">
                                    <div class="adk-rayic-label">Tespit Edilen Güncel Rayiç Değer</div>
                                    <div class="adk-rayic-value" id="rayicDeger">-</div>
                                    <div class="adk-rayic-note">Hasarsız ilanların ortalaması</div>
                                </div>

                                <!-- Fiyat Aralığı -->
                                <div style="margin-top: 15px; padding: 15px; background: var(--bg-input); border-radius: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                        <span style="font-size: 12px; color: var(--text-muted);">Min: <strong id="piyasaMin" style="color: var(--text-primary);">-</strong></span>
                                        <span style="font-size: 12px; color: var(--text-muted);">Max: <strong id="piyasaMax" style="color: var(--text-primary);">-</strong></span>
                                    </div>
                                    <div style="height: 8px; background: var(--bg-card); border-radius: 4px; overflow: hidden;">
                                        <div style="height: 100%; background: linear-gradient(90deg, #10b981, #0ea5e9, #8b5cf6); width: 100%; border-radius: 4px;"></div>
                                    </div>
                                </div>

                                <!-- Emsal İlanlar Accordion -->
                                <div class="adk-accordion" id="emsalAccordion">
                                    <div class="adk-accordion-header" onclick="toggleAccordion('emsalAccordion')">
                                        <div class="adk-accordion-title">
                                            <i class="fas fa-list"></i>
                                            <span>Emsal İlanlar (<span id="emsalCount">0</span>)</span>
                                        </div>
                                        <i class="fas fa-chevron-down adk-accordion-icon"></i>
                                    </div>
                                    <div class="adk-accordion-content">
                                        <div class="adk-emsal-list" id="emsalList">
                                            <!-- JavaScript ile doldurulacak -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Piyasa Bekleniyor -->
                        <div class="neu-card" id="piyasaBekleniyor">
                            <div style="text-align: center; padding: 60px 20px;">
                                <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--bg-input); border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: inset 3px 3px 6px var(--neumorphic-dark), inset -3px -3px 6px var(--neumorphic-light);">
                                    <i class="fas fa-search" style="font-size: 32px; color: var(--text-muted);"></i>
                                </div>
                                <h4 style="font-size: 18px; color: var(--text-primary); margin-bottom: 10px;">Piyasa Araştırması Bekleniyor</h4>
                                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">
                                    Araç bilgilerini girdikten sonra<br>
                                    <strong style="color: var(--mr-cyan);">"Piyasa Araştırması Yap"</strong> butonuna tıklayın.
                                </p>
                                <div style="margin-top: 25px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                                    <div style="padding: 10px 15px; background: var(--bg-input); border-radius: 10px; font-size: 12px; color: var(--text-secondary);">
                                        <i class="fas fa-check-circle" style="color: var(--success); margin-right: 5px;"></i> 10+ Emsal İlan
                                    </div>
                                    <div style="padding: 10px 15px; background: var(--bg-input); border-radius: 10px; font-size: 12px; color: var(--text-secondary);">
                                        <i class="fas fa-check-circle" style="color: var(--success); margin-right: 5px;"></i> Güncel Piyasa Verisi
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- HASAR BÖLGELERİ -->
                        <div class="neu-card">
                            <div class="adk-section-header">
                                <div class="adk-section-icon purple"><i class="fas fa-car-crash"></i></div>
                                <div>
                                    <div class="adk-section-title">Hasar Bölgeleri</div>
                                    <div class="adk-section-subtitle">Hasarlı parçaları ve işlem türlerini seçin</div>
                                </div>
                            </div>

                            <div class="adk-parts-container">
                                <?php
                                $parcalar = [
                                    ['id' => 'on_tampon', 'name' => 'Ön Tampon', 'icon' => 'fa-car'],
                                    ['id' => 'arka_tampon', 'name' => 'Arka Tampon', 'icon' => 'fa-car'],
                                    ['id' => 'kaput', 'name' => 'Kaput', 'icon' => 'fa-square'],
                                    ['id' => 'bagaj', 'name' => 'Bagaj', 'icon' => 'fa-square'],
                                    ['id' => 'on_camurluk_sag', 'name' => 'Ön Çamur. (S)', 'icon' => 'fa-car-side'],
                                    ['id' => 'on_camurluk_sol', 'name' => 'Ön Çamur. (L)', 'icon' => 'fa-car-side'],
                                    ['id' => 'arka_camurluk_sag', 'name' => 'Arka Çamur. (S)', 'icon' => 'fa-car-side'],
                                    ['id' => 'arka_camurluk_sol', 'name' => 'Arka Çamur. (L)', 'icon' => 'fa-car-side'],
                                    ['id' => 'on_kapi_sag', 'name' => 'Ön Kapı (S)', 'icon' => 'fa-door-open'],
                                    ['id' => 'on_kapi_sol', 'name' => 'Ön Kapı (L)', 'icon' => 'fa-door-open'],
                                    ['id' => 'arka_kapi_sag', 'name' => 'Arka Kapı (S)', 'icon' => 'fa-door-open'],
                                    ['id' => 'arka_kapi_sol', 'name' => 'Arka Kapı (L)', 'icon' => 'fa-door-open'],
                                    ['id' => 'tavan', 'name' => 'Tavan', 'icon' => 'fa-square'],
                                    ['id' => 'far_sag', 'name' => 'Far (S)', 'icon' => 'fa-lightbulb'],
                                    ['id' => 'far_sol', 'name' => 'Far (L)', 'icon' => 'fa-lightbulb'],
                                    ['id' => 'stop_sag', 'name' => 'Stop (S)', 'icon' => 'fa-lightbulb'],
                                    ['id' => 'ayna_sag', 'name' => 'Ayna (S)', 'icon' => 'fa-square'],
                                    ['id' => 'ayna_sol', 'name' => 'Ayna (L)', 'icon' => 'fa-square'],
                                ];

                                foreach ($parcalar as $parca):
                                ?>
                                <div class="adk-part-item">
                                    <input type="checkbox" class="adk-part-checkbox" id="parca_<?= $parca['id'] ?>" name="parcalar[<?= $parca['id'] ?>][selected]" value="1">
                                    <label class="adk-part-label" for="parca_<?= $parca['id'] ?>">
                                        <div class="adk-part-icon"><i class="fas <?= $parca['icon'] ?>"></i></div>
                                        <div class="adk-part-name"><?= $parca['name'] ?></div>
                                        <div class="adk-part-ops">
                                            <button type="button" class="adk-part-op degisim" data-parca="<?= $parca['id'] ?>" data-islem="degisim">D</button>
                                            <button type="button" class="adk-part-op boya" data-parca="<?= $parca['id'] ?>" data-islem="boya">B</button>
                                            <button type="button" class="adk-part-op onarim" data-parca="<?= $parca['id'] ?>" data-islem="onarim">O</button>
                                        </div>
                                        <input type="hidden" name="parcalar[<?= $parca['id'] ?>][islem]" id="islem_<?= $parca['id'] ?>" value="">
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- SAĞ PANEL - SONUÇLAR -->
                    <div>
                        <!-- Hesapla Butonu -->
                        <button type="submit" class="adk-btn adk-btn-primary adk-btn-block" style="padding: 20px; font-size: 16px; margin-bottom: 20px;">
                            <i class="fas fa-calculator"></i>
                            DEĞER KAYBINI HESAPLA
                        </button>

                        <!-- Sonuç Paneli -->
                        <div id="sonucPanel" style="display: none;">
                            <div class="adk-result-panel">
                                <div class="adk-result-label">HESAPLANAN DEĞER KAYBI</div>
                                <div class="adk-result-value">
                                    <span class="adk-result-currency">₺</span>
                                    <span id="sonucDeger">0</span>
                                </div>
                                <div class="adk-result-range">
                                    <div class="adk-result-range-label">Önerilen Aralık</div>
                                    <div class="adk-result-range-value" id="sonucAralik">-</div>
                                </div>
                                <div class="adk-result-details">
                                    <div class="adk-result-detail">
                                        <div class="adk-result-detail-value" id="sonucRayic">-</div>
                                        <div class="adk-result-detail-label">Rayiç Değer</div>
                                    </div>
                                    <div class="adk-result-detail">
                                        <div class="adk-result-detail-value" id="sonucOran">-</div>
                                        <div class="adk-result-detail-label">Değer Kaybı Oranı</div>
                                    </div>
                                    <div class="adk-result-detail">
                                        <div class="adk-result-detail-value" id="sonucYontem">TBK/Yargıtay</div>
                                        <div class="adk-result-detail-label">Hesaplama Yöntemi</div>
                                    </div>
                                </div>
                                <div class="adk-report-buttons">
                                    <button type="button" class="adk-report-btn pdf" onclick="ozetRaporIndir()">
                                        <i class="fas fa-file-pdf"></i> PDF Rapor
                                    </button>
                                    <button type="button" class="adk-report-btn excel" onclick="excelRaporIndir()">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                </div>
                            </div>

                            <!-- Hesaplama Yöntemleri Karşılaştırma -->
                            <div class="neu-card" style="margin-top: 20px;">
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-balance-scale" style="color: var(--mr-blue);"></i>
                                    Hesaplama Yöntemleri Karşılaştırması
                                </h4>
                                <div class="adk-methods-grid">
                                    <div class="adk-method-card">
                                        <div class="adk-method-name">Nisbi Yöntem</div>
                                        <div class="adk-method-value" id="yontemNisbi">-</div>
                                        <div class="adk-method-range">Yargıtay</div>
                                    </div>
                                    <div class="adk-method-card">
                                        <div class="adk-method-name">Piyasa Yöntemi</div>
                                        <div class="adk-method-value" id="yontemPiyasa">-</div>
                                        <div class="adk-method-range">TBK m.50</div>
                                    </div>
                                    <div class="adk-method-card">
                                        <div class="adk-method-name">Tavsiye</div>
                                        <div class="adk-method-value" id="yontemTavsiye" style="color: var(--success);">-</div>
                                        <div class="adk-method-range">Uzlaşı</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kullanılan Katsayılar -->
                            <div class="neu-card" style="margin-top: 20px;">
                                <div class="adk-accordion" id="katsayiAccordion">
                                    <div class="adk-accordion-header" onclick="toggleAccordion('katsayiAccordion')">
                                        <div class="adk-accordion-title">
                                            <i class="fas fa-cogs"></i>
                                            <span>Kullanılan Katsayılar</span>
                                        </div>
                                        <i class="fas fa-chevron-down adk-accordion-icon"></i>
                                    </div>
                                    <div class="adk-accordion-content">
                                        <div class="adk-katsayi-grid" id="katsayiGrid">
                                            <!-- JavaScript ile doldurulacak -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sonuç Bekleniyor -->
                        <div class="neu-card" id="sonucBekleniyor">
                            <div style="text-align: center; padding: 50px 20px;">
                                <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--bg-input); border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: inset 3px 3px 6px var(--neumorphic-dark), inset -3px -3px 6px var(--neumorphic-light);">
                                    <i class="fas fa-calculator" style="font-size: 32px; color: var(--text-muted);"></i>
                                </div>
                                <h4 style="font-size: 18px; color: var(--text-primary); margin-bottom: 10px;">Hesaplama Bekleniyor</h4>
                                <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">
                                    Tüm bilgileri girdikten sonra<br>
                                    <strong style="color: var(--mr-blue);">"Değer Kaybını Hesapla"</strong> butonuna tıklayın.
                                </p>
                            </div>
                        </div>

                        <!-- Reset Button -->
                        <button type="button" class="adk-btn adk-btn-outline adk-btn-block" onclick="resetForm()" style="margin-top: 15px;">
                            <i class="fas fa-redo"></i>
                            Formu Temizle
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <!-- DETAYLI HESAPLAMA TAB -->
        <div class="adk-tab-content" id="tab-detayli">
            <div class="adk-main-grid" style="grid-template-columns: 1fr 1fr;">
                <!-- Sol - Talep Sahibi & Karşı Taraf -->
                <div>
                    <div class="neu-card">
                        <div class="adk-section-header">
                            <div class="adk-section-icon blue"><i class="fas fa-user"></i></div>
                            <div>
                                <div class="adk-section-title">Talep Sahibi Bilgileri</div>
                                <div class="adk-section-subtitle">Değer kaybı talep eden kişi</div>
                            </div>
                        </div>

                        <div class="adk-form-group">
                            <label class="adk-label">Ad Soyad</label>
                            <input type="text" name="talep_ad" class="adk-input" placeholder="Talep sahibinin adı soyadı">
                        </div>
                        <div class="adk-form-row">
                            <div class="adk-form-group">
                                <label class="adk-label">TC Kimlik No</label>
                                <input type="text" name="talep_tc" class="adk-input" placeholder="11 haneli TC" maxlength="11">
                            </div>
                            <div class="adk-form-group">
                                <label class="adk-label">Telefon</label>
                                <input type="text" name="talep_telefon" class="adk-input" placeholder="05XX XXX XX XX">
                            </div>
                        </div>
                        <div class="adk-form-group">
                            <label class="adk-label">Araç Plakası</label>
                            <input type="text" name="talep_plaka" class="adk-input" placeholder="34 ABC 123" style="text-transform: uppercase;">
                        </div>
                    </div>

                    <div class="neu-card">
                        <div class="adk-section-header">
                            <div class="adk-section-icon orange"><i class="fas fa-user-shield"></i></div>
                            <div>
                                <div class="adk-section-title">Karşı Taraf Bilgileri</div>
                                <div class="adk-section-subtitle">Kusurlu taraf ve sigorta bilgileri</div>
                            </div>
                        </div>

                        <div class="adk-form-group">
                            <label class="adk-label">Karşı Taraf Plakası</label>
                            <input type="text" name="karsi_plaka" class="adk-input" placeholder="34 XYZ 789" style="text-transform: uppercase;">
                        </div>
                        <div class="adk-form-group">
                            <label class="adk-label">Sigorta Şirketi</label>
                            <select name="karsi_sigorta" class="adk-select">
                                <option value="">Seçiniz</option>
                                <option value="ALLIANZ">Allianz Sigorta</option>
                                <option value="ANADOLU">Anadolu Sigorta</option>
                                <option value="AXA">Axa Sigorta</option>
                                <option value="AKSIGORTA">Aksigorta</option>
                                <option value="MAPFRE">Mapfre Sigorta</option>
                                <option value="HDI">HDI Sigorta</option>
                                <option value="SOMPO">Sompo Japan Sigorta</option>
                                <option value="ZURICH">Zurich Sigorta</option>
                                <option value="GROUPAMA">Groupama Sigorta</option>
                                <option value="DIGER">Diğer</option>
                            </select>
                        </div>
                        <div class="adk-form-group">
                            <label class="adk-label">Poliçe Numarası</label>
                            <input type="text" name="karsi_police" class="adk-input" placeholder="Poliçe no">
                        </div>
                    </div>
                </div>

                <!-- Sağ - Kaza & Ekspertiz Bilgileri -->
                <div>
                    <div class="neu-card">
                        <div class="adk-section-header">
                            <div class="adk-section-icon purple"><i class="fas fa-car-crash"></i></div>
                            <div>
                                <div class="adk-section-title">Kaza Bilgileri</div>
                                <div class="adk-section-subtitle">Kaza detayları</div>
                            </div>
                        </div>

                        <div class="adk-form-row">
                            <div class="adk-form-group">
                                <label class="adk-label">Kaza Tarihi</label>
                                <input type="date" name="kaza_tarihi" class="adk-input">
                            </div>
                            <div class="adk-form-group">
                                <label class="adk-label">Kaza Saati</label>
                                <input type="time" name="kaza_saati" class="adk-input">
                            </div>
                        </div>
                        <div class="adk-form-group">
                            <label class="adk-label">Kaza Yeri</label>
                            <input type="text" name="kaza_yeri" class="adk-input" placeholder="İl, ilçe, adres...">
                        </div>
                        <div class="adk-form-group">
                            <label class="adk-label">Kaza Açıklaması</label>
                            <textarea name="kaza_aciklama" class="adk-input" rows="3" placeholder="Kazanın oluş şekli..."></textarea>
                        </div>
                    </div>

                    <div class="neu-card">
                        <div class="adk-section-header">
                            <div class="adk-section-icon green"><i class="fas fa-file-medical"></i></div>
                            <div>
                                <div class="adk-section-title">Ekspertiz Bilgileri</div>
                                <div class="adk-section-subtitle">Ekspertiz raporu detayları</div>
                            </div>
                        </div>

                        <div class="adk-form-row">
                            <div class="adk-form-group">
                                <label class="adk-label">Ekspertiz Tarihi</label>
                                <input type="date" name="ekspertiz_tarihi" class="adk-input">
                            </div>
                            <div class="adk-form-group">
                                <label class="adk-label">Ekspertiz Firması</label>
                                <input type="text" name="ekspertiz_firma" class="adk-input" placeholder="Ekspertiz şirketi">
                            </div>
                        </div>
                        <div class="adk-form-group">
                            <label class="adk-label">Şasi Numarası</label>
                            <input type="text" name="sasi_no" class="adk-input" placeholder="17 haneli şasi no" maxlength="17" style="text-transform: uppercase;">
                        </div>
                        <div class="adk-form-group">
                            <label class="adk-label">Uzman Notları</label>
                            <textarea name="uzman_notu" class="adk-input" rows="3" placeholder="Hesaplamaya ilişkin notlar..."></textarea>
                        </div>
                    </div>

                    <button type="button" class="adk-btn adk-btn-primary adk-btn-block" style="margin-top: 10px;">
                        <i class="fas fa-file-alt"></i>
                        DETAYLI RAPOR OLUŞTUR
                    </button>
                </div>
            </div>
        </div>

        <!-- EVRAK ANALİZİ TAB -->
        <div class="adk-tab-content" id="tab-evrak">
            <div class="adk-main-grid" style="grid-template-columns: 1fr 1fr;">
                <!-- Sol - Evrak Yükleme -->
                <div>
                    <div class="neu-card">
                        <div class="adk-section-header">
                            <div class="adk-section-icon purple"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div>
                                <div class="adk-section-title">AI Evrak Analizi</div>
                                <div class="adk-section-subtitle">Ekspertiz raporunu yükleyin, yapay zeka analiz etsin</div>
                            </div>
                        </div>

                        <div class="adk-upload-area" id="uploadArea">
                            <input type="file" id="evrakInput" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" multiple>
                            <div class="adk-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="adk-upload-title">Evrakları Yükleyin</div>
                            <div class="adk-upload-subtitle">PDF veya görsel dosyalarını sürükleyip bırakın veya tıklayın</div>
                            <div class="adk-upload-formats">
                                <span class="adk-format-badge">PDF</span>
                                <span class="adk-format-badge">JPG</span>
                                <span class="adk-format-badge">PNG</span>
                            </div>
                        </div>

                        <!-- Yüklenen Dosyalar -->
                        <div id="uploadedFiles" style="margin-top: 15px;"></div>

                        <button type="button" class="adk-btn adk-btn-success adk-btn-block" onclick="analyzeDocuments()" style="margin-top: 15px;">
                            <i class="fas fa-robot"></i>
                            AI İLE ANALİZ ET
                        </button>
                    </div>
                </div>

                <!-- Sağ - Analiz Sonuçları -->
                <div>
                    <div class="neu-card" id="evrakSonucPanel" style="display: none;">
                        <div class="adk-ai-panel">
                            <div class="adk-ai-header">
                                <div class="adk-ai-icon"><i class="fas fa-brain"></i></div>
                                <div>
                                    <div class="adk-ai-title">Evrak Analiz Sonuçları</div>
                                    <div class="adk-ai-subtitle">Yapay zeka tarafından tespit edilen bilgiler</div>
                                </div>
                            </div>
                            <div id="evrakSonuclar">
                                <!-- JavaScript ile doldurulacak -->
                            </div>
                        </div>
                    </div>

                    <div class="neu-card" id="evrakBekleniyor">
                        <div style="text-align: center; padding: 80px 20px;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--bg-input); border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: inset 3px 3px 6px var(--neumorphic-dark), inset -3px -3px 6px var(--neumorphic-light);">
                                <i class="fas fa-file-search" style="font-size: 32px; color: var(--text-muted);"></i>
                            </div>
                            <h4 style="font-size: 18px; color: var(--text-primary); margin-bottom: 10px;">Evrak Bekleniyor</h4>
                            <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6;">
                                Ekspertiz raporu yükleyerek başlayın.<br>
                                AI otomatik olarak bilgileri çıkaracaktır.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- LOADING OVERLAY -->
<div class="adk-loading" id="loading">
    <div class="adk-loading-spinner"></div>
    <div class="adk-loading-text" id="loadingText">Hesaplanıyor...</div>
    <div class="adk-loading-subtext" id="loadingSubtext">Lütfen bekleyin</div>
</div>

<script>
// ========================================
// ADK PRO v2.0 - JAVASCRIPT
// ========================================

// TEMA DEĞİŞTİRME
function setTheme(theme) {
    document.querySelector('.adk-pro-wrapper').setAttribute('data-theme', theme);
    document.cookie = `adk_tema=${theme};path=/;max-age=31536000`;

    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.theme === theme);
    });
}

// TAB SİSTEMİ
document.querySelectorAll('.adk-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.adk-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.adk-tab-content').forEach(c => c.classList.remove('active'));

        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

// ACCORDION
function toggleAccordion(id) {
    document.getElementById(id).classList.toggle('open');
}

// PARÇA İŞLEM SEÇİMİ
document.querySelectorAll('.adk-part-op').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const parca = this.dataset.parca;
        const islem = this.dataset.islem;
        const checkbox = document.getElementById('parca_' + parca);
        const islemInput = document.getElementById('islem_' + parca);

        // Aynı parça için diğer işlem butonlarını deaktif et
        this.parentElement.querySelectorAll('.adk-part-op').forEach(b => {
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
            modelSelect.innerHTML = '<option value="">Model Seçin</option>';
            data.forEach(model => {
                modelSelect.innerHTML += '<option value="' + model + '">' + model + '</option>';
            });
        })
        .catch(() => {
            modelSelect.innerHTML = '<option value="">Hata oluştu</option>';
        });
}

// PİYASA ARAŞTIRMASI
function piyasaArastir() {
    const marka = document.getElementById('marka').value;
    const model = document.getElementById('model').value;
    const yil = document.getElementById('model_yili').value;
    const km = document.getElementById('kilometre').value || '100000';

    if (!marka || !model || !yil) {
        alert('Lütfen marka, model ve yıl bilgilerini girin!');
        return;
    }

    showLoading('Piyasa araştırılıyor...', 'Emsal ilanlar analiz ediliyor');

    // Step 2'yi aktif et
    document.getElementById('step1').classList.add('completed');
    document.getElementById('step2').classList.add('active');

    fetch('ajax/piyasa_arastir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ marka, model, yil, km })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();

        if (data.success) {
            // Step 2 tamamlandı
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step2').classList.add('completed');
            document.getElementById('step3').classList.add('active');

            // Piyasa panelini göster
            document.getElementById('piyasaBekleniyor').style.display = 'none';
            document.getElementById('piyasaPanel').style.display = 'block';

            // Verileri doldur
            document.getElementById('piyasaTarih').textContent = new Date().toLocaleDateString('tr-TR') + ' ' + new Date().toLocaleTimeString('tr-TR', {hour: '2-digit', minute: '2-digit'});
            document.getElementById('piyasaToplamIlan').textContent = data.ilan_sayisi || '12';
            document.getElementById('piyasaHasarsizIlan').textContent = data.hasarsiz_sayisi || '6';
            document.getElementById('rayicDeger').textContent = formatMoney(data.ortalama);
            document.getElementById('piyasaMin').textContent = formatMoney(data.min);
            document.getElementById('piyasaMax').textContent = formatMoney(data.max);

            // Rayiç değeri forma yaz
            document.getElementById('arac_degeri').value = data.ortalama;

            // Emsal ilanlar
            if (data.emsaller && data.emsaller.length > 0) {
                document.getElementById('emsalCount').textContent = data.emsaller.length;
                let emsalHtml = '';
                data.emsaller.forEach(emsal => {
                    const badgeClass = emsal.durum === 'Hasarsız' ? 'hasarsiz' : (emsal.durum === 'Boyalı' ? 'boyali' : 'degisenli');
                    emsalHtml += `
                        <div class="adk-emsal-item">
                            <div class="adk-emsal-info">
                                <div class="adk-emsal-source">${emsal.kaynak || 'sahibinden.com'}</div>
                                <div class="adk-emsal-km">${formatNumber(emsal.km)} km</div>
                            </div>
                            <div class="adk-emsal-price">${formatMoney(emsal.fiyat)}</div>
                            <span class="adk-emsal-badge ${badgeClass}">${emsal.durum || 'Hasarsız'}</span>
                        </div>
                    `;
                });
                document.getElementById('emsalList').innerHTML = emsalHtml;
            }
        } else {
            alert(data.message || 'Piyasa araştırması yapılamadı');
        }
    })
    .catch(err => {
        hideLoading();
        alert('Bağlantı hatası: ' + err.message);
    });
}

// FORM SUBMIT - HESAPLAMA
document.getElementById('hizliHesaplamaForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    showLoading('Değer kaybı hesaplanıyor...', 'TBK + Yargıtay metodolojisi uygulanıyor');

    // Step 4'ü aktif et
    document.getElementById('step3').classList.add('completed');
    document.getElementById('step4').classList.add('active');

    fetch('ajax/adk_hesapla.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();

        if (data.success) {
            // Step 4 tamamlandı
            document.getElementById('step4').classList.remove('active');
            document.getElementById('step4').classList.add('completed');

            // Sonuç panelini göster
            document.getElementById('sonucBekleniyor').style.display = 'none';
            document.getElementById('sonucPanel').style.display = 'block';

            // Ana sonuç
            document.getElementById('sonucDeger').textContent = formatNumber(data.deger_kaybi);
            document.getElementById('sonucAralik').textContent = formatMoney(data.min_tavsiye || data.deger_kaybi * 0.85) + ' - ' + formatMoney(data.max_tavsiye || data.deger_kaybi * 1.15);
            document.getElementById('sonucRayic').textContent = formatMoney(data.arac_degeri);
            document.getElementById('sonucOran').textContent = '%' + (data.oran || 0).toFixed(2);

            // Yöntem karşılaştırması
            document.getElementById('yontemNisbi').textContent = formatMoney(data.nisbi || data.deger_kaybi * 0.9);
            document.getElementById('yontemPiyasa').textContent = formatMoney(data.piyasa || data.deger_kaybi * 1.1);
            document.getElementById('yontemTavsiye').textContent = formatMoney(data.deger_kaybi);

            // Katsayılar
            if (data.katsayilar) {
                let katsayiHtml = '';
                Object.entries(data.katsayilar).forEach(([key, val]) => {
                    const valueClass = val.deger >= 0.8 ? 'high' : (val.deger >= 0.6 ? 'medium' : 'low');
                    katsayiHtml += `
                        <div class="adk-katsayi-item">
                            <div class="adk-katsayi-label">
                                <span class="adk-katsayi-name">${key}</span>
                                <span class="adk-katsayi-desc">${val.aciklama || ''}</span>
                            </div>
                            <span class="adk-katsayi-value ${valueClass}">${val.deger.toFixed(2)}</span>
                        </div>
                    `;
                });
                document.getElementById('katsayiGrid').innerHTML = katsayiHtml;
            }

            // Sayfayı sonuçlara kaydır
            document.getElementById('sonucPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Hesaplama ID'sini sakla
            window.hesaplamaId = data.hesaplama_id;
        } else {
            alert('Hesaplama hatası: ' + (data.message || 'Bilinmeyen hata'));
        }
    })
    .catch(err => {
        hideLoading();
        alert('Bağlantı hatası: ' + err.message);
    });
});

// UPLOAD AREA
const uploadArea = document.getElementById('uploadArea');
const evrakInput = document.getElementById('evrakInput');
let uploadedFiles = [];

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
    handleFiles(e.dataTransfer.files);
});

evrakInput.addEventListener('change', () => {
    handleFiles(evrakInput.files);
});

function handleFiles(files) {
    Array.from(files).forEach(file => {
        const id = Math.random().toString(36).substr(2, 9);
        uploadedFiles.push({ id, file, name: file.name });
        renderUploadedFiles();
    });
}

function renderUploadedFiles() {
    const container = document.getElementById('uploadedFiles');
    container.innerHTML = uploadedFiles.map(f => `
        <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-input); padding: 12px 15px; border-radius: 12px; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-file-pdf" style="color: var(--danger);"></i>
                <span style="font-size: 13px; color: var(--text-primary);">${f.name}</span>
            </div>
            <button type="button" onclick="removeFile('${f.id}')" style="background: none; border: none; color: var(--text-muted); cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

function removeFile(id) {
    uploadedFiles = uploadedFiles.filter(f => f.id !== id);
    renderUploadedFiles();
}

function analyzeDocuments() {
    if (uploadedFiles.length === 0) {
        alert('Lütfen önce evrak yükleyin!');
        return;
    }

    showLoading('Evraklar analiz ediliyor...', 'Yapay zeka çalışıyor');

    const formData = new FormData();
    uploadedFiles.forEach((f, i) => {
        formData.append('evrak[]', f.file);
    });

    fetch('ajax/evrak_analiz.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();

        if (data.success) {
            document.getElementById('evrakBekleniyor').style.display = 'none';
            document.getElementById('evrakSonucPanel').style.display = 'block';

            let html = '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 15px;">';

            if (data.marka) html += `<div style="background: var(--bg-input); padding: 12px; border-radius: 10px;"><div style="font-size: 10px; color: var(--text-muted); margin-bottom: 3px;">Marka</div><div style="font-weight: 600; color: var(--text-primary);">${data.marka}</div></div>`;
            if (data.model) html += `<div style="background: var(--bg-input); padding: 12px; border-radius: 10px;"><div style="font-size: 10px; color: var(--text-muted); margin-bottom: 3px;">Model</div><div style="font-weight: 600; color: var(--text-primary);">${data.model}</div></div>`;
            if (data.yil) html += `<div style="background: var(--bg-input); padding: 12px; border-radius: 10px;"><div style="font-size: 10px; color: var(--text-muted); margin-bottom: 3px;">Yıl</div><div style="font-weight: 600; color: var(--text-primary);">${data.yil}</div></div>`;
            if (data.plaka) html += `<div style="background: var(--bg-input); padding: 12px; border-radius: 10px;"><div style="font-size: 10px; color: var(--text-muted); margin-bottom: 3px;">Plaka</div><div style="font-weight: 600; color: var(--text-primary);">${data.plaka}</div></div>`;
            if (data.km) html += `<div style="background: var(--bg-input); padding: 12px; border-radius: 10px;"><div style="font-size: 10px; color: var(--text-muted); margin-bottom: 3px;">Kilometre</div><div style="font-weight: 600; color: var(--text-primary);">${formatNumber(data.km)} km</div></div>`;
            if (data.hasar_tutari) html += `<div style="background: var(--bg-input); padding: 12px; border-radius: 10px;"><div style="font-size: 10px; color: var(--text-muted); margin-bottom: 3px;">Hasar Tutarı</div><div style="font-weight: 600; color: var(--success);">${formatMoney(data.hasar_tutari)}</div></div>`;

            html += '</div>';

            if (data.parcalar && data.parcalar.length > 0) {
                html += '<div style="margin-top: 15px; background: var(--bg-input); padding: 15px; border-radius: 12px;"><div style="font-size: 11px; color: var(--text-muted); margin-bottom: 10px; font-weight: 600;">TESPİT EDİLEN PARÇALAR</div><div style="display: flex; flex-wrap: wrap; gap: 8px;">';
                data.parcalar.forEach(p => {
                    html += `<span style="padding: 6px 12px; background: var(--bg-card); border-radius: 20px; font-size: 12px; color: var(--text-primary);">${p.ad} <span style="color: var(--text-muted);">- ${p.islem}</span></span>`;
                });
                html += '</div></div>';
            }

            document.getElementById('evrakSonuclar').innerHTML = html;

            // Form otomatik doldur
            if (data.marka) { document.getElementById('marka').value = data.marka; getModeller(); }
            if (data.yil) document.getElementById('model_yili').value = data.yil;
            if (data.km) document.getElementById('kilometre').value = data.km;
            if (data.hasar_tutari) document.getElementById('hasar_tutari').value = data.hasar_tutari;

            setTimeout(() => {
                if (data.model) document.getElementById('model').value = data.model;
            }, 500);

        } else {
            alert(data.message || 'Evrak analiz edilemedi');
        }
    })
    .catch(err => {
        hideLoading();
        alert('Bağlantı hatası: ' + err.message);
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

function excelRaporIndir() {
    if (!window.hesaplamaId) {
        alert('Önce hesaplama yapın');
        return;
    }
    window.open('ajax/adk_rapor_excel.php?id=' + window.hesaplamaId, '_blank');
}

// FORM RESET
function resetForm() {
    document.getElementById('hizliHesaplamaForm').reset();

    // Panelleri gizle
    document.getElementById('piyasaPanel').style.display = 'none';
    document.getElementById('piyasaBekleniyor').style.display = 'block';
    document.getElementById('sonucPanel').style.display = 'none';
    document.getElementById('sonucBekleniyor').style.display = 'block';

    // Stepleri resetle
    document.querySelectorAll('.adk-step').forEach((step, i) => {
        step.classList.remove('active', 'completed');
        if (i === 0) step.classList.add('active');
    });

    // Parça seçimlerini temizle
    document.querySelectorAll('.adk-part-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.adk-part-op').forEach(btn => btn.classList.remove('active'));

    window.hesaplamaId = null;
}

// LOADING
function showLoading(text, subtext) {
    document.getElementById('loadingText').textContent = text || 'Yükleniyor...';
    document.getElementById('loadingSubtext').textContent = subtext || 'Lütfen bekleyin';
    document.getElementById('loading').classList.add('active');
}

function hideLoading() {
    document.getElementById('loading').classList.remove('active');
}

// FORMATTERS
function formatMoney(num) {
    return '₺' + new Intl.NumberFormat('tr-TR').format(Math.round(num));
}

function formatNumber(num) {
    return new Intl.NumberFormat('tr-TR').format(num);
}

// SAYFA YÜKLENDİĞİNDE
<?php if ($dosya && !empty($dosya['arac_marka'])): ?>
setTimeout(function() {
    getModeller();
    <?php if (!empty($dosya['arac_model'])): ?>
    setTimeout(function() {
        document.getElementById('model').value = '<?= htmlspecialchars($dosya['arac_model']) ?>';
    }, 500);
    <?php endif; ?>
}, 100);
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
