<?php
/*******************************************************************************
 *
 *  filename    : MainDashboard.php
 *  description : menu that appears after login, shows login attempts
 *
 *  http://www.ecclesiacrm.com/
 *
 *  2020 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRoleQuery;
use EcclesiaCRM\PluginUserRole;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';

$mailchimp = new MailChimpService();

$isActive = $mailchimp->isActive();

$load_Elements = false;

$securityBits = SessionUser::getUser()->allSecuritiesBits();

// we first force every dashboard plugin to have a user settings in function of the default values
$plugins = PluginQuery::create()
    ->filterByActiv(1)
    ->filterByCategory('Dashboard')
    ->find();

foreach ($plugins as $plugin) {
    $plgnRole = PluginUserRoleQuery::create()
        ->filterByPluginId($plugin->getId())
        ->findOneByUserId(SessionUser::getId());

    if (is_null($plgnRole)) {
        $plgnRole = new PluginUserRole();

        $plgnRole->setPluginId($plugin->getId());

        $plgnRole->setUserId(SessionUser::getId());
        $plgnRole->setDashboardColor($plugin->getDashboardDefaultColor());
        $plgnRole->setDashboardOrientation($plugin->getDashboardDefaultOrientation());

        $plgnRole->save();
    }
}

if ($isActive == true) {
    $isLoaded = $mailchimp->isLoaded();

    if (!$isLoaded) {
        $load_Elements = true;
        ?>
        <br/><br/><br/>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="text-center">
                    <h2 class="headline text-primary"><i class="fas fa-spin fa-spinner"></i> <?= _("Loading in progress") ?> ....</h2>
                </div>

                <div class="error-content">
                    <h3>
                        <i class="fas fa-exclamation-triangle text-primary"></i> <?= gettext("Loading datas for the proper functioning of EcclesiaCRM") ?>.
                    </h3>

                    <p>
                    <ul>
                        <li>
                            <?= _("Importing data from Mailchimp") ?>.
                        </li>
                        <li>
                            <?= _("EcclesiaCRM data integrity check") ?>.
                        </li>
                        <li>
                            <?= _("Verification of the technical data of the hosting for the proper functioning of EcclesiaCRM") ?>.
                        </li>
                    </ul>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!$load_Elements) {

    ?>

    <!-- GDPR -->
    <?php
    if (SessionUser::getUser()->isGdrpDpoEnabled() && SystemConfig::getBooleanValue('bGDPR')) {
        if ($numPersons + $numFamilies > 0) {
            ?>
            <div class="alert bg-gradient-gray-dark alert-dismissible " id="Menu_GDRP">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?= _("GDPR") ?> (<?= _("message for the DPO") ?>)</h4>
                <div class="row">
                    <div class="col-sm-1">
                    </div>
                    <div class="col-sm-5">
                        <?php
                        if ($numPersons) {
                            ?>
                            <?php
                            if ($numPersons == 1) {
                                ?>
                                <?= $numPersons . " " . _("person must be deleted from the CRM.") ?>
                            <?php } else { ?>
                                <?= $numPersons . " " . _("persons must be deleted from the CRM.") ?>
                                <?php
                            }
                            ?>
                            <br>
                            <b><?= _("Click the") ?> <a
                                    href="<?= $sRootPath ?>/v2/personlist/GDRP"><?= _("link") ?></a> <?= _("to solve the problem.") ?>
                            </b>
                            <?php
                        } else {
                            ?>
                            <?= _("No Person to remove in the CRM.") ?>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col-sm-5">
                        <?php
                        if ($numFamilies) {
                            ?>
                            <?php
                            if ($numFamilies == 1) {
                                ?>
                                <?= $numFamilies . " " . _("family must be deleted from the CRM.") ?>
                            <?php } else { ?>
                                <?= $numFamilies . " " . _("families must be deleted from the CRM.") ?>
                                <?php
                            }
                            ?>
                            <br>
                            <b><?= _("Click the") ?> <a
                                    href="<?= $sRootPath ?>/v2/familylist/GDRP"><?= _("link") ?></a> <?= _("to solve the problem.") ?>
                            </b>
                            <?php
                        } else {
                            ?>
                            <?= _("No Family to remove in the CRM.") ?>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col-sm-1">
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>

    <?php if ( SessionUser::getUser()->isMainDashboardEnabled() ) { ?>
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-gradient-lime">
                <div class="inner">
                    <h3 id="singleCNT">
                        <?= $dashboardCounts['singleCount'] ?>
                    </h3>
                    <p>
                        <?= _('Single Persons') ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-male"></i>
                </div>
                <div class="small-box-footer">
                    <a href="<?= $sRootPath ?>/v2/people/list/single" style="color:#ffffff">
                        <?= _('View') ?> <?= _("Singles") ?> <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-gradient-blue">
                <div class="inner">
                    <h3 id="realFamilyCNT">
                        <?= $dashboardCounts['familyCount'] ?>
                    </h3>
                    <p>
                        <?= _("Families") ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-male" style="right: 124px"></i><i class="fas fa-female" style="right: 67px"></i><i
                        class="fas fa-child"></i>
                </div>
                <div class="small-box-footer">
                    <a href="<?= $sRootPath ?>/v2/people/list/family" style="color:#ffffff">
                        <?= _('View') ?> <?= _("Familles") ?> <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3 id="peopleStatsDashboard">
                        <?= $dashboardCounts['personCount'] ?>
                    </h3>
                    <p>
                        <?= _('People') ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
                <a href="<?= $sRootPath ?>/v2/people/list/person" class="small-box-footer">
                    <?= _('See All People') ?> <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div><!-- ./col -->
        <?php
        if (SystemConfig::getBooleanValue("bEnabledSundaySchool")) {
            ?>
            <div class="col-lg-2 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="groupStatsSundaySchool">
                            <?= $dashboardCounts['SundaySchoolCount'] ?>
                        </h3>
                        <p>
                            <?= _('Sunday School Classes') ?>
                        </p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <a href="<?= $sRootPath ?>/v2/sundayschool/dashboard" class="small-box-footer">
                        <?= _('More info') ?> <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div><!-- ./col -->
            <?php
        }
        ?>
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3 id="groupsCountDashboard">
                        <?= $dashboardCounts['groupsCount'] ?>
                    </h3>
                    <p>
                        <?= _('Groups') ?>
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="<?= $sRootPath ?>/v2/group/list" class="small-box-footer">
                    <?= _('More info') ?> <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div><!-- ./col -->
    </div><!-- /.row -->
    <?php } ?>

    <!-- we start the plugin parts : center plugins -->
    <div class="float-right">
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="color: red">
                <i class="fas fa-wrench"></i> <?= _("Plugins managements" ) ?></button>
            <div class="dropdown-menu dropdown-menu-right" role="menu" style="">
                <!--
                TODO : plugins remote manage
                <a href="#" class="dropdown-item">Ajouter un nouveau plugin</a>
                <a class="dropdown-divider" style="color: #0c0c0c"></a>
                -->
                <a href="<?= $sRootPath?>/SettingsIndividual.php" class="dropdown-item" id="add-plugin"><?= _("Settings") ?></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><br></div>
    </div>
    <br/>
    <div class="row">
        <section class="col-lg-12 connectedSortable ui-sortable center-plugins" data-name="center">
            <?php
            $plugins = PluginQuery::create()
                ->filterByActiv(1)
                ->filterByCategory('Dashboard')
                ->usePluginUserRoleQuery()
                    ->filterByDashboardOrientation('top')
                    ->filterByUserId(SessionUser::getId())
                    ->filterByDashboardVisible(true)
                ->endUse()
                ->leftJoinPluginUserRole()
                ->addAsColumn('place', \EcclesiaCRM\Map\PluginUserRoleTableMap::COL_PLGN_USR_RL_PLACE)
                ->orderBy('place')
                ->find();

            foreach ($plugins as $plugin) {
                $security = $plugin->getSecurities();

                if ( !(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)) )
                    continue;

                $userPluginStatus = PluginUserRoleQuery::create()
                    ->filterByUserId(SessionUser::getId())
                    ->findOneByPluginId($plugin->getId());

                $is_collapsed = $userPluginStatus->isCollapsed();

                echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php",[
                    'sRootPath'     => $sRootPath,
                    'sRootDocument' => $sRootDocument,
                    'CSPNonce'      => $CSPNonce,
                    'PluginId'      => $plugin->getId(),
                    'Card_collapsed'  => ($is_collapsed?'collapsed-card':''),
                    'Card_body'       => ($is_collapsed?'display: none':'display: block'),
                    'Card_collapsed_button' => ($is_collapsed?'fa-plus':'fa-minus')
                ])
                ?>
            <?php } ?>
        </section>
    </div>

    <!-- we add the left right plugins -->
    <div class="row">

        <section class="col-lg-6 connectedSortable ui-sortable left-plugins" data-name="left">
            <?php
            $plugins = PluginQuery::create()
                ->filterByActiv(1)
                ->filterByCategory('Dashboard')
                ->usePluginUserRoleQuery()
                    ->filterByDashboardOrientation('left')
                    ->filterByDashboardVisible(true)
                    ->filterByUserId(SessionUser::getId())
                ->endUse()
                ->leftJoinPluginUserRole()
                ->addAsColumn('place', \EcclesiaCRM\Map\PluginUserRoleTableMap::COL_PLGN_USR_RL_PLACE)
                ->orderBy('place')
                ->find();

            foreach ($plugins as $plugin) {
                $security = $plugin->getSecurities();

                if ( !(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)) )
                    continue;

                $userPluginStatus = PluginUserRoleQuery::create()
                    ->filterByUserId(SessionUser::getId())
                    ->findOneByPluginId($plugin->getId());

                $is_collapsed = $userPluginStatus->isCollapsed();

                echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php",[
                    'sRootPath'     => $sRootPath,
                    'sRootDocument' => $sRootDocument,
                    'CSPNonce'      => $CSPNonce,
                    'PluginId'      => $plugin->getId(),
                    'Card_collapsed'  => ($is_collapsed?'collapsed-card':''),
                    'Card_body'       => ($is_collapsed?'display: none':'display: block'),
                    'Card_collapsed_button' => ($is_collapsed?'fa-plus':'fa-minus')
                ])
                ?>
            <?php } ?>
        </section>


        <section class="col-lg-6 connectedSortable ui-sortable right-plugins" data-name="right">

            <?php
            $plugins = PluginQuery::create()
                ->filterByActiv(1)
                ->filterByCategory('Dashboard')
                ->usePluginUserRoleQuery()
                    ->filterByDashboardOrientation('right')
                    ->filterByUserId(SessionUser::getId())
                    ->filterByDashboardVisible(true)
                ->endUse()
                ->leftJoinPluginUserRole()
                ->addAsColumn('place', \EcclesiaCRM\Map\PluginUserRoleTableMap::COL_PLGN_USR_RL_PLACE)
                ->orderBy('place')
                ->find();

            foreach ($plugins as $plugin) {
                $security = $plugin->getSecurities();

                if ( !(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)) )
                    continue;

                $userPluginStatus = PluginUserRoleQuery::create()
                    ->filterByUserId(SessionUser::getId())
                    ->findOneByPluginId($plugin->getId());

                $is_collapsed = $userPluginStatus->isCollapsed();

                echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php",[
                    'sRootPath'     => $sRootPath,
                    'sRootDocument' => $sRootDocument,
                    'CSPNonce'      => $CSPNonce,
                    'PluginId'      => $plugin->getId(),
                    'Card_collapsed'  => ($is_collapsed?'collapsed-card':''),
                    'Card_body'       => ($is_collapsed?'display: none':'display: block'),
                    'Card_collapsed_button' => ($is_collapsed?'fa-plus':'fa-minus')
                ])
                ?>
            <?php
            }
            ?>
        </section>

    </div>

<?php
} // end of $load_Elements
?>

<!-- this page specific inline scripts -->
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.attendeesPresences = false;
    window.CRM.timeOut = <?= SystemConfig::getValue("iEventsOnDashboardPresenceTimeOut") * 1000 ?>;
</script>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"  type="text/javascript"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/dashboard.js"></script>


