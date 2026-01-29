<?php
/**
 * ADK Hesaplama Modülü
 * Dosya detay sayfasına include edilir
 */

$dosya_id = $dosya['id'] ?? 0;
$mevcut_marka = $dosya['arac_marka'] ?? '';
$mevcut_model = $dosya['arac_model'] ?? '';
$mevcut_yil = $dosya['model_yili'] ?? '';
$mevcut_plaka = $dosya['plaka'] ?? '';
$mevcut_km = $dosya['kilometre'] ?? '';
$mevcut_hasar_tarihi = $dosya['kaza_tarihi'] ?? '';

$onceki_adk = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM adk_hesaplamalar WHERE dosya_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$dosya_id]);
    $onceki_adk = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>

<style>
.adk-module{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);border-radius:16px;padding:24px;margin-top:20px;border:1px solid #2d3748}
.adk-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #2d3748}
.adk-header h3{display:flex;align-items:center;gap:10px;color:#fff;font-size:18px;margin:0}
.adk-header h3 .icon{width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#8b5cf6);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px}
.adk-badge{background:rgba(16,185,129,0.2);color:#10b981;padding:6px 12px;border-radius:20px;font-size:11px;font-weight:600}
.adk-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:20px}
.adk-field{display:flex;flex-direction:column;gap:6px}
.adk-field label{font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:600}
.adk-field input,.adk-field select{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:10px 12px;color:#fff;font-size:14px}
.adk-field input:focus,.adk-field select:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.2)}
.adk-field select option{background:#1e293b}
.adk-actions{display:flex;gap:10px;margin-top:15px}
.btn-adk{flex:1;padding:12px 20px;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s}
.btn-adk-primary{background:linear-gradient(135deg,#3b82f6,#8b5cf6);color:white}
.btn-adk-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(59,130,246,0.4)}
.btn-adk-secondary{background:#1e293b;color:#94a3b8;border:1px solid #334155}
.btn-adk-secondary:hover{background:#334155;color:#fff}
.btn-adk-success{background:linear-gradient(135deg,#10b981,#059669);color:white}
.adk-result{display:none;margin-top:20px;padding-top:20px;border-top:1px solid #2d3748}
.adk-result.active{display:block;animation:fadeIn 0.3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.adk-result-main{background:linear-gradient(135deg,rgba(16,185,129,0.1),rgba(6,182,212,0.1));border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:20px;text-align:center;margin-bottom:20px}
.adk-result-label{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
.adk-result-value{font-size:36px;font-weight:800;background:linear-gradient(135deg,#10b981,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:5px}
.adk-result-range{font-size:13px;color:#64748b}
.adk-methods{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
.adk-method-card{background:#0f172a;border-radius:10px;padding:15px;text-align:center}
.adk-method-label{font-size:10px;color:#64748b;text-transform:uppercase;margin-bottom:6px}
.adk-method-value{font-size:16px;font-weight:700;color:#3b82f6}
.adk-analysis{background:linear-gradient(135deg,rgba(139,92,246,0.1),rgba(59,130,246,0.1));border:1px solid rgba(139,92,246,0.2);border-radius:10px;padding:15px}
.adk-analysis-header{display:flex;align-items:center;gap:8px;margin-bottom:10px;color:#8b5cf6;font-size:12px;font-weight:600}
.adk-analysis-content{font-size:13px;line-height:1.7;color:#94a3b8}
.adk-analysis-content strong{color:#fff}
.adk-loading{display:none;text-align:center;padding:30px}
.adk-loading.active{display:block}
.adk-spinner{width:40px;height:40px;border:3px solid #334155;border-top-color:#3b82f6;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 15px}
@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:768px){.adk-grid{grid-template-columns:1fr 1fr}.adk-methods{grid-template-columns:1fr}}
</style>

<div class="adk-module" id="adkModule">
    <div class="adk-header">
        <h3><span class="icon">🚗</span>Değer Kaybı Hesaplama</h3>
        <span class="adk-badge" id="adkPremiumBadge" style="display:none;">⭐ PREMIUM +%15</span>
        <?php if ($onceki_adk): ?>
        <span class="adk-badge">Son: <?= number_format($onceki_adk['onerilen_deger'], 0, ',', '.') ?> ₺</span>
        <?php endif; ?>
    </div>

    <div class="adk-grid">
        <div class="adk-field"><label>Marka</label><select id="adkMarka" onchange="adkUpdateModels()"><option value="">Seçiniz</option></select></div>
        <div class="adk-field"><label>Model</label><select id="adkModel"><option value="">Önce marka seçin</option></select></div>
        <div class="adk-field"><label>Model Yılı</label><select id="adkYil"><option value="">Seçiniz</option></select></div>
        <div class="adk-field"><label>Kilometre</label><input type="text" id="adkKm" placeholder="Örn: 150.000" oninput="adkFormatNumber(this)"></div>
        <div class="adk-field"><label>Rayiç Değer (TL)</label><input type="text" id="adkRayic" placeholder="Piyasa değeri" oninput="adkFormatNumber(this)"></div>
        <div class="adk-field"><label>Onarım Bedeli (TL)</label><input type="text" id="adkOnarim" placeholder="Hasar tutarı" oninput="adkFormatNumber(this)"></div>
        <div class="adk-field"><label>Önceki Hasar</label><select id="adkOncekiHasar"><option value="0">Yok</option><option value="1">1 Adet</option><option value="2">2 Adet</option><option value="3">3 Adet</option><option value="4">4+ Adet</option></select></div>
        <div class="adk-field"><label>Hasarlı Bölge</label><select id="adkBolge"><option value="on">Ön Kısım</option><option value="arka">Arka Kısım</option><option value="yan">Yan Kısım</option><option value="tavan">Tavan</option></select></div>
    </div>

    <div class="adk-actions">
        <button class="btn-adk btn-adk-primary" onclick="adkHesapla()">🤖 AI ile Hesapla</button>
        <button class="btn-adk btn-adk-secondary" onclick="adkTemizle()">🔄 Temizle</button>
    </div>

    <div class="adk-loading" id="adkLoading"><div class="adk-spinner"></div><div style="color:#94a3b8;">Yapay zeka analiz ediyor...</div></div>

    <div class="adk-result" id="adkResult">
        <div class="adk-result-main">
            <div class="adk-result-label">ÖNERİLEN DEĞER KAYBI</div>
            <div class="adk-result-value" id="adkSonucDeger">0 ₺</div>
            <div class="adk-result-range" id="adkSonucAralik">Aralık: 0 - 0 ₺</div>
        </div>
        <div class="adk-methods">
            <div class="adk-method-card"><div class="adk-method-label">Nisbi Yöntem</div><div class="adk-method-value" id="adkNisbi">0 ₺</div></div>
            <div class="adk-method-card"><div class="adk-method-label">Piyasa Yöntemi</div><div class="adk-method-value" id="adkPiyasa">0 ₺</div></div>
            <div class="adk-method-card"><div class="adk-method-label">TSB Formül</div><div class="adk-method-value" id="adkTsb">0 ₺</div></div>
        </div>
        <div class="adk-analysis">
            <div class="adk-analysis-header"><span>🤖</span> AI Analiz Raporu</div>
            <div class="adk-analysis-content" id="adkAnaliz"></div>
        </div>
        <div class="adk-actions" style="margin-top:15px;">
            <button class="btn-adk btn-adk-success" onclick="adkKaydet()">💾 Dosyaya Kaydet</button>
            <button class="btn-adk btn-adk-secondary" onclick="adkPdfIndir()">📥 PDF İndir</button>
        </div>
    </div>
</div>

<script>
const ADK_DOSYA_ID = <?= $dosya_id ?>;
let adkSonucData = null;

document.addEventListener('DOMContentLoaded', function() { adkInit(); });

async function adkInit() {
    await adkLoadMarkalar();
    const yilSelect = document.getElementById('adkYil');
    for (let y = 2025; y >= 1990; y--) { yilSelect.innerHTML += `<option value="${y}">${y}</option>`; }
    <?php if ($mevcut_marka): ?>
    setTimeout(() => {
        document.getElementById('adkMarka').value = '<?= addslashes($mevcut_marka) ?>';
        adkUpdateModels().then(() => { document.getElementById('adkModel').value = '<?= addslashes($mevcut_model) ?>'; });
    }, 500);
    <?php endif; ?>
    <?php if ($mevcut_yil): ?>document.getElementById('adkYil').value = '<?= $mevcut_yil ?>';<?php endif; ?>
    <?php if ($mevcut_km): ?>document.getElementById('adkKm').value = '<?= number_format($mevcut_km, 0, '.', '.') ?>';<?php endif; ?>
}

async function adkLoadMarkalar() {
    try {
        const res = await fetch('adk_api.php?action=get_markalar');
        const data = await res.json();
        if (data.success) {
            const select = document.getElementById('adkMarka');
            select.innerHTML = '<option value="">Seçiniz</option>';
            data.data.forEach(m => {
                const premium = m.premium == 1 ? ' ⭐' : '';
                select.innerHTML += `<option value="${m.marka}" data-premium="${m.premium}">${m.marka}${premium}</option>`;
            });
        }
    } catch (e) { console.error('Marka yükleme hatası:', e); }
}

async function adkUpdateModels() {
    const marka = document.getElementById('adkMarka').value;
    const modelSelect = document.getElementById('adkModel');
    const premiumBadge = document.getElementById('adkPremiumBadge');
    modelSelect.innerHTML = '<option value="">Yükleniyor...</option>';
    if (!marka) { modelSelect.innerHTML = '<option value="">Önce marka seçin</option>'; premiumBadge.style.display = 'none'; return; }
    try {
        const res = await fetch(`adk_api.php?action=get_modeller&marka=${encodeURIComponent(marka)}`);
        const data = await res.json();
        if (data.success) {
            modelSelect.innerHTML = '<option value="">Seçiniz</option>';
            data.data.forEach(m => { modelSelect.innerHTML += `<option value="${m.model}">${m.model}</option>`; });
            premiumBadge.style.display = data.premium ? 'inline-block' : 'none';
        }
    } catch (e) { console.error('Model yükleme hatası:', e); modelSelect.innerHTML = '<option value="">Hata oluştu</option>'; }
}

function adkFormatNumber(input) { let value = input.value.replace(/\D/g, ''); input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
function adkFormatMoney(num) { return Number(num).toLocaleString('tr-TR') + ' ₺'; }

async function adkHesapla() {
    const marka = document.getElementById('adkMarka').value;
    const model = document.getElementById('adkModel').value;
    const yil = document.getElementById('adkYil').value;
    const km = document.getElementById('adkKm').value.replace(/\./g, '');
    const rayic = document.getElementById('adkRayic').value.replace(/\./g, '');
    const onarim = document.getElementById('adkOnarim').value.replace(/\./g, '');
    const oncekiHasar = document.getElementById('adkOncekiHasar').value;
    const bolge = document.getElementById('adkBolge').value;
    if (!marka || !model || !yil || !rayic || !onarim) { alert('Lütfen tüm zorunlu alanları doldurun!'); return; }
    document.getElementById('adkLoading').classList.add('active');
    document.getElementById('adkResult').classList.remove('active');
    try {
        const formData = new FormData();
        formData.append('marka', marka); formData.append('model', model); formData.append('yil', yil);
        formData.append('km', km); formData.append('rayic', rayic); formData.append('onarim', onarim);
        formData.append('onceki_hasar', oncekiHasar); formData.append('bolge', bolge);
        const res = await fetch('adk_api.php?action=hesapla', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) { adkSonucData = data; adkShowResult(data); }
        else { alert('Hata: ' + (data.error || 'Bilinmeyen hata')); }
    } catch (e) { console.error('Hesaplama hatası:', e); alert('Bağlantı hatası!'); }
    finally { document.getElementById('adkLoading').classList.remove('active'); }
}

function adkShowResult(data) {
    const s = data.sonuc;
    document.getElementById('adkSonucDeger').textContent = adkFormatMoney(s.onerilen);
    document.getElementById('adkSonucAralik').textContent = `Aralık: ${adkFormatMoney(s.onerilen_min)} - ${adkFormatMoney(s.onerilen_max)}`;
    document.getElementById('adkNisbi').textContent = `${adkFormatMoney(s.nisbi_min)} - ${adkFormatMoney(s.nisbi_max)}`;
    document.getElementById('adkPiyasa').textContent = adkFormatMoney(s.piyasa_yontemi);
    document.getElementById('adkTsb').textContent = adkFormatMoney(s.tsb_formul);
    document.getElementById('adkAnaliz').innerHTML = data.analiz;
    document.getElementById('adkResult').classList.add('active');
}

async function adkKaydet() {
    if (!adkSonucData) { alert('Önce hesaplama yapın!'); return; }
    const kayitData = {
        dosya_id: ADK_DOSYA_ID, marka: adkSonucData.arac.marka, model: adkSonucData.arac.model,
        yil: adkSonucData.arac.yil, km: adkSonucData.arac.km, rayic: adkSonucData.giris.rayic,
        onarim: adkSonucData.giris.onarim, onceki_hasar: adkSonucData.giris.onceki_hasar,
        bolge: adkSonucData.giris.bolge, nisbi_min: adkSonucData.sonuc.nisbi_min,
        nisbi_max: adkSonucData.sonuc.nisbi_max, piyasa_yontemi: adkSonucData.sonuc.piyasa_yontemi,
        tsb_formul: adkSonucData.sonuc.tsb_formul, onerilen_min: adkSonucData.sonuc.onerilen_min,
        onerilen_max: adkSonucData.sonuc.onerilen_max, onerilen: adkSonucData.sonuc.onerilen,
        katsayilar: adkSonucData.katsayilar
    };
    try {
        const res = await fetch('adk_api.php?action=kaydet', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(kayitData) });
        const data = await res.json();
        if (data.success) { alert('✅ Değer kaybı dosyaya kaydedildi!'); location.reload(); }
        else { alert('Hata: ' + (data.error || 'Kayıt başarısız')); }
    } catch (e) { console.error('Kayıt hatası:', e); alert('Bağlantı hatası!'); }
}

function adkPdfIndir() {
    if (!adkSonucData) { alert('Önce hesaplama yapın!'); return; }
    const params = new URLSearchParams({
        dosya_id: ADK_DOSYA_ID, marka: adkSonucData.arac.marka, model: adkSonucData.arac.model,
        yil: adkSonucData.arac.yil, km: adkSonucData.arac.km || 0, rayic: adkSonucData.giris.rayic,
        onarim: adkSonucData.giris.onarim, onceki_hasar: adkSonucData.giris.onceki_hasar,
        bolge: adkSonucData.giris.bolge, nisbi_min: adkSonucData.sonuc.nisbi_min,
        nisbi_max: adkSonucData.sonuc.nisbi_max, piyasa: adkSonucData.sonuc.piyasa_yontemi,
        tsb: adkSonucData.sonuc.tsb_formul, onerilen_min: adkSonucData.sonuc.onerilen_min,
        onerilen_max: adkSonucData.sonuc.onerilen_max, onerilen: adkSonucData.sonuc.onerilen
    });
    window.open(`adk_pdf.php?${params.toString()}`, '_blank');
}

function adkTemizle() {
    document.getElementById('adkMarka').value = '';
    document.getElementById('adkModel').innerHTML = '<option value="">Önce marka seçin</option>';
    document.getElementById('adkYil').value = '';
    document.getElementById('adkKm').value = '';
    document.getElementById('adkRayic').value = '';
    document.getElementById('adkOnarim').value = '';
    document.getElementById('adkOncekiHasar').value = '0';
    document.getElementById('adkBolge').value = 'on';
    document.getElementById('adkPremiumBadge').style.display = 'none';
    document.getElementById('adkResult').classList.remove('active');
    adkSonucData = null;
}
</script>