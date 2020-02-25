<?php
/*******************************************************************************
*
*  filename    : api/routes/search.php
*  last change : 2017/10/29 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PropertyQuery;

use EcclesiaCRM\Search\PersonSearchRes;
use EcclesiaCRM\Search\AddressSearchRes;
use EcclesiaCRM\Search\FamilySearchRes;
use EcclesiaCRM\Search\GroupSearchRes;
use EcclesiaCRM\Search\DepositSearchRes;
use EcclesiaCRM\Search\PaymentSearchRes;
use EcclesiaCRM\Search\PledgeSearchRes;
use EcclesiaCRM\Search\PersonPropsSearchRes;
use EcclesiaCRM\Search\PersonCustomSearchRes;
use EcclesiaCRM\Search\FamilyCustomSearchRes;
use EcclesiaCRM\Search\PersonPastoralCareSearchRes;
use EcclesiaCRM\Search\FamilyPastoralCareSearchRes;
use EcclesiaCRM\GroupQuery;

// Routes search

// search for a string in Persons, families, groups, Financial Deposits and Payments

/*
 * @! a search query. Returns all instances of Persons, Families, Groups, Deposits, Checks, Payments that match the search query
 * #! param: ref->string :: query string as ref
 */

$app->group('/search', function () {
    $this->get('/{query}', 'quickSearch' );
    $this->post('/comboElements/', 'comboElements' );
    $this->post('/getGroupForTypeID/', 'getGroupForTypeID' );
});

function quickSearch (Request $request, Response $response, array $args) {
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
        new PersonPropsSearchRes(),
        new PersonCustomSearchRes(),
        new FamilyCustomSearchRes(),
        new PersonPastoralCareSearchRes(),
        new FamilyPastoralCareSearchRes()
    ];

    foreach ($resMethods as $resMethod) {
        $resultsArray[] = $resMethod->getRes($query);
    }

    return $response->withJson(array_values(array_filter($resultsArray)));
}

function  comboElements (Request $request, Response $response, array $args) {
    // Create array with Classification Information (lst_ID = 1)
    $gender = ["Gender-1" => _("Male"), "Gender-2" =>_("Female"), ];

    // Create array with Classification Information (lst_ID = 1)
    $ormClassifications = ListOptionQuery::create()->filterById(1)->orderByOptionSequence()->find();

    foreach ($ormClassifications as $classification) {
        $aClassificationName["Classification-".intval($classification->getOptionId())] = $classification->getOptionName();
    }

    foreach ($ormClassifications as $classification) {
        $aClassificationName["Classification-".(intval($classification->getOptionId())-10000)] = "!".$classification->getOptionName();
    }

    // Create array with Family Role Information (lst_ID = 2)
    $ormFamilyRole =  ListOptionQuery::create()->filterById(2)->orderByOptionSequence()->find();

    foreach ($ormFamilyRole as $role) {
        $aFamilyRoleName["FamilyRole-".intval($role->getOptionId())] = $role->getOptionName();
    }

    foreach ($ormFamilyRole as $role) {
        $aFamilyRoleName["FamilyRole-".(intval($role->getOptionId())-10000)] = "!".$role->getOptionName();
    }

    // Get the total number of Person Properties (p) in table Property_pro
    $ormPro = PropertyQuery::create()->orderByProName()->findByProClass('p');

    foreach ($ormPro as $pro) {
        $aPersonPropertyName["PersonProperty-".intval($pro->getProId())] = $pro->getProName();
    }

    foreach ($ormPro as $pro) {
        $aPersonPropertyName["PersonProperty-".(intval($pro->getProId())-10000)] = "!".$pro->getProName();
    }

    // Create array with Group Type Information (lst_ID = 3)
    $ormGroupTypes =  ListOptionQuery::create()
        ->filterById(3)
        ->filterByOptionType(['normal','sunday_school'])
        ->orderByOptionName()
        ->find();

    foreach ($ormGroupTypes  as $type) {
        $aGroupTypes["GroupType-".intval($type->getOptionId())] = $type->getOptionName();
    }

    foreach ($ormGroupTypes  as $type) {
        $aGroupTypes["GroupType-".(intval($type->getOptionId())-10000)] = "!".$type->getOptionName();
    }

    $arr = array_merge([_("Gender") => ['Gender', $gender]],
            [_("Classification") => ['Classification', $aClassificationName]],
            [_("Family Role") => ['FamilyRole', $aFamilyRoleName]] ,
            [_("Person Property")  => ['PersonProperty', $aPersonPropertyName]],
            [_("Group Type") => ['GroupType', $aGroupTypes]]);

    return $response->withJson($arr);
}

function getGroupForTypeID (Request $request, Response $response, array $args) {
    // Create array with Classification Information (lst_ID = 1)

    $req = (object)$request->getParsedBody();


    \EcclesiaCRM\Utils\LoggerUtils::getAppLogger()->info("GroupID : ".$req->GroupType);

    $groups=GroupQuery::Create()
        ->useGroupTypeQuery()
        ->filterByListOptionId($req->GroupType)
        ->endUse()
        ->filterByType ([3,4])// normal groups + sunday groups
        ->orderByName()
        ->find();

    return $response->withJson($groups->toArray());

}


