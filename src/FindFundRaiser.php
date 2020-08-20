<?php
/*******************************************************************************
 *
 *  filename    : FindFundRaiser.php
 *  last change : 2009-04-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
 *                Copyright 2019 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use Propel\Runtime\Propel;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserQuery;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;

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
        $sCrieria .= "OR fr_ID = '$iID' ";
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
require 'Include/Header.php';

?>
<div class="card card-body">
  <form method="get" action="FindFundRaiser.php" name="FindFundRaiser">
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
            <input type="text" name="DateStart" maxlength="10" id="DateStart" size="11" value="<?= OutputUtils::change_date_for_place_holder($dDateStart) ?>" class="date-picker form-control input-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
        </div>
        <div class="col-lg-1">
        </div>
        <div class="col-lg-3">
            <input type="submit" class="btn btn-primary btn-sm" value="<?= _('Apply Filters') ?>" name="FindFundRaiserSubmit">
        </div>
    </div>
    <div class="row">
        <div class="col-lg-2">
            <?= _('Date End') ?>:
        </div>
        <div class="col-lg-2">
            <input type="text" name="DateEnd" maxlength="10" id="DateEnd" size="11" value="<?= OutputUtils::change_date_for_place_holder($dDateEnd) ?>" class="date-picker form-control input-sm" placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
        </div>
        <div class="col-lg-1">
        </div>
        <div class="col-lg-3">
            <input type="submit" class="btn btn-danger btn-sm" value="<?= _('Clear Filters') ?>" name="FilterClear">
        </div>
    </div>
  </form>
</div>

<div class="card card-body">
<?php

// Append a LIMIT clause to the SQL statement
$iPerPage = SessionUser::getUser()->getSearchLimit();
if (empty($_GET['Result_Set'])) {
    $Result_Set = 0;
} else {
    $Result_Set = InputUtils::LegacyFilterInput($_GET['Result_Set'], 'int');
}

if ($iPerPage != '5' && $iPerPage != '10' && $iPerPage != '20' && $iPerPage != '25'
    && $iPerPage != '50' && $iPerPage != '100' && $iPerPage != '200' && $iPerPage != '500') {
    $res = intval($iPerPage);
    if ($res < 5) {
        $iPerPage = '5';
    } else if ($res < 10) {
        $iPerPage = '10';
    } else if ($res < 20) {
        $iPerPage = '20';
    } else if ($res < 25) {
        $iPerPage = '25';
    } else if ($res < 50) {
        $iPerPage = '50';
    } else if ($res < 100) {
        $iPerPage = '100';
    } else if ($res < 200) {
        $iPerPage = '200';
    } else if ($res < 500) {
        $iPerPage = '500';
    }

    $tmpUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
    $tmpUser->setSearchLimit($iPerPage);
    $tmpUser->save();

    $_SESSION['user'] = $tmpUser;
}

$sLimitSQL = " LIMIT $Result_Set, $iPerPage";

// Build SQL query
$sSQL = "SELECT fr_ID, fr_Date, fr_Title FROM fundraiser_fr $sCriteria $sOrderSQL $sLimitSQL";
$sSQLTotal = "SELECT COUNT(fr_ID) FROM fundraiser_fr $sCriteria";

// Execute SQL statement and get total result
$connection = Propel::getConnection();

$pdoDep = $connection->prepare($sSQL);
$pdoDep->execute();

$pdoTotal= $connection->prepare($sSQLTotal);
list($Total) = $pdoTotal->fetch( \PDO::FETCH_BOTH );
?>

<div align="center">
<form action="FindFundRaiser.php" method="get" name="ListNumber">

<?php
// Show previous-page link unless we're at the first page
if ($Result_Set < $Total && $Result_Set > 0) {
    $thisLinkResult = $Result_Set - $iPerPage;
    if ($thisLinkResult < 0) {
        $thisLinkResult = 0;
    }
?>
    <a href="<?= SystemURLs::getRootPath() ?>/FindFundRaiser.php?Result_Set=<?= $thisLinkResult ?>&Sort=<?= $sSort ?>"><?= _('Previous Page') ?></a>&nbsp;&nbsp;
<?php
}

// Calculate starting and ending Page-Number Links
$Pages = ceil($Total / $iPerPage);
$startpage = (ceil($Result_Set / $iPerPage)) - 6;
if ($startpage <= 2) {
    $startpage = 1;
}
$endpage = (ceil($Result_Set / $iPerPage)) + 9;
if ($endpage >= ($Pages - 1)) {
    $endpage = $Pages;
}

// Show Link "1 ..." if startpage does not start at 1
if ($startpage != 1) {
?>
    <a href="<?= SystemURLs::getRootPath() ?>/FindFundRaiser.php?Result_Set=0&Sort=<?= $sSort ?>&ID=<?= $iID ?>&DateStart=<?= $dDateStart ?>&DateEnd=<?= $dDateEnd ?>">1</a> ...
<?php
}

// Display page links
if ($Pages > 1) {
    for ($c = $startpage; $c <= $endpage; $c++) {
        $b = $c - 1;
        $thisLinkResult = $iPerPage * $b;
        if ($thisLinkResult != $Result_Set) {
?>
    <a href="<?= SystemURLs::getRootPath() ?>/FindFundRaiser.php?Result_Set=<?= $thisLinkResult ?>&Sort=<?= $sSort ?>&ID=<?= $iID ?>&DateStart=<?= $dDateStart ?>&DateEnd=<?= $dDateEnd ?>"><?= $c ?></a>&nbsp;
<?php
        } else {
            echo '&nbsp;&nbsp;[ '.$c.' ]&nbsp;&nbsp;';
        }
    }
}

// Show Link "... xx" if endpage is not the maximum number of pages
if ($endpage != $Pages) {
    $thisLinkResult = ($Pages - 1) * $iPerPage;
?>
    <a href="<?= SystemURLs::getRootPath() ?>/FindFundRaiser.php?Result_Set=<?= $thisLinkResult ?>&Sort=<?= $sSort ?>&ID=<?= $iID ?>&DateStart=<?= $dDateStart ?>&DateEnd=<?= $dDateEnd ?>"><?= $Pages ?></a>
<?php
}

// Show next-page link unless we're at the last page
if ($Result_Set >= 0 && $Result_Set < $Total) {
    $thisLinkResult = $Result_Set + $iPerPage;
    if ($thisLinkResult < $Total) {
?>
        &nbsp;&nbsp;<a href="<?= SystemURLs::getRootPath() ?>/FindFundRaiser.php?Result_Set=<?= $thisLinkResult ?>&Sort=<?= $sSort ?>"><?= _('Next Page') ?></a>&nbsp;&nbsp;
<?php
    }
}

// Display Record Limit
echo '<input type="hidden" name="Result_Set" value="'.$Result_Set.'">';
if (isset($sSort)) {
    echo '<input type="hidden" name="Sort" value="'.$sSort.'">';
}

$sLimit5 = '';
$sLimit10 = '';
$sLimit20 = '';
$sLimit25 = '';
$sLimit50 = '';

if ($_SESSION['SearchLimit'] == '5') {
    $sLimit5 = 'selected';
}
if ($_SESSION['SearchLimit'] == '10') {
    $sLimit10 = 'selected';
}
if ($_SESSION['SearchLimit'] == '20') {
    $sLimit20 = 'selected';
}
if ($_SESSION['SearchLimit'] == '25') {
    $sLimit25 = 'selected';
}
if ($_SESSION['SearchLimit'] == '50') {
    $sLimit50 = 'selected';
}

?>
	</form>
</div>
    <br>
 <table cellpadding='4' align='center' cellspacing='0' width='100%' id='fund-listing-table' class="table table-striped table-bordered dataTable no-footer dtr-inline">
	<thead class='TableHeader'>
	<th width='25'><?= _('Edit') ?></th>
	<th><?= _('Number') ?></th>
	<th><?= _('Date') ?></th>
	<th><?= _('Title') ?></th>
	</thead>
<?php
// Display Deposits
while (list($fr_ID, $fr_Date, $fr_Title) = $pdoDep->fetch( \PDO::FETCH_BOTH )) {
?>
    <tr>
        <td>
            <a href="<?= SystemURLs::getRootPath() ?>/FundRaiserEditor.php?FundRaiserID=<?= $fr_ID ?>">
                <i class="fa fa-pencil" aria-hidden="true"></i>
            </a>
        </td>
        <td><?= $fr_ID ?></td>
        <td><?= OutputUtils::change_date_for_place_holder($fr_Date) ?></td>
        <!-- Get deposit total -->
        <td><?= $fr_Title ?></td>
<?php
}
?>
 </table>
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
