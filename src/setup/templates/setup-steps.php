<?php

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\dto\StateDropDown;
use EcclesiaCRM\dto\CountryDropDown;

function getSupportedLocales()
{
  $localesFile = file_get_contents(SystemURLs::getDocumentRoot()."/locale/locales.json");
  $locales = json_decode($localesFile, true);
  $res = '<br><select id="sLanguage" name="sLanguage" class="form-control select2" aria-describedby="sChurchNameHelp" style="width:100%"  required>';
  $first = true;
                  
  foreach ($locales as $key => $value) {
    $res .= '<option value="'.$value["locale"].'" '.(($first)?"selected":"").'>'.gettext($key)." (".$value["locale"].")"."</option>\n";
    $first = false;
  }
  
  $res .= '</select><br>';

  return $res;
}


function select_Timezone($selected = '') {
    $OptionsArray = timezone_identifiers_list();
        $select= '<br><select id="sTimeZone" name="sTimeZone" class="form-control select2" aria-describedby="sTimeZoneHelp" style="width:100%" required>';
        while (list ($key, $row) = each ($OptionsArray) ){
            $select .='<option value="'.$row.'"';
            $select .= ($key == $selected ? ' selected' : '');
            $select .= '>'.$row.'</option>';
        }  // endwhile;
        $select.='</select><br>';
  return $select;
}

$URL = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/';

$sPageTitle = 'EcclesiaCRM – Setup';
require '../Include/HeaderNotLoggedIn.php';
?>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM = {
        root: "<?= SystemURLs::getRootPath() ?>",
        prerequisites : [],
        prerequisitesStatus : false //TODO this is not correct we need 2 flags
    };
</script>
<style>
    .wizard .content > .body {
        width: 100%;
        height: auto;
        padding: 15px;
        position: relative;
    }

</style>

