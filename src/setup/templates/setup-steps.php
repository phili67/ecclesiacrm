<?php

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\dto\StateDropDown;
use EcclesiaCRM\dto\CountryDropDown;


function getSupportedLocales()
{
    $localesFile = file_get_contents(SystemURLs::getDocumentRoot() . "/locale/locales.json");
    $locales = json_decode($localesFile, true);
    $res = '<select id="sLanguage" name="sLanguage" class="form-control" aria-describedby="sChurchNameHelp" required>';
    $first = true;
    foreach ($locales as $key => $value) {
        $res .= '<option value="' . $value["locale"] . '" ' . (($first) ? "selected" : "") . '>' . gettext($key) . " (" . $value["locale"] . ")" . "</option>\n";
        $first = false;
    }
    $res .= '</select>';
    return $res;
}



function select_Timezone($selected = '')
{
    $OptionsArray = timezone_identifiers_list();
    $select = '<select id="sTimeZone" name="sTimeZone" class="form-control" aria-describedby="sTimeZoneHelp" required>';
    foreach ($OptionsArray as $key => $row) {
        $select .= '<option value="' . $row . '"';
        $select .= ($key == $selected ? ' selected' : '');
        $select .= '>' . $row . '</option>';
    }
    $select .= '</select>';
    return $select;
}

$URL = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/';

$sPageTitle = ' CRM – Setup';
require '../Include/HeaderNotLoggedIn.php';
?>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM = {
        root: "<?= SystemURLs::getRootPath() ?>",
        prerequisites: [],
        prerequisitesStatus: false //TODO this is not correct we need 2 flags
    };
</script>

