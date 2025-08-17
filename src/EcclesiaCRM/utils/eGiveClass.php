<?php

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Pledge;
use EcclesiaCRM\SessionUser;

class eGiveClass
{

    public static function yearFirstDate($date)
    {
        $dateArray = explode('/', $date); // this date is in mm/dd/yy format.  ecclesiacrm needs it in yyyy-mm-dd format
        if (strlen($dateArray[2]) == 2) {
            $dateArray[2] += 2000;
        }
        $dateArray[0] = sprintf('%02d', $dateArray[0]);
        $dateArray[1] = sprintf('%02d', $dateArray[1]);
        $dateCI = $dateArray[2] . '-' . $dateArray[0] . '-' . $dateArray[1];

        return $dateCI;
    }

    public static function eGiveExistingKey($transId, $famID, $date, $fundId, $comment)
    {
        $key = $transId . '|' . $famID . '|' . $date . '|' . $fundId . '|' . $comment;

        return $key;
    }
    public static function updateDB(
        $famID,
        $transId,
        $date,
        $name,
        $amount,
        $fundId,
        $comment,
        $frequency,
        $groupKey,
        &$eGiveExisting,
        &$iFYID,
        &$iDepositSlipID,
        &$importCreated,
        &$importNoChange
    ) {

        $keyExisting = self::eGiveExistingKey($transId, $famID, $date, $fundId, $comment);
        if ($eGiveExisting && array_key_exists($keyExisting, $eGiveExisting)) {
            ++$importNoChange;
        } elseif ($famID) { //  insert a new record
            $pledge = new Pledge();
            $pledge->setFamId($famID);
            $pledge->setFyid($iFYID);
            $pledge->setDate($date);
            $pledge->setAmount($amount);
            $pledge->setSchedule($frequency);
            $pledge->setMethod('EGIVE');
            $pledge->setComment($$comment);
            $pledge->setDatelastedited(date('YmdHis'));
            $pledge->setEditedby(SessionUser::getUser()->getPersonId());
            $pledge->setPledgeorpayment('Payment');
            $pledge->setFundid($fundId);
            $pledge->setDepid($iDepositSlipID);
            $pledge->setCheckno(0);
            $pledge->setNondeductible($transId);
            $pledge->setGroupkey($groupKey);

            $pledge->save();
        
            ++$importCreated;            
        }
    }

    public static function getFundId(
        $eGiveFundName,
        &$fundID2Name,
        &$fundID2Desc,
        &$defaultFundId
    ) {
        foreach ($fundID2Name as $fun_ID => $fun_Name) {
            if (preg_match("%$fun_Name%i", $eGiveFundName)) {
                return $fun_ID;
            }
        }

        foreach ($fundID2Desc as $fun_ID => $fun_Desc) {
            $descWords = explode(' ', $fun_Desc);
            foreach ($descWords as $desc) {
                if (preg_match("%$desc%i", $eGiveFundName)) {
                    return $fun_ID;
                }
            }
        }

        return $defaultFundId;
    }

    public static function importDoneFixOrContinue(
        &$importCreated,
        &$importNoChange,
        &$importError,
        &$iDepositSlipID,
        &$missingEgiveIDCount,
        &$egiveID2NameWithUnderscores,
        &$familySelectHtml
    ) {
    ?>
        <form method="post" action="eGive.php?DepositSlipID=<?= $iDepositSlipID ?>">
            <?php
            if ($importError) { // the only way we can fail to import data is if we're missing the egive IDs, so build a table, with text input, and prompt for it.
            ?>
                <p>New eGive Name(s) and ID(s) have been imported and must be associated with the appropriate Family. Use the pulldown in the <b>Family</b> column to select the Family, based on the eGive name, and then press the Re-Import button.<br><br>If you cannot make the assignment now, you can safely go Back to the Deposit Slip, and Re-import this data at a later time. Its possible you may need to view eGive data using the Web View in order to make an accurate Family assignment.</p>
                <table border=1>
                    <tr>
                        <td><b>eGive Name</b></td>
                        <td><b>eGive ID</b></td>
                        <td><b>Family</b></td>
                        <td><b>Set eGive ID into Family</b></td>
                    </tr>
                    <?php

                    foreach ($egiveID2NameWithUnderscores as $egiveID => $nameWithUnderscores) {
                        $name = preg_replace('/_/', ' ', $nameWithUnderscores);
                        echo '<tr>';
                        echo '<td>' . $name . '&nbsp;</td>'; ?>
                        <td>
                            <class="TextColumn"><input type="text" name="MissingEgive_ID_<?= $nameWithUnderscores ?>" value="<?= $egiveID ?>" maxlength="10">
                        </td>
                        <td class="TextColumn">
                            <select name="MissingEgive_FamID_<?= $nameWithUnderscores ?>">
                                <option value="0" selected><?= _('Unassigned') ?></option>
                                <?php
                                echo $familySelectHtml; ?>
                            </select>
                        </td>
                        <td><input type="checkbox" name="MissingEgive_Set_<?= $nameWithUnderscores ?>" value="1" checked></td>
                    <?php
                        echo '</tr>';
                    } ?>
                </table><br>

                <input type="submit" class="btn btn-default" value="<?= _('Re-import to selected family') ?>" name="ReImport">
            <?php
            } ?>

            <p class="MediumLargeText"> <?= _('Data import results: ') . $importCreated . _(' gifts were imported, ') . $importNoChange . _(' gifts unchanged, and ') . $importError . _(' gifts not imported due to problems') ?></p>
            <input type="button" class="btn btn-default" value="<?= _('Back to Deposit Slip') ?>" onclick="javascript:document.location='<?= SystemURLs::getRootPath() ?>/v2/deposit/slipeditor/<?= $iDepositSlipID ?>'"
                <?php
            }

            public static function get_api_data($json, &$iDepositSlipID)
            {
                if ($json === "Church not found or not active.") {
                    $error = $json;                  
                }

                if (empty($error)) {
                    $result = json_decode($json, true);

                    $rc = json_last_error();
                    switch ($rc) {
                        case JSON_ERROR_DEPTH:
                            $error = ' - Maximum stack depth exceeded';
                            break;
                        case JSON_ERROR_CTRL_CHAR:
                            $error = ' - Unexpected control character found';
                            break;
                        case JSON_ERROR_SYNTAX:
                            $error = ' - Syntax error, malformed JSON';
                            break;
                        case JSON_ERROR_NONE:
                        default:
                            $error = '';
                    }
                }

                if (empty($error)) {
                    return $result;
                } else {
                ?>
                <span style="color:red"><?= _("Fatal error in eGive API datastream: '") . $error ?>"'</span><br><br>
            <input type="button" class="btn btn-default" value="<?= _('Back to Deposit Slip') ?>" onclick="javascript:document.location='<?= SystemURLs::getRootPath() ?>/v2/deposit/slipeditor/<?= $iDepositSlipID ?>'"
                <?php
                    return 0;
                }
            }
        }
