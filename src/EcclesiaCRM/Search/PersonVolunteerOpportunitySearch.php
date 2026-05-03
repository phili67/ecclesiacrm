<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Map\VolunteerOpportunityTableMap;
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
                    ->leftJoinWithFamily();


                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $pers->filterByDateDeactivated(null);
                }

                if ( !( mb_strtolower($qry) == mb_strtolower(_('volunteers')) || mb_strtolower($qry) == mb_strtolower(_('volunteer')) ) ) {
                    $pers->usePersonVolunteerOpportunityQuery()
                            ->useVolunteerOpportunityQuery()
                                ->filterByName($searchLikeString, Criteria::LIKE)
                                ->_or()->filterByDescription($searchLikeString, Criteria::LIKE)
                                ->addAsColumn('OpportunityName', VolunteerOpportunityTableMap::COL_VOL_NAME)
                            ->endUse()
                        ->endUse();
                } else {
                    $pers->usePersonVolunteerOpportunityQuery()
                            ->useVolunteerOpportunityQuery()
                                ->filterByName("", Criteria::NOT_EQUAL)
                                ->_or()->filterByDescription("", Criteria::NOT_EQUAL)
                                ->addAsColumn('OpportunityName', VolunteerOpportunityTableMap::COL_VOL_NAME)
                            ->endUse()
                        ->endUse();
                }

                if ($quickSearch) {
                    $pers->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $pers = $pers->find();


                if ( $pers->count() > 0) {
                    $id=1;

                    foreach ($pers as $per) {
                        $personId = $per->getId();

                        if ($quickSearch) {
                            $elt = ['id' => "person-vol-id-" . $id++,
                                'text' => $per->getTitle() . " : " . $per->getLastName() . " " . $per->getFirstName(),
                                'uri' => $rootPath . "/v2/people/person/view/" . $personId];
                        } else {
                            $fam = $per->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="'.$rootPath.'/v2/people/family/view/'.$fam->getId().'">'.
                                    $fam->getName().MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState()).
                                    "</a>";
                            }

                            $inCart = isset($peopleInCart[$personId]);

                            $res = "";
                            if ($showCart) {
                                $res .= '<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if ($showCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if ($showCart) {
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $personId . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    .'                </span>';
                                if ($showCart) {
                                    $res .= '                </a>  ';
                                }
                            } else {
                                if ($showCart) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $personId . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    .'                </span>';
                                if ($showCart) {
                                    $res .= '                </a>  ';
                                }
                            }

                            $elt = [
                                "id" => $personId,
                                "img" => $per->getJPGPhotoDatas(),
                                "searchresult" => '<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($per->getTitle(), $per->getFirstName(), $per->getMiddleName(), $per->getLastName(), $per->getSuffix(), 3) . '</a>',
                                "address" => (!$showPrivacyData)?_('Private Data'):$address,
                                "type" => " "._($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => $per->getOpportunityName(),
                                "ProNames" => "",
                                "FamilyRole" => "",
                                "members" => "",
                                "actions" => $res
                            ];
                        }

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
                                ->addAsColumn('OpportunityName', VolunteerOpportunityTableMap::COL_VOL_NAME)
                            ->endUse()
                        ->endUse();

                    if ($quickSearch) {
                        $volunteerPersons->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                    }

                    $volunteerPersons = $volunteerPersons->find();

                    if ($volunteerPersons->count() > 0) {
                        $id = 1;

                        foreach ($volunteerPersons as $per) {
                            $personId = $per->getId();

                            if ($quickSearch) {
                                $elt = ['id' => "person-vol-id-" . $id++,
                                    'text' => $per->getTitle() . " : " . $per->getLastName() . " " . $per->getFirstName(),
                                    'uri' => $rootPath . "/v2/people/person/view/" . $personId];
                            } else {
                                $fam = $per->getFamily();

                                $address = "";
                                if (!is_null($fam)) {
                                    $address = '<a href="'.$rootPath.'/v2/people/family/view/'.$fam->getId().'">'.
                                        $fam->getName().MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState()).
                                        "</a>";
                                }

                                $inCart = isset($peopleInCart[$personId]);

                                $res = "";
                                if ($showCart) {
                                    $res .= '<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                                }
                                $res .= '<span class="fa-stack">'
                                    .'<i class="fas fa-square fa-stack-2x"></i>'
                                    .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                    .'</span>';
                                if ($showCart) {
                                    $res .= '</a>&nbsp;';
                                }

                                if ($inCart == false) {
                                    if ($showCart) {
                                        $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $personId . '">';
                                    }
                                    $res .= '                <span class="fa-stack">'
                                        .'                <i class="fas fa-square fa-stack-2x"></i>'
                                        .'                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                        .'                </span>';
                                    if ($showCart) {
                                        $res .= '                </a>  ';
                                    }
                                } else {
                                    if ($showCart) {
                                        $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $personId . '">';
                                    }
                                    $res .= '                <span class="fa-stack">'
                                        .'                <i class="fas fa-square fa-stack-2x"></i>'
                                        .'                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                        .'                </span>';
                                    if ($showCart) {
                                        $res .= '                </a>  ';
                                    }
                                }

                                $elt = [
                                    "id" => $per->getId(),
                                    "img" => $per->getJPGPhotoDatas(),
                                    "searchresult" => '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $per->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($per->getTitle(), $per->getFirstName(), $per->getMiddleName(), $per->getLastName(), $per->getSuffix(), 3) . '</a>',
                                    "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled())?_('Private Data'):$address,
                                    "type" => " "._($this->getGlobalSearchType()),
                                    "realType" => $this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => $per->getOpportunityName(),
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => "",
                                    "actions" => $res
                                ];
                            }

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
