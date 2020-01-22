<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\PastoralCareQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;


class FamilyPastoralCareSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Family Pastoral Care');
        parent::__construct();
    }

    public function buildSearch(string $qry)
    {
        if (SessionUser::getUser()->isPastoralCareEnabled() && SystemConfig::getBooleanValue("bSearchIncludePastoralCare")) {
            // now we search the families
            try {
                $searchLikeString = '%'.$qry.'%';
                $cares = PastoralCareQuery::Create()
                    ->leftJoinPastoralCareType()
                    ->leftJoinFamily()
                    ->filterByFamilyId(null, Criteria::NOT_EQUAL)
                    ->filterByText($searchLikeString, Criteria::LIKE)
                    ->_or()
                    ->useFamilyQuery()
                    ->filterByName($searchLikeString, Criteria::LIKE)
                    ->endUse()
                    ->_or()
                    ->usePastoralCareTypeQuery()
                    ->filterByTitle($searchLikeString, Criteria::LIKE)
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
                        $elt = ['id'=>"family-pastoral-care-id-".$id++,
                            'text'=>$care->getPastoralCareType()->getTitle() . " : " . $care->getFamily()->getName(),
                            'uri'=>SystemURLs::getRootPath() . "/v2/pastoralcare/family/".$care->getFamilyId()];

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
