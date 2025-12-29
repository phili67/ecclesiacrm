<?php
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Bootstrapper;

// Set the page title and include HTML header
$sPageTitle = "CRM - Family Verification";
require(SystemURLs::getDocumentRoot(). "/Include/HeaderNotLoggedIn.php");
?>

    <div class="register-box blur">
        <div class="register-logo">
            <a href="<?= SystemURLs::getRootPath() ?>/"><?= Bootstrapper::getSoftwareName() ?><br/>
            <span><?= SystemConfig::getValue("sEntityName") ?></span>
        </div>

        <div class="alert alert-info alert-dismissible">
            <h4><i class="fas fa-ban"></i> <?= gettext("Alert") ?>!</h4>
            <?= gettext('Your account record indicates that you need to change your password before proceding.') ?>
        </div>

        <div class="card-header border-1">
            <h3 class="card-title"><?= gettext('Enter your current password, then your new password twice.  Passwords must be at least').' '.SystemConfig::getValue('iMinPasswordLength').' '.gettext('characters in length.') ?></h3>
        </div>

        <div class="register-box-body">
            <?php
            // output warning and error messages
            if (isset($sErrorText)) {
                echo '<div class="alert alert-danger"><i class="far fa-info-circle"></i> ' . $sErrorText . '</div>';
            }
            ?>

            <form action="<?= SystemURLs::getRootPath() ?>/ident/my-profile/<?= $_SESSION['realToken'] ?>" method="post">
                <div class="input-group mb-3 has-feedback">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-danger"><i class="fas fa-key"></i></button>
                    </div>
                    <input name="oldPassword" type="password" class= "form-control form-control-sm" placeholder="<?= gettext("Old Password") ?>" required>
                </div>
                <div class="input-group mb-3 has-feedback">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary"><i class="fas fa-key"></i></button>
                    </div>
                    <input name="newPassword" type="password" class= "form-control form-control-sm" placeholder="<?= gettext("New Password") ?>" required>
                </div>
                <div class="input-group mb-3 has-feedback">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary"><i class="fas fa-key"></i></button>
                    </div>
                    <input name="confirmPassword" type="password" class= "form-control form-control-sm" placeholder="<?= gettext("Confirm New Password") ?>" required>
                </div>
                <div class="row  mb-3">
                    <!-- /.col -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-block"><i
                                class="fas fa-check"></i> <?= _('Save') ?></button>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.form-box -->
    </div>

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot(). "/Include/FooterNotLoggedIn.php");
