<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;


class PersonPastoralCareSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Individual Pastoral Care');
        parent::__construct($global,'Individual Pastoral Care');
    }

    public function buildSearch(string $qry)
    {
        if (SessionUser::getUser()->isPastoralCareEnabled() && SystemConfig::getBooleanValue("bSearchIncludePastoralCare")) {
            try {
                $searchLikeString = '%' . $qry . '%';
                $cares = PastoralCareQuery::Create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $cares
                        ->usePersonRelatedByPersonIdQuery()
                            ->filterByDateDeactivated(null)
                        ->endUse()
                        ->_and();
                }

                $cares->leftJoinPastoralCareType()
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
                    ->orderByDate(Criteria::DESC);

                if (!$this->global_search) {
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
                        $elt = ['id' => "person-pastoralcare-id-".$id++,
                            'text' => $care->getPastoralCareType()->getTitle() . " : " . $care->getPersonRelatedByPersonId()->getFullName(),
                            'uri' => SystemURLs::getRootPath() . "/v2/pastoralcare/person/" . $care->getPersonId()];

                        if ($this->global_search) {
                            $per = $care->getPersonRelatedByPersonId();
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
