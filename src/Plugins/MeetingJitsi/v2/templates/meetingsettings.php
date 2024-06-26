<?php
/*******************************************************************************
 *
 *  filename    : meetingdashboard.php
 *  last change : 2020-07-04
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2020 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\ChurchMetaData;

require $sRootDocument . '/Include/Header.php';
?>

<div class="row" style="height: 100%">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div
                    class="card-title"><?= dgettext("messages-MeetingJitsi", "Settings") ?> : Jitsi
                </div>
                <div style="float:right"><a href="https://jitsi.org/" target="_blank">
                        <img src="<?= $sRootPath ?>/Images/jitsi_logo.png" height="25/"></a>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">sJitsiDomain</label>
                    <!--  Current Value -->
                    <div class="col-sm-4">
                        <input type="text" size="40" maxlength="255" name="domain" id="domain" value="<?= $domain ?>"
                               class= "form-control form-control-sm">
                    </div>
                    <div class="col-sm-4">
                        <a data-toggle="popover" 
                            title=""
                            data-content="<?= dgettext("messages-MeetingJitsi", "The Jitsi domain name, by default") ?> : meet.jit.si or 8x8.vc"
                           target="_blank"
                           class="blue"
                           data-original-title="<?=  dgettext("messages-MeetingJitsi", "Definition") ?>">
                            <i class="far  fa-question-circle"></i>
                        </a>
                        <label>8x8.vc <?= dgettext("messages-MeetingJitsi", "or") ?> meet.jit.si</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">sJitsiDomainScriptPath</label>
                    <!--  Current Value -->
                    <div class="col-sm-4">
                        <input type="text" size="40" maxlength="255" name="domainscriptpath" id="domainscriptpath"
                               value="<?= $domainscriptpath ?>" class= "form-control form-control-sm">
                    </div>
                    <div class="col-sm-4">
                        <a data-toggle="popover"
                           title=""
                           data-content="<?= dgettext("messages-MeetingJitsi", "The path for the script associated with the domain name") ?> : https://meet.jit.si/external_api.js or https://8x8.vc/external_api.js"
                           target="_blank"
                           class="blue"
                           data-original-title="<?=  dgettext("messages-MeetingJitsi", "Definition") ?>">
                            <i class="far  fa-question-circle"></i>
                        </a>
                        <label>https://8x8.vc/external_api.js<?= dgettext("messages-MeetingJitsi", "or") ?> https://meet.jit.si/external_api.js</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">sApiKey</label>
                    <!--  Current Value -->
                    <div class="col-sm-4">
                        <input type="text" size="40" maxlength="255" name="apiKey" id="apiKey"
                               value="<?= $apiKey ?>" class= "form-control form-control-sm">
                    </div>
                    <div class="col-sm-4">
                        <a data-toggle="popover"
                           title="" 
                           data-content="<?= dgettext("messages-MeetingJitsi", "The api Key") ?> : https://jaas.8x8.vc/#/" 
                           target="_blank"
                           class="blue"
                           data-original-title="<?=  dgettext("messages-MeetingJitsi", "Definition") ?>">
                            <i class="far  fa-question-circle"></i>
                        </a>
                        <label><?= dgettext("messages-MeetingJitsi", "You can find one on the website") ?> : https://jaas.8x8.vc/#/</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary" id="SaveSettings">
                    <i class="fas fa-check"></i> <?= dgettext("messages-MeetingJitsi","Save Changes") ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/Plugins/MeetingJitsi/skin/js/settings.js"></script>

