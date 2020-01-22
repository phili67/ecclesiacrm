<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\PastoralCareQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;


class PersonPastoralCareSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Individual Pastoral Care');
        parent::__construct();
    }

    public function buildSearch(string $qry)
    {
        if (SessionUser::getUser()->isPastoralCareEnabled() && SystemConfig::getBooleanValue("bSearchIncludePastoralCare")) {
            try {
                $searchLikeString = '%' . $qry . '%';
                $cares = PastoralCareQuery::Create()
                    ->leftJoinPastoralCareType()
                    ->joinPersonRelatedByPersonId()
                    ->filterByPersonId(null, Criteria::NOT_EQUAL)
                    ->filterByText($searchLikeString, Criteria::LIKE)
                    ->_or()
                    ->usePastoralCareTypeQuery()
                    ->filterByTitle($searchLikeString, Criteria::LIKE)
                    ->endUse()
                    ->_or()
                    ->usePersonRelatedByPersonIdQuery()
                    ->filterByLastName($searchLikeString, Criteria::LIKE)
                    ->_or()
                    ->filterByFirstName($searchLikeString, Criteria::LIKE)
                    ->endUse()
                    ->orderByDate(Criteria::DESC)
                    ->limit(SystemConfig::getValue("iSearchIncludePastoralCareMax"));

                if (SessionUser::getUser()->isAdmin()) {
                    $cares->find();
                } else {
                    $cares->findByPastorId(SessionUser::getUser()->getPerson()->getId());
                }

                if (!is_null($cares)) {
                    $id=1;

                    foreach ($cares as $care) {
                        $elt = ['id' => "person-pastoralcare-".$id++,
                            'text' => $care->getPastoralCareType()->getTitle() . " : " . $care->getPersonRelatedByPersonId()->getFullName(),
                            'uri' => SystemURLs::getRootPath() . "/v2/pastoralcare/person/" . $care->getPersonId()];

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
