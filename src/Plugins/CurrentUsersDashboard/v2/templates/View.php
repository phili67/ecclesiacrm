<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;
use EcclesiaCRM\UserQuery;

use EcclesiaCRM\SessionUser;
use Propel\Runtime\ActiveQuery\Criteria;


// we've to load the model make the plugin to workmv
spl_autoload_register(function ($className) {
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
});

use PluginStore\NewsDashboardQuery;
use Plugins\Service\NewsDashboardService;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

$users = UserQuery::create()
    ->filterByPersonId(SessionUser::getId(), Criteria::NOT_EQUAL)
    ->filterByIsLoggedIn(1)
    ->find();

?>

<div class="card <?= $plugin->getName() ?> <?= $plugin->getPlgnColor() ?> <?= $Card_collapsed ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>">
    <div class="card-header border-0 ui-sortable-handle">
        <h5 class="card-title"><i class="fas fa-users"></i> <?= dgettext("messages-CurrentUsersDashboard","Connected Users") ?></h5>
        <div class="card-tools">
            <button type="button" class="btn btn-default btn-sm" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body" style="padding-top:0px">

        <label><?= $users->count() ?> <?= ($users->count() == 1)?dgettext("messages-CurrentUsersDashboard","user is connected"):dgettext("messages-CurrentUsersDashboard","users are connected") ?></label>

        <ul>
        <?php
            foreach ($users as $user) {
                ?>
                <li><?= $user->getPerson()->getFullName() ?></li>
            <?php
            }
        ?>
        </ul>
    </div>
</div>
