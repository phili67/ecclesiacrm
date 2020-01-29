<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\PersonCustomMasterQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;


class PersonCustomSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Person Custom Search');
        parent::__construct();
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

                $personsCustom->limit(SystemConfig::getValue("iSearchIncludePersonsMax"))
                    ->find();

                if (!is_null($personsCustom))
                {
                    $id=1;

                    foreach ($personsCustom as $per) {
                        $elt = ['id' => 'person-custom-id-'.$id++,
                            'text' => $per->getPerson()->getFullName(),
                            'uri' => $per->getPerson()->getViewURI()
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
