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

<section class="content pt-3">
    <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="mb-0"><i class="fas fa-sliders-h mr-2 text-info"></i><?= dgettext("messages-MeetingJitsi", "Settings") ?> : Jitsi</h3>
                    <small class="text-muted"><?= dgettext("messages-MeetingJitsi", "Configure your meeting provider and API access") ?></small>
                </div>
                <a href="https://jitsi.org/" target="_blank" rel="noopener noreferrer">
                    <img src="<?= $sRootPath ?>/Plugins/MeetingJitsi/skin/jitsi_logo.png" height="28" alt="Jitsi">
                </a>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-body">

                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 col-lg-2 col-form-label">sJitsiDomain</label>
                        <div class="col-sm-9 col-lg-5 mb-2 mb-lg-0">
                            <input type="text" size="40" maxlength="255" name="domain" id="domain" value="<?= $domain ?>" class="form-control form-control-sm" placeholder="meet.jit.si">
                        </div>
                        <div class="col-lg-5 text-muted small">
                            <a data-toggle="popover"
                               title=""
                               data-content="<?= dgettext("messages-MeetingJitsi", "The Jitsi domain name, by default") ?> : meet.jit.si or 8x8.vc"
                               target="_blank"
                               class="text-info"
                               data-original-title="<?=  dgettext("messages-MeetingJitsi", "Definition") ?>">
                                <i class="far fa-question-circle mr-1"></i>
                            </a>
                            8x8.vc <?= dgettext("messages-MeetingJitsi", "or") ?> meet.jit.si
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 col-lg-2 col-form-label">sJitsiDomainScriptPath</label>
                        <div class="col-sm-9 col-lg-5 mb-2 mb-lg-0">
                            <input type="text" size="40" maxlength="255" name="domainscriptpath" id="domainscriptpath" value="<?= $domainscriptpath ?>" class="form-control form-control-sm" placeholder="https://meet.jit.si/external_api.js">
                        </div>
                        <div class="col-lg-5 text-muted small">
                            <a data-toggle="popover"
                               title=""
                               data-content="<?= dgettext("messages-MeetingJitsi", "The path for the script associated with the domain name") ?> : https://meet.jit.si/external_api.js or https://8x8.vc/external_api.js"
                               target="_blank"
                               class="text-info"
                               data-original-title="<?=  dgettext("messages-MeetingJitsi", "Definition") ?>">
                                <i class="far fa-question-circle mr-1"></i>
                            </a>
                            https://8x8.vc/external_api.js <?= dgettext("messages-MeetingJitsi", "or") ?> https://meet.jit.si/external_api.js
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-0">
                        <label class="col-sm-3 col-lg-2 col-form-label">sApiKey</label>
                        <div class="col-sm-9 col-lg-5 mb-2 mb-lg-0">
                            <input type="text" size="40" maxlength="255" name="apiKey" id="apiKey" value="<?= $apiKey ?>" class="form-control form-control-sm" placeholder="vpaas-magic-cookie/...">
                        </div>
                        <div class="col-lg-5 text-muted small">
                            <a data-toggle="popover"
                               title=""
                               data-content="<?= dgettext("messages-MeetingJitsi", "The api Key") ?> : https://jaas.8x8.vc/#/"
                               target="_blank"
                               class="text-info"
                               data-original-title="<?=  dgettext("messages-MeetingJitsi", "Definition") ?>">
                                <i class="far fa-question-circle mr-1"></i>
                            </a>
                            <?= dgettext("messages-MeetingJitsi", "You can find one on the website") ?> : https://jaas.8x8.vc/#/
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-body border-top">
                    <button class="btn btn-info" id="SaveSettings">
                        <i class="fas fa-save mr-1"></i> <?= dgettext("messages-MeetingJitsi", "Save Changes") ?>
                    </button>
                </div>
            </div>
    </div>
</section>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/Plugins/MeetingJitsi/skin/js/settings.js"></script>

