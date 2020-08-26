<?php
/*******************************************************************************
 *
 *  filename    : DonatedItemEditor.php
 *  last change : 2009-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\DonatedItem;
use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PaddleNumQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\DonatedItemTableMap;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;

use Propel\Runtime\Propel;


$iDonatedItemID = InputUtils::LegacyFilterInputArr($_GET, 'DonatedItemID', 'int');
$linkBack = InputUtils::LegacyFilterInputArr($_GET, 'linkBack');
$iCurrentFundraiser = InputUtils::LegacyFilterInputArr($_GET, 'CurrentFundraiser');

if ($iDonatedItemID > 0) {
    $ormDonatedItem = DonatedItemQuery::create()
        ->findOneById($iDonatedItemID);

    $iCurrentFundraiser = $ormDonatedItem->getFrId();
}

if ($iCurrentFundraiser) {
    $_SESSION['iCurrentFundraiser'] = $iCurrentFundraiser;
} else {
    $iCurrentFundraiser = $_SESSION['iCurrentFundraiser'];
}

// Get the current fundraiser data
if ($iCurrentFundraiser) {
    $ormDeposit = FundRaiserQuery::create()
        ->findOneById($iCurrentFundraiser);

    /*$sSQL = 'SELECT * from fundraiser_fr WHERE fr_ID = ' . $iCurrentFundraiser;
    $rsDeposit = RunQuery($sSQL);
    extract(mysqli_fetch_array($rsDeposit));*/
}

//Set the page title
$sPageTitle = _('Donated Item Editor');

