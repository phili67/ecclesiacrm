<?php

/*******************************************************************************
 *
 *  filename    : meeting.php
 *  last change : 2020-07-07
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2020 Philippe Logel all right reserved not MIT licence
 *                This code can't be include in another software
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

// Routes
use Slim\Routing\RouteCollectorProxy;

// in practice you would require the composer loader if it was not already part of your framework or project
spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\APIControllers', '\\'), array(__DIR__.'/../core/APIControllers', '/'), $className) . '.php';
});

use Plugins\APIControllers\ToDoListDashboardController;

$app->group('/todolistdashboard', function (RouteCollectorProxy $group) {

    $group->post('/addList', ToDoListDashboardController::class . ':addList' );
    $group->delete('/removeList', ToDoListDashboardController::class . ':removeList' );
    $group->post('/modifyList', ToDoListDashboardController::class . ':modifyList' );
    $group->post('/listInfo', ToDoListDashboardController::class . ':listInfo' );

    $group->post('/changeList', ToDoListDashboardController::class . ':changeList' );

    $group->post('/addListItem', ToDoListDashboardController::class . ':addListItem' );
    $group->delete('/deleteListItem', ToDoListDashboardController::class . ':deleteListItem' );
    $group->post('/checkListItem', ToDoListDashboardController::class . ':checkListItem' );
    $group->post('/changeListItemsOrder', ToDoListDashboardController::class . ':changeListItemsOrder' );

    $group->post('/ListItemInfo', ToDoListDashboardController::class . ':ListItemInfo' );
    $group->post('/modifyListItem', ToDoListDashboardController::class . ':modifyListItem' );

});
