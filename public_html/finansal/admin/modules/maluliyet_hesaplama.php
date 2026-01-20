<?php
/**
 * MR HASAR DANIŞMANLIK - Maluliyet Hesaplama
 * HERZAMAN FARKEDER
 */
?>
<div class="page-header">
    <h1><i class="bi bi-heart-pulse me-2"></i>Maluliyet Hesaplama</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8">
                <form id="maluliyetForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Hesaplama Türü</label>
                            <select class="form-select" name="hesap_turu" id="hesapTuru">
                                <option value="gecici">Geçici İş Göremezlik</option>
                                <option value="kalici">Kalıcı Maluliyet</option>
                                <option value="bakici">Bakıcı Gideri</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Doğum Tarihi</label>
                            <input type="text" class="form-control datepicker" name="dogum_tarihi" id="dogumTarihi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kaza Tarihi</label>
                            <input type="text" class="form-control datepicker" name="kaza_tarihi" id="kazaTarihi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maluliyet Oranı (%)</label>
                            <input type="number" class="form-control" name="maluliyet_orani" id="maluliyetOrani" min="0" max="100" step="0.1" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aylık Net Gelir (₺)</label>
                            <input type="number" class="form-control" name="aylik_gelir" id="aylikGelir" step="100" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kusur Oranı (%)</label>
                            <input type="number" class="form-control" name="kusur_orani" id="kusurOrani" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6" id="geciciGunDiv">
                            <label class="form-label">Geçici İş Göremezlik Süresi (Gün)</label>
                            <input type="number" class="form-control" name="gecici_gun" id="geciciGun" min="0" value="0">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="hesaplaBtn">
                            <i class="bi bi-calculator me-2"></i>Hesapla
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-clipboard-data me-2"></i>Hesaplama Sonucu
                    </div>
                    <div class="card-body">
                        <div id="sonucAlani" class="text-center py-4 text-muted">
                            <i class="bi bi-calculator display-4"></i>
                            <p class="mt-2">Bilgileri girin ve hesapla butonuna tıklayın.</p>
                        </div>
                        <div id="sonucDetay" style="display:none;">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Yaş:</td>
                                    <td class="text-end" id="sonucYas">-</td>
                                </tr>
                                <tr>
                                    <td>Aktif Dönem:</td>
                                    <td class="text-end" id="sonucAktif">-</td>
                                </tr>
                                <tr>
                                    <td>PMF Değeri:</td>
                                    <td class="text-end" id="sonucPmf">-</td>
                                </tr>
                            </table>
                            <hr>
                            <div class="text-center">
                                <h5 class="text-muted">Hesaplanan Tazminat</h5>
                                <h2 class="text-primary" id="sonucToplam">₺0</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('hesaplaBtn').addEventListener('click', function() {
    const dogumTarihi = document.getElementById('dogumTarihi').value;
    const kazaTarihi = document.getElementById('kazaTarihi').value;
    const maluliyetOrani = parseFloat(document.getElementById('maluliyetOrani').value) || 0;
    const aylikGelir = parseFloat(document.getElementById('aylikGelir').value) || 0;
    const kusurOrani = parseFloat(document.getElementById('kusurOrani').value) || 0;
    const hesapTuru = document.getElementById('hesapTuru').value;
    const geciciGun = parseInt(document.getElementById('geciciGun').value) || 0;

    if (!dogumTarihi || !kazaTarihi) {
        showToast('error', 'Doğum ve kaza tarihi gereklidir.');
        return;
    }

    // Yaş hesapla
    const birthDate = new Date(dogumTarihi.split('.').reverse().join('-'));
    const accidentDate = new Date(kazaTarihi.split('.').reverse().join('-'));
    const age = Math.floor((accidentDate - birthDate) / (365.25 * 24 * 60 * 60 * 1000));

    // Basit PMF hesabı (60 yaşa kadar aktif)
    const aktivDonem = Math.max(0, 60 - age);
    const pmfDegeri = aktivDonem * 12 * 0.95; // Basitleştirilmiş

    let tazminat = 0;

    if (hesapTuru === 'gecici') {
        // Geçici iş göremezlik
        tazminat = (aylikGelir / 30) * geciciGun;
    } else {
        // Kalıcı maluliyet
        tazminat = aylikGelir * (maluliyetOrani / 100) * pmfDegeri;
    }

    // Kusur düş
    tazminat = tazminat * (1 - kusurOrani / 100);

    document.getElementById('sonucAlani').style.display = 'none';
    document.getElementById('sonucDetay').style.display = 'block';
    document.getElementById('sonucYas').textContent = age + ' yaş';
    document.getElementById('sonucAktif').textContent = aktivDonem + ' yıl';
    document.getElementById('sonucPmf').textContent = pmfDegeri.toFixed(2);
    document.getElementById('sonucToplam').textContent = formatMoney(tazminat);
});
</script>
