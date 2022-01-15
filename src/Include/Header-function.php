<?php
/*******************************************************************************
 *
 *  filename    : Include/Header-functions.php
 *  website     : http://www.ecclesiacrm.com
 *  description : page header used for most pages
 *
 *  Copyright 2001-2004 Phillip Hullquist, Deane Barker, Chris Gebhardt, Michael Wilt
 *  Update 2018 Philippe Logel
 *
 *
 ******************************************************************************/

require_once 'Functions.php';

use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\NotificationService;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Bootstrapper;

use EcclesiaCRM\Theme;


function Header_system_notifications()
{
    if (NotificationService::hasActiveNotifications()) {
        ?>
        <div class="systemNotificationBar">
            <?php
            foreach (NotificationService::getNotifications() as $notification) {
                echo "<a href=\"" . $notification->link . "\">" . $notification->title . "</a>";
            } ?>
        </div>
        <?php
    }
}

function Header_fav_icons ()
{
?>

<link rel="shortcut icon" href="<?= SystemURLs::getRootPath() ?>/Favicons/favicon.ico" type="image/x-icon">
<link rel="icon" href="<?= SystemURLs::getRootPath() ?>/Favicons/favicon.png" type="image/png">
<link rel="icon" sizes="32x32" href="<?= SystemURLs::getRootPath() ?>/Favicons/favicon-32.png" type="image/png">
<link rel="icon" sizes="64x64" href="<?= SystemURLs::getRootPath() ?>/Favicons/favicon-64.png" type="image/png">
<link rel="icon" sizes="96x96" href="<?= SystemURLs::getRootPath() ?>/Favicons/favicon-96.png" type="image/png">
<link rel="icon" sizes="196x196" href="<?= SystemURLs::getRootPath() ?>/Favicons/favicon-196.png" type="image/png">
<link rel="apple-touch-icon" sizes="152x152" href="<?= SystemURLs::getRootPath() ?>/Favicons/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="60x60" href="<?= SystemURLs::getRootPath() ?>/Favicons/apple-touch-icon-60x60.png">
<link rel="apple-touch-icon" sizes="76x76" href="<?= SystemURLs::getRootPath() ?>/Favicons/apple-touch-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="<?= SystemURLs::getRootPath() ?>/Favicons/apple-touch-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="<?= SystemURLs::getRootPath() ?>/Favicons/apple-touch-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="<?= SystemURLs::getRootPath() ?>/Favicons/apple-touch-icon-144x144.png">
<meta name="msapplication-TileImage" content="favicon-144.png">
<meta name="msapplication-TileColor" content="#FFFFFF">

<?php
}

function Header_head_metatag($sPageTitle)
{
    if (empty($sPageTitle)) return;
?>
    <title>EcclesiaCRM: <?= $sPageTitle ?></title>
<?php
}

function Header_modals()
{
    ?>
    <!-- Issue Report Modal -->
    <div id="IssueReportModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
              <div id="submitDiaglogStart">
                  <form name="issueReport">
                      <input type="hidden" name="pageName" value="<?= $_SERVER['SCRIPT_NAME'] ?>"/>
                      <div class="modal-header">
                          <h4 class="modal-title"><?= _('Issue Report!') ?></h4>
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                          <div class="container-fluid">
                              <div class="row">
                                  <div class="col-xl-12">
                                      <label for="issueTitle"><?= _('Enter a Title for your bug / feature report') ?> : </label>
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="col-xl-12">
                                      <input class="bootbox-input bootbox-input-text form-control" type="text" name="issueTitle"  style="min-width: 100%;max-width: 100%;">
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="col-xl-12">
                                      <label for="issueDescription"><?= _('What were you doing when you noticed the bug / feature opportunity?') ?></label>
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="col-xl-12">
                                      <textarea class="form-control" rows="10" name="issueDescription" style="min-width: 100%;max-width: 100%;"></textarea>
                                  </div>
                              </div>
                          </div>
                          <ul>
                              <li><?= _("When you click \"submit,\" an error report will be posted to the EcclesiaCRM GitHub Issue tracker.") ?></li>
                              <li><?= _('Please do not include any confidential information.') ?></li>
                              <li><?= _('Some general information about your system will be submitted along with the request such as Server version and browser headers.') ?></li>
                              <li><?= _('No personally identifiable information will be submitted unless you purposefully include it.') ?></li>
                          </ul>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-primary" id="submitIssue"><?= _('Submit') ?></button>
                      </div>
                  </form>
              </div>
              <div id="submitDiaglogFinish">
                <div class="modal-header">
                    <h4 class="modal-title"><?= _('Issue Report done!') ?></h4>
                    <button type="button" class="close flush-right" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"><h2><?= _("Successfully submitted Issue") ?> <span id="issueSubmitSucces"></span></h2>
                <a href="" target="_blank" id="issueSubmitSuccesLink"><?= _("View Issue on GitHub")." : #" ?> <span id="issueSubmitSuccesLinkText"></span></a>
                <div class="modal-footer">
                          <button type="button" class="btn btn-primary" id="submitIssueDone"><?= _('OK') ?></button>
                </div>
                </div>
              </div>
            </div>

        </div>
    </div>
    <!-- End Issue Report Modal -->

    <?php
}

