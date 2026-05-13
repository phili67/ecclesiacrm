<?php

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SystemService;

// Set the page title and include HTML header
$sPageTitle = _("Family Registration");
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");

$sessionLogoPath = SystemURLs::getRootPath() . "/icon-large.png";
?>

    <form action="<?= SystemURLs::getRootPath() ?>/external/register/done" method="post">
        <div class="register-box register-box-custom external-auth-box external-auth-box--narrow">
            <div class="card register-box-body blur external-auth-card">
                <div class="card-header register-logo external-auth-header">
                    <div class="external-auth-brand">
                        <img src="<?= $sessionLogoPath ?>" alt="<?= Bootstrapper::getSoftwareName() ?>" class="external-auth-brand__logo">
                        <span class="external-auth-brand__version"><?= SystemService::getDBMainVersion() ?></span>
                    </div>
                </div>

            <div class="register-box-body blur external-auth-body">
                <p class="external-auth-title text-center"><b><?= Bootstrapper::getSoftwareName() ?></b><?= _('Registration Complete') ?></p>

                <div class="card card-success">
                    <div class="card-header border-1">
                        <h3
                            class="card-title"><?= _('Thank you for registering your family.'); ?></h3>
                    </div>
                    <div class="card-body">
                        <h3><?= $family->getName() . ' ' . _('Family') ?></h3>
                        <b><?= _('Address') ?></b>: <?= $family->getAddress(); ?><br/>
                        <b><?= _('Home Phone') ?></b>: <?= $family->getHomePhone(); ?>
                        <h3><?= _('Member(s)') ?></h3>
                        <?php foreach ($family->getActivatedPeople() as $person) {
                            ?>
                            <?= $person->getFamilyRoleName() . ' - ' . $person->getFullName(); ?><br/>
                            <?php
                        } ?>
                    </div>


                    <p/>

                    <div class="card-footer">
                        <div class="col-12">
                            <div class="text-center">
                                <a href="<?= SystemURLs::getRootPath() ?>/"
                                   class="btn btn-success btn-block"> <?= _("Done") ?> </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            </div>

            <!-- /.form-box -->
        </div>
    </form>
<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
