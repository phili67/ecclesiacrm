<?php
/*******************************************************************************
*
*  filename    : Reports/ZeroGivers.php
*  last change : 2005-03-26
*  description : Creates a PDF with all the tax letters for a particular calendar year.
*  Copyright 2012 Michael Wilt

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';
require '../Include/ReportFunctions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReport;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;

// Security
if ( !( $_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    Redirect('Menu.php');
    exit;
}

// Filter values
$output = InputUtils::LegacyFilterInput($_POST['output']);
$sDateStart = InputUtils::FilterDate($_POST['DateStart'], 'date');
$sDateEnd = InputUtils::FilterDate($_POST['DateEnd'], 'date');

$letterhead = InputUtils::LegacyFilterInput($_POST['letterhead']);
$remittance = InputUtils::LegacyFilterInput($_POST['remittance']);

// If CSVAdminOnly option is enabled and user is not admin, redirect to the menu.
if (!$_SESSION['user']->isFinanceEnabled() && SystemConfig::getValue('bCSVAdminOnly') && $output != 'pdf') {
    Redirect('Menu.php');
    exit;
}

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

// Build SQL Query
// Build SELECT SQL Portion
$sSQL = "SELECT DISTINCT fam_ID, fam_Name, fam_Address1, fam_Address2, fam_City, fam_State, fam_Zip, fam_Country FROM family_fam LEFT OUTER JOIN person_per ON fam_ID = per_fam_ID WHERE per_cls_ID=1 AND fam_ID NOT IN (SELECT DISTINCT plg_FamID FROM pledge_plg WHERE plg_date BETWEEN '$sDateStart' AND '$sDateEnd' AND plg_PledgeOrPayment = 'Payment') ORDER BY fam_ID";

//Execute SQL Statement
$rsReport = RunQuery($sSQL);

// Exit if no rows returned
$iCountRows = mysqli_num_rows($rsReport);
if ($iCountRows < 1) {
    header('Location: ../FinancialReports.php?ReturnMessage=NoRows&ReportType=Zero%20Givers');
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

    class PDF_ZeroGivers extends ChurchInfoReport
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
            global $letterhead, $sDateStart, $sDateEnd;
            $curY = $this->StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $letterhead);
            $curY += 2 * SystemConfig::getValue('incrementY');
            if ($sDateStart == $sDateEnd) {
            OutputUtils::FormatBirthDate($birthYear, $birthMonth, $birthDay, '-', $flags);
                $DateString = OutputUtils::FormatDate($sDateStart);
            } else {
                $DateString = OutputUtils::FormatDate($sDateStart).' - '.OutputUtils::FormatDate($sDateEnd);
            }

            $blurb = SystemConfig::getValue('sZeroGivers').' '.$DateString;//.' '.SystemConfig::getValue('sZeroGivers');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
            $curY += 30 * SystemConfig::getValue('incrementY');

            return $curY;
        }

        public function FinishPage($curY, $fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
        {
            global $remittance;
            $curY += 2 * SystemConfig::getValue('incrementY');
            $blurb = SystemConfig::getValue('sZeroGivers2');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
            $curY += 3 * SystemConfig::getValue('incrementY');
            $blurb = SystemConfig::getValue('sZeroGivers3');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
            $curY += 3 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely').',');
            $curY += 4 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sTaxSigner'));
        }
    }

    // Instantiate the directory class and build the report.
    $pdf = new PDF_ZeroGivers();

    // Loop through result array
    while ($row = mysqli_fetch_array($rsReport)) {
        extract($row);
        $curY = $pdf->StartNewPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country);

        $pdf->FinishPage($curY, $fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country);
    }

    if (SystemConfig::getValue('iPDFOutputType') == 1) {
        $pdf->Output('ZeroGivers'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
    } else {
        $pdf->Output();
    }

    // Output a text file
// ##################
} elseif ($output == 'csv') {

    // Settings
    //$delimiter = ',';
    $delimiter = $sCSVExportDelemiter;
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
    while ($row = mysqli_fetch_row($rsReport)) {
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
    header('Content-Type: text/csv;charset='.$sCSVExportCharset);
    header('Content-Disposition: attachment; filename=EcclesiaCRM-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
    header('Content-Transfer-Encoding: binary');
    
    if ($sCSVExportCharset == "UTF-8") {
       echo "\xEF\xBB\xBF";
    }
    
    echo $buffer;
}