//Is this the second pass?
if (isset($_POST['DonatedItemSubmit']) || isset($_POST['DonatedItemSubmitAndAdd'])) {
    //Get all the variables from the request object and assign them locally
    $sItem = InputUtils::LegacyFilterInputArr($_POST, 'Item');
    $bMultibuy = InputUtils::FilterInt($_POST['Multibuy']);
    $iDonor = InputUtils::FilterInt($_POST['Donor']);
    $iBuyer = InputUtils::FilterInt($_POST['Buyer']);
    $sTitle = InputUtils::FilterString($_POST['Title']);
    $sDescription = InputUtils::FilterHTML($_POST['Description']);
    $nSellPrice = InputUtils::FilterFloat($_POST['SellPrice']);
    $nEstPrice = InputUtils::FilterFloat($_POST['EstPrice']);
    $nMaterialValue = InputUtils::FilterFloat($_POST['MaterialValue']);
    $nMinimumPrice = InputUtils::FilterFloat($_POST['MinimumPrice']);
    $sPictureURL = InputUtils::LegacyFilterInputArr($_POST, 'PictureURL');

    if (!$bMultibuy) {
        $bMultibuy = 0;
    }
    if (!$iBuyer) {
        $iBuyer = 0;
    }
    // New DonatedItem or deposit
    if (strlen($iDonatedItemID) < 1) {

        $donatedItem = new DonatedItem();

        $donatedItem->setFrId($iCurrentFundraiser);
        $donatedItem->setItem($sItem);
        $donatedItem->setMultibuy($bMultibuy);
        $donatedItem->setDonorId($iDonor);
        $donatedItem->setBuyerId($iBuyer);
        $donatedItem->setTitle($sTitle);
        $donatedItem->setDescription(html_entity_decode($sDescription));
        $donatedItem->setSellprice($nSellPrice);
        $donatedItem->setEstprice($nEstPrice);
        $donatedItem->setMaterialValue($nMaterialValue);
        $donatedItem->setMinimum($nMinimumPrice);
        $donatedItem->setPicture(Propel::getConnection()->quote($sPictureURL));
        $donatedItem->setEnteredby(SessionUser::getUser()->getPersonId());
        $donatedItem->setEntereddate(date('YmdHis'));

        $donatedItem->save();
        $bGetKeyBack = true;
        // Existing record (update)
    } else {
        $donatedItem = DonatedItemQuery::create()
            ->findOneById($iDonatedItemID);

        $donatedItem->setFrId($iCurrentFundraiser);
        $donatedItem->setItem($sItem);
        $donatedItem->setMultibuy($bMultibuy);
        if ($iDonor != 0)
            $donatedItem->setDonorId($iDonor);
        if ($iBuyer != 0)
            $donatedItem->setBuyerId($iBuyer);
        $donatedItem->setTitle(html_entity_decode($sTitle));
        $donatedItem->setDescription(html_entity_decode($sDescription));
        $donatedItem->setSellprice($nSellPrice);
        $donatedItem->setEstprice($nEstPrice);
        $donatedItem->setMaterialValue($nMaterialValue);
        $donatedItem->setMinimum($nMinimumPrice);
        $donatedItem->setPicture($sPictureURL);
        $donatedItem->setEnteredby(SessionUser::getUser()->getPersonId());
        $donatedItem->setEntereddate(date('YmdHis'));

        $donatedItem->save();

        $bGetKeyBack = false;
    }

    // If this is a new DonatedItem or deposit, get the key back
    if ($bGetKeyBack) {
        $ormDonatedItemID = DonatedItemQuery::create()
            ->addAsColumn('DonatedItemID', 'MAX('.DonatedItemTableMap::COL_DI_ID.')')
            ->findOne();

        $iDonatedItemID = $ormDonatedItemID->getDonatedItemID();
    }

    if (isset($_POST['DonatedItemSubmit'])) {
        // Check for redirection to another page after saving information: (ie. DonatedItemEditor.php?previousPage=prev.php?a=1;b=2;c=3)
        if ($linkBack != '') {
            RedirectUtils::Redirect($linkBack);
        } else {
            //Send to the view of this DonatedItem
            RedirectUtils::Redirect('DonatedItemEditor.php?DonatedItemID='.$iDonatedItemID.'&linkBack=', $linkBack);
        }
    } elseif (isset($_POST['DonatedItemSubmitAndAdd'])) {
        //Reload to editor to add another record
        RedirectUtils::Redirect("DonatedItemEditor.php?CurrentFundraiser=$iCurrentFundraiser&linkBack=", $linkBack);
    }
} else {

  //FirstPass
    //Are we editing or adding?
    if (strlen($iDonatedItemID) > 0) {
        //Editing....
        //Get all the data on this record

        $sSQL = "SELECT di_ID, di_Item, di_multibuy, di_donor_ID, di_buyer_ID,
		                   a.per_FirstName as donorFirstName, a.per_LastName as donorLastName,
	                       b.per_FirstName as buyerFirstName, b.per_LastName as buyerLastName,
	                       di_title, di_description, di_sellprice, di_estprice, di_materialvalue,
	                       di_minimum, di_picture
	         FROM donateditem_di
	         LEFT JOIN person_per a ON di_donor_ID=a.per_ID
	         LEFT JOIN person_per b ON di_buyer_ID=b.per_ID
	         WHERE di_ID = '".$iDonatedItemID."'";

        $connection = Propel::getConnection();

        $pdoDonatedItem = $connection->prepare($sSQL);
        $pdoDonatedItem->execute();

        $res = $pdoDonatedItem->fetch(PDO::FETCH_ASSOC);

        $sItem =  $res['di_Item'];
        $bMultibuy = $res['di_multibuy'];
        $iDonor = $res['di_donor_ID'];
        $iBuyer = $res['di_buyer_ID'];
        //$sFirstName = $res['donorFirstName'];
        //$sLastName = $res['donorLastName'];
        //$sBuyerFirstName = $res['buyerFirstName'];
        //$sBuyerLastName = $res['buyerLastName'];
        $sTitle = $res['di_title'];
        $sDescription = $res['di_description'];
        $nSellPrice = $res['di_sellprice'];
        $nEstPrice = $res['di_estprice'];
        $nMaterialValue = $res['di_materialvalue'];
        $nMinimumPrice = $res['di_minimum'];
        $sPictureURL = $res['di_picture'];
    } else {
        //Adding....
        //Set defaults
        $sItem = '';
        $bMultibuy = 0;
        $iDonor = 0;
        $iBuyer = 0;
        $sTitle = '';
        $sDescription = '';
        $nSellPrice = 0.0;
        $nEstPrice = 0.0;
        $nMaterialValue = 0.0;
        $nMinimumPrice = 0.0;
        $sPictureURL = '';
    }
}

//Get People for the drop-down
//Get People for the drop-down
$ormPeople = PersonQuery::create()
    ->orderByLastName()
    ->orderByFirstName()
    ->find();

//Get Paddles for the drop-down
$ormPaddleNum = PaddleNumQuery::create()
    ->usePersonQuery()
    ->addAsColumn('BuyerFirstName', PersonTableMap::COL_PER_FIRSTNAME)
    ->addAsColumn('BuyerLastName', PersonTableMap::COL_PER_LASTNAME)
    ->endUse()
    ->findByFrId($iCurrentFundraiser);

require 'Include/Header.php';
?>

