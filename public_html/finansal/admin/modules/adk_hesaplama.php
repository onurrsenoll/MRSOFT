<?php
/**
 * MR HASAR DANIŞMANLIK - Araç Değer Kaybı Hesaplama
 * HERZAMAN FARKEDER
 */

$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : 0;
$case = null;

if ($caseId) {
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
}

// Parça listesi
$parcalar = [
    'degisen' => [
        'on_tampon' => 'Ön Tampon',
        'arka_tampon' => 'Arka Tampon',
        'on_kaput' => 'Ön Kaput',
        'bagaj_kapagi' => 'Bagaj Kapağı',
        'sol_on_camurluk' => 'Sol Ön Çamurluk',
        'sag_on_camurluk' => 'Sağ Ön Çamurluk',
        'sol_arka_camurluk' => 'Sol Arka Çamurluk',
        'sag_arka_camurluk' => 'Sağ Arka Çamurluk',
        'sol_on_kapi' => 'Sol Ön Kapı',
        'sag_on_kapi' => 'Sağ Ön Kapı',
        'sol_arka_kapi' => 'Sol Arka Kapı',
        'sag_arka_kapi' => 'Sağ Arka Kapı',
        'tavan' => 'Tavan',
        'on_cam' => 'Ön Cam',
        'arka_cam' => 'Arka Cam',
        'sol_on_far' => 'Sol Ön Far',
        'sag_on_far' => 'Sağ Ön Far',
        'sol_arka_stop' => 'Sol Arka Stop',
        'sag_arka_stop' => 'Sağ Arka Stop',
        'sol_ayna' => 'Sol Ayna',
        'sag_ayna' => 'Sağ Ayna',
        'radyator' => 'Radyatör',
        'klima_kondansatoru' => 'Klima Kondansatörü',
    ],
    'yapisal' => [
        'sasi' => 'Şasi Hasarı',
        'motor' => 'Motor Hasarı',
        'sanziman' => 'Şanzıman Hasarı',
    ]
];

// Yaş katsayıları
$yasKatsayilari = [
    ['min' => 0, 'max' => 1, 'katsayi' => 1.00, 'label' => '0-1 Yaş'],
    ['min' => 1, 'max' => 2, 'katsayi' => 0.90, 'label' => '1-2 Yaş'],
    ['min' => 2, 'max' => 3, 'katsayi' => 0.80, 'label' => '2-3 Yaş'],
    ['min' => 3, 'max' => 4, 'katsayi' => 0.70, 'label' => '3-4 Yaş'],
    ['min' => 4, 'max' => 5, 'katsayi' => 0.60, 'label' => '4-5 Yaş'],
    ['min' => 5, 'max' => 6, 'katsayi' => 0.50, 'label' => '5-6 Yaş'],
    ['min' => 6, 'max' => 7, 'katsayi' => 0.40, 'label' => '6-7 Yaş'],
    ['min' => 7, 'max' => 8, 'katsayi' => 0.30, 'label' => '7-8 Yaş'],
    ['min' => 8, 'max' => 10, 'katsayi' => 0.20, 'label' => '8-10 Yaş'],
    ['min' => 10, 'max' => 999, 'katsayi' => 0.10, 'label' => '10+ Yaş'],
];

