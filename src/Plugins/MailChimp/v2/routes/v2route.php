<?php

/*******************************************************************************
 *
 *  filename    : .php
 *  last change : 2019-03-23
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

spl_autoload_register(function ($className) {
    $res = str_replace(array('Plugins\\VIEWControllers', '\\'), array(__DIR__.'/../../core/VIEWControllers', '/'), $className) . '.php';
    if (is_file($res)) {
        include_once $res;
    }
});

use Plugins\VIEWControllers\VIEWMailchimpController;

$app->group('/mailchimp', function (RouteCollectorProxy $group) {
    $group->get('', VIEWMailchimpController::class . ':renderMailChimpDashboard');
    $group->get('/dashboard', VIEWMailchimpController::class . ':renderMailChimpDashboard');
    $group->get('/campaign/{campaignId}', VIEWMailchimpController::class . ':renderMailChimpCampaign');
    $group->get('/managelist/{listId}', VIEWMailchimpController::class . ':renderMailChimpManageList');
    $group->get('/duplicateemails', VIEWMailchimpController::class . ':renderMailChimpDuplicateEmails');
    $group->get('/notinmailchimpemailspersons', VIEWMailchimpController::class . ':renderMailChimpNotInMailchimpEmailsPersons');
    $group->get('/notinmailchimpemailsfamilies', VIEWMailchimpController::class . ':renderMailChimpNotInMailchimpEmailsFamilies');
    $group->get('/settings', VIEWMailchimpController::class . ':renderSettings');
    $group->post('/settings', VIEWMailchimpController::class . ':renderSettings');
});
