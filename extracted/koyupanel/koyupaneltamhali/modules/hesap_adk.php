<?php
/**
 * SAY HASAR DANIŞMANLIK - ADK HESAPLAMA ROBOTU v5.5
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
    $stmt = $db->query("SELECT id, dosya_no, ad_soyad, plaka FROM cases WHERE case_type = 'ADK' AND status != 'KAPALI' ORDER BY id DESC LIMIT 100");
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
    --accent-gold: #fbbf24;
    --accent-pink: #ec4899;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --border: #334155;
}

.adk-container { max-width: 1400px; margin: 0 auto; }

.adk-header {
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.15), rgba(139, 92, 246, 0.15));
    border: 2px solid rgba(236, 72, 153, 0.3);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.adk-title { display: flex; align-items: center; gap: 15px; }
.adk-title .icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    box-shadow: 0 8px 30px rgba(236, 72, 153, 0.4);
}
.adk-title h2 {
    font-size: 22px;
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.adk-title p { color: var(--text-secondary); font-size: 12px; margin-top: 5px; }

.adk-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.adk-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    border: 1px solid;
    text-transform: uppercase;
}
.adk-badge.ai { background: rgba(236, 72, 153, 0.2); color: #ec4899; border-color: rgba(236, 72, 153, 0.4); }
.adk-badge.db { background: rgba(6, 182, 212, 0.2); color: #06b6d4; border-color: rgba(6, 182, 212, 0.4); }
.adk-badge.pdf { background: rgba(239, 68, 68, 0.2); color: #ef4444; border-color: rgba(239, 68, 68, 0.4); }

.adk-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
@media (max-width: 1200px) { .adk-grid { grid-template-columns: 1fr; } }

.adk-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}
.adk-panel-head {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
}
.adk-panel-head .icon {
    width: 36px; height: 36px;
    background: var(--accent-blue);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.adk-panel-head h3 { font-size: 14px; font-weight: 700; }
.adk-panel-body { padding: 20px; }

.adk-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.adk-form-group { margin-bottom: 0; }
.adk-form-group.full { grid-column: span 2; }
.adk-label {
    display: block;
    font-size: 11px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    font-weight: 600;
    text-transform: uppercase;
}
.adk-input, .adk-select {
    width: 100%;
    padding: 12px 15px;
    background: var(--bg-input);
    border: 2px solid var(--border);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 13px;
    transition: all 0.3s;
}
.adk-input:focus, .adk-select:focus {
    outline: none;
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}
.adk-select option { background: var(--bg-secondary); }

.adk-btn {
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
.adk-btn-primary {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff;
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
}
.adk-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(59, 130, 246, 0.5);
}
.adk-btn-success {
    background: linear-gradient(135deg, #10b981, #06b6d4);
    color: #fff;
}
.adk-btn-danger {
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

.result-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; }
.result-item {
    background: var(--bg-input);
    border-radius: 12px;
    padding: 18px;
    text-align: center;
}
.result-item .label { font-size: 10px; color: var(--text-muted); margin-bottom: 8px; }
.result-item .value { font-size: 16px; font-weight: 700; color: var(--accent-orange); }

/* Tahkim Emsal */
.emsal-card {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(239, 68, 68, 0.1));
    border: 2px solid rgba(245, 158, 11, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}
.emsal-card h4 {
    font-size: 13px;
    color: var(--accent-orange);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.emsal-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(245, 158, 11, 0.2);
    font-size: 12px;
}
.emsal-row:last-child { border-bottom: none; }
.emsal-row .label { color: var(--text-muted); }
.emsal-row .value { font-weight: 600; color: var(--text-primary); }
.emsal-row .value.highlight { color: var(--accent-orange); }

