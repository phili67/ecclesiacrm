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
    $group->get('/{query}', SearchController::class . ':quickSearch' );
    $group->get('/getresultbyname/{query}', SearchController::class . ':getSearchResultByName' );
    $group->post('/comboElements/', SearchController::class . ':comboElements' );
    $group->post('/getGroupForTypeID/', SearchController::class . ':getGroupForTypeID' );
    $group->post('/getGroupRoleForGroupID/', SearchController::class . ':getGroupRoleForGroupID' );
    $group->post('/getresult/', SearchController::class . ':getSearchResult' );
    //$group->get('/getresult/', SearchController::class . ':getSearchResult' );// for test
});






