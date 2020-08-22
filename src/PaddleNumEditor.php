<?php
/*******************************************************************************
 *
 *  filename    : PaddleNumEditor.php
 *  last change : 2009-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\PaddleNumQuery;
use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\PaddleNum;
use EcclesiaCRM\MultibuyQuery;
use EcclesiaCRM\Multibuy;
use EcclesiaCRM\PersonQuery;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;

use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\SessionUser;


$iPaddleNumID = InputUtils::LegacyFilterInputArr($_GET, 'PaddleNumID', 'int');
//$linkBack = InputUtils::LegacyFilterInputArr($_GET, 'linkBack');

if ($iPaddleNumID > 0) {
    $ormPaddleNum = PaddleNumQuery::create()
            ->findOneById($iPaddleNumID);

    $iCurrentFundraiser = $ormPaddleNum->getFrId();
} else {
    $iCurrentFundraiser = $_SESSION['iCurrentFundraiser'];
}

if ($iCurrentFundraiser == '') {
    RedirectUtils::Redirect('FindFundRaiser.php');
}

// Get the current fundraiser data
if ($iCurrentFundraiser) {
    $ormDeposit = FundRaiserQuery::create()
            ->findOneById($iCurrentFundraiser);
}

$linkBack = "PaddleNumList.php?FundRaiserID=".$iCurrentFundraiser;

// to get multibuy donated items
$ormMultibuyItems = DonatedItemQuery::create()
    ->filterByMultibuy(1)
    ->findByFrId($iCurrentFundraiser);

//Set the page title
$sPageTitle = _('Buyer Number Editor');

//Is this the second pass?
if ( isset($_POST['PaddleNumSubmit']) || isset($_POST['PaddleNumSubmitAndAdd']) || isset($_POST['GenerateStatement'])) {
    //Get all the variables from the request object and assign them locally
    $iNum = InputUtils::LegacyFilterInput($_POST['Num']);
    $iPerID = InputUtils::LegacyFilterInput($_POST['PerID']);


    if ( $iPerID > 0 ) {// Only with a person you can add a buyer
        foreach ($ormMultibuyItems as $multibuyItem) {
            $mbName = 'MBItem' . $multibuyItem->getId();

            $iMBCount = InputUtils::LegacyFilterInput($_POST[$mbName], 'int');
            if ($iMBCount > 0) { // count for this item is positive.  If a multibuy record exists, update it.  If not, create it.
                $ormNumBought = MultibuyQuery::create()
                    ->filterByPerId($iPerID)
                    ->findOneByItemId($multibuyItem->getId());

                if (!is_null ($ormNumBought)) {
                    $ormNumBought->setPerId($iPerID);
                    $ormNumBought->setCount($iMBCount);
                    $ormNumBought->setItemId($multibuyItem->getId());
                    $ormNumBought->save();
                } else {
                    $ormNumBought = new Multibuy();
                    $ormNumBought->setPerId($iPerID);
                    $ormNumBought->setCount($iMBCount);
                    $ormNumBought->setItemId($multibuyItem->getId());
                    $ormNumBought->save();
                }
            } else { // count is zero, if it was positive before there is a multibuy record that needs to be deleted
                $ormNumBought = MultibuyQuery::create()
                    ->filterByPerId($iPerID)
                    ->findOneByItemId($multibuyItem->getId());

                if (!is_null($ormNumBought)) {
                    $ormNumBought->delete();
                }
            }
        }

        // New PaddleNum
        if (strlen($iPaddleNumID) < 1) {
            $paddNum = new PaddleNum();

            $paddNum->setFrId($iCurrentFundraiser);
            $paddNum->setNum($iNum);
            $paddNum->setPerId($iPerID);

            $paddNum->save();

            $bGetKeyBack = true;
            // Existing record (update)
        } else {
            $paddNum = PaddleNumQuery::create()
                ->findOneById($iPaddleNumID);

            $paddNum->setFrId($iCurrentFundraiser);
            $paddNum->setNum($iNum);
            $paddNum->setPerId($iPerID);

            $paddNum->save();

            $bGetKeyBack = false;
        }
    }

    // If this is a new PaddleNum or deposit, get the key back
    if ($bGetKeyBack) {
        $paddleMax = PaddleNumQuery::create()
            ->addAsColumn('Max', 'MAX('.\EcclesiaCRM\Map\PaddleNumTableMap::COL_PN_ID.')')
            ->findOne();

        $iPaddleNumID =  $paddleMax->getMax();

    }

    if (isset($_POST['PaddleNumSubmit'])) {
        RedirectUtils::Redirect('PaddleNumEditor.php?PaddleNumID=' . $iPaddleNumID . '&linkBack=' . $linkBack);
    } elseif (isset($_POST['PaddleNumSubmitAndAdd'])) {
        //Reload to editor to add another record
        RedirectUtils::Redirect("PaddleNumEditor.php?CurrentFundraiser=$iCurrentFundraiser&linkBack=", $linkBack);
    } elseif (isset($_POST['GenerateStatement'])) {
        //Jump straight to generating the statement report
        RedirectUtils::Redirect("Reports/FundRaiserStatement.php?PaddleNumID=$iPaddleNumID");
    }
} else {

    //FirstPass
    //Are we editing or adding?
    if (strlen($iPaddleNumID) > 0) {
        //Editing....
        //Get all the data on this record
        $ormPaddleNum = PaddleNumQuery::create()
            ->usePersonQuery()
                ->addAsColumn('BuyerFirstName', \EcclesiaCRM\Map\PersonTableMap::COL_PER_FIRSTNAME)
                ->addAsColumn('BuyerLastName', \EcclesiaCRM\Map\PersonTableMap::COL_PER_LASTNAME)
            ->endUse()
            ->findOneById($iPaddleNumID);

        $iNum = $ormPaddleNum->getNum();
        $iPerID = $ormPaddleNum->getPerId();
    } else {
        //Adding....
        //Set defaults
        $ormGetMaxNum = PaddleNumQuery::create()
            ->findByFrId($iCurrentFundraiser);

        $iNum = $ormGetMaxNum->count() + 1;
        $iPerID = 0;
    }
}

//Get People for the drop-down
$ormPeople = PersonQuery::create()
    ->orderByLastName()
    ->orderByFirstName()
    ->find();

require 'Include/Header.php';

?>
<div class="card card-body">
    <form method="post"
          action="PaddleNumEditor.php?<?= 'CurrentFundraiser=' . $iCurrentFundraiser . '&PaddleNumID=' . $iPaddleNumID . '&linkBack=' . $linkBack ?>"
          name="PaddleNumEditor">

        <div class="table-responsive">
            <table class="table" cellpadding="3" align="center">
                <tr>
                    <td>
                        <table border="0" width="100%" cellspacing="0" cellpadding="4">
                            <tr>
                                <td width="50%" valign="top" align="left">
                                    <table cellpadding="3">
                                        <tr>
                                            <td class="LabelColumn"><?= _('Number') ?>:</td>
                                            <td class="TextColumn"><input type="text" name="Num" id="Num"
                                                                          value="<?= $iNum ?>"
                                                                          class="form-control input-sm"></td>
                                        </tr>
                                        <tr>
                                            <td><br></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td class="LabelColumn"><?= _('Buyer') ?>:
                                            </td>
                                            <td class="TextColumn">
                                                <select name="PerID" class="form-control select2" id="Buyers">
                                                    <option value="0" selected><?= _('Unassigned') ?></option>
                                                    <?php
                                                    foreach ($ormPeople as $per) {
                                                        echo '<option value="' . $per->getId() . '"';
                                                        if ($iPerID == $per->getId()) {
                                                            echo ' selected';
                                                        }
                                                        echo '>' . $per->getLastName() . ', ' . $per->getFirstName();
                                                        if (!is_null ($per->getFamily())) {
                                                            echo ' ' . MiscUtils::FormatAddressLine($per->getFamily()->getAddress1(), $per->getFamily()->getCity(), $per->getFamily()->getState());
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td width="50%" valign="top" align="center">
                                    <br>
                                    <table cellpadding="3">
                                        <?php
                                        foreach ($ormMultibuyItems as $multibuyItem) {
                                            $ormNumBought = MultibuyQuery::create()
                                                ->filterByPerId($iPerID)
                                                ->findOneByItemId($multibuyItem->getId());

                                            $mb_count = 0;
                                            if (!is_null($ormNumBought)) {
                                                $mb_count = $ormNumBought->getCount();
                                            }

                                            ?>
                                            <tr>
                                                <td class="LabelColumn"><label><?= $multibuyItem->getTitle() ?></label></td>
                                                <td class="TextColumn"><input class="form-control input-sm" type="text" name="MBItem<?= $multibuyItem->getId() ?>"
                                                                              id="MBItem<?= $multibuyItem->getId() ?>"
                                                                              value="<?= $mb_count ?>"></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>

                                    </table>
                                </td>
                            </tr>

                        </table>
                </tr>
                <tr>
                    <td align="center">
                        <input type="submit" class="btn btn-primary btn-sm" value="<?= _('Save') ?>"
                               name="PaddleNumSubmit">
                        <input type="submit" class="btn btn-info btn-sm" value="<?= _('Generate Statement') ?>"
                               name="GenerateStatement">
                        <?php if (SessionUser::getUser()->isAddRecordsEnabled()) {
                            echo '<input type="submit" class="btn btn-success btn-sm" value="' . _('Save and Add') . "\" name=\"PaddleNumSubmitAndAdd\">\n";
                        } ?>
                        <input type="button" class="btn btn-default btn-sm" value="<?= _('Back') ?>"
                               name="PaddleNumCancel"
                               onclick="javascript:document.location='<?php if (strlen($linkBack) > 0) {
                                   echo $linkBack;
                               } else {
                                   echo 'Menu.php';
                               } ?>';">
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        $("#Buyers").select2();

        $('.fundraiser-table').DataTable({
            responsive: true,
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
        });
    });
</script>

<?php require 'Include/Footer.php' ?>
