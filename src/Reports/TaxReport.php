<?php
/*******************************************************************************
*
*  filename    : Reports/TaxReport.php
*  last change : 2005-03-26
*  description : Creates a PDF with all the tax letters for a particular calendar year.

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\DepositQuery;
use Propel\Runtime\Propel;

// Security
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$year = -1;

if (isset($_GET['Year'])) {
    $year = $_GET['Year'];
}

$delimiter = SessionUser::getUser()->CSVExportDelemiter();
$charset   = SessionUser::getUser()->CSVExportCharset();

// Filter values
$letterhead = InputUtils::LegacyFilterInput($_POST['letterhead']);
$remittance = InputUtils::LegacyFilterInput($_POST['remittance']);
$output = InputUtils::LegacyFilterInput($_POST['output']);
$sReportType = InputUtils::LegacyFilterInput($_POST['ReportType']);
$sDateStart = InputUtils::FilterDate($_POST['DateStart'], 'date');
$sDateEnd = InputUtils::FilterDate($_POST['DateEnd'], 'date');
$iDepID = InputUtils::LegacyFilterInput($_POST['deposit'], 'int');
$iMinimum = InputUtils::LegacyFilterInput($_POST['minimum'], 'int');

// If CSVAdminOnly option is enabled and user is not admin, redirect to the menu.
if (!SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getValue('bCSVAdminOnly') && $output != 'pdf') {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$connection = Propel::getConnection();

if (!empty($_POST['classList'])) {
    $classList = $_POST['classList'];

    if ($classList[0]) {
        $ormClassifications = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(1);


        $inClassList = '(';
        $notInClassList = '(';

        foreach ($ormClassifications as $classification) {
            if (in_array($classification->getOptionID(), $classList)) {
                if ($inClassList == '(') {
                    $inClassList .= $classification->getOptionID();
                } else {
                    $inClassList .= ','.$classification->getOptionID();
                }
            } else {
                if ($notInClassList == '(') {
                    $notInClassList .= $classification->getOptionID();
                } else {
                    $notInClassList .= ','.$classification->getOptionID();
                }
            }
        }
        $inClassList .= ')';
        $notInClassList .= ')';
    }

    // all classes were selected. this should behave as if no filter classes were specified
    if ($notInClassList == '()') {
        unset($classList);
    }
}

// Build SQL Query
// Build SELECT SQL Portion
$sSQL = 'SELECT fam_ID, fam_Name, fam_Address1, fam_Address2, fam_City, fam_State, fam_Zip, fam_Country, fam_envelope, plg_date, plg_amount, plg_method, plg_comment, plg_CheckNo, fun_Name, plg_PledgeOrPayment, plg_NonDeductible FROM family_fam
    INNER JOIN pledge_plg ON fam_ID=plg_FamID
    LEFT JOIN donationfund_fun ON plg_fundID=fun_ID';

if ($classList[0]) {
    $sSQL .= ' LEFT JOIN person_per ON fam_ID=per_fam_ID';
}
$sSQL .= " WHERE plg_PledgeOrPayment='Payment' ";

// Add  SQL criteria
// Report Dates OR Deposit ID
if ($iDepID > 0) {
    $sSQL .= " AND plg_depID='$iDepID' ";
} else {
    $today = date('Y-m-d');
    if (!$sDateEnd && $sDateStart) {
        $sDateEnd = $sDateStart;
    }
    if (!$sDateStart && $sDateEnd) {
        $sDateStart = $sDateEnd;
    }
    if (!$sDateStart && !$sDateEnd) {
        $sDateStart = $today;
        $sDateEnd = $today;
    }
    if ($sDateStart > $sDateEnd) {
        $temp = $sDateStart;
        $sDateStart = $sDateEnd;
        $sDateEnd = $temp;
    }
    $sSQL .= " AND plg_date BETWEEN '$sDateStart' AND '$sDateEnd' ";
}

// Filter by Fund
if (!empty($_POST['funds'])) {
    $count = 0;
    foreach ($_POST['funds'] as $fundID) {
        $fund[$count++] = InputUtils::LegacyFilterInput($fundID, 'int');
    }
    if ($count == 1) {
        if ($fund[0]) {
            $sSQL .= " AND plg_fundID='$fund[0]' ";
        }
    } else {
        $sSQL .= " AND (plg_fundID ='$fund[0]'";
        for ($i = 1; $i < $count; $i++) {
            $sSQL .= " OR plg_fundID='$fund[$i]'";
        }
        $sSQL .= ') ';
    }
}
// Filter by Family
if (!empty($_POST['family'])) {
    $count = 0;
    foreach ($_POST['family'] as $famID) {
        $fam[$count++] = InputUtils::LegacyFilterInput($famID, 'int');
    }
    if ($count == 1) {
        if ($fam[0]) {
            $sSQL .= " AND plg_FamID='$fam[0]' ";
        }
    } else {
        $sSQL .= " AND (plg_FamID='$fam[0]'";
        for ($i = 1; $i < $count; $i++) {
            $sSQL .= " OR plg_FamID='$fam[$i]'";
        }
        $sSQL .= ') ';
    }
}

if ($classList[0]) {
    $q = ' per_cls_ID IN '.$inClassList.' AND per_fam_ID NOT IN (SELECT DISTINCT per_fam_ID FROM person_per WHERE per_cls_ID IN '.$notInClassList.')';

    $sSQL .= ' AND'.$q;
}

// Get Criteria string
preg_match('/WHERE (plg_PledgeOrPayment.*)/i', $sSQL, $aSQLCriteria);

// Add SQL ORDER
$sSQL .= ' ORDER BY plg_FamID, plg_date ';

//Execute SQL Statement
$statement = $connection->prepare($sSQL);
$statement->execute();

// Exit if no rows returned
$iCountRows = $statement->rowCount();
if ($iCountRows < 1) {
    if ($year != -1) {
        RedirectUtils::Redirect('v2/deposit/financial/reports/NoRows/Giving%20Report/'.$year);
    } else {
        RedirectUtils::Redirect('v2/deposit/financial/reports/NoRows/Giving%20Report');
    }
}

// Create Giving Report -- PDF
// ***************************

if ($output == 'pdf') {

    // Set up bottom border values
    if ($remittance == 'yes') {
        $bottom_border1 = 134;
        $bottom_border2 = 180;
    } else {
        $bottom_border1 = 200;
        $bottom_border2 = 250;
    }

    class PDF_TaxReport extends ChurchInfoReportTCPDF
    {
        // Constructor
        public function __construct()
        {
            parent::__construct('P', 'mm', $this->paperFormat);
            $this->SetFont('Times', '', 10);
            $this->SetMargins(20, 20);

            $this->SetAutoPageBreak(false);
        }

        public function StartNewPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $fam_envelope)
        {
            global $letterhead, $sDateStart, $sDateEnd, $iDepID;
            $curY = $this->StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $letterhead);
            if (SystemConfig::getValue('bUseDonationEnvelopes')) {
                $this->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Envelope:').$fam_envelope);
                $curY += SystemConfig::getValue('incrementY');
            }
            $curY += 2 * SystemConfig::getValue('incrementY');
            if ($iDepID) {
                // Get Deposit Date
                $dep = DepositQuery::Create()->findOneById($iDepID);
                $sDateStart = $dep->getDate()->format('Y-m-d');
                $sDateEnd   = $dep->getDate()->format('Y-m-d');
            }
            if ($sDateStart == $sDateEnd) {
                $DateString = OutputUtils::FormatDate($sDateStart);
            } else {
                $DateString = OutputUtils::FormatDate($sDateStart).' - '.OutputUtils::FormatDate($sDateEnd);
            }
            $blurb = SystemConfig::getValue('sTaxReport1').' '.$DateString.'.';
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
            $curY += 2 * SystemConfig::getValue('incrementY');

            return $curY;
        }

        public function FinishPage($curY, $fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
        {
            global $remittance;
            $curY += 2 * SystemConfig::getValue('incrementY');
            $blurb = SystemConfig::getValue('sTaxReport2');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
            $curY += 3 * SystemConfig::getValue('incrementY');
            $blurb = SystemConfig::getValue('sTaxReport3');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
            $curY += 3 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely').',');
            $curY += 4 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sTaxSigner'));

            if ($remittance == 'yes') {
                // Add remittance slip
                $curY = 194;
                $curX = 60;
                $this->WriteAt($curX, $curY, _('Please detach this slip and mail with your next gift.'));
                $curY += (1.5 * SystemConfig::getValue('incrementY'));
                $church_mailing = _('Please mail you next gift to ').SystemConfig::getValue('sEntityName').', '
                    .SystemConfig::getValue('sEntityAddress').', '.SystemConfig::getValue('sEntityCity').', '.SystemConfig::getValue('sEntityState').'  '
                    .SystemConfig::getValue('sEntityZip')._(', Phone: ').SystemConfig::getValue('sEntityPhone');
                $this->SetFont('Times', 'I', 10);
                $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $church_mailing);
                $this->SetFont('Times', '', 10);
                $curY = 215;
                $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $this->MakeSalutation($fam_ID));
                $curY += SystemConfig::getValue('incrementY');
                if ($fam_Address1 != '') {
                    $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_Address1);
                    $curY += SystemConfig::getValue('incrementY');
                }
                if ($fam_Address2 != '') {
                    $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_Address2);
                    $curY += SystemConfig::getValue('incrementY');
                }
                $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_City.', '.$fam_State.'  '.$fam_Zip);
                $curY += SystemConfig::getValue('incrementY');
                if ($fam_Country != '' && $fam_Country != 'USA' && $fam_Country != 'United States') {
                    $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_Country);
                    $curY += SystemConfig::getValue('incrementY');
                }
                $curX = 30;
                $curY = 246;
                $this->WriteAt(SystemConfig::getValue('leftX') + 5, $curY, SystemConfig::getValue('sEntityName'));
                $curY += SystemConfig::getValue('incrementY');
                if (SystemConfig::getValue('sEntityAddress') != '') {
                    $this->WriteAt(SystemConfig::getValue('leftX') + 5, $curY, SystemConfig::getValue('sEntityAddress'));
                    $curY += SystemConfig::getValue('incrementY');
                }
                $this->WriteAt(SystemConfig::getValue('leftX') + 5, $curY, SystemConfig::getValue('sEntityCity').', '.SystemConfig::getValue('sEntityState').'  '.SystemConfig::getValue('sEntityZip'));
                $curY += SystemConfig::getValue('incrementY');
                if ($fam_Country != '' && $fam_Country != 'USA' && $fam_Country != 'United States') {
                    $this->WriteAt(SystemConfig::getValue('leftX') + 5, $curY, $fam_Country);
                    $curY += SystemConfig::getValue('incrementY');
                }
                $curX = 100;
                $curY = 215;
                $this->WriteAt($curX, $curY, _('Gift Amount:'));
                $this->WriteAt($curX + 25, $curY, '_______________________________');
                $curY += (2 * SystemConfig::getValue('incrementY'));
                $this->WriteAt($curX, $curY, _('Gift Designation:'));
                $this->WriteAt($curX + 25, $curY, '_______________________________');
                $curY = 200 + (11 * SystemConfig::getValue('incrementY'));
            }
        }
    }

    $currency = SystemConfig::getValue("sCurrency");

    // Instantiate the directory class and build the report.
    $pdf = new PDF_TaxReport();

    // Loop through result array
    $currentFamilyID = 0;
    while ($row = $statement->fetch( \PDO::FETCH_ASSOC )) {
        extract($row);

        // Check for minimum amount
        if ($iMinimum > 0) {
            $temp = "SELECT SUM(plg_amount) AS total_gifts FROM pledge_plg
                WHERE plg_FamID=$fam_ID AND $aSQLCriteria[1]";

            $tempPDO = $connection->prepare($temp);
            $tempPDO->execute();
            $total_gifts = $tempPDO->fetch(PDO::FETCH_NUM)[0];

            if ($iMinimum > $total_gifts) {
                continue;
            }
        }
        // Check for new family
        if ($fam_ID != $currentFamilyID && $currentFamilyID != 0) {
            //New Family. Finish Previous Family
            $pdf->SetFont('Times', 'B', 10);
            $pdf->Cell(20, $summaryIntervalY / 2, ' ', 0, 1);
            $pdf->Cell(95, $summaryIntervalY, ' ');
            $pdf->Cell(50, $summaryIntervalY, _('Total Payments:'));
            $totalAmountStr = $currency.' '.OutputUtils::money_localized($totalAmount);
            $pdf->SetFont('Courier', '', 9);
            $pdf->Cell(25, $summaryIntervalY, $totalAmountStr, 0, 1, 'R');
            $pdf->SetFont('Times', 'B', 10);
            $pdf->Cell(95, $summaryIntervalY, ' ');
            $pdf->Cell(50, $summaryIntervalY, _('Goods and Services Rendered:'));
            $totalAmountStr = $currency.' '.OutputUtils::money_localized($totalNonDeductible);
            $pdf->SetFont('Courier', '', 9);
            $pdf->Cell(25, $summaryIntervalY, $totalAmountStr, 0, 1, 'R');
            $pdf->SetFont('Times', 'B', 10);
            $pdf->Cell(95, $summaryIntervalY, ' ');
            $pdf->Cell(50, $summaryIntervalY, _('Tax-Deductible Contribution:'));
            $totalAmountStr = $currency.' '.OutputUtils::money_localized($totalAmount - $totalNonDeductible);
            $pdf->SetFont('Courier', '', 9);
            $pdf->Cell(25, $summaryIntervalY, $totalAmountStr, 0, 1, 'R');
            $curY = $pdf->GetY();
            $curY = $pdf->GetY();

            if ($curY > $bottom_border1) {
                $pdf->AddPage();
                if ($letterhead == 'none') {
                    // Leave blank space at top on all pages for pre-printed letterhead
                    $curY = 20 + ($summaryIntervalY * 3) + 25;
                    $pdf->SetY($curY);
                } else {
                    $curY = 20;
                    $pdf->SetY(20);
                }
            }
            $pdf->SetFont('Times', '', 10);
            $pdf->FinishPage($curY, $prev_fam_ID, $prev_fam_Name, $prev_fam_Address1, $prev_fam_Address2, $prev_fam_City, $prev_fam_State, $prev_fam_Zip, $prev_fam_Country);
        }

        // Start Page for New Family
        if ($fam_ID != $currentFamilyID) {
            $curY = $pdf->StartNewPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $fam_envelope);
            $summaryDateX = SystemConfig::getValue('leftX');
            $summaryCheckNoX = 40;
            $summaryMethodX = 60;
            $summaryFundX = 85;
            $summaryMemoX = 110;
            $summaryAmountX = 160;
            $summaryIntervalY = 4;
            $curY += 2 * $summaryIntervalY;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetXY($summaryDateX, $curY);
            $pdf->Cell(20, $summaryIntervalY, _('Date'));
            $pdf->Cell(20, $summaryIntervalY, _('Chk No.'), 0, 0, 'C');
            $pdf->Cell(25, $summaryIntervalY, _('PmtMethod'));
            $pdf->Cell(40, $summaryIntervalY, _('Fund'));
            $pdf->Cell(40, $summaryIntervalY, _('Memo'));
            $pdf->Cell(25, $summaryIntervalY, _('Amount'), 0, 1, 'R');
            //$curY = $pdf->GetY();
            $totalAmount = 0;
            $totalNonDeductible = 0;
            $cnt = 0;
            $currentFamilyID = $fam_ID;
        }
        // Format Data
        if (strlen($plg_CheckNo) > 8) {
            $plg_CheckNo = '...'.mb_substr($plg_CheckNo, -8, 8);
        } else {
            $plg_CheckNo .= '    ';
        }
        if (strlen($fun_Name) > 25) {
            $fun_Name = mb_substr($fun_Name, 0, 25).'...';
        }
        if (strlen($plg_comment) > 25) {
            $plg_comment = mb_substr($plg_comment, 0, 25).'...';
        }
        // Print Gift Data
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(20, $summaryIntervalY, date(SystemConfig::getValue('sDateFormatLong'), strtotime($plg_date)));
        $pdf->Cell(20, $summaryIntervalY, $plg_CheckNo, 0, 0, 'R');
        $pdf->Cell(25, $summaryIntervalY, _($plg_method));
        $pdf->Cell(40, $summaryIntervalY, $fun_Name);
        $pdf->Cell(40, $summaryIntervalY, $plg_comment);
        $pdf->SetFont('Courier', '', 9);
        $pdf->Cell(25, $summaryIntervalY, OutputUtils::money_localized($plg_amount), 0, 1, 'R');
        $totalAmount += $plg_amount;
        $totalNonDeductible += $plg_NonDeductible;
        $cnt += 1;
        $curY = $pdf->GetY();

        if ($curY > $bottom_border2) {
            $pdf->AddPage();
            if ($letterhead == 'none') {
                // Leave blank space at top on all pages for pre-printed letterhead
                $curY = 20 + ($summaryIntervalY * 3) + 25;
                $pdf->SetY($curY);
            } else {
                $curY = 20;
                $pdf->SetY(20);
            }
        }
        $prev_fam_ID = $fam_ID;
        $prev_fam_Name = $fam_Name;
        $prev_fam_Address1 = $fam_Address1;
        $prev_fam_Address2 = $fam_Address2;
        $prev_fam_City = $fam_City;
        $prev_fam_State = $fam_State;
        $prev_fam_Zip = $fam_Zip;
        $prev_fam_Country = $fam_Country;
    }

    // Finish Last Report
    $pdf->SetFont('Times', 'B', 10);
    $pdf->Cell(20, $summaryIntervalY / 2, ' ', 0, 1);
    $pdf->Cell(95, $summaryIntervalY, ' ');
    $pdf->Cell(50, $summaryIntervalY, _('Total Payments:'));
    $totalAmountStr = $currency.' '.OutputUtils::money_localized($totalAmount);
    $pdf->SetFont('Courier', '', 9);
    $pdf->Cell(25, $summaryIntervalY, $totalAmountStr, 0, 1, 'R');
    $pdf->SetFont('Times', 'B', 10);
    $pdf->Cell(95, $summaryIntervalY, ' ');
    $pdf->Cell(50, $summaryIntervalY, _('Goods and Services Rendered:'));
    $totalAmountStr = $currency.' '.OutputUtils::money_localized($totalNonDeductible);
    $pdf->SetFont('Courier', '', 9);
    $pdf->Cell(25, $summaryIntervalY, $totalAmountStr, 0, 1, 'R');
    $pdf->SetFont('Times', 'B', 10);
    $pdf->Cell(95, $summaryIntervalY, ' ');
    $pdf->Cell(50, $summaryIntervalY, _('Tax-Deductible Contribution:'));
    $totalAmountStr = $currency.' '.OutputUtils::money_localized($totalAmount - $totalNonDeductible);
    $pdf->SetFont('Courier', '', 9);
    $pdf->Cell(25, $summaryIntervalY, $totalAmountStr, 0, 1, 'R');
    $curY = $pdf->GetY();

    if ($cnt > 0) {
        if ($curY > $bottom_border1) {
            $pdf->AddPage();
            if ($letterhead == 'none') {
                // Leave blank space at top on all pages for pre-printed letterhead
                $curY = 20 + ($summaryIntervalY * 3) + 25;
                $pdf->SetY($curY);
            } else {
                $curY = 20;
                $pdf->SetY(20);
            }
        }
        $pdf->SetFont('Times', '', 10);
        $pdf->FinishPage($curY, $fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country);
    }

    header('Pragma: public');  // Needed for IE when using a shared SSL certificate
    ob_end_clean();
    if (SystemConfig::getValue('iPDFOutputType') == 1) {
        $pdf->Output('TaxReport'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
    } else {
        $pdf->Output();
    }

    // Output a text file
// ##################
} elseif ($output == 'csv') {

    // Settings
    //$delimiter = ',';
    $delimiter = $delimiter;
    $eol = "\r\n";

    // Build headings row
    preg_match('/SELECT (.*) FROM /i', $sSQL, $result);
    $headings = explode(',', $result[1]);
    $buffer = '';
    foreach ($headings as $heading) {
        $buffer .= trim($heading).$delimiter;
    }
    // Remove trailing delimiter and add eol
    $buffer = mb_substr($buffer, 0, -1).$eol;

    // Add data
    while ($row = $statement->fetch( \PDO::FETCH_ASSOC )) {
        foreach ($row as $field) {
            $field = str_replace($delimiter, ' ', $field);    // Remove any delimiters from data
            $buffer .= InputUtils::translate_special_charset($field).$delimiter;
        }
        // Remove trailing delimiter and add eol
        $buffer = mb_substr($buffer, 0, -1).$eol;
    }

    // Export file
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv;charset='.$charset);
    header('Content-Disposition: attachment; filename=EcclesiaCRM-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
    header('Content-Transfer-Encoding: binary');

    if ($charset == "UTF-8") {
       echo "\xEF\xBB\xBF";
    }

    echo $buffer;
}
