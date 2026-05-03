<?php
/*******************************************************************************
 *
 *  filename    : Include/Footer.php
 *  last change : 2002-04-22
 *  description : footer that appear on the bottom of all pages
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2002 Phillip Hullquist, Deane Barker, Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\Theme;

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;

?>
</section><!-- /.content -->

</div>
<!-- /.content-wrapper -->
<footer class="main-footer">
    <div class="pull-right">
        <b><?= _('Version') ?></b> <?= SystemService::getDBVersion() ?>
    </div>
    <strong><?= _('Copyright') ?> &copy; 2017-<?= SystemService::getCopyrightDate() ?> <a
            href="https://www.ecclesiacrm.com"
            target="_blank"><?= Bootstrapper::getSoftwareName() ?> <?= SystemService::getDBMainVersion() ?>
        </a>.</strong> <?= _('All rights reserved') ?>.
</footer>

<!-- The Right Sidebar -->
<aside class="control-sidebar <?= Theme::getCurrentRightSideBarTypeColor() ?>" id="right-sidebar">
    <ul class="nav nav-tabs nav-justified" role="tablist">
        <li class="nav-item">
            <a href="#control-sidebar-tasks-tab" data-toggle="tab" aria-expanded="true" class="nav-link active"
               role="tab" title="<?= _('Open Tasks') ?>">
                <i class="fas fa-tasks"></i>
                <span class="sr-only"><?= _('Open Tasks') ?></span>
            </a>
        </li>
        <?php if (SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isMenuOptionsEnabled()): ?>
        <li class="nav-item">
            <a href="#control-sidebar-settings-tab" data-toggle="tab" aria-expanded="false" class="nav-link" role="tab" title="<?= _('System Settings') ?>">
                <i class="fas fa-wrench"></i>
                <span class="sr-only"><?= _('System Settings') ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#control-sidebar-settings-other-tab" data-toggle="tab" aria-expanded="false" class="nav-link"
               role="tab" title="<?= _('Configuration Shortcuts') ?>">
                <i class="fas fa-sliders-h"></i>
                <span class="sr-only"><?= _('Configuration Shortcuts') ?></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <div
        class="tab-content p-3 control-sidebar-content os-host os-theme-light os-host-resize-disabled os-host-scrollbar-horizontal-hidden os-host-overflow os-host-overflow-y os-host-transition">
        <!-- Home tab content -->
        <div id="control-sidebar-settings-other-tab" class="tab-pane">
            <?php
            if (SessionUser::getUser()->isMenuOptionsEnabled()) {
                ?>
                <div class="card card-outline card-secondary shadow-sm mb-3">
                    <div class="card-body py-3">
                        <div class="small text-uppercase text-muted mb-2"><?= _('Configuration Shortcuts') ?></div>
                        <div class="font-weight-bold mb-1"><?= _('Quick access by topic') ?></div>
                        <div class="small text-muted mb-0"><?= _('Access the most common configuration areas by topic.') ?></div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm mb-3">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0"><i class="fas fa-home mr-2"></i><?= _('Family') ?></h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/system/option/manager/famroles">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-cog mr-2 text-muted"></i><?= _('Family Roles') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/propertylist/f">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-tags mr-2 text-muted"></i><?= _('Family Properties') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/people/family/customfield/editor">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-sliders-h mr-2 text-muted"></i><?= _('Edit Custom Family Fields') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="card card-outline card-info shadow-sm mb-3">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0"><i class="fas fa-user mr-2"></i><?= _('Person') ?></h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/system/option/manager/classes">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-layer-group mr-2 text-muted"></i><?= _('Classifications Manager') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/propertylist/p">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-tags mr-2 text-muted"></i><?= _('People Properties') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/people/person/customfield/editor">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-sliders-h mr-2 text-muted"></i><?= _('Edit Custom Person Fields') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="card card-outline card-warning shadow-sm mb-3">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0"><i class="fas fa-users mr-2"></i><?= _('Group') ?></h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (SessionUser::getUser()->isManageGroupsEnabled()) { ?>
                            <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/propertylist/g">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-tags mr-2 text-muted"></i><?= _('Group Properties') ?></span>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                            <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/system/option/manager/grptypes">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-sitemap mr-2 text-muted"></i><?= _('Group Types') ?></span>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                        <?php } ?>
                        <?php if (SystemConfig::getBooleanValue("bEnabledSundaySchool") || SessionUser::getUser()->isManageGroupsEnabled()) { ?>
                            <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/system/option/manager/grptypesSundSchool">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-school mr-2 text-muted"></i><?= _('Sunday School Group Types') ?></span>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm mb-0">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0"><i class="fas fa-cogs mr-2"></i><?= _('Other') ?></h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/propertytypelist">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-project-diagram mr-2 text-muted"></i><?= _('Property Types') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <?php if (SessionUser::getUser()->isFinanceEnabled() && (SystemConfig::getBooleanValue("bEnabledFinance") || SystemConfig::getBooleanValue("bEnabledFundraiser"))) { ?>
                            <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/fundlist">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-donate mr-2 text-muted"></i><?= _('Edit Donation Funds') ?></span>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                        <?php } ?>
                        <?php if (SessionUser::getUser()->isPastoralCareEnabled()) { ?>
                            <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/pastoralcarelist">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-hands-helping mr-2 text-muted"></i><?= _("Pastoral Care Type") ?></span>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                        <?php } ?>
                        <?php if (SystemConfig::getBooleanValue("bEnabledMenuLinks")) { ?>
                            <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/menulinklist">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-link mr-2 text-muted"></i><?= _("Global Custom Menus") ?></span>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="card card-outline card-secondary shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="small text-uppercase text-muted mb-2"><?= _('Configuration Shortcuts') ?></div>
                        <div class="font-weight-bold mb-1"><?= _('Administrator access required') ?></div>
                        <div class="small text-muted mb-0"><?= _('Please contact your admin to change the system settings.') ?></div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <div id="control-sidebar-settings-tab" class="tab-pane">
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="small text-uppercase text-muted mb-2"><?= _('System Settings') ?></div>
                    <div class="font-weight-bold mb-1"><?= _('Administrative tools') ?></div>
                    <div class="small text-muted mb-0"><?= _('Administrative tools and global maintenance actions.') ?></div>
                </div>
            </div>

            <?php
            if (SessionUser::getUser()->isAdmin()) {
                ?>
                <div class="card card-outline card-danger shadow-sm mb-3">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0"><i class="fas fa-wrench mr-2"></i><?= _('Administration') ?></h5>
                    </div>
                    <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/systemsettings">
                        <div class="d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-cogs mr-2 text-danger"></i><?= _('Edit General Settings') ?></span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </div>
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/users">
                        <div class="d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-user-secret mr-2 text-primary"></i><?= _('System Users') ?></span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </div>
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/plugins">
                        <div class="d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-plug mr-2 text-primary"></i><?= _('Plugins') ?></span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </div>
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/system/infos">
                        <div class="d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-info-circle mr-2 text-info"></i><?= _('System Infos') ?></span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </div>
                    </a>
                    </div>
                </div>
                <?php
            }
            ?>

            <?php
            if (SessionUser::getUser()->isAdmin()) {
                ?>
                <div class="card card-outline card-success shadow-sm mb-0">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0"><i class="fas fa-tools mr-2"></i><?= _('Maintenance') ?></h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/backup">
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-database mr-2 text-success"></i><?= _('Backup Database')."/CRM" ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/restore">
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-database mr-2 text-warning"></i><?= _('Restore Database')."/CRM" ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/system/csv/import">
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-upload mr-2 text-warning"></i><?= _('CSV Import') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="<?= SystemURLs::getRootPath() ?>/v2/system/csv/export">
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-download mr-2 text-success"></i><?= _('CSV Export Records') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action" href="/v2/kioskmanager">
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-laptop mr-2 text-primary"></i><?= _('Kiosk Manager') ?></span>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <?php
            } else {
                ?>
                <div class="card card-outline card-secondary shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="small text-uppercase text-muted mb-2"><?= _('System Settings') ?></div>
                        <div class="font-weight-bold mb-1"><?= _('Administrator access required') ?></div>
                        <div class="small text-muted mb-0"><?= _('Please contact your admin to change the system settings.') ?></div>
                    </div>
                </div>
                <?php
            }            
            ?>

        </div>
        <!-- /.tab-pane -->

        <!-- Settings tab content -->
        <div id="control-sidebar-tasks-tab" class="tab-pane active">
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-uppercase text-muted mb-1"><?= _('Open Tasks') ?></div>
                            <div class="font-weight-bold mb-1"><?= _('Pending actions') ?></div>
                            <div class="small text-muted mb-0"><?= _('Follow up on the actions that still need attention.') ?></div>
                        </div>
                        <span class="badge badge-danger badge-pill px-3 py-2"><?= $taskSize ?></span>
                    </div>
                </div>
            </div>
            <?php if (empty($tasks)) { ?>
                <div class="card card-outline card-success shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="font-weight-bold text-success mb-1"><i class="fas fa-check-circle mr-2"></i><?= _('All caught up') ?></div>
                        <div class="small text-muted mb-0"><?= _('There are no open tasks at the moment.') ?></div>
                    </div>
                </div>
            <?php } ?>
            <?php foreach ($tasks as $task) {
                $taskIcon = 'fa-info bg-green';
                if ($task['admin']) {
                    $taskIcon = 'fa-lock bg-yellow-gradient';
                }
                ?>
                <!-- Task item -->
                <div class="list-group shadow-sm mb-2">
                    <a class="list-group-item list-group-item-action" href="<?= $task['link'] ?>" <?= ($task['title'] == _('Register Software')) ? 'id="registerSoftware"' : '' ?>>
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="pr-2">
                                <div class="font-weight-bold mb-1">
                                    <i class="menu-icon fa <?= $taskIcon ?> mr-2"></i><?= $task['title'] ?>
                                </div>
                                <div class="small text-muted" title="<?= htmlspecialchars($task['desc'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= $task['desc'] ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted small mt-1"></i>
                        </div>
                    </a>
                </div>
                <!-- end task item -->
                <?php
            }
            ?>
            <!-- /.control-sidebar-menu -->

        </div>
        <!-- /.tab-pane -->
    </div>
</aside>
<!-- The sidebar's background -->
<!-- This div must placed right after the sidebar for it to work-->
<div class="control-sidebar-bg"></div>
</div>

<!-- Bootstrap 4.x -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/popper/popper.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/util.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/alert.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/button.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/carousel.js"></script>
<!--<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/dropdown.js"></script>-->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/modal.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/scrollspy.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/tab.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/toast.js"></script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/tooltip.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/popover.js"></script>

<!-- AdminLTE App -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/adminlte/adminlte.min.js"></script>

<!-- InputMask -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/inputmask/jquery.inputmask.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/pdfmake.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/vfs_fonts.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/jquery.dataTables.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/jszip.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/dataTables.bootstrap4.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/responsive/dataTables.responsive.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/rowGroup.bootstrap4.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/dataTables.buttons.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.dataTables.min.js"></script>    
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.colVis.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.html5.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.print.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/dataTables.rowGroup.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/rowGroup.dataTables.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/RowGroup/rowGroup.bootstrap4.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/Select/dataTables.select.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/datetime-moment/datetime-moment.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/buttons.bootstrap4.min.js"></script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/external/chartjs/Chart.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/pace/pace.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/select2/select2.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootbox/bootbox.all.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/fastclick/fastclick.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-toggle/bootstrap-toggle.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/i18next/i18next.min.js"></script>
<script
    src="<?= SystemURLs::getRootPath() ?>/locale/js/<?= Bootstrapper::getCurrentLocale()->getLocale() ?>.js"></script>

<?php
// we load the plugin
if (SessionUser::getCurrentPageName() == 'v2/dashboard') {
    $plugins = PluginQuery::create()
        ->filterByActiv(1)
        ->filterByCategory('Dashboard', Criteria::EQUAL )
        ->usePluginUserRoleQuery()
            ->filterByUserId(SessionUser::getId())
            ->filterByDashboardVisible(true)
        ->endUse()
        ->findByActiv(true);

    foreach ($plugins as $plugin) {
        $security = $plugin->getSecurities();
        
        if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
            continue;

        // write the plgin dependencies js code
        $plugin->getDependencies();                
        ?>
            <script src="<?= SystemURLs::getRootPath() ?>/Plugins/<?= $plugin->getName() ?>/locale/js/<?= Bootstrapper::getCurrentLocale()->getLocale() ?>.js"></script>
        <?php
        if (file_exists(__DIR__ . "/../Plugins/" . $plugin->getName() . "/skin/js/")) {
            $directories = MiscUtils::expandDirectories(SystemURLs::getDocumentRoot() . "/Plugins/" . $plugin->getName() . "/skin/js", $plugin->getName());        
        }        
    }        
} elseif (!is_null(SessionUser::getPluginName()) and !empty(SessionUser::getPluginName())) {
    $plugin = PluginQuery::create()
        ->filterByCategory('Dashboard', Criteria::NOT_EQUAL )
        ->filterByName(SessionUser::getPluginName())
        ->findOneByActiv(true);

    // write the plgin dependencies js code
    $plugin->getDependencies();
    ?>
        <script src="<?= SystemURLs::getRootPath() ?>/Plugins/<?= $pluginName ?>/locale/js/<?= Bootstrapper::getCurrentLocale()->getLocale() ?>.js"></script>
    <?php    
}
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-validator/validator.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/IssueReporter.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/Tooltips.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/event/Events.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ShowAge.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/DataTables.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/Footer.js"></script>

<?php if (isset($sGlobalMessage)) {
    // to use this function :
    // put $sGlobalMessage at the top of your template and use : $sGlobalMessageClass too (primary, success, etc ...)
    ?>
    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        $(function() {
            window.CRM.showGlobalMessage("<?= $sGlobalMessage ?>", "<?=$sGlobalMessageClass?>");
        });
    </script>
    <?php
} ?>

<?php include_once('analyticstracking.php'); ?>
</body>
</html>
<?php

// Turn OFF output buffering
ob_end_flush();

// Reset the Global Message
$_SESSION['sGlobalMessage'] = '';

?>
