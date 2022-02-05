<?php
/*******************************************************************************
 *
 *  filename    : FundRaiserEditor.php
 *  last change : 2009-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\RedirectUtils;

use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\FundRaiser;

use Propel\Runtime\Propel;

$linkBack = InputUtils::LegacyFilterInputArr($_GET, 'linkBack');
$iFundRaiserID = InputUtils::LegacyFilterInputArr($_GET, 'FundRaiserID');

if ($iFundRaiserID > 0) {
    // Get the current fund raiser record
    $ormFRR = FundRaiserQuery::create()
        ->findOneById($iFundRaiserID);
    // Set current fundraiser
    $_SESSION['iCurrentFundraiser'] = $iFundRaiserID;
}

if ($iFundRaiserID > 0) {
    $sPageTitle = _('Fundraiser') . ' #' . $iFundRaiserID . ' ' . $ormFRR->getTitle();
} else {
    $sPageTitle = _('Create New Fund Raiser');
}

$sDateError = '';

//Is this the second pass?
if (isset($_POST['FundRaiserSubmit'])) {
    //Get all the variables from the request object and assign them locally
    $dDate = InputUtils::FilterDate($_POST['Date']);
    $sTitle = InputUtils::FilterString($_POST['Title']);
    $sDescription = InputUtils::FilterString($_POST['Description']);

    //Initialize the error flag
    $bErrorFlag = false;

    // Validate Date
    if (strlen($dDate) > 0) {
        list($iYear, $iMonth, $iDay) = sscanf($dDate, '%04d-%02d-%02d');
        if (!checkdate($iMonth, $iDay, $iYear)) {
            $sDateError = '<span style="color: red; ">' . _('Not a valid date') . '</span>';
            $bErrorFlag = true;
        }
    }

    //If no errors, then let's update...
    if (!$bErrorFlag) {
        // New deposit slip
        if ($iFundRaiserID <= 0) {
            $fundRaiser = new FundRaiser();

            $fundRaiser->setDate($dDate);
            $fundRaiser->setTitle($sTitle);
            $fundRaiser->setDescription($sDescription);
            $fundRaiser->setEnteredBy(SessionUser::getUser()->getPersonId());
            $fundRaiser->setEnteredDate(date('YmdHis'));

            $fundRaiser->save();

            $bGetKeyBack = true;
            // Existing record (update)
        } else {
            $fundRaiser = FundRaiserQuery::create()
                ->findOneById($iFundRaiserID);

            $fundRaiser->setDate($dDate);
            $fundRaiser->setTitle($sTitle);
            $fundRaiser->setDescription($sDescription);
            $fundRaiser->setEnteredBy(SessionUser::getUser()->getPersonId());
            $fundRaiser->setEnteredDate(date('YmdHis'));

            $fundRaiser->save();

            $bGetKeyBack = false;
        }
        // If this is a new fundraiser, get the key back
        if ($bGetKeyBack) {
            $_SESSION['iCurrentFundraiser'] = $iFundRaiserID = $fundRaiser->getId();
        }

        if (isset($_POST['FundRaiserSubmit'])) {
            if ($linkBack != '') {
                RedirectUtils::Redirect($linkBack);
            } else {
                //Send to the view of this FundRaiser
                RedirectUtils::Redirect('FundRaiserEditor.php?FundRaiserID=' . $iFundRaiserID);
            }
        }
    }
} else {

    //FirstPass
    //Are we editing or adding?
    if ($iFundRaiserID > 0) {
        //Editing....
        //Get all the data on this record
        $ormFundRaiser = FundRaiserQuery::create()
            ->findOneById($iFundRaiserID);

        $dDate = $ormFundRaiser->getDate()->format('Y-m-d');
        $sTitle = $ormFundRaiser->getTitle();
        $sDescription = $ormFundRaiser->getDescription();
    } else {
        $dDate = '';
        $sTitle = '';
        $sDescription = '';
    }
}

if ($iFundRaiserID > 0) {
    $sSQL = "SELECT di_ID, di_Item, di_multibuy,
	                a.per_FirstName as donorFirstName, a.per_LastName as donorLastName,
	                b.per_FirstName as buyerFirstName, b.per_LastName as buyerLastName,
	                di_title, di_sellprice, di_estprice, di_materialvalue, di_minimum
	         FROM donateditem_di
	         LEFT JOIN person_per a ON di_donor_ID=a.per_ID
	         LEFT JOIN person_per b ON di_buyer_ID=b.per_ID
	         WHERE di_FR_ID = '" . $iFundRaiserID . "' ORDER BY di_multibuy,SUBSTR(di_item,1,1),cast(SUBSTR(di_item,2) as unsigned integer),SUBSTR(di_item,4)";

    $connection = Propel::getConnection();

    $pdoDonatedItems = $connection->prepare($sSQL);
    $pdoDonatedItems->execute();

    $DonatedItemsCNT = $pdoDonatedItems->rowCount();
} else {
    $DonatedItemsCNT = 0;
    $dDate = date('Y-m-d');    // Set default date to today
}

// Set Current Deposit setting for user
if ($iFundRaiserID > 0) {
    $_SESSION['iCurrentFundraiser'] = $iFundRaiserID;        // Probably redundant
}

require 'Include/Header.php';

?>
<div class="card">
    <div class="card-header border-0">
        <h3 class="card-title"><?= _('infos') ?></h3>
    </div>
    <div class="card-body">
        <form method="post"
              action="FundRaiserEditor.php?<?= 'linkBack=' . $linkBack . '&FundRaiserID=' . $iFundRaiserID ?>"
              name="FundRaiserEditor">

            <table cellpadding="3" align="center">

                <tr>
                    <td style="width:500px">
                        <br/>
                        <div class="row">
                            <div class="col-md-3">
                                <?= _('Date') ?>:
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="Date"
                                       value="<?= OutputUtils::change_date_for_place_holder($dDate) ?>"
                                       maxlength="10" id="Date" size="11"
                                       class="date-picker form-control input-sm"><font
                                    color="red"><?php echo $sDateError ?></font>
                            </div>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-md-3">
                                <?= _('Title') ?>:
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="Title" id="Title" value="<?= $sTitle ?>"
                                       class="form-control input-sm">
                            </div>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-md-3">
                                <?= _('Description') ?>:
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="Description" id="Description"
                                       value="<?= $sDescription ?>" class="form-control input-sm">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <br/>
                        <input type="submit" class="btn btn-primary btn-sm" value="<?= _('Save') ?>"
                               name="FundRaiserSubmit">
                        <input type="button" class="btn btn-default btn-sm" value="<?= _('Cancel') ?>"
                               name="FundRaiserCancel"
                               onclick="javascript:document.location='<?php if (strlen($linkBack) > 0) {
                                   echo $linkBack;
                               } else {
                                   echo 'v2/dashboard';
                               } ?>';">
                        <?php
                        if ($iFundRaiserID > 0) {
                            ?>
                            <input type=button class="btn btn-success btn-sm" value="<?= _('Add Donated Item') ?>" name=AddDonatedItem onclick="javascript:document.location='v2/fundraiser/donatedItemEditor/0/<?= $iFundRaiserID ?>';">
                            <input type=button class="btn btn-danger btn-sm" value="<?= _('Buyers') ?>" name=AddDonatedItem onclick="javascript:document.location='v2/fundraiser/paddlenum/list/<?= $iFundRaiserID ?>';" data-toggle="tooltip" data-placement="bottom" title="<?= _("Add buyers to your Fundraiser") ?>">
                            <br/><br/>
                            <input type=button class="btn btn-success btn-sm" value="<?= _('Generate Catalog') ?>" name=GenerateCatalog onclick="javascript:document.location='Reports/FRCatalog.php?CurrentFundraiser=<?= $iFundRaiserID ?>';">
                            <input type=button class="btn btn-info btn-sm" value="<?= _('Generate Bid Sheets') ?>" name=GenerateBidSheets onclick="javascript:document.location='Reports/FRBidSheets.php?CurrentFundraiser=<?= $iFundRaiserID ?>';">
                            <input type=button class="btn btn-warning btn-sm" value="<?=  _('Generate Certificates') ?>" name=GenerateCertificates onclick="javascript:document.location='Reports/FRCertificates.php?CurrentFundraiser=<?= $iFundRaiserID ?>';">
                            <input type=button class="btn btn-success btn-sm" value="<?= _('Batch Winner Entry') ?>" name=BatchWinnerEntry onclick="javascript:document.location='BatchWinnerEntry.php?CurrentFundraiser=<?= $iFundRaiserID ?>&linkBack=FundRaiserEditor.php?FundRaiserID=<?= $iFundRaiserID ?>&CurrentFundraiser=<?= $iFundRaiserID ?>';">
                        <?php
                        }
                        ?>
                        <br>
                    </td>
                </tr>
            </table>
        </form>

    </div>


    <br>
</div>

<?php if ($iFundRaiserID != -1) { ?>
    <div class="card">
        <div class="card-header border-0">
            <h3 class="card-title"><?= _('Donated items for this fundraiser') ?></h3>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered dataTable no-footer dtr-inline" id="fundraiser-table"
                   cellpadding="5"
                   cellspacing="0" width="100%"></table>
        </div>
    </div>

    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        $(document).ready(function () {
            window.CRM.fundraiserID = <?= $iFundRaiserID ?>;
        });
    </script>

    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/fundraiser/fundraiserEditor.js"></script>
<?php } ?>

<?php require 'Include/Footer.php' ?>
