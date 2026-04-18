<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\VolunteerOpportunityQuery;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

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

    private function getSearchTypeDefinitions(array $query_elements = [], array $group_elements = [], array $group_role_elements = []): array
    {
        return [
            'persons' => [
                'label' => _('Persons'),
                'factory' => function () use ($query_elements, $group_elements, $group_role_elements) {
                    return new PersonSearchRes(SearchLevel::GLOBAL_SEARCH, $query_elements, $group_elements, $group_role_elements);
                }
            ],
            'addresses' => [
                'label' => _('Addresses'),
                'factory' => function () {
                    return new AddressSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'person-properties' => [
                'label' => _('Person Properties'),
                'factory' => function () {
                    return new PersonPropsSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'person-custom-fields' => [
                'label' => _('Person Custom Field'),
                'factory' => function () {
                    return new PersonCustomSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'person-group-roles' => [
                'label' => _('Person Group role assignment'),
                'factory' => function () {
                    return new PersonGroupManagerSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'person-pastoral-cares' => [
                'label' => _('Individual Pastoral Cares'),
                'factory' => function () {
                    return new PersonPastoralCareSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'person-assignments' => [
                'label' => _('Group assignments'),
                'factory' => function () {
                    return new PersonAssignToGroupSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'families' => [
                'label' => _('Families'),
                'factory' => function () {
                    return new FamilySearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'family-custom-fields' => [
                'label' => _('Family Custom Field'),
                'factory' => function () {
                    return new FamilyCustomSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'family-pastoral-cares' => [
                'label' => _('Family Pastoral Cares'),
                'factory' => function () {
                    return new FamilyPastoralCareSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'family-properties' => [
                'label' => _('Family Properties'),
                'factory' => function () {
                    return new FamilyPropsSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'groups' => [
                'label' => _('Groups'),
                'factory' => function () {
                    return new GroupSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'group-properties' => [
                'label' => _('Group Properties'),
                'factory' => function () {
                    return new GroupPropsSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'deposits' => [
                'label' => _('Deposits'),
                'factory' => function () {
                    return new DepositSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'payments' => [
                'label' => _('Payments'),
                'factory' => function () {
                    return new PaymentSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'pledges' => [
                'label' => _('Pledges'),
                'factory' => function () {
                    return new PledgeSearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ],
            'volunteer-opportunities' => [
                'label' => _('Volunteer Opportunities'),
                'factory' => function () {
                    return new PersonVolunteerOpportunitySearchRes(SearchLevel::GLOBAL_SEARCH);
                }
            ]
        ];
    }

    private function buildSearchMethods(string $query, array $query_elements, array $group_elements, array $group_role_elements, array $selected_search_types = [], bool $allPeopleSearch = false): array
    {
        $definitions = $this->getSearchTypeDefinitions($query_elements, $group_elements, $group_role_elements);

        if ($allPeopleSearch || count($query_elements) > 0) {
            $allowed_search_types = ['persons'];
        } elseif (mb_strlen($query) > 0 && $query != "*") {
            $allowed_search_types = array_keys($definitions);
        } else {
            return [];
        }

        if (!empty($selected_search_types)) {
            $allowed_search_types = array_values(array_intersect($allowed_search_types, $selected_search_types));
        }

        $search_methods = [];

        foreach ($allowed_search_types as $search_type) {
            if (!isset($definitions[$search_type])) {
                continue;
            }

            $search_methods[] = $definitions[$search_type]['factory']();
        }

        return $search_methods;
    }

    private function getSearchResultsArray(object $req): array
    {
        $query = filter_var($req->SearchTerm, FILTER_SANITIZE_STRING);
        $query_elements = (array) $req->Elements;
        $group_elements = (array) $req->GroupElements;
        $group_role_elements = (array) $req->GroupRoleElements;
        $selected_search_types = array_values(array_filter((array) ($req->SearchTypes ?? []), 'is_string'));
        $allPeopleSearch = $query == "*";

        if ($allPeopleSearch) {
            $query = "";
        }

        $resultsArray = [];
        $resMethods = $this->buildSearchMethods($query, $query_elements, $group_elements, $group_role_elements, $selected_search_types, $allPeopleSearch);

        foreach ($resMethods as $resMethod) {
            if (!$resMethod->allowed()) {
                continue;
            }

            $resultsArray = array_merge($resultsArray, $resMethod->getRes($query));
        }

        return $resultsArray;
    }

    private function extractCartResultIds(array $resultsArray): array
    {
        $personIds = [];
        $familyIds = [];
        $groupIds = [];
        $personTypes = [
            'Persons',
            'Person Custom Field',
            'Individual Pastoral Cares',
            'Person Properties',
            'Person Group role assignment',
            'Volunteer Opportunities'
        ];
        $familyTypes = [
            'Families',
            'Addresses',
            'Family Custom Field',
            'Family Pastoral Cares'
        ];
        $groupTypes = [
            'Groups',
            'Group Properties'
        ];

        foreach ($resultsArray as $result) {
            if (!is_array($result) || !isset($result['realType'])) {
                continue;
            }

            if (in_array($result['realType'], $personTypes) && isset($result['id'])) {
                $personIds[(int) $result['id']] = (int) $result['id'];
                continue;
            }

            if (in_array($result['realType'], $familyTypes) && isset($result['id'])) {
                $familyIds[(int) $result['id']] = (int) $result['id'];

                if (!empty($result['members']) && is_array($result['members'])) {
                    foreach ($result['members'] as $memberId) {
                        $personIds[(int) $memberId] = (int) $memberId;
                    }
                }

                continue;
            }

            if (in_array($result['realType'], $groupTypes) && isset($result['id'])) {
                $groupIds[(int) $result['id']] = (int) $result['id'];

                if (!empty($result['members']) && is_array($result['members'])) {
                    foreach ($result['members'] as $memberId) {
                        $personIds[(int) $memberId] = (int) $memberId;
                    }
                }

                continue;
            }

            if (!empty($result['members']) && is_array($result['members'])) {
                foreach ($result['members'] as $memberId) {
                    $personIds[(int) $memberId] = (int) $memberId;
                }
            }
        }

        return [
            'PeopleIds' => array_values($personIds),
            'FamilyIds' => array_values($familyIds),
            'GroupIds' => array_values($groupIds)
        ];
    }

    private function getSearchTypeOptions(): array
    {
        $options = [];

        foreach ($this->getSearchTypeDefinitions() as $id => $definition) {
            $options[] = [
                'id' => $id,
                'text' => $definition['label']
            ];
        }

        return $options;
    }


    public function getSearchResultByName (ServerRequest $request, Response $response, array $args): Response {
        $query = $args['query'];

        $resultsArray = [];

        if ($query == "*") {
            $resultsArray[] =
                ['id' => 'person-id-1',
                    'text' => "*",
                    'uri' => ""];
        } else {
            // when the query start with 
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

                $volunteerOpportunities = VolunteerOpportunityQuery::create()->find();

                $id = 2;
                if ( $volunteerOpportunities->count() > 0 ) {
                    foreach ($volunteerOpportunities as $volunteerOpportunity) {
                        $resultsArray[] = ['id' => 'search-id-'.$id++,
                            'text' => $volunteerOpportunity->getName(),
                            'uri' => ""];
                    }
                }
            } elseif ( str_starts_with(mb_strtolower(_("Groups")), mb_strtolower($query)) ) {
                $resultsArray[] =
                    ['id' => 'search-id-1',
                        'text' => _("Groups"),
                        'uri' => ""];
            } elseif ( str_starts_with(mb_strtolower(_("Sunday Groups")), mb_strtolower($query)) 
                and SystemConfig::getBooleanValue('bEnabledSundaySchool')) {
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
                new FamilySearchRes(SearchLevel::STRING_RETURN),
                new GroupSearchRes(SearchLevel::STRING_RETURN),
                new DepositSearchRes(SearchLevel::STRING_RETURN),
                new PaymentSearchRes(SearchLevel::STRING_RETURN),
                new PledgeSearchRes(SearchLevel::STRING_RETURN),
                new PersonVolunteerOpportunitySearchRes(SearchLevel::STRING_RETURN)
            ];
        }

        foreach ($resMethods as $resMethod) {
            if ( !$resMethod->allowed() ) continue;

            $res = $resMethod->getRes($query);

            if (count($res) && $res[0] == null)
                continue;

            if ( !is_array($res) ) {
                $res = $res->jsonSerialize();
            }
            $resultsArray = array_merge($resultsArray, $res);
        }

        $resultsArray[] = ['id' => 'search-extra-id-1',
            'text' => $query,
            'uri' => ""];

        return $response->withJson(($resultsArray));
    }

    public function getSearchResult (ServerRequest $request, Response $response, array $args): Response {
        $req = (object)$request->getParsedBody();

        if (empty($req->SearchTerm)) {
            return $response->withJson(["SearchResults" => []]);
        }

        $resultsArray = $this->getSearchResultsArray($req);

        return $response->withJson(["SearchResults" => $resultsArray]);
    }

    public function getSearchPeopleIds(ServerRequest $request, Response $response, array $args): Response {
        $req = (object)$request->getParsedBody();

        return $response->withJson($this->extractCartResultIds($this->getSearchResultsArray($req)));
    }

    public function getSearchTypes(ServerRequest $request, Response $response, array $args): Response {
        return $response->withJson([
            'SearchTypes' => $this->getSearchTypeOptions()
        ]);
    }

    public function quickSearch (ServerRequest $request, Response $response, array $args): Response {
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
            if ( !$resMethod->allowed() ) continue;
            $resultsArray[] = $resMethod->getRes($query);
        }

        return $response->withJson(array_values(array_filter($resultsArray)));
    }

    public function  comboElements (ServerRequest $request, Response $response, array $args): Response {

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

    public function getGroupForTypeID (ServerRequest $request, Response $response, array $args): Response {
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

    public function getGroupRoleForGroupID (ServerRequest $request, Response $response, array $args): Response {
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
