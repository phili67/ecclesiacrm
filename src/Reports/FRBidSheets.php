<?php
/*******************************************************************************
*
*  filename    : Reports/FRBidSheets.php
*  last change : 2003-08-30
*  description : Creates a PDF with a silent auction bid sheet for every item.

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\Reports\PDF_FRBidSheetsReport;

use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\DonatedItemQuery;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\DonatedItemTableMap;

use Propel\Runtime\ActiveQuery\Criteria;

$iCurrentFundraiser = $_GET['CurrentFundraiser'];

$currency = SystemConfig::getValue("sCurrency");

// Get the information about this fundraiser
$thisFRORM = FundRaiserQuery::create()->findOneById($iCurrentFundraiser);

$fundTitle = $thisFRORM->getTitle();
$fundDescription = $thisFRORM->getDescription();

// Get all the donated items
$ormItems = DonatedItemQuery::create()
    ->addJoin(DonatedItemTableMap::COL_DI_DONOR_ID, PersonTableMap::COL_PER_ID, Criteria::LEFT_JOIN)
    ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME)
    ->addAsColumn('LastName', PersonTableMap::COL_PER_LASTNAME)
    ->addAsColumn('cri1', 'SUBSTR('. DonatedItemTableMap::COL_DI_ITEM.',1,1)')
    ->addAsColumn('cri2', 'cast(SUBSTR('. DonatedItemTableMap::COL_DI_ITEM.',2) as unsigned integer)')
    ->addAsColumn('cri3', 'SUBSTR('. DonatedItemTableMap::COL_DI_ITEM.',4)')
    ->orderBy('cri1')
    ->orderBy('cri2')
    ->orderBy('cri3')
    ->findByFrId($iCurrentFundraiser);

$pdf = new PDF_FRBidSheetsReport($fundTitle, $fundDescription);
$pdf->SetTitle($thisFRORM->getTitle());

// Loop through items

foreach ($ormItems as $item) {
    $pdf->AddPage();

    $pdf->SetFont('Times', 'B', 24);
    $pdf->Write(5, $item->getItem().":\t");
    $pdf->Write(5, stripslashes($item->getTitle())."\n\n");
    $pdf->SetFont('Times', '', 16);
    $pdf->Write(8, stripslashes($item->getDescription())."\n");
    if ($item->getEstprice() > 0) {
        $pdf->Write(8, _('Estimated value ').$currency.OutputUtils::money_localized($item->getEstprice()).'.  ');
    }
    if ($item->getLastName() != '') {
        $pdf->Write(8, _('Donated by ').$item->getFirstName().' '.$item->getLastName().".\n");
    }
    $pdf->Write(8, "\n");

    $widName = 100;
    $widPaddle = 30;
    $widBid = 40;
    $lineHeight = 7;

    $pdf->SetFont('Times', 'B', 16);
    $pdf->Cell($widName, $lineHeight, _('Name'), 1, 0);
    $pdf->Cell($widPaddle, $lineHeight, _('Paddle'), 1, 0);
    $pdf->Cell($widBid, $lineHeight, _('Bid'), 1, 1);

    if ($item->getMinimum() > 0) {
        $pdf->Cell($widName, $lineHeight, '', 1, 0);
        $pdf->Cell($widPaddle, $lineHeight, '', 1, 0);
        $pdf->Cell($widBid, $lineHeight, $currency.OutputUtils::money_localized($item->getMinimum()), 1, 1);
    }
    for ($i = 0; $i < 20; $i += 1) {
        $pdf->Cell($widName, $lineHeight, '', 1, 0);
        $pdf->Cell($widPaddle, $lineHeight, '', 1, 0);
        $pdf->Cell($widBid, $lineHeight, '', 1, 1);
    }
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('FRBidSheets'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
