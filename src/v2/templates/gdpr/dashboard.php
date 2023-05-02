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

<div class="card card-primary card-body">
    <div class="row ">
        <div class="col-sm-2" style="vertical-align: middle;">
            <a class="btn btn-app" href="<?= $sRootPath ?>/Reports/GDPR/GDPRListExport.php"><i
                    class="fas fa-print"></i> <?= _("Printable Page") ?></a>
        </div>
        <div class="col-sm-9" style="vertical-align: middle;">
            <table class="outer">
                <tr>
                    <td><label><?= _("GDPR DPO Signer") ?></label></td>
                    <td>&nbsp;:&nbsp;</td>
                    <td><?= $gdprSigner ?></td>
                </tr>
                <tr>
                    <td><label><?= _("GDPR DPO Signer Email") ?></label></td>
                    <td>&nbsp;:&nbsp;</td>
                    <td><?= $gdprSignerEmail ?></td>
                </tr>
            </table>
        </div>        
        <?php if ( SessionUser::getUser()->isAdmin() ) { ?>
                <div class="col-sm-1 pull-right" style="vertical-align: middle;">
                    <a class="btn btn-app" href="<?= $sRootPath ?>/v2/systemsettings/GDPR" data-typeid="2" data-toggle="tooltip"  data-placement="bottom" title="<?= _("GDPR Settings") ?>"><i
                        class="fas fa-gear"></i><?= _("Settings") ?></a>
                </div>            
            <?php } ?>
    </div>
</div>

<div class="card">
    <div class="card-header border-1">
        <div class="card-title">
            <h3 class="card-title"><i class="fas fa-user"></i> <?= _("GDPR Person status") ?></h3>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered" id="GDRP-Table" cellpadding="5" cellspacing="0"
               width="100%"></table>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/gdpr/GDRPDashboard.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
