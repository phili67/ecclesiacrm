<?php
/*******************************************************************************
*
*  filename    : Reports/FundRaiserStatement.php
*  last change : 2009-04-17
*  description : Creates a PDF with one or more fund raiser statements
*  copyright   : Copyright 2009 Michael Wilt

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReport;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;

use Propel\Runtime\Propel;

$iPaddleNumID = InputUtils::LegacyFilterInputArr($_GET, 'PaddleNumID', 'int');
$iFundRaiserID = $_SESSION['iCurrentFundraiser'];

//Get the paddlenum records for this fundraiser
if ($iPaddleNumID > 0) {
    $selectOneCrit = ' AND pn_ID='.$iPaddleNumID.' ';
} else {
    $selectOneCrit = '';
}


$sSQL = 'SELECT pn_ID, pn_fr_ID, pn_Num, pn_per_ID,
                a.per_FirstName as paddleFirstName, a.per_LastName as paddleLastName, a.per_Email as paddleEmail,
				b.fam_ID, b.fam_Name, b.fam_Address1, b.fam_Address2, b.fam_City, b.fam_State, b.fam_Zip, b.fam_Country
         FROM paddlenum_pn
         LEFT JOIN person_per a ON pn_per_ID=a.per_ID
         LEFT JOIN family_fam b ON fam_ID = a.per_fam_ID
         WHERE pn_FR_ID ='.$iFundRaiserID.$selectOneCrit.' ORDER BY pn_Num';

$connection = Propel::getConnection();

$ormPaddleNums = $connection->prepare($sSQL);
$ormPaddleNums->execute();

class PDF_FundRaiserStatement extends ChurchInfoReport
{
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->SetFont('Times', '', 10);
        $this->SetMargins(20, 20);

        $this->SetAutoPageBreak(false);
    }

    public function StartNewPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
    {
        global $letterhead;
        $curY = $this->StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $letterhead);

        return $curY;
    }

    public function FinishPage($curY)
    {
    }

    public function CellWithWrap($curY, $curNewY, $ItemWid, $tableCellY, $txt, $bdr, $aligncode)
    {
        $curPage = $this->PageNo();
        $leftX = $this->GetX();
        $this->SetXY($leftX, $curY);
        $this->MultiCell($ItemWid, $tableCellY, $txt, $bdr, $aligncode);
        $newY = $this->GetY();
        $newPage = $this->PageNo();
        $this->SetXY($leftX + $ItemWid, $curY);
        if ($newPage > $curPage) {
            return $newY;
        } else {
            return max($newY, $curNewY);
        }
    }
}

// Instantiate the directory class and build the report.
$pdf = new PDF_FundRaiserStatement();

$currency = OutputUtils::translate_currency_fpdf(SystemConfig::getValue("sCurrency"));

// Loop through result array
while ($row = $ormPaddleNums->fetch( \PDO::FETCH_ASSOC )) {
    if ($iPaddleNumID || isset($_POST["Chk".$row['pn_ID']])) {
        $curY = $pdf->StartNewPage($row['fam_ID'], $row['fam_Name'], $row['fam_Address1'], $row['fam_Address2'], $row['fam_City'], $row['fam_State'], $row['fam_Zip'], $row['fam_Country']);

        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Donated Items:'));
        $curY += 2 * SystemConfig::getValue('incrementY');

        $ItemWid = 10;
        $QtyWid = 10;
        $TitleWid = 50;
        $DonorWid = 30;
        $EmailWid = 40;
        $PhoneWid = 24;
        $PriceWid = 20;
        $tableCellY = 4;

        // Get donated items and make the table
        $sSQL = 'SELECT di_item, di_title, di_buyer_id, di_sellprice,
		                a.per_FirstName as buyerFirstName,
		                a.per_LastName as buyerLastName,
		                a.per_Email as buyerEmail,
		                b.fam_homephone as buyerPhone
		                FROM donateditem_di LEFT JOIN person_per a on a.per_ID = di_buyer_id
		                                    LEFT JOIN family_fam b on a.per_fam_id = b.fam_id
		                WHERE di_FR_ID = '.$iFundRaiserID;//.' AND di_donor_id = '.$row['pn_per_ID'];

        $ormDonatedItems = $connection->prepare($sSQL);
        $ormDonatedItems->execute();

        $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
        $pdf->SetFont('Times', 'B', 10);

        $pdf->Cell($ItemWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Item')));
        $pdf->Cell($TitleWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Name')));
        $pdf->Cell($DonorWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Buyer')));
        $pdf->Cell($PhoneWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Phone')));
        $pdf->Cell($EmailWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Email')));
        $pdf->Cell($PriceWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Amount')), 0, 1, 'R');
        $curY = $pdf->GetY();
        $pdf->SetFont('Times', '', 10);

        while ($itemRow = $ormDonatedItems->fetch( \PDO::FETCH_ASSOC )) {
            $nextY = $curY;
            $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
            $nextY = $pdf->CellWithWrap($curY, $nextY, $ItemWid, $tableCellY, OutputUtils::translate_text_fpdf($itemRow['di_item']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $TitleWid, $tableCellY, OutputUtils::translate_text_fpdf($itemRow['di_title']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $DonorWid, $tableCellY, OutputUtils::translate_text_fpdf($itemRow['buyerFirstName'].' '.$itemRow['buyerLastName']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $PhoneWid, $tableCellY, $itemRow['buyerPhone'], 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $EmailWid, $tableCellY, $itemRow['buyerEmail'], 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $PriceWid, $tableCellY, $currency.OutputUtils::money_localized($itemRow['di_sellprice']), 0, 'R');
            $curY = $nextY;
        }

        // Get purchased items and make the table
        $curY += 2 * $tableCellY;
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Purchased Items:'));
        $curY += 2 * SystemConfig::getValue('incrementY');

        $totalAmount = 0.0;

        // Get individual auction items first
        $sSQL = 'SELECT di_item, di_title, di_donor_id, di_sellprice,
		                a.per_FirstName as donorFirstName,
		                a.per_LastName as donorLastName,
		                a.per_Email as donorEmail,
		                b.fam_homePhone as donorPhone
		                FROM donateditem_di LEFT JOIN person_per a on a.per_ID = di_donor_id
		                                    LEFT JOIN family_fam b on a.per_fam_id=b.fam_id
		                WHERE di_FR_ID = '.$iFundRaiserID.' AND di_buyer_id = '.$row['pn_per_ID'];
        $ormPurchasedItems = $connection->prepare($sSQL);
        $ormPurchasedItems->execute();

        $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell($ItemWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Item')));
        $pdf->Cell($QtyWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Qty')));
        $pdf->Cell($TitleWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Name')));
        $pdf->Cell($DonorWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Donor')));
        $pdf->Cell($PhoneWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Phone')));
        $pdf->Cell($EmailWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Email')));
        $pdf->Cell($PriceWid, $tableCellY, OutputUtils::translate_text_fpdf(_('Amount')), 0, 1, 'R');
        $pdf->SetFont('Times', '', 10);
        $curY += SystemConfig::getValue('incrementY');

        while ($itemRow = $ormPurchasedItems->fetch( \PDO::FETCH_ASSOC )) {
            $nextY = $curY;
            $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
            $nextY = $pdf->CellWithWrap($curY, $nextY, $ItemWid, $tableCellY, OutputUtils::translate_text_fpdf($itemRow['di_item']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $QtyWid, $tableCellY, '1', 0, 'L'); // quantity 1 for all individual items
            $nextY = $pdf->CellWithWrap($curY, $nextY, $TitleWid, $tableCellY, OutputUtils::translate_text_fpdf($itemRow['di_title']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $DonorWid, $tableCellY, OutputUtils::translate_text_fpdf($itemRow['donorFirstName'].' '.$itemRow['donorLastName']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $PhoneWid, $tableCellY, $itemRow['donorPhone'], 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $EmailWid, $tableCellY, $itemRow['donorEmail'], 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $PriceWid, $tableCellY, $currency.OutputUtils::money_localized($itemRow['di_sellprice']), 0, 'R');
            $curY = $nextY;
            $totalAmount += $itemRow['di_sellprice'];
        }

        // Get multibuy items for this buyer
        $sqlMultiBuy = 'SELECT mb_count, mb_item_ID,
		                a.per_FirstName as donorFirstName,
		                a.per_LastName as donorLastName,
		                a.per_Email as donorEmail,
		                c.fam_HomePhone as donorPhone,
						b.di_item, b.di_title, b.di_donor_id, b.di_sellprice
						FROM multibuy_mb
						LEFT JOIN donateditem_di b ON mb_item_ID=b.di_ID
						LEFT JOIN person_per a ON b.di_donor_id=a.per_ID
						LEFT JOIN family_fam c ON a.per_fam_id = c.fam_ID
						WHERE b.di_FR_ID='.$iFundRaiserID.' AND mb_per_ID='.$row['pn_per_ID'];
        $ormMultiBuy = $connection->prepare($sqlMultiBuy);
        $ormMultiBuy->execute();

        while ($mbRow = $ormMultiBuy->fetch( \PDO::FETCH_ASSOC )) {
            $nextY = $curY;
            $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
            $nextY = $pdf->CellWithWrap($curY, $nextY, $ItemWid, $tableCellY, OutputUtils::translate_text_fpdf($mbRow['di_item']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $QtyWid, $tableCellY, $mbRow['mb_count'], 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $TitleWid, $tableCellY, stripslashes($mbRow['di_title']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $DonorWid, $tableCellY, OutputUtils::translate_text_fpdf($mbRow['donorFirstName'].' '.$mbRow['donorLastName']), 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $PhoneWid, $tableCellY, $mbRow['donorPhone'], 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $EmailWid, $tableCellY, $mbRow['donorEmail'], 0, 'L');
            $nextY = $pdf->CellWithWrap($curY, $nextY, $PriceWid, $tableCellY, $currency.OutputUtils::money_localized($mbRow['mb_count'] * $mbRow['di_sellprice']), 0, 'R');
            $curY = $nextY;
            $totalAmount += $mbRow['mb_count'] * $mbRow['di_sellprice'];
        }

        // Report total purchased items
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, (_('Total of all purchases: $').$totalAmount));
        $curY += 2 * SystemConfig::getValue('incrementY');

        // Make the tear-off record for the bottom of the page
        $curY = 240;
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('-----------------------------------------------------------------------------------------------------------------------------------------------'));
        $curY += 2 * SystemConfig::getValue('incrementY');
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, (_('Buyer # ').$row['pn_Num'].' : '.$row['paddleFirstName'].' '.$row['paddleLastName'].' : '._('Total purchases: $').$totalAmount.' : '._('Amount paid: ________________')));
        $curY += 2 * SystemConfig::getValue('incrementY');
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Paid by (  ) Cash    (  ) Check    (  ) Credit card __ __ __ __    __ __ __ __    __ __ __ __    __ __ __ __  Exp __ / __'));
        $curY += 2 * SystemConfig::getValue('incrementY');
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('                                        Signature ________________________________________________________________'));

        $pdf->FinishPage($curY);
    }
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
$pdf->Output('FundRaiserStatement'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
