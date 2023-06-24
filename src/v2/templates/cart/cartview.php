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
    <div class="card">
        <div class="card-header border-1">
            <h3 class="card-title"><?= _("Cart Functions") ?></h3>
        </div>
        <div class="card-body">
            <a href="#" id="emptyCart" class="btn btn-app emptyCart"><i class="fas fa-eraser"></i><?= _('Empty Cart') ?>
            </a>
            <?php if (SessionUser::getUser()->isManageGroupsEnabled()) {
                ?>
                <a id="emptyCartToGroup" class="btn btn-app"><i
                        class="fas fa-tag"></i><?= _('Empty Cart to Group') ?></a>
                <?php
            }
            if (SessionUser::getUser()->isAddRecordsEnabled()) {
                ?>
                <a href="<?= $sRootPath ?>/v2/cart/to/family" class="btn btn-app"><i
                        class="fas fa-users"></i><?= _('Empty Cart to Family') ?></a>
                <?php
            } ?>
            <a href="#" id="emptyCartToEvent" class="btn btn-app"><i
                    class="fas fa-ticket-alt"></i><?= _('Empty Cart to Event') ?></a>

            <?php
            if (SessionUser::getUser()->isShowMapEnabled()) {
                ?>
                <a href="<?= $sRootPath ?>/v2/map/0" class="btn btn-app"><i
                        class="fas fa-map-marker-alt"></i><?= _('Map Cart') ?></a>
                <?php
            }
            ?>
            <?php if (SessionUser::getUser()->isManageGroupsEnabled()) {
            ?>
            <a class="btn btn-app bg-yellow-gradient"
               data-toggle="tooltip" data-placement="bottom" title="<?= _("Get the vCard of the person") ?>"
               href="<?= $sRootPath ?>/api/cart/addressbook/extract"><i
                    class="far fa-id-card">
                </i> <?= _("vCard") ?></a>
            <?php
                    }
                    ?>
            <?php if (SessionUser::getUser()->isCSVExportEnabled()) {
                ?>
                <a href="<?= $sRootPath ?>/v2/system/csv/export/cart" class="btn btn-app bg-gradient-green"><i
                        class="fas fa-file-excel"></i><?= _('CSV Export') ?></a>
                <?php
            } ?>
            <a href="<?= $sRootPath ?>/Reports/NameTags.php?labeltype=74536&labelfont=times&labelfontsize=36"
               class="btn btn-app bg-gradient-blue"><i
                    class="fas fa-file-pdf"></i><?= _('Name Tags') ?></a>
            <a class="btn btn-app bg-gradient-purple" href="<?= $sRootPath ?>/v2/cart/to/badge"> <i
                    class="fas fa-id-badge"></i> <span class="cartActionDescription"><?= _("Badges") ?></span></a>
            <?php

            if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
                // Display link
                ?>
                <a href="mailto:<?= $sEmailLink ?>" class="btn btn-app" id="emailLink" target="_blank"><i
                        class='far fa-paper-plane'></i><?= _('Email Cart') ?></a>
                <a href="mailto:?bcc=<?= $sEmailLink ?>" class="btn btn-app" id="emailCCIlink" target="_blank"><i
                        class="fas fa-paper-plane"></i><?= _('Email (BCC)') ?></a>
                <?php
            }

            if ($sPhoneLink) {
                if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
                    ?>
                    &nbsp;
                    <div class="btn-group" id="globalSMSLink">
                        <a class="btn btn-app" href="javascript:void(0)" onclick="allPhonesCommaD()"><i
                                class="fas fa-mobile"></i> <?= _("Text Cart") ?></a>
                        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <a href="javascript:void(0)" onclick="allPhonesCommaD()" class="dropdown-item "><i
                                        class="fas fa-mobile"></i> <?= _("Copy Paste the Texts") ?></a>
                            <a href="sms:<?= str_replace(' ', '', mb_substr($sPhoneLinkSMS, 0, -2)) ?>"
                                   class="dropdown-item sPhoneLinkSMS"><i class="fas fa-mobile"></i> <?= _("Text Cart") ?></a>
                        </div>
                    </div>
                    <?php
                }
            } ?>
            <a href="<?= $sRootPath ?>/v2/people/directory/report/Cart+Directory"
               class="btn btn-app"><i
                    class="fas fa-book"></i><?= _('Create Directory From Cart') ?></a>

            <?php if (SessionUser::getUser()->isAddRecordsEnabled()) {
                ?>
                <a href="#" id="deleteCart" class="btn btn-app bg-gradient-red"><i
                        class="fas fa-trash-alt"></i><?= _('Delete Persons From CRM') ?></a>

                <a href="#" id="deactivateCart" class="btn btn-app bg-gradient-orange"><i
                        class="fas fa-trash-alt"></i><?= _('Deactivate Persons From Cart') ?></a>
                <?php
            } ?>

        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
    <!-- Default card -->
    <div class="card">
        <div class="card-header border-1">
            <h3 class="card-title"><?= _('Generate Labels') ?></h3>
        </div>
        <form method="get" action="<?= $sRootPath ?>/Reports/PDFLabel.php" name="labelform">
            <div class="card-body">
                <?php
                LabelUtils::LabelGroupSelect('groupbymode');
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <?= _('Bulk Mail Presort') ?>
                    </div>
                    <div class="col-md-6">
                        <input name="bulkmailpresort" type="checkbox" onclick="codename()" id="BulkMailPresort"
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
                            name="bulkmailquiet" type="checkbox" onclick="codename()" id="QuietBulkMail" value="1"
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
            <div class="row">
                <div class="col-md-5"></div>
                <div class="col-md-4">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-primary"
                                                         value="<?= _('Generate Labels') ?>" name="Submit">
                </div>
            </div>
            <br>
        </form>
        <!-- /.card-body -->
    </div>


    <?php
} ?>

<!-- END CART FUNCTIONS -->

<!-- BEGIN CART LISTING -->
<?php if (isset($iNumPersons) && $iNumPersons > 0): ?>
    <div class="card card-primary">
        <div class="card-header border-1">
            <h3 class="card-title">
                <?= _('Your cart contains') . ' ' . $iNumPersons . ' ' . _('persons from') . ' ' . $iNumFamilies . ' ' . _('families') ?>
                .</h3>
        </div>
        <div class="card-body">
            <table class="table table-hover dt-responsive" id="cart-listing-table" style="width:100%;">

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
