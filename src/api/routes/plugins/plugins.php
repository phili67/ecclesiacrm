<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PluginsController;

$app->group('/plugins', function (RouteCollectorProxy $group) {

    /*
     * @! Activate a plugin (admin role)
     * #! param: ref->int :: Id
     */
    $group->post('/activate', PluginsController::class . ':activate' );
    /*
     * @! Deactivate a plugin (admin role)
     * #! param: ref->int :: Id
     */
    $group->post('/deactivate', PluginsController::class . ':deactivate' );
    /*
     * @! Remove a plugin (admin role)
     * #! param: ref->int :: Id
     */
    $group->delete('/', PluginsController::class . ':remove' );
    /*
     * @! Add a plugin (admin role), post $_FILES['pluginFile']
     */
    $group->post('/add', PluginsController::class . ':add' );
    /*
     * @! update/upgrade a plugin (admin role), post $_FILES['pluginFile']
     */
    $group->post('/upgrade', PluginsController::class . ':upgrade' );
    /*
     * @! Check installed plugin versions and local migrations (admin role)
     */
    $group->get('/updates', PluginsController::class . ':availableUpdates');
    /*
     * @! Apply the local migration for an installed plugin (admin role)
     * #! param: ref->int :: id
     * #! param: ref->string :: installedVersion
     * #! param: ref->string :: availableVersion
     */
    $group->post('/upgrade-installed', PluginsController::class . ':upgradeInstalled');
    /*
     * @! Place dashboard items plugins on the dashboard
     * #! param: ref->array :: dashBoardItems
     */
    $group->post('/addDashboardPlaces', PluginsController::class . ':addDashboardPlaces' );
    /*
     * @! Add a dashboard plugin from the dashboard by his name
     * #! param: ref->string :: name
     */
    $group->post('/removeFromDashboard', PluginsController::class . ':removeFromDashboard' );
    /*
     * @! Remove a dashboard plugin from the dashboard by his name
     * #! param: ref->string :: name
     */
    $group->post('/collapseFromDashboard', PluginsController::class . ':collapseFromDashboard' );

});
