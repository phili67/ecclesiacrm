<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;


class PersonSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Persons');
        parent::__construct();
    }

    public function buildSearch(string $qry)
    {
        //Person Search
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $searchLikeString = '%' . $qry . '%';
                $people = PersonQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $people->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }

                $people->filterByFirstName($searchLikeString, Criteria::LIKE)->
                _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
                _or()->filterByEmail($searchLikeString, Criteria::LIKE)->
                _or()->filterByWorkEmail($searchLikeString, Criteria::LIKE)->
                _or()->filterByHomePhone($searchLikeString, Criteria::LIKE)->
                _or()->filterByCellPhone($searchLikeString, Criteria::LIKE)->
                _or()->filterByWorkPhone($searchLikeString, Criteria::LIKE)->
                limit(SystemConfig::getValue("iSearchIncludePersonsMax"))->find();

                if (!is_null($people)) {
                    $id = 1;

                    foreach ($people as $person) {
                        $elt = ['id' => 'person-id-'.$id++,
                            'text' => $person->getFullName(),
                            'uri' => $person->getViewURI()];

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
