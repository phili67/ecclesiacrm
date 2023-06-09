<?php
/*******************************************************************************
*
*  filename    : Reports/FRCertificates.php
*  last change : 2003-08-30
*  description : Creates a PDF with a silent auction bid sheet for every item.

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\PDF_CertificatesReport;
use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\FundRaiserQuery;

use EcclesiaCRM\Map\DonatedItemTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\Utils\OutputUtils;

$iCurrentFundraiser = $_GET['CurrentFundraiser'];
$curY = 0;

// Get the information about this fundraiser
$thisFRORM = FundRaiserQuery::create()->findOneById($iCurrentFundraiser);

$fundTitle = $thisFRORM->getTitle();
$fundDescription = $thisFRORM->getDescription();


// Get all the donated items
$ormItems = DonatedItemQuery::create()
        ->addJoin(DonatedItemTableMap::COL_DI_DONOR_ID, PersonTableMap::COL_PER_ID, Criteria::LEFT_JOIN)
        ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME)
        ->addAsColumn('LastName', PersonTableMap::COL_PER_LASTNAME)
        ->orderByItem()
        ->findByFrId($iCurrentFundraiser);

$pdf = new PDF_CertificatesReport($fundTitle, $fundDescription);
$pdf->SetTitle($thisFRORM->getTitle());

$currency = SystemConfig::getValue("sCurrency");

foreach ($ormItems as $item) {
    $pdf->AddPage();

    $pdf->SetFont('Times', 'B', 24);
    $pdf->Write(8, $item->getItem().":\t");
    $pdf->Write(8, stripslashes($item->getTitle())."\n\n");
    $pdf->SetFont('Times', '', 16);
    $pdf->Write(8, stripslashes($item->getDescription())."\n");
    if ($item->getEstprice() > 0) {
        $pdf->Write(8, _('Estimated value ').$currency.OutputUtils::money_localized($item->getEstprice()).'.  ');
    }
    if ($item->getLastName() != '') {
        $pdf->Write(8, _('Donated by ').$item->getFirstName().' '.$item->getLastName().".\n\n");
    }
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('FRCertificates'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
