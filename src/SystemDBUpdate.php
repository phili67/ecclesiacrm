<?php

use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Service\UpgradeService;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\Bootstrapper;


// Include the function library
require 'Include/Config.php';
$bSuppressSessionTests = true; // DO NOT MOVE
require 'Include/Functions.php';

if (Bootstrapper::isDBCurrent()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if (InputUtils::FilterString($_GET['upgrade']) == "true") {
    try {
        UpgradeService::upgradeDatabaseVersion();
        RedirectUtils::Redirect('v2/dashboard');
        exit;
    } catch (\Exception $ex) {
        $errorMessage = $ex->getMessage();
    }
}

// Set the page title and include HTML header
$sPageTitle = _('System Upgrade');
require 'Include/HeaderNotLoggedIn.php'; ?>

<p></br></p>

<div class="error-page">
    <div class="row">
        <div class="col-3"><h1 class="headline text-yellow" style="font-size:60px">426</h1></div>
        <div class="col-6">
            <div class="error-content">
                <div class="row">
                    <h3><i class="fa fa-warning text-yellow"></i> <?= _('Upgrade Required') ?></h3>
                    <p>
                        <?= _("Current DB Version" . ": " . SystemService::getDBVersion()) ?> <br/>
                        <?= _("Current Software Version" . ": " . SystemService::getInstalledVersion()) ?> <br/>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-3"></div>
    </div>
    <?php if (empty($errorMessage)) {
    ?>
        <div class="row">
            <div class="col-12">
                <p></br></p>
                <form>
                    <input type="hidden" name="upgrade" value="true"/>
                    <button type="submit" class="btn btn-primary btn-block"><i
                            class="fa fa-database"></i> <?= _('Upgrade database') ?></button>
                </form>
            </div>
        </div>
    <?php
} else {
        ?>
        <div class="main-box-body clearfix" id="globalMessage">
            <div class="alert alert-danger fade in" id="globalMessageAlert">
                <i class="fa fa-warning fa-fw fa-lg"></i> <?= $errorMessage ?>
            </div>
        </div>
    <?php
    } ?>
</div>


<?php require 'Include/FooterNotLoggedIn.php'; ?>
