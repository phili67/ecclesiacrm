<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
use EcclesiaCRM\Search\PersonGroupManagerSearchRes;
use EcclesiaCRM\Search\FamilyPropsSearchRes;
use EcclesiaCRM\Search\GroupPropsSearchRes;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Search\SearchLevel;

class SearchController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getSearchResultByName (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $query = $args['query'];

        $resultsArray = [];

        if ($query == "*") {
            $resultsArray[] =
                ['id' => 'person-id-1',
                    'text' => "*",
                    'uri' => ""];
        } else {
            if ( str_starts_with(mb_strtolower(_("Families")), mb_strtolower($query)) ) {
                $resultsArray[] =
                    ['id' => 'search-id-1',
                        'text' => _("Families"),
                        'uri' => ""];
            } elseif ( str_starts_with(mb_strtolower(_("Singles")), mb_strtolower($query)) ) {
                $resultsArray[] =
                    ['id' => 'search-id-1',
                        'text' => _("Singles"),
                        'uri' => ""];
            } elseif ( str_starts_with(mb_strtolower(_("Volunteers")), mb_strtolower($query)) ) {
                $resultsArray[] =
                    ['id' => 'search-id-1',
                        'text' => _("Volunteers"),
                        'uri' => ""];
            } elseif ( str_starts_with(mb_strtolower(_("Groups")), mb_strtolower($query)) ) {
                $resultsArray[] =
                    ['id' => 'search-id-1',
                        'text' => _("Groups"),
                        'uri' => ""];
            } elseif ( str_starts_with(mb_strtolower(_("Sunday Groups")), mb_strtolower($query)) ) {
                $resultsArray[] =
                    ['id' => 'search-id-1',
                        'text' => _("Sunday Groups"),
                        'uri' => ""];
            } elseif ( str_starts_with(mb_strtolower(_("groupmasters")), mb_strtolower($query)) ) {
                $resultsArray[] =
                    ['id' => 'search-id-1',
                        'text' => _("groupmasters"),
                        'uri' => ""];
            }

            $resMethods = [
                new PersonSearchRes(SearchLevel::STRING_RETURN),
                new AddressSearchRes(SearchLevel::STRING_RETURN),
                new PersonPropsSearchRes(SearchLevel::STRING_RETURN),
                new PersonCustomSearchRes(SearchLevel::STRING_RETURN),
                new PersonAssignToGroupSearchRes(SearchLevel::STRING_RETURN),
                new PersonPastoralCareSearchRes(SearchLevel::STRING_RETURN),
                new PersonGroupManagerSearchRes (SearchLevel::STRING_RETURN),
                new FamilySearchRes(SearchLevel::STRING_RETURN),
                new FamilyCustomSearchRes(SearchLevel::STRING_RETURN),
                new FamilyPropsSearchRes(SearchLevel::STRING_RETURN),
                new FamilyPastoralCareSearchRes(SearchLevel::STRING_RETURN),
                new GroupSearchRes(SearchLevel::STRING_RETURN),
                new GroupPropsSearchRes(SearchLevel::STRING_RETURN),
                new DepositSearchRes(SearchLevel::STRING_RETURN),
                new PaymentSearchRes(SearchLevel::STRING_RETURN),
                new PledgeSearchRes(SearchLevel::STRING_RETURN),
                new PersonVolunteerOpportunitySearchRes(SearchLevel::STRING_RETURN)
            ];
        }

        foreach ($resMethods as $resMethod) {
            $res = $resMethod->getRes($query);

            if ( !is_array($res) ) {
                $res = $res->jsonSerialize();
            }
            $resultsArray = array_merge($resultsArray,$res);
        }

        return $response->withJson($resultsArray);
    }

    public function getSearchResult (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $req = (object)$request->getParsedBody();

        $query = $req->SearchTerm;

        $query = filter_var($query, FILTER_SANITIZE_STRING);

        $query_elements = $req->Elements;
        $group_elements = $req->GroupElements;
        $group_role_elements = $req->GroupRoleElements;

        $resultsArray = [];

        if (mb_strlen($query) > 0 && $query != "*") {
            $resMethods = [
                new PersonSearchRes(SearchLevel::GLOBAL_SEARCH, $query_elements, $group_elements, $group_role_elements),
                new AddressSearchRes(SearchLevel::GLOBAL_SEARCH),
                new PersonPropsSearchRes(SearchLevel::GLOBAL_SEARCH),
                new PersonCustomSearchRes(SearchLevel::GLOBAL_SEARCH),
                new PersonGroupManagerSearchRes (SearchLevel::GLOBAL_SEARCH),
                new PersonPastoralCareSearchRes(SearchLevel::GLOBAL_SEARCH),
                new PersonAssignToGroupSearchRes( SearchLevel::GLOBAL_SEARCH),
                new FamilySearchRes(SearchLevel::GLOBAL_SEARCH),
                new FamilyCustomSearchRes(SearchLevel::GLOBAL_SEARCH),
                new FamilyPastoralCareSearchRes(SearchLevel::GLOBAL_SEARCH),
                new FamilyPropsSearchRes(SearchLevel::GLOBAL_SEARCH),
                new GroupSearchRes( SearchLevel::GLOBAL_SEARCH),
                new GroupPropsSearchRes(SearchLevel::GLOBAL_SEARCH),
                new DepositSearchRes(SearchLevel::GLOBAL_SEARCH),
                new PaymentSearchRes(SearchLevel::GLOBAL_SEARCH),
                new PledgeSearchRes( SearchLevel::GLOBAL_SEARCH),
                new PersonVolunteerOpportunitySearchRes( SearchLevel::GLOBAL_SEARCH)
            ];
        } elseif ($query == "*" || count($query_elements) > 0) {
            $query = "";
            $resMethods = [
                new PersonSearchRes(SearchLevel::GLOBAL_SEARCH, $query_elements, $group_elements, $group_role_elements)
            ];
        }

        foreach ($resMethods as $resMethod) {
            $resultsArray = array_merge($resultsArray,$resMethod->getRes($query));
        }

        return $response->withJson(["SearchResults" => $resultsArray]);
    }

    public function quickSearch (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $query = $args['query'];

        $resultsArray = [];

        $resMethods = [
            new PersonSearchRes(SearchLevel::QUICK_SEARCH),
            new AddressSearchRes(SearchLevel::QUICK_SEARCH),
            new PersonPropsSearchRes(SearchLevel::QUICK_SEARCH),
            new PersonCustomSearchRes(SearchLevel::QUICK_SEARCH),
            new PersonAssignToGroupSearchRes(SearchLevel::QUICK_SEARCH),
            new PersonPastoralCareSearchRes(SearchLevel::QUICK_SEARCH),
            new PersonGroupManagerSearchRes (SearchLevel::QUICK_SEARCH),
            new FamilySearchRes(SearchLevel::QUICK_SEARCH),
            new FamilyCustomSearchRes(SearchLevel::QUICK_SEARCH),
            new FamilyPropsSearchRes(SearchLevel::QUICK_SEARCH),
            new FamilyPastoralCareSearchRes(SearchLevel::QUICK_SEARCH),
            new GroupSearchRes(SearchLevel::QUICK_SEARCH),
            new GroupPropsSearchRes(SearchLevel::QUICK_SEARCH),
            new DepositSearchRes(SearchLevel::QUICK_SEARCH),
            new PaymentSearchRes(SearchLevel::QUICK_SEARCH),
            new PledgeSearchRes(SearchLevel::QUICK_SEARCH),
            new PersonVolunteerOpportunitySearchRes(SearchLevel::QUICK_SEARCH)
        ];

        foreach ($resMethods as $resMethod) {
            $resultsArray[] = $resMethod->getRes($query);
        }

        return $response->withJson(array_values(array_filter($resultsArray)));
    }

    public function  comboElements (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

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

    public function getGroupForTypeID (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function getGroupRoleForGroupID (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
}
