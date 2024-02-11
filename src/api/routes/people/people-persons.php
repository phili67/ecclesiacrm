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

    $group->get('/sundayschool/search/{query}', PeoplePersonController::class . ":searchSundaySchoolPerson" );

    /*
     * @! Returns a list of the persons who are in the cart
     */
    $group->get('/cart/view', PeoplePersonController::class . ":personCartView" );


    /*
    * @! Verify the person for the personId
    * #! param: id->int :: personId as id
    */
    $group->post('/{personId:[0-9]+}/verify', PeoplePersonController::class . ":verifyPerson" );

    /*
    * @! Verify the person for the personId
    * #! param: id->int :: personId as id
    */
    $group->post('/{personId:[0-9]+}/verifyPDF', PeoplePersonController::class . ":verifyPersonPDF" );

    /*
    * @! Verify the person for the personId now
    * #! param: id->int :: personId as id
    */
    $group->post('/verify/{personId:[0-9]+}/now', PeoplePersonController::class . ":verifyPersonNow" );

    /*
    * @! Verify the family for the familyId now
    * #! param: id->int :: family
    */
    $group->post('/verify/url', PeoplePersonController::class . ':verifyPersonURL' );

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
    /*
     * @! Return assigned properties for a person
     * #! param: id->int :: personId
     */
    $group->post('/personproperties/{personID:[0-9]+}', PeoplePersonController::class . ":personpropertiesPerPersonId" );
    /*
     * @! Return Number of BirthDates
     */
    $group->get('/numbers', PeoplePersonController::class . ":numbersOfBirthDates" );


    /*
     * @! get person photo
     * #! param: id->int :: personId
     */
    $group->get('/{personId:[0-9]+}/photo', PeoplePersonController::class . ":photo" );

    /*
     * @! get person thumbnail
     * #! param: id->int :: personId
     */
    $group->get('/{personId:[0-9]+}/thumbnail', PeoplePersonController::class . ":thumbnail" );

    /*
     * @! Set person photo
     * #! param: id->int :: personId
     * #! param: id->string :: imgBase64
     */
    $group->post('/{personId:[0-9]+}/photo', PeoplePersonController::class . ":postPersonPhoto" );
    /*
     * @! delete person photo
     * #! param: id->int :: personId
     */
    $group->delete('/{personId:[0-9]+}/photo', PeoplePersonController::class . ":deletePersonPhoto" );

    /*
     * @! add person to cart
     * #! param: id->int :: personId
     */
    $group->post('/{personId:[0-9]+}/addToCart', PeoplePersonController::class . ":addPersonToCart" );

    /*
     * @! delete person
     * #! param: id->int :: personId
     */
    $group->delete('/{personId:[0-9]+}', PeoplePersonController::class . ":deletePerson" );

    /*
     * @! delete person field
     * #! param: id->int :: orderID
     * #! param: id->int :: field
     */
    $group->post('/deletefield', PeoplePersonController::class . ":deletePersonField" );
    /*
     * @! up action person field
     * #! param: id->int :: orderID
     * #! param: id->int :: field
     */
    $group->post('/upactionfield', PeoplePersonController::class . ":upactionPersonfield" );
    /*
     * @! down action person field
     * #! param: id->int :: orderID
     * #! param: id->int :: field
     */
    $group->post('/downactionfield', PeoplePersonController::class . ":downactionPersonfield" );

/**
 * A method that review dup emails in the db and returns families and people where that email is used.
 */

    /*
     * @! duplicate emails in mailchimp
     */
    $group->get('/duplicate/emails', PeoplePersonController::class . ":duplicateEmails" );
    /*
     * @! not in email for mailchimp
     */
    $group->get('/NotInMailChimp/emails/{type}', PeoplePersonController::class . ":notInMailChimpEmails" );

/**
 * A method that review dup emails in the db and returns families and people where that email is used.
 */
    /*
     * @! Export note as word file
     * #! param: id->int :: personId
     * #! param: id->int :: noteId
     */
    $group->post('/saveNoteAsWordFile', PeoplePersonController::class . ":saveNoteAsWordFile" );

    /*
     * @! Export vCard for the current user
     * #! param: id->int :: personId
     */
    $group->get( '/addressbook/extract/{personId:[0-9]+}', PeoplePersonController::class . ":addressBook" );
});


