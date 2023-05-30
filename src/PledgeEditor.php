<?php
/*******************************************************************************
 *
 *  filename    : PledgeEditor.php
 *  last change : 2012-06-29
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
 *                Copyright 2004-2012Michael Wilt
 *                Copyright 2018 Philippe Logel
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\MICRReader;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\AutoPayment;
use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\Pledge;
use EcclesiaCRM\DepositQuery;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\map\PledgeTableMap;


// Security
if (!(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance'))) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if (SystemConfig::getValue('bUseScannedChecks')) { // Instantiate the MICR class dead code ?
    $micrObj = new MICRReader();
}

$iEnvelope = 0;
$sCheckNoError = '';
$iCheckNo = '';
$sDateError = '';
$sAmountError = '';
$nNonDeductible = [];
$sComment = '';
$tScanString = '';
$dep_Closed = false;
$iAutID = 0;
$iCurrentDeposit = 0;

$nAmount = []; // this will be the array for collecting values for each fund
$sAmountError = [];
$sComment = [];

$checkHash = [];

// Get the list of funds
$funds = DonationFundQuery::Create()->findByActive('true');

foreach ($funds as $fund) {
    $fundId2Name[$fund->getId()] = $fund->getName();
    $nAmount[$fund->getId()] = 0.0;
    $nNonDeductible[$fund->getId()] = 0.0;
    $sAmountError[$fund->getId()] = '';
    $sComment[$fund->getId()] = '';
    if (!isset($defaultFundID)) {
        $defaultFundID = $fund->getId();
    }
    $fundIdActive[$fund->getId()] = $fund->getActive();
}

// Handle URL via _GET first
if (array_key_exists('PledgeOrPayment', $_GET)) {
    $PledgeOrPayment = InputUtils::LegacyFilterInput($_GET['PledgeOrPayment'], 'string');
}
$sGroupKey = '';
if (array_key_exists('GroupKey', $_GET)) {
    $sGroupKey = InputUtils::LegacyFilterInput($_GET['GroupKey'], 'string');
} // this will only be set if someone pressed the 'edit' button on the Pledge or Deposit line
if (array_key_exists('CurrentDeposit', $_GET)) {
    $iCurrentDeposit = InputUtils::LegacyFilterInput($_GET['CurrentDeposit'], 'integer');
}

$linkBack = InputUtils::LegacyFilterInput($_GET['linkBack'], 'string');
$iFamily = 0;
if (array_key_exists('FamilyID', $_GET)) {
    $iFamily = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');
}

if (isset($_SESSION['iCurrentDeposit'])) {
    $iCurrentDeposit = $_SESSION['iCurrentDeposit'];
}


$fund2PlgIds = []; // this will be the array cross-referencing funds to existing plg_plgid's

if ($sGroupKey) {
    $pledges = PledgeQuery::Create()->findByGroupkey($sGroupKey);

    foreach ($pledges as $pledge) {
        $onePlgID = $pledge->getId();
        $oneFundID = $pledge->getFundid();
        $oneDepID = $pledge->getDepid();
        $iOriginalSelectedFund = $oneFundID; // remember the original fund in case we switch to splitting
        $fund2PlgIds[$oneFundID] = $onePlgID;

        // Security: User must have Finance permission or be the one who entered this record originally
        if (!(SessionUser::getUser()->isFinanceEnabled() || SessionUser::getUser()->getPersonId() == $pledge->getEditedby())) {
            RedirectUtils::Redirect('v2/dashboard');
            exit;
        }
    }
}


if ($iCurrentDeposit == 0) {
    $iCurrentDeposit = $oneDepID;
}


// Handle _POST input if the form was up and a button press came in
if (isset($_POST['PledgeSubmit']) or
    isset($_POST['PledgeSubmitAndAdd']) or
    isset($_POST['MatchFamily']) or
    isset($_POST['MatchEnvelope']) or
    isset($_POST['SetDefaultCheck']) or
    isset($_POST['SetFundTypeSelection']) or
    isset($_POST['PledgeOrPayment'])) {

    if (array_key_exists('PledgeOrPayment', $_POST)) {
        $PledgeOrPayment = InputUtils::LegacyFilterInput($_POST['PledgeOrPayment'], 'string');
    } else {
        $PledgeOrPayment = "Pledge";
    }

    $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');

    $dDate = InputUtils::FilterDate($_POST['Date']);
    if (!$dDate) {
        if (array_key_exists('idefaultDate', $_SESSION)) {
            $dDate = $_SESSION['idefaultDate'];
        } else {
            $dDate = date('Y-m-d');
        }
    }
    $_SESSION['idefaultDate'] = $dDate;

    // set from drop-down if set, saved session default, or by calcuation
    $iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
    if (!$iFYID) {
        $iFYID = $_SESSION['idefaultFY'];
    }
    if (!$iFYID) {
        $iFYID = MiscUtils::CurrentFY();
    }
    $_SESSION['idefaultFY'] = $iFYID;

    if (array_key_exists('CheckNo', $_POST)) {
        $iCheckNo = InputUtils::LegacyFilterInput($_POST['CheckNo'], 'int');
    } else {
        $iCheckNo = 0;
    }

    if (array_key_exists('Schedule', $_POST)) {
        $iSchedule = InputUtils::LegacyFilterInput($_POST['Schedule']);
    } else {
        $iSchedule = 'Once';
    }
    $_SESSION['iDefaultSchedule'] = $iSchedule;

    $iMethod = InputUtils::LegacyFilterInput($_POST['Method']);
    if (!$iMethod) {
        if ($sGroupKey) {
            $ormResult = PledgeQuery::Create()
                ->setDistinct(PledgeTableMap::COL_PLG_METHOD)
                ->findOneByGroupkey($sGroupKey);

            $iMethod = $ormResult->getMethod();
        } elseif ($iCurrentDeposit) {
            $ormMethod = PledgeQuery::Create()
                ->orderById()
                ->limit(1)
                ->findOneByDepid($iCurrentDeposit);

            if (!is_null($ormMethod)) {
                $iMethod = $ormMethod->getMethod();
            } else {
                $iMethod = 'CHECK';
            }
        } else {
            $iMethod = 'CHECK';
        }
    }
    $_SESSION['idefaultPaymentMethod'] = $iMethod;

    $iEnvelope = 0;
    if (array_key_exists('Envelope', $_POST)) {
        $iEnvelope = InputUtils::LegacyFilterInput($_POST['Envelope'], 'int');
    }
} else { // Form was not up previously, take data from existing records or make default values
    if ($sGroupKey) {
        $pledgeSearch = PledgeQuery::Create()
            ->orderByGroupkey()
            ->withColumn('COUNT(plg_GroupKey)', 'NumGroupKeys')
            ->findOneByGroupkey($sGroupKey);

        $numGroupKeys = $pledgeSearch->getNumGroupKeys();
        $iAutID = $pledgeSearch->getAutId();
        $PledgeOrPayment = $pledgeSearch->getPledgeorpayment();
        $fundId = $pledgeSearch->getFundid();
        $dDate = $pledgeSearch->getDate()->format('Y-m-d');
        $iFYID = $pledgeSearch->getFyid();
        $iCheckNo = $pledgeSearch->getCheckno();
        $iSchedule = $pledgeSearch->getSchedule();
        $iMethod = $pledgeSearch->getMethod();
        $iCurrentDeposit = $pledgeSearch->getDepid();

        $ormFam = PledgeQuery::Create()
            ->setDistinct(PledgeTableMap::COL_PLG_METHOD)
            ->findOneByGroupkey($sGroupKey);

        $iFamily = $ormFam->getFamId();
        $iCheckNo = $ormFam->getCheckno();
        $iFYID = $ormFam->getFyid();

        $pledgesAmount = PledgeQuery::Create()
            ->findByGroupkey($sGroupKey);

        foreach ($pledgesAmount as $pledgeAmount) {
            $nAmount[$pledgeAmount->getFundid()] = $pledgeAmount->getAmount();
            $nNonDeductible[$pledgeAmount->getFundid()] = $pledgeAmount->getNondeductible();
            $sComment[$pledgeAmount->getFundid()] = $pledgeAmount->getComment();
        }
    } else {
        if (array_key_exists('idefaultDate', $_SESSION)) {
            $dDate = $_SESSION['idefaultDate'];
        } else {
            $dDate = date('Y-m-d');
        }

        if (array_key_exists('idefaultFY', $_SESSION)) {
            $iFYID = $_SESSION['idefaultFY'];
        } else {
            $iFYID = MiscUtils::CurrentFY();
        }
        if (array_key_exists('iDefaultSchedule', $_SESSION)) {
            $iSchedule = $_SESSION['iDefaultSchedule'];
        } else {
            $iSchedule = 'Once';
        }
        if (array_key_exists('idefaultPaymentMethod', $_SESSION)) {
            $iMethod = $_SESSION['idefaultPaymentMethod'];
        } else {
            $iMethod = 'Check';
        }
    }
    if (!$iEnvelope && $iFamily) {
        $fam = FamilyQuery::Create()->findOneById($iFamily);

        if ($fam->getEnvelope()) {
            $iEnvelope = $fam->getEnvelope();
        }
    }
}

if ($PledgeOrPayment == 'Pledge') { // Don't assign the deposit slip if this is a pledge
    //$iCurrentDeposit = 0;
} else { // its a deposit
    if ($iCurrentDeposit > 0) {
        $_SESSION['iCurrentDeposit'] = $iCurrentDeposit;
    } else {
        $iCurrentDeposit = $_SESSION['iCurrentDeposit'];
    }
}

// Get the current deposit slip data
if ($iCurrentDeposit) {
    $deposit = DepositQuery::Create()->findOneById($iCurrentDeposit);

    $dep_Closed = $deposit->getClosed();
    $dep_Date = $deposit->getDate()->format('Y-m-d');
    $dep_Type = $deposit->getType();
}


if ($iMethod == 'CASH' || $iMethod == 'CHECK') {
    $dep_Type = 'Bank';
} elseif ($iMethod == 'CREDITCARD') {
    $dep_Type = 'CreditCard';
} elseif ($iMethod == 'BANKDRAFT') {
    $dep_Type = 'BankDraft';
}

if ($PledgeOrPayment == 'Payment') {
    $bEnableNonDeductible = SystemConfig::getValue('bEnableNonDeductible'); // this could/should be a config parm?  regardless, having a non-deductible amount for a pledge doesn't seem possible
}

if (isset($_POST['PledgeSubmit']) || isset($_POST['PledgeSubmitAndAdd'])) {
    //Initialize the error flag
    $bErrorFlag = false;
    // make sure at least one fund has a non-zero numer
    $nonZeroFundAmountEntered = 0;
    foreach ($fundId2Name as $fun_id => $fun_name) {
        //$fun_active = $fundActive[$fun_id];
        $nAmount[$fun_id] = InputUtils::LegacyFilterInput($_POST[$fun_id . '_Amount']);
        $sComment[$fun_id] = InputUtils::LegacyFilterInput($_POST[$fun_id . '_Comment']);
        if ($nAmount[$fun_id] > 0) {
            ++$nonZeroFundAmountEntered;
        }

        if ($bEnableNonDeductible) {
            $nNonDeductible[$fun_id] = InputUtils::LegacyFilterInput($_POST[$fun_id . '_NonDeductible']);
            //Validate the NonDeductible Amount
            if ($nNonDeductible[$fun_id] > $nAmount[$fun_id]) { //Validate the NonDeductible Amount
                $sNonDeductibleError[$fun_id] = _("NonDeductible amount can't be greater than total amount.");
                $bErrorFlag = true;
            }
        }
    } // end foreach

    if (!$nonZeroFundAmountEntered) {
        $sAmountError[$fun_id] = _('At least one fund must have a non-zero amount.');
        $bErrorFlag = true;
    }


    if (array_key_exists('ScanInput', $_POST)) {
        $tScanString = InputUtils::LegacyFilterInput($_POST['ScanInput']);
    } else {
        $tScanString = '';
    }
    $iAutID = 0;
    if (array_key_exists('AutoPay', $_POST)) {
        $iAutID = InputUtils::LegacyFilterInput($_POST['AutoPay']);
    }
    //$iEnvelope = InputUtils::LegacyFilterInput($_POST["Envelope"], 'int');

    if ($PledgeOrPayment == 'Payment' && !$iCheckNo && $iMethod == 'CHECK') {
        $sCheckNoError = '<span style="color: red; ">' . _('Must specify non-zero check number') . '</span>';
        $bErrorFlag = true;
    }

    // detect check inconsistencies
    if ($PledgeOrPayment == 'Payment' && $iCheckNo) {
        if ($iMethod == 'CASH') {
            $sCheckNoError = '<span style="color: red; ">' . _("Check number not valid for 'CASH' payment") . '</span>';
            $bErrorFlag = true;
        } elseif ($iMethod == 'CHECK' && !$sGroupKey) {
            $chkKey = $iFamily . '|' . $iCheckNo;
            if (array_key_exists($chkKey, $checkHash)) {
                $text = "Check number '" . $iCheckNo . "' for selected family already exists.";
                $sCheckNoError = '<span style="color: red; ">' . _($text) . '</span>';
                $bErrorFlag = true;
            }
        }
    }

    // Validate Date
    if (strlen($dDate) > 0) {
        list($iYear, $iMonth, $iDay) = sscanf($dDate, '%04d-%02d-%02d');
        if (!checkdate($iMonth, $iDay, $iYear)) {
            $sDateError = '<span style="color: red; ">' . _('Not a valid date') . '</span>';
            $bErrorFlag = true;
        }
    }

    //If no errors, then let's update...
    if (!$bErrorFlag && !$dep_Closed) {
        // Only set PledgeOrPayment when the record is first created
        // loop through all funds and create non-zero amount pledge records
        foreach ($fundId2Name as $fun_id => $fun_name) {
            if (!$iCheckNo) {
                $iCheckNo = 0;
            }
            if ($fund2PlgIds && array_key_exists($fun_id, $fund2PlgIds)) {
                if ($nAmount[$fun_id] > 0) {
                    $pledge = PledgeQuery::Create()->findOneById($fund2PlgIds[$fun_id]);

                    $pledge->setPledgeorpayment($PledgeOrPayment);
                    $pledge->setFamId($iFamily);
                    $pledge->setFyid($iFYID);
                    $pledge->setDate($dDate);
                    $pledge->setAmount($nAmount[$fun_id]);
                    $pledge->setSchedule($iSchedule);
                    $pledge->setMethod($iMethod);
                    $pledge->setComment($sComment[$fun_id]);
                    $pledge->setDatelastedited(date('YmdHis'));
                    $pledge->setEditedby(SessionUser::getUser()->getPersonId());
                    $pledge->setCheckno($iCheckNo);
                    $pledge->setScanstring($tScanString);
                    $pledge->setAutId($iAutID);
                    $pledge->setNondeductible($nNonDeductible[$fun_id]);

                    $pledge->save();
                } else { // delete that record
                    $pledge = PledgeQuery::Create()->findOneById($fund2PlgIds[$fun_id]);
                    $pledge->delete();
                }
            } elseif ($nAmount[$fun_id] > 0) {
                if ($iMethod != 'CHECK') {
                    $iCheckNo = 'NULL';
                }
                if (!$sGroupKey) {
                    if ($iMethod == 'CHECK') {
                        $sGroupKey = MiscUtils::genGroupKey($iCheckNo, $iFamily, $fun_id, $dDate);
                    } elseif ($iMethod == 'BANKDRAFT') {
                        if (!$iAutID) {
                            $iAutID = 'draft';
                        }
                        $sGroupKey = MiscUtils::genGroupKey($iAutID, $iFamily, $fun_id, $dDate);
                    } elseif ($iMethod == 'CREDITCARD') {
                        if (!$iAutID) {
                            $iAutID = 'credit';
                        }
                        $sGroupKey = MiscUtils::genGroupKey($iAutID, $iFamily, $fun_id, $dDate);
                    } else {
                        $sGroupKey = MiscUtils::genGroupKey('cash', $iFamily, $fun_id, $dDate);
                    }
                }

                if ($iCurrentDeposit == 0) {
                    $iCurrentDeposit = $_SESSION['iCurrentDeposit'];
                }


                $pledge = new Pledge();

                $pledge->setFamId($iFamily);
                $pledge->setFyid($iFYID);
                $pledge->setDate($dDate);
                $pledge->setAmount($nAmount[$fun_id]);
                $pledge->setSchedule($iSchedule);
                $pledge->setMethod($iMethod);
                $pledge->setComment($sComment[$fun_id]);
                $pledge->setDatelastedited(date('YmdHis'));
                $pledge->setEditedby(SessionUser::getUser()->getPersonId());
                $pledge->setPledgeorpayment($PledgeOrPayment);
                $pledge->setFundid($fun_id);
                $pledge->setDepid($iCurrentDeposit);
                $pledge->setCheckno($iCheckNo);
                $pledge->setScanstring($tScanString);
                $pledge->setAutId($iAutID);
                $pledge->setNondeductible($nNonDeductible[$fun_id]);
                $pledge->setGroupkey($sGroupKey);

                $pledge->save();

            }
        } // end foreach of $fundId2Name
        if (isset($_POST['PledgeSubmit'])) {
            // Check for redirection to another page after saving information: (ie. PledgeEditor.php?previousPage=prev.php?a=1;b=2;c=3)
            if ($linkBack != '') {
                RedirectUtils::Redirect($linkBack);
            } else {
                //Send to the view of this pledge
                RedirectUtils::Redirect('PledgeEditor.php?PledgeOrPayment=' . $PledgeOrPayment . '&GroupKey=' . $sGroupKey . '&linkBack=', $linkBack);
            }
        } elseif (isset($_POST['PledgeSubmitAndAdd'])) {
            //Reload to editor to add another record
            RedirectUtils::Redirect("PledgeEditor.php?CurrentDeposit=$iCurrentDeposit&PledgeOrPayment=" . $PledgeOrPayment . '&linkBack=', $linkBack);
        }
    } // end if !$bErrorFlag
} elseif (isset($_POST['MatchFamily']) || isset($_POST['MatchEnvelope']) || isset($_POST['SetDefaultCheck'])) {

    //$iCheckNo = 0;
    // Take care of match-family first- select the family based on the scanned check
    if (SystemConfig::getValue('bUseScannedChecks') && isset($_POST['MatchFamily'])) {
        $tScanString = InputUtils::LegacyFilterInput($_POST['ScanInput']);

        $routeAndAccount = $micrObj->FindRouteAndAccount($tScanString); // use routing and account number for matching

        if ($routeAndAccount) {
            $fam = FamilyQuery::Create()->findOneByScanCheck($routeAndAccount);
            $iFamily = $fam->getId();
            $iCheckNo = $micrObj->FindCheckNo($tScanString);
        } else {
            $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');
            $iCheckNo = InputUtils::LegacyFilterInput($_POST['CheckNo'], 'int');
        }
    } elseif (isset($_POST['MatchEnvelope'])) {
        // Match envelope is similar to match check- use the envelope number to choose a family

        $iEnvelope = InputUtils::LegacyFilterInput($_POST['Envelope'], 'int');
        if ($iEnvelope && strlen($iEnvelope) > 0) {
            $fam = FamilyQuery::Create()->findOneByEnvelope($iEnvelope);
            if (!is_null($fam)) {
                $iFamily = $fam->getId();
            }
        }
    } else {
        $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID']);
        $iCheckNo = InputUtils::LegacyFilterInput($_POST['CheckNo'], 'int');
    }

    // Handle special buttons at the bottom of the form.
    if (isset($_POST['SetDefaultCheck'])) {
        $tScanString = InputUtils::LegacyFilterInput($_POST['ScanInput']);
        $routeAndAccount = $micrObj->FindRouteAndAccount($tScanString); // use routing and account number for matching
        $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');
        $fam = FamilyQuery::Create()->findOneById($iFamily);
        $fam->setScanCheck($routeAndAccount);
        $fam->save();
    }
}

// Set Current Deposit setting for user
if ($iCurrentDeposit) {
    /* @var $currentUser \EcclesiaCRM\User */
    $currentUser = SessionUser::getUser();
    $currentUser->setCurrentDeposit($iCurrentDeposit);
    $currentUser->save();
}

