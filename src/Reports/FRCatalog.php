<?php
/*******************************************************************************
*
*  filename    : Reports/ConfirmReport.php
*  last change : 2003-08-30
*  description : Creates a PDF with all the confirmation letters asking member
*                families to verify the information in the database.

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReport;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\DonatedItemQuery;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\DonatedItemTableMap;

use Propel\Runtime\ActiveQuery\Criteria;

$iCurrentFundraiser = $_GET['CurrentFundraiser'];

$curY = 0;

class PDF_FRCatalogReport extends ChurchInfoReport
{
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->leftX = 10;
        $this->SetFont('Times', '', 10);
        $this->SetMargins(10, 20);

        $this->AddPage();
        $this->SetAutoPageBreak(true, 25);
    }

    public function AddPage($orientation = '', $format = '')
    {
        global $fr_title, $fr_description, $curY;

        parent::AddPage($orientation, $format);

        $this->SetFont('Times', 'B', 16);
        $this->Write(8, $fr_title."\n");
        $curY += 8;
        $this->Write(8, $fr_description."\n\n");
        $curY += 8;
        $this->SetFont('Times', '', 12);
    }
}

// Get the information about this fundraiser
$thisFRORM = FundRaiserQuery::create()->findOneById($iCurrentFundraiser);

$currency = OutputUtils::translate_currency_fpdf(SystemConfig::getValue("sCurrency"));

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

$sSQL = 'SELECT * FROM donateditem_di LEFT JOIN person_per on per_ID=di_donor_ID WHERE di_FR_ID='.$iCurrentFundraiser.
' ORDER BY SUBSTR(di_item,1,1),cast(SUBSTR(di_item,2) as unsigned integer),SUBSTR(di_item,4)';
$rsItems = RunQuery($sSQL);

$pdf = new PDF_FRCatalogReport();
$pdf->SetTitle(OutputUtils::translate_text_fpdf($thisFRORM->getTitle()));

// Loop through items
$idFirstChar = '';

foreach ($ormItems as $item) {
    $newIdFirstChar = OutputUtils::translate_text_fpdf(mb_substr($item->getItem(), 0, 1));
    $maxYNewPage = 220;
    if ($item->getPicture() != '') {
        $maxYNewPage = 120;
    }
    if ($pdf->GetY() > $maxYNewPage || ($idFirstChar != '' && $idFirstChar != $newIdFirstChar)) {
        $pdf->AddPage();
    }
    $idFirstChar = $newIdFirstChar;

    $pdf->SetFont('Times', 'B', 12);
    $pdf->Write(6, OutputUtils::translate_text_fpdf($item->getItem()).': ');
    $pdf->Write(6, OutputUtils::translate_text_fpdf(stripslashes($item->getTitle()))."\n");

    if ($item->getPicture() != '') {
        $s = getimagesize($item->getPicture());
        $h = (100.0 / $s[0]) * $s[1];
        $pdf->Image($item->getPicture(), $pdf->GetX(), $pdf->GetY(), 100.0, $h);
        $pdf->SetY($pdf->GetY() + $h);
    }

    $pdf->SetFont('Times', '', 12);
    $pdf->Write(6, OutputUtils::translate_text_fpdf(stripslashes($item->getDescription()))."\n");
    if ($item->getMinimum() > 0) {
        $pdf->Write(6, OutputUtils::translate_text_fpdf(_('Minimum bid ')).$currency.OutputUtils::money_localized($item->getMinimum()).'.  ');
    }
    if ($item->getEstPrice() > 0) {
        $pdf->Write(6, OutputUtils::translate_text_fpdf(_('Estimated value ')).$currency.OutputUtils::money_localized($item->getEstPrice()).'.  ');
    }
    if ($item->getLastName() != '') {
        $pdf->Write(6, OutputUtils::translate_text_fpdf(_('Donated by ')).OutputUtils::translate_text_fpdf($item->getFirstName().' '.$item->getLastName()).".\n");
    }
    $pdf->Write(6, "\n");
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('FRCatalog'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
