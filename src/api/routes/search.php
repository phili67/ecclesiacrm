<?php
/*******************************************************************************
*
*  filename    : api/routes/search.php
*  last change : 2020/10/29 Philippe Logel all right reserved
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
use EcclesiaCRM\Search\PersonAssignToGroupSearchRes;
use EcclesiaCRM\Search\PersonVolunteerOpportunitySearchRes;
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
    $this->post('/getGroupRoleForGroupID/', 'getGroupRoleForGroupID' );
    $this->post('/getresult/', 'getSearchResult' );
    $this->get('/getresult/', 'getSearchResult' );// for test
});

function getSearchResult (Request $request, Response $response, array $args) {
    $req = (object)$request->getParsedBody();

    $query = $req->SearchTerm;
    $query_elements = $req->Elements;
    $group_elements = $req->GroupElements;
    $group_role_elements = $req->GroupRoleElements;

    $resultsArray = [];

    if (mb_strlen($query) > 0 && $query != "*") {
        $resMethods = [
            new PersonSearchRes(true, $query_elements, $group_elements, $group_role_elements),
            new AddressSearchRes(true),
            new PersonPropsSearchRes(true),
            new PersonCustomSearchRes(true),
            new PersonPastoralCareSearchRes(true),
            new PersonAssignToGroupSearchRes( true),
            new FamilySearchRes(true),
            new FamilyCustomSearchRes(true),
            new FamilyPastoralCareSearchRes(true),
            new DepositSearchRes(true),
            new PaymentSearchRes(true),
            new PledgeSearchRes( true),
            new GroupSearchRes( true),
            new PersonVolunteerOpportunitySearchRes( true)
        ];
    } elseif ($query == "*" || count($query_elements) > 0) {
        $query = "";
        $resMethods = [
            new PersonSearchRes(true, $query_elements, $group_elements, $group_role_elements)
        ];
    }

    foreach ($resMethods as $resMethod) {
        $resultsArray = array_merge($resultsArray,$resMethod->getRes($query));
    }

    return $response->withJson(["SearchResults" => $resultsArray]);
}

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
        new PersonAssignToGroupSearchRes(),
        new FamilyCustomSearchRes(),
        new PersonPastoralCareSearchRes(),
        new FamilyPastoralCareSearchRes(),
        new PersonVolunteerOpportunitySearchRes()
    ];

    foreach ($resMethods as $resMethod) {
        $resultsArray[] = $resMethod->getRes($query);
    }

    return $response->withJson(array_values(array_filter($resultsArray)));
}

function  comboElements (Request $request, Response $response, array $args) {

    $iTenThousand = 10000;

    // Create array with Classification Information (lst_ID = 1)
    $gender = ["Gender-1" => _("Male"), "Gender-2" =>_("Female"), ];

    // Create array with Classification Information (lst_ID = 1)
    $ormClassifications = ListOptionQuery::create()->filterById(1)->orderByOptionSequence()->find();

    $aClassificationName["Classification-0"] = _("Unassigned");
    foreach ($ormClassifications as $classification) {
        $aClassificationName["Classification-".intval($classification->getOptionId())] = $classification->getOptionName();
    }

    $aClassificationName["Classification--10000"] = "!"._("Unassigned");
    foreach ($ormClassifications as $classification) {
        $aClassificationName["Classification-".(intval($classification->getOptionId())-$iTenThousand)] = "!".$classification->getOptionName();
    }

    // Create array with Family Role Information (lst_ID = 2)
    $ormFamilyRole =  ListOptionQuery::create()->filterById(2)->orderByOptionSequence()->find();

    $aFamilyRoleName["FamilyRole-0"] = _("Unassigned");
    foreach ($ormFamilyRole as $role) {
        $aFamilyRoleName["FamilyRole-".intval($role->getOptionId())] = $role->getOptionName();
    }

    $aClassificationName["FamilyRole--10000"] = "!"._("Unassigned");
    foreach ($ormFamilyRole as $role) {
        $aFamilyRoleName["FamilyRole-".(intval($role->getOptionId())-$iTenThousand)] = "!".$role->getOptionName();
    }

    // Get the total number of Person Properties (p) in table Property_pro
    $ormPro = PropertyQuery::create()->orderByProName()->findByProClass('p');

    $aFamilyRoleName["PersonProperty-0"] = _("Unassigned");
    foreach ($ormPro as $pro) {
        $aPersonPropertyName["PersonProperty-".intval($pro->getProId())] = $pro->getProName();
    }

    $aClassificationName["PersonProperty--10000"] = "!"._("Unassigned");
    foreach ($ormPro as $pro) {
        $aPersonPropertyName["PersonProperty-".(intval($pro->getProId())-$iTenThousand)] = "!".$pro->getProName();
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
        $aGroupTypes["GroupType-".(intval($type->getOptionId())-$iTenThousand)] = "!".$type->getOptionName();
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

    $groups=GroupQuery::Create()
        ->useGroupTypeQuery()
        ->filterByListOptionId($req->GroupType)
        ->endUse()
        ->filterByType ([3,4])// normal groups + sunday groups
        ->orderByName()
        ->find();

    return $response->withJson($groups->toArray());
}

function getGroupRoleForGroupID (Request $request, Response $response, array $args) {
    // Create array with Classification Information (lst_ID = 1)

    $req = (object)$request->getParsedBody();


    // Get the group's role list ID
    $grp = GroupQuery::create()->findOneById($req->Group);

    if (!is_null ($grp)) {
        $iRoleListID  = $grp->getRoleListId();
    }

    // Get the roles
    $ormRoles = ListOptionQuery::create()->filterById($iRoleListID)->orderByOptionSequence()->find();

    unset($aGroupRoles);
    foreach ($ormRoles as $role) {
        $aGroupRoles[intval($role->getOptionId())] = $role->getOptionName();
    }

    return $response->withJson($aGroupRoles);
}


