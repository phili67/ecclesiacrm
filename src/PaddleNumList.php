<?php
/*******************************************************************************
 *
 *  filename    : PaddleNumList.php
 *  last change : 2009-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;


$linkBack = InputUtils::LegacyFilterInputArr($_GET, 'linkBack');

if (isset ($_GET['FundRaiserID'])) {
    $iFundRaiserID = InputUtils::LegacyFilterInputArr($_GET, 'FundRaiserID');
} else {
    $iFundRaiserID = $_SESSION['iCurrentFundraiser'];
}

$sPageTitle = _('Buyers for this fundraiser:') . $iFundRaiserID;
require 'Include/Header.php';
?>
<form method="post"
      action="<?= SystemURLs::getRootPath() ?>/Reports/FundRaiserStatement.php?CurrentFundraiser=<?= $iFundRaiserID ?>&linkBack=FundRaiserEditor.php?FundRaiserID=<?= $iFundRaiserID ?> &CurrentFundraiser=<?= $iFundRaiserID ?>\">

    <div class="card card-body">
        <div class="row">
            <?php
            if ($iFundRaiserID > 0) {
                ?>
                <input type=button class="btn btn-default btn-sm" value="<?= _('Select all') ?>" name=SelectAll id="SelectAll">
                <?php
            }
            ?>
            <input type=button class="btn btn-default btn-sm" value="<?= _('Select none') ?>" name=SelectNone id="SelectNone">
            <input type=button class="btn btn-primary btn-sm" value="<?= _('Add Buyer') ?> " name=AddBuyer id="AddBuyer">
            <input type=button class="btn btn-primary btn-sm" value="<?= _('Add Donors to Buyer List') ?> "
                   name=AddBuyer
                   onclick="javascript:document.location='AddDonors.php?FundRaiserID=<?= $iFundRaiserID ?>'">

            <input type=submit class="btn btn-info btn-sm" value="<?= _('Generate Statements for Selected') ?>"
                   name=GenerateStatements>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= _("Buyers") ?></h3>
        </div>
        <div class="card-body">
            <table cellpadding="5" cellspacing="5"
                   class="table table-striped table-bordered dataTable no-footer dtr-inline"
                   id="buyer-listing-table"
                   width="100%"></table>
        </div>
    </div>
</form>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.fundraiserID = <?= $iFundRaiserID ?>;
    window.CRM.checkAll = false;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/fundraiser/paddleNumList.js">

<?php require 'Include/Footer.php' ?>
