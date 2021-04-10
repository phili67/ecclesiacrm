<?php

/* Copyright Philippe Logel not MIT */
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\MailchimpController;

$app->group('/mailchimp', function (RouteCollectorProxy $group) {

    $group->get('/search/{query}', MailchimpController::class . ':searchList' );
    $group->get('/list/{listID}', MailchimpController::class . ':oneList' );
    $group->get('/lists', MailchimpController::class . ':lists' );
    $group->get('/listmembers/{listID}', MailchimpController::class . ':listmembers' );
    $group->post('/createlist', MailchimpController::class . ':createList' );
    $group->post('/modifylist', MailchimpController::class . ':modifyList' );
    $group->post('/deleteallsubscribers', MailchimpController::class . ':deleteallsubscribers' );
    $group->post('/deletelist', MailchimpController::class . ':deleteList' );

    $group->post('/list/removeTag', MailchimpController::class . ':removeTag' );
    $group->post('/list/removeAllTagsForMembers', MailchimpController::class . ':removeAllTagsForMembers' );
    $group->post('/list/addTag', MailchimpController::class . ':addTag' );
    $group->post('/list/getAllTags', MailchimpController::class . ':getAllTags' );
    $group->post('/list/removeTagForMembers', MailchimpController::class . ':removeTagForMembers' );

    $group->post('/campaign/actions/create', MailchimpController::class . ':campaignCreate' );
    $group->post('/campaign/actions/delete', MailchimpController::class . ':campaignDelete' );
    $group->post('/campaign/actions/send', MailchimpController::class . ':campaignSend' );
    $group->post('/campaign/actions/save', MailchimpController::class . ':campaignSave' );
    $group->get('/campaign/{campaignID}/content', MailchimpController::class . ':campaignContent' );

    $group->post('/status', MailchimpController::class . ':statusList' );
    $group->post('/suppress', MailchimpController::class . ':suppress' );
    $group->post('/suppressMembers', MailchimpController::class . ':suppressMembers' );
    $group->post('/addallnewsletterpersons', MailchimpController::class . ':addallnewsletterpersons' );
    $group->post('/addallpersons', MailchimpController::class . ':addallpersons' );
    $group->post('/addperson', MailchimpController::class . ':addPerson' );
    $group->post('/addfamily', MailchimpController::class . ':addFamily' );
    $group->post('/addAllFamilies', MailchimpController::class . ':addAllFamilies' );
    $group->post('/addgroup', MailchimpController::class . ':addGroup' );

    $group->post('/testConnection', MailchimpController::class . ':testEmailConnectionMVC' );

});



