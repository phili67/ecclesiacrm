<?php
/*******************************************************************************
*
*  filename    : Reports/FamilyPledgeSummary.php
*  last change : 2005-03-26
*  description : Creates a PDF summary of pledge status by family
*  Copyright 2004-2009  Michael Wilt

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\DonationFundQuery;

use Propel\Runtime\Propel;

// Security
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
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
}

//Get the Fiscal Year ID out of the querystring
$iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
$_SESSION['idefaultFY'] = $iFYID; // Remember the chosen FYID
$output = InputUtils::LegacyFilterInput($_POST['output']);
$pledge_filter = '';
if (array_key_exists('pledge_filter', $_POST)) {
    $pledge_filter = InputUtils::LegacyFilterInput($_POST['pledge_filter']);
}
$only_owe = '';
if (array_key_exists('only_owe', $_POST)) {
    $only_owe = InputUtils::LegacyFilterInput($_POST['only_owe']);
}

// If CSVAdminOnly option is enabled and user is not admin, redirect to the menu.
if (!SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getValue('bCSVAdminOnly')) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

// Get all the families
$sSQL = 'SELECT DISTINCT fam_ID, fam_Name FROM family_fam';

if ($classList[0]) {
    $sSQL .= ' LEFT JOIN person_per ON fam_ID=per_fam_ID';
}
$sSQL .= ' WHERE';

$criteria = '';
if ($classList[0] && $notInClassList != '()' && $inClassList != '()') {
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

// Filter by Family
if (!empty($_POST['family'])) {
    $count = 0;
    foreach ($_POST['family'] as $famID) {
        $fam[$count++] = InputUtils::LegacyFilterInput($famID, 'int');
    }
    if ($count == 1) {
        if ($fam[0]) {
            $sSQL .= " AND fam_ID='$fam[0]' ";
        }
    } else {
        $sSQL .= " AND (fam_ID='$fam[0]'";
        for ($i = 1; $i < $count; $i++) {
            $sSQL .= " OR fam_ID='$fam[$i]'";
        }
        $sSQL .= ') ';
    }
}

$sSQL .= ' ORDER BY fam_Name';

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

// Get the list of
$ormFunds = DonationFundQuery::create()->find();

$fundPaymentTotal = [];
$fundPledgeTotal = [];
foreach ($ormFunds as $fund) {
    $fun_name = $fund->getName();
    $fundPaymentTotal[$fun_name] = 0;
    $fundPledgeTotal[$fun_name] = 0;
}

// Create PDF Report
// *****************
class PDF_FamilyPledgeSummaryReport extends ChurchInfoReportTCPDF
{
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);

        $this->SetFont('Times', '', 10);
        $this->SetMargins(20, 20);

        $this->SetAutoPageBreak(false);
        $this->incrementY = 0;
    }
}

// Instantiate the directory class and build the report.
$pdf = new PDF_FamilyPledgeSummaryReport();
$pdf->AddPage();

$leftX = 10;
$famNameX = 10;
$famMethodX = 90;
$famFundX = 120;
$famPledgeX = 150;
$famPayX = 170;
$famOweX = 190;

$famNameWid = $famMethodX - $famNameX;
$famMethodWid = $famFundX - $famMethodX;
$famFundWid = $famPledgeX - $famFundX;
$famPledgeWid = $famPayX - $famPledgeX;
$famPayWid = $famOweX - $famPayX;
$famOweWid = $famPayWid;

$pageTop = 10;
$y = $pageTop;
$lineInc = 4.5;

$pdf->WriteAt($leftX, $y, _('Pledge Family Summary'));
$y += $lineInc;

$pdf->WriteAtCell($famNameX, $y, $famNameWid, _('Name'));
$pdf->WriteAtCell($famMethodX, $y, $famMethodWid, _('Method'));
$pdf->WriteAtCell($famFundX, $y, $famFundWid, _('Fund'));
$pdf->WriteAtCell($famPledgeX, $y, $famPledgeWid, _('Pledge'));
$pdf->WriteAtCell($famPayX, $y, $famPayWid, _('Paid'));
$pdf->WriteAtCell($famOweX, $y, $famOweWid, _('Owe'));
$y += $lineInc;

// Loop through families
while ($family = $pdoFamilies->fetch( \PDO::FETCH_BOTH )) {
    // Check for pledges if filtering by pledges
    if ($pledge_filter == 'pledge') {
        $temp = "SELECT plg_plgID FROM pledge_plg
			WHERE plg_FamID='".$family['fam_ID']."' AND plg_PledgeOrPayment='Pledge' AND plg_FYID=$iFYID".$sSQLFundCriteria;

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

        while ($aRow = $pdoPledges->fetch(PDO::FETCH_ASSOC)){ // permet de récupérer le tableau associatif
            if ($aRow['plg_PledgeOrPayment'] == 'Pledge') {
                $oweByFund[$aRow['plg_fundID']] -= $aRow['plg_amount'];
            } else {
                $oweByFund[$aRow['plg_fundID']] += $aRow['plg_amount'];
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

    // Get pledges only
    $sSQL = 'SELECT *, b.fun_Name AS fundName FROM pledge_plg
			 LEFT JOIN donationfund_fun b ON plg_fundID = b.fun_ID
			 WHERE plg_FamID = '.$family['fam_ID'].' AND plg_FYID = '.$iFYID.$sSQLFundCriteria." AND plg_PledgeOrPayment = 'Pledge' ORDER BY plg_date";

    $pdoPledges = $connection->prepare($sSQL);
    $pdoPledges->execute();

    $totalAmountPledges = 0;

    if ($pdoPledges->rowCount() > 0) {
        $totalAmount = 0;
        $cnt = 0;
        while ($aRow = $pdoPledges->fetch(PDO::FETCH_ASSOC)){
            $fundName = $aRow['fundName'];
            if (strlen($aRow['fundName']) > 19) {
                $fundName = mb_substr($aRow['fundName'], 0, 18).'...';
            }

            $fundPledgeTotal[$fundName] += (float)$aRow['plg_amount'];
            $fundPledgeMethod[$fundName] = $aRow['plg_method'];
            $totalAmount += (float)$aRow['plg_amount'];
            $cnt += 1;
        }
        $pdf->SetFont('Times', '', 10);
        $totalAmountPledges = $totalAmount;
    }

    // Get payments only
    $sSQL = 'SELECT *, b.fun_Name AS fundName FROM pledge_plg
			 LEFT JOIN donationfund_fun b ON plg_fundID = b.fun_ID
			 WHERE plg_FamID = '.$family['fam_ID'].' AND plg_FYID = '.$iFYID.$sSQLFundCriteria." AND plg_PledgeOrPayment = 'Payment' ORDER BY plg_date";

    $pdoPledges = $connection->prepare($sSQL);
    $pdoPledges->execute();

    $totalAmountPayments = 0;
    if ($pdoPledges->rowCount() > 0) {
        $totalAmount = 0;
        $cnt = 0;
        while ($aRow = $pdoPledges->fetch(PDO::FETCH_ASSOC)){
            $fundName = $aRow['fundName'];

            $totalAmount += $aRow['plg_amount'];
            $fundPaymentTotal[$fundName] += $aRow['plg_amount'];
            $cnt += 1;
        }
        $totalAmountPayments = $totalAmount;
    }

    if ($ormFunds->count() > 0) {
        foreach ($ormFunds as $fund) {
            $fun_name = $fund->getName();
            if ($fundPledgeTotal[$fun_name] > 0) {
                $amountDue = $fundPledgeTotal[$fun_name] - $fundPaymentTotal[$fun_name];
                if ($amountDue < 0) {
                    $amountDue = 0;
                }

                $pdf->WriteAtCell($famNameX, $y, $famNameWid, $pdf->MakeSalutation($family['fam_ID']));
                $pdf->WriteAtCell($famPledgeX, $y, $famPledgeWid, OutputUtils::money_localized($fundPledgeTotal[$fun_name]));
                $pdf->WriteAtCell($famMethodX, $y, $famMethodWid, _($fundPledgeMethod[$fun_name]));
                $pdf->WriteAtCell($famFundX, $y, $famFundWid, _($fun_name));
                $pdf->WriteAtCell($famPayX, $y, $famPayWid, OutputUtils::money_localized($fundPaymentTotal[$fun_name]));
                $pdf->WriteAtCell($famOweX, $y, $famOweWid, $amountDue);
                $y += $lineInc;
                if ($y > 250) {
                    $pdf->AddPage();
                    $y = $pageTop;
                }
            }
            $fundPledgeTotal[$fun_name] = 0; // Clear the array for the next person
            $fundPaymentTotal[$fun_name] = 0;
        }
    }
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('FamilyPledgeSummary'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
