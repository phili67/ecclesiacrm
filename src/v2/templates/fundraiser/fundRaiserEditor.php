<?php
/*******************************************************************************
 *
 *  filename    : fundRaiserEditor.php
 *  last change : 2023-06-08
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2023s Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\FundRaiser;
use EcclesiaCRM\FundRaiserQuery;
 
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\RedirectUtils;

use Propel\Runtime\Propel;

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
                RedirectUtils::Redirect('v2/fundraiser/editor/' . $iFundRaiserID);
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

require $sRootDocument . '/Include/Header.php';

?>
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-1"></i><?= _('Fundraiser Information') ?></h3>
        <span class="badge badge-light border"><?= $iFundRaiserID > 0 ? _('Edit') : _('New') ?></span>
    </div>
    <div class="card-body py-3">
        <form id="fundRaiserEditorForm" method="post"
              action="<?= $sRootPath ?>/v2/fundraiser/editor<?= ($iFundRaiserID != -1)?('/'.$iFundRaiserID):'' ?><?=  ($linkBack != '')?('/'.$linkBack):'' ?>"
              name="FundRaiserEditor" class="mb-0">
            <div class="form-group row align-items-center">
                <label class="col-md-2 col-form-label font-weight-semibold" for="Date"><?= _('Date') ?></label>
                <div class="col-md-4 col-lg-3">
                    <input type="text" name="Date"
                           value="<?= OutputUtils::change_date_for_place_holder($dDate) ?>"
                           maxlength="10" id="Date" size="11"
                           class="date-picker form-control form-control-sm">
                </div>
                <div class="col-md-6 col-lg-7">
                    <?php if (!empty($sDateError)) { ?>
                        <small class="text-danger"><?= $sDateError ?></small>
                    <?php } ?>
                </div>
            </div>

            <div class="form-group row align-items-center">
                <label class="col-md-2 col-form-label font-weight-semibold" for="Title"><?= _('Title') ?></label>
                <div class="col-md-6 col-lg-5">
                    <input type="text" name="Title" id="Title" value="<?= $sTitle ?>"
                           class="form-control form-control-sm">
                </div>
            </div>

            <div class="form-group row align-items-center mb-0">
                <label class="col-md-2 col-form-label font-weight-semibold" for="Description"><?= _('Description') ?></label>
                <div class="col-md-8 col-lg-7">
                    <input type="text" name="Description" id="Description"
                           value="<?= $sDescription ?>" class="form-control form-control-sm">
                </div>
            </div>

            <div class="pt-3 mt-3 border-top">
                <div class="d-flex flex-wrap align-items-center mb-2">
                    <button type="submit" class="btn btn-sm btn-primary mr-2 mb-1" name="FundRaiserSubmit">
                        <i class="fas fa-save mr-1"></i><?= _('Save') ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary mr-2 mb-1" name="FundRaiserCancel"
                            onclick="javascript:document.location='<?= (strlen($linkBack) > 0)?($sRootPath.'/'.$linkBack):($sRootPath.'/v2/dashboard') ?>';">
                        <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                    </button>
                </div>

                <?php if ($iFundRaiserID > 0) { ?>
                    <div class="d-flex flex-wrap align-items-center mb-2">
                        <button type="button" class="btn btn-sm btn-success mr-2 mb-1" name="AddDonatedItem"
                                onclick="javascript:document.location='<?= $sRootPath ?>/v2/fundraiser/donatedItemEditor/0/<?= $iFundRaiserID ?>';">
                            <i class="fas fa-plus mr-1"></i><?= _('Add Donated Item') ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger mr-2 mb-1" name="ManageBuyers"
                                onclick="javascript:document.location='<?= $sRootPath ?>/v2/fundraiser/paddlenum/list/<?= $iFundRaiserID ?>';"
                                data-toggle="tooltip" data-placement="bottom" title="<?= _('Add buyers to your Fundraiser') ?>">
                            <i class="fas fa-users mr-1"></i><?= _('Buyers') ?>
                        </button>
                    </div>

                    <div class="d-flex flex-wrap align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-success mr-2 mb-1" name="GenerateCatalog"
                                onclick="javascript:document.location='<?= $sRootPath ?>/Reports/FRCatalog.php?CurrentFundraiser=<?= $iFundRaiserID ?>';">
                            <i class="fas fa-book mr-1"></i><?= _('Generate Catalog') ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info mr-2 mb-1" name="GenerateBidSheets"
                                onclick="javascript:document.location='<?= $sRootPath ?>/Reports/FRBidSheets.php?CurrentFundraiser=<?= $iFundRaiserID ?>';">
                            <i class="fas fa-file-alt mr-1"></i><?= _('Generate Bid Sheets') ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning mr-2 mb-1" name="GenerateCertificates"
                                onclick="javascript:document.location='<?= $sRootPath ?>/Reports/FRCertificates.php?CurrentFundraiser=<?= $iFundRaiserID ?>';">
                            <i class="fas fa-certificate mr-1"></i><?= _('Generate Certificates') ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" name="BatchWinnerEntry"
                                onclick="javascript:document.location='<?= $sRootPath ?>/v2/fundraiser/batch/winner/entry/<?= $iFundRaiserID ?>/v2-fundraiser-editor-<?= $iFundRaiserID ?>';">
                            <i class="fas fa-layer-group mr-1"></i><?= _('Batch Winner Entry') ?>
                        </button>
                    </div>
                <?php } ?>
            </div>
        </form>
    </div>
</div>

<?php if ($iFundRaiserID != -1) { ?>
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-gift mr-1"></i><?= _('Donated items for this fundraiser') ?></h3>
            <span class="badge badge-light border" id="donatedItemsCountBadge"><?= (int) $DonatedItemsCNT ?></span>
        </div>
        <div class="card-body py-2">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0 dataTable no-footer dtr-inline" id="fundraiser-table"
                       cellpadding="5"
                       cellspacing="0" width="100%"></table>
            </div>
        </div>
    </div>

    <script nonce="<?= $sCSPNonce ?>">
        $(function() {
            window.CRM.fundraiserID = <?= $iFundRaiserID ?>;
        });
    </script>

    <script src="<?= $sRootPath ?>/skin/js/fundraiser/fundraiserEditor.js"></script>
<?php } ?>


<?php require $sRootDocument . '/Include/Footer.php'; ?>