function Header_body_scripts()
{
    $localeInfo = Bootstrapper::GetCurrentLocale();
?>
    <script nonce="<?= SystemURLs::getCSPNonce() ?>">

        var Allbuttons = [ 'copy', 'pdf', 'colvis', 'print' ];

        window.CRM = {
            root: "<?= SystemURLs::getRootPath() ?>",
            lang: "<?= $localeInfo->getLanguageCode() ?>",
            locale: "<?= $localeInfo->getLocale() ?>",
            shortLocale: "<?= $localeInfo->getShortLocale() ?>",
            currency: "<?= SystemConfig::getValue('sCurrency') ?>",
            maxUploadSize: "<?= SystemService::getMaxUploadFileSize(true) ?>",
            maxUploadSizeBytes: "<?= SystemService::getMaxUploadFileSize(false) ?>",
            datePickerformat:"<?= SystemConfig::getValue('sDatePickerPlaceHolder') ?>",
            timeEnglish:<?= (SystemConfig::getBooleanValue("bTimeEnglish"))?"true":"false" ?>,
            iDashboardPageServiceIntervalTime:"<?= SystemConfig::getValue('iDashboardPageServiceIntervalTime') ?>",
            showTooltip:<?= (SessionUser::getUser()->isShowTooltipEnabled())?"true":"false" ?>,
            showCart:<?= (SessionUser::getUser()->isShowCartEnabled())?"true":"false" ?>,
            sMapProvider:"<?= SystemConfig::getValue('sMapProvider')?>",
            sMapExternalProvider:"<?= SessionUser::getUser()->MapExternalProvider() ?>",
            iGoogleMapKey:"<?= SystemConfig::getValue('sGoogleMapKey')?>",
            sBingMapKey:"<?= SystemConfig::getValue('sBingMapKey')?>",
            iLittleMapZoom:<?= SystemConfig::getValue('iLittleMapZoom')?>,
            sNominatimLink:"<?= SystemConfig::getValue('sNominatimLink')?>",
            iPersonId:<?= SessionUser::getUser()->getPersonId() ?>,
            sChurchName:"<?= SystemConfig::getValue('sChurchName') ?>",
            sLogLevel:<?= SystemConfig::getValue('sLogLevel') ?>,
            sChurchCountry:"<?= SystemConfig::getValue('sChurchCountry') ?>",
            bEDrive:<?= (SessionUser::getUser()->isEDrive())?"true":"false" ?>,
            bThumbnailIconPresence:<?= (SystemConfig::getBooleanValue("bThumbnailIconPresence"))?"true":"false" ?>,
            bPastoralcareStats:<?= (SystemConfig::getBooleanValue("bPastoralcareStats"))?"true":"false" ?>,
            sLightDarkMode: "<?= Theme::LightDarkMode() ?>",
            bDarkMode: <?= Theme::isDarkModeEnabled()?'true':'false' ?>,
            bHtmlSourceEditor: <?= SessionUser::getUser()->isHtmlSourceEditorEnabled()?'true':'false' ?>,
            plugin: {
                dataTable : {
                   "language": {
                        "url": "<?= SystemURLs::getRootPath() ?>/locale/datatables/<?= $localeInfo->getDataTables() ?>.json",
                        buttons: {
                          colvis: "<?= _('Change columns') ?>",
                          print: "<?= _('Print') ?>"
                        }
                    },
                    responsive: true,
                    "dom": 'Bfrtip',
                    "buttons": [ <?= ( (SessionUser::getUser()->isCreateDirectoryEnabled() )?"'copy', ":"" ) . ( (SessionUser::getUser()->isCSVExportEnabled() )?"'csv','excel',":"" ) . ( (SessionUser::getUser()->isCreateDirectoryEnabled() )?"'pdf', 'print', ":"" ) ?> 'colvis'  ],
                }
            },
            PageName:"<?= $_SERVER['REQUEST_URI']?>"
        };
    </script>
    <script src="<?= SystemURLs::getRootPath() ?>/skin/js/CRMJSOM.js"></script>
<?php
}
?>
