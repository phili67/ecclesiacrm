<?php
/*******************************************************************************
*
*  filename    : Reports/ReminderReport.php
*  last change : 2005-03-26
*  description : Creates a PDF of the current deposit slip
*  Copyright   : Philippe Logel 2019 all rights reserved
*
******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use Propel\Runtime\Propel;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\PledgeQuery;

// Security
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$delimiter = SessionUser::getUser()->CSVExportDelemiter();
$charset   = SessionUser::getUser()->CSVExportCharset();

// Filter Values
$output = InputUtils::LegacyFilterInput($_POST['output']);
$iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
$_SESSION['idefaultFY'] = $iFYID; // Remember the chosen FYID

// If CSVAdminOnly option is enabled and user is not admin, redirect to the menu.
if (!SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getValue('bCSVAdminOnly') && $output != 'pdf') {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$connection = Propel::getConnection();

// Get the list of funds
$funds = DonationFundQuery::Create()->orderByActive()->orderByName()->findByActive('true');

$overpaid = [];
$underpaid = [];
$pledgeFundTotal = [];
$paymentFundTotal = [];

foreach ($funds as $fund) {
    $fun_name = $fund->getName();
    $overpaid[$fun_name] = 0;
    $underpaid[$fun_name] = 0;
    $paymentCnt[$fun_name] = 0;
    $pledgeCnt[$fun_name] = 0;
    $pledgeFundTotal[$fun_name] = 0;
    $paymentFundTotal[$fun_name] = 0;
}

$pledgeFundTotal['Unassigned'] = 0;
$paymentFundTotal['Unassigned'] = 0;
$paymentCnt['Unassigned'] = 0;
$pledgeCnt['Unassigned'] = 0;

// Get pledges and payments for this fiscal year
$pledges = PledgeQuery::Create()
            ->leftJoinDonationFund()
              ->withColumn('donationfund_fun.fun_Active', 'fundActive')
              ->withColumn('donationfund_fun.fun_Name', 'fundName')
            ->filterByFyid ($iFYID);

if (!empty($_POST['funds'])) {
     $count = 0;
     foreach ($_POST['funds'] as $fundID) {
         $fund_buf[$count++] = InputUtils::LegacyFilterInput($fundID, 'int');
     }
     if ($count == 1) {
         if ($fund_buf[0]) {
             $pledges->filterByFundid ($fund_buf[0]);
         }
     } else {
         $pledges->filterByFundid ($fund_buf);
     }
}

$pledges->useDonationFundQuery()
        //->orderByActive() // this can't be done due to the algorithm below
        //->orderByName()
        ->endUse()
        ->orderByFamId()
        ->find();




// Create PDF Report
// *****************
if ($output == 'pdf') {
    class PDF_PledgeSummaryReport extends ChurchInfoReportTCPDF
    {
        // Constructor
        public function __construct()
        {
            parent::__construct('P', 'mm', $this->paperFormat);

            $this->SetFont('Times', '', 10);
            $this->SetMargins(0, 0);

            $this->SetAutoPageBreak(false);
            $this->AddPage();
        }
    }

    // Instantiate the directory class and build the report.
    $pdf = new PDF_PledgeSummaryReport();

    // Total all the pledges and payments by fund.  Compute overpaid and underpaid for each family as
    // we go through them.

    // This algorithm is complicated for the sake of efficiency.  The query gets all the payments ordered
    // by family.  As the loop below goes through the payments, it collects pledges and payment for each
    // family, by fund.  It needs to go around one extra time so the last payment gets posted to underpaid/
    // overpaid.
    $curFam = 0;
    $paidThisFam = [];
    $pledgeThisFam = [];
    $totRows = $pledges->count();
    $thisRow = 0;
    $fundName = '';
    $plg_famID = 0;

    foreach ($pledges as $pledge) {
        $fundName = $pledge->getFundName();

        if ($fundName == '') {
            $fundName = 'Unassigned';
        }

        if ($pledge->getFamId() != $curFam) {
            // Switching families.  Post the results for the previous family and initialize for the new family

            foreach ($funds as $fund) {
                $fun_name = $fund->getName();
                if (array_key_exists($fun_name, $pledgeThisFam) && $pledgeThisFam[$fun_name] > 0) {
                    $thisPledge = $pledgeThisFam[$fun_name];
                } else {
                    $thisPledge = 0.0;
                }
                if (array_key_exists($fun_name, $paidThisFam) && $paidThisFam[$fun_name] > 0) {
                    $thisPay = $paidThisFam[$fun_name];
                } else {
                    $thisPay = 0.0;
                }
                $pledgeDiff = $thisPay - $thisPledge;
                if ($pledgeDiff > 0) {
                    $overpaid[$fun_name] += $pledgeDiff;
                } else {
                    $underpaid[$fun_name] -= $pledgeDiff;
                }
            }
            $paidThisFam = [];
            $pledgeThisFam = [];
            $curFam = $pledge->getFamId();
        }

        if ($pledge->getPledgeorpayment() == 'Pledge') {
            if (array_key_exists($fundName, $pledgeFundTotal)) {
                $pledgeFundTotal[$fundName] += $pledge->getAmount();
                $pledgeCnt[$fundName] += 1;
            } else {
                $pledgeFundTotal[$fundName] = $pledge->getAmount();
                $pledgeCnt[$fundName] = 1;
            }
            if (array_key_exists($fundName, $pledgeThisFam)) {
                $pledgeThisFam[$fundName] += $pledge->getAmount();
            } else {
                $pledgeThisFam[$fundName] = $pledge->getAmount();
            }
        } elseif ($pledge->getPledgeorpayment() == 'Payment') {
            if (array_key_exists($fundName, $paymentFundTotal)) {
                $paymentFundTotal[$fundName] += $pledge->getAmount();
                $paymentCnt[$fundName] += 1;
            } else {
                $paymentFundTotal[$fundName] = $pledge->getAmount();
                $paymentCnt[$fundName] = 1;
            }
            if (array_key_exists($fundName, $paidThisFam)) {
                $paidThisFam[$fundName] += $pledge->getAmount();
            } else {
                $paidThisFam[$fundName] = $pledge->getAmount();
            }
        }

        $thisRow++;
    }

    // we loop a last time in the fund to finish the work
    foreach ($funds as $fund) {
        $fun_name = $fund->getName();
        if (array_key_exists($fun_name, $pledgeThisFam) && $pledgeThisFam[$fun_name] > 0) {
            $thisPledge = $pledgeThisFam[$fun_name];
        } else {
            $thisPledge = 0.0;
        }
        if (array_key_exists($fun_name, $paidThisFam) && $paidThisFam[$fun_name] > 0) {
            $thisPay = $paidThisFam[$fun_name];
        } else {
            $thisPay = 0.0;
        }
        $pledgeDiff = $thisPay - $thisPledge;
        if ($pledgeDiff > 0) {
            $overpaid[$fun_name] += $pledgeDiff;
        } else {
            $underpaid[$fun_name] -= $pledgeDiff;
        }
    }

    $nameX = 20;
    $pledgeX = 60;
    $paymentX = 80;
    $pledgeCountX = 100;
    $paymentCountX = 120;
    $underpaidX = 145;
    $overpaidX = 170;
    $curY = 20;

    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchName'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchAddress'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchCity').', '.SystemConfig::getValue('sChurchState').'  '.SystemConfig::getValue('sChurchZip'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchPhone').'  '.SystemConfig::getValue('sChurchEmail'));
    $curY += 2 * SystemConfig::getValue('incrementY');

    $blurb = SystemConfig::getValue('sPledgeSummary1').' ';
    $blurb .= MiscUtils::MakeFYString($iFYID);
    $blurb .= " ".SystemConfig::getValue('sPledgeSummary2').' '.date(SystemConfig::getValue('sDatePickerFormat')).'.';
    $pdf->WriteAt($nameX, $curY, $blurb);

    $curY += 3 * SystemConfig::getValue('incrementY');

    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAt($nameX, $curY, _('Fund'));
    $pdf->PrintRightJustified($pledgeX, $curY, _('Pledges'));
    $pdf->PrintRightJustified($paymentX, $curY, _('Payments'));
    $pdf->PrintRightJustified($pledgeCountX+6, $curY, "# "._('Pledges'));
    $pdf->PrintRightJustified($paymentCountX+8, $curY, "# "._('Payments'));
    $pdf->PrintRightJustified($underpaidX, $curY, _('Overpaid'));
    $pdf->PrintRightJustified($overpaidX, $curY, _('Underpaid'));
    $pdf->SetFont('Times', '', 10);
    $curY += SystemConfig::getValue('incrementY');

    foreach ($funds as $fund) {
        $fun_name = $fund->getName();
        if ($pledgeFundTotal[$fun_name] > 0 || $paymentFundTotal[$fun_name] > 0) {
            if (strlen($fun_name) > 30) {
                $short_fun_name = mb_substr($fun_name, 0, 30).'...';
            } else {
                $short_fun_name = $fun_name;
            }
            $pdf->WriteAt($nameX, $curY, _($short_fun_name));
            $amountStr = OutputUtils::money_localized($pledgeFundTotal[$fun_name]);
            $pdf->PrintRightJustified($pledgeX, $curY, $amountStr);
            $amountStr = OutputUtils::money_localized($paymentFundTotal[$fun_name]);
            $pdf->PrintRightJustified($paymentX, $curY, $amountStr);
            $pdf->PrintRightJustified($pledgeCountX, $curY, $pledgeCnt[$fun_name]);
            $pdf->PrintRightJustified($paymentCountX, $curY, $paymentCnt[$fun_name]);

            $amountStr = OutputUtils::money_localized($overpaid[$fun_name]);
            $pdf->PrintRightJustified($underpaidX, $curY, $amountStr);
            $amountStr = OutputUtils::money_localized($underpaid[$fun_name]);
            $pdf->PrintRightJustified($overpaidX, $curY, $amountStr);
            $curY += SystemConfig::getValue('incrementY');
        }
    }

    if ($pledgeFundTotal['Unassigned'] > 0 || $paymentFundTotal['Unassigned'] > 0) {
        $pdf->WriteAt($nameX, $curY, 'Unassigned');
        $amountStr = OutputUtils::money_localized($pledgeFundTotal['Unassigned']);
        $pdf->PrintRightJustified($pledgeX, $curY, $amountStr);
        $amountStr = OutputUtils::money_localized($paymentFundTotal['Unassigned']);
        $pdf->PrintRightJustified($paymentX, $curY, $amountStr);
        $pdf->PrintRightJustified($pledgeCountX, $curY, $pledgeCnt['Unassigned']);
        $pdf->PrintRightJustified($paymentCountX, $curY, $paymentCnt['Unassigned']);
        $curY += SystemConfig::getValue('incrementY');
    }

    header('Pragma: public');  // Needed for IE when using a shared SSL certificate
    ob_end_clean();
    if (SystemConfig::getValue('iPDFOutputType') == 1) {
        $pdf->Output('PledgeSummaryReport'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
    } else {
        $pdf->Output();
    }

    // Output a text file
// ##################
} elseif ($output == 'csv') {

    // Settings
    $delimiter = $delimiter;
    $eol = "\r\n";

    // Build headings row
    $headings = explode(',', "plg_plgID, plg_FYID, plg_amount, plg_PledgeOrPayment, plg_fundID, plg_famID, b.fun_Name AS fundName, b.fun_Active AS fundActive");
    $buffer = '';
    foreach ($headings as $heading) {
        $buffer .= trim($heading).$delimiter;
    }
    // Remove trailing delimiter and add eol
    $buffer = mb_substr($buffer, 0, -1).$eol;

    // Add data
    foreach ($pledges as $pledge) {
      $buffer .= $pledge->getId().$delimiter.$pledge->getFyid().$delimiter.OutputUtils::money_localized($pledge->getAmount()).$delimiter;
      $buffer .= _($pledge->getPledgeorpayment()).$delimiter.$pledge->getFundid().$delimiter.$pledge->getFamId().$delimiter;
      $buffer .= _($pledge->getFundName()).$delimiter._($pledge->getFundActive());
      $buffer .= $eol;
    }

    // Export file
    header('Content-type: application/csv;charset='.$charset);
    header('Content-Disposition: attachment; filename=Pledges-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    //add BOM to fix UTF-8 in Excel 2016 but not under, so the problem is solved with the charset variable
    if ($charset == "UTF-8") {
        echo "\xEF\xBB\xBF";
    }
    echo $buffer;
}
