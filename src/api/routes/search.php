<?php
/*******************************************************************************
*
*  filename    : api/routes/search.php
*  last change : 2020/10/29 Philippe Logel all right reserved
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SearchController;

// Routes search

// search for a string in Persons, families, groups, Financial Deposits and Payments

/*
 * @! a search query. Returns all instances of Persons, Families, Groups, Deposits, Checks, Payments that match the search query
 * #! param: ref->string :: query string as ref
 */

$app->group('/search', function (RouteCollectorProxy $group) {

    /*
    * @! Quick search for left menu search field
    * #! param: ref->string :: query
    */
    $group->get('/{query}', SearchController::class . ':quickSearch' );
    /*
    * @! Main search for all options : *, famillies, persons, etc ...
    * #! param: ref->string :: query
    */
    $group->get('/getresultbyname/{query}', SearchController::class . ':getSearchResultByName' );
    /*
    * @! Combo elements : whe we search by *, you can add options like Gender, Classification, FamilyRole, etc ....
    * #! param: ref->string :: query
    */
    $group->post('/comboElements/', SearchController::class . ':comboElements' );
    /*
    * @! Search for group typ
    * #! param: ref->string :: GroupType
    */
    $group->post('/getGroupForTypeID/', SearchController::class . ':getGroupForTypeID' );
    /*
    * @! Get group role for Group ID
    * #! param: ref->int :: Group
    */
    $group->post('/getGroupRoleForGroupID/', SearchController::class . ':getGroupRoleForGroupID' );
    /*
    * @! Get search result for the main seach view
    * #! param: ref->string :: query
    */
    $group->post('/getresult/', SearchController::class . ':getSearchResult' );
    //$group->get('/getresult/', SearchController::class . ':getSearchResult' );// for test
});






