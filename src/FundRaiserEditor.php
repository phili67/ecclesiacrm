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

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\FundRaiser;

use Propel\Runtime\Propel;


use EcclesiaCRM\SessionUser;

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
    $sTitle = InputUtils::LegacyFilterInputArr($_POST, 'Title');
    $sDescription = InputUtils::LegacyFilterInputArr($_POST, 'Description');

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

    $DonatedItemsCNT =  $pdoDonatedItems->rowCount();
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
<div class="card card-body">
    <form method="post" action="FundRaiserEditor.php?<?= 'linkBack=' . $linkBack . '&FundRaiserID=' . $iFundRaiserID ?>"
          name="FundRaiserEditor">

        <table cellpadding="3" align="center">

            <tr>
                <td  style="width:500px">
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
                               echo 'Menu.php';
                           } ?>';">
                    <?php
                    if ($iFundRaiserID > 0) {
                        echo '<input type=button class="btn btn-success btn-sm" value="' . _('Add Donated Item') . "\" name=AddDonatedItem onclick=\"javascript:document.location='DonatedItemEditor.php?CurrentFundraiser=$iFundRaiserID&linkBack=FundRaiserEditor.php?FundRaiserID=$iFundRaiserID&CurrentFundraiser=$iFundRaiserID';\">\n";
                        echo '<input type=button class="btn btn-success btn-sm" value="' . _('Generate Catalog') . "\" name=GenerateCatalog onclick=\"javascript:document.location='Reports/FRCatalog.php?CurrentFundraiser=$iFundRaiserID';\">\n";
                        echo '<input type=button class="btn btn-info btn-sm" value="' . _('Generate Bid Sheets') . "\" name=GenerateBidSheets onclick=\"javascript:document.location='Reports/FRBidSheets.php?CurrentFundraiser=$iFundRaiserID';\">\n";
                        echo '<input type=button class="btn btn-warning btn-sm" value="' . _('Generate Certificates') . "\" name=GenerateCertificates onclick=\"javascript:document.location='Reports/FRCertificates.php?CurrentFundraiser=$iFundRaiserID';\">\n";
                        echo '<input type=button class="btn btn-success btn-sm" value="' . _('Batch Winner Entry') . "\" name=BatchWinnerEntry onclick=\"javascript:document.location='BatchWinnerEntry.php?CurrentFundraiser=$iFundRaiserID&linkBack=FundRaiserEditor.php?FundRaiserID=$iFundRaiserID&CurrentFundraiser=$iFundRaiserID';\">\n";
                    }
                    ?>
                    <br>
                </td>
            </tr>
    </form>
    </table>


    <br>
</div>

<?php if ($iFundRaiserID != -1) { ?>
    <div class="card card-body">
        <b><?= _('Donated items for this fundraiser') ?>:</b>
        <br>
        <div class="table-responsive">
            <table class="table table-striped table-bordered dataTable no-footer dtr-inline fundraiser-table"
                   cellpadding="5"
                   cellspacing="0" width="100%">

                <thead class="TableHeader">
                <th><?= _('Item') ?></th>
                <th><?= _('Multiple') ?></th>
                <th><?= _('Donor') ?></th>
                <th><?= _('Buyer') ?></th>
                <th><?= _('Title') ?></th>
                <th><?= _('Sale Price') ?></th>
                <th><?= _('Estimated value') ?></th>
                <th><?= _('Material Value') ?></th>
                <th><?= _('Minimum Price') ?></th>
                <th><?= _('Delete') ?></th>
                </thead>

                <?php
                $tog = 0;


                //Loop through all donated items
                if ($DonatedItemsCNT > 0) {

                    while ($row = $pdoDonatedItems->fetch( \PDO::FETCH_BOTH )) {
                        echo $DonatedItemsCNT;

                        if ($row['di_Item'] == '') {
                            $row['di_Item'] = '~';
                        }

                        $sRowClass = 'RowColorA'; ?>
                        <tr class="<?= $sRowClass ?>">
                            <td>
                                <a href="<?= SystemURLs::getRootPath() ?>/DonatedItemEditor.php?DonatedItemID=<?= $row['di_ID'] . '&linkBack=FundRaiserEditor.php?FundRaiserID=' . $iFundRaiserID ?>"><i
                                        class="fa fa-pencil" aria-hidden="true"></i>&nbsp;<?= $row['di_Item'] ?></a>
                            </td>
                            <td>
                                <?php if ($row['di_multibuy']) {
                                    echo 'X';
                                } ?>&nbsp;
                            </td>
                            <td>
                                <?= $row['donorFirstName'] . ' ' . $row['donorLastName'] ?>&nbsp;
                            </td>
                            <td>
                                <?php if ($row['di_multibuy']) {
                                    echo _('Multiple');
                                } else {
                                    echo $row['buyerFirstName'] . ' ' . $row['buyerLastName'];
                                } ?>&nbsp;
                            </td>
                            <td>
                                <?= $row['di_title'] ?>&nbsp;
                            </td>
                            <td align=center>
                                <?= OutputUtils::number_localized($row['di_sellprice']) ?>&nbsp;
                            </td>
                            <td align=center>
                                <?= OutputUtils::number_localized($row['di_estprice']) ?>&nbsp;
                            </td>
                            <td align=center>
                                <?= OutputUtils::number_localized($row['di_materialvalue']) ?>&nbsp;
                            </td>
                            <td align=center>
                                <?= OutputUtils::number_localized($row['di_minimum']) ?>&nbsp;
                            </td>
                            <td>
                                <a href="<?= SystemURLs::getRootPath() ?>/DonatedItemDelete.php?DonatedItemID=<?= $row['di_ID'] . '&linkBack=FundRaiserEditor.php?FundRaiserID=' . $iFundRaiserID ?>">
                                    <i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                    } // while
                }// if
                ?>

            </table>
        </div>
    </div>

    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        $(document).ready(function () {
            $('.fundraiser-table').DataTable({
                responsive: true,
                "language": {
                    "url": window.CRM.plugin.dataTable.language.url
                },
            });
        });
    </script>
<?php } ?>

<?php require 'Include/Footer.php' ?>
