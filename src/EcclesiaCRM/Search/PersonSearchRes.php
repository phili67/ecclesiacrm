<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\PersonCustomTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Record2propertyR2pQuery;


class PersonSearchRes extends BaseSearchRes
{
    protected $query_elements = null;
    protected $group_elements = null;

    public function __construct($global = false, $query_elements = null, $group_elements = null)
    {
        $this->name = _('Persons');

        # only available in this subclass
        $this->query_elements = $query_elements;
        $this->group_elements = $group_elements;

        parent::__construct($global, "Persons");
    }

    public function buildSearch(string $qry)
    {
        //Person Search
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $searchLikeString = '%' . $qry . '%';
                $people = PersonQuery::create();

                if ($this->global_search) {// we are in the search project
                    /*
                     * $sSQL = "SELECT COALESCE(cls.lst_OptionName, 'Unassigned') AS ClassName, p.per_LastName, p.per_FirstName
                            FROM person_per p
                            LEFT JOIN list_lst cls ON (p.per_cls_ID=cls.lst_OptionID AND cls.lst_ID=1)
                            LEFT JOIN list_lst fmr ON (p.per_fam_ID=fmr.lst_OptionID AND fmr.lst_ID=2)
                            WHERE p.per_DateDeactivated IS NULL;
                     */
                    $people->addAlias('cls', ListOptionTableMap::TABLE_NAME)
                        ->addMultipleJoin(array(
                                array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID)),
                                array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
                            )
                            , Criteria::LEFT_JOIN)
                        ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
                        ->addMultipleJoin(array(
                                array(PersonTableMap::COL_PER_FAM_ID, ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_OPTIONID)),
                                array(ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_ID), 2)
                            )
                            , Criteria::LEFT_JOIN);

                    $people->addAsColumn('ClassName', "COALESCE(" . ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONNAME) . ", 'Unassigned')" );
                }


                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $people->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }

                if ($this->global_search) {// we are in the search project

                    if ( mb_strlen($qry) > 0 ) {
                        // now we search in the Property fields
                        $not_like = ""; // can be "NOT "
                        $criteria = Criteria::LIKE; // Criteria::NOT_LIKE

                        // Get the lists of custom person fields
                        $ormPersonCustomFields = PersonCustomMasterQuery::create()
                            ->orderByCustomOrder()
                            ->find();

                        $people->addJoin (PersonTableMap::COL_PER_ID, PersonCustomTableMap::COL_PER_ID, Criteria::LEFT_JOIN);

                        foreach ($ormPersonCustomFields as $customfield ) {
                            $people->withColumn($customfield->getCustomField());
                            $people->where($customfield->getCustomField()." ".$not_like." LIKE ?",$searchLikeString,\PDO::PARAM_STR );
                            $people->_or();
                        }

                        $people->_or()->filterByFirstName($searchLikeString, $criteria)
                            ->_or()->filterByLastName($searchLikeString, $criteria)
                            ->_or()->filterByEmail($searchLikeString, $criteria)
                            ->_or()->filterByWorkEmail($searchLikeString, $criteria)
                            ->_or()->filterByHomePhone($searchLikeString, $criteria)
                            ->_or()->filterByCellPhone($searchLikeString, $criteria)
                            ->_or()->filterByWorkPhone($searchLikeString, $criteria);
                    }

                    if (!is_null ($this->query_elements)) {
                        if (!is_null($this->query_elements['Gender'])) {
                            $people->_and()->filterByGender($this->query_elements['Gender']);
                        }
                        if (!is_null($this->query_elements['Classification'])) {
                            if ($this->query_elements['Classification'] < 0) {
                                $criteria = Criteria::NOT_EQUAL;
                                $this->query_elements['Classification'] += 10000;
                                $people->_and()->filterByClsId($this->query_elements['Classification'],$criteria);
                            } else {
                                $people->_and()->filterByClsId($this->query_elements['Classification']);
                            }
                        }
                        if (!is_null($this->query_elements['FamilyRole'])) {
                            if ($this->query_elements['FamilyRole'] < 0) {
                                $criteria = Criteria::NOT_EQUAL;
                                $this->query_elements['FamilyRole'] += 10000;
                                $people->_and()->filterByFmrId($this->query_elements['FamilyRole'],$criteria);
                            } else {
                                $people->_and()->filterByFmrId($this->query_elements['FamilyRole']);
                            }
                        }
                        if (!is_null($this->query_elements['PersonProperty'])) {
                            if ($this->query_elements['PersonProperty'] < 0) {
                                $this->query_elements['PersonProperty'] += 10000;

                                $people->addJoin(PersonTableMap::COL_PER_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
                                    ->where(PersonTableMap::COL_PER_ID. " NOT IN (SELECT " . Record2propertyR2pTableMap::COL_R2P_RECORD_ID  . " FROM record2property_r2p WHERE r2p_pro_ID=" . $this->query_elements['PersonProperty'] . ")"); //NOT LIKE 'a%';
                            } else {
                                $people->addJoin(PersonTableMap::COL_PER_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
                                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
                                    ->where(PropertyTableMap::COL_PRO_CLASS . "='p' AND " . Record2propertyR2pTableMap::COL_R2P_PRO_ID . " LIKE '" . $this->query_elements['PersonProperty'] . "'"); //NOT LIKE 'a%';
                            }
                        }
                    }

                    $people->find();

                    LoggerUtils::getAppLogger()->warn("coucou : ".print_r($this->query_elements,1));
                    LoggerUtils::getAppLogger()->warn("coucou : ".$people->toString());

                    if (!is_null($people)) {
                        $id = 1;

                        foreach ($people as $person) {
                            $ormAssignedProperties = Record2propertyR2pQuery::Create()
                                ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                                ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                                ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                                ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                                ->where(PropertyTableMap::COL_PRO_CLASS."='p'")
                                ->addAscendingOrderByColumn('ProName')
                                ->addAscendingOrderByColumn('ProTypeName')
                                ->findByR2pRecordId($person->getId());

                            $properties = "";
                            foreach ($ormAssignedProperties as $property) {
                                $properties = $properties.$property->getProName().", ";
                            }

                            $elt = ['id' => $person->getId(),
                                'text' => $person->getFullName(),
                                'uri' => $person->getViewURI(),
                                'Gender' => ($person->getGender() == 1)?_('Male'):_('Female'),
                                'FamilyRole' => $person->getFamilyRoleName(),
                                'Classification' => _($person->getClassName()),
                                'ProNames' => $properties,
                                'type' => _($this->getGlobalSearchType())];

                            array_push($this->results, $elt);
                        }
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
