<?php
/*******************************************************************************
*
*  filename    : api/routes/search.php
*  last change : 2017/10/29 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/

use EcclesiaCRM\Search\PersonSearchRes;
use EcclesiaCRM\Search\AddressSearchRes;
use EcclesiaCRM\Search\FamilySearchRes;
use EcclesiaCRM\Search\GroupSearchRes;
use EcclesiaCRM\Search\DepositSearchRes;
use EcclesiaCRM\Search\PaymentSearchRes;
use EcclesiaCRM\Search\PersonPastoralCareSearchRes;
use EcclesiaCRM\Search\FamilyPastoralCareSearchRes;

// Routes search

// search for a string in Persons, families, groups, Financial Deposits and Payments

/*
 * @! a search query. Returns all instances of Persons, Families, Groups, Deposits, Checks, Payments that match the search query
 * #! param: ref->string :: query string as ref
 */

$app->get('/search/{query}', function ($request, $response, $args) {
    $query = $args['query'];

    $resultsArray = [];

    $resMethods = [
        new PersonSearchRes(),
        new AddressSearchRes(),
        new FamilySearchRes(),
        new GroupSearchRes(),
        new DepositSearchRes(),
        new PaymentSearchRes(),
        new PersonPastoralCareSearchRes(),
        new FamilyPastoralCareSearchRes()
    ];

    foreach ($resMethods as $resMethod) {
        $res = $resMethod->getRes($query);
        if (!empty ($res)) {
            $resultsArray[] = $res;
        }
    }

    return $response->withJson(array_filter($resultsArray));
});
