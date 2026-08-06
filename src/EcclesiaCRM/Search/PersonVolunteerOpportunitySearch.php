<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Map\VolunteerOpportunityTableMap;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;


class PersonVolunteerOpportunitySearchRes extends BaseSearchRes
{
    private function getAddressMarkup($family, string $rootPath): string
    {
        if (is_null($family)) {
            return '';
        }

        return '<a href="' . $rootPath . '/v2/people/family/view/' . $family->getId() . '">' .
            $family->getName() . MiscUtils::FormatAddressLine($family->getAddress1(), $family->getCity(), $family->getState()) .
            '</a>';
    }

    private function getActionsMarkup(int $personId, bool $showCart, bool $inCart, string $rootPath): string
    {
        $actions = '';

        if ($showCart) {
            $actions .= '<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
        }

        $actions .= '<span class="fa-stack">'
            . '<i class="fas fa-square fa-stack-2x"></i>'
            . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
            . '</span>';

        if ($showCart) {
            $actions .= '</a>&nbsp;';
        }

        if (!$inCart) {
            if ($showCart) {
                $actions .= '<a class="AddToPeopleCart" data-cartpersonid="' . $personId . '">';
            }

            $actions .= '                <span class="fa-stack">'
                . '                <i class="fas fa-square fa-stack-2x"></i>'
                . '                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                . '                </span>';

            if ($showCart) {
                $actions .= '                </a>  ';
            }
        } else {
            if ($showCart) {
                $actions .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $personId . '">';
            }

            $actions .= '                <span class="fa-stack">'
                . '                <i class="fas fa-square fa-stack-2x"></i>'
                . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                . '                </span>';

            if ($showCart) {
                $actions .= '                </a>  ';
            }
        }

        return $actions;
    }

    private function buildPersonResult($person, array $opportunityNamesByPersonId, string $rootPath, bool $quickSearch, bool $showCart, bool $showPrivacyData, array $peopleInCart, int &$id): array
    {
        $personId = $person->getId();

        if ($quickSearch) {
            return [
                'id' => 'person-vol-id-' . $id++,
                'text' => $person->getTitle() . ' : ' . $person->getLastName() . ' ' . $person->getFirstName(),
                'uri' => $rootPath . '/v2/people/person/view/' . $personId
            ];
        }

        $address = $this->getAddressMarkup($person->getFamily(), $rootPath);
        $actions = $this->getActionsMarkup($personId, $showCart, isset($peopleInCart[$personId]), $rootPath);

        return [
            'id' => $personId,
            'img' => $person->getJPGPhotoDatas(),
            'searchresult' => '<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) . '</a>',
            'address' => (!$showPrivacyData) ? _('Private Data') : $address,
            'type' => ' ' . _($this->getGlobalSearchType()),
            'realType' => $this->getGlobalSearchType(),
            'Gender' => '',
            'Classification' => $opportunityNamesByPersonId[$personId] ?? '',
            'ProNames' => '',
            'FamilyRole' => '',
            'members' => '',
            'actions' => $actions
        ];
    }

    private function getOpportunityNamesByPersonIds(array $personIds): array
    {
        $personIds = array_values(array_unique(array_filter($personIds, static function ($personId) {
            return !empty($personId);
        })));

        if (empty($personIds)) {
            return [];
        }

        $opportunityNamesByPersonId = [];
        $assignments = PersonVolunteerOpportunityQuery::create()
            ->filterByPersonId($personIds, Criteria::IN)
            ->useVolunteerOpportunityQuery()
                ->filterByName('', Criteria::NOT_EQUAL)
                ->addAsColumn('OpportunityName', VolunteerOpportunityTableMap::COL_VOL_NAME)
            ->endUse()
            ->find();

        foreach ($assignments as $assignment) {
            $opportunityName = trim((string) $assignment->getOpportunityName());

            if ($opportunityName === '') {
                continue;
            }

            $opportunityNamesByPersonId[$assignment->getPersonId()][$opportunityName] = true;
        }

        return array_map(static function (array $opportunityNames): string {
            return implode(', ', array_keys($opportunityNames));
        }, $opportunityNamesByPersonId);
    }

