<?php

/* Copyright Philippe Logel not MIT */


use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\VIEWControllers\VIEWMailchimpController;

$app->group('/mailchimp', function (RouteCollectorProxy $group) {
    $group->get('', VIEWMailchimpController::class . ':renderMailChimpDashboard');
    $group->get('/dashboard', VIEWMailchimpController::class . ':renderMailChimpDashboard');
    $group->get('/debug', VIEWMailchimpController::class . ':renderMailChimpDebug');
    $group->get('/campaign/{campaignId}', VIEWMailchimpController::class . ':renderMailChimpCampaign');
    $group->get('/managelist/{listId}', VIEWMailchimpController::class . ':renderMailChimpManageList');
    $group->get('/duplicateemails', VIEWMailchimpController::class . ':renderMailChimpDuplicateEmails');
    $group->get('/notinmailchimpemailspersons', VIEWMailchimpController::class . ':renderMailChimpNotInMailchimpEmailsPersons');
    $group->get('/notinmailchimpemailsfamilies', VIEWMailchimpController::class . ':renderMailChimpNotInMailchimpEmailsFamilies');
});
