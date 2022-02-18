<?php
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

// Set the page title and include HTML header
$sPageTitle = "EcclesiaCRM - Family Verification";
require(SystemURLs::getDocumentRoot(). "/Include/HeaderNotLoggedIn.php");
?>

    <div class="register-box">
        <div class="register-logo">
            <a href="<?= SystemURLs::getRootPath() ?>/"><b>Ecclesia</b>CRM</a><br/>
            <span><?= SystemConfig::getValue("sChurchName") .  $token ?></span>
        </div>

        <div class="register-box-body">
            <p class="login-box-msg"><?= gettext("Please enter the following to start your family's verification") ?></p>

            <form action="<?= SystemURLs::getRootPath() ?>/external/verify/" method="post">
                <div class="form-group has-feedback">
                    <input name="firstName" type="text" class= "form-control form-control-sm" placeholder="<?= gettext("First Name") ?>" required>
                    <span class="fas fa-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input name="lastName" type="text" class= "form-control form-control-sm" placeholder="<?= gettext("Last Name") ?>" required>
                    <span class="fas fa-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input name="zip" type="text" class= "form-control form-control-sm" placeholder="<?= gettext("Zip") ?>" required>
                    <span class="fas fa-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input name="email" type="text" class= "form-control form-control-sm" placeholder="<?= gettext("Email") ?>" required>
                    <span class="fas fa-user form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <button type="submit" class="btn bg-olive"><?= gettext("Next"); ?></button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        <!-- /.form-box -->
    </div>

<?php
// Add the page footer
require(SystemURLs::getDocumentRoot(). "/Include/FooterNotLoggedIn.php");
