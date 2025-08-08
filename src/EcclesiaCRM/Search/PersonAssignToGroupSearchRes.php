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

                $searchLikeString = '%'.$qry.'%';

                $ormAssignedGroups = Person2group2roleP2g2rQuery::Create()
                    ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
                    ->addMultipleJoin(array(array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID, ListOptionTableMap::COL_LST_OPTIONID), array(GroupTableMap::COL_GRP_ROLELISTID, ListOptionTableMap::COL_LST_ID)), Criteria::LEFT_JOIN)
                    ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
                    ->addAsColumn('roleName', ListOptionTableMap::COL_LST_OPTIONNAME)
                    ->addAsColumn('groupName', GroupTableMap::COL_GRP_NAME)
                    ->addAsColumn('hasSpecialProps', GroupTableMap::COL_GRP_HASSPECIALPROPS)
                    ->Where(ListOptionTableMap::COL_LST_OPTIONNAME . " LIKE '" . $searchLikeString . "' ORDER BY grp_Name");

                if ( $this->isQuickSearch() ) {
                    $ormAssignedGroups->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $ormAssignedGroups->find();

                if ( $ormAssignedGroups->count() > 0)
                {
                    $id=1;

                    foreach ($ormAssignedGroups as $per) {
                        if ( $this->isQuickSearch() ) {
                            $elt = ['id' => 'assigned-person-group-id-' . $id++,
                                'text' => $per->getPerson()->getFullName(),
                                'uri' => $per->getPerson()->getViewURI()
                            ];
                        } else {
                            $fam = $per->getPerson()->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $fam->getID() . '">' .
                                    $fam->getName() . MiscUtils::FormatAddressLine($per->getPerson()->getFamily()->getAddress1(), $per->getPerson()->getFamily()->getCity(), $per->getPerson()->getFamily()->getState()) .
                                    "</a>";
                            }

                            $inCart = Cart::PersonInCart($per->getPerson()->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res = '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/editor/' . $per->getPerson()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }

                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                . '</span>';

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $per->getPerson()->getId() . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                    . "                </span>\n";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $per->getPerson()->getId() . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n"
                                    . "                </span>\n";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            }
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '&nbsp;<a href="' . SystemURLs::getRootPath() . '/v2/people/person/print/' . $per->getPerson()->getId() . '"  data-toggle="tooltip" data-placement="top" title="' . _('Print') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-print fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>';
                            }

                            $elt = [
                                "id" => $per->getPerson()->getId(),
                                "img" => $per->getPerson()->getJPGPhotoDatas(),
                                "searchresult" => '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $per->getPerson()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($per->getPerson()->getTitle(), $per->getPerson()->getFirstName(), $per->getPerson()->getMiddleName(), $per->getPerson()->getLastName(),
                                        $per->getPerson()->getSuffix(), 3) . '</a> (<a href="'.SystemURLs::getRootPath().'/v2/group/'.$per->getGroupId().'/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">'.$per->getgroupName().'</a>)',
                                "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $address,
                                "type" => " " . _($this->getGlobalSearchType()),
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
