<?php

/*******************************************************************************
 *
 *  filename    : pastoralecare.php api
 *  last change : 2020-06-24
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *  Updated : 2020-07-07
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PastoralCareController;

$app->group('/pastoralcare', function (RouteCollectorProxy $group) {

    $group->post('/', PastoralCareController::class . ':getAllPastoralCare' );
    $group->post('/deletetype', PastoralCareController::class . ':deletePastoralCareType' );
    $group->post('/createtype', PastoralCareController::class . ':createPastoralCareType' );
    $group->post('/settype', PastoralCareController::class . ':setPastoralCareType' );
    $group->post('/edittype', PastoralCareController::class . ':editPastoralCareType' );

    $group->post('/person/add', PastoralCareController::class . ':addPastoralCarePerson' );
    $group->post('/person/delete', PastoralCareController::class . ':deletePastoralCarePerson' );
    $group->post('/person/getinfo', PastoralCareController::class . ':getPastoralCareInfoPerson' );
    $group->post('/person/modify', PastoralCareController::class . ':modifyPastoralCarePerson' );

    $group->post('/family/add', PastoralCareController::class . ':addPastoralCareFamily' );
    $group->post('/family/delete', PastoralCareController::class . ':deletePastoralCareFamily' );
    $group->post('/family/getinfo', PastoralCareController::class . ':getPastoralCareInfoFamily' );
    $group->post('/family/modify', PastoralCareController::class . ':modifyPastoralCareFamily' );

    $group->post('/members', PastoralCareController::class . ':pastoralcareMembersDashboard' );
    $group->post('/personNeverBeenContacted', PastoralCareController::class . ':personNeverBeenContacted' );
    $group->post('/familyNeverBeenContacted', PastoralCareController::class . ':familyNeverBeenContacted' );
    $group->post('/singleNeverBeenContacted', PastoralCareController::class . ':singleNeverBeenContacted' );
    $group->post('/retiredNeverBeenContacted', PastoralCareController::class . ':retiredNeverBeenContacted' );
    $group->post('/youngNeverBeenContacted', PastoralCareController::class . ':youngNeverBeenContacted' );

    $group->post('/createRandomly', PastoralCareController::class . ':createRandomlyPastoralCare');

    $group->post('/getPersonByClassification', PastoralCareController::class . ':getPersonByClassificationPastoralCare' );

    $group->post('/getPersonByClassification/{type:[0-9]+}', PastoralCareController::class . ':getPersonByClassificationPastoralCare' );

    $group->get('/getlistforuser/{UserID:[0-9]+}', PastoralCareController::class . ':getPastoralCareListForUser' );

});
