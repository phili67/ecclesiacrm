<?php

/******************************************************************************
*
*  filename    : api/routes/people.php
*  last change : Copyright all right reserved 2018/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PeopleController;

// Routes people
$app->group('/people', function (RouteCollectorProxy $group) {

    /*
     * @! Returns a list of the person who's first name or last name matches the :query parameter
     * #! param: ref->string :: query string ref
     */
  $group->get('/searchonlyperson/{query}', PeopleController::class . ':searchonlyperson' );


    /*
     * @! Returns a list of the person who's first name or last name matches the :query parameter
     * #! param: ref->string :: query string ref
     */
  $group->get('/searchonlyuser/{query}', PeopleController::class . ':searchonlyuser' );

    /*
     * @! Returns a list of the members/families/groups who's first name or last name matches the :query parameter
     * #! param: ref->string :: query string ref
     */
  $group->get('/search/{query}', PeopleController::class . ':searchpeople' );
  $group->get('/search/{query}/{type}', PeopleController::class . ':searchpeople' );

    /*
     * @! Returns all classifications
     * #! param: nothing
     */
  $group->get('/classifications/all', PeopleController::class . ':getAllClassifications' );

    /*
     * @! Returns all classifications
     * #! param: nothing
     */
  $group->post('/person/classification/assign', PeopleController::class . ':postPersonClassification' );

});