    public function __construct($global = false)
    {
        $this->name = _('Volunteer Opportunities');
        parent::__construct($global,'Volunteer Opportunities');
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isSeePrivacyDataEnabled();
    }

    public function buildSearch(string $qry)
    {
        if ( SystemConfig::getBooleanValue("bSearchIncludePersons") ) {
            try {
                $currentUser = SessionUser::getUser();
                $searchLikeString = '%' . str_replace('*', '%', $qry) . '%';
                $rootPath = SystemURLs::getRootPath();
                $quickSearch = $this->isQuickSearch();
                $showCart = $currentUser->isShowCartEnabled();
                $showPrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $peopleInCart = $showCart ? array_fill_keys(Cart::PeopleInCart(), true) : [];
                
                $pers = PersonQuery::create()
                    ->setDistinct()
                    ->leftJoinWithFamily();


                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $pers->filterByDateDeactivated(null);
                }

                if ( !( mb_strtolower($qry) == mb_strtolower(_('volunteers')) || mb_strtolower($qry) == mb_strtolower(_('volunteer')) ) ) {
                    $pers->usePersonVolunteerOpportunityQuery()
                            ->useVolunteerOpportunityQuery()
                                ->filterByName($searchLikeString, Criteria::LIKE)
                                ->_or()->filterByDescription($searchLikeString, Criteria::LIKE)
                            ->endUse()
                        ->endUse();
                } else {
                    $pers->usePersonVolunteerOpportunityQuery()
                            ->useVolunteerOpportunityQuery()
                                ->filterByName("", Criteria::NOT_EQUAL)
                                ->_or()->filterByDescription("", Criteria::NOT_EQUAL)
                            ->endUse()
                        ->endUse();
                }

                if ($quickSearch) {
                    $pers->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $pers = $pers->find();


                if ( $pers->count() > 0) {
                    $opportunityNamesByPersonId = $this->getOpportunityNamesByPersonIds(array_map(static function ($person) {
                        return $person->getId();
                    }, iterator_to_array($pers)));
                    $id=1;

                    foreach ($pers as $per) {
                        $elt = $this->buildPersonResult($per, $opportunityNamesByPersonId, $rootPath, $quickSearch, $showCart, $showPrivacyData, $peopleInCart, $id);

                        array_push($this->results, $elt);
                    }
                } else {
                    // in the case of a search for "volunteer" or "volunteers", we want to return all the volunteers, so we need to do a specific search
                    $volunteerPersons = PersonQuery::create();
                    $volunteerPersons->filterByLastName($searchLikeString, Criteria::LIKE)
                            ->_or()->filterByFirstName($searchLikeString, Criteria::LIKE);
                    $volunteerPersons->setDistinct();
                    $volunteerPersons->leftJoinWithFamily();

                    if (SystemConfig::getBooleanValue('bGDPR')) {
                        $volunteerPersons->filterByDateDeactivated(null);
                    }

                    $volunteerPersons->usePersonVolunteerOpportunityQuery()
                            ->useVolunteerOpportunityQuery()
                                ->filterByName('', Criteria::NOT_EQUAL)
                            ->endUse()
                        ->endUse();

                    if ($quickSearch) {
                        $volunteerPersons->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                    }                    

                    $volunteerPersons = $volunteerPersons->find();

                    if ($volunteerPersons->count() > 0) {
                        $opportunityNamesByPersonId = $this->getOpportunityNamesByPersonIds(array_map(static function ($person) {
                            return $person->getId();
                        }, iterator_to_array($volunteerPersons)));
                        $id = 1;

                        foreach ($volunteerPersons as $per) {
                            $elt = $this->buildPersonResult($per, $opportunityNamesByPersonId, $rootPath, $quickSearch, $showCart, $showPrivacyData, $peopleInCart, $id);

                            array_push($this->results, $elt);
                        }
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