//Set the page title
if ($PledgeOrPayment == 'Pledge') {
    $sPageTitle = _('Pledge Editor') . ': ' . _($dep_Type) . _(' Deposit Slip #') . $iCurrentDeposit . " (" . OutputUtils::change_date_for_place_holder($dep_Date) . ")";
} elseif ($iCurrentDeposit) {
    $sPageTitle = _('Payment Editor') . ': ' . _($dep_Type) . _(' Deposit Slip #') . $iCurrentDeposit . " (" . OutputUtils::change_date_for_place_holder($dep_Date) . ")";

    $checksFit = SystemConfig::getValue('iChecksPerDepositForm');

    $pledges = PledgeQuery::Create()->findByDepid($iCurrentDeposit);

    $depositCount = 0;
    foreach ($pledges as $pledge) {
        $chkKey = $pledge->getFamId() . '|' . $pledge->getCheckno();

        if ($pledge->getMethod() == 'CHECK' && (!array_key_exists($chkKey, $checkHash))) {
            $checkHash[$chkKey] = $pledge->getId();
            ++$depositCount;
        }
    }

    $roomForDeposits = $checksFit - $depositCount;
    if ($roomForDeposits <= 0) {
        $sPageTitle .= '<font color=red>';
    }
    $sPageTitle .= ' (' . $roomForDeposits . _(' more entries will fit.') . ')';
    if ($roomForDeposits <= 0) {
        $sPageTitle .= '</font>';
    }
} else { // not a plege and a current deposit hasn't been created yet
    if ($sGroupKey) {
        $sPageTitle = _('Payment Editor - Modify Existing Payment');
    } else {
        $sPageTitle = _('Payment Editor - New Deposit Slip Will Be Created');
    }
} // end if $PledgeOrPayment

