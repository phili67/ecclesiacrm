<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;


class PersonPastoralCareSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Individual Pastoral Cares");
        parent::__construct($global,"Individual Pastoral Cares");
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isPastoralCareEnabled();
    }

    public function buildSearch(string $qry)
    {
        $currentUser = SessionUser::getUser();

        if ($currentUser->isPastoralCareEnabled() && SystemConfig::getBooleanValue("bSearchIncludePastoralCare")) {
            try {
                $searchLikeString = '%' . str_replace('*', '%', $qry) . '%';
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
                    ->_or()->filterByPastorName($searchLikeString, Criteria::LIKE)
                    ->orderByDate(Criteria::DESC);

                $quickSearch = $this->isQuickSearch();

                if ($quickSearch) {
                    $cares->limit(SystemConfig::getValue("iSearchIncludePastoralCareMax"));
                }


                if ($currentUser->isAdmin()) {
                    $cares = $cares->find();
                } else {
                    $cares = $cares->findByPastorId($currentUser->getPerson()->getId());
                }

                $shouldShowCart = $currentUser->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $shouldSeePrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $peopleInCart = $shouldShowCart ? array_fill_keys(Cart::PeopleInCart(), true) : [];

                if ( $cares->count() > 0 ) {
                    $id=1;

                    foreach ($cares as $care) {
                        if ($quickSearch) {
                            $elt = ['id' => "person-pastoralcare-id-" . $id++,
                                'text' => $care->getPastoralCareType()->getTitle() . " : " . $care->getPersonRelatedByPersonId()->getFullName(),
                                'uri' => $rootPath . "/v2/pastoralcare/person/" . $care->getPersonId()];
                        } else {
                            $per = $care->getPersonRelatedByPersonId();
                            $fam = $per->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="'.$rootPath.'/v2/people/family/view/'.$fam->getID().'">'.
                                    $fam->getName().MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState()).
                                    "</a>";
                            }

                            $inCart = isset($peopleInCart[$per->getId()]);

                            $res = $this->buildActionButtons([
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => $rootPath . '/v2/pastoralcare/person/' . $per->getId(),
                                    'icon' => '<i class="fas fa-pencil-alt"></i>',
                                    'classes' => 'btn btn-sm btn-outline-primary',
                                    'label' => '',
                                    'datas' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('Edit')]
                                ],                                
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => '',
                                    'icon' => (!$inCart ? '<i class="fas fa-cart-plus fa-inverse"></i>' : '<i class="fas fa-times"></i>'),
                                    'classes' => 'btn btn-sm btn-primary' . ($inCart ? ' RemoveFromPeopleCart' : ' AddToPeopleCart'),
                                    'label' => '',
                                    'datas' => ['cartpersonid' => $per->getId(), 'toggle' => 'tooltip', 'placement' => 'top', 'title' => (!$inCart ? _('Add to Cart') : _('Remove from Cart'))]
                                ]
                            ]);                                                 

                            $elt = [
                                "id" => $per->getId(),
                                "img" => $per->getJPGPhotoDatas(),
                                "searchresult" => '<a href="'.$rootPath.'/v2/people/person/view/'.$per->getId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.OutputUtils::FormatFullName($per->getTitle(), $per->getFirstName(), $per->getMiddleName(), $per->getLastName(), $per->getSuffix(), 3).'</a>',
                                "address" => (!$shouldSeePrivacyData)?_('Private Data'):$address,
                                "type" => " "._($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => $care->getDate(SystemConfig::getValue('sDateFormatLong')) . " - " . $care->getPastoralCareType()->getTitle(),                                
                                "ProNames" => "",
                                "FamilyRole" => "",
                                "members" => "",
                                "actions" => $res
                            ];
                        }

                        array_push($this->results, $elt);
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
