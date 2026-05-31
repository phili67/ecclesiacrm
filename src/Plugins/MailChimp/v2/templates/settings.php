<?php
/*******************************************************************************
 *
 *  filename    : settings.php
 *  last change : 2026-05-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

$sRootPath = $sRootPath ?? '';
$sRootDocument = $sRootDocument ?? '';
$sCSPNonce = $sCSPNonce ?? '';
$lang = $lang ?? 'en';
$getSupportURL = $getSupportURL ?? '#';

require $sRootDocument . '/Include/Header.php';

echo '<link rel="stylesheet" href="' . htmlspecialchars((string) $sRootPath, ENT_QUOTES, 'UTF-8') . '/Plugins/MailChimp/skin/css/mailchimp.css">';

$mailChimpApiKey = htmlspecialchars((string) ($apiKey ?? ''), ENT_QUOTES, 'UTF-8');
$mailChimpRequestTimeOut = (int) ($requestTimeOut ?? 30);
$mailChimpExternalCssFont = htmlspecialchars((string) ($externalCssFont ?? ''), ENT_QUOTES, 'UTF-8');
$mailChimpEmailSender = htmlspecialchars((string) ($sMailChimpEmailSender ?? ''), ENT_QUOTES, 'UTF-8');
$mailChimpExtraFont = htmlspecialchars((string) ($sMailChimpExtraFont ?? ''), ENT_QUOTES, 'UTF-8');
$mailChimpIsActive = !empty($isMailChimpActiv);
$mailChimpWithAddressPhone = !empty($bWithAddressPhone);
?>

<div
    class="mailchimp-settings-page"
    id="MailChimpSettingsPage"
    data-mailchimp-active="<?= $mailChimpIsActive ? 1 : 0 ?>"
    data-root-path="<?= htmlspecialchars((string) $sRootPath, ENT_QUOTES, 'UTF-8') ?>"
>
    <section class="hero-panel p-4 p-lg-5 mb-4">
        <div class="hero-grid align-items-center">
            <div>
                <span class="eyebrow"><i class="fas fa-paper-plane"></i><?= dgettext("messages-MailChimp", "MailChimp Settings") ?></span>
                <h1 class="hero-title"><?= dgettext("messages-MailChimp", "Shape your MailChimp workspace") ?></h1>
                <p class="hero-copy mb-0">
                    <?= dgettext("messages-MailChimp", "Manage the API connection, timeout policy, audience enrichment, and typography defaults used by the MailChimp plugin.") ?>
                </p>

                <div class="hero-actions">
                    <span class="status-pill <?= $mailChimpIsActive ? '' : 'is-off' ?>">
                        <i class="fas <?= $mailChimpIsActive ? 'fa-check-circle' : 'fa-plug' ?>"></i>
                        <?= $mailChimpIsActive ? dgettext("messages-MailChimp", "MailChimp is active") : dgettext("messages-MailChimp", "MailChimp is not active") ?>
                    </span>
                    <a href="https://mailchimp.com/<?= $lang ?>/help/about-api-keys/" target="_blank" class="btn btn-dark">
                        <i class="fas fa-key mr-1"></i><?= dgettext("messages-MailChimp", "API key guide") ?>
                    </a>
                    <a href="<?= htmlspecialchars((string) $getSupportURL, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-outline-dark">
                        <i class="fas fa-life-ring mr-1"></i><?= dgettext("messages-MailChimp", "Support") ?>
                    </a>
                </div>

                <div class="meta-grid">
                    <div class="meta-box">
                        <div class="meta-value">6</div>
                        <div class="meta-copy"><?= dgettext("messages-MailChimp", "Configuration fields") ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-value"><?= $mailChimpRequestTimeOut ?></div>
                        <div class="meta-copy"><?= dgettext("messages-MailChimp", "Timeout seconds") ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-value"><?= $mailChimpWithAddressPhone ? dgettext("messages-MailChimp", "On") : dgettext("messages-MailChimp", "Off") ?></div>
                        <div class="meta-copy"><?= dgettext("messages-MailChimp", "Address and phone sync") ?></div>
                    </div>
                </div>
            </div>

            <div class="settings-card soft p-4">
                <div class="section-kicker"><i class="fas fa-sparkles"></i><?= dgettext("messages-MailChimp", "What this page controls") ?></div>
                <div class="section-title"><?= dgettext("messages-MailChimp", "MailChimp plugin defaults") ?></div>
                <p class="meta-copy mb-3">
                    <?= dgettext("messages-MailChimp", "These values are used to connect the plugin, enrich contacts, and inject reusable font assets into generated campaign content.") ?>
                </p>
                <div class="tips-list">
                    <div class="tip-item">
                        <div class="font-weight-bold mb-1"><?= dgettext("messages-MailChimp", "API key") ?></div>
                        <div class="meta-copy mb-0"><?= dgettext("messages-MailChimp", "Required to load lists, members, and campaigns from MailChimp.") ?></div>
                    </div>
                    <div class="tip-item">
                        <div class="font-weight-bold mb-1"><?= dgettext("messages-MailChimp", "Timeout and sender") ?></div>
                        <div class="meta-copy mb-0"><?= dgettext("messages-MailChimp", "Used during remote requests and campaign generation.") ?></div>
                    </div>
                    <div class="tip-item">
                        <div class="font-weight-bold mb-1"><?= dgettext("messages-MailChimp", "Typography fields") ?></div>
                        <div class="meta-copy mb-0"><?= dgettext("messages-MailChimp", "Prepended to campaign HTML to keep a consistent brand style.") ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <form class="settings-card overflow-hidden" id="MailChimpSettingsForm" method="post" action="">
        <div class="card-body p-4 p-lg-5">
            <div class="row">
                <div class="col-xl-8">
                    <section class="settings-card p-4 mb-4">
                        <div class="section-kicker"><i class="fas fa-link"></i><?= dgettext("messages-MailChimp", "Connection") ?></div>
                        <div class="section-title"><?= dgettext("messages-MailChimp", "API access and sending identity") ?></div>
                        <p class="field-help mb-4">
                            <?= dgettext("messages-MailChimp", "Keep the API key private, define a sensible timeout for remote calls, and set the sender email used by the plugin.") ?>
                        </p>

                        <div class="form-group">
                            <label class="field-label" for="apiKey"><?= dgettext("messages-MailChimp", "MailChimp API key") ?></label>
                            <div class="field-shell">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input type="password" class="form-control" maxlength="255" id="apiKey" name="apiKey" value="<?= $mailChimpApiKey ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn btn-light" type="button" id="ToggleApiKey" aria-label="<?= dgettext("messages-MailChimp", "Show or hide API key") ?>">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="field-help mt-2">https://mailchimp.com/<?= $lang ?>/help/about-api-keys/</div>
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="field-label" for="requestTimeOut"><?= dgettext("messages-MailChimp", "Request timeout") ?></label>
                                    <div class="field-shell">
                                        <div class="input-group">
                                            <input type="number" class="form-control" min="1" max="300" id="requestTimeOut" name="requestTimeOut" value="<?= $mailChimpRequestTimeOut ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><?= dgettext("messages-MailChimp", "seconds") ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group mb-0">
                                    <label class="field-label" for="sMailChimpEmailSender"><?= dgettext("messages-MailChimp", "Sender email address") ?></label>
                                    <div class="field-shell">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="far fa-envelope"></i></span>
                                            </div>
                                            <input type="email" class="form-control" maxlength="255" id="sMailChimpEmailSender" name="sMailChimpEmailSender" value="<?= $mailChimpEmailSender ?>" placeholder="newsletter@example.org">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="settings-card p-4 mb-4">
                        <div class="section-kicker"><i class="fas fa-users-cog"></i><?= dgettext("messages-MailChimp", "Audience enrichment") ?></div>
                        <div class="section-title"><?= dgettext("messages-MailChimp", "Contact payload options") ?></div>
                        <p class="field-help mb-4">
                            <?= dgettext("messages-MailChimp", "Enable this option only if your MailChimp audience contains matching merge fields for postal address and phone number.") ?>
                        </p>

                        <div class="switch-panel">
                            <div>
                                <div class="font-weight-bold mb-1"><?= dgettext("messages-MailChimp", "Include address and phone in MailChimp records") ?></div>
                                <div class="field-help mb-0"><?= dgettext("messages-MailChimp", "When enabled, the plugin can push additional family and person information during synchronization.") ?></div>
                            </div>
                            <div class="custom-control custom-switch m-0">
                                <input type="hidden" name="bWithAddressPhone" value="0">
                                <input type="checkbox" class="custom-control-input" id="bWithAddressPhone" name="bWithAddressPhone" value="1" <?= $mailChimpWithAddressPhone ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="bWithAddressPhone"></label>
                            </div>
                        </div>
                    </section>

                    <section class="settings-card p-4">
                        <div class="section-kicker"><i class="fas fa-font"></i><?= dgettext("messages-MailChimp", "Content defaults") ?></div>
                        <div class="section-title"><?= dgettext("messages-MailChimp", "Typography and external assets") ?></div>
                        <p class="field-help mb-4">
                            <?= dgettext("messages-MailChimp", "These values are used when campaign HTML needs shared CSS imports or a custom font stack.") ?>
                        </p>

                        <div class="form-group">
                            <label class="field-label" for="externalCssFont"><?= dgettext("messages-MailChimp", "External CSS font URL") ?></label>
                            <div class="field-shell">
                                <textarea class="form-control" id="externalCssFont" name="externalCssFont" data-autoresize="true" placeholder="https://fonts.googleapis.com/css2?family=..." ><?= $mailChimpExternalCssFont ?></textarea>
                            </div>
                            <div class="field-help mt-2"><?= dgettext("messages-MailChimp", "This value can be injected ahead of generated campaign HTML.") ?></div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="field-label" for="sMailChimpExtraFont"><?= dgettext("messages-MailChimp", "Extra font CSS") ?></label>
                            <div class="field-shell">
                                <textarea class="form-control" id="sMailChimpExtraFont" name="sMailChimpExtraFont" data-autoresize="true" placeholder="font-family: 'Helvetica Neue', Arial, sans-serif;" ><?= $mailChimpExtraFont ?></textarea>
                            </div>
                            <div class="field-help mt-2"><?= dgettext("messages-MailChimp", "Use this field for additional CSS declarations or fallback font stacks.") ?></div>
                        </div>
                    </section>
                </div>

                <div class="col-xl-4 mt-4 mt-xl-0">
                    <section class="settings-card soft p-4 mb-4">
                        <div class="section-kicker"><i class="fas fa-route"></i><?= dgettext("messages-MailChimp", "Implementation map") ?></div>
                        <div class="tips-list">
                            <div class="tip-item">
                                <div class="font-weight-bold mb-1"><?= dgettext("messages-MailChimp", "API key and timeout") ?></div>
                                <div class="meta-copy mb-0"><?= dgettext("messages-MailChimp", "Used by the plugin service when it creates the MailChimp client and fetches remote audiences.") ?></div>
                            </div>
                            <div class="tip-item">
                                <div class="font-weight-bold mb-1"><?= dgettext("messages-MailChimp", "Address and phone flag") ?></div>
                                <div class="meta-copy mb-0"><?= dgettext("messages-MailChimp", "Controls whether additional address and phone fields are sent with contacts.") ?></div>
                            </div>
                            <div class="tip-item">
                                <div class="font-weight-bold mb-1"><?= dgettext("messages-MailChimp", "External CSS and extra fonts") ?></div>
                                <div class="meta-copy mb-0"><?= dgettext("messages-MailChimp", "Prepended to campaign content to keep the same visual language across newsletters.") ?></div>
                            </div>
                        </div>
                    </section>

                    <section class="settings-card p-4">
                        <div class="section-kicker"><i class="fas fa-external-link-alt"></i><?= dgettext("messages-MailChimp", "Resources") ?></div>
                        <div class="d-flex flex-column" style="gap:.75rem;">
                            <a href="https://mailchimp.com/<?= $lang ?>/help/about-api-keys/" target="_blank" class="btn btn-outline-dark text-left">
                                <i class="fas fa-key mr-2"></i><?= dgettext("messages-MailChimp", "Manage API keys") ?>
                            </a>
                            <a href="https://mailchimp.com/<?= $lang ?>/help/getting-started-with-audience-fields/" target="_blank" class="btn btn-outline-dark text-left">
                                <i class="fas fa-address-card mr-2"></i><?= dgettext("messages-MailChimp", "Audience fields guide") ?>
                            </a>
                            <a href="<?= htmlspecialchars((string) $getSupportURL, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-outline-dark text-left">
                                <i class="fas fa-life-ring mr-2"></i><?= dgettext("messages-MailChimp", "Project support") ?>
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="save-bar">
            <div class="save-note save-state" id="MailChimpSaveState" data-dirty="false">
                <i class="fas fa-database mr-1"></i><?= dgettext("messages-MailChimp", "No local changes yet.") ?>
            </div>
            <button class="btn btn-dark btn-lg px-4" id="SaveSettings" type="submit">
                <i class="fas fa-save mr-2"></i><?= dgettext("messages-MailChimp", "Save MailChimp settings") ?>
            </button>
        </div>
    </form>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/Plugins/MailChimp/skin/js/settings.js"></script>