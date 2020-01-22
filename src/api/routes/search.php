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
use EcclesiaCRM\Search\PersonCustomSearchRes;
use EcclesiaCRM\Search\FamilyCustomSearchRes;
use EcclesiaCRM\Search\PledgeSearchRes;

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
        new PledgeSearchRes(),
        new PersonPastoralCareSearchRes(),
        new PersonCustomSearchRes(),
        new FamilyPastoralCareSearchRes(),
        new FamilyCustomSearchRes()
    ];

    foreach ($resMethods as $resMethod) {
        $resultsArray[] = $resMethod->getRes($query);
    }

    return $response->withJson(array_values(array_filter($resultsArray)));
});
