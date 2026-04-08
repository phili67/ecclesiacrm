<?php

/*******************************************************************************
 *
 *  filename    : dashboard.php
 *  last change : 2018-07-13
 *  description : manage the full GDPR
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorizaion
 *
 ******************************************************************************/

 use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-shield-alt mr-1"></i><?= _('GDPR Management') ?></h3>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-info" href="<?= $sRootPath ?>/Reports/GDPR/GDPRListExport.php" data-toggle="tooltip"  data-placement="bottom" title="<?= _("Export GDPR Data") ?>">
                <i class="fas fa-file-export mr-1"></i><?= _("Export") ?>
            </a>
            <?php if ( SessionUser::getUser()->isAdmin() ) { ?>
                <a class="btn btn-sm btn-warning" href="<?= $sRootPath ?>/v2/systemsettings/gdpr" data-toggle="tooltip"  data-placement="bottom" title="<?= _("GDPR Settings") ?>">
                    <i class="fas fa-cog mr-1"></i><?= _("Settings") ?>
                </a>            
            <?php } ?>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-sm-6">
                <div class="row mb-3">
                    <div class="col-sm-5">
                        <strong><?= _("GDPR DPO Signer") ?></strong>
                    </div>
                    <div class="col-sm-7">
                        <span><?= $gdprSigner ?></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                        <strong><?= _("GDPR DPO Signer Email") ?></strong>
                    </div>
                    <div class="col-sm-7">
                        <span><?= $gdprSignerEmail ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-info shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title mb-0"><i class="fas fa-users mr-1"></i><?= _("GDPR Person Status") ?></h3>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover table-bordered table-sm" id="GDRP-Table" cellpadding="5" cellspacing="0"
               width="100%"></table>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/gdpr/GDRPDashboard.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