if ($dep_Closed) {
    $sPageTitle .= ' &nbsp; <font color=red>' . _('Deposit closed') . '</font>';
}

//$familySelectHtml = MiscUtils::buildFamilySelect($iFamily, $sDirRoleHead, $sDirRoleSpouse);
$sFamilyName = '';
if ($iFamily) {
    $fam = FamilyQuery::Create()->findOneById($iFamily);
    $sFamilyName = $fam->getName() . ' ' . MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState());
}

require 'Include/Header.php';

?>

<form method="post"
      action="PledgeEditor.php?CurrentDeposit=<?= $iCurrentDeposit ?>&GroupKey=<?= $sGroupKey ?>&PledgeOrPayment=<?= $PledgeOrPayment ?>&linkBack=<?= $linkBack ?>"
      name="PledgeEditor">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-1">
                    <h3 class="card-title"><?= _("Payment Details") ?></h3>
                </div>
                <div class="card-body">
                    <input type="hidden" name="FamilyID" id="FamilyID" value="<?= $iFamily ?>">
                    <input type="hidden" name="PledgeOrPayment" id="PledgeOrPayment" value="<?= $PledgeOrPayment ?>">

                    <div class="col-md-12">
                        <label for="FamilyName"><?= _('Family') . " " . _("or") . " " . _("Person") ?></label>
                        <select class= "form-control form-control-sm" id="FamilyName" name="FamilyName" width="100%">
                            <option selected><?= $sFamilyName ?></option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php if (!$dDate) {
                                $dDate = $dep_Date;
                            } ?>
                            <label for="Date"><?= _('Date') ?></label>
                            <input class= "form-control form-control-sm" data-provide="datepicker"
                                   data-date-format='<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>' type="text"
                                   name="Date" value="<?= OutputUtils::change_date_for_place_holder($dDate) ?>"><font
                                color="red"><?= $sDateError ?></font>
                            <label for="FYID"><?= _('Fiscal Year') ?></label>
                            <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>

                            <?php if ($dep_Type == 'Bank' && SystemConfig::getValue('bUseDonationEnvelopes')) {
                                ?>
                                <label for="Envelope"><?= _('Envelope Number') ?></label>
                                <input class= "form-control form-control-sm" type="number" name="Envelope" size=8 id="Envelope"
                                       value="<?= $iEnvelope ?>">
                                <?php if (!$dep_Closed) {
                                    ?>
                                    <input class= "form-control form-control-sm" type="submit" class="btn btn-default" value="<?= _('Find family->') ?>"
                                           name="MatchEnvelope">
                                    <?php
                                } ?>

                                <?php
                            } ?>

                            <?php if ($PledgeOrPayment == 'Pledge') {
                                ?>

                                <label for="Schedule"><?= _('Payment Schedule') ?></label>
                                <select name="Schedule" class= "form-control form-control-sm">
                                    <option value="0"><?= _('Select Schedule') ?></option>
                                    <option value="Weekly" <?php if ($iSchedule == 'Weekly') {
                                        echo 'selected';
                                    } ?>><?= _('Weekly') ?>
                                    </option>
                                    <option value="Monthly" <?php if ($iSchedule == 'Monthly') {
                                        echo 'selected';
                                    } ?>><?= _('Monthly') ?>
                                    </option>
                                    <option value="Quarterly" <?php if ($iSchedule == 'Quarterly') {
                                        echo 'selected';
                                    } ?>><?= _('Quarterly') ?>
                                    </option>
                                    <option value="Once" <?php if ($iSchedule == 'Once') {
                                        echo 'selected';
                                    } ?>><?= _('Once') ?>
                                    </option>
                                    <option value="Other" <?php if ($iSchedule == 'Other') {
                                        echo 'selected';
                                    } ?>><?= _('Other') ?>
                                    </option>
                                </select>

                                <?php
                            } ?>
                            <label for="statut"><?= _('Statut') ?></label>
                            <select name="PledgeOrPayment" id="PledgeOrPaymentSelect" class= "form-control form-control-sm">
                                <option
                                    value="Pledge" <?= ($PledgeOrPayment == 'Pledge') ? "selected" : "" ?>><?= _('Pledge') ?></option>
                                <option
                                    value="Payment" <?= ($PledgeOrPayment == 'Payment') ? "selected" : "" ?>><?= _('Payment') ?></option>
                            </select>

                        </div>

                        <div class="col-md-6">
                            <label for="Method"><?= _('Payment by') ?></label>
                            <select class= "form-control form-control-sm" name="Method" id="Method">
                                <?php if ($dep_Type == 'Bank' || !$iCurrentDeposit) {
                                    ?>
                                    <option value="CHECK" <?php if ($iMethod == 'CHECK') {
                                        echo 'selected';
                                    } ?>><?= _('Check'); ?>
                                    </option>
                                    <option value="CASH" <?php if ($iMethod == 'CASH') {
                                        echo 'selected';
                                    } ?>><?= _('Cash'); ?>
                                    </option>
                                    <?php
                                } ?>
                                <?php if (($dep_Type == 'CreditCard' || !$iCurrentDeposit) && $dep_Type != 'BankDraft' && $dep_Type != 'Bank') {
                                    ?>
                                    <option value="CREDITCARD" <?php if ($iMethod == 'CREDITCARD') {
                                        echo 'selected';
                                    } ?>><?= _('Credit Card') ?>
                                    </option>
                                    <?php
                                } ?>
                                <?php if (($dep_Type == 'BankDraft' || !$iCurrentDeposit) && $dep_Type != 'CreditCard' && $dep_Type != 'Bank') {
                                    ?>
                                    <option value="BANKDRAFT" <?php if ($iMethod == 'BANKDRAFT') {
                                        echo 'selected';
                                    } ?>><?= _('Bank Draft') ?>
                                    </option>
                                    <?php
                                } ?>
                                <?php if (($PledgeOrPayment == 'Pledge') && $dep_Type != 'CreditCard' && $dep_Type != 'BankDraft' && $dep_Type != 'Bank') {
                                    ?>
                                    <option value="EGIVE" <?= $iMethod == 'EGIVE' ? 'selected' : '' ?>>
                                        <?= _('eGive') ?>
                                    </option>
                                    <?php
                                } ?>
                            </select>

                            <div id="checkNumberGroup">
                                <label for="CheckNo"><?= _('Check') ?><?= _(' #') ?></label>
                                <input class= "form-control form-control-sm" type="number" name="CheckNo" id="CheckNo"
                                       value="<?= $iCheckNo ?>"/><font color="red"><?= $sCheckNoError ?></font>
                            </div>

                            <label for="TotalAmount"><?= _('Total') . " " . SystemConfig::getValue('sCurrency') ?></label>
                            <input class= "form-control form-control-sm" type="number" step="any" name="TotalAmount" id="TotalAmount"
                                   disabled/>

                        </div>
                    </div>

                    <div class="row">
                    <?php
                    if ($dep_Type == 'CreditCard' || $dep_Type == 'BankDraft') {
                        ?>
                        <div class="col-md-6">

                            <tr>
                                <td class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>">
                                    <label><?= _('Choose online payment method') ?></label></td>
                                <td class="TextColumnWithBottomBorder">
                                    <select name="AutoPay" class= "form-control form-control-sm">
                                        <?php
                                        echo '<option value=0';
                                        if ($iAutID == 'CreditCard') {
                                            echo ' selected';
                                        }
                                        echo '>' . _('Select online payment record') . "</option>\n";
                                        echo '<option value=0>----------------------</option>';

                                        if ($dep_Type == 'CreditCard') {
                                            $autoPayements = AutoPaymentQuery::Create()->filterByFamilyid($iFamily)->filterByEnableCreditCard(true)->filterByInterval(1)->find();
                                        } else {
                                            $autoPayements = AutoPaymentQuery::Create()->filterByFamilyid($iFamily)->filterByEnableBankDraft(true)->filterByInterval(1)->find();
                                        }

                                        foreach ($autoPayements as $autoPayement) {
                                            echo "cocu";
                                            if ($autoPayement->getCreditCard()) {
                                                $showStr = _('Credit card') . " : " . mb_substr($autoPayement->getCreditCard(), strlen($autoPayement->getCreditCard()) - 4, 4);
                                            } else if ($autoPayement->getEnableBankDraft()) {
                                                $showStr = _('Bank account') . " : " . $autoPayement->getBankName() . ' ' . $aut_Route . ' ' . $aut_Account;
                                            }

                                            echo '<option value=' . $autoPayement->getId();
                                            if ($iAutID == $autoPayement->getId()) {
                                                echo ' selected';
                                            }
                                            echo '>' . $showStr . "</option>\n";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                        </div>
                        <?php
                    } ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php if (SystemConfig::getValue('bUseScannedChecks') && ($dep_Type == 'Bank' || $PledgeOrPayment == 'Pledge')) {
                                ?>
                                <td align="center"
                                    class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Scan check') ?>
                                    <textarea name="ScanInput" rows="2" cols="70"><?= $tScanString ?></textarea></td>
                                <?php
                            } ?>
                        </div>

                        <div class="col-md-6">
                            <?php if (SystemConfig::getValue('bUseScannedChecks') && $dep_Type == 'Bank') {
                                ?>
                                <input type="submit" class="btn btn-default" value="<?= _('find family from check account #') ?>"
                                       name="MatchFamily">
                                <input type="submit" class="btn btn-default"
                                       value="<?= _('Set default check account number for family') ?>"
                                       name="SetDefaultCheck">
                                <?php
                            } ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <br>
                            <?php if (!$dep_Closed) {
                                ?>
                                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="PledgeSubmit">
                                <?php if (SessionUser::getUser()->isAddRecordsEnabled()) {
                                    echo '<input type="submit" class="btn btn-info" value="' . _('Save and Add') . '" name="PledgeSubmitAndAdd">';
                                } ?>
                                <?php
                            } ?>
                            <?php if (!$dep_Closed) {
                                $cancelText = _('Cancel');
                            } else {
                                $cancelText = _('Return');
                            } ?>
                            <input type="button" class="btn btn-default" value="<?= _($cancelText) ?>" name="PledgeCancel"
                                   onclick="javascript:document.location='<?= $linkBack ? $linkBack : 'v2/dashboard' ?>';">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-1">
                    <h3 class="card-title"><?= _("Fund Split") ?></h3>
                </div>
                <div class="card-body">
                    <table id="FundTable" style="border-spacing: 10px;border-collapse: separate;">
                        <thead>
                        <tr>
                            <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Fund Name') ?></th>
                            <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Amount') ?></th>

                            <?php if ($bEnableNonDeductible) {
                                ?>
                                <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Non-deductible amount') ?></th>
                                <?php
                            } ?>

                            <th class="<?= $PledgeOrPayment == 'Pledge' ? 'LabelColumn' : 'PaymentLabelColumn' ?>"><?= _('Comment') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($fundId2Name as $fun_id => $fun_name) {
                            ?>
                            <tr>
                                <td class="TextColumn"><?= _($fun_name) ?></td>
                                <td class="TextColumn">
                                    <input class="form-control FundAmount" type="number" step="any"
                                           name="<?= $fun_id ?>_Amount" id="<?= $fun_id ?>_Amount"
                                           value="<?= $nAmount[$fun_id] ?>"><br>
                                    <font color="red"><?= $sAmountError[$fun_id] ?></font>
                                </td>
                                <?php
                                if ($bEnableNonDeductible) {
                                    ?>
                                    <td class="TextColumn">
                                        <input class= "form-control form-control-sm" type="number" step="any"
                                               name="<?= $fun_id ?>_NonDeductible" id="<?= $fun_id ?>_NonDeductible"
                                               value="<?= $nNonDeductible[$fun_id] ?>"/>
                                        <br>
                                        <font color="red"><?= $sNonDeductibleError[$fun_id] ?></font>
                                    </td>
                                    <?php
                                } ?>
                                <td class="TextColumn">
                                    <input class= "form-control form-control-sm" type="text" size=40 name="<?= $fun_id ?>_Comment"
                                           id="<?= $fun_id ?>_Comment" value="<?= $sComment[$fun_id] ?>">
                                </td>
                            </tr>
                            <?php
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</form>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var dep_Date = "<?= OutputUtils::change_date_for_place_holder($dep_Date) ?>";
    var dep_Type = "<?= $dep_Type ?>";
    var dep_Closed = <?= ($dep_Closed) ? '1' : '0' ?>;
    var CurrentDeposit = <?= $iCurrentDeposit ?>;
    var Closed = "<?= ($dep_Closed && $sGroupKey && $PledgeOrPayment == 'Payment') ? ' &nbsp; <font color=red>' . _('Deposit closed') . '</font>' : "" ?>";

    $(document).ready(function () {
        $("#FamilyName").select2({
            minimumInputLength: 2,
            language: window.CRM.shortLocale,
            ajax: {
                url: function (params) {
                    var a = window.CRM.root + '/api/families/search/' + params.term;
                    return a;
                },
                dataType: 'json',
                delay: 250,
                data: "",
                processResults: function (data, params) {
                    var results = [];
                    var families = JSON.parse(data).Families
                    $.each(families, function (key, object) {
                        results.push({
                            id: object.Id,
                            text: object.displayName
                        });
                    });
                    return {
                        results: results
                    };
                }
            }
        });

        $("#FamilyName").on("select2:select", function (e) {
            $('[name=FamilyID]').val(e.params.data.id);

            window.CRM.APIRequest({
                method: "POST",
                path: "payments/families",
                data: JSON.stringify({"famId": e.params.data.id, "type": "<?= $dep_Type ?>"})
            }, function (data) {
                var my_list = $("[name=AutoPay]").empty();
                var len = data.length;

                my_list.append($('<option>', {
                    value: 0,
                    text: i18next.t("Select online payment record")
                }));

                my_list.append($('<option>', {
                    value: 0,
                    text: '----------------------'
                }));

                for (i = 0; i < len; ++i) {
                    my_list.append($('<option>', {
                        value: data[i].authID,
                        text: data[i].showStr
                    }));
                }

                console.log("Add the Menu OK");
            });
        });

        var fundTableConfig = {
            paging: false,
            searching: false,
        };

        $.extend(fundTableConfig, window.CRM.plugin.dataTable);

        $("#FundTable").DataTable(fundTableConfig);


        $(".FundAmount").change(function () {
            CalculateTotal();
        });

        $("#Method").change(function () {
            EvalCheckNumberGroup();
        });

        EvalCheckNumberGroup();
        CalculateTotal();
    });

    $("#PledgeOrPaymentSelect").change(function () {
        if (dep_Closed) {
            window.CRM.DisplayAlert(i18next.t("Warning !!!"), i18next.t("Deposit closed"));
            var sel = $("#PledgeOrPaymentSelect");
            sel.data("prev", sel.val());
            return false;
        }

        EvalCheckNumberGroup();

        if ($("#Method option:selected").val() === "CASH" && $("#PledgeOrPaymentSelect option:selected").val() === 'Payment') {
            $("#Method").val("CHECK");
            $("#checkNumberGroup").show();
        }

        if ($("#PledgeOrPaymentSelect option:selected").val() === 'Payment') {
            $(".content-header").html("<h1>" + i18next.t("Payment Editor") + ": " + i18next.t(dep_Type) + i18next.t(" Deposit Slip #") + CurrentDeposit + " (" + dep_Date + ")" + Closed + "</h1>");
        } else {
            $(".content-header").html("<h1>" + i18next.t("Pledge Editor") + ": " + i18next.t(dep_Type) + i18next.t(" Deposit Slip #") + CurrentDeposit + " (" + dep_Date + ")" + Closed + "</h1>");
        }
    });

    function EvalCheckNumberGroup() {
        if ($("#Method option:selected").val() === "CHECK" && $("#PledgeOrPaymentSelect option:selected").val() === 'Payment') {
            $("#checkNumberGroup").show();
        } else {
            $("#checkNumberGroup").hide();

            if ($("#Method option:selected").val() === "CHECK") {
                $("#Method").val("CASH");
            }
            $("#CheckNo").val('');
        }
    }

    function CalculateTotal() {
        var Total = 0.0;
        $(".FundAmount").each(function (object) {
            var FundAmount = Number($(this).val());
            if (FundAmount > 0) {
                Total += FundAmount;
            }
        });
        $("#TotalAmount").val(Number(Total).toFixed(2));
    }
</script>


<?php require 'Include/Footer.php' ?>
