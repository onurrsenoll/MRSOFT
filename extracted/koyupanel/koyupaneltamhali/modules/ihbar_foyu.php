<?php
/**
 * MR HASAR DANIŞMANLIK - İHBAR FÖYÜ
 * HERZAMAN FARKEDER
 */

require_once __DIR__ . '/../config.php';
include __DIR__ . '/../includes/header.php';
?>

<style>
.ihbar-container{max-width:1400px;margin:0 auto;background:white;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden}
.ihbar-header{background:linear-gradient(135deg,#2C5F8D 0%,#1a3d5c 100%);color:white;padding:30px;text-align:center}
.ihbar-header h1{font-size:24px;margin-bottom:5px}
.ihbar-header p{font-size:13px;color:#B8D4E8}
.tab-container{display:flex;background:#f8f9fa;border-bottom:2px solid #dee2e6}
.tab{flex:1;padding:15px;text-align:center;cursor:pointer;font-weight:bold;font-size:15px;background:#f8f9fa;border:none;transition:all 0.3s}
.tab:hover{background:#e9ecef}
.tab.active{background:white;color:#2C5F8D;border-bottom:3px solid #2C5F8D}
.tab-content{display:none}
.tab-content.active{display:block}
.form-content{padding:30px 40px}
.section{margin-bottom:20px;padding:15px 20px;background:#f8f9fa;border-radius:8px;border-left:4px solid #2C5F8D}
.section-title{font-size:13px;font-weight:bold;color:#2C5F8D;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:10px}
.form-group{display:flex;flex-direction:column}
.form-group.full-width{grid-column:1/-1}
.form-group label{font-size:11px;font-weight:600;color:#495057;margin-bottom:4px}
.form-group input,.form-group textarea,.form-group select{padding:8px 10px;border:2px solid #e0e0e0;border-radius:6px;font-size:12px;transition:all 0.3s}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{outline:none;border-color:#2C5F8D;box-shadow:0 0 0 3px rgba(44,95,141,0.1)}
.checkbox-group{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;margin-top:10px}
.checkbox-item{display:flex;align-items:center;gap:6px}
.checkbox-item input[type="checkbox"]{width:16px;height:16px;cursor:pointer}
.checkbox-item label{margin:0;cursor:pointer;font-weight:normal;font-size:11px}
.parts-input-section{background:#fff;padding:25px;border-radius:12px;margin-bottom:30px;border:2px solid #2C5F8D}
.parts-input-grid{display:grid;grid-template-columns:2fr 1fr 1.2fr 0.8fr auto;gap:15px;align-items:end}
.parts-table{width:100%;border-collapse:collapse;margin-top:20px;background:white;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
.parts-table th{background:#2C5F8D;color:white;padding:12px;text-align:left;font-size:13px;font-weight:bold}
.parts-table td{padding:10px 12px;border-bottom:1px solid #dee2e6;font-size:13px}
.parts-table tr:hover{background:#f8f9fa}
.delete-btn{background:#DC3545;color:white;border:none;padding:6px 12px;border-radius:5px;cursor:pointer;font-size:12px}
.total-section{background:#FFF3CD;padding:20px;border-radius:8px;margin-top:20px;border:2px solid #FFC107}
.total-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:16px;font-weight:bold}
.total-row.grand{font-size:20px;color:#2C5F8D;padding-top:10px;border-top:2px solid #2C5F8D}
.button-container{padding:30px 40px;background:#f8f9fa;display:flex;gap:15px;justify-content:center;border-top:1px solid #e0e0e0;flex-wrap:wrap}
.ibtn{padding:12px 20px;font-size:13px;font-weight:bold;border:none;border-radius:10px;cursor:pointer;transition:all 0.3s;display:flex;align-items:center;gap:8px}
.ibtn:hover{transform:translateY(-2px)}
.ibtn-green{background:linear-gradient(135deg,#28A745 0%,#20873a 100%);color:white}
.ibtn-red{background:linear-gradient(135deg,#DC3545 0%,#c82333 100%);color:white}
.ibtn-yellow{background:linear-gradient(135deg,#FFC107 0%,#e0a800 100%);color:#000}
.ibtn-blue{background:linear-gradient(135deg,#17A2B8 0%,#138496 100%);color:white}
.ibtn-purple{background:linear-gradient(135deg,#9B59B6 0%,#8E44AD 100%);color:white}
.ibtn-orange{background:linear-gradient(135deg,#E67E22 0%,#D35400 100%);color:white}
.ibtn-dark{background:linear-gradient(135deg,#34495E 0%,#2C3E50 100%);color:white}
@media(max-width:768px){.form-row,.parts-input-grid{grid-template-columns:1fr}.checkbox-group{grid-template-columns:1fr}.button-container{flex-direction:column}}
@media print{.nav,.button-container,.parts-input-section,.tab-container,#excelSection,#kdvSection,#iscilikSection{display:none!important}.ihbar-container{box-shadow:none}.tab-content{display:block!important}.ihbar-header{background:#2C5F8D!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>

<div class="ihbar-container">
    <div class="ihbar-header">
        <h1>MR HASAR DANIŞMANLIK VE FİLO YÖNETİMİ</h1>
        <p>İhbar Föyü ve Parça Listesi Yönetim Sistemi</p>
    </div>
    
    <div class="tab-container">
        <button class="tab active" onclick="switchTab('dosya')">📋 İhbar Föyü</button>
        <button class="tab" onclick="switchTab('parca')">🔧 Parça Listesi</button>
    </div>
    
    <!-- İHBAR FÖYÜ TAB -->
    <div id="dosya-tab" class="tab-content active">
        <div class="form-content">
            
            <!-- ARAÇ VE SAHİP BİLGİLERİ -->
            <div class="section">
                <div class="section-title">🚗 ARAÇ VE SAHİP BİLGİLERİ</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Plaka:</label>
                        <input type="text" id="plaka" style="text-transform:uppercase">
                    </div>
                    <div class="form-group">
                        <label>Marka / Model:</label>
                        <input type="text" id="marka">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Şasi No:</label>
                        <input type="text" id="sasi">
                    </div>
                    <div class="form-group">
                        <label>Kilometre:</label>
                        <input type="text" id="kilometre">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ruhsat Sahibi Ad Soyad:</label>
                        <input type="text" id="sahip_ad">
                    </div>
                    <div class="form-group">
                        <label>TC Kimlik No / Telefon:</label>
                        <input type="text" id="sahip_tel">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>IBAN:</label>
                        <input type="text" id="iban">
                    </div>
                </div>
            </div>
            
            <!-- DOSYA BİLGİLERİ -->
            <div class="section">
                <div class="section-title">📋 DOSYA BİLGİLERİ</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sigorta Şirketi:</label>
                        <input type="text" id="sigorta">
                    </div>
                    <div class="form-group">
                        <label>Dosya No:</label>
                        <input type="text" id="dosya_no">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Dosya Türü:</label>
                        <select id="dosya_turu">
                            <option value="KASKO">KASKO</option>
                            <option value="TRAFİK">TRAFİK</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Poliçe No:</label>
                        <input type="text" id="police_no">
                    </div>
                </div>
            </div>
            
            <!-- EKSPER VE SERVİS BİLGİLERİ -->
            <div class="section">
                <div class="section-title">🔍 EKSPER VE SERVİS BİLGİLERİ</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Eksper Ofisi & Adı:</label>
                        <input type="text" id="eksper_adi">
                    </div>
                    <div class="form-group">
                        <label>İletişim Bilgisi:</label>
                        <input type="text" id="eksper_iletisim" placeholder="Telefon / E-posta">
                    </div>
                </div>
                <div style="border-top:1px solid #dee2e6;margin:20px 0"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Servis Adı:</label>
                        <input type="text" id="servis_adi">
                    </div>
                    <div class="form-group">
                        <label>Yetkili Adı Soyadı:</label>
                        <input type="text" id="servis_yetkili">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Servis İletişim ve Adres:</label>
                        <input type="text" id="servis_tel">
                    </div>
                </div>
            </div>
            
            <!-- KAZA BİLGİLERİ -->
            <div class="section">
                <div class="section-title">⚠️ KAZA BİLGİLERİ</div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Kaza Yeri:</label>
                        <input type="text" id="kaza_yeri">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Kaza Açıklaması:</label>
                        <textarea id="kaza_aciklama" rows="4"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- EVRAK KONTROL -->
            <div class="section">
                <div class="section-title">✓ EVRAK KONTROL</div>
                <div class="checkbox-group">
                    <div class="checkbox-item"><input type="checkbox" id="evrak_ehliyet"><label for="evrak_ehliyet">Ehliyet</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_ruhsat"><label for="evrak_ruhsat">Ruhsat</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_police"><label for="evrak_police">Poliçe</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_ktt"><label for="evrak_ktt">KTT</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_iban"><label for="evrak_iban">IBAN</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_kilometre"><label for="evrak_kilometre">Kilometre</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_olay"><label for="evrak_olay">Olay Yeri Foto</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_hasar"><label for="evrak_hasar">Hasar Foto</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_parca"><label for="evrak_parca">Parça Listesi</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_ekspertiz"><label for="evrak_ekspertiz">Ekspertiz Raporu</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_fatura"><label for="evrak_fatura">Onarım Faturası</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="evrak_vekaletname"><label for="evrak_vekaletname">Vekaletname</label></div>
                </div>
            </div>
            
            <!-- İŞLEM DURUMU -->
            <div class="section">
                <div class="section-title">⚡ İŞLEM DURUMU</div>
                <div class="checkbox-group">
                    <div class="checkbox-item"><input type="checkbox" id="islem_ihbar"><label for="islem_ihbar">İhbar Yapıldı</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="islem_evrak"><label for="islem_evrak">Evrak Gönderildi</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="islem_ekspertiz"><label for="islem_ekspertiz">Ekspertiz Yapıldı</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="islem_basladi"><label for="islem_basladi">Onarım Başladı</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="islem_bitti"><label for="islem_bitti">Onarım Bitti</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="islem_odeme"><label for="islem_odeme">Ödeme Alındı</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="islem_deger"><label for="islem_deger">Değer Kaybı Açıldı</label></div>
                    <div class="checkbox-item"><input type="checkbox" id="islem_kapandi"><label for="islem_kapandi">Dosya Kapatıldı</label></div>
                </div>
            </div>
            
            <!-- NOTLAR -->
            <div class="section">
                <div class="section-title">📝 NOTLAR</div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <textarea id="notlar" rows="4"></textarea>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- PARÇA LİSTESİ TAB -->
    <div id="parca-tab" class="tab-content">
        <div class="form-content">
            
            <!-- EXCEL YÜKLE -->
            <div id="excelSection" style="background:#FFF3CD;padding:20px;border-radius:12px;margin-bottom:25px;border:2px solid #FFC107">
                <div style="font-weight:bold;font-size:15px;color:#856404;margin-bottom:15px">📂 EXCEL'DEN PARÇA LİSTESİ YÜKLEYİN</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                    <input type="file" id="excelImport" accept=".xlsx,.xls" onchange="importFromExcel()" style="padding:10px;border:2px solid #FFC107;border-radius:8px;background:white;flex:1;min-width:250px">
                    <button class="ibtn ibtn-blue" onclick="downloadTemplate()">📄 Şablon İndir</button>
                </div>
            </div>
            
            <!-- YENİ PARÇA EKLE -->
            <div class="parts-input-section">
                <div class="section-title">➕ YENİ PARÇA EKLE</div>
                <div class="parts-input-grid">
                    <div class="form-group">
                        <label>Parça Adı:</label>
                        <input type="text" id="part_name" placeholder="Örn: Ön Far Komple LED">
                    </div>
                    <div class="form-group">
                        <label>OEM Kod:</label>
                        <input type="text" id="part_oem" placeholder="OEM Kodu">
                    </div>
                    <div class="form-group">
                        <label>Orijinal Fiyat (TL):</label>
                        <input type="number" id="part_original" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Miktar:</label>
                        <input type="number" id="part_quantity" value="1" min="1">
                    </div>
                    <button class="ibtn ibtn-green" onclick="addPart()" style="height:38px">➕ Ekle</button>
                </div>
            </div>
            
            <!-- KDV CHECKBOX -->
            <div id="kdvSection" style="background:#E8F5E9;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #27AE60">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;font-weight:600">
                    <input type="checkbox" id="kdvCheckbox" onchange="renderPartsTable()" style="width:20px;height:20px">
                    <span>💵 KDV (%20) DAHİL HESAPLA</span>
                </label>
                <p style="font-size:11px;color:#666;margin:8px 0 0 30px">ℹ️ Fatura isteyen müşteriler için KDV dahil hesaplama yapılır</p>
            </div>
            
            <!-- İŞÇİLİK BEDELİ -->
            <div id="iscilikSection" style="background:#FFF3CD;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #FFC107">
                <div style="font-weight:bold;font-size:14px;color:#856404;margin-bottom:10px">🔧 İŞÇİLİK BEDELİ</div>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="number" id="iscilikBedeli" placeholder="İşçilik Bedeli (TL)" step="0.01" min="0" value="0" style="flex:1;padding:10px;border:2px solid #FFC107;border-radius:6px;font-size:14px" onchange="renderPartsTable()">
                    <span style="font-size:12px;color:#856404">⚠️ İşçiliğe KDV eklenmez</span>
                </div>
            </div>
            
            <!-- PARÇA TABLOSU -->
            <div class="section">
                <div class="section-title">📋 PARÇA LİSTESİ</div>
                <table class="parts-table" id="partsTable">
                    <thead>
                        <tr>
                            <th style="width:50px">Sıra</th>
                            <th>Parça Adı</th>
                            <th style="width:120px">OEM Kod</th>
                            <th style="width:100px;text-align:right">Orijinal (TL)</th>
                            <th style="width:80px;text-align:right">KDV %20</th>
                            <th style="width:100px;text-align:right">KDV Dahil</th>
                            <th style="width:60px">Miktar</th>
                            <th style="width:80px">İşlem</th>
                        </tr>
                    </thead>
                    <tbody id="partsTableBody">
                        <tr><td colspan="8" style="text-align:center;color:#999;padding:30px">Henüz parça eklenmedi</td></tr>
                    </tbody>
                </table>
                
                <div class="total-section" id="totalSection" style="display:none">
                    <div class="total-row">
                        <span>TOPLAM PARÇA (KDV Hariç):</span>
                        <span id="totalParca">0 TL</span>
                    </div>
                    <div class="total-row" id="kdvRow" style="display:none">
                        <span>TOPLAM KDV (%20):</span>
                        <span id="totalKDV">0 TL</span>
                    </div>
                    <div class="total-row" id="parcaKDVRow" style="display:none">
                        <span>PARÇA TOPLAMI (KDV Dahil):</span>
                        <span id="totalParcaKDV">0 TL</span>
                    </div>
                    <div class="total-row" id="iscilikRow" style="display:none">
                        <span>İŞÇİLİK (KDV yok):</span>
                        <span id="totalIscilik">0 TL</span>
                    </div>
                    <div class="total-row grand">
                        <span>GENEL TOPLAM:</span>
                        <span id="genelToplam">0 TL</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- BUTONLAR -->
    <div class="button-container">
        <button class="ibtn ibtn-orange" onclick="openPasteModal()">📋 METİN YAPIŞTIR (HIZLI DOLDUR)</button>
        <input type="file" id="jsonImport" accept=".json" style="display:none" onchange="loadJSONFile(event)">
        <button class="ibtn ibtn-purple" onclick="document.getElementById('jsonImport').click()">📂 JSON DOSYASI YÜKLE</button>
        <button class="ibtn ibtn-green" onclick="saveFile()">💾 DOSYAYI KAYDET</button>
        <button class="ibtn ibtn-blue" onclick="openSearchModal()">🔍 DOSYA ARA</button>
        <button class="ibtn ibtn-dark" onclick="createCompleteExcel()">📊 KOMPLE EXCEL ÇIKTISI AL</button>
        <button class="ibtn ibtn-blue" onclick="createPartsOnlyExcel()">🔧 SADECE PARÇA LİSTESİ ÇIKTISI</button>
        <button class="ibtn ibtn-yellow" onclick="printForm()">🖨️ YAZDIR / PDF KAYDET</button>
        <button class="ibtn ibtn-red" onclick="clearAll()">🗑️ HEPSİNİ TEMİZLE</button>
    </div>
</div>

<!-- DOSYA ARAMA MODAL -->
<div id="searchModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10000;justify-content:center;align-items:center">
    <div style="background:white;border-radius:12px;width:90%;max-width:800px;max-height:90vh;overflow:hidden">
        <div style="background:linear-gradient(135deg,#3498DB 0%,#2874A6 100%);color:white;padding:20px;display:flex;justify-content:space-between;align-items:center">
            <h2 style="margin:0;font-size:18px">🔍 KAYITLI DOSYALARI ARA</h2>
            <button onclick="closeSearchModal()" style="background:none;border:none;color:white;font-size:28px;cursor:pointer">&times;</button>
        </div>
        <div style="padding:20px">
            <div style="position:relative;margin-bottom:20px">
                <input type="text" id="searchInput" placeholder="Plaka veya ad soyad ile arayın..." oninput="filterFiles()" style="width:100%;padding:12px 40px 12px 15px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px">
                <span style="position:absolute;right:15px;top:50%;transform:translateY(-50%);color:#999">🔍</span>
            </div>
            <div id="filesList" style="max-height:400px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:8px">
                <div style="text-align:center;padding:40px;color:#999">📁 Henüz kaydedilmiş dosya yok</div>
            </div>
        </div>
    </div>
</div>

<!-- METİN YAPIŞTIR MODAL -->
<div id="pasteModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:10000;justify-content:center;align-items:center">
    <div style="background:white;padding:30px;border-radius:16px;width:90%;max-width:700px;max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h2 style="margin:0;color:#2C5F8D">📋 METİN YAPIŞTIR</h2>
            <button onclick="closePasteModal()" style="background:none;border:none;font-size:28px;cursor:pointer;color:#666">&times;</button>
        </div>
        <div style="background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:20px;font-size:13px;color:#666">
            <strong>📌 FORMAT ÖRNEĞİ:</strong><br>
            PLAKA: 55ABC123<br>
            MARKA: HONDA CBR<br>
            RUHSAT_SAHIBI: ALİ YILMAZ<br>
            SİGORTA: ALLİANZ<br>
            ---PARCALAR---<br>
            Ön Çamurluk,2300<br>
            Arka Stop,1500
        </div>
        <textarea id="pasteTextarea" placeholder="Metin verisini buraya yapıştırın..." style="width:100%;height:300px;padding:15px;border:2px solid #ddd;border-radius:8px;font-family:monospace;font-size:14px"></textarea>
        <div style="display:flex;gap:15px;margin-top:20px">
            <button onclick="processPastedText()" class="ibtn ibtn-green" style="flex:1">✅ VERİLERİ YÜKLE</button>
            <button onclick="closePasteModal()" class="ibtn ibtn-red">İptal</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
let partsList = [];

// TAB DEĞİŞTİRME
function switchTab(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector(`.tab[onclick="switchTab('${tab}')"]`).classList.add('active');
    document.getElementById(tab + '-tab').classList.add('active');
}

// PARÇA EKLEME
function addPart() {
    const name = document.getElementById('part_name').value.trim();
    const oem = document.getElementById('part_oem').value.trim();
    const original = parseFloat(document.getElementById('part_original').value) || 0;
    const quantity = parseInt(document.getElementById('part_quantity').value) || 1;
    
    if (!name || original <= 0) {
        alert('⚠️ Parça adı ve fiyat giriniz!');
        return;
    }
    
    partsList.push({ name, oem, original, quantity });
    renderPartsTable();
    
    document.getElementById('part_name').value = '';
    document.getElementById('part_oem').value = '';
    document.getElementById('part_original').value = '';
    document.getElementById('part_quantity').value = '1';
    document.getElementById('part_name').focus();
}

// PARÇA SİLME
function deletePart(index) {
    partsList.splice(index, 1);
    renderPartsTable();
}

// PARÇA TABLOSU RENDER
function renderPartsTable() {
    const tbody = document.getElementById('partsTableBody');
    const kdvChecked = document.getElementById('kdvCheckbox').checked;
    const iscilik = parseFloat(document.getElementById('iscilikBedeli').value) || 0;
    
    if (partsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#999;padding:30px">Henüz parça eklenmedi</td></tr>';
        document.getElementById('totalSection').style.display = 'none';
        return;
    }
    
    let html = '';
    let totalParca = 0;
    let totalKDV = 0;
    
    partsList.forEach((part, index) => {
        const kdv = part.original * 0.20;
        const kdvDahil = part.original + kdv;
        totalParca += part.original * part.quantity;
        totalKDV += kdv * part.quantity;
        
        html += `<tr>
            <td>${index + 1}</td>
            <td>${part.name}</td>
            <td>${part.oem || '-'}</td>
            <td style="text-align:right">${formatMoney(part.original)}</td>
            <td style="text-align:right">${formatMoney(kdv)}</td>
            <td style="text-align:right">${formatMoney(kdvDahil)}</td>
            <td style="text-align:center">${part.quantity}</td>
            <td style="text-align:center"><button class="delete-btn" onclick="deletePart(${index})">🗑️</button></td>
        </tr>`;
    });
    
    tbody.innerHTML = html;
    
    document.getElementById('totalSection').style.display = 'block';
    document.getElementById('totalParca').textContent = formatMoney(totalParca);
    document.getElementById('totalKDV').textContent = formatMoney(totalKDV);
    document.getElementById('totalParcaKDV').textContent = formatMoney(totalParca + totalKDV);
    document.getElementById('totalIscilik').textContent = formatMoney(iscilik);
    
    document.getElementById('kdvRow').style.display = kdvChecked ? 'flex' : 'none';
    document.getElementById('parcaKDVRow').style.display = kdvChecked ? 'flex' : 'none';
    document.getElementById('iscilikRow').style.display = iscilik > 0 ? 'flex' : 'none';
    
    const genelToplam = kdvChecked ? (totalParca + totalKDV + iscilik) : (totalParca + iscilik);
    document.getElementById('genelToplam').textContent = formatMoney(genelToplam);
}

// PARA FORMATI
function formatMoney(num) {
    return num.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
}

// DOSYA KAYDET
function saveFile() {
    const data = collectFormData();
    
    // localStorage'a kaydet
    const files = JSON.parse(localStorage.getItem('ihbar_dosyalari') || '[]');
    const existingIndex = files.findIndex(f => f.plaka === data.plaka && data.plaka);
    if (existingIndex >= 0) {
        files[existingIndex] = data;
    } else {
        files.unshift(data);
    }
    localStorage.setItem('ihbar_dosyalari', JSON.stringify(files));
    
    // JSON dosyası olarak indir
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ihbar_foyu_${data.plaka || 'dosya'}_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    
    alert('✅ Dosya kaydedildi ve indirildi!');
}

// FORM VERİLERİNİ TOPLA
function collectFormData() {
    return {
        plaka: document.getElementById('plaka').value,
        marka: document.getElementById('marka').value,
        sasi: document.getElementById('sasi').value,
        kilometre: document.getElementById('kilometre').value,
        sahip_ad: document.getElementById('sahip_ad').value,
        sahip_tel: document.getElementById('sahip_tel').value,
        iban: document.getElementById('iban').value,
        sigorta: document.getElementById('sigorta').value,
        dosya_no: document.getElementById('dosya_no').value,
        dosya_turu: document.getElementById('dosya_turu').value,
        police_no: document.getElementById('police_no').value,
        eksper_adi: document.getElementById('eksper_adi').value,
        eksper_iletisim: document.getElementById('eksper_iletisim').value,
        servis_adi: document.getElementById('servis_adi').value,
        servis_yetkili: document.getElementById('servis_yetkili').value,
        servis_tel: document.getElementById('servis_tel').value,
        kaza_yeri: document.getElementById('kaza_yeri').value,
        kaza_aciklama: document.getElementById('kaza_aciklama').value,
        notlar: document.getElementById('notlar').value,
        evraklar: getCheckedItems('evrak'),
        islemler: getCheckedItems('islem'),
        parcalar: partsList,
        iscilik: document.getElementById('iscilikBedeli').value,
        kdvDahil: document.getElementById('kdvCheckbox').checked,
        tarih: new Date().toISOString()
    };
}

// JSON DOSYASI YÜKLE
function loadJSONFile(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = JSON.parse(e.target.result);
            fillFormData(data);
            alert('✅ Dosya yüklendi!');
        } catch (err) {
            alert('❌ Dosya okunamadı!');
        }
    };
    reader.readAsText(file);
}

// FORM VERİLERİNİ DOLDUR
function fillFormData(data) {
    document.getElementById('plaka').value = data.plaka || '';
    document.getElementById('marka').value = data.marka || '';
    document.getElementById('sasi').value = data.sasi || '';
    document.getElementById('kilometre').value = data.kilometre || '';
    document.getElementById('sahip_ad').value = data.sahip_ad || '';
    document.getElementById('sahip_tel').value = data.sahip_tel || '';
    document.getElementById('iban').value = data.iban || '';
    document.getElementById('sigorta').value = data.sigorta || '';
    document.getElementById('dosya_no').value = data.dosya_no || '';
    document.getElementById('dosya_turu').value = data.dosya_turu || 'KASKO';
    document.getElementById('police_no').value = data.police_no || '';
    document.getElementById('eksper_adi').value = data.eksper_adi || '';
    document.getElementById('eksper_iletisim').value = data.eksper_iletisim || '';
    document.getElementById('servis_adi').value = data.servis_adi || '';
    document.getElementById('servis_yetkili').value = data.servis_yetkili || '';
    document.getElementById('servis_tel').value = data.servis_tel || '';
    document.getElementById('kaza_yeri').value = data.kaza_yeri || '';
    document.getElementById('kaza_aciklama').value = data.kaza_aciklama || '';
    document.getElementById('notlar').value = data.notlar || '';
    document.getElementById('iscilikBedeli').value = data.iscilik || 0;
    document.getElementById('kdvCheckbox').checked = data.kdvDahil || false;
    
    if (data.parcalar) {
        partsList = data.parcalar;
        renderPartsTable();
    }
}

// CHECKBOX DEĞERLER
function getCheckedItems(prefix) {
    const items = [];
    document.querySelectorAll(`input[id^="${prefix}_"]`).forEach(cb => {
        if (cb.checked) items.push(cb.id.replace(prefix + '_', ''));
    });
    return items;
}

// TEMİZLE
function clearAll() {
    if (confirm('Tüm alanlar temizlensin mi?')) {
        document.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(el => el.value = '');
        document.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false);
        document.getElementById('iscilikBedeli').value = '0';
        partsList = [];
        renderPartsTable();
    }
}

// METİN YAPIŞTIR MODAL
function openPasteModal() {
    document.getElementById('pasteModal').style.display = 'flex';
    document.getElementById('pasteTextarea').value = '';
    document.getElementById('pasteTextarea').focus();
}

function closePasteModal() {
    document.getElementById('pasteModal').style.display = 'none';
}

function processPastedText() {
    const text = document.getElementById('pasteTextarea').value.trim();
    if (!text) { alert('⚠️ Lütfen metin yapıştırın!'); return; }
    
    const lines = text.split('\n');
    let inParcalar = false;
    const parcalar = [];
    
    const mapping = {
        'PLAKA': 'plaka', 'MARKA': 'marka', 'ŞASİ': 'sasi', 'SASI': 'sasi',
        'KİLOMETRE': 'kilometre', 'KILOMETRE': 'kilometre', 'SAHİP': 'sahip_ad',
        'RUHSAT_SAHIBI': 'sahip_ad', 'RUHSAT SAHİBİ': 'sahip_ad',
        'TC': 'sahip_tel', 'TELEFON': 'sahip_tel', 'IBAN': 'iban',
        'SİGORTA': 'sigorta', 'SIGORTA': 'sigorta', 'DOSYA': 'dosya_no', 'DOSYA_NO': 'dosya_no',
        'POLİÇE': 'police_no', 'POLICE': 'police_no', 'POLİÇE_NO': 'police_no',
        'EKSPER': 'eksper_adi', 'EKSPER_ADI': 'eksper_adi',
        'SERVİS': 'servis_adi', 'SERVIS': 'servis_adi', 'SERVİS_ADI': 'servis_adi',
        'NOT': 'notlar', 'NOTLAR': 'notlar', 'İŞÇİLİK': 'iscilikBedeli', 'ISCILIK': 'iscilikBedeli'
    };
    
    for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed) continue;
        
        if (trimmed.includes('---PARÇA') || trimmed.includes('---PARCA')) {
            inParcalar = true;
            continue;
        }
        
        if (inParcalar) {
            const parts = trimmed.split(',');
            if (parts.length >= 2) {
                const name = parts[0].trim();
                const price = parseFloat(parts[1].replace(/[^\d]/g, '')) || 0;
                if (name && price > 0) {
                    parcalar.push({ name, oem: '', original: price, quantity: 1 });
                }
            }
        } else {
            const colonIndex = trimmed.indexOf(':');
            if (colonIndex > 0) {
                const key = trimmed.substring(0, colonIndex).trim().toUpperCase();
                const value = trimmed.substring(colonIndex + 1).trim();
                const fieldId = mapping[key];
                if (fieldId) {
                    const el = document.getElementById(fieldId);
                    if (el) el.value = value;
                }
            }
        }
    }
    
    if (parcalar.length > 0) {
        partsList = parcalar;
        renderPartsTable();
    }
    
    closePasteModal();
    alert('✅ Veriler yüklendi! Parça sayısı: ' + parcalar.length);
}

// DOSYA ARAMA MODAL
function openSearchModal() {
    document.getElementById('searchModal').style.display = 'flex';
    loadSavedFiles();
}

function closeSearchModal() {
    document.getElementById('searchModal').style.display = 'none';
}

function loadSavedFiles() {
    const files = JSON.parse(localStorage.getItem('ihbar_dosyalari') || '[]');
    const container = document.getElementById('filesList');
    
    if (files.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:40px;color:#999">📁 Henüz kaydedilmiş dosya yok</div>';
        return;
    }
    
    let html = '';
    files.forEach((file, index) => {
        html += `
        <div style="padding:15px;border-bottom:1px solid #e0e0e0;display:flex;justify-content:space-between;align-items:center">
            <div style="flex:1">
                <div style="font-weight:bold;color:#2C5F8D;font-size:15px;margin-bottom:5px">${file.plaka || 'Plakasız'} - ${file.sahip_ad || 'İsimsiz'}</div>
                <div style="font-size:12px;color:#666">${file.sigorta || '-'} | ${file.dosya_no || '-'} | ${new Date(file.tarih).toLocaleDateString('tr-TR')}</div>
            </div>
            <div style="display:flex;gap:10px">
                <button onclick="loadSavedFile(${index})" class="ibtn ibtn-blue" style="padding:8px 15px;font-size:12px">📂 Yükle</button>
                <button onclick="deleteSavedFile(${index})" class="ibtn ibtn-red" style="padding:8px 15px;font-size:12px">🗑️ Sil</button>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

function filterFiles() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const files = JSON.parse(localStorage.getItem('ihbar_dosyalari') || '[]');
    const container = document.getElementById('filesList');
    
    const filtered = files.filter(f => 
        (f.plaka || '').toLowerCase().includes(search) || 
        (f.sahip_ad || '').toLowerCase().includes(search)
    );
    
    if (filtered.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:40px;color:#999">🔍 Sonuç bulunamadı</div>';
        return;
    }
    
    let html = '';
    filtered.forEach((file) => {
        const realIndex = files.indexOf(file);
        html += `
        <div style="padding:15px;border-bottom:1px solid #e0e0e0;display:flex;justify-content:space-between;align-items:center">
            <div style="flex:1">
                <div style="font-weight:bold;color:#2C5F8D;font-size:15px;margin-bottom:5px">${file.plaka || 'Plakasız'} - ${file.sahip_ad || 'İsimsiz'}</div>
                <div style="font-size:12px;color:#666">${file.sigorta || '-'} | ${file.dosya_no || '-'} | ${new Date(file.tarih).toLocaleDateString('tr-TR')}</div>
            </div>
            <div style="display:flex;gap:10px">
                <button onclick="loadSavedFile(${realIndex})" class="ibtn ibtn-blue" style="padding:8px 15px;font-size:12px">📂 Yükle</button>
                <button onclick="deleteSavedFile(${realIndex})" class="ibtn ibtn-red" style="padding:8px 15px;font-size:12px">🗑️ Sil</button>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

function loadSavedFile(index) {
    const files = JSON.parse(localStorage.getItem('ihbar_dosyalari') || '[]');
    const data = files[index];
    if (!data) return;
    
    fillFormData(data);
    closeSearchModal();
    alert('✅ Dosya yüklendi!');
}

function deleteSavedFile(index) {
    if (!confirm('Bu dosyayı silmek istediğinize emin misiniz?')) return;
    
    const files = JSON.parse(localStorage.getItem('ihbar_dosyalari') || '[]');
    files.splice(index, 1);
    localStorage.setItem('ihbar_dosyalari', JSON.stringify(files));
    loadSavedFiles();
}

// EXCEL İÇE AKTAR
function importFromExcel() {
    const file = document.getElementById('excelImport').files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });
        
        rows.forEach((row, i) => {
            if (i === 0) return;
            const name = row[0] || row[1];
            const price = parseFloat(String(row[2] || row[3] || 0).replace(/[^\d]/g, '')) || 0;
            if (name && price > 0) {
                partsList.push({ name: String(name), oem: String(row[1] || ''), original: price, quantity: 1 });
            }
        });
        
        renderPartsTable();
        alert('✅ Excel yüklendi! ' + partsList.length + ' parça');
    };
    reader.readAsArrayBuffer(file);
}

// EXCEL ŞABLON İNDİR
function downloadTemplate() {
    const ws_data = [['Parça Adı', 'OEM Kod', 'Fiyat (TL)'], ['Ön Tampon', '12345678', '5000'], ['Arka Stop', '87654321', '2500']];
    const ws = XLSX.utils.aoa_to_sheet(ws_data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Parçalar');
    XLSX.writeFile(wb, 'parca_sablonu.xlsx');
}

// KOMPLE EXCEL ÇIKTISI
function createCompleteExcel() {
    const wb = XLSX.utils.book_new();
    
    const info = [
        ['MR HASAR DANIŞMANLIK - İHBAR FÖYÜ'],
        ['Tarih: ' + new Date().toLocaleDateString('tr-TR')],
        [''],
        ['ARAÇ VE SAHİP BİLGİLERİ'],
        ['Plaka', document.getElementById('plaka').value],
        ['Marka/Model', document.getElementById('marka').value],
        ['Şasi No', document.getElementById('sasi').value],
        ['Kilometre', document.getElementById('kilometre').value],
        ['Ruhsat Sahibi', document.getElementById('sahip_ad').value],
        ['TC/Telefon', document.getElementById('sahip_tel').value],
        ['IBAN', document.getElementById('iban').value],
        [''],
        ['DOSYA BİLGİLERİ'],
        ['Sigorta', document.getElementById('sigorta').value],
        ['Dosya No', document.getElementById('dosya_no').value],
        ['Dosya Türü', document.getElementById('dosya_turu').value],
        ['Poliçe No', document.getElementById('police_no').value],
        [''],
        ['EKSPER VE SERVİS'],
        ['Eksper', document.getElementById('eksper_adi').value],
        ['Eksper İletişim', document.getElementById('eksper_iletisim').value],
        ['Servis', document.getElementById('servis_adi').value],
        ['Servis Yetkili', document.getElementById('servis_yetkili').value],
        ['Servis İletişim', document.getElementById('servis_tel').value],
        [''],
        ['KAZA BİLGİLERİ'],
        ['Kaza Yeri', document.getElementById('kaza_yeri').value],
        ['Açıklama', document.getElementById('kaza_aciklama').value],
        [''],
        ['NOTLAR'],
        ['', document.getElementById('notlar').value]
    ];
    
    const ws1 = XLSX.utils.aoa_to_sheet(info);
    ws1['!cols'] = [{wch:20}, {wch:50}];
    XLSX.utils.book_append_sheet(wb, ws1, 'Dosya Bilgileri');
    
    if (partsList.length > 0) {
        const kdvChecked = document.getElementById('kdvCheckbox').checked;
        const iscilik = parseFloat(document.getElementById('iscilikBedeli').value) || 0;
        
        const parcaData = [['Sıra', 'Parça Adı', 'OEM', 'Birim Fiyat', 'KDV', 'KDV Dahil', 'Miktar', 'Toplam']];
        let toplam = 0, toplamKDV = 0;
        
        partsList.forEach((p, i) => {
            const kdv = p.original * 0.20;
            const satirToplam = kdvChecked ? (p.original + kdv) * p.quantity : p.original * p.quantity;
            toplam += p.original * p.quantity;
            toplamKDV += kdv * p.quantity;
            parcaData.push([i + 1, p.name, p.oem || '-', p.original, kdv.toFixed(2), (p.original + kdv).toFixed(2), p.quantity, satirToplam.toFixed(2)]);
        });
        
        parcaData.push(['']);
        parcaData.push(['', '', '', '', '', '', 'Parça Toplam:', toplam.toFixed(2)]);
        if (kdvChecked) {
            parcaData.push(['', '', '', '', '', '', 'KDV Toplam:', toplamKDV.toFixed(2)]);
        }
        if (iscilik > 0) {
            parcaData.push(['', '', '', '', '', '', 'İşçilik:', iscilik.toFixed(2)]);
        }
        const genelToplam = kdvChecked ? (toplam + toplamKDV + iscilik) : (toplam + iscilik);
        parcaData.push(['', '', '', '', '', '', 'GENEL TOPLAM:', genelToplam.toFixed(2)]);
        
        const ws2 = XLSX.utils.aoa_to_sheet(parcaData);
        ws2['!cols'] = [{wch:6}, {wch:30}, {wch:15}, {wch:12}, {wch:10}, {wch:12}, {wch:8}, {wch:12}];
        XLSX.utils.book_append_sheet(wb, ws2, 'Parça Listesi');
    }
    
    XLSX.writeFile(wb, `ihbar_foyu_${document.getElementById('plaka').value || 'dosya'}_${new Date().toISOString().split('T')[0]}.xlsx`);
    alert('✅ Komple Excel çıktısı indirildi!');
}

// SADECE PARÇA LİSTESİ EXCEL
function createPartsOnlyExcel() {
    if (partsList.length === 0) {
        alert('⚠️ Parça listesi boş!');
        return;
    }
    
    const kdvChecked = document.getElementById('kdvCheckbox').checked;
    const iscilik = parseFloat(document.getElementById('iscilikBedeli').value) || 0;
    
    const ws_data = [
        ['MR HASAR DANIŞMANLIK - PARÇA LİSTESİ'],
        ['Plaka: ' + (document.getElementById('plaka').value || '-')],
        ['Ruhsat Sahibi: ' + (document.getElementById('sahip_ad').value || '-')],
        ['Tarih: ' + new Date().toLocaleDateString('tr-TR')],
        [''],
        ['Sıra', 'Parça Adı', 'OEM Kod', 'Birim Fiyat', 'KDV (%20)', 'KDV Dahil', 'Miktar', 'Toplam']
    ];
    
    let toplamParca = 0, toplamKDV = 0;
    
    partsList.forEach((part, index) => {
        const kdv = part.original * 0.20;
        const kdvDahil = part.original + kdv;
        const satirToplam = kdvChecked ? kdvDahil * part.quantity : part.original * part.quantity;
        toplamParca += part.original * part.quantity;
        toplamKDV += kdv * part.quantity;
        
        ws_data.push([index + 1, part.name, part.oem || '-', part.original, kdv.toFixed(2), kdvDahil.toFixed(2), part.quantity, satirToplam.toFixed(2)]);
    });
    
    ws_data.push(['']);
    ws_data.push(['', '', '', '', '', '', 'PARÇA TOPLAMI (KDV Hariç):', toplamParca.toFixed(2)]);
    if (kdvChecked) {
        ws_data.push(['', '', '', '', '', '', 'TOPLAM KDV:', toplamKDV.toFixed(2)]);
        ws_data.push(['', '', '', '', '', '', 'PARÇA TOPLAMI (KDV Dahil):', (toplamParca + toplamKDV).toFixed(2)]);
    }
    if (iscilik > 0) {
        ws_data.push(['', '', '', '', '', '', 'İŞÇİLİK:', iscilik.toFixed(2)]);
    }
    const genelToplam = kdvChecked ? (toplamParca + toplamKDV + iscilik) : (toplamParca + iscilik);
    ws_data.push(['', '', '', '', '', '', 'GENEL TOPLAM:', genelToplam.toFixed(2)]);
    ws_data.push(['']);
    ws_data.push(['', 'İşçilik bedeli, boya ve diğer masraflar dahil değildir.']);
    
    const ws = XLSX.utils.aoa_to_sheet(ws_data);
    ws['!cols'] = [{wch:6}, {wch:35}, {wch:15}, {wch:12}, {wch:12}, {wch:12}, {wch:8}, {wch:15}];
    
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Parça Listesi');
    
    const plaka = document.getElementById('plaka').value.replace(/\s+/g, '_') || 'dosya';
    XLSX.writeFile(wb, `Parca_Listesi_${plaka}_${new Date().toISOString().split('T')[0]}.xlsx`);
    alert('✅ Parça listesi Excel dosyası indirildi!');
}

// YAZDIR
function printForm() {
    window.print();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>