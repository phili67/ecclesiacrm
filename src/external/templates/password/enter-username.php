<?php

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

// Set the page title and include HTML header
$sPageTitle = gettext("Family Registration");
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");
?>

    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header login-logo">
                <?php
                $headerHTML = Bootstrapper::getSoftwareName();
                $sHeader = SystemConfig::getValue("sHeader");
                $sEntityName = SystemConfig::getValue("sEntityName");
                if (!empty($sHeader)) {
                    $headerHTML = html_entity_decode($sHeader, ENT_QUOTES);
                } else if (!empty($sEntityName)) {
                    $headerHTML = $sEntityName;
                }
                ?>
                <a href="<?= SystemURLs::getRootPath() ?>/"><?= $headerHTML ?></a>
            </div>

            <div class="card-body login-card-body">
            <p class="login-box-msg"><?= gettext('Reset your password') ?></p>

            <div class="form-group has-feedback">
                <div class="input-group mb-3">
                    <input id="username" type="text" class= "form-control form-control-sm" placeholder="<?= gettext('Login Name') ?>"
                       required>
                    <div class="input-group-text">
                        <span class="fas fa-user"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" id="resetPassword" class="btn btn-primary btn-block"><?= gettext('OK'); ?></button>
                </div>
                <!-- /.col -->
            </div>
            <p class="mt-3 mb-1">
                <a href="<?= SystemURLs::getRootPath() ?>/session/login"><?= _("Login") ?></a>
            </p>
        </div>
        </div>
        <!-- /.form-box -->
    </div>
    <script nonce="<?= SystemURLs::getCSPNonce() ?>" >
        $("#resetPassword").on('click',function (e) {
            var userName = $("#username").val();
            if (userName) {
                fetch(window.CRM.root + "/external/password/reset/" + userName, {            
                    method: "POST"
                })
                    .then(res => res.json())
                    .then(data => {
                        bootbox.alert("<?= gettext("Check your email for a password reset link")?>",
                            function () {
                                window.location.href = window.CRM.root + "/";
                            }
                        );
                    })
                    .catch(error => {
                        // enter your logic for when there is an error (ex. error toast)
                        bootbox.alert("<?= gettext("Sorry, we are unable to process your request at this point in time.")?>");
                    });            

            } else {
                bootbox.alert("<?= gettext("Login Name is Required")?>");
            }
        });
    </script>
<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
