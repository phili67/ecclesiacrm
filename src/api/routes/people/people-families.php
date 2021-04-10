<?php

/* Contributors Philippe Logel */

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PeopleFamilyController;


$app->group('/families', function (RouteCollectorProxy $group) {

/*
 * @! Return family properties for familyID
 * #! param: id->int   :: familyId as id
 */
    $group->post('/familyproperties/{familyID:[0-9]+}', PeopleFamilyController::class . ":postfamilyproperties" );
/*
 * @! Return if mailchimp is activated for family
 * #! param: id->int   :: familyId as id
 * #! param: ref->string :: email as ref
 */
    $group->post('/isMailChimpActive', PeopleFamilyController::class . ":isMailChimpActiveFamily" );
/*
 * @! Return the family as json
 * #! param: id->int   :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}', PeopleFamilyController::class . ":getFamily" );
/*
 * @! Return the family info as json
 * #! param: id->int   :: familyId as id
 */
    $group->post('/info', PeopleFamilyController::class . ":familyInfo" );
/*
 * @! Return the numbers of Anniversaries for MenuEvent
 */
    $group->get('/numbers', PeopleFamilyController::class . ":numbersOfAnniversaries" );
/*
 * @! Returns a list of the families who's name matches the :query parameter
 * #! param: ref->string :: query as ref
 */
    $group->get('/search/{query}', PeopleFamilyController::class . ":searchFamily" );
/*
 * @! Returns a list of the self-registered families
 */
    $group->get('/self-register', PeopleFamilyController::class . ":selfRegisterFamily" );
/*
 * @! Returns a list of the self-verified families
 */
    $group->get('/self-verify', PeopleFamilyController::class . ":selfVerifyFamily" );
/*
 * @! Returns a list of the pending self-verified families
 */
    $group->get('/pending-self-verify', PeopleFamilyController::class . ":pendingSelfVerify" );
/*
 * @! Returns a family string based on the scan string of an MICR reader containing a routing and account number
 * #! param: ref->string :: scanString as ref
 */
    $group->get('/byCheckNumber/{scanString}', PeopleFamilyController::class . ":byCheckNumberScan" );

 /*
 * @! Returns the photo for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}/photo', PeopleFamilyController::class . ":photo" );

 /*
 * @! Returns the thumbnail for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}/thumbnail', PeopleFamilyController::class . ":thumbnail" );

 /*
 * @! Post the photo for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->post('/{familyId:[0-9]+}/photo', PeopleFamilyController::class . ":postFamilyPhoto" );

 /*
 * @! Delete the photo for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->delete('/{familyId:[0-9]+}/photo', PeopleFamilyController::class . ":deleteFamilyPhoto" );

 /*
 * @! Verify the family for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->post('/{familyId:[0-9]+}/verify', PeopleFamilyController::class . ":verifyFamily" );

 /*
 * @! Verify the family for the familyId now
 * #! param: id->int :: familyId as id
 */
    $group->post('/verify/{familyId:[0-9]+}/now', PeopleFamilyController::class . ":verifyFamilyNow" );

/*
 * @! Verify the family for the familyId now
 * #! param: id->int :: family
 */
    $group->post('/verify/url', PeopleFamilyController::class . ':verifyFamilyURL' );

/*
 * @! Update the family status to activated or deactivated with :familyId and :status true/false. Pass true to activate and false to deactivate.
 * #! param: id->int   :: familyId as id
 * #! param: ref->bool :: status as ref
 */
    $group->post('/{familyId:[0-9]+}/activate/{status}', PeopleFamilyController::class . ":familyActivateStatus" );
 /*
 * @! Return the location for the family
 * #! param: id->int :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}/geolocation', PeopleFamilyController::class . ":familyGeolocation" );

 /*
 * @! delete familyField custom field
 * #! param: id->int :: orderID as id
 * #! param: id->int :: field as id
 */
    $group->post('/deletefield', PeopleFamilyController::class . ":deleteFamilyField" );
 /*
 * @! Move up the family custom field
 * #! param: id->int :: orderID as id
 * #! param: id->int :: field as id
 */
    $group->post('/upactionfield', PeopleFamilyController::class . ":upactionFamilyField" );
 /*
 * @! Move down the family custom field
 * #! param: id->int :: orderID as id
 * #! param: id->int :: field as id
 */
    $group->post('/downactionfield', PeopleFamilyController::class . ":downactionFamilyField" );

});
