<?php

use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRoleQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;

// we've to load the model make the plugin to workmv
spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
});

use PluginStore\NewsDashboardQuery;
use Plugins\Service\NewsDashboardService;

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
        ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

$notes = NewsDashboardQuery::create()
    ->find();

$role = PluginUserRoleQuery::create()
    ->filterByPluginId($PluginId)
    ->findOneByUserId(SessionUser::getId());

$role = $role->getRole();

$isAdmin = false;
if ( $role == 'admin' or SessionUser::isAdmin() ) {
    $isAdmin = true;
}
?>

<div class="card <?= $plugin->getName() ?> <?= $Card_collapsed ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>">
    <div class="card-header border-0 ui-sortable-handle">
        <h5 class="card-title"><i class="fas fa-newspaper"></i> <?= dgettext("messages-NewsDashboard","News") ?></h5>
        <div class="card-tools">
            <button type="button" class="btn btn-default btn-sm" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body"  style="<?= $Card_body ?>;padding: .15rem;">
        <?php if ( $notes->count() == 0 ) { ?>
        <ul class="products-list product-list-in-card pl-2 pr-2" id="news-dashboard-list">
            <?= dgettext("messages-NewsDashboard","No news at this time") ?>
        </ul>
        <?php } else {
            ?>
            <ul class="products-list product-list-in-card pl-2 pr-2" id="news-dashboard-list">
        <?php
            foreach ( $notes as $note ) { ?>
                    <li class="item">
                        <div class="product-img">
                            <img src="<?= $sRootPath ?>/Plugins/NewsDashboard/core/images/<?= NewsDashboardService::getImage($note->getType()) ?>" alt="Product Image" class="img-size-50">
                        </div>
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title"><?=  $note->getTitle() ?>
                                <span class="badge badge-warning float-right"><?= dgettext("messages-NewsDashboard","Last modification on"). " : ". OutputUtils::change_date_for_place_holder($note->getDateentered()->format('Y-m-d')) ?></span></a>
                            <span class="product-description">
                                <?= $note->getText() ?>
                                <?php if ($isAdmin) { ?>
                                <div class="row">
                                    <div class="col-md-11">
                                        <button type="button" class="btn btn-danger btn-sm float-right remove-dashboard-news-note" data-id="<?= $note->getId() ?>"><i class="fas fa-trash"></i> <?= dgettext("messages-NewsDashboard","Remove") ?></button>
                                        <button type="button" class="btn btn-primary btn-sm float-right edit-dashboard-news-note" data-id="<?= $note->getId() ?>" style="margin-right: 12px"><i class="fas fa-edit"></i> <?= dgettext("messages-NewsDashboard","Edit") ?></button>
                                    </div>
                                </div>
                                <?php } ?>
                            </span>
                        </div>
                    </li>
        <?php
            }
            ?>
            </ul>
        <?php
        } ?>
    </div>
    <?php if ($isAdmin) { ?>
        <div class="card-footer clearfix">
            <button type="button" class="btn btn-primary float-right" id="add-dashboard-news-note"><i class="fas fa-plus"></i> <?= dgettext("messages-NewsDashboard","Add News") ?></button>
        </div>
    <?php } ?>
</div>

<script nonce="<?= $CSPNonce ?>">
    $(document).ready(function () {
        window.CRM.newsDashboardIsAdmin = <?= $isAdmin?'true':'false' ?>;
        window.CRM.userID = <?= SessionUser::getId() ?>;
    });
</script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script src="<?= $sRootPath ?>/Plugins/NewsDashboard/skin/NewsDashboard.js"></script>
