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


                echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php",[
                    'sRootPath'     => $sRootPath,
                    'sRootDocument' => $sRootDocument,
                    'CSPNonce'      => $CSPNonce,
                    'PluginId'      => $plugin->getId()
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

                echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php",[
                    'sRootPath'     => $sRootPath,
                    'sRootDocument' => $sRootDocument,
                    'CSPNonce'      => $CSPNonce,
                    'PluginId'      => $plugin->getId()
                ])
                ?>
            <?php } ?>

            <div class="card">
                <div class="card-header ui-sortable-handle" style="cursor: move;">
                    <h3 class="card-title">
                        <i class="ion ion-clipboard mr-1"></i>
                        To Do List
                    </h3>
                    <div class="card-tools">
                        <ul class="pagination pagination-sm">
                            <li class="page-item"><a href="#" class="page-link">«</a></li>
                            <li class="page-item"><a href="#" class="page-link">1</a></li>
                            <li class="page-item"><a href="#" class="page-link">2</a></li>
                            <li class="page-item"><a href="#" class="page-link">3</a></li>
                            <li class="page-item"><a href="#" class="page-link">»</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <ul class="todo-list ui-sortable" data-widget="todo-list">
                        <li>

<span class="handle ui-sortable-handle">
<i class="fas fa-ellipsis-v"></i>
<i class="fas fa-ellipsis-v"></i>
</span>

                            <div class="icheck-primary d-inline ml-2">
                                <input type="checkbox" value="" name="todo1" id="todoCheck1">
                                <label for="todoCheck1"></label>
                            </div>

                            <span class="text">Design a nice theme</span>

                            <small class="badge badge-danger"><i class="far fa-clock"></i> 2 mins</small>

                            <div class="tools">
                                <i class="fas fa-edit"></i>
                                <i class="fas fa-trash-o"></i>
                            </div>
                        </li>
                        <li class="done">
<span class="handle ui-sortable-handle">
<i class="fas fa-ellipsis-v"></i>
<i class="fas fa-ellipsis-v"></i>
</span>
                            <div class="icheck-primary d-inline ml-2">
                                <input type="checkbox" value="" name="todo2" id="todoCheck2" checked="">
                                <label for="todoCheck2"></label>
                            </div>
                            <span class="text">Make the theme responsive</span>
                            <small class="badge badge-info"><i class="far fa-clock"></i> 4 hours</small>
                            <div class="tools">
                                <i class="fas fa-edit"></i>
                                <i class="fas fa-trash-o"></i>
                            </div>
                        </li>
                        <li>
<span class="handle ui-sortable-handle">
<i class="fas fa-ellipsis-v"></i>
<i class="fas fa-ellipsis-v"></i>
</span>
                            <div class="icheck-primary d-inline ml-2">
                                <input type="checkbox" value="" name="todo3" id="todoCheck3">
                                <label for="todoCheck3"></label>
                            </div>
                            <span class="text">Let theme shine like a star</span>
                            <small class="badge badge-warning"><i class="far fa-clock"></i> 1 day</small>
                            <div class="tools">
                                <i class="fas fa-edit"></i>
                                <i class="fas fa-trash-o"></i>
                            </div>
                        </li>
                        <li>
<span class="handle ui-sortable-handle">
<i class="fas fa-ellipsis-v"></i>
<i class="fas fa-ellipsis-v"></i>
</span>
                            <div class="icheck-primary d-inline ml-2">
                                <input type="checkbox" value="" name="todo4" id="todoCheck4">
                                <label for="todoCheck4"></label>
                            </div>
                            <span class="text">Let theme shine like a star</span>
                            <small class="badge badge-success"><i class="far fa-clock"></i> 3 days</small>
                            <div class="tools">
                                <i class="fas fa-edit"></i>
                                <i class="fas fa-trash-o"></i>
                            </div>
                        </li>
                        <li>
<span class="handle ui-sortable-handle">
<i class="fas fa-ellipsis-v"></i>
<i class="fas fa-ellipsis-v"></i>
</span>
                            <div class="icheck-primary d-inline ml-2">
                                <input type="checkbox" value="" name="todo5" id="todoCheck5">
                                <label for="todoCheck5"></label>
                            </div>
                            <span class="text">Check your messages and notifications</span>
                            <small class="badge badge-primary"><i class="far fa-clock"></i> 1 week</small>
                            <div class="tools">
                                <i class="fas fa-edit"></i>
                                <i class="fas fa-trash-o"></i>
                            </div>
                        </li>
                        <li>
<span class="handle ui-sortable-handle">
<i class="fas fa-ellipsis-v"></i>
<i class="fas fa-ellipsis-v"></i>
</span>
                            <div class="icheck-primary d-inline ml-2">
                                <input type="checkbox" value="" name="todo6" id="todoCheck6">
                                <label for="todoCheck6"></label>
                            </div>
                            <span class="text">Let theme shine like a star</span>
                            <small class="badge badge-secondary"><i class="far fa-clock"></i> 1 month</small>
                            <div class="tools">
                                <i class="fas fa-edit"></i>
                                <i class="fas fa-trash-o"></i>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="card-footer clearfix">
                    <button type="button" class="btn btn-primary float-right"><i class="fas fa-plus"></i> Add item</button>
                </div>
            </div>

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

                echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php",[
                    'sRootPath'     => $sRootPath,
                    'sRootDocument' => $sRootDocument,
                    'CSPNonce'      => $CSPNonce,
                    'PluginId'      => $plugin->getId()
                ])
                ?>
            <?php } ?>
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

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"  type="text/javascript"></script>

<script>
    $('.todo-list').sortable({placeholder:'sort-highlight',handle:'.handle',forcePlaceholderSize:true,zIndex:999999});
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/dashboard.js"></script>