<h1 class="text-center">Welcome to Ecclesia<b>CRM</b><?=SystemService::getPackageMainVersion() ?> setup wizard</h1>
<p/><br/>
<form id="setup-form">
    <div id="wizard">
        <h2>System Prerequisite</h2>
        <section>
            <table class="table table-condensed" id="prerequisites"></table>
            <p/>
            <div class="callout callout-warning" id="prerequisites-war">
                This server isn't quite ready for Ecclesia<b>CRM</b><?=SystemService::getPackageMainVersion() ?>. If you know what you are doing.
                <a href="#" onclick="skipCheck()"><b>Click here</b></a>.
            </div>
        </section>

        <h2>Useful Server Info</h2>
        <section>
            <table class="table">
                <tr>
                    <td>Max file upload size</td>
                    <td><?php echo ini_get('upload_max_filesize') ?></td>
                </tr>
                <tr>
                    <td>Max POST size</td>
                    <td><?php echo ini_get('post_max_size') ?></td>
                </tr>
                <tr>
                    <td>PHP Memory Limit</td>
                    <td><?php echo ini_get('memory_limit') ?></td>
                </tr>
            </table>
        </section>

        <h2>Install Location</h2>
        <section>
            <div class="form-group">
                <label for="ROOT_PATH">Root Path</label>
                <input type="text" name="ROOT_PATH" id="ROOT_PATH"
                       value="<?= \EcclesiaCRM\dto\SystemURLs::getRootPath() ?>" class="form-control"
                       aria-describedby="ROOT_PATH_HELP">
                <small id="ROOT_PATH_HELP" class="form-text text-muted">
                    Root path of your Ecclesia<b>CRM</b><?=SystemService::getPackageMainVersion() ?> installation ( THIS MUST BE SET CORRECTLY! )
                    <p/>
                    <i><b>Examples:</b></i>
                    <p/>
                    If you will be accessing from <b>http://www.yourdomain.com/ecclesiacrm</b> then you would
                    enter <b>'/ecclesiacrm'</b> here.
                    <br/>
                    If you will be accessing from <b>http://www.yourdomain.com</b> then you would enter
                    <b>''</b> ... an empty string for a top level installation.

                    <p/>
                    <i><b>NOTE:</b></i>
                    <p/>
                    SHOULD Start end with slash.<br/>
                    SHOULD NOT end with slash.<br/>
                    It is case sensitive.
                    </ul>
                </small>
            </div>
            <div class="form-group">
                <label for="URL">Base URL</label>
                <input type="text" name="URL" id="URL" value="<?= $URL ?>" class="form-control"
                       aria-describedby="URL_HELP" required>
                <small id="URL_HELP" class="form-text text-muted">
                    This is the URL that you prefer most users use when they log in. These are case sensitive.
                </small>
            </div>
        </section>
        <h2>Database Setup</h2>
        <section>
            <div class="form-group">
                <label for="DB_SERVER_NAME">Database Server Name</label>
                <input type="text" name="DB_SERVER_NAME" id="DB_SERVER_NAME" class="form-control"
                       aria-describedby="DB_SERVER_NAME_HELP" required>
                <small id="DB_SERVER_NAME_HELP" class="form-text text-muted"></small>
            </div>
            <div class="form-group">
                <label for="DB_NAME">Database Name</label>
                <input type="text" name="DB_NAME" id="DB_NAME" placeholder="ecclesiacrm" class="form-control"
                       aria-describedby="DB_NAME_HELP" required>
                <small id="DB_NAME_HELP" class="form-text text-muted"></small>
            </div>
            <div class="form-group">
                <label for="DB_USER">Database User</label>
                <input type="text" name="DB_USER" id="DB_USER" placeholder="ecclesiacrm" class="form-control"
                       aria-describedby="DB_USER_HELP" required>
                <small id="DB_USER_HELP" class="form-text text-muted">Must have permissions to create tables</small>
            </div>
            <div class="form-group">
                <label for="DB_PASSWORD">Database Password</label>
                <input type="password" name="DB_PASSWORD" id="DB_PASSWORD" class="form-control"
                       aria-describedby="DB_PASSWORD_HELP" required>
                <small id="DB_PASSWORD_HELP" class="form-text text-muted"></small>
            </div>
            <div class="form-group">
                <label for="DB_PASSWORD2">Confirm Database Password</label>
                <input type="password" name="DB_PASSWORD2" id="DB_PASSWORD2" class="form-control"
                       aria-describedby="DB_PASSWORD2_HELP" required>
                <small id="DB_PASSWORD2_HELP" class="form-text text-muted"></small>
            </div>
        </section>
        <h2>Church Main Infos</h2>
        <section>
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
                <label for="sChurchName">Church Name</label>
                <input type="text" name="sChurchName" id="sChurchName" class="form-control"
                       aria-describedby="sChurchNameHelp" required>
                <small id="sChurchNameHelp" class="form-text text-muted"></small>
            </div>
            <div class="form-group">
                <label for="sChurchAddress">Church Address (1 street Christian)</label>
                <input type="text" name="sChurchAddress" id="sChurchAddress" class="form-control"
                       aria-describedby="sChurchAddressHelp" required>
                <small id="sChurchAddressHelp" class="form-text text-muted"></small>
            </div>

            <div class="form-group">
                <label for="sChurchCity">Church City (New York)</label>
                <input type="text" name="sChurchCity" id="sChurchCity" class="form-control"
                       aria-describedby="sChurchCityHelp" required>
                <small id="sChurchCityHelp" class="form-text text-muted"></small>
            </div>
                        
            <div class="form-group">
                <label for="sChurchZip">Church Zip</label>
                <input type="text" name="sChurchZip" id="sChurchZip" class="form-control"
                       aria-describedby="sChurchZipHelp" required>
                <small id="sChurchZipHelp" class="form-text text-muted"></small>
            </div>

