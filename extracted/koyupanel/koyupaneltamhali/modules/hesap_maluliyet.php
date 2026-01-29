<?php
/**
 * SAY HASAR DANIŞMANLIK - MALULİYET HESAPLAMA ROBOTU
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 */

require_once __DIR__ . '/../config.php';

startSecureSession();

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Dosya listesi (ilişkilendirme için)
try {
    $db = getDB();
    $stmt = $db->query("SELECT id, dosya_no, ad_soyad, tc_kimlik FROM cases WHERE case_type = 'BEDENI' AND status != 'KAPALI' ORDER BY id DESC LIMIT 100");
    $dosyalar = $stmt->fetchAll();
} catch (Exception $e) {
    $dosyalar = [];
}

include __DIR__ . '/../includes/header.php';
?>

<style>
:root {
    --bg-primary: #0f172a;
    --bg-secondary: #1e293b;
    --bg-card: #1e293b;
    --bg-input: #334155;
    --accent-blue: #3b82f6;
    --accent-purple: #8b5cf6;
    --accent-green: #10b981;
    --accent-orange: #f59e0b;
    --accent-red: #ef4444;
    --accent-cyan: #06b6d4;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --border: #334155;
}

.bh-container { max-width: 1400px; margin: 0 auto; }

.bh-header {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 182, 212, 0.15));
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.bh-title { display: flex; align-items: center; gap: 15px; }
.bh-title .icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #10b981, #06b6d4);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4);
}
.bh-title h2 {
    font-size: 22px;
    background: linear-gradient(135deg, #10b981, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.bh-title p { color: var(--text-secondary); font-size: 12px; margin-top: 5px; }

.bh-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.bh-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    border: 1px solid;
    text-transform: uppercase;
}
.bh-badge.pmf { background: rgba(16, 185, 129, 0.2); color: #10b981; border-color: rgba(16, 185, 129, 0.4); }
.bh-badge.trh { background: rgba(6, 182, 212, 0.2); color: #06b6d4; border-color: rgba(6, 182, 212, 0.4); }
.bh-badge.pdf { background: rgba(239, 68, 68, 0.2); color: #ef4444; border-color: rgba(239, 68, 68, 0.4); }

.bh-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
@media (max-width: 1200px) { .bh-grid { grid-template-columns: 1fr; } }

.bh-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}
.bh-panel-head {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(6, 182, 212, 0.1));
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
}
.bh-panel-head .icon {
    width: 36px; height: 36px;
    background: var(--accent-green);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.bh-panel-head h3 { font-size: 14px; font-weight: 700; }
.bh-panel-body { padding: 20px; }

.bh-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.bh-form-group { margin-bottom: 0; }
.bh-form-group.full { grid-column: span 2; }
.bh-label {
    display: block;
    font-size: 11px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    font-weight: 600;
    text-transform: uppercase;
}
.bh-input, .bh-select {
    width: 100%;
    padding: 12px 15px;
    background: var(--bg-input);
    border: 2px solid var(--border);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 13px;
    transition: all 0.3s;
}
.bh-input:focus, .bh-select:focus {
    outline: none;
    border-color: var(--accent-green);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}
.bh-select option { background: var(--bg-secondary); }

.bh-btn {
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    text-transform: uppercase;
}
.bh-btn-primary {
    background: linear-gradient(135deg, #10b981, #06b6d4);
    color: #fff;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
}
.bh-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5);
}
.bh-btn-success {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff;
}
.bh-btn-danger {
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: #fff;
}

/* Sonuç Alanı */
.result-placeholder {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}
.result-placeholder .icon {
    font-size: 60px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.result-display { display: none; }
.result-display.active { display: block; }

.result-card {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(6, 182, 212, 0.1));
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
    text-align: center;
}
.result-label { font-size: 12px; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; }
.result-value {
    font-size: 36px;
    font-weight: 800;
    background: linear-gradient(135deg, #10b981, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.result-sub { font-size: 11px; color: var(--text-muted); margin-top: 5px; }

.result-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px; }
.result-item {
    background: var(--bg-input);
    border-radius: 12px;
    padding: 18px;
    text-align: center;
}
.result-item .label { font-size: 10px; color: var(--text-muted); margin-bottom: 8px; }
.result-item .value { font-size: 16px; font-weight: 700; color: var(--accent-orange); }

/* Detay Tablosu */
.detail-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 12px;
}
.detail-table th, .detail-table td {
    padding: 12px;
    border: 1px solid var(--border);
    text-align: left;
}
.detail-table th {
    background: var(--bg-input);
    color: var(--text-secondary);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 10px;
}
.detail-table td { color: var(--text-primary); }
.detail-table .highlight { color: var(--accent-green); font-weight: 700; }

/* Loading */
.bh-loading {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 20px;
}
.bh-loading.active { display: flex; }
.bh-spinner {
    width: 60px; height: 60px;
    border: 4px solid var(--border);
    border-top-color: var(--accent-green);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Info Box */
.info-box {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 10px;
    padding: 15px;
    margin-top: 15px;
    font-size: 11px;
    color: var(--text-secondary);
    line-height: 1.6;
}
.info-box strong { color: var(--accent-blue); }
</style>

<div class="bh-container">
    <!-- HEADER -->
    <div class="bh-header">
        <div class="bh-title">
            <div class="icon">🏥</div>
            <div>
                <h2>MALULİYET / BEDENİ HASAR HESAPLAMA</h2>
                <p>PMF TABLOLARI İLE PROFESYONEL TAZMİNAT HESABI</p>
            </div>
        </div>
        <div class="bh-badges">
            <span class="bh-badge pmf">📊 PMF TABLOLARI</span>
            <span class="bh-badge trh">📋 TRH-2010</span>
            <span class="bh-badge trh">📋 CSO 1980</span>
            <span class="bh-badge pdf">📄 PDF RAPOR</span>
        </div>
    </div>

    <div class="bh-grid">
        <!-- SOL - GİRİŞ FORMU -->
        <div class="bh-panel">
            <div class="bh-panel-head">
                <div class="icon">📝</div>
                <h3>KİŞİ VE MALULİYET BİLGİLERİ</h3>
            </div>
            <div class="bh-panel-body">
                <div class="bh-form-grid">
                    <!-- Dosya İlişkilendirme -->
                    <div class="bh-form-group full">
                        <label class="bh-label">📁 DOSYA İLİŞKİLENDİR (OPSİYONEL)</label>
                        <select id="dosya_id" class="bh-select">
                            <option value="">-- DOSYA SEÇİNİZ --</option>
                            <?php foreach ($dosyalar as $d): ?>
                            <option value="<?= $d['id'] ?>">
                                <?= e($d['dosya_no']) ?> - <?= e($d['ad_soyad']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Ad Soyad -->
                    <div class="bh-form-group">
                        <label class="bh-label">👤 AD SOYAD *</label>
                        <input type="text" id="adSoyad" class="bh-input" placeholder="Mağdur adı soyadı" required>
                    </div>
                    
                    <!-- Doğum Tarihi -->
                    <div class="bh-form-group">
                        <label class="bh-label">📅 DOĞUM TARİHİ *</label>
                        <input type="date" id="dogumTarihi" class="bh-input" required>
                    </div>
                    
                    <!-- Cinsiyet -->
                    <div class="bh-form-group">
                        <label class="bh-label">⚧️ CİNSİYET *</label>
                        <select id="cinsiyet" class="bh-select" required>
                            <option value="">SEÇİNİZ...</option>
                            <option value="E">ERKEK</option>
                            <option value="K">KADIN</option>
                        </select>
                    </div>
                    
                    <!-- Kaza Tarihi -->
                    <div class="bh-form-group">
                        <label class="bh-label">💥 KAZA TARİHİ *</label>
                        <input type="date" id="kazaTarihi" class="bh-input" required>
                    </div>
                    
                    <!-- Meslek -->
                    <div class="bh-form-group">
                        <label class="bh-label">💼 MESLEK</label>
                        <input type="text" id="meslek" class="bh-input" placeholder="Örn: Mühendis">
                    </div>
                    
                    <!-- Aylık Gelir -->
                    <div class="bh-form-group">
                        <label class="bh-label">💰 AYLIK NET GELİR (₺) *</label>
                        <input type="number" id="aylikGelir" class="bh-input" placeholder="Örn: 35000" required>
                    </div>
                    
                    <!-- Maluliyet Oranı -->
                    <div class="bh-form-group">
                        <label class="bh-label">📊 MALULİYET ORANI (%) *</label>
                        <input type="number" id="maluliyetOrani" class="bh-input" min="1" max="100" placeholder="Örn: 15" required>
                    </div>
                    
                    <!-- Kusur Oranı -->
                    <div class="bh-form-group">
                        <label class="bh-label">⚖️ HAK SAHİBİ KUSUR (%) *</label>
                        <select id="kusurOrani" class="bh-select" required>
                            <option value="0">%0 - KUSURSUZ</option>
                            <option value="25">%25</option>
                            <option value="50">%50</option>
                            <option value="75">%75</option>
                            <option value="100">%100 - TAM KUSURLU</option>
                        </select>
                    </div>
                    
                    <!-- PMF Tablosu -->
                    <div class="bh-form-group">
                        <label class="bh-label">📋 PMF TABLOSU *</label>
                        <select id="pmfTablosu" class="bh-select" required>
                            <option value="TRH2010">TRH-2010 (Güncel)</option>
                            <option value="CSO1980">CSO 1980</option>
                            <option value="PMF1931">PMF 1931</option>
                        </select>
                    </div>
                    
                    <!-- Teknik Faiz -->
                    <div class="bh-form-group">
                        <label class="bh-label">📈 TEKNİK FAİZ ORANI (%)</label>
                        <select id="teknikFaiz" class="bh-select">
                            <option value="1.8">%1.8 (Yargıtay)</option>
                            <option value="2.0">%2.0</option>
                            <option value="2.5">%2.5</option>
                            <option value="3.0">%3.0</option>
                        </select>
                    </div>
                    
                    <!-- Emeklilik Yaşı -->
                    <div class="bh-form-group">
                        <label class="bh-label">🎂 EMEKLİLİK YAŞI</label>
                        <select id="emeklilikYasi" class="bh-select">
                            <option value="60">60 Yaş</option>
                            <option value="65" selected>65 Yaş (Standart)</option>
                            <option value="70">70 Yaş</option>
                        </select>
                    </div>
                    
                    <!-- Geçici İş Göremezlik -->
                    <div class="bh-form-group">
                        <label class="bh-label">🏥 GEÇİCİ İŞ GÖREMEZLİK (GÜN)</label>
                        <input type="number" id="geciciGun" class="bh-input" value="0" min="0" placeholder="0">
                    </div>
                    
                    <!-- Bakıcı İhtiyacı -->
                    <div class="bh-form-group">
                        <label class="bh-label">👨‍⚕️ BAKICI İHTİYACI</label>
                        <select id="bakiciIhtiyaci" class="bh-select">
                            <option value="0">YOK</option>
                            <option value="25">%25 - HAFİF</option>
                            <option value="50">%50 - ORTA</option>
                            <option value="75">%75 - AĞIR</option>
                            <option value="100">%100 - TAM</option>
                        </select>
                    </div>
                </div>
                
                <div class="info-box">
                    <strong>📌 BİLGİ:</strong> TRH-2010 tablosu Yargıtay tarafından kabul edilen güncel PMF tablosudur. 
                    Teknik faiz oranı %1.8 Yargıtay içtihatlarına uygundur. Aktif dönem emeklilik yaşına kadar, 
                    pasif dönem muhtemel ömür sonuna kadar hesaplanır.
                </div>
                
                <div style="display:flex;gap:12px;margin-top:25px;flex-wrap:wrap">
                    <button type="button" class="bh-btn bh-btn-primary" onclick="hesapla()">
                        <i class="fas fa-calculator"></i> HESAPLA
                    </button>
                    <button type="button" class="bh-btn bh-btn-success" onclick="exportPDF()" id="btnPDF" style="display:none">
                        <i class="fas fa-file-pdf"></i> PDF RAPOR
                    </button>
                    <button type="button" class="bh-btn bh-btn-danger" onclick="temizle()">
                        <i class="fas fa-trash"></i> TEMİZLE
                    </button>
                </div>
            </div>
        </div>

        <!-- SAĞ - SONUÇLAR -->
        <div class="bh-panel">
            <div class="bh-panel-head">
                <div class="icon" style="background:var(--accent-cyan)">💰</div>
                <h3>TAZMİNAT HESAPLAMA SONUÇLARI</h3>
            </div>
            <div class="bh-panel-body">
                <!-- Placeholder -->
                <div class="result-placeholder" id="resultPlaceholder">
                    <div class="icon">🏥</div>
                    <h3>HESAPLAMA BEKLENİYOR</h3>
                    <p>Kişi ve maluliyet bilgilerini girin ve HESAPLA butonuna tıklayın</p>
                </div>
                
                <!-- Sonuçlar -->
                <div class="result-display" id="resultDisplay">
                    <!-- Toplam Tazminat -->
                    <div class="result-card">
                        <div class="result-label">TOPLAM TAZMİNAT (KUSUR ÖNCESİ)</div>
                        <div class="result-value" id="toplamTazminat">₺0</div>
                        <div class="result-sub" id="toplamInfo">Aktif + Pasif + Geçici + Bakıcı</div>
                    </div>
                    
                    <div class="result-card" style="background:linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(239, 68, 68, 0.1));border-color:rgba(245, 158, 11, 0.3);">
                        <div class="result-label">KUSUR SONRASI TAZMİNAT</div>
                        <div class="result-value" id="kusurSonrasi" style="background:linear-gradient(135deg, #f59e0b, #ef4444);-webkit-background-clip:text;">₺0</div>
                        <div class="result-sub" id="kusurInfo">Karşı tarafın ödeyeceği tutar</div>
                    </div>
                    
                    <!-- Detay Grid -->
                    <div class="result-grid">
                        <div class="result-item">
                            <div class="label">AKTİF DÖNEM KAYBI</div>
                            <div class="value" id="aktifKayip">₺0</div>
                        </div>
                        <div class="result-item">
                            <div class="label">PASİF DÖNEM KAYBI</div>
                            <div class="value" id="pasifKayip">₺0</div>
                        </div>
                        <div class="result-item">
                            <div class="label">GEÇİCİ İŞ GÖREMEZLİK</div>
                            <div class="value" id="geciciKayip">₺0</div>
                        </div>
                        <div class="result-item">
                            <div class="label">BAKICI GİDERİ</div>
                            <div class="value" id="bakiciGideri">₺0</div>
                        </div>
                    </div>
                    
                    <!-- Detay Tablosu -->
                    <table class="detail-table" id="detailTable">
                        <thead>
                            <tr>
                                <th>PARAMETRE</th>
                                <th>DEĞER</th>
                            </tr>
                        </thead>
                        <tbody id="detailBody"></tbody>
                    </table>
                    
                    <!-- Analiz Raporu -->
                    <div style="background:var(--bg-input);border-radius:12px;padding:18px;margin-top:20px">
                        <h4 style="font-size:12px;color:var(--accent-green);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                            📊 HESAPLAMA DETAYI
                        </h4>
                        <div id="analizRapor" style="font-size:12px;color:var(--text-secondary);line-height:1.8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading -->
<div class="bh-loading" id="loadingOverlay">
    <div class="bh-spinner"></div>
    <div style="color:#fff;font-size:14px" id="loadingText">Hesaplanıyor...</div>
</div>

<!-- PDF Container (Hidden) -->
<div id="pdfContainer" style="position:absolute;left:-9999px;width:800px;background:#fff;padding:40px;color:#000"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// PMF TABLOLARI - TRH 2010 (Bakiye Ömür Süreleri)
const PMF_TRH2010 = {
    E: { // Erkek
        0: 71.38, 1: 70.90, 2: 69.95, 3: 69.00, 4: 68.03, 5: 67.06, 6: 66.09, 7: 65.11, 8: 64.13, 9: 63.15,
        10: 62.17, 11: 61.19, 12: 60.21, 13: 59.23, 14: 58.25, 15: 57.28, 16: 56.31, 17: 55.35, 18: 54.39, 19: 53.44,
        20: 52.50, 21: 51.56, 22: 50.63, 23: 49.70, 24: 48.77, 25: 47.85, 26: 46.92, 27: 46.00, 28: 45.08, 29: 44.16,
        30: 43.24, 31: 42.33, 32: 41.41, 33: 40.50, 34: 39.59, 35: 38.68, 36: 37.78, 37: 36.87, 38: 35.97, 39: 35.08,
        40: 34.18, 41: 33.29, 42: 32.41, 43: 31.52, 44: 30.65, 45: 29.78, 46: 28.91, 47: 28.05, 48: 27.20, 49: 26.35,
        50: 25.51, 51: 24.68, 52: 23.86, 53: 23.05, 54: 22.24, 55: 21.45, 56: 20.67, 57: 19.90, 58: 19.14, 59: 18.39,
        60: 17.66, 61: 16.94, 62: 16.23, 63: 15.54, 64: 14.86, 65: 14.20, 66: 13.55, 67: 12.92, 68: 12.30, 69: 11.70,
        70: 11.12, 71: 10.55, 72: 10.00, 73: 9.47, 74: 8.96, 75: 8.46, 76: 7.99, 77: 7.53, 78: 7.09, 79: 6.67,
        80: 6.27, 81: 5.89, 82: 5.53, 83: 5.19, 84: 4.87, 85: 4.57, 86: 4.29, 87: 4.03, 88: 3.79, 89: 3.56,
        90: 3.35, 91: 3.16, 92: 2.98, 93: 2.82, 94: 2.67, 95: 2.53, 96: 2.40, 97: 2.29, 98: 2.18, 99: 2.08, 100: 2.00
    },
    K: { // Kadın
        0: 76.30, 1: 75.77, 2: 74.82, 3: 73.86, 4: 72.90, 5: 71.93, 6: 70.95, 7: 69.98, 8: 69.00, 9: 68.02,
        10: 67.04, 11: 66.06, 12: 65.08, 13: 64.10, 14: 63.12, 15: 62.14, 16: 61.16, 17: 60.19, 18: 59.21, 19: 58.24,
        20: 57.27, 21: 56.30, 22: 55.33, 23: 54.37, 24: 53.40, 25: 52.44, 26: 51.48, 27: 50.52, 28: 49.56, 29: 48.60,
        30: 47.64, 31: 46.69, 32: 45.73, 33: 44.78, 34: 43.83, 35: 42.88, 36: 41.93, 37: 40.99, 38: 40.04, 39: 39.10,
        40: 38.16, 41: 37.23, 42: 36.29, 43: 35.36, 44: 34.44, 45: 33.51, 46: 32.60, 47: 31.68, 48: 30.77, 49: 29.87,
        50: 28.97, 51: 28.08, 52: 27.19, 53: 26.31, 54: 25.44, 55: 24.57, 56: 23.72, 57: 22.87, 58: 22.03, 59: 21.20,
        60: 20.38, 61: 19.57, 62: 18.78, 63: 17.99, 64: 17.22, 65: 16.46, 66: 15.72, 67: 14.99, 68: 14.27, 69: 13.57,
        70: 12.89, 71: 12.22, 72: 11.57, 73: 10.94, 74: 10.32, 75: 9.73, 76: 9.15, 77: 8.60, 78: 8.06, 79: 7.55,
        80: 7.06, 81: 6.59, 82: 6.14, 83: 5.72, 84: 5.32, 85: 4.94, 86: 4.59, 87: 4.26, 88: 3.95, 89: 3.67,
        90: 3.41, 91: 3.17, 92: 2.95, 93: 2.75, 94: 2.57, 95: 2.41, 96: 2.26, 97: 2.13, 98: 2.01, 99: 1.90, 100: 1.80
    }
};

// Asgari ücret (2025)
const ASGARI_UCRET = 22104;

let calculationResult = null;

// Yaş hesapla
function calculateAge(birthDate, referenceDate) {
    const birth = new Date(birthDate);
    const ref = new Date(referenceDate);
    let age = ref.getFullYear() - birth.getFullYear();
    const m = ref.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && ref.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

// Para formatla
function formatMoney(val) {
    return '₺' + Math.round(val).toLocaleString('tr-TR');
}

// Peşin Değer Katsayısı Hesapla (1.8% teknik faiz)
function calculatePesinDegerKatsayisi(yil, faizOrani) {
    if (yil <= 0) return 0;
    const r = faizOrani / 100;
    return (1 - Math.pow(1 + r, -yil)) / r;
}

// HESAPLA
function hesapla() {
    const adSoyad = document.getElementById('adSoyad').value;
    const dogumTarihi = document.getElementById('dogumTarihi').value;
    const cinsiyet = document.getElementById('cinsiyet').value;
    const kazaTarihi = document.getElementById('kazaTarihi').value;
    const meslek = document.getElementById('meslek').value;
    const aylikGelir = parseFloat(document.getElementById('aylikGelir').value) || 0;
    const maluliyetOrani = parseFloat(document.getElementById('maluliyetOrani').value) || 0;
    const kusurOrani = parseFloat(document.getElementById('kusurOrani').value) || 0;
    const pmfTablosu = document.getElementById('pmfTablosu').value;
    const teknikFaiz = parseFloat(document.getElementById('teknikFaiz').value) || 1.8;
    const emeklilikYasi = parseInt(document.getElementById('emeklilikYasi').value) || 65;
    const geciciGun = parseInt(document.getElementById('geciciGun').value) || 0;
    const bakiciIhtiyaci = parseFloat(document.getElementById('bakiciIhtiyaci').value) || 0;
    
    // Validasyon
    if (!adSoyad || !dogumTarihi || !cinsiyet || !kazaTarihi || !aylikGelir || !maluliyetOrani) {
        alert('Lütfen tüm zorunlu alanları doldurun!');
        return;
    }
    
    // Loading göster
    document.getElementById('loadingOverlay').classList.add('active');
    document.getElementById('loadingText').textContent = '📊 PMF Hesaplanıyor...';
    
    setTimeout(() => {
        // Yaş hesapla
        const kazaAnindakiYas = calculateAge(dogumTarihi, kazaTarihi);
        
        // PMF'den bakiye ömür al
        const pmfTable = PMF_TRH2010;
        const bakiyeOmur = pmfTable[cinsiyet][Math.min(kazaAnindakiYas, 100)] || 10;
        const muhtemelOlumYasi = kazaAnindakiYas + bakiyeOmur;
        
        // Aktif dönem (şu anki yaştan emeklilik yaşına kadar)
        const aktifDonemYil = Math.max(0, emeklilikYasi - kazaAnindakiYas);
        
        // Pasif dönem (emeklilikten muhtemel ölüme kadar)
        const pasifDonemYil = Math.max(0, muhtemelOlumYasi - emeklilikYasi);
        
        // Yıllık gelir
        const yillikGelir = aylikGelir * 12;
        
        // Pasif dönem geliri (asgari ücret)
        const pasifDonemGelir = ASGARI_UCRET * 12;
        
        // Peşin değer katsayıları
        const aktifPDK = calculatePesinDegerKatsayisi(aktifDonemYil, teknikFaiz);
        const pasifPDK = calculatePesinDegerKatsayisi(pasifDonemYil, teknikFaiz);
        
        // Aktif dönem kaybı
        const aktifDonemKaybi = yillikGelir * aktifPDK * (maluliyetOrani / 100);
        
        // Pasif dönem kaybı (iskonto edilmiş)
        const pasifIskonto = Math.pow(1 + (teknikFaiz / 100), -aktifDonemYil);
        const pasifDonemKaybi = pasifDonemGelir * pasifPDK * (maluliyetOrani / 100) * pasifIskonto;
        
        // Geçici iş göremezlik
        const gunlukGelir = aylikGelir / 30;
        const geciciIsGoremezlik = gunlukGelir * geciciGun;
        
        // Bakıcı gideri (ömür boyu)
        let bakiciGideriToplam = 0;
        if (bakiciIhtiyaci > 0) {
            const bakiciAylik = ASGARI_UCRET * (bakiciIhtiyaci / 100);
            const bakiciYillik = bakiciAylik * 12;
            const bakiciPDK = calculatePesinDegerKatsayisi(bakiyeOmur, teknikFaiz);
            bakiciGideriToplam = bakiciYillik * bakiciPDK;
        }
        
        // Toplam tazminat
        const toplamTazminat = aktifDonemKaybi + pasifDonemKaybi + geciciIsGoremezlik + bakiciGideriToplam;
        
        // Kusur sonrası
        const hakliOran = 100 - kusurOrani;
        const kusurSonrasiTazminat = toplamTazminat * (hakliOran / 100);
        
        // Sonuç objesi
        calculationResult = {
            adSoyad,
            dogumTarihi,
            cinsiyet: cinsiyet === 'E' ? 'ERKEK' : 'KADIN',
            kazaTarihi,
            meslek,
            aylikGelir,
            yillikGelir,
            maluliyetOrani,
            kusurOrani,
            hakliOran,
            pmfTablosu,
            teknikFaiz,
            emeklilikYasi,
            kazaAnindakiYas,
            bakiyeOmur,
            muhtemelOlumYasi,
            aktifDonemYil,
            pasifDonemYil,
            aktifPDK,
            pasifPDK,
            aktifDonemKaybi,
            pasifDonemKaybi,
            geciciGun,
            geciciIsGoremezlik,
            bakiciIhtiyaci,
            bakiciGideriToplam,
            toplamTazminat,
            kusurSonrasiTazminat
        };
        
        // Sonuçları göster
        displayResults();
        
        document.getElementById('loadingOverlay').classList.remove('active');
        document.getElementById('btnPDF').style.display = 'inline-flex';
        
    }, 1500);
}

// Sonuçları göster
function displayResults() {
    document.getElementById('resultPlaceholder').style.display = 'none';
    document.getElementById('resultDisplay').classList.add('active');
    
    const r = calculationResult;
    
    document.getElementById('toplamTazminat').textContent = formatMoney(r.toplamTazminat);
    document.getElementById('kusurSonrasi').textContent = formatMoney(r.kusurSonrasiTazminat);
    document.getElementById('kusurInfo').textContent = r.kusurOrani > 0 ? 
        `%${r.hakliOran} haklılık oranı uygulandı` : 'Kusur indirimi yok';
    
    document.getElementById('aktifKayip').textContent = formatMoney(r.aktifDonemKaybi);
    document.getElementById('pasifKayip').textContent = formatMoney(r.pasifDonemKaybi);
    document.getElementById('geciciKayip').textContent = formatMoney(r.geciciIsGoremezlik);
    document.getElementById('bakiciGideri').textContent = formatMoney(r.bakiciGideriToplam);
    
    // Detay tablosu
    document.getElementById('detailBody').innerHTML = `
        <tr><td>Kaza Anındaki Yaş</td><td class="highlight">${r.kazaAnindakiYas} yaş</td></tr>
        <tr><td>PMF Bakiye Ömür (${r.pmfTablosu})</td><td class="highlight">${r.bakiyeOmur.toFixed(2)} yıl</td></tr>
        <tr><td>Muhtemel Ölüm Yaşı</td><td>${r.muhtemelOlumYasi.toFixed(0)} yaş</td></tr>
        <tr><td>Aktif Dönem</td><td>${r.aktifDonemYil} yıl (${r.kazaAnindakiYas} - ${r.emeklilikYasi} yaş)</td></tr>
        <tr><td>Pasif Dönem</td><td>${r.pasifDonemYil.toFixed(0)} yıl (${r.emeklilikYasi} - ${r.muhtemelOlumYasi.toFixed(0)} yaş)</td></tr>
        <tr><td>Aktif PDK (%${r.teknikFaiz})</td><td>${r.aktifPDK.toFixed(4)}</td></tr>
        <tr><td>Pasif PDK (%${r.teknikFaiz})</td><td>${r.pasifPDK.toFixed(4)}</td></tr>
        <tr><td>Maluliyet Oranı</td><td class="highlight">%${r.maluliyetOrani}</td></tr>
        <tr><td>Kusur Oranı</td><td>%${r.kusurOrani}</td></tr>
    `;
    
    // Analiz raporu
    document.getElementById('analizRapor').innerHTML = `
        <strong>${r.adSoyad}</strong> (${r.cinsiyet}, ${r.kazaAnindakiYas} yaş) için tazminat hesabı:<br><br>
        📊 <strong>Maluliyet:</strong> %${r.maluliyetOrani}<br>
        💰 <strong>Aylık Gelir:</strong> ${formatMoney(r.aylikGelir)}<br>
        📋 <strong>PMF Tablosu:</strong> ${r.pmfTablosu} → Bakiye Ömür: ${r.bakiyeOmur.toFixed(2)} yıl<br><br>
        
        <strong>💼 AKTİF DÖNEM (${r.aktifDonemYil} yıl):</strong><br>
        ${formatMoney(r.yillikGelir)} × ${r.aktifPDK.toFixed(4)} × %${r.maluliyetOrani} = <span style="color:#10b981">${formatMoney(r.aktifDonemKaybi)}</span><br><br>
        
        <strong>🏖️ PASİF DÖNEM (${r.pasifDonemYil.toFixed(0)} yıl):</strong><br>
        ${formatMoney(ASGARI_UCRET * 12)} × ${r.pasifPDK.toFixed(4)} × %${r.maluliyetOrani} = <span style="color:#10b981">${formatMoney(r.pasifDonemKaybi)}</span><br><br>
        
        ${r.geciciGun > 0 ? `<strong>🏥 GEÇİCİ İŞ GÖREMEZLİK (${r.geciciGun} gün):</strong> ${formatMoney(r.geciciIsGoremezlik)}<br><br>` : ''}
        ${r.bakiciIhtiyaci > 0 ? `<strong>👨‍⚕️ BAKICI GİDERİ (%${r.bakiciIhtiyaci}):</strong> ${formatMoney(r.bakiciGideriToplam)}<br><br>` : ''}
        
        <strong>📊 TOPLAM:</strong> <span style="color:#f59e0b;font-size:14px">${formatMoney(r.toplamTazminat)}</span><br>
        ${r.kusurOrani > 0 ? `<strong>⚖️ KUSUR SONRASI (%${r.hakliOran}):</strong> <span style="color:#ef4444;font-size:14px">${formatMoney(r.kusurSonrasiTazminat)}</span>` : ''}
    `;
}

// TEMİZLE
function temizle() {
    document.getElementById('adSoyad').value = '';
    document.getElementById('dogumTarihi').value = '';
    document.getElementById('cinsiyet').value = '';
    document.getElementById('kazaTarihi').value = '';
    document.getElementById('meslek').value = '';
    document.getElementById('aylikGelir').value = '';
    document.getElementById('maluliyetOrani').value = '';
    document.getElementById('kusurOrani').value = '0';
    document.getElementById('pmfTablosu').value = 'TRH2010';
    document.getElementById('teknikFaiz').value = '1.8';
    document.getElementById('emeklilikYasi').value = '65';
    document.getElementById('geciciGun').value = '0';
    document.getElementById('bakiciIhtiyaci').value = '0';
    document.getElementById('dosya_id').value = '';
    
    document.getElementById('resultPlaceholder').style.display = 'block';
    document.getElementById('resultDisplay').classList.remove('active');
    document.getElementById('btnPDF').style.display = 'none';
    
    calculationResult = null;
}

// PDF EXPORT
async function exportPDF() {
    if (!calculationResult) {
        alert('Önce hesaplama yapın!');
        return;
    }
    
    document.getElementById('loadingOverlay').classList.add('active');
    document.getElementById('loadingText').textContent = '📄 PDF Oluşturuluyor...';
    
    const r = calculationResult;
    const pdfContainer = document.getElementById('pdfContainer');
    
    pdfContainer.innerHTML = `
        <div style="text-align:center;margin-bottom:30px;border-bottom:3px solid #10b981;padding-bottom:20px">
            <h1 style="color:#1e293b;font-size:24px;margin-bottom:5px">SAY HASAR DANIŞMANLIK</h1>
            <p style="color:#64748b;font-size:12px">MALULİYET TAZMİNAT RAPORU • ${new Date().toLocaleDateString('tr-TR')}</p>
        </div>
        
        <div style="margin-bottom:25px">
            <h2 style="color:#10b981;font-size:16px;margin-bottom:15px;border-left:4px solid #10b981;padding-left:10px">👤 KİŞİ BİLGİLERİ</h2>
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5;width:25%"><strong>Ad Soyad</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5">${r.adSoyad}</td>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5;width:25%"><strong>Cinsiyet</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5">${r.cinsiyet}</td>
                </tr>
                <tr>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5"><strong>Doğum Tarihi</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5">${new Date(r.dogumTarihi).toLocaleDateString('tr-TR')}</td>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5"><strong>Kaza Tarihi</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5">${new Date(r.kazaTarihi).toLocaleDateString('tr-TR')}</td>
                </tr>
                <tr>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5"><strong>Kaza Yaşı</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5">${r.kazaAnindakiYas} yaş</td>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5"><strong>Aylık Gelir</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5">${formatMoney(r.aylikGelir)}</td>
                </tr>
                <tr>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5"><strong>Maluliyet</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5;color:#ef4444;font-weight:bold">%${r.maluliyetOrani}</td>
                    <td style="padding:10px;background:#f0fdf4;border:1px solid #d1fae5"><strong>Kusur</strong></td>
                    <td style="padding:10px;border:1px solid #d1fae5">%${r.kusurOrani}</td>
                </tr>
            </table>
        </div>
        
        <div style="margin-bottom:25px">
            <h2 style="color:#06b6d4;font-size:16px;margin-bottom:15px;border-left:4px solid #06b6d4;padding-left:10px">📊 PMF & HESAPLAMA PARAMETRELERİ</h2>
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="padding:10px;background:#ecfeff;border:1px solid #a5f3fc"><strong>PMF Tablosu</strong></td>
                    <td style="padding:10px;border:1px solid #a5f3fc">${r.pmfTablosu}</td>
                    <td style="padding:10px;background:#ecfeff;border:1px solid #a5f3fc"><strong>Teknik Faiz</strong></td>
                    <td style="padding:10px;border:1px solid #a5f3fc">%${r.teknikFaiz}</td>
                </tr>
                <tr>
                    <td style="padding:10px;background:#ecfeff;border:1px solid #a5f3fc"><strong>Bakiye Ömür</strong></td>
                    <td style="padding:10px;border:1px solid #a5f3fc">${r.bakiyeOmur.toFixed(2)} yıl</td>
                    <td style="padding:10px;background:#ecfeff;border:1px solid #a5f3fc"><strong>Emeklilik Yaşı</strong></td>
                    <td style="padding:10px;border:1px solid #a5f3fc">${r.emeklilikYasi} yaş</td>
                </tr>
                <tr>
                    <td style="padding:10px;background:#ecfeff;border:1px solid #a5f3fc"><strong>Aktif Dönem</strong></td>
                    <td style="padding:10px;border:1px solid #a5f3fc">${r.aktifDonemYil} yıl</td>
                    <td style="padding:10px;background:#ecfeff;border:1px solid #a5f3fc"><strong>Pasif Dönem</strong></td>
                    <td style="padding:10px;border:1px solid #a5f3fc">${r.pasifDonemYil.toFixed(0)} yıl</td>
                </tr>
            </table>
        </div>
        
        <div style="margin-bottom:25px">
            <h2 style="color:#f59e0b;font-size:16px;margin-bottom:15px;border-left:4px solid #f59e0b;padding-left:10px">💰 TAZMİNAT HESABI</h2>
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="padding:12px;background:#fef3c7;border:1px solid #fcd34d"><strong>Aktif Dönem Kaybı</strong></td>
                    <td style="padding:12px;border:1px solid #fcd34d;text-align:right;font-weight:bold">${formatMoney(r.aktifDonemKaybi)}</td>
                </tr>
                <tr>
                    <td style="padding:12px;background:#fef3c7;border:1px solid #fcd34d"><strong>Pasif Dönem Kaybı</strong></td>
                    <td style="padding:12px;border:1px solid #fcd34d;text-align:right;font-weight:bold">${formatMoney(r.pasifDonemKaybi)}</td>
                </tr>
                ${r.geciciGun > 0 ? `
                <tr>
                    <td style="padding:12px;background:#fef3c7;border:1px solid #fcd34d"><strong>Geçici İş Göremezlik (${r.geciciGun} gün)</strong></td>
                    <td style="padding:12px;border:1px solid #fcd34d;text-align:right;font-weight:bold">${formatMoney(r.geciciIsGoremezlik)}</td>
                </tr>
                ` : ''}
                ${r.bakiciIhtiyaci > 0 ? `
                <tr>
                    <td style="padding:12px;background:#fef3c7;border:1px solid #fcd34d"><strong>Bakıcı Gideri (%${r.bakiciIhtiyaci})</strong></td>
                    <td style="padding:12px;border:1px solid #fcd34d;text-align:right;font-weight:bold">${formatMoney(r.bakiciGideriToplam)}</td>
                </tr>
                ` : ''}
                <tr>
                    <td style="padding:15px;background:#10b981;color:#fff;font-size:14px"><strong>TOPLAM TAZMİNAT</strong></td>
                    <td style="padding:15px;background:#10b981;color:#fff;text-align:right;font-size:18px;font-weight:bold">${formatMoney(r.toplamTazminat)}</td>
                </tr>
                ${r.kusurOrani > 0 ? `
                <tr>
                    <td style="padding:15px;background:#f59e0b;color:#fff;font-size:14px"><strong>KUSUR SONRASI (%${r.hakliOran})</strong></td>
                    <td style="padding:15px;background:#f59e0b;color:#fff;text-align:right;font-size:18px;font-weight:bold">${formatMoney(r.kusurSonrasiTazminat)}</td>
                </tr>
                ` : ''}
            </table>
        </div>
        
        <div style="text-align:center;margin-top:40px;padding-top:20px;border-top:2px solid #e2e8f0">
            <p style="color:#64748b;font-size:10px">SAY HASAR DANIŞMANLIK © ${new Date().getFullYear()}</p>
            <p style="color:#f59e0b;font-size:11px;font-weight:700;margin-top:5px">MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER</p>
        </div>
    `;
    
    try {
        const { jsPDF } = window.jspdf;
        const canvas = await html2canvas(pdfContainer, { scale: 2, useCORS: true });
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgWidth = 210;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, Math.min(imgHeight, 297));
        pdf.save(`SAY_Maluliyet_${r.adSoyad.replace(/\s/g, '_')}_${Date.now()}.pdf`);
        
        document.getElementById('loadingOverlay').classList.remove('active');
        alert('✓ PDF başarıyla indirildi!');
    } catch (err) {
        document.getElementById('loadingOverlay').classList.remove('active');
        alert('PDF oluşturma hatası: ' + err.message);
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>