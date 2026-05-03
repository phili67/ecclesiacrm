<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;


class PersonAssignToGroupSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Person Group role assignments');
        parent::__construct($global, "Person Group role assignments");
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
                $pos = mb_strpos (mb_strtoupper(_("Teacher")),mb_strtoupper($qry));

                if ($pos === 0) {
                    $len = mb_strlen($qry);
                    $qry = mb_substr("teacher",0,$len);
                } else {
                    $pos = mb_strpos (mb_strtoupper(_("Student")),mb_strtoupper($qry));

                    if ($pos === 0) {
                        $len = mb_strlen($qry);
                        $qry = mb_substr("student",0,$len);
                    }
                }

                $searchLikeString = '%' . str_replace('*', '%', $qry) . '%';

                $ormAssignedGroups = Person2group2roleP2g2rQuery::Create()
                    ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
                    ->addMultipleJoin(array(array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID, ListOptionTableMap::COL_LST_OPTIONID), array(GroupTableMap::COL_GRP_ROLELISTID, ListOptionTableMap::COL_LST_ID)), Criteria::LEFT_JOIN)
                    ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
                    ->addAsColumn('roleName', ListOptionTableMap::COL_LST_OPTIONNAME)
                    ->addAsColumn('groupName', GroupTableMap::COL_GRP_NAME)
                    ->addAsColumn('hasSpecialProps', GroupTableMap::COL_GRP_HASSPECIALPROPS)
                    ->leftJoinPerson()
                    ->usePersonQuery()
                        ->leftJoinWithFamily()
                    ->endUse()
                    ->Where(ListOptionTableMap::COL_LST_OPTIONNAME . " LIKE '" . $searchLikeString 
                    . "' OR ". PersonTableMap::COL_PER_FIRSTNAME . " LIKE '" . $searchLikeString 
                    . "' OR " . PersonTableMap::COL_PER_LASTNAME . " LIKE '" . $searchLikeString . "' ORDER BY " . GroupTableMap::COL_GRP_NAME);

                $quickSearch = $this->isQuickSearch();

                if ($quickSearch) {
                    $ormAssignedGroups->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $ormAssignedGroups = $ormAssignedGroups->find();

                $shouldShowCart = $currentUser->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $shouldSeePrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $peopleInCart = $shouldShowCart ? array_fill_keys(Cart::PeopleInCart(), true) : [];
                

                if ( $ormAssignedGroups->count() > 0)
                {
                    $id=1;

                    foreach ($ormAssignedGroups as $per) {
                        $person = $per->getPerson();

                        if ($person === null) {
                            continue;
                        }

                        $personId = $person->getId();

                        if ($quickSearch) {
                            $elt = ['id' => 'assigned-person-group-id-' . $id++,
                                'text' => $person->getFullName(),
                                'uri' => $person->getViewURI()
                            ];
                        } else {
                            $fam = $person->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="' . $rootPath . '/v2/people/family/view/' . $fam->getID() . '">' .
                                    $fam->getName() . MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState()) .
                                    "</a>";
                            }

                            $inCart = isset($peopleInCart[$personId]);

                            $res = "";
                            if ($shouldShowCart) {
                                $res = '<a href="' . $rootPath . '/v2/people/person/editor/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }

                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                . '</span>';

                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if ($shouldShowCart) {
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $personId . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                    . "                </span>\n";
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if ($shouldShowCart) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $personId . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n"
                                    . "                </span>\n";
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            }
                            if ($shouldShowCart) {
                                $res .= '&nbsp;<a href="' . $rootPath . '/v2/people/person/print/' . $personId . '"  data-toggle="tooltip" data-placement="top" title="' . _('Print') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-print fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>';
                            }

                            $elt = [
                                "id" => $personId,
                                "img" => $person->getJPGPhotoDatas(),
                                "searchresult" => '<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(),
                                    $person->getSuffix(), 3) . '</a> (<a href="'.$rootPath.'/v2/group/'.$per->getGroupId().'/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">'.$per->getgroupName().'</a>)',
                                "address" => (!$shouldSeePrivacyData) ? _('Private Data') : $address,
                                "type" => " " . _($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => $per->getGroupName() . " : " . $per->getRoleName(),// . " (" . ($per->getHasSpecialProps() ? _("Yes") : _("No")) . ")",
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
