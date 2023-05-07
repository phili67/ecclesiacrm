<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\GroupTypeTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;


class PersonSearchRes extends BaseSearchRes
{
    protected $query_elements      = null;
    protected $group_elements      = null;
    protected $group_role_elements = null;

    public function __construct($global = false, $query_elements = null, $group_elements = null, $group_role_elements = null)
    {
        $this->name = _('Persons');

        # only available in this subclass
        $this->query_elements      = $query_elements;
        $this->group_elements      = $group_elements;
        $this->group_role_elements = $group_role_elements;

        parent::__construct($global, "Persons");
    }

    public function buildSearch(string $qry)
    {
        //Person Search
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $searchLikeString = '%' . $qry . '%';
                $people = PersonQuery::create();
                $people->setDistinct(PersonTableMap::COL_PER_ID);



                $iTenThousand = 10000;

                $isGlobalSearch = $this->isGlobalSearch();
                $isStringSearch = $this->isStringSearch();

                if ( $isGlobalSearch or $isStringSearch ) {// we are in the global search project

                    /*
                     * $sSQL = "SELECT COALESCE(cls.lst_OptionName, 'Unassigned') AS ClassName, p.per_LastName, p.per_FirstName
                            FROM person_per p
                            LEFT JOIN list_lst cls ON (p.per_cls_ID=cls.lst_OptionID AND cls.lst_ID=1)
                            LEFT JOIN list_lst fmr ON (p.per_fam_ID=fmr.lst_OptionID AND fmr.lst_ID=2)
                            WHERE p.per_DateDeactivated IS NULL;
                     */
                    $people->addAlias('cls', ListOptionTableMap::TABLE_NAME)
                        ->addMultipleJoin(array(
                                array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_OPTIONID)),
                                array(ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_ID), 1)
                            )
                            , Criteria::LEFT_JOIN)
                        ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
                        ->addMultipleJoin(array(
                                array(PersonTableMap::COL_PER_FAM_ID, ListOptionTableMap::Alias("fmr", ListOptionTableMap::COL_LST_OPTIONID)),
                                array(ListOptionTableMap::Alias("fmr", ListOptionTableMap::COL_LST_ID), 2)
                            )
                            , Criteria::LEFT_JOIN);

                    $people->addAsColumn('ClassName', "COALESCE(" . ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_OPTIONNAME) . ", 'Unassigned')");

                    if (mb_strlen($qry) > 0) {
                        // now we search in the Property fields
                        $not_like = ""; // can be "NOT "
                        $criteria = Criteria::LIKE; // Criteria::NOT_LIKE

                        // Get the lists of custom person fields
                        /*$ormPersonCustomFields = PersonCustomMasterQuery::create()
                            ->orderByCustomOrder()
                            ->find();

                        $people->addJoin (PersonTableMap::COL_PER_ID, PersonCustomTableMap::COL_PER_ID, Criteria::LEFT_JOIN);

                        foreach ($ormPersonCustomFields as $customfield ) {
                            $people->withColumn($customfield->getCustomField());
                            $people->where($customfield->getCustomField()." ".$not_like." LIKE ?",$searchLikeString,\PDO::PARAM_STR );
                            $people->_or();
                        }*/

                        $people->_or()->filterByFirstName($searchLikeString, $criteria)
                            ->_or()->filterByLastName($searchLikeString, $criteria)
                            ->_or()->filterByEmail($searchLikeString, $criteria)
                            ->_or()->filterByWorkEmail($searchLikeString, $criteria)
                            ->_or()->filterByHomePhone($searchLikeString, $criteria)
                            ->_or()->filterByCellPhone($searchLikeString, $criteria)
                            ->_or()->filterByWorkPhone($searchLikeString, $criteria);
                    }

                    if (!is_null($this->query_elements)) {
                        if (array_key_exists('Gender',$this->query_elements)) {
                            $people->_and()->filterByGender($this->query_elements['Gender']);
                        }
                        if (array_key_exists('Classification',$this->query_elements)) {
                            if ($this->query_elements['Classification'] < 0) {
                                $criteria = Criteria::NOT_EQUAL;
                                $this->query_elements['Classification'] += $iTenThousand;
                                $people->_and()->filterByClsId($this->query_elements['Classification'], $criteria);
                            } else {
                                $people->_and()->filterByClsId($this->query_elements['Classification']);
                            }
                        }
                        if (array_key_exists('FamilyRole',$this->query_elements)) {
                            if ($this->query_elements['FamilyRole'] < 0) {
                                $criteria = Criteria::NOT_EQUAL;
                                $this->query_elements['FamilyRole'] += $iTenThousand;
                                $people->_and()->filterByFmrId($this->query_elements['FamilyRole'], $criteria);
                            } else {
                                $people->_and()->filterByFmrId($this->query_elements['FamilyRole']);
                            }
                        }

                        if (array_key_exists('GroupType',$this->query_elements)) {

                            if ($this->query_elements['GroupType'] < 0) {
                                /*$sGroupWhereExt = ' AND per_ID NOT IN (SELECT p2g2r_per_ID '.
                                ' FROM person2group2role_p2g2r '.
                                ' LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID '.
                                ' LEFT JOIN group_type ON grptp_grp_ID=grp_ID'.
                                ' WHERE grptp_lst_OptionID = '.($iGroupType + $iTenThousand).')';*/

                                $people->where(PersonTableMap::COL_PER_ID . ' NOT IN (SELECT p2g2r_per_ID
                                        FROM person2group2role_p2g2r
                                        LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
                                        LEFT JOIN group_type ON grptp_grp_ID=grp_ID
                                        WHERE grptp_lst_OptionID = ' . ($this->query_elements['GroupType'] + $iTenThousand) . ')');

                            } else {
                                /*$sSQLsub = 'SELECT per_ID
                                    FROM person_per
                                    LEFT JOIN person2group2role_p2g2r  ON p2g2r_per_ID = per_ID
                                    LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
                                    LEFT JOIN group_type ON grptp_grp_ID=grp_ID
                                    WHERE grptp_lst_OptionID = ' . $iGroupTypeMissing .' GROUP BY per_ID';*/

                                $sGroupWhereExt = GroupTypeTableMap::COL_GRPTP_LST_OPTIONID . " = " . $this->query_elements['GroupType'];

                                if (!is_null($this->group_elements['Group']) && $this->group_elements['Group'] >= 0) {
                                    if (!is_null($this->group_role_elements['Role']) && $this->group_role_elements['Role'] >= 0) {
                                        /*$sJoinExt = ' LEFT JOIN person2group2role_p2g2r '.
                                                    ' ON per_ID = p2g2r_per_ID '.
                                                    ' LEFT JOIN list_lst '.
                                                    ' ON p2g2r_grp_ID = lst_ID ';

                                        $sGroupWhereExt = ' AND p2g2r_grp_ID='.$iGroupID.' '.
                                                            ' AND p2g2r_per_ID=per_ID '.
                                                            ' AND p2g2r_rle_ID='.$iRoleID.' ';*/

                                        $people->addJoin(PersonTableMap::COL_PER_ID, Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID, Criteria::LEFT_JOIN)
                                            ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
                                            ->addJoin(GroupTableMap::COL_GRP_ID, GroupTypeTableMap::COL_GRPTP_GRP_ID, Criteria::LEFT_JOIN)
                                            ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, ListOptionTableMap::COL_LST_ID, Criteria::LEFT_JOIN);

                                        $sGroupWhereExt .= ' AND p2g2r_grp_ID=' . $this->group_elements['Group'] . ' ' .
                                            ' AND p2g2r_per_ID = per_ID' .
                                            ' AND p2g2r_rle_ID=' . $this->group_role_elements['Role'] . ' ';

                                    } else {
                                        /*$sJoinExt = ' LEFT JOIN person2group2role_p2g2r '.
                                          ' ON per_ID = p2g2r_per_ID ';

                                            $sGroupWhereExt = ' AND p2g2r_grp_ID='.$iGroupID.' '.
                                          ' AND p2g2r_per_ID = per_ID ';*/

                                        $people->addJoin(PersonTableMap::COL_PER_ID, Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID, Criteria::LEFT_JOIN)
                                            ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
                                            ->addJoin(GroupTableMap::COL_GRP_ID, GroupTypeTableMap::COL_GRPTP_GRP_ID, Criteria::LEFT_JOIN);

                                        $sGroupWhereExt .= ' AND p2g2r_grp_ID=' . $this->group_elements['Group'] . ' ' .
                                            ' AND p2g2r_per_ID = per_ID';
                                    }
                                } else {
                                    $people->addJoin(PersonTableMap::COL_PER_ID, Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID, Criteria::LEFT_JOIN)
                                        ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
                                        ->addJoin(GroupTableMap::COL_GRP_ID, GroupTypeTableMap::COL_GRPTP_GRP_ID, Criteria::LEFT_JOIN);

                                }

                                $people->where($sGroupWhereExt);
                            }
                        }


                        if (!is_null($this->query_elements['PersonProperty'])) {
                            if ($this->query_elements['PersonProperty'] < 0) {
                                $this->query_elements['PersonProperty'] += $iTenThousand;

                                $people->addJoin(PersonTableMap::COL_PER_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
                                    ->where(PersonTableMap::COL_PER_ID . " NOT IN (SELECT " . Record2propertyR2pTableMap::COL_R2P_RECORD_ID . " FROM record2property_r2p WHERE r2p_pro_ID=" . $this->query_elements['PersonProperty'] . ")"); //NOT LIKE 'a%';
                            } else {
                                $people->addJoin(PersonTableMap::COL_PER_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
                                    ->where(PropertyTableMap::COL_PRO_CLASS . "='p' AND " . Record2propertyR2pTableMap::COL_R2P_PRO_ID . " LIKE '" . $this->query_elements['PersonProperty'] . "'"); //NOT LIKE 'a%';
                            }
                        }
                    }

                    if (SystemConfig::getBooleanValue('bGDPR')) {
                        $people->_and()->filterByDateDeactivated(null);// GDPR, when a person is completely deactivated
                    }

                    $people->find();

                    if ( $people->count() > 0 ) {
                        $id = 1;
                        $res_buffer = [];

                        foreach ($people as $person) {
                            $ormAssignedProperties = Record2propertyR2pQuery::Create()
                                ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
                                ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
                                ->addAsColumn('ProName', PropertyTableMap::COL_PRO_NAME)
                                ->addAsColumn('ProTypeName', PropertyTypeTableMap::COL_PRT_NAME)
                                ->where(PropertyTableMap::COL_PRO_CLASS . "='p'")
                                ->addAscendingOrderByColumn('ProName')
                                ->addAscendingOrderByColumn('ProTypeName')
                                ->findByR2pRecordId($person->getId());

                            $properties = "";
                            foreach ($ormAssignedProperties as $property) {
                                $properties = $properties . $property->getProName() . ", ";
                            }

                            $fam = $person->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $fam->getID() . '">' .
                                    $fam->getName() . MiscUtils::FormatAddressLine($person->getFamily()->getAddress1(), $person->getFamily()->getCity(), $person->getFamily()->getState()) .
                                    "</a>";
                            }

                            $inCart = Cart::PersonInCart($person->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/PersonEditor.php?PersonID=' . $person->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
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
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $person->getId() . '">';
                                }
                                $res .= "\n"
                                    . "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                    . "                </span>\n";

                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $person->getId() . '">';
                                }
                                $res .= "\n"
                                    . "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n"
                                    . "                </span>\n";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '&nbsp;<a href="' . SystemURLs::getRootPath() . '/PrintView.php?PersonID=' . $person->getId() . '"  data-toggle="tooltip" data-placement="top" title="' . _('Print') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-print fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>';
                            }

                            if ( $isStringSearch ) {
                                $tableOfRes = [$person->getFirstName(), $person->getLastName(), $person->getEmail(), $person->getWorkEmail(),
                                    $person->getHomePhone(), $person->getCellPhone(), $person->getWorkPhone() ];

                                if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                                    array_merge($tableOfRes, [_($person->getClassName()), $properties, $person->getFamilyRoleName()]);
                                }

                                foreach ($tableOfRes as $item){
                                    if (mb_strpos( mb_strtolower($item),mb_strtolower($qry)) !== false and !in_array($item, $res_buffer)){
                                        $elt = ['id' => 'searchname-person-id-' . ($id++),
                                            'text' => $item,
                                            'uri' => ""];
                                        array_push($this->results, $elt);
                                        array_push($res_buffer, $item);
                                    }
                                }
                            } else {
                                $elt = [
                                    'id' => $person->getId(),
                                    'searchresult' => '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $person->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) . '</a>',
                                    'img' => '<img src="/api/persons/' . $person->getId() . '/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">',
                                    'address' => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $address,
                                    'type' => _($this->getGlobalSearchType()),
                                    'realType' => $this->getGlobalSearchType(),
                                    'Gender' => ($person->getGender() == 1) ? _('Male') : _('Female'),
                                    'Classification' => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : _($person->getClassName()),
                                    'ProNames' => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $properties,
                                    'FamilyRole' => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $person->getFamilyRoleName(),
                                    "members" => "",
                                    'actions' => $res
                                ];

                                array_push($this->results, $elt);
                            }
                        }
                        $this->results = array_unique($this->results,SORT_ASC);
                    }
                } else {// not global search
                    $people->filterByFirstName($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByLastName($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByEmail($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByWorkEmail($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByHomePhone($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByCellPhone($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByWorkPhone($searchLikeString, Criteria::LIKE)
                        ->limit(SystemConfig::getValue("iSearchIncludePersonsMax"))
                        ->find();

                    if (!is_null($people)) {
                        $id = 1;

                        foreach ($people as $person) {
                            if ($person->getDateDeactivated() != NULL) continue;

                            $elt = ['id' => 'person-id-' . $id++,
                                'text' => $person->getFullName(),
                                'uri' => $person->getViewURI()];

                            array_push($this->results, $elt);
                        }
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
