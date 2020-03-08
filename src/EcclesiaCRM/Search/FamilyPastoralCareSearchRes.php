<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\PastoralCareQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;


class FamilyPastoralCareSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Family Pastoral Care');
        parent::__construct($global, " Family Pastoral Care");
    }

    public function buildSearch(string $qry)
    {
        if (SessionUser::getUser()->isPastoralCareEnabled() && SystemConfig::getBooleanValue("bSearchIncludePastoralCare")) {
            // now we search the families
            try {
                $searchLikeString = '%'.$qry.'%';
                $cares = PastoralCareQuery::Create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $cares
                        ->useFamilyQuery()
                        ->filterByDateDeactivated(null)
                        ->endUse()
                        ->_and();
                }

                $cares->leftJoinPastoralCareType()
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
                    ->orderByDate(Criteria::DESC);

                if ($this->global_search) {
                    $cares->limit(SystemConfig::getValue("iSearchIncludePastoralCareMax"));
                }

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

                        $members = $care->getFamily()->getPeopleSorted();

                        $res_members = [];
                        $globalMembers = "";

                        foreach ($members as $member) {
                            $res_members[] = $member->getId();
                            $globalMembers .= '• <a href="'.SystemURLs::getRootPath().'/PersonView.php?PersonID='.$member->getId().'">'.$member->getFirstName()." ".$member->getLastName()."</a><br>";
                        }

                        $elt["text"] = _("Family Pastoral Care").' : <a href="'.SystemURLs::getRootPath().'/FamilyView.php?FamilyID='.$care->getFamily()->getId().'" data-toggle="tooltip" data-placement="top" data-original-title="'._('Edit').'">'.$care->getFamily()->getName().'</a>'." "._("Members")." : <br>".$globalMembers;
                        $elt["id"] = $care->getFamily()->getId();
                        $elt["address"] = $care->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH"));
                        $elt["type"] = _($this->getGlobalSearchType());
                        $elt["realType"] = $this->getGlobalSearchType();
                        $elt["Gender"] = "";
                        $elt["Classification"] = "";
                        $elt["ProNames"] = "";
                        $elt["FamilyRole"] = "";
                        $elt["inCart"] = Cart::FamilyInCart($care->getFamily()->getId());
                        $elt["members"] = $res_members;

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
