<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\PersonCustomMasterQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;


class PersonCustomSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Person Custom Search');
        parent::__construct($global, "Person Custom Search");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $searchLikeString = '%'.$qry.'%';

                // Get the lists of custom person fields
                $ormPersonCustomFields = PersonCustomMasterQuery::create()
                    ->orderByCustomOrder()
                    ->find();

                $personsCustom = PersonCustomQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $personsCustom->usePersonQuery()
                        ->filterByDateDeactivated(null)
                        ->endUse();
                }

                foreach ($ormPersonCustomFields as $customfield ) {
                    $personsCustom->withColumn($customfield->getCustomField());
                    $personsCustom->where($customfield->getCustomField()." LIKE ?",$searchLikeString,\PDO::PARAM_STR );
                    $personsCustom->_or();
                }

                if (!$this->global_search) {
                    $personsCustom->limit(SystemConfig::getValue("iSearchIncludePersonsMax"))
                        ->find();
                }

                if (!is_null($personsCustom))
                {
                    $id=1;

                    foreach ($personsCustom as $per) {
                        $elt = ['id' => 'person-custom-id-'.$id++,
                            'text' => $per->getPerson()->getFullName(),
                            'uri' => $per->getPerson()->getViewURI()
                        ];



                        if ($this->global_search) {
                            $fam = $per->getPerson()->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="'.SystemURLs::getRootPath().'/FamilyView.php?FamilyID='.$fam->getID().'">'.
                                    $fam->getName().MiscUtils::FormatAddressLine($per->getPerson()->getFamily()->getAddress1(), $per->getPerson()->getFamily()->getCity(), $per->getPerson()->getFamily()->getState()).
                                    "</a>";
                            }

                            $elt["id"] = $per->getPerson()->getId();
                            $elt["address"] = $address;
                            $elt["type"] = _($this->getGlobalSearchType());
                            $elt["realType"] = $this->getGlobalSearchType();
                            $elt["Gender"] = "";
                            $elt["Classification"] = "";
                            $elt["ProNames"] = "";
                            $elt["FamilyRole"] = "";
                            $elt["inCart"] = Cart::PersonInCart($per->getPerson()->getId());
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
