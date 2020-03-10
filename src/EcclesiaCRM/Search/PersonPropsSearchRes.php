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
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;


class PersonPropsSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Person Properties Search');
        parent::__construct($global,'Person Properties Search');
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {

                // it's possible to include the properties not
                $not_like = "";
                if ($qry[0] == "!") {
                    $qry = substr($qry,1);
                    $not_like = "NOT ";
                }
                $searchLikeString = '%'.$qry.'%';

                $person_Props = PersonQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $person_Props->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }

                $person_Props->addJoin(PersonTableMap::COL_PER_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID,Criteria::LEFT_JOIN)
                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                    ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                    ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                    ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                    ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                    ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                    ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                    ->where(PropertyTableMap::COL_PRO_CLASS."='p' AND ".PropertyTableMap::COL_PRO_NAME." ".$not_like."LIKE '".$searchLikeString."'") //NOT LIKE 'a%';
                    ->addAscendingOrderByColumn('ProName')
                    ->addAscendingOrderByColumn('ProTypeName');

                if (!$this->global_search) {
                    $person_Props->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $person_Props->find();


                if (!is_null($person_Props))
                {
                    $id=1;

                    foreach ($person_Props as $per) {
                        $elt = ['id' => 'person-props-id-'.$id++,
                            'text' => $per->getFullName()." (".$per->getProName().")",
                            'uri' => $per->getViewURI()
                        ];

                        if ($this->global_search) {
                            $fam = $per->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="'.SystemURLs::getRootPath().'/FamilyView.php?FamilyID='.$fam->getID().'">'.
                                    $fam->getName().MiscUtils::FormatAddressLine($per->getFamily()->getAddress1(), $per->getFamily()->getCity(), $per->getFamily()->getState()).
                                    "</a>";
                            }

                            $elt["id"] = $per->getId();
                            $elt["address"] = $address;
                            $elt["type"] = _($this->getGlobalSearchType());
                            $elt["realType"] = $this->getGlobalSearchType();
                            $elt["Gender"] = "";
                            $elt["Classification"] = "";
                            $elt["ProNames"] = "";
                            $elt["FamilyRole"] = "";
                            $elt["inCart"] = Cart::PersonInCart($per->getId());
                        }

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
