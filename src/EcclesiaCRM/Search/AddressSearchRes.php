<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;


class AddressSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Address');
        parent::__construct($global, "Adresses");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeAddresses")) {
            try {
                $searchLikeString = '%'.$qry.'%';
                $addresses = FamilyQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $addresses->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }


                $addresses->filterByCity($searchLikeString, Criteria::LIKE)->
                _or()->filterByAddress1($searchLikeString, Criteria::LIKE)->
                _or()->filterByAddress2($searchLikeString, Criteria::LIKE)->
                _or()->filterByZip($searchLikeString, Criteria::LIKE)->
                _or()->filterByState($searchLikeString, Criteria::LIKE);

                if (!$this->global_search) {
                    $addresses->limit(SystemConfig::getValue("iSearchIncludeAddressesMax"))->find();
                }

                if (!is_null($addresses))
                {
                    $id=1;

                    foreach ($addresses as $address) {
                        $elt = ['id' => 'address-id-'.$id++,
                            'text'=>$address->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                            'uri'=>$address->getViewURI()
                        ];

                        if ($this->global_search) {
                            $elt["type"] = $this->getGlobalSearchType();
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
