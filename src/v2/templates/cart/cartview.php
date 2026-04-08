<?php

/*******************************************************************************
 *
 *  filename    : templates/cartview.php
 *  last change : 2019-12-26
 *  description : manage the cartview
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *
 ******************************************************************************/

use EcclesiaCRM\Utils\LabelUtils;
use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<!-- BEGIN CART FUNCTIONS -->
<?php
if ($iNumPersons > 0) {
    ?>
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-shopping-cart mr-1"></i><?= _('Cart Actions') ?></h3>
        </div>
        <div class="card-body py-2">
            <div class="d-flex flex-wrap">
                <a href="#" id="emptyCart" class="btn btn-sm btn-outline-danger emptyCart mr-2 mb-2"><i class="fas fa-eraser mr-1"></i><?= _('Empty Cart') ?></a>

                <?php if (SessionUser::getUser()->isManageGroupsEnabled()) { ?>
                    <a id="emptyCartToGroup" class="btn btn-sm btn-outline-primary mr-2 mb-2"><i class="fas fa-tag mr-1"></i><?= _('Empty Cart to Group') ?></a>
                <?php }

                if (SessionUser::getUser()->isAddRecordsEnabled()) { ?>
                    <a href="<?= $sRootPath ?>/v2/cart/to/family" class="btn btn-sm btn-outline-primary mr-2 mb-2"><i class="fas fa-users mr-1"></i><?= _('Empty Cart to Family') ?></a>
                <?php } ?>

                <a href="#" id="emptyCartToEvent" class="btn btn-sm btn-outline-primary mr-2 mb-2"><i class="fas fa-ticket-alt mr-1"></i><?= _('Empty Cart to Event') ?></a>

                <?php if (SessionUser::getUser()->isShowMapEnabled()) { ?>
                    <a href="<?= $sRootPath ?>/v2/map/0" class="btn btn-sm btn-outline-secondary mr-2 mb-2"><i class="fas fa-map-marker-alt mr-1"></i><?= _('Map Cart') ?></a>
                <?php } ?>

                <?php if (SessionUser::getUser()->isManageGroupsEnabled()) { ?>
                    <a class="btn btn-sm btn-outline-warning mr-2 mb-2"
                        data-toggle="tooltip" data-placement="bottom" title="<?= _("Get the vCard of the person") ?>"
                        href="<?= $sRootPath ?>/api/cart/addressbook/extract"><i class="far fa-id-card mr-1"></i><?= _("vCard") ?></a>
                <?php } ?>

                <?php if (SessionUser::getUser()->isCSVExportEnabled()) { ?>
                    <a href="<?= $sRootPath ?>/v2/system/csv/export/cart" class="btn btn-sm btn-outline-success mr-2 mb-2"><i class="fas fa-file-excel mr-1"></i><?= _('CSV Export') ?></a>
                <?php } ?>

                <a href="<?= $sRootPath ?>/Reports/NameTags.php?labeltype=74536&labelfont=times&labelfontsize=36" class="btn btn-sm btn-outline-info mr-2 mb-2"><i class="fas fa-file-pdf mr-1"></i><?= _('Name Tags') ?></a>
                <a class="btn btn-sm btn-outline-info mr-2 mb-2" href="<?= $sRootPath ?>/v2/cart/to/badge"><i class="fas fa-id-badge mr-1"></i><span class="cartActionDescription"><?= _("Badges") ?></span></a>

                <?php
                if (SessionUser::getUser()->isEmailEnabled()) {
                ?>
                    <a href="mailto:<?= $sEmailLink ?>" class="btn btn-sm btn-outline-secondary mr-2 mb-2" id="emailLink" target="_blank"><i class="far fa-paper-plane mr-1"></i><?= _('Email Cart') ?></a>
                    <a href="mailto:?bcc=<?= $sEmailLink ?>" class="btn btn-sm btn-outline-secondary mr-2 mb-2" id="emailCCIlink" target="_blank"><i class="fas fa-paper-plane mr-1"></i><?= _('Email (BCC)') ?></a>
                <?php
                }

                if ($sPhoneLink) {
                    if (SessionUser::getUser()->isEmailEnabled()) {
                ?>
                    <div class="btn-group mr-2 mb-2" id="globalSMSLink">
                        <a class="btn btn-sm btn-outline-secondary allPhonesCommaD" href="#"><i class="fas fa-mobile mr-1"></i><?= _("Text Cart") ?></a>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <a href="#" class="dropdown-item allPhonesCommaD"><i class="fas fa-mobile mr-1"></i><?= _("Copy Paste the Texts") ?></a>
                            <a href="sms:<?= str_replace(' ', '', mb_substr($sPhoneLinkSMS, 0, -2)) ?>" id="sPhoneLinkSMS" class="dropdown-item"><i class="fas fa-mobile mr-1"></i><?= _("Text Cart") ?></a>
                        </div>
                    </div>
                <?php
                    }
                }
                ?>

                <a href="<?= $sRootPath ?>/v2/people/directory/report/Cart+Directory" class="btn btn-sm btn-outline-secondary mr-2 mb-2"><i class="fas fa-book mr-1"></i><?= _('Create Directory From Cart') ?></a>

                <?php if (SessionUser::getUser()->isAddRecordsEnabled()) { ?>
                    <a href="#" id="deleteCart" class="btn btn-sm btn-outline-danger mr-2 mb-2"><i class="fas fa-trash-alt mr-1"></i><?= _('Delete Persons From CRM') ?></a>
                    <a href="#" id="deactivateCart" class="btn btn-sm btn-outline-warning mr-2 mb-2"><i class="fas fa-user-slash mr-1"></i><?= _('Deactivate Persons From Cart') ?></a>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Default card -->
     <form method="get" action="<?= $sRootPath ?>/Reports/PDFLabel.php" name="labelform">
      <div class="card card-secondary collapsed-card">
          <div class="card-header">
            <h3 class="card-title"><?= _('Generate Labels') ?></h3>

            <div class="card-tools pull-right">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">            
            <?php
            LabelUtils::LabelGroupSelect('groupbymode');
            ?>
            <div class="row">
                <div class="col-md-3">
                    <?= _('Bulk Mail Presort') ?>
                </div>
                <div class="col-md-3">
                    <input name="bulkmailpresort" type="checkbox" id="BulkMailPresort" class="codename"
                            value="1"
                            <?= (array_key_exists('buildmailpresort', $_COOKIE) && $_COOKIE['bulkmailpresort']) ? 'checked' : '' ?>><br>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= _('Quiet Presort') ?>
                </div>
                <div class="col-md-6">
                    <!-- // This would be better with $_SESSION variable -->
                    <!-- // instead of cookie ... (save $_SESSION in MySQL) -->
                    <input
                        <?= (array_key_exists('buildmailpresort', $_COOKIE) && !$_COOKIE['bulkmailpresort']) ? 'disabled ' : '' ?>
                        name="bulkmailquiet" type="checkbox" id="QuietBulkMail" value="1" class="codename" disabled
                        <?= (array_key_exists('bulkmailquiet', $_COOKIE) && $_COOKIE['bulkmailquiet'] && array_key_exists('buildmailpresort', $_COOKIE) && $_COOKIE['bulkmailpresort']) ? 'checked' : '' ?>>
                </div>
            </div>
            <?php
            LabelUtils::ToParentsOfCheckBox('toparents');
            LabelUtils::LabelSelect('labeltype');
            LabelUtils::FontSelect('labelfont');
            LabelUtils::FontSizeSelect('labelfontsize');
            LabelUtils::StartRowStartColumn();
            LabelUtils::IgnoreIncompleteAddresses();
            LabelUtils::LabelFileType();
            ?>                                           
        </div>
        <!-- /.card-body -->
        <div class="card-footer">
            <input type="submit" class="btn btn-primary" value="<?= _('Generate Labels') ?>" name="Submit">
        </div> 
    </div>
    </form>


    <?php
} ?>

<!-- END CART FUNCTIONS -->

<!-- BEGIN CART LISTING -->
<?php if (isset($iNumPersons) && $iNumPersons > 0): ?>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <?= _('Your cart contains') . ' ' . $iNumPersons . ' ' . _('persons from') . ' ' . $iNumFamilies . ' ' . _('families') ?>
                .</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm table-hover dt-responsive" id="cart-listing-table" style="width:100%;">

            </table>
        </div>
    </div>
<?php endif; ?>
<!-- END CART LISTING -->

<script src="<?= $sRootPath ?>/skin/js/CartView.js"></script>

<script nonce="<?= $CSPNonce ?>">
    window.CRM.sEmailLink = "<?= mb_substr($sEmailLink, 0, -2) ?>";
    window.CRM.sPhoneLink = "<?= mb_substr($sPhoneLink, 0, -2) ?>";
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
