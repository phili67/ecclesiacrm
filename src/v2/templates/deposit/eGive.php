<?php
/*******************************************************************************
 *
 *  filename    : eGive.php
 *  last change : 2009-08-27
 *  description : Tool for importing eGive data
 *
 ******************************************************************************/

// Include the function library
require $sRootDocument . '/Include/eGiveConfig.php'; // Specific account information is in here

use EcclesiaCRM\Egive;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\eGiveClass;
use EcclesiaCRM\Utils\OutputUtils;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';

if (isset($_POST['ApiGet'])) {
    $startDate = InputUtils::FilterDate($_POST['StartDate']);
    $endDate = InputUtils::FilterDate($_POST['EndDate']);

    $url = $eGiveURL.'/api/login/?apiKey='.$eGiveApiKey;
    $fp = fopen($url, 'r');

    $json = stream_get_contents($fp);
    fclose($fp);

    $api_error = 1;
    $logon = eGiveClass::get_api_data($json, $iDepositSlipID);
    
    if ($logon && $logon['status'] === 'success') {
        $api_error = 0;
        $token = $logon['token'];

        $url = $eGiveURL.'/api/transactions/'.$eGiveOrgID.'/'.$startDate;
        if ($endDate) {
            $url .= '/'.$endDate;
        }
        $url .= '/?token='.$token;

        $fp = fopen($url, 'r');

        $json = stream_get_contents($fp);
        fclose($fp);
        $data = eGiveClass::get_api_data($json, $iDepositSlipID);
        if ($data && $data['status'] === 'success') {
            $api_error = 0;

            // each transaction has these fields: 'transactionID' 'envelopeID' 'giftID' 'frequency' 'amount'
            // 'giverID' 'giverName' 'giverEmail' 'dateCompleted' 'breakouts'
            $importCreated = 0;
            $importNoChange = 0;
            $importError = 0;

            foreach ($data['transactions'] as $trans) {
                $transId = $trans['transactionID'];
                $name = $trans['giverName'];
                $totalAmount = $trans['amount'];
                $breakouts = $trans['breakouts'];
                $dateCompleted = $trans['dateCompleted'];
                $egiveID = $trans['giverID'];
                $frequency = $trans['frequency'];
                $dateTime = explode(' ', $dateCompleted);
                $date = eGiveClass::yearFirstDate($dateTime[0]);
                $famID = 0;

                if ($egiveID2FamID && array_key_exists($egiveID, $egiveID2FamID)) {
                    $famID = $egiveID2FamID[$egiveID];
                } else {
                    $patterns[0] = '/\s+/'; // any whitespace
                    $patterns[1] = '/\./'; // or dots
                    $nameWithUnderscores = preg_replace($patterns, '_', $name);
                    $egiveID2NameWithUnderscores[$egiveID] = $nameWithUnderscores;
                }

                unset($amount);
                unset($eGiveFund);

                foreach ($breakouts as $breakout) {
                    $am = $breakout[0];
                    if ($am) {
                        $eGiveFundName = $breakout[1];
                        $fundId = eGiveClass::getFundId($eGiveFundName, $fundID2Name, $fundID2Desc, $defaultFundId);

                        if ($eGiveFund[$fundId]) {
                            $eGiveFund[$fundId] .= ','.$eGiveFundName;
                        } else {
                            $eGiveFund[$fundId] = $eGiveFundName;
                        }

                        if ($amount[$fundId]) {
                            $amount[$fundId] += $am;
                        } else {
                            $amount[$fundId] = $am;
                        }

                        $totalAmount -= $am;
                    }
                }

                if ($totalAmount) {
                    $eGiveFundName = 'unspecified';

                    $fundId = eGiveClass::getFundId($eGiveFundName, $fundID2Name, $fundID2Desc, $defaultFundId);
                    if ($eGiveFund[$fundId]) {
                        $eGiveFund[$fundId] .= ','.$eGiveFundName;
                    } else {
                        $eGiveFund[$fundId] = $eGiveFundName;
                    }

                    if ($amount[$fundId]) {
                        $amount[$fundId] += $totalAmount;
                    } else {
                        $amount[$fundId] = $totalAmount;
                    }
                }

                if ($amount) { // eGive records can be 'zero' for two reasons:  a) intentional zero to suspend giving, or b) rejected bank transfer
                    ksort($amount, SORT_NUMERIC);
                    $fundIds = implode(',', array_keys($amount));
                    $groupKey = MiscUtils::genGroupKey($transId, $famID, $fundIds, $date);

                    foreach ($amount as $fundId => $am) {
                        $comment = $eGiveFund[$fundId];
                        if ($famID) {
                            eGiveClass::updateDB($famID, $transId, $date, $name, $am, $fundId, $comment, $frequency, $groupKey,
                                $eGiveExisting, $iFYID, $iDepositSlipID,$importCreated, $importNoChange);
                        } else {
                            $missingValue = $transId.'|'.$date.'|'.$egiveID.'|'.$name.'|'.$am.'|'.$fundId.'|'.$comment.'|'.$frequency.'|'.$groupKey;
                            $giftDataMissingEgiveID[] = $missingValue;
                            ++$importError;
                        }
                    }
                }
            }
        }
    }
    $url = $eGiveURL.'/api/logout/?apiKey='.$eGiveApiKey;
    $fp = fopen($url, 'r');

    $json = stream_get_contents($fp);
    fclose($fp);

    // don't know if it makes sense to check the logout success here...  we've already gotten data, cratering the transaction because the logout didn't work seems dumb.  In fact, I don't even check the logout success....  because of that very reason.
    $logout = json_decode($json, true);

    $_SESSION['giftDataMissingEgiveID'] = $giftDataMissingEgiveID;
    $_SESSION['egiveID2NameWithUnderscores'] = $egiveID2NameWithUnderscores;
    if (!$api_error) {
        eGiveClass::importDoneFixOrContinue($importCreated, $importNoChange, $importError, $iDepositSlipID, 
            $missingEgiveIDCount, $egiveID2NameWithUnderscores, $familySelectHtml);
    }
} elseif (isset($_POST['ReImport'])) {
    $giftDataMissingEgiveID = $_SESSION['giftDataMissingEgiveID'];
    $egiveID2NameWithUnderscores = $_SESSION['egiveID2NameWithUnderscores'];

    $importCreated = 0;
    $importNoChange = 0;
    $importError = 0;
    foreach ($egiveID2NameWithUnderscores as $egiveID => $nameWithUnderscores) {
        $famID = $_POST['MissingEgive_FamID_'.$nameWithUnderscores];
        $doUpdate = $_POST['MissingEgive_Set_'.$nameWithUnderscores];
        if ($famID) {
            if ($doUpdate) {
                $egive = new Egive();
                $egive->setEgiveId($egiveID);
                $egive->setFamId($famID);
                $egive->setDateEntered(date('YmdHis'));
                $egive->setEnteredBy(SessionUser::getUser()->getPersonId());
                $egive->save();
            }

            foreach ($giftDataMissingEgiveID as $data) {
                $fields = explode('|', $data);
                if ($fields[2] == $egiveID) {
                    $transId = $fields[0];
                    $date = $fields[1];
                    $name = $fields[3];
                    $amount = $fields[4];
                    $fundId = $fields[5];
                    $comment = $fields[6];
                    $frequency = $fields[7];
                    $groupKey = $fields[8];

                    eGiveClass::updateDB($famID, $transId, $date, $name, $amount, $fundId, $comment, $frequency, $groupKey,
                            $eGiveExisting, $iFYID, $iDepositSlipID,$importCreated, $importNoChange);
                }
            }
        } else {
            ++$importError;
        }
    }
    $_SESSION['giftDataMissingEgiveID'] = $giftDataMissingEgiveID;
    $_SESSION['egiveID2NameWithUnderscores'] = $egiveID2NameWithUnderscores;

    eGiveClass::importDoneFixOrContinue($importCreated, $importNoChange, $importError, $iDepositSlipID, $missingEgiveIDCount, $egiveID2NameWithUnderscores, $familySelectHtml);
} else {
    ?>
	<table cellpadding="3" align="left">
	<tr><td>
		<form method="post" action="<?= $sRootPath ?>/v2/deposit/egive/<?= $iDepositSlipID ?>" enctype="multipart/form-data">
		<class="LabelColumn"><b><?= _('Start Date: ') ?></b>
			<class="TextColumn">
                <input type="text" name="StartDate" value="<?= OutputUtils::change_date_for_place_holder($lwDate) ?>" maxlength="10" id="StartDate" size="11" 
                    class="form-control form-control-sm date-picker"
                    placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"><span color="red"><?=  $sDateError ?></span><br>
			<class="LabelColumn"><b><?= _('End Date: ') ?></b>
			<class="TextColumn">
                <input type="text" name="EndDate" value="<?= OutputUtils::change_date_for_place_holder($dDate) ?>" maxlength="10" id="EndDate" size="11" 
                    class="form-control form-control-sm date-picker"
                    placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>"><span color="red"><?=  $sDateError ?></span><br><br>
		                <input type="submit" class="btn btn-default" value="<?= _('Import eGive') ?>" name="ApiGet">
		<br><br><br>
		</form>
		</td>
	</tr>
    </table>
<?php
}
?>

<?php
require $sRootDocument . '/Include/Footer.php';
?>
