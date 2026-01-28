<?php
require_once '../config.php';
$pdo = getDB();
require_once 'tcpdf/tcpdf.php';

$hesaplama_id = intval($_GET['id'] ?? 0);
if ($hesaplama_id <= 0) { die('Hesaplama ID belirtilmedi!'); }

$stmt = $pdo->prepare("SELECT * FROM bh_calculations WHERE id = ?");
$stmt->execute([$hesaplama_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) { die('Hesaplama verisi bulunamadı!'); }

$dosya = null;
if (!empty($data['case_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$data['case_id']]);
    $dosya = $stmt->fetch(PDO::FETCH_ASSOC);
}

$dogum = new DateTime($data['dogum_tarihi']);
$kaza = new DateTime($data['kaza_tarihi']);
$yas = $kaza->diff($dogum)->y;

function formatPara($num) { return number_format(floatval($num), 2, ',', '.') . ' TL'; }

class BHPDF extends TCPDF {
    public function Header() {
        $this->SetFont('dejavusans', 'B', 20);
        $this->SetTextColor(16, 185, 129);
        $this->Cell(0, 10, 'MR HASAR DANISMANLIK', 0, 1, 'C');
        $this->SetFont('dejavusans', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Bedeni Hasar Tazminat Hesaplama Raporu', 0, 1, 'C');
        $this->Ln(5);
        $this->SetDrawColor(16, 185, 129);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(10);
    }
    public function Footer() {
        $this->SetY(-20);
        $this->SetFont('dejavusans', '', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 5, 'Bu rapor MR Hasar Danismanlik sistemi tarafindan olusturulmustur.', 0, 1, 'C');
        $this->Cell(0, 5, 'Sayfa ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . ' | ' . date('d.m.Y H:i'), 0, 0, 'C');
    }
}

$pdf = new BHPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('MR Hasar Danismanlik');
$pdf->SetAuthor('MR Hasar Danismanlik');
$pdf->SetTitle('Bedeni Hasar Raporu');
$pdf->SetMargins(15, 45, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(true, 25);
$pdf->setFontSubsetting(true);
$pdf->AddPage();

$pdf->SetFont('dejavusans', 'B', 11);
$pdf->SetTextColor(50, 50, 50);
$raporNo = 'BH-' . date('Y') . '-' . str_pad($data['id'], 6, '0', STR_PAD_LEFT);
$pdf->Cell(95, 8, 'Rapor No: ' . $raporNo, 0, 0, 'L');
$pdf->Cell(95, 8, 'Tarih: ' . date('d.m.Y', strtotime($data['created_at'])), 0, 1, 'R');
$pdf->Ln(5);

// DOSYA BİLGİLERİ
if ($dosya) {
    $pdf->SetFillColor(240, 255, 250);
    $pdf->SetDrawColor(16, 185, 129);
    $pdf->RoundedRect(15, $pdf->GetY(), 180, 25, 3, '1111', 'DF');
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->SetTextColor(16, 185, 129);
    $pdf->Cell(180, 10, '  DOSYA BILGILERI', 0, 1, 'L');
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->SetX(20);
    $pdf->Cell(40, 7, 'Dosya No:', 0, 0, 'L');
    $pdf->Cell(60, 7, $dosya['dosya_no'] ?? '-', 0, 0, 'L');
    $pdf->Cell(30, 7, 'Magdur:', 0, 0, 'L');
    $pdf->Cell(50, 7, $dosya['ad_soyad'] ?? '-', 0, 1, 'L');
    $pdf->Ln(10);
}

// KİŞİ BİLGİLERİ
$pdf->SetFillColor(240, 255, 250);
$pdf->SetDrawColor(16, 185, 129);
$pdf->RoundedRect(15, $pdf->GetY(), 180, 35, 3, '1111', 'DF');
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetTextColor(16, 185, 129);
$pdf->Cell(180, 10, '  KISI BILGILERI', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetTextColor(50, 50, 50);
$y = $pdf->GetY();

$pdf->SetXY(20, $y);
$pdf->Cell(40, 7, 'Dogum Tarihi:', 0, 0, 'L');
$pdf->Cell(45, 7, date('d.m.Y', strtotime($data['dogum_tarihi'])), 0, 1, 'L');
$pdf->SetX(20);
$pdf->Cell(40, 7, 'Kaza Tarihi:', 0, 0, 'L');
$pdf->Cell(45, 7, date('d.m.Y', strtotime($data['kaza_tarihi'])), 0, 1, 'L');

$pdf->SetXY(110, $y);
$pdf->Cell(40, 7, 'Kaza Anindaki Yas:', 0, 0, 'L');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(45, 7, $yas . ' yas', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetXY(110, $y + 7);
$pdf->Cell(40, 7, 'Maluliyet Orani:', 0, 0, 'L');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->SetTextColor(220, 38, 38);
$pdf->Cell(45, 7, '%' . $data['maluliyet_orani'], 0, 1, 'L');
$pdf->SetTextColor(50, 50, 50);
$pdf->Ln(10);

// GELİR BİLGİLERİ
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetTextColor(16, 185, 129);
$pdf->Cell(0, 10, 'GELIR BILGILERI', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(60, 7, 'Gelir:', 0, 0, 'L');
$pdf->Cell(60, 7, formatPara($data['gelir']) . ' (' . ($data['gelir_turu'] == 'aylik' ? 'Aylik' : 'Yillik') . ')', 0, 1, 'L');
$pdf->Cell(60, 7, 'Kusur Orani:', 0, 0, 'L');
$pdf->Cell(60, 7, '%' . $data['kusur_orani'], 0, 1, 'L');
$pdf->Cell(60, 7, 'Gecici Is Goremezlik:', 0, 0, 'L');
$pdf->Cell(60, 7, $data['gecici_is_goremezlik_gun'] . ' gun', 0, 1, 'L');
$pdf->Ln(10);

// HESAPLAMA DETAYLARI
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetTextColor(16, 185, 129);
$pdf->Cell(0, 10, 'HESAPLAMA DETAYLARI', 0, 1, 'L');

$pdf->SetFillColor(16, 185, 129);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(120, 8, 'Kalem', 1, 0, 'C', true);
$pdf->Cell(60, 8, 'Tutar', 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252);
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('dejavusans', '', 10);

$pdf->Cell(120, 8, 'Aktif Donem Tazminati', 1, 0, 'L', true);
$pdf->Cell(60, 8, formatPara($data['aktif_donem']), 1, 1, 'R', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(120, 8, 'Pasif Donem Tazminati', 1, 0, 'L', true);
$pdf->Cell(60, 8, formatPara($data['pasif_donem']), 1, 1, 'R', true);

$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(120, 8, 'Gecici Is Goremezlik Tazminati', 1, 0, 'L', true);
$pdf->Cell(60, 8, formatPara($data['gecici_is_goremezlik']), 1, 1, 'R', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(120, 8, 'Bakim Tazminati', 1, 0, 'L', true);
$pdf->Cell(60, 8, formatPara($data['bakim_tazminati']), 1, 1, 'R', true);

$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(120, 8, 'Tedavi Giderleri', 1, 0, 'L', true);
$pdf->Cell(60, 8, formatPara($data['tedavi_gideri']), 1, 1, 'R', true);

$pdf->Ln(10);

// SONUÇ KUTUSU
$pdf->SetFillColor(16, 185, 129);
$pdf->SetDrawColor(16, 185, 129);
$pdf->RoundedRect(15, $pdf->GetY(), 180, 30, 3, '1111', 'DF');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(180, 10, 'TOPLAM TAZMINAT', 0, 1, 'C');
$pdf->SetFont('dejavusans', 'B', 24);
$pdf->Cell(180, 15, formatPara($data['toplam_tazminat']), 0, 1, 'C');
$pdf->Ln(15);

// AÇIKLAMALAR
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'ACIKLAMALAR', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(180, 5, "1. Bu rapor, Yargitay ictihatlari ve Sigorta Tahkim Komisyonu kararlari dogrultusunda hazirlanmistir.\n\n2. Aktif donem hesabi 60 yasina kadar, pasif donem hesabi kalan omur uzerinden yapilmistir.\n\n3. PMF (TRH-2010) yasam tablosu kullanilmistir.\n\n4. Bu rapor bilgilendirme amaclidir ve kesin hukuki sonuc dogurmaz.", 0, 'L');
$pdf->Ln(15);

$pdf->SetFont('dejavusans', '', 10);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(90, 8, 'Hazirlayan:', 0, 0, 'L');
$pdf->Cell(90, 8, 'Onaylayan:', 0, 1, 'L');
$pdf->Ln(15);
$pdf->Cell(90, 8, '............................', 0, 0, 'C');
$pdf->Cell(90, 8, '............................', 0, 1, 'C');
$pdf->Cell(90, 5, 'MR Hasar Danismanlik', 0, 0, 'C');
$pdf->Cell(90, 5, 'Yetkili Imza', 0, 1, 'C');

$pdf->Output('BH_Rapor_' . $raporNo . '_' . date('Ymd') . '.pdf', 'I');