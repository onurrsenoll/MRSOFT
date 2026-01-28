<?php
/**
 * MR HASAR DANIŞMANLIK - ADK RAPOR PDF OLUŞTURMA
 * Profesyonel PDF Rapor Üretimi
 */

require_once __DIR__ . '/../../config.php';
startSecureSession();

// PDF kütüphanesi yoksa basit HTML-to-PDF çözümü
// Gerçek projede TCPDF veya DOMPDF kullanılabilir

$hesaplama_id = intval($_GET['id'] ?? 0);
$rapor_tipi = $_GET['tip'] ?? 'ozet'; // ozet veya detayli

if ($hesaplama_id == 0) {
    die('Geçersiz hesaplama ID');
}

try {
    $db = getDB();

    // Hesaplama bilgilerini al
    $stmt = $db->prepare("SELECT * FROM adk_hesaplamalar WHERE id = ?");
    $stmt->execute([$hesaplama_id]);
    $hesaplama = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hesaplama) {
        die('Hesaplama bulunamadı');
    }

    // Dosya bilgisini al (varsa)
    $case = null;
    if ($hesaplama['case_id']) {
        $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$hesaplama['case_id']]);
        $case = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Parçaları decode et
    $parcalar = json_decode($hesaplama['parcalar'], true) ?: [];

    // Şirket bilgileri
    $sirket = [
        'ad' => 'MR HASAR DANIŞMANLIK',
        'adres' => 'İstanbul, Türkiye',
        'telefon' => '+90 XXX XXX XX XX',
        'email' => 'info@mrhasar.com',
        'web' => 'www.mrhasar.com'
    ];

    // PDF Header
    header('Content-Type: text/html; charset=utf-8');

    // Print-friendly CSS ile HTML rapor
    ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADK Rapor - <?= htmlspecialchars($hesaplama['rapor_no']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #1a237e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1a237e;
        }

        .logo span {
            color: #1976d2;
        }

        .rapor-info {
            text-align: right;
        }

        .rapor-no {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
        }

        .rapor-tarih {
            color: #666;
        }

        /* Başlık */
        .title {
            text-align: center;
            margin: 30px 0;
        }

        .title h1 {
            font-size: 20px;
            color: #1a237e;
            margin-bottom: 5px;
        }

        .title h2 {
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }

        /* Bölümler */
        .section {
            margin-bottom: 25px;
        }

        .section-title {
            background: linear-gradient(135deg, #1a237e 0%, #1976d2 100%);
            color: #fff;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        /* Tablolar */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f5f5f5;
            font-weight: 600;
            color: #333;
            width: 40%;
        }

        td {
            color: #555;
        }

        /* Sonuç kutusu */
        .result-box {
            background: linear-gradient(135deg, #1a237e 0%, #1976d2 100%);
            color: #fff;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }

        .result-box .label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .result-box .value {
            font-size: 32px;
            font-weight: bold;
        }

        .result-box .percentage {
            font-size: 16px;
            margin-top: 10px;
            opacity: 0.9;
        }

        /* Katsayılar */
        .katsayi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .katsayi-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .katsayi-item .name {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .katsayi-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #1a237e;
        }

        /* Parçalar listesi */
        .parcalar-list {
            list-style: none;
        }

        .parcalar-list li {
            padding: 8px 12px;
            background: #f8f9fa;
            margin-bottom: 5px;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
        }

        .parcalar-list .islem {
            color: #1976d2;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        .footer .company {
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 5px;
        }

        /* Yasal uyarı */
        .legal {
            background: #fff8e1;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-top: 25px;
            font-size: 11px;
            color: #795548;
        }

        .legal-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Print styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        /* Print button */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1a237e;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .print-btn:hover {
            background: #1976d2;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Yazdır / PDF</button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">MR <span>HASAR</span> DANIŞMANLIK</div>
            <div class="rapor-info">
                <div class="rapor-no"><?= htmlspecialchars($hesaplama['rapor_no']) ?></div>
                <div class="rapor-tarih"><?= date('d.m.Y H:i', strtotime($hesaplama['rapor_tarihi'])) ?></div>
            </div>
        </div>

        <!-- Başlık -->
        <div class="title">
            <h1>ARAÇ DEĞER KAYBI HESAPLAMA RAPORU</h1>
            <h2><?= $rapor_tipi == 'detayli' ? 'Detaylı Analiz Raporu' : 'Özet Rapor' ?></h2>
        </div>

        <!-- Araç Bilgileri -->
        <div class="section">
            <div class="section-title">ARAÇ BİLGİLERİ</div>
            <table>
                <tr>
                    <th>Marka / Model</th>
                    <td><?= htmlspecialchars($hesaplama['marka'] . ' ' . $hesaplama['model']) ?></td>
                </tr>
                <tr>
                    <th>Model Yılı</th>
                    <td><?= htmlspecialchars($hesaplama['model_yili']) ?></td>
                </tr>
                <tr>
                    <th>Kilometre</th>
                    <td><?= number_format($hesaplama['kilometre'], 0, ',', '.') ?> km</td>
                </tr>
                <?php if ($hesaplama['plaka']): ?>
                <tr>
                    <th>Plaka</th>
                    <td><?= htmlspecialchars($hesaplama['plaka']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($hesaplama['sasi_no']): ?>
                <tr>
                    <th>Şasi No</th>
                    <td><?= htmlspecialchars($hesaplama['sasi_no']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Değer Bilgileri -->
        <div class="section">
            <div class="section-title">DEĞER BİLGİLERİ</div>
            <table>
                <tr>
                    <th>Araç Piyasa Değeri</th>
                    <td><strong><?= number_format($hesaplama['arac_degeri'], 2, ',', '.') ?> ₺</strong></td>
                </tr>
                <tr>
                    <th>Hasar Tutarı</th>
                    <td><?= number_format($hesaplama['hasar_tutari'], 2, ',', '.') ?> ₺</td>
                </tr>
                <tr>
                    <th>Geçmiş Hasar Kaydı</th>
                    <td><?= $hesaplama['gecmis_hasar'] == 'VAR' ? 'Var' : 'Yok' ?></td>
                </tr>
                <tr>
                    <th>Kusur Oranı</th>
                    <td>%<?= $hesaplama['kusur_orani'] ?></td>
                </tr>
            </table>
        </div>

        <!-- Sonuç Kutusu -->
        <div class="result-box">
            <div class="label">HESAPLANAN DEĞER KAYBI</div>
            <div class="value"><?= number_format($hesaplama['deger_kaybi'], 2, ',', '.') ?> ₺</div>
            <div class="percentage">Araç değerinin %<?= number_format($hesaplama['deger_kaybi_orani'], 2, ',', '.') ?>'si</div>
        </div>

        <?php if ($rapor_tipi == 'detayli'): ?>
        <!-- Detaylı Analiz -->
        <div class="section">
            <div class="section-title">HESAPLAMA KATSAYILARI</div>
            <div class="katsayi-grid">
                <div class="katsayi-item">
                    <div class="name">Yaş Katsayısı</div>
                    <div class="value"><?= number_format($hesaplama['yas_katsayi'], 3, ',', '.') ?></div>
                </div>
                <div class="katsayi-item">
                    <div class="name">KM Katsayısı</div>
                    <div class="value"><?= number_format($hesaplama['km_katsayi'], 3, ',', '.') ?></div>
                </div>
                <div class="katsayi-item">
                    <div class="name">Hasar Katsayısı</div>
                    <div class="value"><?= number_format($hesaplama['hasar_katsayi'], 3, ',', '.') ?></div>
                </div>
                <div class="katsayi-item">
                    <div class="name">Parça Katsayısı</div>
                    <div class="value"><?= number_format($hesaplama['parca_katsayi'], 3, ',', '.') ?></div>
                </div>
                <div class="katsayi-item">
                    <div class="name">Geçmiş Hasar K.</div>
                    <div class="value"><?= number_format($hesaplama['gecmis_hasar_katsayi'], 3, ',', '.') ?></div>
                </div>
                <div class="katsayi-item">
                    <div class="name">Toplam Katsayı</div>
                    <div class="value"><?= number_format($hesaplama['yas_katsayi'] * $hesaplama['km_katsayi'] * $hesaplama['hasar_katsayi'] * $hesaplama['parca_katsayi'] * $hesaplama['gecmis_hasar_katsayi'], 3, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <?php if (!empty($parcalar)): ?>
        <div class="section">
            <div class="section-title">HASARLI PARÇALAR</div>
            <ul class="parcalar-list">
                <?php foreach ($parcalar as $parca): ?>
                <li>
                    <span><?= htmlspecialchars($parca['parca'] ?? 'Parça') ?></span>
                    <span class="islem"><?= htmlspecialchars(ucfirst($parca['islem'] ?? 'Onarım')) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-title">HESAPLAMA METODOLOJİSİ</div>
            <table>
                <tr>
                    <th>Hesaplama Yöntemi</th>
                    <td>TBK/YARGITAY Metodolojisi</td>
                </tr>
                <tr>
                    <th>Temel Formül</th>
                    <td>Değer Kaybı = Araç Değeri × Temel Oran × Toplam Katsayı × Kusur Oranı</td>
                </tr>
                <tr>
                    <th>Temel Oran</th>
                    <td>%8 (Yargıtay içtihatları doğrultusunda)</td>
                </tr>
                <tr>
                    <th>Değer Kaybı Aralığı</th>
                    <td>%2 - %25 (Yasal limitler dahilinde)</td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <!-- Yasal Uyarı -->
        <div class="legal">
            <div class="legal-title">⚠️ Yasal Uyarı</div>
            <p>Bu rapor, TBK (Türk Borçlar Kanunu) ve Yargıtay içtihatları doğrultusunda hazırlanmıştır.
            Hesaplanan değer kaybı tutarı tahmini niteliktedir ve kesin bir taahhüt içermez.
            Nihai değer kaybı tutarı, sigorta şirketi ekspertizi veya mahkeme bilirkişi raporu ile belirlenecektir.
            Bu rapor sadece bilgilendirme amaçlıdır ve hukuki belge niteliği taşımaz.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="company"><?= htmlspecialchars($sirket['ad']) ?></div>
            <div><?= htmlspecialchars($sirket['adres']) ?></div>
            <div>Tel: <?= htmlspecialchars($sirket['telefon']) ?> | <?= htmlspecialchars($sirket['email']) ?></div>
            <div style="margin-top: 10px; color: #999;">Bu rapor <?= date('d.m.Y H:i') ?> tarihinde oluşturulmuştur.</div>
        </div>
    </div>

    <script>
        // Sayfa yüklendiğinde otomatik yazdırma dialogu açılabilir
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
    <?php

} catch (Exception $e) {
    die('Rapor oluşturma hatası: ' . $e->getMessage());
}
