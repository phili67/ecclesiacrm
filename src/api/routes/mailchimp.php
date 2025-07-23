<?php

/* Copyright Philippe Logel not MIT */
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\MailchimpController;

$app->group('/mailchimp', function (RouteCollectorProxy $group) {

    /*
    * @! Search in the list field : *, family name, group, etc ...
    * #! param: ref->string :: query
    */
    $group->get('/search/{query}', MailchimpController::class . ':searchList' );
    /*
    * @! get one list info (['MailChimpList' => $list,'MailChimpCampaign' => $campaign,'membersCount' => count($mailchimp->getListMembersFromListId($args['listID']))])
    * #! param: ref->int :: listID
    *
    */
    $group->get('/list/{listID}', MailchimpController::class . ':oneList' );
    /*
    * @! get all lists ['MailChimpLists' => $lists,'MailChimpCampaigns' => $campaigns, 'firstLoaded' => !$isLoaded, 'isActive' => $isActive]
    * #! param: ref->int :: listID
    */
    $group->get('/lists', MailchimpController::class . ':lists' );
    /*
    * @! get all members list for listID
    * #! param: ref->int :: listID
    */
    $group->get('/listmembers/{listID}', MailchimpController::class . ':listmembers' );
    /*
    * @! create a list
    * #! param: ref->string :: ListTitle
    * #! param: ref->string :: Subject
    * #! param: ref->string :: PermissionReminder
    * #! param: ref->bool :: ArchiveBars
    * #! param: ref->bool :: Status (private | public)
    */
    $group->post('/createlist', MailchimpController::class . ':createList' );
    /*
    * @! modify list by list id
    * #! param: ref->int :: list_id
    * #! param: ref->string :: name
    * #! param: ref->string :: subject
    * #! param: ref->string :: permission_reminder
    */
    $group->post('/modifylist', MailchimpController::class . ':modifyList' );
    /*
    * @! delete all subscribers
    * #! param: ref->int :: list_id
    */
    $group->post('/deleteallsubscribers', MailchimpController::class . ':deleteallsubscribers' );
    /*
    * @! delete list by list ID
    * #! param: ref->int :: list_id
    */
    $group->post('/deletelist', MailchimpController::class . ':deleteList' );


    /*
    * @! remove TagID in the List by list ID
    * #! param: ref->int :: list_id
    * #! param: ref->int :: tag_ID
    */
    $group->post('/list/removeTag', MailchimpController::class . ':removeTag' );
    /*
    * @! remove all tags in list ID by an array of emails
    * #! param: ref->int :: list_id
    * #! param: ref->array :: emails
    */
    $group->post('/list/removeAllTagsForMembers', MailchimpController::class . ':removeAllTagsForMembers' );
    /*
    * @! add a tag to all members by emails array or create a tag (-1) by name for all emails array.
    * #! param: ref->int :: list_id
    * #! param: ref->string :: tag (could be -1 : in this case, you'll create a new tag)
    * #! param: ref->string :: name (in case tag is -1)
    * #! param: ref->array :: emails
    */
    $group->post('/list/addTag', MailchimpController::class . ':addTag' );
    /*
    * @! get all tags for for list by id
    * #! param: ref->int :: list_id
    */
    $group->post('/list/getAllTags', MailchimpController::class . ':getAllTags' );
    /*
    * @! remove tag for all members (emails array) in list Id
    * #! param: ref->int :: list_id
    * #! param: ref->int :: tag
    * #! param: ref->array :: emails
    */
    $group->post('/list/removeTagForMembers', MailchimpController::class . ':removeTagForMembers' );


    /*
    * @! Create a campaign for tagID with subject etc ....
    * #! param: ref->int :: list_id
    * #! param: ref->string :: subject
    * #! param: ref->string :: title
    * #! param: ref->html_code :: htmlBody
    * #! param: ref->string :: tagId
    */
    $group->post('/campaign/actions/create', MailchimpController::class . ':campaignCreate' );
    /*
    * @! Delete campaign by id
    * #! param: ref->int :: campaign_id
    */
    $group->post('/campaign/actions/delete', MailchimpController::class . ':campaignDelete' );
    /*
    * @! Send campaign by id
    * #! param: ref->int :: campaign_id
    */
    $group->post('/campaign/actions/send', MailchimpController::class . ':campaignSend' );
    /*
    * @! Save a campaign
    * #! param: ref->int :: campaign_id
    * #! param: ref->string :: subject
    * #! param: ref->html_code :: content
    * #! param: ref->string :: oldStatus ("save" | "paused" | scheduled)
    */
    $group->post('/campaign/actions/save', MailchimpController::class . ':campaignSave' );
    /*
    * @! Get html contect of a campaign
    * #! param: ref->int :: campaignID
    */
    $group->get('/campaign/{campaignID}/content', MailchimpController::class . ':campaignContent' );


    /*
    * @! update the list status
    * #! param: ref->int :: list_id
    * #! param: ref->string :: status ("save" | "paused" | scheduled)
    * #! param: ref->string :: email
    */
    $group->post('/status', MailchimpController::class . ':statusList' );
    /*
    * @! delete email in the list id
    * #! param: ref->int :: list_id
    * #! param: ref->string :: email (one email)
    */
    $group->post('/suppress', MailchimpController::class . ':suppress' );
    /*
    * @! delete emails in the list id
    * #! param: ref->int :: list_id
    * #! param: ref->array :: array of emails
    */
    $group->post('/suppressMembers', MailchimpController::class . ':suppressMembers' );
    /*
    * @! add all members checked by newsletter checkbox in the CRM
    * #! param: ref->int :: list_id
    */
    $group->post('/addallnewsletterpersons', MailchimpController::class . ':addallnewsletterpersons' );
    /*
    * @! add all persons in the CRM who have a email or work email to list ID
    * #! param: ref->int :: list_id
    */
    $group->post('/addallpersons', MailchimpController::class . ':addallpersons' );
    /*
    * @! add one person ID to list ID
    * #! param: ref->int :: list_id
    * #! param: ref->int :: personID
    */
    $group->post('/addperson', MailchimpController::class . ':addPerson' );
    /*
    * @! add one family ID to list ID
    * #! param: ref->int :: list_id
    * #! param: ref->int :: familyID
    */
    $group->post('/addfamily', MailchimpController::class . ':addFamily' );
    /*
    * @! add all families to list ID
    * #! param: ref->int :: list_id
    */
    $group->post('/addAllFamilies', MailchimpController::class . ':addAllFamilies' );
    /*
    * @! add all group members by ID to list ID
    * #! param: ref->int :: list_id
    * #! param: ref->int :: fgroupID
    */
    $group->post('/addgroup', MailchimpController::class . ':addGroup' );

});



