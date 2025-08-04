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
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRoleQuery;
use EcclesiaCRM\PluginUserRole;
use Propel\Runtime\ActiveQuery\Criteria;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';

// we first force every dashboard plugin to have a user settings in function of the default values
$plugins = PluginQuery::create()
    ->filterByActiv(1)
    ->filterByCategory('Dashboard')
    ->filterByDashboardDefaultOrientation('widget', Criteria::NOT_EQUAL)
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

// we first force every dashboard plugin to have a user settings in function of the default values
$pluginWidgets = PluginQuery::create()
    ->filterByActiv(1)
    ->filterByCategory('Dashboard')
    ->filterByDashboardDefaultOrientation('widget', Criteria::EQUAL)
    ->find();

foreach ($pluginWidgets as $plugin) {
    $plgnRole = PluginUserRoleQuery::create()
        ->filterByPluginId($plugin->getId())
        ->findOneByUserId(SessionUser::getId());

    if (is_null($plgnRole)) {
        $plgnRole = new PluginUserRole();

        $plgnRole->setPluginId($plugin->getId());
        $plgnRole->setUserId(SessionUser::getId());
        $plgnRole->setDashboardColor($plugin->getDashboardDefaultColor());
        $plgnRole->setDashboardOrientation($plugin->getDashboardDefaultOrientation());
        $plgnRole->setDashboardVisible(true);

        $plgnRole->save();
    }
}
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

<?php if (SessionUser::getUser()->isMainDashboardEnabled()) { ?>
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <?php
        if (SystemConfig::getBooleanValue("bEnabledSundaySchool")) {
        ?>
            <div class="col-lg-2 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-gradient-yellow">
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
            <div class="small-box bg-gradient-maroon">
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

<!-- widgets -->
<div class="row">
    <?php
    $widgetCount = $pluginWidgets->count();

    $i = 0;
    foreach ($pluginWidgets as $plugin) {
        $security = $plugin->getSecurities();

        if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
            continue;

        if ($i % 6 == 0 and $i > 0) {
        ?>
                </row>
                <row>
        <?php
        }
        $i++;

        echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php", [
            'sRootPath'     => $sRootPath,
            'sRootDocument' => $sRootDocument,
            'CSPNonce'      => $CSPNonce,
            'PluginId'      => $plugin->getId()
        ]);
    }
        ?>
</div><!-- /.row -->
<!-- /.widgets -->

<!-- we start the plugin parts : center plugins -->
<div class="float-right">
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="color: red">
            <i class="fas fa-wrench"></i> <?= _("Plugins managements") ?></button>
        <div class="dropdown-menu dropdown-menu-right" role="menu" style="">
            <!--
                TODO : plugins remote manage
                <a href="#" class="dropdown-item">Ajouter un nouveau plugin</a>
                <a class="dropdown-divider" style="color: #0c0c0c"></a>
                -->
            <a href="<?= $sRootPath ?>/v2/users/settings" class="dropdown-item" id="add-plugin"><?= _("Settings") ?></a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12"><br></div>
</div>
<br />
<div class="row">
    <section class="col-lg-12 connectedSortable ui-sortable top-plugins" data-name="center">
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

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $userPluginStatus = PluginUserRoleQuery::create()
                ->filterByUserId(SessionUser::getId())
                ->findOneByPluginId($plugin->getId());

            $is_collapsed = $userPluginStatus->isCollapsed();

            echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php", [
                'sRootPath'     => $sRootPath,
                'sRootDocument' => $sRootDocument,
                'CSPNonce'      => $CSPNonce,
                'PluginId'      => $plugin->getId(),
                'Card_collapsed'  => ($is_collapsed ? 'collapsed-card' : ''),
                'Card_body'       => ($is_collapsed ? 'display: none' : 'display: block'),
                'Card_collapsed_button' => ($is_collapsed ? 'fa-plus' : 'fa-minus')
            ])
        ?>
        <?php } ?>
    </section>
</div>

<!-- we add the left right plugins -->
<div class="row">

    <section class="col-lg-4 connectedSortable ui-sortable left-plugins" data-name="left">
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

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $userPluginStatus = PluginUserRoleQuery::create()
                ->filterByUserId(SessionUser::getId())
                ->findOneByPluginId($plugin->getId());

            $is_collapsed = $userPluginStatus->isCollapsed();

            echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php", [
                'sRootPath'     => $sRootPath,
                'sRootDocument' => $sRootDocument,
                'CSPNonce'      => $CSPNonce,
                'PluginId'      => $plugin->getId(),
                'Card_collapsed'  => ($is_collapsed ? 'collapsed-card' : ''),
                'Card_body'       => ($is_collapsed ? 'display: none' : 'display: block'),
                'Card_collapsed_button' => ($is_collapsed ? 'fa-plus' : 'fa-minus')
            ])
        ?>
        <?php } ?>
    </section>

    <!-- the center dashboard plugins -->
    <section class="col-lg-4 connectedSortable ui-sortable center-plugins" data-name="right">

        <?php
        $plugins = PluginQuery::create()
            ->filterByActiv(1)
            ->filterByCategory('Dashboard')
            ->usePluginUserRoleQuery()
            ->filterByDashboardOrientation('center')
            ->filterByUserId(SessionUser::getId())
            ->filterByDashboardVisible(true)
            ->endUse()
            ->leftJoinPluginUserRole()
            ->addAsColumn('place', \EcclesiaCRM\Map\PluginUserRoleTableMap::COL_PLGN_USR_RL_PLACE)
            ->orderBy('place')
            ->find();

        foreach ($plugins as $plugin) {
            $security = $plugin->getSecurities();

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $userPluginStatus = PluginUserRoleQuery::create()
                ->filterByUserId(SessionUser::getId())
                ->findOneByPluginId($plugin->getId());

            $is_collapsed = $userPluginStatus->isCollapsed();

            echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php", [
                'sRootPath'     => $sRootPath,
                'sRootDocument' => $sRootDocument,
                'CSPNonce'      => $CSPNonce,
                'PluginId'      => $plugin->getId(),
                'Card_collapsed'  => ($is_collapsed ? 'collapsed-card' : ''),
                'Card_body'       => ($is_collapsed ? 'display: none' : 'display: block'),
                'Card_collapsed_button' => ($is_collapsed ? 'fa-plus' : 'fa-minus')
            ])
        ?>
        <?php
        }
        ?>
    </section>

    <!-- the right dashboard plugins -->
    <section class="col-lg-4 connectedSortable ui-sortable right-plugins" data-name="right">

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

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $userPluginStatus = PluginUserRoleQuery::create()
                ->filterByUserId(SessionUser::getId())
                ->findOneByPluginId($plugin->getId());

            $is_collapsed = $userPluginStatus->isCollapsed();

            echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php", [
                'sRootPath'     => $sRootPath,
                'sRootDocument' => $sRootDocument,
                'CSPNonce'      => $CSPNonce,
                'PluginId'      => $plugin->getId(),
                'Card_collapsed'  => ($is_collapsed ? 'collapsed-card' : ''),
                'Card_body'       => ($is_collapsed ? 'display: none' : 'display: block'),
                'Card_collapsed_button' => ($is_collapsed ? 'fa-plus' : 'fa-minus')
            ])
        ?>
        <?php
        }
        ?>
    </section>

</div>


<!-- this page specific inline scripts -->
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    window.CRM.attendeesPresences = false;
    window.CRM.timeOut = <?= SystemConfig::getValue("iEventsOnDashboardPresenceTimeOut") * 1000 ?>;
</script>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?= $sRootPath ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>

<script src="<?= $sRootPath ?>/skin/js/dashboard.js"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>