<div class="container-fluid px-3 px-md-4">
    <div class="setup-shell">
        <div class="setup-hero card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <span class="badge badge-primary badge-pill px-3 py-2 mb-3">Guided installation</span>
                        <h1 class="mb-3">Welcome to the <b>CRM</b><?= SystemService::getPackageMainVersion() ?> Setup Wizard</h1>
                        <p class="lead mb-0">Follow the steps below to configure your CRM with a clearer, safer setup flow. Required fields are marked with an asterisk (*), and most settings can still be adjusted later from the application.</p>
                    </div>
                    <div class="col-lg-5 mt-4 mt-lg-0">
                        <div class="row">
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <div class="setup-stat card border-0 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="setup-stat-value">9</div>
                                        <div class="setup-stat-label">Guided steps</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <div class="setup-stat card border-0 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="setup-stat-value">5</div>
                                        <div class="setup-stat-label">Minutes avg.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="setup-stat card border-0 h-100">
                                    <div class="card-body text-center py-3">
                                        <div class="setup-stat-value">1</div>
                                        <div class="setup-stat-label">Final review</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="setup-form" autocomplete="on">
            <div id="wizard">

                <h2>Step 1 of 9: Prerequisite Check</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <p>Let’s make sure your server is ready for <b>CRM</b><?= SystemService::getPackageMainVersion() ?>.<br>
                                <span style="font-size:0.95em;color:#888;">(If something’s missing, don’t panic! You can fix it later.)</span>
                            </p>
                            <table class="table table-condensed" id="prerequisites">
                                <tbody></tbody>
                            </table>
                            <div class="alert alert-warning mt-3" id="prerequisites-war">
                                <b>Heads up:</b> This server is not quite ready for App<b>CRM</b><?= SystemService::getPackageMainVersion() ?>.<br>
                                If you know what you’re doing, <a href="#" id="skipCheck"><b>click here to continue anyway</b></a>.<br>
                                <span style="font-size:0.95em;color:#888;">(You can always come back and fix things later!)</span>
                            </div>
                        </div>
                    </div>
                </section>


                <h2>Step 2 of 9: Server Information</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <p>Here’s a quick look at your server’s limits. <span style="font-size:0.95em;color:#888;">(If you’re not sure, the defaults are usually fine!)</span></p>
                            <table class="table">
                                <tr>
                                    <td><b>Max file upload size</b></td>
                                    <td><?php echo ini_get('upload_max_filesize') ?></td>
                                </tr>
                                <tr>
                                    <td><b>Max POST size</b></td>
                                    <td><?php echo ini_get('post_max_size') ?></td>
                                </tr>
                                <tr>
                                    <td><b>PHP memory limit</b></td>
                                    <td><?php echo ini_get('memory_limit') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </section>


                <h2>Step 3 of 9: Installation Location</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <p>Where will your CRM live? Tell us the path on your server.<br>
                                <span style="font-size:0.95em;color:#888;">(Don’t worry, you can change this later if needed.)</span>
                            </p>
                            <div class="form-group">
                                <label for="ROOT_PATH">Root path <span style="color:red">*</span></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-folder-open"></i></span>
                                    </div>
                                    <input type="text" name="ROOT_PATH" id="ROOT_PATH"
                                        value="<?= \EcclesiaCRM\dto\SystemURLs::getRootPath() ?>" class="form-control"
                                        aria-describedby="ROOT_PATH_HELP" placeholder="E.g.: /ecclesiacrm or leave blank for root">
                                </div>
                                <div id="ROOT_PATH_HELP" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    <ul style="margin-bottom:0;">
                                        <li><b>Example:</b> For <b>http://www.yourdomain.com/ecclesiacrm</b> enter <b>'/ecclesiacrm'</b>.</li>
                                        <li>For <b>http://www.yourdomain.com</b> leave blank.</li>
                                        <li>Should start with a slash, should not end with a slash, case sensitive.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="URL">Base URL <span style="color:red">*</span></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-link"></i></span>
                                    </div>
                                    <input type="text" name="URL" id="URL" value="<?= $URL ?>" class="form-control"
                                        aria-describedby="URL_HELP" required placeholder="E.g.: http://www.yourdomain.com/ecclesiacrm">
                                </div>
                                <div id="URL_HELP" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    This is the preferred URL for users to log in (case sensitive).
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <h2>Step 4 of 9: Database Configuration</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="DB_SERVER_NAME">Database Server Name</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-database"></i></span>
                                    </div>
                                    <input type="text" name="DB_SERVER_NAME" id="DB_SERVER_NAME" class="form-control"
                                        aria-describedby="DB_SERVER_NAME_HELP" required>
                                </div>
                                <div id="DB_SERVER_NAME_HELP" class="form-text text-muted mt-1" style="margin-left:2.2em;">Use localhost over 127.0.0.1</div>
                            </div>
                            <div class="form-group">
                                <label for="DB_SERVER_PORT">MySQL Database Server Port</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-plug"></i></span>
                                    </div>
                                    <input type="text" name="DB_SERVER_PORT" id="DB_SERVER_PORT" class="form-control"
                                        aria-describedby="DB_SERVER_PORT_HELP" required value="3306">
                                </div>
                                <div id="DB_SERVER_PORT_HELP" class="form-text text-muted mt-1" style="margin-left:2.2em;">Default MySQL Port is 3306</div>
                            </div>
                            <div class="form-group">
                                <label for="DB_NAME">Database Name</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-database"></i></span>
                                    </div>
                                    <input type="text" name="DB_NAME" id="DB_NAME" placeholder="ecclesiacrm" class="form-control"
                                        aria-describedby="DB_NAME_HELP" required>
                                </div>
                                <small id="DB_NAME_HELP" class="form-text text-muted"></small>
                            </div>
                            <div class="form-group">
                                <label for="DB_USER">Database User</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    </div>
                                    <input type="text" name="DB_USER" id="DB_USER" placeholder="ecclesiacrm" class="form-control"
                                        aria-describedby="DB_USER_HELP" required>
                                </div>
                                <div id="DB_USER_HELP" class="form-text text-muted mt-1" style="margin-left:2.2em;">Must have permissions to create tables and views</div>
                            </div>
                            <div class="form-group">
                                <label for="DB_PASSWORD">Database Password</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    </div>
                                    <input type="password" name="DB_PASSWORD" id="DB_PASSWORD" class="form-control"
                                        aria-describedby="DB_PASSWORD_HELP" required>
                                </div>
                                <small id="DB_PASSWORD_HELP" class="form-text text-muted"></small>
                            </div>
                            <div class="form-group">
                                <label for="DB_PASSWORD2">Confirm Database Password</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    </div>
                                    <input type="password" name="DB_PASSWORD2" id="DB_PASSWORD2" class="form-control"
                                        aria-describedby="DB_PASSWORD2_HELP" required>
                                </div>
                                <small id="DB_PASSWORD2_HELP" class="form-text text-muted"></small>
                            </div>
                            <div class="alert alert-warning alert-db" id="databaseconnection-war">
                                Check your database connection. Click the link <a href="#" id="dataBaseCheck"><b>here</b></a> to check your connection.
                            </div>
                        </div>
                    </div>
                </section>
                <h2>Step 5 of 9: Main Church Information</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="sLanguage">Language Messages (For the system Settings)</label>
                                <?= getSupportedLocales() ?>
                                <small id="sLanguageHelp" class="form-text text-muted"></small>
                            </div>
                            <div class="form-group">
                                <label for="sTimeZone">Time Zone : WebDAV | CalDAV</label>
                                <?= select_Timezone() ?>
                                <small id="sTimeZoneHelp" class="form-text text-muted">
                                    Time zone fr webdav server : america/new_york<br>
                                    In france : Europe/Paris<br>
                                    You can find the defaut time Zone : <a href="https://en.wikipedia.org/wiki/List_of_tz_database_time_zones">here</a>
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="sEntityName">Church Name</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-church"></i></span>
                                    </div>
                                    <input type="text" name="sEntityName" id="sEntityName" class="form-control"
                                        aria-describedby="sChurchNameHelp" required>
                                </div>
                                <div id="sChurchNameHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>
                            <div class="form-group">
                                <label for="sEntityAddress">Church Address (1 street Christian)</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-map-marker-alt"></i></span>
                                    </div>
                                    <input type="text" name="sEntityAddress" id="sEntityAddress" class="form-control"
                                        aria-describedby="sChurchAddressHelp" required>
                                </div>
                                <div id="sChurchAddressHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>

                            <div class="form-group">
                                <label for="sEntityCity">Church City (New York)</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-city"></i></span>
                                    </div>
                                    <input type="text" name="sEntityCity" id="sEntityCity" class="form-control"
                                        aria-describedby="sChurchCityHelp" required>
                                </div>
                                <div id="sChurchCityHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>

                            <div class="form-group">
                                <label for="sEntityZip">Church Zip</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-mail-bulk"></i></span>
                                    </div>
                                    <input type="text" name="sEntityZip" id="sEntityZip" class="form-control"
                                        aria-describedby="sChurchZipHelp" required>
                                </div>
                                <div id="sChurchZipHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>

                            <!--            <div class="form-group">
                <label for="sEntityState">Church State</label>
                    <?php
                    $statesDDF = new StateDropDown();
                    echo $statesDDF->getDropDown("", "sEntityState");
                    ?>
                <small id="sChurchStateHelp" class="form-text text-muted"></small>
            </div>-->

                            <div class="form-group">
                                <label for="sEntityCountry">Church Country</label>
                                <?php
                                $countriesDDF = new CountryDropDown();
                                echo $countriesDDF->getDropDown("", "sEntityCountry");
                                ?>
                                <div id="sChurchCountryHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>

                            <div class="form-group">
                                <label for="sEntityPhone">Church Phone</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                    </div>
                                    <input type="text" name="sEntityPhone" id="sEntityPhone" class="form-control"
                                        aria-describedby="sChurchPhoneHelp">
                                </div>
                                <div id="sChurchPhoneHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>

                            <div class="form-group">
                                <label for="sEntityEmail">Church email</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="sEntityEmail" id="sEntityEmail" class="form-control"
                                        aria-describedby="sChurchEmailHelp" required>
                                </div>
                                <div id="sChurchEmailHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>

                            <div class="alert alert-info" id="prerequisites-war">
                                This information can be updated late on via <b><i>System Settings</i></b>.
                            </div>
                        </div>
                    </div>
                </section>
                <h2>Step 6 of 9: Signers & GDPR</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="sEntityName">Confirm Signer</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user-check"></i></span>
                                    </div>
                                    <input type="text" name="sConfirmSigner" id="sConfirmSigner" class="form-control"
                                        aria-describedby="sConfirmSignerHelp" required>
                                </div>
                                <div id="sConfirmSignerHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Database information confirmation and correction report signer
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="sReminderSigner">Reminder Signer</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-bell"></i></span>
                                    </div>
                                    <input type="text" name="sReminderSigner" id="sReminderSigner" class="form-control"
                                        aria-describedby="sReminderSignerHelp" required>
                                </div>
                                <div id="sReminderSignerHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Pledge Reminder Signer
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="sTaxSigner">Tax Signer</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-file-invoice-dollar"></i></span>
                                    </div>
                                    <input type="text" name="sTaxSigner" id="sTaxSigner" class="form-control"
                                        aria-describedby="sTaxSignerHelp">
                                </div>
                                <div id="sTaxSignerHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Tax Report signer
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="bGDPR">GDPR Europe</label>
                                <select name="bGDPR" id="bGDPR" class="form-control form-control-sm" aria-describedby="bGDPRHelp">
                                    <option value="1">True</option>
                                    <option value="0" selected>False</option>
                                </select>
                                <div id="bGDPRHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    When you would like to activated it or not
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="sGdprDpoSigner">DPO Grpd Signer</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user-shield"></i></span>
                                    </div>
                                    <input type="text" name="sGdprDpoSigner" id="sGdprDpoSigner" class="form-control"
                                        aria-describedby="sGdprDpoSignerHelp">
                                </div>
                                <div id="sGdprDpoSignerHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    The DPO administrator for the GDPR.
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="sGdprDpoSignerEmail">DPO Grpd Signer Email</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="sGdprDpoSignerEmail" id="sGdprDpoSignerEmail" class="form-control"
                                        aria-describedby="sGdprDpoSignerEmailHelp">
                                </div>
                                <div id="sGdprDpoSignerHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    The DPO administrator for the GDPR email.
                                </div>
                            </div>

                            <div class="alert alert-info" id="prerequisites-war">
                                This information can be updated late on via <b><i>System Settings</i> too</b>.
                            </div>
                        </div>
                    </div>
                </section>

                <h2>Step 7 of 9: Social Networks</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">


                            <div class="form-group">
                                <label for="sEntityWebSite">Church Website</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-globe"></i></span>
                                    </div>
                                    <input type="text" name="sEntityWebSite" id="sEntityWebSite" class="form-control"
                                        aria-describedby="sChurchWebSiteHelp">
                                </div>
                                <div id="sChurchWebSiteHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>
                            <div class="form-group">
                                <label for="sEntityFB">Church Facebook</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                                    </div>
                                    <input type="text" name="sEntityFB" id="sEntityFB" class="form-control"
                                        aria-describedby="sChurchFBHelp">
                                </div>
                                <div id="sChurchFBHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>

                            <div class="form-group">
                                <label for="sEntityTwitter">Church Twitter</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                    </div>
                                    <input type="text" name="sEntityTwitter" id="sEntityTwitter" class="form-control"
                                        aria-describedby="sChurchTwitterHelp">
                                </div>
                                <div id="sChurchTwitterHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;"></div>
                            </div>


                            <div class="alert alert-info" id="prerequisites-war">
                                This information can be updated late on via <b><i>System Settings</i> too</b>.
                            </div>
                        </div>
                    </div>
                </section>

                <h2>Step 8 of 9: Mail Server</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <div class="alert alert-info" id="prerequisites-war">
                                This information can be updated late on via <b><i>System Settings</i> too</b>.
                            </div>
                            <div class="form-group">
                                <label for="sSMTPHost">SMTP Host</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-server"></i></span>
                                    </div>
                                    <input type="text" name="sSMTPHost" id="sSMTPHost" class="form-control"
                                        aria-describedby="sSMTPHostHelp">
                                </div>
                                <div id="sSMTPHostHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Either a single hostname, you can also specify a different port by using this format: [hostname:port]<br>
                                    SMTP Server Address (mail.server.com:25)<br>
                                    SMTP Server Address (mail.server.com:587) for an SSL over TLS connexion
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bSMTPAuth">SMTP Auth</label>
                                <select name="bSMTPAuth" id="bSMTPAuth" class="form-control form-control-sm" aria-describedby="bSMTPAuthHelp">
                                    <option value="1" selected>True</option>
                                    <option value="0">False</option>
                                </select>
                                <div id="sSMTPHostHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Does your SMTP server require auththentication (username/password)?
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sSMTPUser">SMTP Host User</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    </div>
                                    <input type="text" name="sSMTPUser" id="sSMTPUser" class="form-control"
                                        aria-describedby="sSMTPUserHelp">
                                </div>
                                <div id="sSMTPUserHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    SMTP username.
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sSMTPPass">SMTP Host Password</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    </div>
                                    <input type="password" name="sSMTPPass" id="sSMTPPass" class="form-control"
                                        aria-describedby="sSMTPPassHelp">
                                </div>
                                <div id="sSMTPPassHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    SMTP password.
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="iSMTPTimeout">SMTP Host Timeout</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-clock"></i></span>
                                    </div>
                                    <input type="number" name="iSMTPTimeout" id="iSMTPTimeout" class="form-control"
                                        aria-describedby="iSMTPTimeoutHelp" value="10">
                                </div>
                                <div id="iSMTPTimeoutHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    The SMTP server timeout in seconds.
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sToEmailAddress">Send to Email address</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="sToEmailAddress" id="sToEmailAddress" class="form-control"
                                        aria-describedby="sToEmailAddressHelp" value="">
                                </div>
                                <div id="sToEmailAddressHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Default account for receiving a copy of all emails
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bPHPMailerAutoTLS">Mailer Auto TLS</label>
                                <select name="bPHPMailerAutoTLS" id="bPHPMailerAutoTLS" class="form-control form-control-sm" aria-describedby="bPHPMailerAutoTLSHelp">
                                    <option value="0" selected>False</option>
                                    <option value="1">True</option>
                                </select>
                                <div id="bPHPMailerAutoTLSHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Automatically enable SMTP encryption if offered by the relaying server.
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sPHPMailerSMTPSecure">PHPMailer SMTP Secure</label>
                                <select name="sPHPMailerSMTPSecure" id="sPHPMailerSMTPSecure" class="form-control form-control-sm" aria-describedby="sPHPMailerSMTPSecureHelp">
                                    <option value="" selected>None</option>
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                                <div id="bPHPMailerAutoTLSHelp" class="form-text text-muted mt-1" style="margin-left:2.2em;">
                                    Set the encryption system to use - ssl (deprecated) or tls
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <h2>Step 9 of 9: Final Tips</h2>
                <section>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-body">
                            <div class="alert alert-success" id="prerequisites-war">
                                <b>🎉 Awesome, you made it!</b> The installation is almost complete.<br>
                                To log in to App<b>CRM</b><?= SystemService::getPackageMainVersion() ?>, use the following credentials:
                                <ul class="mt-2" style="padding-left:20px">
                                    <li>Username: <b>admin</b></li>
                                    <li>Password: <b>changeme</b></li>
                                </ul>
                                <p class="mt-2">You can change these credentials in the system settings after logging in.<br>
                                    <span style="font-size:0.95em;color:#888;">(And don’t forget to treat yourself to a coffee – you’ve earned it!)</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </form>
        <script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/setup.js"></script>

    </div>
</div>

<?php
require '../Include/FooterNotLoggedIn.php';
?>