<form method="post" action="DonatedItemEditor.php?<?= 'CurrentFundraiser='.$iCurrentFundraiser.'&DonatedItemID='.$iDonatedItemID.'&linkBack='.$linkBack; ?>" name="DonatedItemEditor">
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-4 col-md-offset-2 col-xs-6">
                        <div class="form-group">
                            <label><?= _('Item') ?>:</label>
                            <input type="text" name="Item" id="Item" value="<?= $sItem ?>" class="form-control">
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="Multibuy" value="1" <?= $bMultibuy ? 'checked' : ''; ?>>
                                <?= _('Sell to everyone'); ?> (<?= _('Multiple items'); ?>)
                            </label>
                        </div>

                        <div class="form-group">
                            <label><?= _('Donor'); ?>:</label>
                            <select name="Donor" id="Donor" class="form-control select2">
                                <option value="0" selected><?= _('Unassigned') ?></option>
                                <?php
                                foreach ($ormPeople as $per) {
                                    echo '<option value="' . $per->getId() . '"';
                                    if ($iDonor == $per->getId()) {
                                        echo ' selected';
                                    }
                                    echo '>' . $per->getLastName() . ', ' . $per->getFirstName();
                                    if (!is_null($per->getFamily())) {
                                        echo ' ' . MiscUtils::FormatAddressLine($per->getFamily()->getAddress1(), $per->getFamily()->getCity(), $per->getFamily()->getState());
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><?= _('Title') ?>:</label>
                            <input type="text" name="Title" id="Title" value="<?= htmlentities($sTitle) ?>" class="form-control"/>
                        </div>

                        <div class="form-group">
                            <label><?= _('Estimated Price') ?>:</label>
                            <input type="text" name="EstPrice" id="EstPrice" value="<?= OutputUtils::number_localized($nEstPrice) ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label><?= _('Material Value') ?>:</label>
                            <input type="text" name="MaterialValue" id="MaterialValue" value="<?= OutputUtils::number_localized($nMaterialValue) ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label><?= _('Minimum Price') ?>:</label>
                            <input type="text" name="MinimumPrice" id="MinimumPrice" value="<?= OutputUtils::number_localized($nMinimumPrice) ?>" class="form-control">
                        </div>

                    </div>

                    <div class="col-md-4 col-xs-6">
                        <div class="form-group">
                            <label><?= _('Buyer') ?>:</label>
                        <?php if ($bMultibuy) {
                            echo _('Multiple');
                        } else {
                            ?>
                        <select name="Buyer" id="Buyer" class="form-control">
                          <option value="0" selected><?= _('Unassigned') ?></option>
                            <?php
                            foreach ($ormPaddleNum as $buyer) {
                                echo '<option value="'.$buyer->getPerId().'"';
                                if ($iBuyer == $buyer->getPerId()) {
                                    echo ' selected';
                                }
                                echo '>'.$buyer->getNum().': '.$buyer->getBuyerFirstName().' '.$buyer->getBuyerLastName();
                              }
                            }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><?= _('Final Price') ?>:</label>
                            <input type="text" name="SellPrice" id="SellPrice" value="<?= OutputUtils::number_localized($nSellPrice) ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label><?= _('Replicate item') ?></label>
                            <div class="input-group">
                                <input type="text" name="NumberCopies" id="NumberCopies" value="0" class="form-control">
                                <span class="input-group-btn">
                                    <input type="button" class="btn btn-primary" value="<?= _('Go') ?>" name="DonatedItemReplicate"
                                    onclick="javascript:document.location = 'DonatedItemReplicate.php?DonatedItemID=<?= $iDonatedItemID ?>&Count=' + NumberCopies.value">
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-6 col-md-offset-2 col-xs-12">
                        <div class="form-group">
                            <label><?= _('Description') ?>:</label>
                            <textarea name="Description" rows="5" cols="90" class="form-control"><?= htmlentities($sDescription) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label><?= _('Picture URL') ?>:</label>
                            <textarea name="PictureURL" rows="1" cols="90" class="form-control"><?= htmlentities($sPictureURL) ?></textarea>
                        </div>

                        <?php if ($sPictureURL != ''): ?>
                            <div class="form-group"><img src="<?= htmlentities($sPictureURL) ?>"/></div>
                        <?php endif; ?>

                    </div>

                </div> <!-- row -->
            </div>

            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" name="DonatedItemSubmit">
                <?php if (SessionUser::getUser()->isAddRecordsEnabled()): ?>
                    <input type="submit" class="btn btn-primary" value="<?= _('Save and Add'); ?>" name="DonatedItemSubmitAndAdd">
                <?php endif; ?>
                <input type="button" class="btn btn-default" value="<?= _('Cancel') ?>" name="DonatedItemCancel"
                onclick="javascript:document.location = '<?= strlen($linkBack) > 0 ? $linkBack : 'Menu.php'; ?>';">
            </div>

        </div>
    </div>
</form>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
    $(document).ready(function() {
        $("#Donor").select2();
        $("#Buyer").select2();
    });
</script>

<?php require 'Include/Footer.php'; ?>
