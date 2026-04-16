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

use EcclesiaCRM\Utils\MiscUtils;

use EcclesiaCRM\Theme;
use EcclesiaCRM\Utils\GeoUtils;

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
    <div id="IssueReportModal" class="modal fade" role="dialog" aria-labelledby="issueReportModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <!-- Modal content-->
            <div class="modal-content border-0 shadow-lg">
              <div id="submitDiaglogStart">
                  <form name="issueReport">
                      <input type="hidden" name="pageName" value="<?= $_SERVER['SCRIPT_NAME'] ?>"/>
                      <div class="modal-header border-0 pb-0">
                          <div>
                              <h4 class="modal-title d-flex align-items-center" id="issueReportModalTitle">
                                  <i class="fas fa-bug text-primary mr-2"></i><?= _('Issue Report!') ?>
                              </h4>
                              <p class="text-muted mb-0"><?= _('Describe the problem or feature request clearly so it can be reviewed and reproduced quickly.') ?></p>
                          </div>
                          <button type="button" class="close" data-dismiss="modal" aria-label="<?= _('Close') ?>">
                              <span aria-hidden="true">&times;</span>
                          </button>
                      </div>
                      <div class="modal-body">
                          <div class="alert alert-light border d-flex align-items-start mb-4">
                              <i class="fas fa-lightbulb text-warning mt-1 mr-3"></i>
                              <div>
                                  <strong class="d-block mb-1"><?= _('Helpful reports save time') ?></strong>
                                  <span class="text-muted"><?= _('Use a short title, list the action you took, and explain what you expected to happen.') ?></span>
                              </div>
                          </div>
                          <div class="container-fluid px-0">
                              <div class="form-group mb-4">
                                  <label class="issueTitle font-weight-bold d-flex align-items-center">
                                      <i class="fas fa-heading text-primary mr-2"></i><?= _('Enter a Title for your bug / feature report') ?>
                                  </label>
                                  <div class="input-group input-group-sm">
                                      <div class="input-group-prepend">
                                          <span class="input-group-text"><i class="fas fa-pen"></i></span>
                                      </div>
                                      <input class="bootbox-input bootbox-input-text form-control" type="text" name="issueTitle" placeholder="<?= _('Example: Backup download button not visible after completion') ?>">
                                  </div>
                              </div>
                              <div class="form-group mb-0">
                                  <label class="issueDescription font-weight-bold d-flex align-items-center">
                                      <i class="fas fa-align-left text-primary mr-2"></i><?= _('What were you doing when you noticed the bug / feature opportunity?') ?>
                                  </label>
                                  <textarea class="form-control form-control-sm" rows="10" name="issueDescription" placeholder="<?= _('Describe the steps, the visible result, and any message shown on screen.') ?>"></textarea>
                                  <small class="form-text text-muted"><?= _('Tip: mention the page, the action, and the expected result.') ?></small>
                              </div>
                          </div>
                          <div class="card bg-light mt-4 mb-0">
                              <div class="card-body py-3">
                                  <h5 class="mb-3 d-flex align-items-center">
                                      <i class="fas fa-shield-alt text-info mr-2"></i><?= _('Before you submit') ?>
                                  </h5>
                                  <ul class="mb-0 pl-3">
                                      <li><?= _("When you click \"submit,\" an error report will be posted to the CRM GitHub Issue tracker.") ?></li>
                                      <li><?= _('Please do not include any confidential information.') ?></li>
                                      <li><?= _('Some general information about your system will be submitted along with the request such as Server version and browser headers.') ?></li>
                                      <li><?= _('No personally identifiable information will be submitted unless you purposefully include it.') ?></li>
                                  </ul>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                          <button type="button" class="btn btn-light" data-dismiss="modal">
                              <i class="fas fa-times mr-1" aria-hidden="true"></i> <?= _('Cancel') ?>
                          </button>
                          <button type="button" class="btn btn-primary px-4" id="submitIssue"><i class="fa fa-paper-plane mr-1" aria-hidden="true"></i> <?= _('Submit') ?></button>
                      </div>
                  </form>
              </div>
              <div id="submitDiaglogFinish">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title d-flex align-items-center">
                            <i class="fas fa-check-circle text-success mr-2"></i><?= _('Issue Report done!') ?>
                        </h4>
                        <p class="text-muted mb-0"><?= _('Your report has been submitted successfully.') ?></p>
                    </div>
                    <button type="button" class="close flush-right" data-dismiss="modal" aria-label="<?= _('Close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3">
                    <div class="alert alert-success d-flex align-items-start mb-4">
                        <i class="fas fa-check mt-1 mr-3"></i>
                        <div>
                            <h5 class="mb-1"><?= _("Successfully submitted Issue") ?> <span id="issueSubmitSucces"></span></h5>
                            <p class="mb-0 text-muted"><?= _('You can open the created issue directly on GitHub from the link below.') ?></p>
                        </div>
                    </div>
                    <a href="" target="_blank" id="issueSubmitSuccesLink" class="btn btn-outline-primary"><i class="fab fa-github mr-1"></i><?= _("View Issue on GitHub")." : #" ?> <span id="issueSubmitSuccesLinkText"></span></a>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary px-4" id="submitIssueDone"><i class="fas fa-check mr-1"></i><?= _('OK') ?></button>
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
    $pluginInfos = MiscUtils::pluginInformations();

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
            iMapZoom: <?= SystemConfig::getValue("iMapZoom")?>,
            sMapKey: "<?= GeoUtils::getKey() ?>",
            sMapExternalProvider:"<?= SessionUser::getUser()->MapExternalProvider() ?>",
            iGoogleMapKey:"<?= SystemConfig::getValue('sGoogleMapKey')?>",
            iLittleMapZoom:<?= SystemConfig::getValue('iLittleMapZoom')?>,
            sNominatimLink:"<?= SystemConfig::getValue('sNominatimLink')?>",
            iPersonId:<?= SessionUser::getUser()->getPersonId() ?>,
            sEntityName:"<?= SystemConfig::getValue('sEntityName') ?>",
            sLogLevel:<?= SystemConfig::getValue('sLogLevel') ?>,
            sEntityCountry:"<?= SystemConfig::getValue('sEntityCountry') ?>",
            bEDrive:<?= (SessionUser::getUser()->isEDrive())?"true":"false" ?>,
            bThumbnailIconPresence:<?= (SystemConfig::getBooleanValue("bThumbnailIconPresence"))?"true":"false" ?>,
            bPastoralcareStats:<?= (SystemConfig::getBooleanValue("bPastoralcareStats"))?"true":"false" ?>,
            sLightDarkMode: "<?= Theme::LightDarkMode() ?>",
            bDarkMode: <?= Theme::isDarkModeEnabled()?'true':'false' ?>,
            bHtmlSourceEditor: <?= SessionUser::getUser()->isHtmlSourceEditorEnabled()?'true':'false' ?>,
            all_plugins_i18keys: <?= $pluginInfos['pluginNames'] ?>,
            isMailerAvailable: <?= $pluginInfos['isMailerAvalaible'] ?>,
            jwtToken: '<?= SessionUser::getUser()->getJwtTokenForApi() ?>',
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
                    dom: 'Blfrtip',
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
