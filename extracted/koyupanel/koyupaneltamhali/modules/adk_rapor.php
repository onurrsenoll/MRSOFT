<?php
require_once '../config.php';
$pdo = getDB();
require_once 'tcpdf/tcpdf.php';

$hesaplama_id = intval($_GET['id'] ?? 0);
if ($hesaplama_id <= 0) { die('Hesaplama ID belirtilmedi!'); }

$stmt = $pdo->prepare("SELECT * FROM adk_calculations WHERE id = ?");
$stmt->execute([$hesaplama_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) { die('Hesaplama verisi bulunamadı!'); }

$dosya = null;
if (!empty($data['case_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$data['case_id']]);
    $dosya = $stmt->fetch(PDO::FETCH_ASSOC);
}

$premiumMarkalar = ['BMW', 'MERCEDES-BENZ', 'AUDI', 'PORSCHE', 'LEXUS', 'VOLVO', 'ALFA ROMEO', 'LAND ROVER', 'JAGUAR', 'MINI', 'TESLA', 'BENTLEY', 'ROLLS-ROYCE', 'FERRARI', 'LAMBORGHINI', 'MASERATI'];
$isPremium = in_array(strtoupper($data['marka']), $premiumMarkalar);
$aracYasi = date('Y') - intval($data['model_yili']);

function formatPara($num) { return number_format(floatval($num), 2, ',', '.') . ' TL'; }

class ADKPDF extends TCPDF {
    public function Header() {
        $this->SetFont('dejavusans', 'B', 20);
        $this->SetTextColor(59, 130, 246);
        $this->Cell(0, 10, 'MR HASAR DANISMANLIK', 0, 1, 'C');
        $this->SetFont('dejavusans', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Arac Deger Kaybi Analiz Raporu', 0, 1, 'C');
        $this->Ln(5);
        $this->SetDrawColor(59, 130, 246);
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

$pdf = new ADKPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('MR Hasar Danismanlik');
$pdf->SetAuthor('MR Hasar Danismanlik');
$pdf->SetTitle('Deger Kaybi Raporu - ' . $data['marka'] . ' ' . $data['model']);
$pdf->SetMargins(15, 45, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(true, 25);
$pdf->setFontSubsetting(true);
$pdf->AddPage();

$pdf->SetFont('dejavusans', 'B', 11);
$pdf->SetTextColor(50, 50, 50);
$raporNo = 'ADK-' . date('Y') . '-' . str_pad($data['id'], 6, '0', STR_PAD_LEFT);
$pdf->Cell(95, 8, 'Rapor No: ' . $raporNo, 0, 0, 'L');
$pdf->Cell(95, 8, 'Tarih: ' . date('d.m.Y', strtotime($data['created_at'])), 0, 1, 'R');
$pdf->Ln(5);

$pdf->SetFillColor(240, 245, 255);
$pdf->SetDrawColor(59, 130, 246);
$pdf->RoundedRect(15, $pdf->GetY(), 180, 45, 3, '1111', 'DF');
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetTextColor(59, 130, 246);
$pdf->Cell(180, 10, '  ARAC BILGILERI', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetTextColor(50, 50, 50);
$y = $pdf->GetY();

$pdf->SetXY(20, $y);
$pdf->Cell(40, 7, 'Marka/Model:', 0, 0, 'L');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(45, 7, $data['marka'] . ' ' . $data['model'] . ($isPremium ? ' (Premium)' : ''), 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetX(20);
$pdf->Cell(40, 7, 'Model Yili:', 0, 0, 'L');
$pdf->Cell(45, 7, $data['model_yili'] . ' (' . $aracYasi . ' yas)', 0, 1, 'L');
$pdf->SetX(20);
$pdf->Cell(40, 7, 'Kilometre:', 0, 0, 'L');
$pdf->Cell(45, 7, number_format(intval($data['kilometre']), 0, ',', '.') . ' km', 0, 1, 'L');

$pdf->SetXY(110, $y);
$pdf->Cell(40, 7, 'Plaka:', 0, 0, 'L');
$pdf->Cell(45, 7, $dosya['plaka'] ?? '-', 0, 1, 'L');
$pdf->SetXY(110, $y + 7);
$pdf->Cell(40, 7, 'Arac Degeri:', 0, 0, 'L');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(45, 7, formatPara($data['arac_degeri']), 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetXY(110, $y + 14);
$pdf->Cell(40, 7, 'Hasar Tutari:', 0, 0, 'L');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(45, 7, formatPara($data['hasar_tutari']), 0, 1, 'L');
$pdf->Ln(15);

$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetTextColor(59, 130, 246);
$pdf->Cell(0, 10, 'HASAR BILGILERI', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(60, 7, 'Kaza Tarihi:', 0, 0, 'L');
$pdf->Cell(60, 7, $data['kaza_tarihi'] ? date('d.m.Y', strtotime($data['kaza_tarihi'])) : '-', 0, 1, 'L');
$pdf->Cell(60, 7, 'Tramer Kaydi:', 0, 0, 'L');
$pdf->Cell(60, 7, $data['tramer_kaydi'] ?? '-', 0, 1, 'L');
$pdf->Ln(10);

$pdf->SetFillColor(16, 185, 129);
$pdf->SetDrawColor(16, 185, 129);
$pdf->RoundedRect(15, $pdf->GetY(), 180, 30, 3, '1111', 'DF');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(180, 10, 'TESPIT EDILEN DEGER KAYBI', 0, 1, 'C');
$pdf->SetFont('dejavusans', 'B', 24);
$pdf->Cell(180, 15, formatPara($data['deger_kaybi']), 0, 1, 'C');
$pdf->Ln(15);

$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'ACIKLAMALAR', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(180, 5, "1. Bu rapor, Yargitay ictihatlari ve Sigorta Tahkim Komisyonu kararlari dogrultusunda hazirlanmistir.\n\n2. Deger kaybi hesabi; arac degeri, hasar tutari, kilometre, model yili ve diger faktorler dikkate alinarak yapilmistir.\n\n3. Bu rapor bilgilendirme amaclidir ve kesin hukuki sonuc dogurmaz.", 0, 'L');
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

$pdf->Output('ADK_Rapor_' . $data['marka'] . '_' . $data['model'] . '_' . date('Ymd') . '.pdf', 'I');