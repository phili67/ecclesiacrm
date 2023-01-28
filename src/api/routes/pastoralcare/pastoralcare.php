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

    /*
     * @! Get all pastoral care for User ID (person)
     * #! param: ref->int :: UserID
     */
    $group->post('/', PastoralCareController::class . ':getAllPastoralCare' );
    /*
     * @! delete pastoral care type
     * #! param: ref->int :: pastoralCareTypeId
     */
    $group->post('/deletetype', PastoralCareController::class . ':deletePastoralCareType' );
    /*
     * @! create pastoral care type
     * #! param: ref->bool :: Visible
     * #! param: ref->string :: Title
     * #! param: ref->string :: Description
     */
    $group->post('/createtype', PastoralCareController::class . ':createPastoralCareType' );
    /*
     * @! modify and set pastoral care type
     * #! param: ref->int :: pastoralCareTypeId
     * #! param: ref->bool :: Visible
     * #! param: ref->string :: Title
     * #! param: ref->string :: Description
     */
    $group->post('/settype', PastoralCareController::class . ':setPastoralCareType' );
    /*
     * @! get pastoral care type infos
     * #! param: ref->int :: pastoralCareTypeId
     */
    $group->post('/edittype', PastoralCareController::class . ':editPastoralCareType' );

// pastoral care for a person

    /*
     * @! create new pastoral care for a person
     * #! param: ref->int :: typeID
     * #! param: ref->int :: personID
     * #! param: ref->int :: currentPastorId
     * #! param: ref->bool :: visibilityStatus
     * #! param: ref->string :: noteText
     */
    $group->post('/person/add', PastoralCareController::class . ':addPastoralCarePerson' );
    /*
     * @! delete pastoral care for a person ID
     * #! param: ref->int :: ID
     */
    $group->post('/person/delete', PastoralCareController::class . ':deletePastoralCarePerson' );
    /*
     * @! get pastoral care infos for a person ID
     * #! param: ref->int :: ID
     */
    $group->post('/person/getinfo', PastoralCareController::class . ':getPastoralCareInfoPerson' );
    /*
     * @! get pastoral care for a person ID
     * #! param: ref->int :: ID
     * #! param: ref->int :: typeID
     * #! param: ref->int :: personID
     * #! param: ref->int :: currentPastorId
     * #! param: ref->bool :: visibilityStatus
     * #! param: ref->string :: noteText
     */
    $group->post('/person/modify', PastoralCareController::class . ':modifyPastoralCarePerson' );

// pastoral care for a family
    /*
     * @! create new pastoral care for a family
     * #! param: ref->int :: typeID
     * #! param: ref->int :: familyID
     * #! param: ref->int :: currentPastorId
     * #! param: ref->bool :: visibilityStatus
     * #! param: ref->string :: noteText
     * #! param: ref->bool :: includeFamMembers
     */
    $group->post('/family/add', PastoralCareController::class . ':addPastoralCareFamily' );
    /*
     * @! delete pastoral care for a family ID
     * #! param: ref->int :: ID
     */
    $group->post('/family/delete', PastoralCareController::class . ':deletePastoralCareFamily' );
    /*
     * @! get pastoral care for a family ID
     * #! param: ref->int :: ID
     */
    $group->post('/family/getinfo', PastoralCareController::class . ':getPastoralCareInfoFamily' );
    /*
     * @! modify pastoral care for a family ID
     * #! param: ref->int :: ID
     * #! param: ref->int :: typeID
     * #! param: ref->int :: familyID
     * #! param: ref->int :: currentPastorId
     * #! param: ref->bool :: visibilityStatus
     * #! param: ref->string :: noteText
    */
    $group->post('/family/modify', PastoralCareController::class . ':modifyPastoralCareFamily' );

// for the main pastoral care dashboard
    /*
     * @! get all pastoral cares for all the members in the sPastoralcarePeriod (see for this the settings infos)
    */
    $group->post('/members', PastoralCareController::class . ':pastoralcareMembersDashboard' );
    /*
     * @! get the persons never been contacted sPastoralcarePeriod (see for this the settings infos)
    */
    $group->post('/personNeverBeenContacted', PastoralCareController::class . ':personNeverBeenContacted' );
    /*
     * @! get the families never been contacted sPastoralcarePeriod (see for this the settings infos)
    */
    $group->post('/familyNeverBeenContacted', PastoralCareController::class . ':familyNeverBeenContacted' );
    /*
     * @! get the single persons never been contacted sPastoralcarePeriod (see for this the settings infos)
    */
    $group->post('/singleNeverBeenContacted', PastoralCareController::class . ':singleNeverBeenContacted' );
    /*
     * @! get the retired persons never been contacted sPastoralcarePeriod (see for this the settings infos)
    */
    $group->post('/retiredNeverBeenContacted', PastoralCareController::class . ':retiredNeverBeenContacted' );
    /*
     * @! get the young persons never been contacted sPastoralcarePeriod (see for this the settings infos)
    */
    $group->post('/youngNeverBeenContacted', PastoralCareController::class . ':youngNeverBeenContacted' );
    /*
     * @! get the young persons never been contacted sPastoralcarePeriod (see for this the settings infos)
     * #! param: ref->int :: typeID (1 : person, 2: family, 3: retired, 4: young person, 5: single person
    */
    $group->post('/createRandomly', PastoralCareController::class . ':createRandomlyPastoralCare');
    /*
     * @! get the persons never been reached
    */
    $group->post('/getPersonByClassification', PastoralCareController::class . ':getPersonByClassificationPastoralCare' );
    /*
     * @! get the persons never been reached for the last period (sPastoralcarePeriod)
     * #! param: ref->int :: type (1: yet contacted)
    */
    $group->post('/getPersonByClassification/{type:[0-9]+}', PastoralCareController::class . ':getPersonByClassificationPastoralCare' );
    /*
     * @! get the pastoral care user in period for pastor current user ID in current period (sPastoralcarePeriod)
     * #! param: ref->int :: UserID
    */
    $group->get('/getlistforuser/{UserID:[0-9]+}', PastoralCareController::class . ':getPastoralCareListForUser' );

});