// Araç markaları
$vehicleBrands = getDropdownData($pdo, 'adk_vehicle_brands');
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-calculator me-2"></i>Araç Değer Kaybı Hesaplama</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                    <li class="breadcrumb-item active">ADK Hesaplama</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Hesaplama Formu -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-car-front me-2"></i>Araç ve Hasar Bilgileri
            </div>
            <div class="card-body">
                <form id="adkForm">
                    <div class="row g-3">
                        <!-- Araç Bilgileri -->
                        <div class="col-md-4">
                            <label class="form-label">Araç Marka</label>
                            <select class="form-select select2" name="vehicle_brand" id="vehicleBrand">
                                <option value="">Seçiniz</option>
                                <?php foreach ($vehicleBrands as $brand): ?>
                                <option value="<?= e($brand['name']) ?>" <?= ($case['vehicle_brand'] ?? '') === $brand['name'] ? 'selected' : '' ?>>
                                    <?= e($brand['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" name="vehicle_model" id="vehicleModel"
                                   value="<?= e($case['vehicle_model'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Model Yılı</label>
                            <select class="form-select" name="vehicle_year" id="vehicleYear">
                                <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                                <option value="<?= $y ?>" <?= ($case['vehicle_year'] ?? date('Y')) == $y ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Araç Rayiç Değeri (₺)</label>
                            <input type="number" class="form-control" name="rayic_deger" id="rayicDeger"
                                   placeholder="Örn: 500000" step="1000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kaza Tarihi</label>
                            <input type="text" class="form-control datepicker" name="kaza_tarihi" id="kazaTarihi"
                                   value="<?= $case && $case['accident_date'] ? formatDate($case['accident_date'], 'd.m.Y') : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kusur Oranı (%)</label>
                            <input type="number" class="form-control" name="kusur_orani" id="kusurOrani"
                                   value="<?= e($case['fault_rate'] ?? 0) ?>" min="0" max="100">
                        </div>
                    </div>

                    <!-- Değişen Parçalar -->
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="bi bi-tools me-2"></i>Değişen Parçalar</h6>
                        <div class="row g-2">
                            <?php foreach ($parcalar['degisen'] as $key => $label): ?>
                            <div class="col-md-3 col-6">
                                <div class="form-check">
                                    <input class="form-check-input parca-checkbox" type="checkbox"
                                           name="degisen[]" value="<?= $key ?>" id="d_<?= $key ?>" data-type="degisen">
                                    <label class="form-check-label small" for="d_<?= $key ?>"><?= $label ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Boyanan Parçalar -->
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="bi bi-palette me-2"></i>Boyanan Parçalar</h6>
                        <div class="row g-2">
                            <?php foreach ($parcalar['degisen'] as $key => $label): ?>
                            <div class="col-md-3 col-6">
                                <div class="form-check">
                                    <input class="form-check-input parca-checkbox" type="checkbox"
                                           name="boyanan[]" value="<?= $key ?>" id="b_<?= $key ?>" data-type="boyanan">
                                    <label class="form-check-label small" for="b_<?= $key ?>"><?= $label ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Yapısal Hasarlar -->
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Yapısal Hasarlar</h6>
                        <div class="row g-2">
                            <?php foreach ($parcalar['yapisal'] as $key => $label): ?>
                            <div class="col-md-3 col-6">
                                <div class="form-check">
                                    <input class="form-check-input parca-checkbox" type="checkbox"
                                           name="yapisal[]" value="<?= $key ?>" id="y_<?= $key ?>" data-type="yapisal">
                                    <label class="form-check-label small" for="y_<?= $key ?>"><?= $label ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tramer Kaydı -->
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="bi bi-file-text me-2"></i>Tramer Kaydı</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Önceki Tramer Kaydı Var mı?</label>
                                <select class="form-select" name="onceki_tramer" id="oncekiTramer">
                                    <option value="0">Hayır</option>
                                    <option value="1">Evet</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="tramerTutarDiv" style="display:none;">
                                <label class="form-label">Önceki Tramer Tutarı (₺)</label>
                                <input type="number" class="form-control" name="onceki_tramer_tutar" id="oncekiTramerTutar"
                                       step="1000" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="hesaplaBtn">
                            <i class="bi bi-calculator me-2"></i>Hesapla
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Temizle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Sonuç Kartı -->
        <div class="card sticky-top" style="top: 80px;">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard-data me-2"></i>Hesaplama Sonucu
            </div>
            <div class="card-body">
                <div id="sonucAlani" class="text-center py-4 text-muted">
                    <i class="bi bi-arrow-left-circle display-4"></i>
                    <p class="mt-3">Bilgileri doldurup "Hesapla" butonuna tıklayın.</p>
                </div>

                <div id="sonucDetay" style="display:none;">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted">Araç Rayiç Değeri:</td>
                            <td class="text-end" id="sonucRayic">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Araç Yaşı:</td>
                            <td class="text-end" id="sonucYas">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Yaş Katsayısı:</td>
                            <td class="text-end" id="sonucYasKatsayi">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Hasar Oranı:</td>
                            <td class="text-end" id="sonucHasarOrani">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kusur Oranı:</td>
                            <td class="text-end" id="sonucKusur">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tramer Düşümü:</td>
                            <td class="text-end" id="sonucTramer">-</td>
                        </tr>
                    </table>

                    <hr>

                    <div class="text-center">
                        <h5 class="text-muted mb-1">Hesaplanan ADK</h5>
                        <h2 class="text-primary mb-0" id="sonucToplam">₺0</h2>
                    </div>

                    <div class="mt-4 d-grid gap-2">
                        <?php if ($case): ?>
                        <button type="button" class="btn btn-success" id="kaydetBtn">
                            <i class="bi bi-save me-2"></i>Dosyaya Kaydet
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-primary" onclick="printElement('sonucDetay')">
                            <i class="bi bi-printer me-2"></i>Yazdır
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yaş Katsayı Tablosu -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-table me-2"></i>Yaş Katsayıları
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Araç Yaşı</th>
                            <th class="text-end">Katsayı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($yasKatsayilari as $yk): ?>
                        <tr>
                            <td><?= $yk['label'] ?></td>
                            <td class="text-end">%<?= $yk['katsayi'] * 100 ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const yasKatsayilari = <?= json_encode($yasKatsayilari) ?>;

// Tramer toggle
document.getElementById('oncekiTramer').addEventListener('change', function() {
    document.getElementById('tramerTutarDiv').style.display = this.value === '1' ? 'block' : 'none';
});

// Hesaplama
document.getElementById('hesaplaBtn').addEventListener('click', function() {
    const rayicDeger = parseFloat(document.getElementById('rayicDeger').value) || 0;
    const aracYili = parseInt(document.getElementById('vehicleYear').value) || new Date().getFullYear();
    const kusurOrani = parseFloat(document.getElementById('kusurOrani').value) || 0;
    const oncekiTramer = document.getElementById('oncekiTramer').value === '1';
    const oncekiTramerTutar = parseFloat(document.getElementById('oncekiTramerTutar').value) || 0;

    if (rayicDeger <= 0) {
        showToast('error', 'Lütfen araç rayiç değerini giriniz.');
        return;
    }

    // Araç yaşı hesapla
    const currentYear = new Date().getFullYear();
    const aracYasi = currentYear - aracYili;

    // Yaş katsayısı bul
    let yasKatsayi = 0.10;
    for (const yk of yasKatsayilari) {
        if (aracYasi >= yk.min && aracYasi < yk.max) {
            yasKatsayi = yk.katsayi;
            break;
        }
    }

    // Hasar oranı hesapla
    const degisenSayisi = document.querySelectorAll('input[name="degisen[]"]:checked').length;
    const boyananSayisi = document.querySelectorAll('input[name="boyanan[]"]:checked').length;
    const yapisalSayisi = document.querySelectorAll('input[name="yapisal[]"]:checked').length;

    // Hasar oranı formülü
    let hasarOrani = 0;
    hasarOrani += degisenSayisi * 0.02; // Her değişen parça %2
    hasarOrani += boyananSayisi * 0.01; // Her boyanan parça %1
    hasarOrani += yapisalSayisi * 0.05; // Her yapısal hasar %5

    hasarOrani = Math.min(hasarOrani, 0.30); // Maksimum %30

    // ADK hesapla
    let adk = rayicDeger * hasarOrani * yasKatsayi;

    // Kusur oranı düş
    adk = adk * (1 - kusurOrani / 100);

    // Tramer düşümü
    let tramerDusumu = 0;
    if (oncekiTramer && oncekiTramerTutar > 0) {
        tramerDusumu = oncekiTramerTutar * 0.1; // Önceki tramerin %10'u
        adk = adk - tramerDusumu;
    }

    adk = Math.max(adk, 0);

    // Sonuçları göster
    document.getElementById('sonucAlani').style.display = 'none';
    document.getElementById('sonucDetay').style.display = 'block';

    document.getElementById('sonucRayic').textContent = formatMoney(rayicDeger);
    document.getElementById('sonucYas').textContent = aracYasi + ' Yıl';
    document.getElementById('sonucYasKatsayi').textContent = '%' + (yasKatsayi * 100).toFixed(0);
    document.getElementById('sonucHasarOrani').textContent = '%' + (hasarOrani * 100).toFixed(1);
    document.getElementById('sonucKusur').textContent = '%' + kusurOrani;
    document.getElementById('sonucTramer').textContent = tramerDusumu > 0 ? formatMoney(tramerDusumu) : '-';
    document.getElementById('sonucToplam').textContent = formatMoney(adk);
});

<?php if ($case): ?>
// Dosyaya kaydet
document.getElementById('kaydetBtn')?.addEventListener('click', function() {
    const adk = parseFloat(document.getElementById('sonucToplam').textContent.replace(/[^\d,]/g, '').replace(',', '.')) || 0;

    fetch('index.php?page=adk_hesaplama&action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            case_id: <?= $caseId ?>,
            calculated_amount: adk,
            csrf_token: csrfToken
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Hesaplama dosyaya kaydedildi.');
        } else {
            showToast('error', data.message || 'Bir hata oluştu.');
        }
    });
});
<?php endif; ?>
</script>
