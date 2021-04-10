<?php
/* contributor Philippe Logel */

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PeoplePersonController;

$app->group('/persons', function (RouteCollectorProxy $group) {

/*
 * @! Returns a list of the persons who's first name or last name matches the :query parameter
 * #! param: ref->string :: query string ref
 */
    $group->get('/search/{query}', PeoplePersonController::class . ":searchPerson" );

/*
 * @! Returns a list of the persons who are in the cart
 */
    $group->get('/cart/view', PeoplePersonController::class . ":personCartView" );

/**
 *
 * VolunteerOpportunity
 *
 **/

/*
 * @! Returns all the volunteers opportunities
 * #! param: id->int :: personId as id
 */
    $group->post('/volunteers/{personID:[0-9]+}', PeoplePersonController::class . ":volunteersPerPersonId" );
/*
 * @! delete a volunteer opportunity for a user
 * #! param: id1->int :: personId as id1
 * #! param: id2->int :: volunteerOpportunityId as id2
 */
    $group->post('/volunteers/delete', PeoplePersonController::class . ":volunteersDelete" );
/*
 * @! Add volunteers opportunity
 * #! param: id1->int :: personId as id1
 * #! param: id2->int :: volID as id2
 */
    $group->post('/volunteers/add', PeoplePersonController::class . ":volunteersAdd" );

/*
 * @! Return if MailChimp is activated
 * #! param: id->int :: personId as id
 * #! param: ref->string :: email as ref
 */
    $group->post('/isMailChimpActive', PeoplePersonController::class . ":isMailChimpActivePerson" );

/**
 * Update the person status to activated or deactivated with :familyId and :status true/false.
 * Pass true to activate and false to deactivate.     *
 */

/*
 * @! Return if MailChimp is activated
 * #! param: id->int :: personId as id
 * #! param: ref->string :: email as ref
 */
    $group->post('/{personId:[0-9]+}/activate/{status}', PeoplePersonController::class . ":activateDeacticate" );

    // api for person properties
    $group->post('/personproperties/{personID:[0-9]+}', PeoplePersonController::class . ":personpropertiesPerPersonId" );
    $group->get('/numbers', PeoplePersonController::class . ":numbersOfBirthDates" );

    $group->get('/{personId:[0-9]+}/photo', PeoplePersonController::class . ":photo" );

    $group->get('/{personId:[0-9]+}/thumbnail', PeoplePersonController::class . ":thumbnail" );

    $group->post('/{personId:[0-9]+}/photo', PeoplePersonController::class . ":postPersonPhoto" );
    $group->delete('/{personId:[0-9]+}/photo', PeoplePersonController::class . ":deletePersonPhoto" );

    $group->post('/{personId:[0-9]+}/addToCart', PeoplePersonController::class . ":addPersonToCart" );

    /**
     * @var $response \Psr\Http\Message\ResponseInterface
     */
    $group->delete('/{personId:[0-9]+}', PeoplePersonController::class . ":deletePerson" );

    $group->post('/deletefield', PeoplePersonController::class . ":deletePersonField" );
    $group->post('/upactionfield', PeoplePersonController::class . ":upactionPersonfield" );
    $group->post('/downactionfield', PeoplePersonController::class . ":downactionPersonfield" );

/**
 * A method that review dup emails in the db and returns families and people where that email is used.
 */

    $group->get('/duplicate/emails', PeoplePersonController::class . ":duplicateEmails" );
    $group->get('/NotInMailChimp/emails/{type}', PeoplePersonController::class . ":notInMailChimpEmails" );

/**
 * A method that review dup emails in the db and returns families and people where that email is used.
 */
    $group->post('/saveNoteAsWordFile', PeoplePersonController::class . ":saveNoteAsWordFile" );
});


