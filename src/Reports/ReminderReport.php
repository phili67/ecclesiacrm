<?php
/*******************************************************************************
*
*  filename    : Reports/ReminderReport.php
*  last change : 2005-03-26
*  description : Creates a PDF of the current deposit slip
*  Copyright 2004-2005  Michael Wilt, Timothy Dearborn
******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReport;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\DonationFundQuery;

use Propel\Runtime\Propel;



// Security
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

//Get the Fiscal Year ID out of the querystring
$iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
$_SESSION['idefaultFY'] = $iFYID; // Remember the chosen FYID
$output = InputUtils::LegacyFilterInput($_POST['output']);
$pledge_filter = InputUtils::LegacyFilterInput($_POST['pledge_filter']);
$only_owe = InputUtils::LegacyFilterInput($_POST['only_owe']);

// If CSVAdminOnly option is enabled and user is not admin, redirect to the menu.
if (!SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getValue('bCSVAdminOnly')) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if (!empty($_POST['classList'])) {
    $classList = $_POST['classList'];

    if ($classList[0]) {
        $ormClassifications = ListOptionQuery::create()
            ->filterById(1)
            ->orderByOptionSequence()
            ->find();

        $inClassList = '(';
        $notInClassList = '(';

        foreach ($ormClassifications as $classification) {
            if (in_array($classification->getOptionId(), $classList)) {
                if ($inClassList == '(') {
                    $inClassList .= $classification->getOptionId();
                } else {
                    $inClassList .= ','.$classification->getOptionId();
                }
            } else {
                if ($notInClassList == '(') {
                    $notInClassList .= $classification->getOptionId();
                } else {
                    $notInClassList .= ','.$classification->getOptionId();
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

// Get all the families
$sSQL = 'SELECT * FROM family_fam';

if ($classList[0]) {
    $sSQL .= ' LEFT JOIN person_per ON fam_ID=per_fam_ID';
}
$sSQL .= ' WHERE';

$criteria = '';

// Filter by Family
if (!empty($_POST['family'])) {
    $count = 0;
    foreach ($_POST['family'] as $famID) {
        $fam[$count++] = InputUtils::LegacyFilterInput($famID, 'int');
    }
    if ($count == 1) {
        if ($fam[0]) {
            $q = " fam_ID='$fam[0]'";
            if ($criteria) {
                $criteria .= ' AND'.$q;
            } else {
                $criteria = $q;
            }
        }
    } else {
        $q = " (fam_ID='$fam[0]'";
        if ($criteria) {
            $criteria .= ' AND'.$q;
        } else {
            $criteria = $q;
        }
        for ($i = 1; $i < $count; $i++) {
            $criteria .= " OR fam_ID='$fam[$i]'";
        }
        $criteria .= ')';
    }
}

if ($classList[0]) {
    $q = ' per_cls_ID IN '.$inClassList.' AND per_fam_ID NOT IN (SELECT DISTINCT per_fam_ID FROM person_per WHERE per_cls_ID IN '.$notInClassList.')';
    if ($criteria) {
        $criteria .= ' AND'.$q;
    } else {
        $criteria = $q;
    }
}

if (!$criteria) {
    $criteria = ' 1';
}
$sSQL .= $criteria;

//var_dump($sSQL);
$connection = Propel::getConnection();

$pdoFamilies = $connection->prepare($sSQL);
$pdoFamilies->execute();


$sSQLFundCriteria = '';

// Build criteria string for funds
if (!empty($_POST['funds'])) {
    $fundCount = 0;
    foreach ($_POST['funds'] as $fundID) {
        $fund[$fundCount++] = InputUtils::LegacyFilterInput($fundID, 'int');
    }
    if ($fundCount == 1) {
        if ($fund[0]) {
            $sSQLFundCriteria .= " AND plg_fundID='$fund[0]' ";
        }
    } else {
        $sSQLFundCriteria .= " AND (plg_fundID ='$fund[0]'";
        for ($i = 1; $i < $fundCount; $i++) {
            $sSQLFundCriteria .= " OR plg_fundID='$fund[$i]'";
        }
        $sSQLFundCriteria .= ') ';
    }
}

// Make the string describing the fund filter
if ($fundCount > 0) {
    if ($fundCount == 1) {
        if ($fund[0] == _('All Funds')) {
            $fundOnlyString = _(' for all funds');
        } else {
            $fundOnlyString = _(' for fund ');
        }
    } else {
        $fundOnlyString = _('for funds ');
    }
    for ($i = 0; $i < $fundCount; $i++) {
        $ormOneFund = DonationFundQuery::create()
            ->findOneById($fund[$i]);

        $fundOnlyString .= $ormOneFund->getName();
        if ($i < $fundCount - 1) {
            $fundOnlyString .= ', ';
        }
    }
}

// Get the list of funds
$ormFunds = DonationFundQuery::create()->find();

// Create PDF Report
// *****************
class PDF_ReminderReport extends ChurchInfoReport
{
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);

        $this->SetFont('Times', '', 10);
        $this->SetMargins(20, 20);

        $this->SetAutoPageBreak(false);
    }

    public function StartNewPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $fundOnlyString, $iFYID)
    {
        $curY = $this->StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country);
        $curY += 2 * SystemConfig::getValue('incrementY');
        $blurb = SystemConfig::getValue('sReminder1')." ".MiscUtils::MakeFYString($iFYID).$fundOnlyString.'.';
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 2 * SystemConfig::getValue('incrementY');

        return $curY;
    }

    public function FinishPage($curY)
    {
        $curY += 2 * SystemConfig::getValue('incrementY');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely').',');
        $curY += 4 * SystemConfig::getValue('incrementY');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sReminderSigner'));
    }
}

// Instantiate the directory class and build the report.
$pdf = new PDF_ReminderReport();

// Loop through families
while ( $family = $pdoFamilies->fetch( \PDO::FETCH_BOTH ) ) {
    // Check for pledges if filtering by pledges
    if ($pledge_filter == 'pledge') {
        $temp = "SELECT plg_plgID FROM pledge_plg
            WHERE plg_FamID='" . $family['fam_ID'] . "' AND plg_PledgeOrPayment='Pledge' AND plg_FYID=$iFYID" . $sSQLFundCriteria;

        $pdoPledgeCheck = $connection->prepare($sSQL);
        $pdoPledgeCheck->execute();

        if ($pdoPledgeCheck->rowCount() == 0) {
            continue;
        }
    }

    // Get pledges and payments for this family and this fiscal year
    $sSQL = 'SELECT *, b.fun_Name AS fundName FROM pledge_plg
             LEFT JOIN donationfund_fun b ON plg_fundID = b.fun_ID
             WHERE plg_FamID = '.$family['fam_ID'].' AND plg_FYID = '.$iFYID.$sSQLFundCriteria.' ORDER BY plg_date';

    $pdoPledges = $connection->prepare($sSQL);
    $pdoPledges->execute();

    // If there is no pledge or a payment go to next family
    if ($pdoPledges->rowCount() == 0) {
        continue;
    }

    if ($only_owe == 'yes') {
        // Run through pledges and payments for this family to see if there are any unpaid pledges
        $oweByFund = [];
        $bOwe = 0;
        while ( $aRow = $pdoPledges->fetch( \PDO::FETCH_BOTH ) ) {
            if ($aRow['plg_PledgeOrPayment'] == 'Pledge') {
                if (array_key_exists($aRow['plg_fundID'], $oweByFund)) {
                    $oweByFund[$aRow['plg_fundID']] -= $aRow['plg_amount'];
                } else {
                    $oweByFund[$aRow['plg_fundID']] = -$aRow['plg_amount'];
                }
            } else {
                if (array_key_exists($aRow['plg_fundID'], $oweByFund)) {
                    $oweByFund[$aRow['plg_fundID']] += $aRow['plg_amount'];
                } else {
                    $oweByFund[$aRow['plg_fundID']] = $aRow['plg_amount'];
                }
            }
        }
        foreach ($oweByFund as $oweRow) {
            if ($oweRow < 0) {
                $bOwe = 1;
            }
        }
        if (!$bOwe) {
            continue;
        }
    }

    // Add a page for this reminder report
    $curY = $pdf->StartNewPage($family['fam_ID'], $family['fam_Name'], $family['fam_Address1'], $family['fam_Address2'], $family['fam_City'], $family['fam_State'], $family['fam_Zip'], $family['fam_Country'], fundOnlyString, $iFYID);

    // Get pledges only
    $sSQL = 'SELECT *, b.fun_Name AS fundName FROM pledge_plg
             LEFT JOIN donationfund_fun b ON plg_fundID = b.fun_ID
             WHERE plg_FamID = '.$family['fam_ID'].' AND plg_FYID = '.$iFYID.$sSQLFundCriteria." AND plg_PledgeOrPayment = 'Pledge' ORDER BY plg_date";

    $pdoPledges = $connection->prepare($sSQL);
    $pdoPledges->execute();

    $totalAmountPledges = 0;
    $fundPledgeTotal = [];

    $summaryDateX = SystemConfig::getValue('leftX');
    $summaryFundX = 45;
    $summaryAmountX = 80;

    $summaryDateWid = $summaryFundX - $summaryDateX;
    $summaryFundWid = $summaryAmountX - $summaryFundX;
    $summaryAmountWid = 15;

    $summaryIntervalY = 4;

    if ($pdoPledges->rowCount() == 0) {
        $curY += $summaryIntervalY;
        $noPledgeString = SystemConfig::getValue('sReminderNoPledge').'('.$fundOnlyString.')';
        $pdf->WriteAt($summaryDateX, $curY, $noPledgeString);
        $curY += 2 * $summaryIntervalY;
    } else {
        $curY += $summaryIntervalY;
        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell($summaryDateX, $curY, $summaryDateWid, _("Pledge"));
        $curY += $summaryIntervalY;

        $pdf->SetFont('Times', 'B', 9);

        $pdf->WriteAtCell($summaryDateX, $curY, $summaryDateWid, _("Date"));
        $pdf->WriteAtCell($summaryFundX, $curY, $summaryFundWid, _("Fund"));
        $pdf->WriteAtCell($summaryAmountX, $curY, $summaryAmountWid, _("Amount"));

        $curY += $summaryIntervalY;

        $totalAmount = 0;
        $cnt = 0;

        while ( $aRow = $pdoPledges->fetch( \PDO::FETCH_BOTH ) ) {
            if (strlen($aRow['fundName']) > 19) {
                $fundName = mb_substr($aRow['fundName'], 0, 18).'...';
            }

            $pdf->SetFont('Times', '', 10);

            $pdf->WriteAtCell($summaryDateX, $curY, $summaryDateWid, date(SystemConfig::getValue('sDateFormatLong'), strtotime($aRow['plg_date'])));
            $pdf->WriteAtCell($summaryFundX, $curY, $summaryFundWid, $aRow['fundName']);

            $pdf->SetFont('Courier', '', 8);

            $pdf->PrintRightJustifiedCell($summaryAmountX, $curY, $summaryAmountWid, OutputUtils::money_localized($aRow['plg_amount']));

            if (array_key_exists($fundName, $fundPledgeTotal)) {
                $fundPledgeTotal[$fundName] += $aRow['plg_amount'];
            } else {
                $fundPledgeTotal[$fundName] = $aRow['plg_amount'];
            }
            $totalAmount += $aRow['plg_amount'];
            $cnt += 1;

            $curY += $summaryIntervalY;
        }
        $pdf->SetFont('Times', '', 10);
        if ($cnt > 1) {
            $pdf->WriteAtCell($summaryFundX, $curY, $summaryFundWid, _("Total pledges"));
            $pdf->SetFont('Courier', '', 8);
            $totalAmountStr = OutputUtils::money_localized($totalAmount);
            $pdf->PrintRightJustifiedCell($summaryAmountX, $curY, $summaryAmountWid, $totalAmountStr);
            $curY += $summaryIntervalY;
        }
        $totalAmountPledges = $totalAmount;
    }

    // Get payments only
    $sSQL = 'SELECT *, b.fun_Name AS fundName FROM pledge_plg
             LEFT JOIN donationfund_fun b ON plg_fundID = b.fun_ID
             WHERE plg_FamID = '.$family['fam_ID'].' AND plg_FYID = '.$iFYID.$sSQLFundCriteria." AND plg_PledgeOrPayment = 'Payment' ORDER BY plg_date";
    $pdoPledges = $connection->prepare($sSQL);
    $pdoPledges->execute();


    $totalAmountPayments = 0;
    $fundPaymentTotal = [];
    if ($pdoPledges->rowCount() == 0) {
        $curY += $summaryIntervalY;
        $pdf->WriteAt($summaryDateX, $curY, SystemConfig::getValue('sReminderNoPayments'));
        $curY += 2 * $summaryIntervalY;
    } else {
        $summaryDateX = SystemConfig::getValue('leftX');
        $summaryCheckNoX = 40;
        $summaryMethodX = 60;
        $summaryFundX = 85;
        $summaryMemoX = 120;
        $summaryAmountX = 170;
        $summaryIntervalY = 4;

        $summaryDateWid = $summaryCheckNoX - $summaryDateX;
        $summaryCheckNoWid = $summaryMethodX - $summaryCheckNoX;
        $summaryMethodWid = $summaryFundX - $summaryMethodX;
        $summaryFundWid = $summaryMemoX - $summaryFundX;
        $summaryMemoWid = $summaryAmountX - $summaryMemoX;
        $summaryAmountWid = 15;

        $curY += $summaryIntervalY;
        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell($summaryDateX, $curY, $summaryDateWid, _("Payments"));
        $curY += $summaryIntervalY;

        $pdf->SetFont('Times', 'B', 9);

        $pdf->WriteAtCell($summaryDateX, $curY, $summaryDateWid, _("Date"));
        $pdf->WriteAtCell($summaryCheckNoX, $curY, $summaryCheckNoWid, _("Chk No."));
        $pdf->WriteAtCell($summaryMethodX, $curY, $summaryMethodWid, _("PmtMethod"));
        $pdf->WriteAtCell($summaryFundX, $curY, $summaryFundWid, _("Fund"));
        $pdf->WriteAtCell($summaryMemoX, $curY, $summaryMemoWid, _("Memo"));
        $pdf->WriteAtCell($summaryAmountX, $curY, $summaryAmountWid, _("Amount"));

        $curY += $summaryIntervalY;

        $totalAmount = 0;
        $cnt = 0;
        while ( $aRow = $pdoPledges->fetch( \PDO::FETCH_BOTH ) ) {
            // Format Data
            if (strlen($aRow['plg_CheckNo']) > 8) {
                $plg_CheckNo = '...'.mb_substr($aRow['plg_CheckNo'], -8, 8);
            }
            if (strlen($fundName) > 19) {
                $fundName = mb_substr($fundName, 0, 18).'...';
            }
            if (strlen($aRow['plg_comment']) > 30) {
                $plg_comment = mb_substr($aRow['plg_comment'], 0, 30).'...';
            }

            $pdf->SetFont('Times', '', 10);

            $pdf->WriteAtCell($summaryDateX, $curY, $summaryDateWid, date(SystemConfig::getValue('sDateFormatLong'), strtotime($aRow['plg_date'])));
            $pdf->PrintRightJustifiedCell($summaryCheckNoX, $curY, $summaryCheckNoWid, $aRow['plg_CheckNo']);
            $pdf->WriteAtCell($summaryMethodX, $curY, $summaryMethodWid, _($aRow['plg_method']));
            $pdf->WriteAtCell($summaryFundX, $curY, $summaryFundWid, $aRow['fundName']);
            $pdf->WriteAtCell($summaryMemoX, $curY, $summaryMemoWid, $aRow['plg_comment']);

            $pdf->SetFont('Courier', '', 8);

            $pdf->PrintRightJustifiedCell($summaryAmountX, $curY, $summaryAmountWid, OutputUtils::money_localized($aRow['plg_amount']));

            $totalAmount += $aRow['plg_amount'];
            if (array_key_exists($fundName, $fundPaymentTotal)) {
                $fundPaymentTotal[$fundName] += $aRow['plg_amount'];
            } else {
                $fundPaymentTotal[$fundName] = $aRow['plg_amount'];
            }
            $cnt += 1;

            $curY += $summaryIntervalY;

            if ($curY > 220) {
                $pdf->AddPage();
                $curY = 20;
            }
        }
        $pdf->SetFont('Times', '', 10);
        if ($cnt > 1) {
            $pdf->WriteAtCell($summaryMemoX, $curY, $summaryMemoWid, _("Total payments"));
            $pdf->SetFont('Courier', '', 8);
            $totalAmountString = OutputUtils::money_localized($totalAmount);
            $pdf->PrintRightJustifiedCell($summaryAmountX, $curY, $summaryAmountWid, $totalAmountString);
            $curY += $summaryIntervalY;
        }
        $pdf->SetFont('Times', '', 10);
        $totalAmountPayments = $totalAmount;
    }

    $curY += $summaryIntervalY;

    if ($ormFunds->count() > 0) {
        foreach ($ormFunds as $fund)
            $fun_name = $fund->getName();
            if (array_key_exists($fun_name, $fundPledgeTotal) && $fundPledgeTotal[$fun_name] > 0) {
                if (array_key_exists($fun_name, $fundPaymentTotal)) {
                    $amountDue = $fundPledgeTotal[$fun_name] - $fundPaymentTotal[$fun_name];
                } else {
                    $amountDue = $fundPledgeTotal[$fun_name];
                }
                if ($amountDue < 0) {
                    $amountDue = 0;
                }
                $amountStr = sprintf(_("Amount due for")." ".$fun_name." : ".OutputUtils::money_localized($amountDue));
                $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $amountStr);
                $curY += $summaryIntervalY;
            }
        }

    $pdf->FinishPage($curY);
}

if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('ReminderReport'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
