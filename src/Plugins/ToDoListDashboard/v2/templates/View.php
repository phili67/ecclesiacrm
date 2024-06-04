<?php

use EcclesiaCRM\SessionUser;

use EcclesiaCRM\PluginQuery;

use EcclesiaCRM\Map\PluginUserRoleTableMap;

// we've to load the model make the plugin to workmv
spl_autoload_register(function ($className) {
    $res = str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    if (is_file($res)) {
        include_once $res;
    }
    $res = str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
    if (is_file($res)) {
        include_once $res;
    }
});

use PluginStore\ToDoListDashboardQuery;
use PluginStore\ToDoListDashboardItemQuery;
use Plugins\Service\ToDoListDashboardService;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
    ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

$lists = ToDoListDashboardQuery::create()
    ->filterByUserId(SessionUser::getId())
    ->find();

$defaultList = ToDoListDashboardQuery::create()
    ->filterByUserId(SessionUser::getId())
    ->findOneByVisible(true);
?>

<div class="card card-tabs <?= $plugin->getName() ?> <?= $Card_collapsed ?>" data-name="<?= $plugin->getName() ?>">
    <div class="card-header" style="cursor: move;">
        <h3 class="card-title">
            <i class="ion ion-clipboard mr-1"></i>
            <?= dgettext("messages-ToDoListDashboard","To Do List") ?>
        </h3>
        <div class="card-tools">
            <div class="float-right" style="margin-left:10px;margin-top: -3px;margin-right:-18px">
                <button type="button" class="btn btn-default btn-sm" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse" title="Collapse">
                    <i class="fas <?= $Card_collapsed_button?>"></i>
                </button>
            </div>
            <!--<div class="float-right ">
                <ul class="pagination pagination-sm">
                    <li class="page-item"><a href="#" class="page-link">«</a></li>
                    <li class="page-item"><a href="#" class="page-link">1</a></li>
                    <li class="page-item"><a href="#" class="page-link">2</a></li>
                    <li class="page-item"><a href="#" class="page-link">3</a></li>
                    <li class="page-item"><a href="#" class="page-link">»</a></li>
                </ul>
            </div>-->
            <div class="float-right" style="margin-right:20px;margin-top: -5px">
                <select class="form-control form-control-sm" id="select-to-do-list-dashboard" <?= ($lists->count() == 0?'hidden':'') ?>>
                    <?php foreach ($lists as $list) { ?>
                        <option value="<?= $list->getId() ?>" <?= ($list->isVisible()?'selected':'') ?>><?= $list->getName() ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="float-right" style="margin-right:5px;margin-top: -4px">
                <button type="button" class="btn btn-success btn-sm float-right" id="edit-To-Do-List-Dashboard"
                        data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= dgettext("messages-ToDoListDashboard","Edit Name of Current To Do List") ?>"><i class="fas fa-edit"></i></button>
            </div>
            <div class="float-right" style="margin-right:5px;margin-top: -4px">
                <button type="button" class="btn btn-danger btn-sm float-right" id="remove-To-Do-List-Dashboard"
                        data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= dgettext("messages-ToDoListDashboard","Remove Current To Do List") ?>"><i class="fas fa-trash"></i></button>
            </div>
            <div class="float-right" style="margin-right:5px;margin-top: -4px">
                <button type="button" class="btn btn-primary btn-sm float-right" id="Add-To-Do-List-Dashboard"
                        data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= dgettext("messages-ToDoListDashboard","Add a To Do List") ?>"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>

    <div class="card-body"  style="<?= $Card_body ?>;padding: .05rem;">
        <ul class="todo-list ui-sortable" id="todo-list" data-widget="todo-list">
            <?php
                if ( !is_null($defaultList) ) {
                    $items = ToDoListDashboardItemQuery::create()
                        ->orderByPlace()
                        ->filterByList($defaultList->getId())
                        ->find();

                    if ( $items->count() > 0 ) {
                        foreach ($items as $item) {
                            $date = $item->getDateTime();

                            $periodTime = ToDoListDashboardService::getColorPeriod($date);
                            ?>
                            <li data-id="<?= $item->getId() ?>">
                                <span class="handle ui-sortable-handle">
                                <i class="fas fa-ellipsis-v"></i>
                                <i class="fas fa-ellipsis-v"></i>
                                </span>

                                <div class="icheck-primary d-inline ml-2">
                                    <input type="checkbox" value="" name="todo1" class="todoListItemCheck" data-id="<?= $item->getId() ?>" <?= ($item->isChecked() == 1)?"checked":"" ?> id="todo-<?= $item->getId() ?>">
                                    <label for="todo-<?= $item->getId() ?>"></label>
                                </div>

                                <span class="text"><?= $item->getName() ?></span>

                                <small class="badge badge-<?= $periodTime['color'] ?>"><i class="far fa-clock" alt="toto"></i> <?= $periodTime['period'] ?></small>

                                <div class="tools">
                                    <i class="fas fa-edit edit-todoitemlist" data-id="<?= $item->getId() ?>"></i>
                                    <i class="fas fa-trash remove-todoitemlist" data-id="<?= $item->getId() ?>"></i>
                                </div>
                            </li>
                            <?php
                        }
                    }
                }
            ?>
        </ul>
    </div>

    <div class="card-footer clearfix">
        <button type="button" class="btn btn-primary btn-sm float-right" id="add-to-do-list-item" <?= (is_null($defaultList) > 0 or (!is_null($items) and $items->count() == 8))?'disabled':'' ?>><i class="fas fa-plus"></i> <?= dgettext("messages-ToDoListDashboard","Add item") ?></button>
    </div>
</div>

<script nonce="<?= $CSPNonce ?>">
    $(function() {
        window.CRM.TodoListDashboardId = <?= (!is_null($defaultList)?$defaultList->getId():-1) ?>;
    });
</script>
