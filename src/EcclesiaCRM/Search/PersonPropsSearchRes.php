<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\Base\PersonQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;


class PersonPropsSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Person Properties");
        parent::__construct($global,"Person Properties");
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isSeePrivacyDataEnabled();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $currentUser = SessionUser::getUser();
                $shouldShowCart = $currentUser->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $shouldSeePrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $quickSearch = $this->isQuickSearch();
                $peopleInCart = $shouldShowCart ? array_fill_keys(Cart::PeopleInCart(), true) : [];
                $normalizedQuery = str_replace('*', '%', $qry);

                // it's possible to include the properties not
                $not_like = "";
                if ($qry[0] == "!") {
                    $qry = substr($qry,1);
                    $not_like = "NOT ";
                    $normalizedQuery = str_replace('*', '%', $qry);
                }
                $searchLikeString = '%' . $normalizedQuery . '%';

                $person_Props = PersonQuery::create()
                    ->leftJoinWithFamily();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $person_Props->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }

                $person_Props->addJoin(PersonTableMap::COL_PER_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID,Criteria::LEFT_JOIN)
                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                    ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                    ->addAsColumn('ProValue',Record2propertyR2pTableMap::COL_R2P_VALUE)
                    ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                    ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                    ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                    ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                    ->where(PropertyTableMap::COL_PRO_CLASS."='p' AND (".PropertyTableMap::COL_PRO_NAME." ".$not_like."LIKE '".$searchLikeString."'  OR " . Record2propertyR2pTableMap::COL_R2P_VALUE . " LIKE '" . $searchLikeString . "' )"
                    . " OR " . PersonTableMap::COL_PER_FIRSTNAME . " LIKE '" . $searchLikeString . "' OR " . PersonTableMap::COL_PER_LASTNAME . " LIKE '" . $searchLikeString . "'") //NOT LIKE 'a%';
                    ->addAscendingOrderByColumn('ProName')
                    ->addAscendingOrderByColumn('ProTypeName');

                if ($quickSearch) {
                    $person_Props->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $person_Props = $person_Props->find();
                    

                if ( $person_Props->count() > 0 )
                {
                    $id=1;

                    

                    foreach ($person_Props as $per) {
                                            $personId = $per->getId();

                                            if ($quickSearch) {
                            $elt = ['id' => 'person-props-id-' . $id++,
                                'text' => $per->getFullName() . " (" . $per->getProName() . ")",
                                'uri' => $per->getViewURI()
                            ];
                        } else {
                            $fam = $per->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="'.$rootPath.'/v2/people/family/view/'.$fam->getID().'">'.
                                    $fam->getName().MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState()).
                                    "</a>";
                            }

                            $inCart = isset($peopleInCart[$personId]);

                            $res = "";

                            if ($shouldShowCart) {
                                $res .= '<a href="' . $per->getViewURI() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if ($shouldShowCart) {
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $personId . '">';
                                }

                                $res .= "                <span class=\"fa-stack\">\n"
                                    ."                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    ."                <i class=\"fas fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                    ."                </span>\n";
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if ($shouldShowCart) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $personId . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">"
                                    ."                <i class=\"fas fa-square fa-stack-2x\"></i>"
                                    ."                <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n"
                                    ."                </span>";
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            }

                            if ($shouldShowCart) {
                                $res .= '&nbsp;<a href="' . $rootPath . '/v2/people/person/print/' . $personId . '"  data-toggle="tooltip" data-placement="top" title="' . _('Print') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-print fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>';
                            }

                            $elt = [
                                "id" => $personId,
                                "img" => $per->getJPGPhotoDatas(),
                                "searchresult" => '<a href="'.$rootPath.'/v2/people/person/view/'.$personId.'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.OutputUtils::FormatFullName($per->getTitle(), $per->getFirstName(), $per->getMiddleName(), $per->getLastName(), $per->getSuffix(), 3).'</a>',
                                "address" => (!$shouldSeePrivacyData)?_('Private Data'):$address,
                                "type" => " "._($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => $per->getProName() . (!empty($per->getProValue()) ? " : " . $per->getProValue() : ""),
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
