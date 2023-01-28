<?php
// Copyright 2018 Philippe Logel all right reserved
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SystemGDRPController;

$app->group('/gdrp', function (RouteCollectorProxy $group) {

    /*
    * @! Get all GDPR notes for each custom fields
    */
    $group->post('/', SystemGDRPController::class . ':getAllGdprNotes' );
    /*
    * @! Set GDPR note (comment)
    * #! param: ref->int :: custom_id
    * #! param: ref->string :: comment
    * #! param: ref->int :: type 'person', 'personCustom', 'personProperty', 'family', 'familyCustom'
    */
    $group->post('/setComment', SystemGDRPController::class . ':setGdprComment' );
    /*
    * @! remove a person for gdpr by person ID
    * #! param: ref->int :: personId
    */
    $group->post('/removeperson', SystemGDRPController::class . ':removePersonGdpr' );
    /*
    * @! Remove all persons
    */
    $group->post('/removeallpersons', SystemGDRPController::class . ':removeAllPersonsGdpr' );
    /*
    * @! remove a fmaily for gdpr by family ID
    * #! param: ref->int :: familyId
    */
    $group->post('/removefamily', SystemGDRPController::class . ':removeFamilyGdpr' );
    /*
    * @! Remove all families
    */
    $group->post('/removeallfamilies', SystemGDRPController::class . ':removeAllFamiliesGdpr' );

});


