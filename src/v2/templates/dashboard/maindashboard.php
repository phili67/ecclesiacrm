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
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';
?>

<!-- GDPR -->
<?php
if (SessionUser::getUser()->isGdrpDpoEnabled() && SystemConfig::getBooleanValue('bGDPR')) {
    if ($numPersons + $numFamilies > 0) {
?>
        <div class="alert alert-warning alert-dismissible" id="Menu_GDRP">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> <?= _("GDPR") ?> (<?= _("message for the DPO") ?>)</h4>
            <div class="row">
                <div class="col-sm-6 mb-2 mb-sm-0">
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
                <div class="col-sm-6">
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
            </div>
        </div>
<?php
    }
}
?>

<!-- we start the plugin parts : center plugins -->
<div class="float-right">
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="color: red">
            <i class="fas fa-wrench"></i> <?= _("Customize Page") ?></button>
        <div class="dropdown-menu dropdown-menu-right" role="menu">
            <!--
                TODO : plugins remote manage
                <a href="#" class="dropdown-item">Ajouter un nouveau plugin</a>
                <a class="dropdown-divider" style="color: #0c0c0c"></a>
                -->
            <a href="<?= $sRootPath ?>/v2/users/settings#usersettings-pane-specific" class="dropdown-item" id="add-plugin"><?= _("Settings") ?></a>
        </div>
    </div>
</div>

<hr />

<div class="mb-3 mt-2">
    <h3 class="h5 mb-1"><i class="fas fa-tachometer-alt mr-2"></i><?= _("Dashboard") ?></h3>
    <p class="text-muted small mb-0"><i class="fas fa-info-circle mr-1"></i><?= _("Quick access to your widgets and plugin insights") ?></p>
</div>

<!-- widgets -->
<div class="row">
    <div class="col-12 mb-2">
        <h4 class="h6 text-muted mb-2"><i class="fas fa-th mr-1"></i><?= _("Quick Widgets") ?></h4>
    </div>
    <?php
    $visibleWidgetCount = 0;
    foreach ($widgetPlugins as $plugin) {
        $security = $plugin->getSecurities();

        if (!(SessionUser::getUser() != null and SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
            continue;
        $visibleWidgetCount++;

        echo $this->fetch("../../../Plugins/" . $plugin->getName() . "/v2/templates/View.php", [
            'sRootPath'     => $sRootPath,
            'sRootDocument' => $sRootDocument,
            'CSPNonce'      => $CSPNonce,
            'PluginId'      => $plugin->getId()
        ]);
    }

    if ($visibleWidgetCount === 0) {
        ?>
        <div class="col-12">
            <div class="alert alert-light border mb-0">
                <i class="fas fa-info-circle mr-1"></i><?= _("No widgets are currently available for your profile.") ?>
            </div>
        </div>
    <?php
    }
        ?>
</div><!-- /.row -->
<!-- /.widgets -->

<div class="row mt-3">
    <section class="col-lg-12 connectedSortable ui-sortable top-plugins" data-name="center">
        <h4 class="h6 text-muted mb-2"><i class="fas fa-arrow-up mr-1"></i><?= _("Top Plugins") ?></h4>
        <?php
        foreach ($topPlugins as $plugin) {
            $security = $plugin->getSecurities();

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $is_collapsed = $plugin->getCollapsed();

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
<div class="row mt-2">
    <section class="col-lg-4 connectedSortable ui-sortable left-plugins" data-name="left">
        <h4 class="h6 text-muted mb-2"><i class="fas fa-columns mr-1"></i><?= _("Left Column") ?></h4>
        <?php
        foreach ($leftPlugins as $plugin) {
            $security = $plugin->getSecurities();

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $is_collapsed = $plugin->getCollapsed();

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
        <h4 class="h6 text-muted mb-2"><i class="fas fa-columns mr-1"></i><?= _("Center Column") ?></h4>
        <?php
        foreach ($centerPlugins as $plugin) {
            $security = $plugin->getSecurities();

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $is_collapsed = $plugin->getCollapsed();

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
        <h4 class="h6 text-muted mb-2"><i class="fas fa-columns mr-1"></i><?= _("Right Column") ?></h4>

        <?php
        foreach ($rightPlugins as $plugin) {
            $security = $plugin->getSecurities();

            if (!(SessionUser::getUser()->isSecurityEnableForPlugin($plugin->getName(), $security)))
                continue;

            $is_collapsed = $plugin->getCollapsed();

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
<script nonce="<?= \EcclesiaCRM\dto\SystemURLs::getCSPNonce() ?>">
    window.CRM.attendeesPresences = false;
    window.CRM.timeOut = <?= SystemConfig::getValue("iEventsOnDashboardPresenceTimeOut") * 1000 ?>;
</script>

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="<?= $sRootPath ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>

<script src="<?= $sRootPath ?>/skin/js/dashboard.js"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>