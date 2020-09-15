<?php
/*******************************************************************************
 *
 *  filename    : FindFundRaiser.php
 *  last change : 2009-04-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
 *                Copyright 2020 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\FundRaiserQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

//Set the page title
$sPageTitle = _('Fundraiser Listing');

//Filter Values
$dDateStart = '';
$dDateEnd = '';
$iID = '';
$sSort = '';

if (array_key_exists('DateStart', $_GET)) {
    $dDateStart = InputUtils::FilterDate($_GET['DateStart']);
}
if (array_key_exists('DateEnd', $_GET)) {
    $dDateEnd = InputUtils::FilterDate($_GET['DateEnd']);
}
if (array_key_exists('ID', $_GET)) {
    $iID = InputUtils::LegacyFilterInput($_GET['ID']);
}
if (array_key_exists('Sort', $_GET)) {
    $sSort = InputUtils::LegacyFilterInput($_GET['Sort']);
}

// Build SQL Criteria
$sCriteria = '';
if ($dDateStart || $dDateEnd) {
    if (!$dDateStart && $dDateEnd) {
        $dDateStart = $dDateEnd;
    }
    if (!$dDateEnd && $dDateStart) {
        $dDateEnd = $dDateStart;
    }
    $sCriteria .= " WHERE fr_Date BETWEEN '$dDateStart' AND '$dDateEnd' ";
}
if ($iID) {
    if ($sCriteria) {
        $sCriteria .= "OR fr_ID = '$iID' ";
    } else {
        $sCriteria = " WHERE fr_ID = '$iID' ";
    }
}
if (array_key_exists('FilterClear', $_GET) && $_GET['FilterClear']) {
    $sCriteria = '';
    $dDateStart = '';
    $dDateEnd = '';
    $iID = '';
}

$ormDep = FundRaiserQuery::create();

if ($dDateStart || $dDateEnd) {
    //echo $dDateStart ." " . $dDateEnd;
    $ormDep->filterByDate(array("min" => $dDateStart . " 00:00:00", "max" => $dDateEnd . " 23:59:59"));
}

if ($iID) {
    $ormDep->findById($iID);
} else {
    $ormDep->find();
}

require 'Include/Header.php';

?>
<div class="card">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _('Filters') ?></h3>
    </div>
    <div class="card-body">
        <form method="get" action="<?= SystemURLs::getRootPath() ?>/FindFundRaiser.php" name="FindFundRaiser">
            <input name="sort" type="hidden" value="<?= $sSort ?>">

            <div class="row">
                <div class="col-lg-2">
                    <?= _('Number') ?>:
                </div>
                <div class="col-lg-2">
                    <input type="text" name="ID" id="ID" value="<?= $iID ?>" class="form-control input-sm">
                </div>
            </div>

            <div class="row">
                <div class="col-lg-2">
                    <?= _('Date Start') ?>:
                </div>
                <div class="col-lg-2">
                    <input type="text" name="DateStart" maxlength="10" id="DateStart" size="11"
                           value="<?= OutputUtils::change_date_for_place_holder($dDateStart) ?>"
                           class="date-picker form-control input-sm"
                           placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                </div>
                <div class="col-lg-1">
                </div>
                <div class="col-lg-3">
                    <input type="submit" class="btn btn-primary btn-sm" value="<?= _('Apply Filters') ?>"
                           name="FindFundRaiserSubmit">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2">
                    <?= _('Date End') ?>:
                </div>
                <div class="col-lg-2">
                    <input type="text" name="DateEnd" maxlength="10" id="DateEnd" size="11"
                           value="<?= OutputUtils::change_date_for_place_holder($dDateEnd) ?>"
                           class="date-picker form-control input-sm"
                           placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                </div>
                <div class="col-lg-1">
                </div>
                <div class="col-lg-3">
                    <input type="submit" class="btn btn-danger btn-sm" value="<?= _('Clear Filters') ?>"
                           name="FilterClear">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header with-border">
        <h3 class="card-title"><?= _('Fundraisers') ?></h3>
    </div>
    <div class="card-body">
        <br>
        <table cellpadding='4' align='center' cellspacing='0' width='100%' id='fund-listing-table'
               class="table table-striped table-bordered dataTable no-footer dtr-inline">
            <thead class='TableHeader'>
            <th width='25'><?= _('Edit') ?></th>
            <th><?= _('Number') ?></th>
            <th><?= _('Date') ?></th>
            <th><?= _('Title') ?></th>
            </thead>
            <?php
            // Display
            foreach ($ormDep as $dep) {
                ?>
                <tr>
                    <td>
                        <a href="<?= SystemURLs::getRootPath() ?>/FundRaiserEditor.php?FundRaiserID=<?= $dep->getId() ?>">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                    </td>
                    <td><?= $dep->getId() ?></td>
                    <td><?= OutputUtils::change_date_for_place_holder($dep->getDate()->format('Y-m-d')) ?></td>
                    <!-- Get deposit total -->
                    <td><?= $dep->getTitle() ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        $('#fund-listing-table').DataTable({
            responsive: true,
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
        });
    });
</script>

<?php require 'Include/Footer.php' ?>
