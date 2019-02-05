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

?>
</section><!-- /.content -->

</div>
<!-- /.content-wrapper -->
<footer class="main-footer">
    <div class="pull-right">
        <b><?= gettext('Version') ?></b> <?= SystemService::getDBVersion() ?>
    </div>
    <strong><?= gettext('Copyright') ?> &copy; 2017-<?= SystemService::getCopyrightDate() ?> <a href="https://www.ecclesiacrm.com" target="_blank"><b>Ecclesia</b>CRM<?= SystemService::getDBMainVersion() ?></a>.</strong> <?= gettext('All rights reserved') ?>
    .
</footer>

<!-- The Right Sidebar -->
<aside class="control-sidebar control-sidebar-light">
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
        <li class="active">
            <a href="#control-sidebar-tasks-tab" data-toggle="tab" aria-expanded="true">
                <i class="fa fa-tasks"></i>
            </a>
        </li>
        <li>
            <a href="#control-sidebar-settings-tab" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-wrench"></i>
            </a>
        </li>
        <li>
            <a href="#control-sidebar-settings-other-tab" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-sliders"></i>
            </a>
        </li>

    </ul>
    <div class="tab-content">
        <!-- Home tab content -->
        <div class="tab-pane" id="control-sidebar-settings-other-tab">
      <?php 
        if (SessionUser::getUser()->isMenuOptionsEnabled()) {
      ?>
            <h4 class="control-sidebar-heading"><i class="fa fa-cogs"></i> <?= gettext('Family') ?></h4>
            <ul class="control-sidebar-menu">
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/OptionManager.php?mode=famroles">
                        <i class="fa fa-cog"></i> <?= gettext('Family Roles') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/PropertyList.php?Type=f">
                        <i class="fa fa-cog"></i> <?= gettext('Family Properties') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/FamilyCustomFieldsEditor.php">
                        <i class="fa fa-cog"></i> <?= gettext('Edit Custom Family Fields') ?>
                    </a>
                </li>
            </ul>
            <h4 class="control-sidebar-heading"><i class="fa fa-cogs"></i> <?= gettext('Person') ?></h4>
            <ul class="control-sidebar-menu">
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/OptionManager.php?mode=classes">
                        <i class="fa fa-cog"></i> <?= gettext('Classifications Manager') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/PropertyList.php?Type=p">
                        <i class="fa fa-cog"></i> <?= gettext('People Properties') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/PersonCustomFieldsEditor.php">
                        <i class="fa fa-cog"></i> <?= gettext('Edit Custom Person Fields') ?>
                    </a>
                </li>
            </ul>
            <h4 class="control-sidebar-heading"><i class="fa fa-cogs"></i> <?= gettext('Group') ?></h4>
            <ul class="control-sidebar-menu">
          <?php
             if (SessionUser::getUser()->isManageGroupsEnabled()) {
          ?>
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/PropertyList.php?Type=g">
                        <i class="fa fa-cog"></i> <?= gettext('Group Properties') ?>
                    </a>
                </li>
          <?php
             }

             if (SystemConfig::getBooleanValue("bEnabledSundaySchool") || SessionUser::getUser()->isManageGroupsEnabled() ) {
          ?>
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/PropertyList.php?Type=m">
                        <i class="fa fa-cog"></i> <?= gettext('Sunday School Menu Properties') ?>
                    </a>
                </li>
          <?php
             }

             if (SessionUser::getUser()->isManageGroupsEnabled()) {
          ?>
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/OptionManager.php?mode=grptypes">
                        <i class="fa fa-cog"></i> <?= gettext('Edit Group Types') ?>
                    </a>
                </li>
          <?php
             }
          ?>
            </ul>
            <h4 class="control-sidebar-heading"><i class="fa fa-cogs"></i> <?= gettext('Other') ?></h4>
            <ul class="control-sidebar-menu">
                <li>
                    <a href="<?= SystemURLs::getRootPath() ?>/PropertyTypeList.php">
                        <i class="fa fa-cog"></i> <?= gettext('Property Types') ?>
                    </a>
                </li>
              <?php
                if (SessionUser::getUser()->isCanvasserEnabled()) {
              ?>
                    <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/VolunteerOpportunityEditor.php">
                            <i class="fa fa-cog"></i> <?= gettext('Volunteer Opportunities') ?>
                        </a>
                    </li>
              <?php
                } 
              
                if (SessionUser::getUser()->isFinanceEnabled() && (SystemConfig::getBooleanValue("bEnabledFinance") || SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
              ?>
                    <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/FundList.php">
                            <i class="fa fa-cog"></i> <?= gettext('Edit Donation Funds') ?>
                        </a>
                    </li>
              <?php
                 } 
                
                 if (SessionUser::getUser()->isPastoralCareEnabled()) {
              ?>
                    <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/PastoralCareList.php">
                            <i class="fa fa-cog"></i> <?= gettext("Pastoral Care Type") ?>
                        </a>
                    </li>
              <?php
                }
                
                if (SystemConfig::getBooleanValue("bEnabledMenuLinks")) {
              ?>
                    <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/v2/menulinklist">
                            <i class="fa fa-cog"></i> <?= gettext("Global Custom Menus") ?>
                        </a>
                    </li>
              <?php
                } 
              ?>
            </ul>
         
            <!-- /.control-sidebar-menu -->

      <?php
        } else {
      ?>
           <ul>
               <li><div class="menu-info"><?= gettext('Please contact your admin to change the system settings.') ?></div></li>
           </ul>
      <?php
        }
      ?>
        </div>

        <div id="control-sidebar-settings-tab" class="tab-pane">
            <div><h4 class="control-sidebar-heading"><?= gettext('System Settings') ?></h4>
                <ul class="control-sidebar-menu">
                  <?php 
                  if (SessionUser::getUser()->isAdmin()) {
                  ?>
                    <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/SystemSettings.php">
                            <i class="menu-icon fa fa-gears bg-red"></i>
                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading"><?= gettext('Edit General Settings') ?></h4>
                            </div>
                        </a>
                    </li>
                  <?php
                   } 
                  ?>
                  <?php 
                  if (SessionUser::getUser()->isAdmin()) {
                  ?>
                    <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/UserList.php">
                            <i class="menu-icon fa fa-user-secret bg-gray"></i>
                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading"><?= gettext('System Users') ?></h4>
                            </div>
                        </a>
                    </li>
                  <?php
                  } 
                  ?>
                </ul>
                <hr/>
                <ul class="control-sidebar-menu">
                  <?php 
                    if (SessionUser::getUser()->isAdmin()) {
                  ?>
                        <li>
                            <a href="<?= SystemURLs::getRootPath() ?>/RestoreDatabase.php">
                                <i class="menu-icon fa fa-database bg-yellow-gradient"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading"><?= gettext('Restore Database') ?></h4>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="<?= SystemURLs::getRootPath() ?>/BackupDatabase.php">
                                <i class="menu-icon fa fa-database bg-green"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading"><?= gettext('Backup Database') ?></h4>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="<?= SystemURLs::getRootPath() ?>/CSVImport.php">
                                <i class="menu-icon fa fa-upload bg-yellow-gradient"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading"><?= gettext('CSV Import') ?></h4>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="<?= SystemURLs::getRootPath() ?>/KioskManager.php">
                                <i class="menu-icon fa fa-laptop bg-blue-gradient"></i>
                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading"><?= gettext('Kiosk Manager') ?></h4>
                                </div>
                            </a>
                        </li>
                  <?php
                    } else {
                  ?>
                        <li><div class="menu-info"><?= gettext('Please contact your admin to change the system settings.') ?></div></li>
                  <?php
                    } 
                    
                    if (SessionUser::getUser()->isAdmin()) {
                  ?>
                    <li>
                        <a href="<?= SystemURLs::getRootPath() ?>/CSVExport.php">
                            <i class="menu-icon fa fa-download bg-green"></i>
                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading"><?= gettext('CSV Export Records') ?></h4>
                            </div>
                        </a>
                    </li>
                  <?php
                    } 
                  ?>
                </ul>
            </div>
        </div>
        <!-- /.tab-pane -->

        <!-- Settings tab content -->
        <div class="tab-pane active" id="control-sidebar-tasks-tab">
            <h3 class="control-sidebar-heading"><?= gettext('Open Tasks') ?></h3>
            <?= gettext('You have') ?> &nbsp; <span class="label label-danger"><?= $taskSize ?></span>
            &nbsp; <?= gettext('task(s)') ?>
            <br/><br/>
            <ul class="control-sidebar-menu">
                <?php foreach ($tasks as $task) {
        $taskIcon = 'fa-info bg-green';
        if ($task['admin']) {
            $taskIcon = 'fa-lock bg-yellow-gradient';
        } ?>
                    <!-- Task item -->
                    <li>
                        <a href="<?= $task['link'] ?>">
                            <i class="menu-icon fa fa-fw <?= $taskIcon ?>"></i>
                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading"
                                    title="<?= $task['desc'] ?>"><?= $task['title'] ?></h4>
                            </div>
                        </a>

                    </li>
                    <!-- end task item -->
                    <?php
    } ?>
            </ul>
            <!-- /.control-sidebar-menu -->

        </div>
        <!-- /.tab-pane -->
    </div>
</aside>
<!-- The sidebar's background -->
<!-- This div must placed right after the sidebar for it to work-->
<div class="control-sidebar-bg"></div>
</div>

<!-- Bootstrap 3.3.5 -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap/bootstrap.min.js"></script>

<!-- AdminLTE App -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/adminlte/adminlte.min.js"></script>

<!-- InputMask -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/inputmask/jquery.inputmask.bundle.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/inputmask/inputmask.date.extensions.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/inputmask/inputmask.extensions.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-notify/bootstrap-notify.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/pdfmake.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/vfs_fonts.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/jquery.dataTables.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/jszip.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/responsive/dataTables.responsive.min.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/dataTables.buttons.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.colVis.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.html5.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/TableTools/buttons.print.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/datatables/extensions/Select/dataTables.select.min.js"></script>


<script src="<?= SystemURLs::getRootPath() ?>/skin/external/chartjs/Chart.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/pace/pace.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/select2/select2.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootbox/bootbox.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/fastclick/fastclick.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-toggle/bootstrap-toggle.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/i18next/i18next.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/locale/js/<?= Bootstrapper::getCurrentLocale()->getLocale() ?>.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/bootstrap-validator/validator.min.js"></script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/IssueReporter.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/system/Tooltips.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/event/Events.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ShowAge.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/DataTables.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/Footer.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  $('.sidebar-toggle').pushMenu({expandOnHover:<?= (SessionUser::getUser()->isSidebarExpandOnHoverEnabled())?"true":"false" ?>});
</script>

<?php if (isset($sGlobalMessage)) {
        ?>
    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        $("document").ready(function () {
            showGlobalMessage("<?= $sGlobalMessage ?>", "<?=$sGlobalMessageClass?>");
        });
    </script>
    <?php
    } ?>

<?php  include_once('analyticstracking.php'); ?>
</body>
</html>
<?php

// Turn OFF output buffering
ob_end_flush();

// Reset the Global Message
$_SESSION['sGlobalMessage'] = '';

?>
