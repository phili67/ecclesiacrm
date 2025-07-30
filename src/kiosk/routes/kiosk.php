<?php

use Slim\Routing\RouteCollectorProxy;
use EcclesiaCRM\APIControllers\ExternalKioskController;


$app->group('', function (RouteCollectorProxy $group) {

    $group->get('/', ExternalKioskController::class.':getAll');

    $group->get('/heartbeat', ExternalKioskController::class.':heartbeat');

    $group->post('/checkin', ExternalKioskController::class.':checkin');

    $group->post('/uncheckin', ExternalKioskController::class.':uncheckin');

    $group->post('/checkout', ExternalKioskController::class.':checkout');

    $group->post('/uncheckout', ExternalKioskController::class.':uncheckout');

    $group->post('/triggerNotification', ExternalKioskController::class.':triggerNotification');

    $group->get('/activeClassMembers', ExternalKioskController::class.':activeClassMembers');

    $group->get('/activeClassMember/{PersonId}/photo', ExternalKioskController::class.':activeClassMemberPhotos');
});