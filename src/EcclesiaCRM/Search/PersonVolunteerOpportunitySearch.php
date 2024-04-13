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

    public function buildSearch(string $qry)
    {
        if ( SystemConfig::getBooleanValue("bSearchIncludePersons") ) {
            try {
                $searchLikeString = '%' . $qry . '%';
                $pers = PersonQuery::create();


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

                if ( $this->isQuickSearch() ) {
                    $pers->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $pers->find();


                if ( $pers->count() > 0) {
                    $id=1;

                    foreach ($pers as $per) {
                        if ( $this->isQuickSearch() ) {
                            $elt = ['id' => "person-vol-id-" . $id++,
                                'text' => $per->getTitle() . " : " . $per->getLastName() . " " . $per->getFirstName(),
                                'uri' => SystemURLs::getRootPath() . "/v2/people/person/view/" . $per->getId()];
                        } else {
                            $fam = $per->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="'.SystemURLs::getRootPath().'/v2/people/family/view/'.$fam->getId().'">'.
                                    $fam->getName().MiscUtils::FormatAddressLine($per->getFamily()->getAddress1(), $per->getFamily()->getCity(), $per->getFamily()->getState()).
                                    "</a>";
                            }

                            $inCart = Cart::PersonInCart($per->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $per->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $per->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '                </a>  ';
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $per->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '                </a>  ';
                                }
                            }
                    
                            $elt = [
                                "id" => $per->getId(),
                                "img" => $per->getPNGPhotoDatas(),
                                "searchresult" => '<a href="'.SystemURLs::getRootPath().'/v2/people/person/view/'.$per->getId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.OutputUtils::FormatFullName($per->getTitle(), $per->getFirstName(), $per->getMiddleName(), $per->getLastName(), $per->getSuffix(), 3).'</a> ('.$per->getOpportunityName().")",
                                "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled())?_('Private Data'):$address,
                                "type" => " "._($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => "",
                                "ProNames" => "",
                                "FamilyRole" => "",
                                "members" => "",
                                "actions" => $res
                            ];
                        }

                        array_push($this->results, $elt);
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