<!--            <div class="form-group">
                <label for="sChurchState">Church State</label>
                    <?php
                        $statesDDF = new StateDropDown();     
                        echo $statesDDF->getDropDown("","sChurchState");
                    ?>
                <small id="sChurchStateHelp" class="form-text text-muted"></small>
            </div>-->

            <div class="form-group">
                <label for="sChurchCountry">Church Country</label>
                    <?php
                      $countriesDDF = new CountryDropDown();     
                      echo $countriesDDF->getDropDown("", "sChurchCountry");
                    ?>
                <small id="sChurchCountryHelp" class="form-text text-muted"></small>
            </div>

            <div class="form-group">
                <label for="sChurchPhone">Church Phone</label>
                <input type="text" name="sChurchPhone" id="sChurchPhone" class="form-control"
                       aria-describedby="sChurchPhoneHelp">
                <small id="sChurchPhoneHelp" class="form-text text-muted"></small>
            </div>

            <div class="form-group">
                <label for="sChurchEmail">Church email</label>
                <input type="email" name="sChurchEmail" id="sChurchEmail" class="form-control"
                       aria-describedby="sChurchEmailHelp" required>
                <small id="sChurchEmailHelp" class="form-text text-muted"></small>
            </div>

            <div class="callout callout-info" id="prerequisites-war">
                This information can be updated late on via <b><i>System Settings</i></b>.
            </div>
        </section>
        <h2>Church Signer | Tax Signer | DPO GDPR</h2>
        <section>
            <div class="form-group">
                <label for="sChurchName">Confirm Signer</label>
                <input type="text" name="sConfirmSigner" id="sConfirmSigner" class="form-control"
                       aria-describedby="sConfirmSignerHelp" required>
                <small id="sConfirmSignerHelp" class="form-text text-muted">
                  Database information confirmation and correction report signer
                </small>
            </div>
            
            <div class="form-group">
                <label for="sReminderSigner">Reminder Signer</label>
                <input type="text" name="sReminderSigner" id="sReminderSigner" class="form-control"
                       aria-describedby="sReminderSignerHelp" required>
                <small id="sReminderSignerHelp" class="form-text text-muted">
                  Pledge Reminder Signer
                </small>
            </div>

            <div class="form-group">
                <label for="sTaxSigner">Tax Signer</label>
                <input type="text" name="sTaxSigner" id="sTaxSigner" class="form-control"
                       aria-describedby="sTaxSignerHelp">
                <small id="sTaxSignerHelp" class="form-text text-muted">
                  Tax Report signer
                </small>
            </div>
            
            <div class="form-group">
                <label for="bGDPR">GDPR Europe</label>
                <select name="bGDPR" id="bGDPR"  class="form-control" aria-describedby="bGDPRHelp">
                  <option value="1">True</option>
                  <option value="0" selected>False</option> 
                </select>
                <small id="bGDPRHelp" class="form-text text-muted">
                    When you would like to activated it or not
                </small>
            </div>

            <div class="form-group">
                <label for="sGdprDpoSigner">DPO Grpd Signer</label>
                <input type="text" name="sGdprDpoSigner" id="sGdprDpoSigner" class="form-control"
                       aria-describedby="sGdprDpoSignerHelp">
                <small id="sGdprDpoSignerHelp" class="form-text text-muted">
                  The DPO administrator for the GDPR.
                </small>
            </div>
            
            <div class="form-group">
                <label for="sGdprDpoSignerEmail">DPO Grpd Signer Email</label>
                <input type="email" name="sGdprDpoSignerEmail" id="sGdprDpoSignerEmail" class="form-control"
                       aria-describedby="sGdprDpoSignerEmailHelp">
                <small id="sGdprDpoSignerHelp" class="form-text text-muted">
                  The DPO administrator for the GDPR email.
                </small>
            </div>
            
            <div class="callout callout-info" id="prerequisites-war">
                This information can be updated late on via <b><i>System Settings</i> too</b>.
            </div>
        </section>
        
        <h2>Social Networks</h2>
        <section>

            <div class="form-group">
                <label for="sChurchName">Church WebSite</label>
                <input type="text" name="sChurchWebSite" id="sChurchWebSite" class="form-control"
                       aria-describedby="sChurchWebSiteHelp">
                <small id="sChurchWebSiteHelp" class="form-text text-muted"></small>
            </div>
            <div class="form-group">
                <label for="sChurchAddress">Church FaceBook</label>
                <input type="text" name="sChurchFB" id="sChurchFB" class="form-control"
                       aria-describedby="sChurchFBHelp">
                <small id="sChurchFBHelp" class="form-text text-muted"></small>
            </div>

            <div class="form-group">
                <label for="sChurchTwitter">Church Twitter</label>
                <input type="text" name="sChurchTwitter" id="sChurchTwitter" class="form-control"
                       aria-describedby="sChurchTwitterHelp">
                <small id="sChurchFBHelp" class="form-text text-muted"></small>
            </div>


            <div class="callout callout-info" id="prerequisites-war">
                This information can be updated late on via <b><i>System Settings</i> too</b>.
            </div>
        </section>
       
        <h2>Mail Server</h2>
        <section>
            <div class="callout callout-info" id="prerequisites-war">
                This information can be updated late on via <b><i>System Settings</i> too</b>.
            </div>
            <div class="form-group">
                <label for="sSMTPHost">SMTP Host</label>
                <input type="text" name="sSMTPHost" id="sSMTPHost" class="form-control"
                       aria-describedby="sSMTPHostHelp">
                <small id="sSMTPHostHelp" class="form-text text-muted">
                    Either a single hostname, you can also specify a different port by using this format: [hostname:port]<br>
                    SMTP Server Address (mail.server.com:25)<br>
                    SMTP Server Address (mail.server.com:587) for an SSL over TLS connexion
                </small>
            </div>
            <div class="form-group">
                <label for="bSMTPAuth">SMTP Auth</label>
                <select name="bSMTPAuth" id="bSMTPAuth"  class="form-control" aria-describedby="bSMTPAuthHelp">
                  <option value="1" selected>True</option> 
                  <option value="0">False</option>
                </select>
                <small id="sSMTPHostHelp" class="form-text text-muted">
                    Does your SMTP server require auththentication (username/password)?
                </small>
            </div>
            <div class="form-group">
                <label for="sSMTPUser">SMTP Host User</label>
                <input type="text" name="sSMTPUser" id="sSMTPUser" class="form-control"
                       aria-describedby="sSMTPUserHelp">
                <small id="sSMTPUserHelp" class="form-text text-muted">
                    SMTP username.
                </small>
            </div>
            <div class="form-group">
                <label for="sSMTPPass">SMTP Host Password</label>
                <input type="password" name="sSMTPPass" id="sSMTPPass" class="form-control"
                       aria-describedby="sSMTPPassHelp">
                <small id="sSMTPPassHelp" class="form-text text-muted">
                    SMTP password.
                </small>
            </div>
            <div class="form-group">
                <label for="iSMTPTimeout">SMTP Host Timeout</label>
                <input type="number" name="iSMTPTimeout" id="iSMTPTimeout" class="form-control"
                       aria-describedby="iSMTPTimeoutHelp" value="10">
                <small id="iSMTPTimeoutHelp" class="form-text text-muted">
                    The SMTP server timeout in seconds.
                </small>
            </div>
            <div class="form-group">
                <label for="sToEmailAddress">Send to Email address</label>
                <input type="email" name="sToEmailAddress" id="sToEmailAddress" class="form-control"
                       aria-describedby="sToEmailAddressHelp" value="">
                <small id="sToEmailAddressHelp" class="form-text text-muted">
                    Default account for receiving a copy of all emails
                </small>
            </div>
            <div class="form-group">
                <label for="bPHPMailerAutoTLS">Mailer Auto TLS</label>
                <select name="bPHPMailerAutoTLS" id="bPHPMailerAutoTLS"  class="form-control" aria-describedby="bPHPMailerAutoTLSHelp">
                  <option value="0" selected>False</option> 
                  <option value="1">True</option>
                </select>
                <small id="bPHPMailerAutoTLSHelp" class="form-text text-muted">
                    Automatically enable SMTP encryption if offered by the relaying server.
                </small>
            </div>
            <div class="form-group">
                <label for="sPHPMailerSMTPSecure">PHPMailer SMTP Secure</label>
                <select name="sPHPMailerSMTPSecure" id="sPHPMailerSMTPSecure"  class="form-control" aria-describedby="sPHPMailerSMTPSecureHelp">
                  <option value="" selected>None</option> 
                  <option value="tls">TLS</option> 
                  <option value="ssl">SSL</option>
                </select>
                <small id="bPHPMailerAutoTLSHelp" class="form-text text-muted">
                    Set the encryption system to use - ssl (deprecated) or tls
                </small>
            </div>
        </section>
        
        <h2>Final infos</h2>
        <section>
            <div class="callout callout-info" id="prerequisites-war">
                To open a connection to EcclesiaCRM, use the information below :
                <ul style="padding-left:20px">
                  <li>login    : <b>admin</b></li>
                  <li>password : <b>changeme</b></li>
            </div>
        </section>
    </div>
</form>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery.steps/jquery.steps.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-validation/jquery.validate.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/select2/select2.full.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/setup.js"></script>

<?php
require '../Include/FooterNotLoggedIn.php';
?>