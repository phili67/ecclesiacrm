<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\Base\FamilyCustomMasterQuery;
use EcclesiaCRM\Base\FamilyCustomQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;


class FamilyCustomSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Family Custom Search');
        parent::__construct();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeFamilies")) {
            try {
                $searchLikeString = '%'.$qry.'%';

                // Get the lists of custom person fields
                $ormFamiliesCustomFields = FamilyCustomMasterQuery::create()
                    ->orderByCustomOrder()
                    ->find();

                $familiesCustom = FamilyCustomQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $familiesCustom->useFamilyQuery()
                        ->filterByDateDeactivated(null)
                        ->endUse();
                }

                foreach ($ormFamiliesCustomFields as $customfield ) {
                    $familiesCustom->withColumn($customfield->getCustomField());
                    $familiesCustom->where($customfield->getCustomField()." LIKE ?",$searchLikeString,\PDO::PARAM_STR );
                    $familiesCustom->_or();
                }

                $familiesCustom
                    ->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"))
                    ->find();


                if (!is_null($familiesCustom))
                {
                    $id=1;

                    foreach ($familiesCustom as $fam) {
                        $elt = ['id' => 'family-custom-id-'.$id++,
                            "text" => $fam->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                            "uri" => $fam->getFamily()->getViewURI()
                        ];

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