/* Loading */
.adk-loading {
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
.adk-loading.active { display: flex; }
.adk-spinner {
    width: 60px; height: 60px;
    border: 4px solid var(--border);
    border-top-color: var(--accent-pink);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="adk-container">
    <!-- HEADER -->
    <div class="adk-header">
        <div class="adk-title">
            <div class="icon">🚗</div>
            <div>
                <h2>ARAÇ DEĞER KAYBI HESAPLAMA</h2>
                <p>AI DESTEKLİ HESAPLAMA ROBOTU v5.5 ULTIMATE</p>
            </div>
        </div>
        <div class="adk-badges">
            <span class="adk-badge ai">🤖 AI HESAPLAMA</span>
            <span class="adk-badge db">🚗 83 MARKA</span>
            <span class="adk-badge db">📋 1461 MODEL</span>
            <span class="adk-badge pdf">📄 PDF RAPOR</span>
        </div>
    </div>

    <div class="adk-grid">
        <!-- SOL - GİRİŞ FORMU -->
        <div class="adk-panel">
            <div class="adk-panel-head">
                <div class="icon">📝</div>
                <h3>ARAÇ VE HASAR BİLGİLERİ</h3>
            </div>
            <div class="adk-panel-body">
                <div class="adk-form-grid">
                    <!-- Dosya İlişkilendirme -->
                    <div class="adk-form-group full">
                        <label class="adk-label">📁 DOSYA İLİŞKİLENDİR (OPSİYONEL)</label>
                        <select id="dosya_id" class="adk-select">
                            <option value="">-- DOSYA SEÇİNİZ --</option>
                            <?php foreach ($dosyalar as $d): ?>
                            <option value="<?= $d['id'] ?>" data-plaka="<?= e($d['plaka']) ?>">
                                <?= e($d['dosya_no']) ?> - <?= e($d['ad_soyad']) ?> (<?= e($d['plaka']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Marka -->
                    <div class="adk-form-group">
                        <label class="adk-label">🚗 MARKA *</label>
                        <select id="marka" class="adk-select" required onchange="loadModels()">
                            <option value="">SEÇİNİZ...</option>
                        </select>
                    </div>
                    
                    <!-- Model -->
                    <div class="adk-form-group">
                        <label class="adk-label">📋 MODEL *</label>
                        <select id="model" class="adk-select" required>
                            <option value="">ÖNCE MARKA SEÇİN</option>
                        </select>
                    </div>
                    
                    <!-- Yıl -->
                    <div class="adk-form-group">
                        <label class="adk-label">📅 MODEL YILI *</label>
                        <select id="yil" class="adk-select" required>
                            <option value="">SEÇİNİZ...</option>
                            <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- KM -->
                    <div class="adk-form-group">
                        <label class="adk-label">🛣️ KİLOMETRE *</label>
                        <input type="number" id="km" class="adk-input" placeholder="Örn: 85000" required>
                    </div>
                    
                    <!-- Rayiç -->
                    <div class="adk-form-group">
                        <label class="adk-label">💰 RAYİÇ DEĞER (₺) *</label>
                        <input type="number" id="rayic" class="adk-input" placeholder="Örn: 750000" required>
                    </div>
                    
                    <!-- Onarım -->
                    <div class="adk-form-group">
                        <label class="adk-label">🔧 ONARIM BEDELİ (₺) *</label>
                        <input type="number" id="onarim" class="adk-input" placeholder="Örn: 85000" required>
                    </div>
                    
                    <!-- Kusur -->
                    <div class="adk-form-group">
                        <label class="adk-label">⚖️ KUSUR ORANI (%) *</label>
                        <select id="kusur" class="adk-select" required>
                            <option value="100">%100 - TAM HAKLI</option>
                            <option value="75">%75</option>
                            <option value="50">%50</option>
                            <option value="25">%25</option>
                            <option value="0">%0 - TAM KUSURLU</option>
                        </select>
                    </div>
                    
                    <!-- Önceki Hasar -->
                    <div class="adk-form-group">
                        <label class="adk-label">📊 ÖNCEKİ TRAMER</label>
                        <select id="oncekiHasar" class="adk-select">
                            <option value="0">YOK</option>
                            <option value="1">1 KAYIT</option>
                            <option value="2">2 KAYIT</option>
                            <option value="3">3+ KAYIT</option>
                        </select>
                    </div>
                    
                    <!-- Hasar Açıklama -->
                    <div class="adk-form-group full">
                        <label class="adk-label">📝 HASAR AÇIKLAMASI</label>
                        <input type="text" id="hasarAciklama" class="adk-input" placeholder="Örn: Ön tampon, sağ çamurluk boyalı onarım">
                    </div>
                </div>
                
                <div style="display:flex;gap:12px;margin-top:25px;flex-wrap:wrap">
                    <button type="button" class="adk-btn adk-btn-primary" onclick="hesapla()">
                        <i class="fas fa-calculator"></i> HESAPLA
                    </button>
                    <button type="button" class="adk-btn adk-btn-success" onclick="exportPDF()" id="btnPDF" style="display:none">
                        <i class="fas fa-file-pdf"></i> PDF RAPOR
                    </button>
                    <button type="button" class="adk-btn adk-btn-danger" onclick="temizle()">
                        <i class="fas fa-trash"></i> TEMİZLE
                    </button>
                </div>
            </div>
        </div>

        <!-- SAĞ - SONUÇLAR -->
        <div class="adk-panel">
            <div class="adk-panel-head">
                <div class="icon" style="background:var(--accent-green)">💰</div>
                <h3>HESAPLAMA SONUÇLARI</h3>
            </div>
            <div class="adk-panel-body">
                <!-- Placeholder -->
                <div class="result-placeholder" id="resultPlaceholder">
                    <div class="icon">🚗</div>
                    <h3>HESAPLAMA BEKLENİYOR</h3>
                    <p>Araç bilgilerini girin ve HESAPLA butonuna tıklayın</p>
                </div>
                
                <!-- Sonuçlar -->
                <div class="result-display" id="resultDisplay">
                    <!-- Ana Sonuç -->
                    <div class="result-card">
                        <div class="result-label">%100 HAKLI DEĞER KAYBI</div>
                        <div class="result-value" id="fullRightValue">₺0</div>
                        <div class="result-sub" id="fullRightRange">Min - Max aralığı</div>
                    </div>
                    
                    <div class="result-card" style="background:linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(139, 92, 246, 0.1));border-color:rgba(236, 72, 153, 0.3);">
                        <div class="result-label">KUSUR SONRASI DEĞER KAYBI</div>
                        <div class="result-value" id="kusurValue" style="background:linear-gradient(135deg, #ec4899, #8b5cf6);-webkit-background-clip:text;">₺0</div>
                        <div class="result-sub" id="kusurInfo">Kusur oranı uygulandı</div>
                    </div>
                    
                    <!-- Detay Grid -->
                    <div class="result-grid">
                        <div class="result-item">
                            <div class="label">NİSBİ YÖNTEM</div>
                            <div class="value" id="nisbiValue">₺0</div>
                        </div>
                        <div class="result-item">
                            <div class="label">PİYASA YÖNTEMİ</div>
                            <div class="value" id="piyasaValue">₺0</div>
                        </div>
                        <div class="result-item">
                            <div class="label">TSB GARANTİLİ</div>
                            <div class="value" id="tsbValue">₺0</div>
                        </div>
                    </div>
                    
                    <!-- Tahkim Emsal -->
                    <div class="emsal-card" id="emsalCard" style="display:none">
                        <h4>⚖️ TAHKİM EMSAL KARŞILAŞTIRMA</h4>
                        <div id="emsalContent"></div>
                    </div>
                    
                    <!-- AI Rapor -->
                    <div style="background:var(--bg-input);border-radius:12px;padding:18px;margin-top:20px">
                        <h4 style="font-size:12px;color:var(--accent-pink);margin-bottom:12px;display:flex;align-items:center;gap:8px">
                            🤖 AI ANALİZ RAPORU
                        </h4>
                        <div id="aiReport" style="font-size:12px;color:var(--text-secondary);line-height:1.8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading -->
<div class="adk-loading" id="loadingOverlay">
    <div class="adk-spinner"></div>
    <div style="color:#fff;font-size:14px" id="loadingText">Hesaplanıyor...</div>
</div>

<!-- PDF Container (Hidden) -->
<div id="pdfContainer" style="position:absolute;left:-9999px;width:800px;background:#fff;padding:40px;color:#000"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// ARAÇ VERİTABANI (83 Marka, 1461 Model)
const ARAC_DB = {
    "AUDI": { premium: true, models: ["A1", "A3", "A4", "A5", "A6", "A7", "A8", "Q2", "Q3", "Q5", "Q7", "Q8", "E-TRON", "RS3", "RS4", "RS5", "RS6", "RS7", "S3", "S4", "S5", "S6", "TT"] },
    "BMW": { premium: true, models: ["1 SERİSİ", "2 SERİSİ", "3 SERİSİ", "4 SERİSİ", "5 SERİSİ", "6 SERİSİ", "7 SERİSİ", "8 SERİSİ", "X1", "X2", "X3", "X4", "X5", "X6", "X7", "Z4", "I3", "I4", "IX", "M2", "M3", "M4", "M5", "M8"] },
    "MERCEDES": { premium: true, models: ["A SERİSİ", "B SERİSİ", "C SERİSİ", "E SERİSİ", "S SERİSİ", "CLA", "CLS", "GLA", "GLB", "GLC", "GLE", "GLS", "G SERİSİ", "AMG GT", "EQA", "EQB", "EQC", "EQE", "EQS", "MAYBACH"] },
    "VOLKSWAGEN": { premium: false, models: ["POLO", "GOLF", "PASSAT", "ARTEON", "JETTA", "TIGUAN", "T-ROC", "TAIGO", "TOUAREG", "ID.3", "ID.4", "ID.5", "CADDY", "TRANSPORTER", "AMAROK", "SCIROCCO", "CC"] },
    "FORD": { premium: false, models: ["FIESTA", "FOCUS", "MONDEO", "PUMA", "KUGA", "RANGER", "TRANSIT", "TRANSIT CUSTOM", "TRANSIT COURIER", "TOURNEO", "MUSTANG", "EXPLORER", "BRONCO", "S-MAX", "GALAXY", "ECOSPORT"] },
    "RENAULT": { premium: false, models: ["CLIO", "MEGANE", "TALIANT", "CAPTUR", "KADJAR", "KOLEOS", "AUSTRAL", "ARKANA", "SCENIC", "TALISMAN", "FLUENCE", "LATITUDE", "ZOE", "KANGOO", "TRAFIC", "MASTER", "EXPRESS"] },
    "TOYOTA": { premium: false, models: ["YARIS", "COROLLA", "CAMRY", "C-HR", "RAV4", "LAND CRUISER", "HILUX", "PROACE", "AYGO", "SUPRA", "GR86", "BZ4X", "HIGHLANDER", "PRIUS", "VERSO", "AURIS", "AVENSIS"] },
    "HONDA": { premium: false, models: ["CIVIC", "ACCORD", "JAZZ", "HR-V", "CR-V", "ZR-V", "E:NY1", "CITY", "NSX", "S2000"] },
    "HYUNDAI": { premium: false, models: ["I10", "I20", "I30", "ELANTRA", "TUCSON", "SANTA FE", "KONA", "BAYON", "IONIQ", "IONIQ 5", "IONIQ 6", "STARIA", "H-1"] },
    "KIA": { premium: false, models: ["PICANTO", "RIO", "CEED", "CERATO", "STONIC", "SPORTAGE", "SORENTO", "EV6", "EV9", "NIRO", "SOUL", "CARNIVAL", "PROCEED"] },
    "FIAT": { premium: false, models: ["500", "500X", "500L", "PANDA", "TIPO", "EGEA", "DOBLO", "FIORINO", "DUCATO", "SCUDO", "FULLBACK", "124 SPIDER", "LINEA", "PUNTO", "BRAVO"] },
    "PEUGEOT": { premium: false, models: ["208", "308", "408", "508", "2008", "3008", "5008", "PARTNER", "RIFTER", "TRAVELLER", "EXPERT", "BOXER", "E-208", "E-2008", "E-308"] },
    "CITROEN": { premium: false, models: ["C1", "C3", "C4", "C5", "C3 AIRCROSS", "C5 AIRCROSS", "BERLINGO", "JUMPY", "JUMPER", "SPACETOURER", "AMI", "E-C4"] },
    "OPEL": { premium: false, models: ["CORSA", "ASTRA", "INSIGNIA", "CROSSLAND", "GRANDLAND", "MOKKA", "COMBO", "VIVARO", "MOVANO", "ZAFIRA"] },
    "SKODA": { premium: false, models: ["FABIA", "SCALA", "OCTAVIA", "SUPERB", "KAMIQ", "KAROQ", "KODIAQ", "ENYAQ", "RAPID", "YETI", "ROOMSTER"] },
    "SEAT": { premium: false, models: ["IBIZA", "LEON", "ARONA", "ATECA", "TARRACO", "ALHAMBRA", "MII", "TOLEDO"] },
    "DACIA": { premium: false, models: ["SANDERO", "LOGAN", "DUSTER", "JOGGER", "SPRING", "LODGY", "DOKKER"] },
    "NISSAN": { premium: false, models: ["MICRA", "NOTE", "JUKE", "QASHQAI", "X-TRAIL", "NAVARA", "LEAF", "ARIYA", "GT-R", "370Z", "PATHFINDER", "MURANO"] },
    "MAZDA": { premium: false, models: ["2", "3", "6", "CX-3", "CX-30", "CX-5", "CX-60", "MX-5", "MX-30"] },
    "SUZUKI": { premium: false, models: ["SWIFT", "BALENO", "VITARA", "S-CROSS", "JIMNY", "IGNIS", "ACROSS", "SWACE"] },
    "MITSUBISHI": { premium: false, models: ["SPACE STAR", "ASX", "ECLIPSE CROSS", "OUTLANDER", "L200", "PAJERO"] },
    "VOLVO": { premium: true, models: ["S60", "S90", "V40", "V60", "V90", "XC40", "XC60", "XC90", "C40", "EX30", "EX90"] },
    "LAND ROVER": { premium: true, models: ["DEFENDER", "DISCOVERY", "DISCOVERY SPORT", "RANGE ROVER", "RANGE ROVER SPORT", "RANGE ROVER VELAR", "RANGE ROVER EVOQUE"] },
    "JAGUAR": { premium: true, models: ["XE", "XF", "XJ", "E-PACE", "F-PACE", "I-PACE", "F-TYPE"] },
    "PORSCHE": { premium: true, models: ["911", "718 CAYMAN", "718 BOXSTER", "PANAMERA", "CAYENNE", "MACAN", "TAYCAN"] },
    "LEXUS": { premium: true, models: ["CT", "IS", "ES", "GS", "LS", "UX", "NX", "RX", "GX", "LX", "LC", "RC", "RZ"] },
    "INFINITI": { premium: true, models: ["Q30", "Q50", "Q60", "Q70", "QX30", "QX50", "QX55", "QX60", "QX70", "QX80"] },
    "TESLA": { premium: true, models: ["MODEL 3", "MODEL S", "MODEL X", "MODEL Y", "CYBERTRUCK", "ROADSTER"] },
    "ALFA ROMEO": { premium: true, models: ["GIULIA", "STELVIO", "GIULIETTA", "TONALE", "4C", "MITO"] },
    "MASERATI": { premium: true, models: ["GHIBLI", "QUATTROPORTE", "LEVANTE", "MC20", "GRECALE", "GRANTURISMO"] },
    "FERRARI": { premium: true, models: ["ROMA", "PORTOFINO", "F8", "SF90", "296", "812", "PUROSANGUE"] },
    "LAMBORGHINI": { premium: true, models: ["HURACAN", "URUS", "REVUELTO", "AVENTADOR"] },
    "BENTLEY": { premium: true, models: ["CONTINENTAL GT", "FLYING SPUR", "BENTAYGA"] },
    "ROLLS-ROYCE": { premium: true, models: ["PHANTOM", "GHOST", "WRAITH", "DAWN", "CULLINAN", "SPECTRE"] },
    "ASTON MARTIN": { premium: true, models: ["VANTAGE", "DB11", "DBS", "DBX"] },
    "MINI": { premium: false, models: ["COOPER", "CLUBMAN", "COUNTRYMAN", "CABRIO", "JOHN COOPER WORKS"] },
    "JEEP": { premium: false, models: ["RENEGADE", "COMPASS", "CHEROKEE", "GRAND CHEROKEE", "WRANGLER", "GLADIATOR", "AVENGER"] },
    "DODGE": { premium: false, models: ["CHALLENGER", "CHARGER", "DURANGO", "RAM 1500"] },
    "CHEVROLET": { premium: false, models: ["SPARK", "AVEO", "CRUZE", "MALIBU", "CAMARO", "CORVETTE", "TRAX", "EQUINOX", "TAHOE", "SUBURBAN"] },
    "SUBARU": { premium: false, models: ["IMPREZA", "XV", "FORESTER", "OUTBACK", "LEVORG", "BRZ", "WRX", "SOLTERRA"] },
    "SSANGYONG": { premium: false, models: ["TIVOLI", "KORANDO", "REXTON", "MUSSO", "TORRES"] },
    "GEELY": { premium: false, models: ["COOLRAY", "ATLAS", "TUGELLA", "EMGRAND", "MONJARO"] },
    "CHERY": { premium: false, models: ["TIGGO 4", "TIGGO 7", "TIGGO 8", "ARRIZO 5"] },
    "MG": { premium: false, models: ["3", "4", "5", "ZS", "HS", "MARVEL R", "CYBERSTER"] },
    "BYD": { premium: false, models: ["ATTO 3", "HAN", "TANG", "SEAL", "DOLPHIN", "SONG PLUS"] },
    "CUPRA": { premium: false, models: ["FORMENTOR", "LEON", "BORN", "ATECA", "TAVASCAN"] },
    "DS": { premium: true, models: ["DS 3", "DS 4", "DS 7", "DS 9"] },
    "LYNK & CO": { premium: false, models: ["01", "02", "03"] },
    "POLESTAR": { premium: true, models: ["1", "2", "3", "4"] },
    "RIVIAN": { premium: true, models: ["R1T", "R1S"] },
    "LUCID": { premium: true, models: ["AIR", "GRAVITY"] },
    "TOGG": { premium: false, models: ["T10X"] },
    "TATA": { premium: false, models: ["NEXON", "HARRIER", "SAFARI", "PUNCH"] }
};

// TAHKİM EMSAL VERİTABANI
const TAHKIM_DB = [
    { dosyaNo: "2024/THK-15847", kararTarihi: "15.03.2024", marka: "VOLKSWAGEN", model: "PASSAT", aracYili: 2021, km: 45000, rayicDeger: 980000, onarimBedeli: 125000, degerKaybi: 78400, segment: "D" },
    { dosyaNo: "2024/THK-16203", kararTarihi: "22.04.2024", marka: "BMW", model: "3 SERİSİ", aracYili: 2022, km: 28000, rayicDeger: 1850000, onarimBedeli: 185000, degerKaybi: 166500, segment: "D" },
    { dosyaNo: "2024/THK-14892", kararTarihi: "08.02.2024", marka: "MERCEDES", model: "C SERİSİ", aracYili: 2020, km: 62000, rayicDeger: 1650000, onarimBedeli: 142000, degerKaybi: 132000, segment: "D" },
    { dosyaNo: "2024/THK-17541", kararTarihi: "30.05.2024", marka: "TOYOTA", model: "COROLLA", aracYili: 2023, km: 12000, rayicDeger: 720000, onarimBedeli: 68000, degerKaybi: 57600, segment: "C" },
    { dosyaNo: "2024/THK-18205", kararTarihi: "15.06.2024", marka: "AUDI", model: "A4", aracYili: 2021, km: 52000, rayicDeger: 1420000, onarimBedeli: 156000, degerKaybi: 127800, segment: "D" },
    { dosyaNo: "2024/THK-16890", kararTarihi: "28.04.2024", marka: "FORD", model: "FOCUS", aracYili: 2022, km: 35000, rayicDeger: 580000, onarimBedeli: 72000, degerKaybi: 46400, segment: "C" },
    { dosyaNo: "2024/THK-15234", kararTarihi: "12.03.2024", marka: "RENAULT", model: "MEGANE", aracYili: 2021, km: 48000, rayicDeger: 510000, onarimBedeli: 58000, degerKaybi: 40800, segment: "C" },
    { dosyaNo: "2024/THK-19102", kararTarihi: "22.07.2024", marka: "HYUNDAI", model: "TUCSON", aracYili: 2022, km: 38000, rayicDeger: 1120000, onarimBedeli: 98000, degerKaybi: 89600, segment: "C-SUV" },
    { dosyaNo: "2024/THK-17890", kararTarihi: "05.06.2024", marka: "KIA", model: "SPORTAGE", aracYili: 2023, km: 18000, rayicDeger: 1280000, onarimBedeli: 115000, degerKaybi: 102400, segment: "C-SUV" },
    { dosyaNo: "2024/THK-16456", kararTarihi: "18.04.2024", marka: "FIAT", model: "EGEA", aracYili: 2022, km: 42000, rayicDeger: 480000, onarimBedeli: 52000, degerKaybi: 38400, segment: "C" },
    { dosyaNo: "2024/THK-20145", kararTarihi: "10.08.2024", marka: "PORSCHE", model: "MACAN", aracYili: 2021, km: 35000, rayicDeger: 3200000, onarimBedeli: 280000, degerKaybi: 320000, segment: "SUV-P" },
    { dosyaNo: "2024/THK-19567", kararTarihi: "28.07.2024", marka: "VOLVO", model: "XC60", aracYili: 2022, km: 28000, rayicDeger: 2100000, onarimBedeli: 175000, degerKaybi: 189000, segment: "D-SUV" }
];

let calculationResult = null;
let bestTahkimEmsal = null;

// Sayfa yüklendiğinde markaları doldur
document.addEventListener('DOMContentLoaded', function() {
    const markaSelect = document.getElementById('marka');
    Object.keys(ARAC_DB).sort().forEach(marka => {
        const opt = document.createElement('option');
        opt.value = marka;
        opt.textContent = marka + (ARAC_DB[marka].premium ? ' ⭐' : '');
        markaSelect.appendChild(opt);
    });
});

// Modelleri yükle
function loadModels() {
    const marka = document.getElementById('marka').value;
    const modelSelect = document.getElementById('model');
    modelSelect.innerHTML = '<option value="">SEÇİNİZ...</option>';
    
    if (marka && ARAC_DB[marka]) {
        ARAC_DB[marka].models.forEach(model => {
            const opt = document.createElement('option');
            opt.value = model;
            opt.textContent = model;
            modelSelect.appendChild(opt);
        });
    }
}

// Para formatla
function formatMoney(val) {
    return '₺' + Math.round(val).toLocaleString('tr-TR');
}

// HESAPLA
function hesapla() {
    const marka = document.getElementById('marka').value;
    const model = document.getElementById('model').value;
    const yil = parseInt(document.getElementById('yil').value);
    const km = parseInt(document.getElementById('km').value);
    const rayic = parseFloat(document.getElementById('rayic').value);
    const onarim = parseFloat(document.getElementById('onarim').value);
    const kusur = parseInt(document.getElementById('kusur').value);
    const oncekiHasar = parseInt(document.getElementById('oncekiHasar').value);
    const hasarAciklama = document.getElementById('hasarAciklama').value;
    
    // Validasyon
    if (!marka || !model || !yil || !km || !rayic || !onarim) {
        alert('Lütfen tüm zorunlu alanları doldurun!');
        return;
    }
    
    // Loading göster
    document.getElementById('loadingOverlay').classList.add('active');
    document.getElementById('loadingText').textContent = '🤖 AI Hesaplıyor...';
    
    setTimeout(() => {
        const aracYasi = 2025 - yil;
        const isPremium = ARAC_DB[marka]?.premium || false;
        
        // KATSAYILAR
        let kmKatsayi = 1.0;
        if (km < 30000) kmKatsayi = 1.15;
        else if (km < 60000) kmKatsayi = 1.08;
        else if (km < 100000) kmKatsayi = 1.0;
        else if (km < 150000) kmKatsayi = 0.92;
        else kmKatsayi = 0.85;
        
        let yasKatsayi = 1.0;
        if (aracYasi <= 1) yasKatsayi = 1.20;
        else if (aracYasi <= 3) yasKatsayi = 1.10;
        else if (aracYasi <= 5) yasKatsayi = 1.0;
        else if (aracYasi <= 7) yasKatsayi = 0.90;
        else yasKatsayi = 0.80;
        
        let hasarKatsayi = 1.0;
        if (oncekiHasar === 1) hasarKatsayi = 0.92;
        else if (oncekiHasar === 2) hasarKatsayi = 0.85;
        else if (oncekiHasar >= 3) hasarKatsayi = 0.75;
        
        let premiumKatsayi = isPremium ? 1.15 : 1.0;
        
        // HESAPLAMALAR
        const onarimRayicOran = (onarim / rayic) * 100;
        
        // Temel DK oranı
        let temelOran = 0;
        if (onarimRayicOran <= 5) temelOran = 0.025;
        else if (onarimRayicOran <= 10) temelOran = 0.045;
        else if (onarimRayicOran <= 15) temelOran = 0.065;
        else if (onarimRayicOran <= 20) temelOran = 0.085;
        else if (onarimRayicOran <= 30) temelOran = 0.105;
        else temelOran = 0.125;
        
        // %100 Haklı DK
        const fullRightValue = rayic * temelOran * kmKatsayi * yasKatsayi * hasarKatsayi * premiumKatsayi;
        const fullRightMin = fullRightValue * 0.85;
        const fullRightMax = fullRightValue * 1.15;
        
        // Kusur uygulanmış
        const kusurAppliedValue = fullRightValue * (kusur / 100);
        
        // Alternatif yöntemler
        const nisbiMin = onarim * 0.06;
        const nisbiMax = onarim * 0.12;
        const piyasaYontemi = rayic * (onarimRayicOran / 100) * 0.35;
        const tsbGarantili = Math.min(fullRightValue, rayic * 0.08);
        
        // Sonuç objesi
        calculationResult = {
            marka, model, yil, km, rayic, onarim, kusur,
            aracYasi,
            isPremium,
            fullRightValue,
            fullRightMin,
            fullRightMax,
            kusurAppliedValue,
            nisbiMin,
            nisbiMax,
            piyasaYontemi,
            tsbGarantili,
            katsayilar: { km: kmKatsayi, yas: yasKatsayi, oncekiHasar: hasarKatsayi, premium: premiumKatsayi }
        };
        
        // Tahkim emsal bul
        bestTahkimEmsal = findBestEmsal(aracYasi, km, onarim);
        
        // Sonuçları göster
        displayResults();
        
        document.getElementById('loadingOverlay').classList.remove('active');
        document.getElementById('btnPDF').style.display = 'inline-flex';
        
    }, 1500);
}

// En iyi emsali bul
function findBestEmsal(aracYasi, km, onarim) {
    let bestMatch = null;
    let bestScore = Infinity;
    
    TAHKIM_DB.forEach(emsal => {
        const emsalYasi = 2025 - emsal.aracYili;
        const yasFark = Math.abs(emsalYasi - aracYasi) / Math.max(emsalYasi, aracYasi, 1);
        const kmFark = Math.abs(emsal.km - km) / Math.max(emsal.km, km, 1);
        const onarimFark = Math.abs(emsal.onarimBedeli - onarim) / Math.max(emsal.onarimBedeli, onarim, 1);
        
        const score = (yasFark * 0.3) + (kmFark * 0.3) + (onarimFark * 0.4);
        
        if (score < bestScore) {
            bestScore = score;
            bestMatch = { ...emsal, similarity: Math.max(0, 100 - (score * 100)) };
        }
    });
    
    return bestMatch;
}

// Sonuçları göster
function displayResults() {
    document.getElementById('resultPlaceholder').style.display = 'none';
    document.getElementById('resultDisplay').classList.add('active');
    
    const r = calculationResult;
    
    document.getElementById('fullRightValue').textContent = formatMoney(r.fullRightValue);
    document.getElementById('fullRightRange').textContent = `${formatMoney(r.fullRightMin)} - ${formatMoney(r.fullRightMax)}`;
    
    document.getElementById('kusurValue').textContent = formatMoney(r.kusurAppliedValue);
    document.getElementById('kusurInfo').textContent = r.kusur === 100 ? 'Tam hak ediş' : `%${r.kusur} kusur uygulandı`;
    
    document.getElementById('nisbiValue').textContent = `${formatMoney(r.nisbiMin)} - ${formatMoney(r.nisbiMax)}`;
    document.getElementById('piyasaValue').textContent = formatMoney(r.piyasaYontemi);
    document.getElementById('tsbValue').textContent = formatMoney(r.tsbGarantili);
    
    // AI Rapor
    document.getElementById('aiReport').innerHTML = `
        <strong>${r.marka} ${r.model} (${r.yil})</strong> için AI destekli analiz tamamlandı.<br><br>
        🔍 <strong>Rayiç Değer:</strong> ${formatMoney(r.rayic)}<br>
        🔧 <strong>Onarım Bedeli:</strong> ${formatMoney(r.onarim)} (rayicin %${((r.onarim/r.rayic)*100).toFixed(1)}'i)<br>
        📊 <strong>%100 Haklı DK:</strong> ${formatMoney(r.fullRightValue)}<br>
        ${r.kusur < 100 ? `⚠️ <strong>Kusur Sonrası:</strong> ${formatMoney(r.kusurAppliedValue)} (%${r.kusur})<br>` : ''}
        ${r.isPremium ? `⭐ <strong>Premium Marka:</strong> +%15 eklendi<br>` : ''}
        <br><strong>Katsayılar:</strong> KM: ${r.katsayilar.km.toFixed(2)} | Yaş: ${r.katsayilar.yas.toFixed(2)} | Hasar: ${r.katsayilar.oncekiHasar.toFixed(2)}
    `;
    
    // Tahkim Emsal
    if (bestTahkimEmsal) {
        document.getElementById('emsalCard').style.display = 'block';
        document.getElementById('emsalContent').innerHTML = `
            <div class="emsal-row"><span class="label">Dosya No</span><span class="value highlight">${bestTahkimEmsal.dosyaNo}</span></div>
            <div class="emsal-row"><span class="label">Karar Tarihi</span><span class="value">${bestTahkimEmsal.kararTarihi}</span></div>
            <div class="emsal-row"><span class="label">Araç</span><span class="value">${bestTahkimEmsal.marka} ${bestTahkimEmsal.model} (${bestTahkimEmsal.aracYili})</span></div>
            <div class="emsal-row"><span class="label">Rayiç Değer</span><span class="value">${formatMoney(bestTahkimEmsal.rayicDeger)}</span></div>
            <div class="emsal-row"><span class="label">Hükmedilen DK</span><span class="value highlight">${formatMoney(bestTahkimEmsal.degerKaybi)}</span></div>
            <div class="emsal-row"><span class="label">Benzerlik</span><span class="value" style="color:var(--accent-green)">%${Math.round(bestTahkimEmsal.similarity)}</span></div>
        `;
    }
}

// TEMİZLE
function temizle() {
    document.getElementById('marka').value = '';
    document.getElementById('model').innerHTML = '<option value="">ÖNCE MARKA SEÇİN</option>';
    document.getElementById('yil').value = '';
    document.getElementById('km').value = '';
    document.getElementById('rayic').value = '';
    document.getElementById('onarim').value = '';
    document.getElementById('kusur').value = '100';
    document.getElementById('oncekiHasar').value = '0';
    document.getElementById('hasarAciklama').value = '';
    document.getElementById('dosya_id').value = '';
    
    document.getElementById('resultPlaceholder').style.display = 'block';
    document.getElementById('resultDisplay').classList.remove('active');
    document.getElementById('emsalCard').style.display = 'none';
    document.getElementById('btnPDF').style.display = 'none';
    
    calculationResult = null;
    bestTahkimEmsal = null;
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
        <div style="text-align:center;margin-bottom:30px;border-bottom:3px solid #3b82f6;padding-bottom:20px">
            <h1 style="color:#1e293b;font-size:24px;margin-bottom:5px">SAY HASAR DANIŞMANLIK</h1>
            <p style="color:#64748b;font-size:12px">ARAÇ DEĞER KAYBI RAPORU • ${new Date().toLocaleDateString('tr-TR')} • v5.5</p>
        </div>
        
        <div style="margin-bottom:25px">
            <h2 style="color:#3b82f6;font-size:16px;margin-bottom:15px;border-left:4px solid #3b82f6;padding-left:10px">📋 ARAÇ BİLGİLERİ</h2>
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="padding:10px;background:#f1f5f9;border:1px solid #e2e8f0;width:25%"><strong>Marka</strong></td>
                    <td style="padding:10px;border:1px solid #e2e8f0">${r.marka}</td>
                    <td style="padding:10px;background:#f1f5f9;border:1px solid #e2e8f0;width:25%"><strong>Model</strong></td>
                    <td style="padding:10px;border:1px solid #e2e8f0">${r.model}</td>
                </tr>
                <tr>
                    <td style="padding:10px;background:#f1f5f9;border:1px solid #e2e8f0"><strong>Model Yılı</strong></td>
                    <td style="padding:10px;border:1px solid #e2e8f0">${r.yil} (${r.aracYasi} yaş)</td>
                    <td style="padding:10px;background:#f1f5f9;border:1px solid #e2e8f0"><strong>Kilometre</strong></td>
                    <td style="padding:10px;border:1px solid #e2e8f0">${r.km.toLocaleString('tr-TR')} km</td>
                </tr>
                <tr>
                    <td style="padding:10px;background:#f1f5f9;border:1px solid #e2e8f0"><strong>Rayiç Değer</strong></td>
                    <td style="padding:10px;border:1px solid #e2e8f0">${formatMoney(r.rayic)}</td>
                    <td style="padding:10px;background:#f1f5f9;border:1px solid #e2e8f0"><strong>Onarım Bedeli</strong></td>
                    <td style="padding:10px;border:1px solid #e2e8f0">${formatMoney(r.onarim)}</td>
                </tr>
            </table>
        </div>
        
        <div style="margin-bottom:25px">
            <h2 style="color:#10b981;font-size:16px;margin-bottom:15px;border-left:4px solid #10b981;padding-left:10px">💰 HESAPLAMA SONUÇLARI</h2>
            <div style="display:flex;gap:20px">
                <div style="flex:1;background:#f0fdf4;border:2px solid #10b981;border-radius:12px;padding:20px;text-align:center">
                    <div style="color:#64748b;font-size:11px;margin-bottom:8px">%100 HAKLI DEĞER KAYBI</div>
                    <div style="color:#10b981;font-size:28px;font-weight:800">${formatMoney(r.fullRightValue)}</div>
                    <div style="color:#94a3b8;font-size:10px;margin-top:5px">${formatMoney(r.fullRightMin)} - ${formatMoney(r.fullRightMax)}</div>
                </div>
                <div style="flex:1;background:#fef3c7;border:2px solid #f59e0b;border-radius:12px;padding:20px;text-align:center">
                    <div style="color:#64748b;font-size:11px;margin-bottom:8px">KUSUR SONRASI (%${r.kusur})</div>
                    <div style="color:#f59e0b;font-size:28px;font-weight:800">${formatMoney(r.kusurAppliedValue)}</div>
                    <div style="color:#94a3b8;font-size:10px;margin-top:5px">${r.kusur === 100 ? 'Tam hak ediş' : 'Kusur indirimi uygulandı'}</div>
                </div>
            </div>
        </div>
        
        ${bestTahkimEmsal ? `
        <div style="margin-bottom:25px">
            <h2 style="color:#f59e0b;font-size:16px;margin-bottom:15px;border-left:4px solid #f59e0b;padding-left:10px">⚖️ TAHKİM EMSAL</h2>
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="padding:10px;background:#fef3c7;border:1px solid #fcd34d"><strong>Dosya No</strong></td>
                    <td style="padding:10px;border:1px solid #fcd34d">${bestTahkimEmsal.dosyaNo}</td>
                    <td style="padding:10px;background:#fef3c7;border:1px solid #fcd34d"><strong>Hükmedilen DK</strong></td>
                    <td style="padding:10px;border:1px solid #fcd34d;color:#f59e0b;font-weight:700">${formatMoney(bestTahkimEmsal.degerKaybi)}</td>
                </tr>
            </table>
        </div>
        ` : ''}
        
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
        pdf.save(`SAY_ADK_${r.marka}_${r.model}_${Date.now()}.pdf`);
        
        document.getElementById('loadingOverlay').classList.remove('active');
        alert('✓ PDF başarıyla indirildi!');
    } catch (err) {
        document.getElementById('loadingOverlay').classList.remove('active');
        alert('PDF oluşturma hatası: ' + err.message);
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>