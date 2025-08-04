<?php
/*******************************************************************************
*
*  filename    : Reports/AdvancedDeposit.php
*  last change : 2013-02-21
*  description : Creates a PDF customized Deposit Report .

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
use Propel\Runtime\Propel;

// Security
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$delemiter = SessionUser::getUser()->CSVExportDelemiter();
$charset   = SessionUser::getUser()->CSVExportCharset();

// Filter values
$sort = InputUtils::LegacyFilterInput($_POST['sort']);
$detail_level = InputUtils::LegacyFilterInput($_POST['detail_level']);
$datetype = InputUtils::LegacyFilterInput($_POST['datetype']);
$output = InputUtils::LegacyFilterInput($_POST['output']);
$sDateStart = InputUtils::FilterDate($_POST['DateStart'], 'date');
$sDateEnd = InputUtils::FilterDate($_POST['DateEnd'], 'date');
$iDepID = InputUtils::LegacyFilterInput($_POST['deposit'], 'int');

$currency = SystemConfig::getValue("sCurrency");

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
}

if (!empty($_POST['family'])) {
    $familyList = $_POST['family'];
}

if (!$sort) {
    $sort = 'deposit';
}
if (!$detail_level) {
    $detail_level = 'detail';
}
if (!$output) {
    $output = 'pdf';
}

// If CSVAdminOnly option is enabled and user is not admin, redirect to the menu.
if (!SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getValue('bCSVAdminOnly') && $output != 'pdf') {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

// Build SQL Query
// Build SELECT SQL Portion

$sSQL = 'SELECT DISTINCT fam_ID, fam_Name, fam_Address1, fam_Address2, fam_City, fam_State, fam_Zip, fam_Country, plg_date, plg_amount, plg_method, plg_comment, plg_depID, plg_CheckNo, fun_ID, fun_Name, dep_Date FROM pledge_plg';
$sSQL .= ' LEFT JOIN family_fam ON plg_FamID=fam_ID';
$sSQL .= ' INNER JOIN deposit_dep ON plg_depID = dep_ID';
$sSQL .= ' LEFT JOIN donationfund_fun ON plg_fundID=fun_ID';

if ($classList[0]) {
    $sSQL .= ' LEFT JOIN person_per ON fam_ID=per_fam_ID';
}
$sSQL .= " WHERE plg_PledgeOrPayment='Payment'";

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
    if ($datetype == 'Payment') {
        $sSQL .= " AND plg_date BETWEEN '$sDateStart' AND '$sDateEnd' ";
    } else {
        $sSQL .= " AND dep_Date BETWEEN '$sDateStart' AND '$sDateEnd' ";
    }
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
if ($familyList) {
    $count = 0;
    foreach ($familyList as $famID) {
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
        $sSQL .= ' ) ';
    }
}

if ($classList[0]) {
    $sSQL .= ' AND per_cls_ID IN '.$inClassList.' AND per_fam_ID NOT IN (SELECT DISTINCT per_fam_ID FROM person_per WHERE per_cls_ID IN '.$notInClassList.')';
}

// Filter by Payment Method
if (!empty($_POST['method'])) {
    $count = 0;
    foreach ($_POST['method'] as $MethodItem) {
        $aMethod[$count++] = InputUtils::LegacyFilterInput($MethodItem);
    }
    if ($count == 1) {
        if ($aMethod[0]) {
            $sSQL .= " AND plg_method='$aMethod[0]' ";
        }
    } else {
        $sSQL .= " AND (plg_method='$aMethod[0]' ";
        for ($i = 1; $i < $count; $i++) {
            $sSQL .= " OR plg_method='$aMethod[$i]'";
        }
        $sSQL .= ') ';
    }
}

// Add SQL ORDER
if ($sort == 'deposit') {
    $sSQL .= ' ORDER BY plg_depID, fun_Name, fam_Name, fam_ID';
} elseif ($sort == 'fund') {
    $sSQL .= ' ORDER BY fun_Name, fam_Name, fam_ID, plg_depID ';
} elseif ($sort == 'family') {
    $sSQL .= ' ORDER BY fam_Name, fam_ID, fun_Name, plg_depID';
}

//var_dump($sSQL);

//Execute SQL Statement
$statement = $connection->prepare($sSQL);
$statement->execute();

// Exit if no rows returned
$iCountRows = $statement->rowCount();
if ($iCountRows < 1) {
    RedirectUtils::Redirect('v2/deposit/financial/reports/NoRows/Advanced%20Deposit%20Report');
}

// Create PDF Report -- PDF
// ***************************

if ($output == 'pdf') {

    // Set up bottom border value
    $bottom_border = 250;
    $summaryIntervalY = 4;
    $page = 1;

    class PDF_TaxReport extends ChurchInfoReportTCPDF
    {
        // Constructor
        public function __construct()
        {
            parent::__construct('P', 'mm', $this->paperFormat);
            $this->SetFont('Times', '', 10);
            $this->SetMargins(20, 15);

            $this->SetAutoPageBreak(false);
        }

        public function PrintRightJustified($x, $y, $str)
        {
            $iLen = strlen($str);
            $nMoveBy = 2 * $iLen;
            $this->SetXY($x - $nMoveBy, $y);
            $this->Write(8, $str);
        }

        public function StartFirstPage()
        {
            global $sDateStart, $sDateEnd, $sort, $iDepID, $datetype;
            $this->AddPage();
            $curY = 20;
            $curX = 60;
            $this->SetFont('Times', 'B', 14);
            $this->WriteAt($curX, $curY, SystemConfig::getValue('sEntityName').' : '._('Deposit Report'));
            $curY += 2 * SystemConfig::getValue('incrementY');
            $this->SetFont('Times', 'B', 10);
            $curX = SystemConfig::getValue('leftX');
            $this->WriteAt($curX, $curY, _('Data sorted by').' '.ucwords($sort));
            $curY += SystemConfig::getValue('incrementY');
            if (!$iDepID) {
                $this->WriteAt($curX, $curY, _($datetype)." "._("Dates")." : ".$sDateStart." "._("through")." ". $sDateEnd);
                $curY += SystemConfig::getValue('incrementY');
            }
            if ($iDepID || $_POST['family'][0] || $_POST['funds'][0] || $_POST['method'][0]) {
                $heading = _('Filtered by').' ';
                if ($iDepID) {
                    $heading .= _("Deposit")." #$iDepID, ";
                }
                if ($_POST['family'][0]) {
                    $heading .= _('Selected Families').', ';
                }
                if ($_POST['funds'][0]) {
                    $heading .= _('Selected Funds').', ';
                }
                if ($_POST['method'][0]) {
                    $heading .= _('Selected Payment Methods').', ';
                }
                $heading = mb_substr($heading, 0, -2);
            } else {
                $heading = _('Showing all records for report dates.');
            }
            $this->WriteAt($curX, $curY, $heading);
            $curY += 2 * SystemConfig::getValue('incrementY');
            $this->SetFont('Times', '', 10);

            return $curY;
        }

        public function PageBreak($page)
        {
            // Finish footer of previous page if neccessary and add new page
            global $curY, $bottom_border, $detail_level;
            if ($curY > $bottom_border) {
                $this->FinishPage($page);
                $page++;
                $this->AddPage();
                $curY = 20;
                if ($detail_level == 'detail') {
                    $curY = $this->Headings($curY);
                }
            }

            return $page;
        }

        public function Headings($curY)
        {
            global $sort, $summaryIntervalY;
            if ($sort == 'deposit') {
                $curX = SystemConfig::getValue('leftX');
                $this->SetFont('Times', 'BU', 10);
                $this->WriteAt($curX, $curY, _('Chk No.'));
                $this->WriteAt(40, $curY, _('Fund'));
                $this->WriteAt(80, $curY, _('Recieved From'));
                $this->WriteAt(135, $curY, _('Memo'));
                $this->WriteAt(181, $curY, _('Amount'));
                $curY += 2 * $summaryIntervalY;
            } elseif ($sort == 'fund') {
                $curX = SystemConfig::getValue('leftX');
                $this->SetFont('Times', 'BU', 10);
                $this->WriteAt($curX, $curY, _('Chk No.'));
                $this->WriteAt(40, $curY, _('Deposit No./ Date'));
                $this->WriteAt(80, $curY, _('Recieved From'));
                $this->WriteAt(135, $curY, _('Memo'));
                $this->WriteAt(181, $curY, _('Amount'));
                $curY += 2 * $summaryIntervalY;
            } elseif ($sort == 'family') {
                $curX = SystemConfig::getValue('leftX');
                $this->SetFont('Times', 'BU', 10);
                $this->WriteAt($curX, $curY, _('Chk No.'));
                $this->WriteAt(40, $curY, _('Deposit No./Date'));
                $this->WriteAt(80, $curY, _('Fund'));
                $this->WriteAt(135, $curY, _('Memo'));
                $this->WriteAt(181, $curY, _('Amount'));
                $curY += 2 * $summaryIntervalY;
            }

            return $curY;
        }

        public function FinishPage($page)
        {
            $footer = _("Page")." ".$page." "._("Generated on")." ".date(SystemConfig::getValue("sDateTimeFormat"));
            $this->SetFont('Times', 'I', 9);
            $this->WriteAt(80, 258, $footer);
        }
    }

    // Instantiate the directory class and build the report.
    $pdf = new PDF_TaxReport();

    $curY = $pdf->StartFirstPage();
    $curX = 0;

    $currentDepositID = 0;
    $currentFundID = 0;
    $totalAmount = 0;
    $totalFund = [];

    $countFund = 0;
    $countDeposit = 0;
    $countReport = 0;
    $currentFundAmount = 0;
    $currentDepositAmount = 0;
    $currentReportAmount = 0;

    // **********************
    // Sort by Deposit Report
    // **********************
    if ($sort == 'deposit') {
        if ($detail_level == 'detail') {
            $curY = $pdf->Headings($curY);
        }

        while ($aRow = $statement->fetch( \PDO::FETCH_ASSOC )) {
            extract($aRow);
            if (!$fun_ID) {
                $fun_ID = -1;
                $fun_Name = 'Undesignated';
            }
            if (!$fam_ID) {
                $fam_ID = -1;
                $fam_Name = 'Unassigned';
            }
            // First Deposit Heading
            if (!$currentDepositID && $detail_level != 'summary') {
                $sDepositTitle = _("Deposit")." #".$plg_depID." (".date(SystemConfig::getValue('sDateFormatLong'), strtotime($dep_Date)).")";
                $pdf->SetFont('Times', 'B', 10);
                $pdf->WriteAt(20, $curY, $sDepositTitle);
                $curY += 1.5 * $summaryIntervalY;
            }
            // Check for new fund
            if (($currentFundID != $fun_ID || $currentDepositID != $plg_depID) && $currentFundID && $detail_level != 'summary') {
                // New Fund. Print Previous Fund Summary
                if ($countFund > 1) {
                    $item = _('items');
                } else {
                    $item = _('item');
                }
                $sFundSummary = "$currentFundName ".utf8_decode(_("Total"))." - $countFund $item:   $currency".OutputUtils::money_localized($currentFundAmount);
                $curY += 2;
                $pdf->SetXY(20, $curY);
                $pdf->SetFont('Times', 'I', 10);
                $pdf->Cell(176, $summaryIntervalY, $sFundSummary, 0, 0, 'R');
                $curY += 1.75 * $summaryIntervalY;
                $countFund = 0;
                $currentFundAmount = 0;
                $page = $pdf->PageBreak($page);
            }
            // Check for new deposit
            if ($currentDepositID != $plg_depID && $currentDepositID) {
                // New Deposit ID.  Print Previous Deposit Summary
                if ($countDeposit > 1) {
                    $item = _('items');
                } else {
                    $item = _('item');
                }
                $sDepositSummary = utf8_decode(_("Deposit"))." #$currentDepositID ".utf8_decode(_("Total"))." - $countDeposit $item:   $currency".OutputUtils::money_localized($currentDepositAmount);
                $pdf->SetXY(20, $curY);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->Cell(176, $summaryIntervalY, $sDepositSummary, 0, 0, 'R');
                $curY += 2 * $summaryIntervalY;
                if ($detail_level != 'summary') {
                    $pdf->line(40, $curY - 2, 195, $curY - 2);
                }
                $page = $pdf->PageBreak($page);

                // New Deposit Title
                if ($detail_level != 'summary') {
                    $sDepositTitle = _("Deposit")." #$plg_depID (".date(SystemConfig::getValue('sDateFormatLong'), strtotime($dep_Date)).")";
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->WriteAt(20, $curY, $sDepositTitle);
                    $curY += 1.5 * $summaryIntervalY;
                }
                $countDeposit = 0;
                $currentDepositAmount = 0;
            }

            // Print Deposit Detail
            if ($detail_level == 'detail') {
                // Format Data
                if ($plg_method == 'CREDITCARD') {
                    $plg_method = 'CREDIT';
                }
                if ($plg_method == 'BANKDRAFT') {
                    $plg_method = 'DRAFT';
                }
                if ($plg_method != 'CHECK') {
                    $plg_CheckNo = $plg_method;
                }
                if (strlen($plg_CheckNo) > 8) {
                    $plg_CheckNo = '...'.mb_substr($plg_CheckNo, -8, 8);
                }
                if (strlen($fun_Name) > 22) {
                    $sfun_Name = mb_substr($fun_Name, 0, 21).'...';
                } else {
                    $sfun_Name = $fun_Name;
                }
                if (strlen($plg_comment) > 29) {
                    $plg_comment = mb_substr($plg_comment, 0, 28).'...';
                }
                $fam_Name = $fam_Name.' - '.$fam_Address1;
                if (strlen($fam_Name) > 31) {
                    $fam_Name = mb_substr($fam_Name, 0, 30).'...';
                }

                // Print Data
                $pdf->SetFont('Times', '', 10);
                $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
                $pdf->Cell(16, $summaryIntervalY, $plg_CheckNo, 0, 0, 'R');
                $pdf->Cell(40, $summaryIntervalY, utf8_decode($sfun_Name));
                $pdf->Cell(55, $summaryIntervalY, utf8_decode($fam_Name));
                $pdf->Cell(40, $summaryIntervalY, utf8_decode($plg_comment));
                $pdf->SetFont('Courier', '', 9);
                $pdf->Cell(25, $summaryIntervalY, OutputUtils::money_localized($plg_amount), 0, 0, 'R');
                $pdf->SetFont('Times', '', 10);
                $curY += $summaryIntervalY;
                $page = $pdf->PageBreak($page);
            }
            // Update running totals
            $totalAmount += $plg_amount;
            if (array_key_exists($fun_Name, $totalFund)) {
                $totalFund[$fun_Name] += $plg_amount;
            } else {
                $totalFund[$fun_Name] = $plg_amount;
            }
            $countFund++;
            $countDeposit++;
            $countReport++;
            $currentFundAmount += $plg_amount;
            $currentDepositAmount += $plg_amount;
            $currentReportAmount += $plg_amount;
            $currentDepositID = $plg_depID;
            $currentFundID = $fun_ID;
            $currentFundName = $fun_Name;
            $currentDepositDate = date(SystemConfig::getValue('sDateFormatLong'), strtotime($dep_Date));
        }

        // Print Final Summary
        // Print Fund Summary
        if ($detail_level != 'summary') {
            if ($countFund > 1) {
                $item = _('items');
            } else {
                $item = _('item');
            }
            $sFundSummary = utf8_decode($fun_Name)." ".utf8_decode(_("Total"))." - $countFund $item:   $currency".OutputUtils::money_localized($currentFundAmount);
            $curY += 2;
            $pdf->SetXY(20, $curY);
            $pdf->SetFont('Times', 'I', 10);
            $pdf->Cell(176, $summaryIntervalY, $sFundSummary, 0, 0, 'R');
            $curY += 1.75 * $summaryIntervalY;
            $page = $pdf->PageBreak($page);
        }
        // Print Deposit Summary
        if ($countDeposit > 1) {
            $item = _('items');
        } else {
            $item = _('item');
        }
        $sDepositSummary = utf8_decode(_("Deposit"))." #".$currentDepositID." ".utf8_decode(_("Total"))." - $countDeposit $item:   $currency".OutputUtils::money_localized($currentDepositAmount);
        $pdf->SetXY(20, $curY);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(176, $summaryIntervalY, $sDepositSummary, 0, 0, 'R');
        $curY += 2 * $summaryIntervalY;
        $page = $pdf->PageBreak($page);
    } elseif ($sort == 'fund') {

        // **********************
        // Sort by Fund  Report
        // **********************

        if ($detail_level == 'detail') {
            $curY = $pdf->Headings($curY);
        }

        while ($aRow = $statement->fetch( \PDO::FETCH_ASSOC )) {
            extract($aRow);
            if (!$fun_ID) {
                $fun_ID = -1;
                $fun_Name = 'Undesignated';
            }
            if (!$fam_ID) {
                $fam_ID = -1;
                $fam_Name = 'Unassigned';
            }
            // First Fund Heading
            if (!$currentFundName && $detail_level != 'summary') {
                $sFundTitle = "Fund: $fun_Name";
                $pdf->SetFont('Times', 'B', 10);
                $pdf->WriteAt(20, $curY, $sFundTitle);
                $curY += 1.5 * $summaryIntervalY;
            }
            // Check for new Family
            if (($currentFamilyID != $fam_ID || $currentFundID != $fun_ID) && $currentFamilyID && $detail_level != 'summary') {
                // New Family. Print Previous Family Summary
                if ($countFamily > 1) {
                    $item = _('items');
                } else {
                    $item = _('item');
                }
                $sFamilySummary = utf8_decode($currentFamilyName)." - ".utf8_decode($currentFamilyAddress)." - $countFamily $item:   $currency".OutputUtils::money_localized($currentFamilyAmount);
                $curY += 2;
                $pdf->SetXY(20, $curY);
                $pdf->SetFont('Times', 'I', 10);
                $pdf->Cell(176, $summaryIntervalY, $sFamilySummary, 0, 0, 'R');
                $curY += 1.75 * $summaryIntervalY;
                $countFamily = 0;
                $currentFamilyAmount = 0;
                $page = $pdf->PageBreak($page);
            }
            // Check for new Fund
            if ($currentFundID != $fun_ID && $currentFundID) {
                // New Fund ID.  Print Previous Fund Summary
                if ($countFund > 1) {
                    $item = _('items');
                } else {
                    $item = _('item');
                }
                $sFundSummary = utf8_decode($currentFundName)." Total - $countFund $item:  $currency".OutputUtils::money_localized($currentFundAmount);
                $pdf->SetXY(20, $curY);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->Cell(176, $summaryIntervalY, $sFundSummary, 0, 0, 'R');
                $curY += 2 * $summaryIntervalY;
                if ($detail_level != 'summary') {
                    $pdf->line(40, $curY - 2, 195, $curY - 2);
                }
                $page = $pdf->PageBreak($page);

                // New Fund Title
                if ($detail_level != 'summary') {
                    $sFundTitle = "Fund: $fun_Name";
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->WriteAt(20, $curY, $sFundTitle);
                    $curY += 1.5 * $summaryIntervalY;
                }
                $countFund = 0;
                $currentFundAmount = 0;
            }

            // Print Deposit Detail
            if ($detail_level == 'detail') {
                // Format Data
                if ($plg_method == 'CREDITCARD') {
                    $plg_method = 'CREDIT';
                }
                if ($plg_method == 'BANKDRAFT') {
                    $plg_method = 'DRAFT';
                }
                if ($plg_method != 'CHECK') {
                    $plg_CheckNo = $plg_method;
                }
                if (strlen($plg_CheckNo) > 8) {
                    $plg_CheckNo = '...'.mb_substr($plg_CheckNo, -8, 8);
                }
                $sDeposit = _("Dep #").$plg_depID." ".date(SystemConfig::getValue('sDateFormatLong'), strtotime($dep_Date));
                if (strlen($sDeposit) > 22) {
                    $sDeposit = mb_substr($sDeposit, 0, 21).'...';
                }
                if (strlen($plg_comment) > 29) {
                    $plg_comment = mb_substr($plg_comment, 0, 28).'...';
                }
                $fam_Name = $fam_Name.' - '.$fam_Address1;
                if (strlen($fam_Name) > 31) {
                    $fam_Name = mb_substr($fam_Name, 0, 30).'...';
                }

                // Print Data
                $pdf->SetFont('Times', '', 10);
                $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
                $pdf->Cell(16, $summaryIntervalY, $plg_CheckNo, 0, 0, 'R');
                $pdf->Cell(40, $summaryIntervalY, utf8_decode($sDeposit));
                $pdf->Cell(55, $summaryIntervalY, utf8_decode($fam_Name));
                $pdf->Cell(40, $summaryIntervalY, utf8_decode($plg_comment));
                $pdf->SetFont('Courier', '', 9);
                $pdf->Cell(25, $summaryIntervalY, OutputUtils::money_localized($plg_amount), 0, 0, 'R');
                $pdf->SetFont('Times', '', 10);
                $curY += $summaryIntervalY;
                $page = $pdf->PageBreak($page);
            }
            // Update running totals
            $totalAmount += $plg_amount;
            if (array_key_exists($fun_Name, $totalFund)) {
                $totalFund[$fun_Name] += $plg_amount;
            } else {
                $totalFund[$fun_Name] = $plg_amount;
            }
            $countFund++;
            $countFamily++;
            $countReport++;
            $currentFundAmount += $plg_amount;
            $currentFamilyAmount += $plg_amount;
            $currentReportAmount += $plg_amount;
            $currentFamilyID = $fam_ID;
            $currentFamilyName = $fam_Name;
            $currentFundID = $fun_ID;
            $currentFundName = $fun_Name;
            $currentFamilyAddress = $fam_Address1;
        }

        // Print Final Summary
        // Print Family Summary
        if ($detail_level != 'summary') {
            if ($countFamily > 1) {
                $item = _('items');
            } else {
                $item = _('item');
            }
            $sFamilySummary = utf8_decode($currentFamilyName)." - ".utf8_decode($currentFamilyAddress)." - $countFamily $item:   $currency".OutputUtils::money_localized($currentFamilyAmount);
            $curY += 2;
            $pdf->SetXY(20, $curY);
            $pdf->SetFont('Times', 'I', 10);
            $pdf->Cell(176, $summaryIntervalY, $sFamilySummary, 0, 0, 'R');
            $curY += 1.75 * $summaryIntervalY;
            $page = $pdf->PageBreak($page);
        }
        // Print Fund Summary
        if ($countFund > 1) {
            $item = _('items');
        } else {
            $item = _('item');
        }
        $sFundSummary = utf8_decode($currentFundName)." ".utf8_decode(_("Total"))." - $countFund $item:   $currency".OutputUtils::money_localized($currentFundAmount);
        $pdf->SetXY(20, $curY);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(176, $summaryIntervalY, $sFundSummary, 0, 0, 'R');
        $curY += 2 * $summaryIntervalY;
        if ($detail_level != 'summary') {
            $pdf->line(40, $curY - 2, 195, $curY - 2);
        }
        $page = $pdf->PageBreak($page);
    } elseif ($sort == 'family') {

        // **********************
        // Sort by Family  Report
        // **********************

        while ($aRow = $statement->fetch( \PDO::FETCH_ASSOC )) {
            extract($aRow);
            if (!$fun_ID) {
                $fun_ID = -1;
                $fun_Name = 'Undesignated';
            }
            if (!$fam_ID) {
                $fam_ID = -1;
                $fam_Name = 'Unassigned';
                $fam_Address1 = '';
            }
            // First Family Heading
            if (!$currentFamilyID && $detail_level != 'summary') {
                $sFamilyTitle = "$fam_Name - $fam_Address1";
                $pdf->SetFont('Times', 'B', 10);
                $pdf->WriteAt(20, $curY, $sFamilyTitle);
                $curY += 1.5 * $summaryIntervalY;
            }
            // Check for new Fund
            if (($currentFundID != $fun_ID || $currentFamilyID != $fam_ID) && $currentFundID && $detail_level != 'summary') {
                // New Fund. Print Previous Fund Summary
                if ($countFund > 1) {
                    $item = _('items');
                } else {
                    $item = _('item');
                }
                $sFundSummary = utf8_decode($currentFundName)." - $countFund $item:   $currency".OutputUtils::money_localized($currentFundAmount);
                $curY += 2;
                $pdf->SetXY(20, $curY);
                $pdf->SetFont('Times', 'I', 10);
                $pdf->Cell(176, $summaryIntervalY, $sFundSummary, 0, 0, 'R');
                $curY += 1.75 * $summaryIntervalY;
                $countFund = 0;
                $currentFundAmount = 0;
                $page = $pdf->PageBreak($page);
            }
            // Check for new Family
            if ($currentFamilyID != $fam_ID && $currentFamilyID) {
                // New Family.  Print Previous Family Summary
                if ($countFamily > 1) {
                    $item = _('items');
                } else {
                    $item = _('item');
                }
                $sFamilySummary = utf8_decode($currentFamilyName)." - ".utf8_decode($currentFamilyAddress)." - $countFamily $item:   $currency".OutputUtils::money_localized($currentFamilyAmount);
                $pdf->SetXY(20, $curY);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->Cell(176, $summaryIntervalY, $sFamilySummary, 0, 0, 'R');
                $curY += 2 * $summaryIntervalY;
                if ($detail_level != 'summary') {
                    $pdf->line(40, $curY - 2, 195, $curY - 2);
                }
                $page = $pdf->PageBreak($page);

                // New Family Title
                if ($detail_level != 'summary') {
                    $sFamilyTitle = "$fam_Name - $fam_Address1";
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->WriteAt(20, $curY, $sFamilyTitle);
                    $curY += 1.5 * $summaryIntervalY;
                }
                $countFamily = 0;
                $currentFamilyAmount = 0;
            }

            // Print Deposit Detail
            if ($detail_level == 'detail') {
                // Format Data
                if ($plg_method == 'CREDITCARD') {
                    $plg_method = 'CREDIT';
                }
                if ($plg_method == 'BANKDRAFT') {
                    $plg_method = 'DRAFT';
                }
                if ($plg_method != 'CHECK') {
                    $plg_CheckNo = $plg_method;
                }
                if (strlen($plg_CheckNo) > 8) {
                    $plg_CheckNo = '...'.mb_substr($plg_CheckNo, -8, 8);
                }
                $sDeposit = _("Dep #").$plg_depID." ".date(SystemConfig::getValue('sDateFormatLong'), strtotime($dep_Date));
                if (strlen($sDeposit) > 22) {
                    $sDeposit = mb_substr($sDeposit, 0, 21).'...';
                }
                if (strlen($plg_comment) > 29) {
                    $plg_comment = mb_substr($plg_comment, 0, 28).'...';
                }
                $sFundName = $fun_Name;
                if (strlen($sFundName) > 31) {
                    $sFundName = mb_substr($sFundName, 0, 30).'...';
                }

                // Print Data
                $pdf->SetFont('Times', '', 10);
                $pdf->SetXY(SystemConfig::getValue('leftX'), $curY);
                $pdf->Cell(16, $summaryIntervalY, $plg_CheckNo, 0, 0, 'R');
                $pdf->Cell(40, $summaryIntervalY, utf8_decode($sDeposit));
                $pdf->Cell(55, $summaryIntervalY, utf8_decode($sFundName));
                $pdf->Cell(40, $summaryIntervalY, utf8_decode($plg_comment));
                $pdf->SetFont('Courier', '', 9);
                $pdf->Cell(25, $summaryIntervalY, OutputUtils::money_localized($plg_amount), 0, 0, 'R');
                $pdf->SetFont('Times', '', 10);
                $curY += $summaryIntervalY;
                $page = $pdf->PageBreak($page);
            }
            // Update running totals
            $totalAmount += $plg_amount;
            if (array_key_exists($fun_Name, $totalFund)) {
                $totalFund[$fun_Name] += $plg_amount;
            } else {
                $totalFund[$fun_Name] = $plg_amount;
            }
            $countFund++;
            $countFamily++;
            $countReport++;
            $currentFundAmount += $plg_amount;
            $currentFamilyAmount += $plg_amount;
            $currentReportAmount += $plg_amount;
            $currentFamilyID = $fam_ID;
            $currentFamilyName = $fam_Name;
            $currentFundID = $fun_ID;
            $currentFundName = $fun_Name;
            $currentFamilyAddress = $fam_Address1;
        }

        // Print Final Summary
        // Print Fund Summary
        if ($detail_level != 'summary') {
            if ($countFund > 1) {
                $item = _('items');
            } else {
                $item = _('item');
            }
            $sFundSummary = utf8_decode($currentFundName)." - $countFund $item:   $currency".OutputUtils::money_localized($currentFundAmount);
            $curY += 2;
            $pdf->SetXY(20, $curY);
            $pdf->SetFont('Times', 'I', 10);
            $pdf->Cell(176, $summaryIntervalY, $sFundSummary, 0, 0, 'R');
            $curY += 1.75 * $summaryIntervalY;
            $page = $pdf->PageBreak($page);
        }
        // Print Family Summary
        if ($countFamily > 1) {
            $item = _('items');
        } else {
            $item = _('item');
        }
        $sFamilySummary = utf8_decode($currentFamilyName)." - ".utf8_decode($currentFamilyAddress)." - $countFamily $item:   $currency".OutputUtils::money_localized($currentFamilyAmount);
        $pdf->SetXY(20, $curY);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->Cell(176, $summaryIntervalY, $sFamilySummary, 0, 0, 'R');
        $curY += 2 * $summaryIntervalY;
        if ($detail_level != 'summary') {
            $pdf->line(40, $curY - 2, 195, $curY - 2);
        }
        $page = $pdf->PageBreak($page);
    }

    // Print Report Summary
    if ($countReport > 1) {
        $item = _('items');
    } else {
        $item = _('item');
    }
    $sReportSummary = utf8_decode(_("Report Total"))." ($countReport $item):   $currency".OutputUtils::money_localized($currentReportAmount);
    $pdf->SetXY(20, $curY);
    $pdf->SetFont('Times', 'B', 10);
    $pdf->Cell(176, $summaryIntervalY, $sReportSummary, 0, 0, 'R');
    $pdf->line(40, $curY - 2, 195, $curY - 2);
    $curY += 2.5 * $summaryIntervalY;
    $page = $pdf->PageBreak($page);

    // Print Fund Totals
    $pdf->SetFont('Times', 'B', 10);
    $pdf->SetXY($curX, $curY);
    $pdf->WriteAt(20, $curY, _('Deposit totals by fund'));
    $pdf->SetFont('Courier', '', 10);
    $curY += 1.5 * $summaryIntervalY;
    ksort($totalFund);
    reset($totalFund);
    while ($FundTotal = current($totalFund)) {
        if (strlen(key($totalFund) > 22)) {
            $sfun_Name = mb_substr(key($totalFund), 0, 21).'...';
        } else {
            $sfun_Name = key($totalFund);
        }
        $pdf->SetXY(20, $curY);
        $pdf->Cell(45, $summaryIntervalY, utf8_decode($sfun_Name));
        $pdf->Cell(25, $summaryIntervalY, OutputUtils::money_localized($FundTotal), 0, 0, 'R');
        $curY += $summaryIntervalY;
        $page = $pdf->PageBreak($page);
        next($totalFund);
    }

    $pdf->FinishPage($page);

    ob_end_clean();
    $pdf->Output('DepositReport-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');

    // Output a text file
// ##################
} elseif ($output == 'csv') {

    // Settings
    //$delimiter = ',';
    $delimiter = $delemiter;
    $eol = "\r\n";

    // Build headings row
    preg_match('SELECT (.*) FROM ', $sSQL, $result);
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
