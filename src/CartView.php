<?php
/*******************************************************************************
 *
 *  filename    : CartView.php
 *  website     : http://www.ecclesiacrm.com
 *
 *  Copyright 2001-2003 Phillip Hullquist, Deane Barker, Chris Gebhardt
 *  Copyright 2019 Philippe Logel
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\LabelUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Propel\Runtime\Propel;

$connection = Propel::getConnection();

// Set the page title and include HTML header
$sPageTitle = _('View Your Cart');
require 'Include/Header.php'; ?>
<?php

if (!SessionUser::getUser()->isShowCartEnabled()) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

// Confirmation message that people where added to Event from Cart
if (!Cart::HasPeople()) {
    if (!array_key_exists('Message', $_GET)) {
        ?>
        <p class="text-center callout callout-warning"><?= _('You have no items in your cart.') ?> </p>
        <?php
    } else {
        switch ($_GET['Message']) {
            case 'aMessage': ?>
                <p class="text-center callout callout-info"><?= $_GET['iCount'] . ' ' . ($_GET['iCount'] == 1 ? 'Record' : 'Records') . ' Emptied into Event ID:' . $_GET['iEID'] ?> </p>
                <?php break;
        }
    }
    ?>
    <p align="center"><input type="button" name="Exit" class="btn btn-primary" value="<?= _('Back to Menu') ?>"
                             onclick="javascript:document.location='Menu.php';"></p>
    </div>
    <?php
} else {
    $iNumPersons = Cart::CountPeople();
    $iNumFamilies = Cart::CountFamilies();

    /*if ($iNumPersons > 16) {
        ?>
        <form method="get" action="CartView.php#GenerateLabels">
        <input type="submit" class="btn" name="gotolabels"
        value="<?= _('Go To Labels') ?>">
        </form>
        <?php
    }*/ ?>

    <!-- BEGIN CART FUNCTIONS -->
    <?php
    if (Cart::CountPeople() > 0) {
        ?>
        <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= _("Cart Functions") ?></h3>
        </div>
        <div class="box-body">
        <a href="#" id="emptyCart" class="btn btn-app emptyCart"><i class="fa fa-eraser"></i><?= _('Empty Cart') ?></a>
        <?php if (SessionUser::getUser()->isManageGroupsEnabled()) {
            ?>
            <a id="emptyCartToGroup" class="btn btn-app"><i
                    class="fa fa-object-ungroup"></i><?= _('Empty Cart to Group') ?></a>
            <?php
        }
        if (SessionUser::getUser()->isAddRecordsEnabled()) {
            ?>
            <a href="<?= SystemURLs::getRootPath() ?>/CartToFamily.php" class="btn btn-app"><i
                    class="fa fa-users"></i><?= _('Empty Cart to Family') ?></a>
            <?php
        } ?>
        <a href="#" id="emptyCartToEvent" class="btn btn-app"><i
                class="fa fa-ticket"></i><?= _('Empty Cart to Event') ?></a>

        <?php
        if (SessionUser::getUser()->isShowMapEnabled()) {
            ?>
            <a href="<?= SystemURLs::getRootPath() ?>/v2/map/0" class="btn btn-app"><i
                    class="fa fa-map-marker"></i><?= _('Map Cart') ?></a>
            <?php
        }
        ?>
        <?php if (SessionUser::getUser()->isCSVExportEnabled()) {
            ?>
            <a href="<?= SystemURLs::getRootPath() ?>/CSVExport.php?Source=cart" class="btn btn-app bg-green"><i
                    class="fa fa-file-excel-o"></i><?= _('CSV Export') ?></a>
            <?php
        } ?>
        <a href="<?= SystemURLs::getRootPath() ?>/Reports/NameTags.php?labeltype=74536&labelfont=times&labelfontsize=36"
           class="btn btn-app bg-aqua"><i
                class="fa fa-file-pdf-o"></i><?= _('Name Tags') ?></a>
        <a class="btn btn-app bg-purple" href="<?= SystemURLs::getRootPath() ?>/CartToBadge.php"> <i
                class="fa fa-file-picture-o"></i> <span class="cartActionDescription"><?= _("Badges") ?></span></a>
        <?php
        if (Cart::CountPeople() != 0) {

            // Email Cart links
            // Note: This will email entire group, even if a specific role is currently selected.
            $sSQL = "SELECT per_Email, fam_Email
                        FROM person_per
                        LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
                        LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
                        LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
                    WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

            $statementEmails = $connection->prepare($sSQL);
            $statementEmails->execute();

            $sEmailLink = '';
            while ($row = $statementEmails->fetch(\PDO::FETCH_BOTH)) {
                $sEmail = MiscUtils::SelectWhichInfo($row['per_Email'], $row['fam_Email'], false);
                if ($sEmail) {
                    /* if ($sEmailLink) // Don't put delimiter before first email
                        $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
                    // Add email only if email address is not already in string
                    if (!stristr($sEmailLink, $sEmail)) {
                        $sEmailLink .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
                    }
                }
            }

            $sEmailLink = mb_substr($sEmailLink, 0, -1);

            if ($sEmailLink) {
                // Add default email if default email has been set and is not already in string
                if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
                    $sEmailLink .= SessionUser::getUser()->MailtoDelimiter() . SystemConfig::getValue('sToEmailAddress');
                }

                $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

                if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
                    // Display link
                    ?>
                    <a href="mailto:<?= $sEmailLink ?>" class="btn btn-app" id="emailLink"><i
                            class='fa fa-send-o'></i><?= _('Email Cart') ?></a>
                    <a href="mailto:?bcc=<?= $sEmailLink ?>" class="btn btn-app" id="emailCCIlink"><i
                            class="fa fa-send"></i><?= _('Email (BCC)') ?></a>
                    <?php
                }
            }

            //Text Cart Link
            $sSQL = "SELECT per_CellPhone, fam_CellPhone
                            FROM person_per LEFT
                            JOIN family_fam ON person_per.per_fam_ID = family_fam.fam_ID
                        WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

            $statement = $connection->prepare($sSQL);
            $statement->execute();

            $sPhoneLink = '';
            $sPhoneLinkSMS = '';
            $sCommaDelimiter = ', ';

            while ($row = $statement->fetch(\PDO::FETCH_BOTH)) {
                $sPhone = MiscUtils::SelectWhichInfo($row['per_CellPhone'], $row['fam_CellPhone'], false);
                if ($sPhone) {
                    /* if ($sPhoneLink) // Don't put delimiter before first phone
                        $sPhoneLink .= $sCommaDelimiter;  */
                    // Add phone only if phone is not already in string
                    if (!stristr($sPhoneLink, $sPhone)) {
                        $sPhoneLink .= $sPhone . $sCommaDelimiter;
                        $sPhoneLinkSMS .= $sPhone . $sCommaDelimiter;
                    }
                }
            }
            if ($sPhoneLink) {
                if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
                    ?>
                    &nbsp;
                    <div class="btn-group" id="globalSMSLink">
                        <a class="btn btn-app" href="javascript:void(0)" onclick="allPhonesCommaD()"><i
                                class="fa fa-mobile-phone"></i> <?= _("Text Cart") ?></a>
                        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="javascript:void(0)" onclick="allPhonesCommaD()"><i
                                        class="fa fa-mobile-phone"></i> <?= _("Copy Paste the Texts") ?></a></li>
                            <li><a href="sms:<?= str_replace(' ', '', mb_substr($sPhoneLinkSMS, 0, -2)) ?>" class="sPhoneLinkSMS"><i
                                        class="fa fa-mobile-phone"></i><?= _("Text Cart") ?></li>
                        </ul>
                    </div>
                    <?php
                }
            } ?>
            <a href="<?= SystemURLs::getRootPath() ?>/DirectoryReports.php?cartdir=Cart+Directory"
               class="btn btn-app"><i
                    class="fa fa-book"></i><?= _('Create Directory From Cart') ?></a>

            <?php if (SessionUser::getUser()->isAddRecordsEnabled()) {
                ?>
                <a href="#" id="deleteCart" class="btn btn-app bg-red"><i
                        class="fa fa-trash"></i><?= _('Delete Persons From CRM') ?></a>

                <a href="#" id="deactivateCart" class="btn btn-app bg-orange"><i
                        class="fa fa-trash"></i><?= _('Deactivate Persons From Cart') ?></a>
                <?php
            } ?>

            </div>
            <!-- /.box-body -->
            </div>
            <!-- /.box -->
            <?php
        }
        ?>
        <!-- Default box -->
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= _('Generate Labels') ?></h3>
            </div>
            <form method="get" action="<?= SystemURLs::getRootPath() ?>/Reports/PDFLabel.php" name="labelform">
                <div class="box-body">
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
            <!-- /.box-body -->
        </div>


        <?php
    }
} ?>

<!-- END CART FUNCTIONS -->

<!-- BEGIN CART LISTING -->
<?php if (isset($iNumPersons) && $iNumPersons > 0): ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                <?= _('Your cart contains') . ' ' . $iNumPersons . ' ' . _('persons from') . ' ' . $iNumFamilies . ' ' . _('families') ?>
                .</h3>
        </div>
        <div class="box-body">
            <table class="table table-hover dt-responsive" id="cart-listing-table" style="width:100%;">

            </table>
        </div>
    </div>
<?php endif; ?>
<!-- END CART LISTING -->

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/CartView.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.sEmailLink = "<?= mb_substr($sEmailLink, 0, -2) ?>";
    window.CRM.sPhoneLink = "<?= mb_substr($sPhoneLink, 0, -2) ?>";
</script>

<?php
require 'Include/Footer.php';
?>
