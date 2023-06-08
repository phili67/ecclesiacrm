<?php
/*******************************************************************************
 *
 *  filename    : PaddleNumList.php
 *  last change : 2020-09-22
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2020 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';
?>

<form method="post"
      action="<?= $sRootPath ?>/Reports/FundRaiserStatement.php?CurrentFundraiser=<?= $iFundRaiserID ?>&linkBack=v2/fundraiser/editor/<?= $iFundRaiserID ?>\">
    <div class="card card-body">
        <div class="row">

            <?php
            if ($iFundRaiserID > 0) {
                ?>
                <div class="col-md-2">
                    <input type="checkbox" id="SelectAll" name="scales">
                    <label for="SelectAll"><?= _('Select all') ?></label>
                </div>
                <?php
            }
            ?>
            <div class="col-md-2">
            <input type=button class="btn btn-success btn-sm" value="<?= _('Add Buyer') ?> " name=AddBuyer
                   id="AddBuyer">
            </div>
            <div class="col-md-3">
            <input type=button class="btn btn-primary btn-sm" value="<?= _('Add Donors to Buyer List') ?> "
                   name=AddDonnor id="AddDonnor">
            </div>
            <div class="col-md-3">
            <input type=submit class="btn btn-info btn-sm" value="<?= _('Generate Statements for Selected') ?>"
                   name=GenerateStatements>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header  border-1">
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

<script src="<?= $sRootPath ?>/skin/js/fundraiser/paddleNumList.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
