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
        $this->name = _("Family Pastoral Cares");
        parent::__construct($global, "Family Pastoral Cares");
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isPastoralCareEnabled();
    }

    public function buildSearch(string $qry)
    {
        $currentUser = SessionUser::getUser();

        if ($currentUser->isPastoralCareEnabled() && SystemConfig::getBooleanValue("bSearchIncludePastoralCare")) {
            // now we search the families
            try {
                $searchLikeString = '%' . str_replace('*', '%', $qry) . '%';
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
                    ->_and()->filterByText($searchLikeString, Criteria::LIKE)
                    ->_or()
                    ->useFamilyQuery()
                    ->filterByName($searchLikeString, Criteria::LIKE)
                    ->endUse()
                    ->_or()
                    ->usePastoralCareTypeQuery()
                    ->filterByTitle($searchLikeString, Criteria::LIKE)
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
                $includeFamilyHoh = SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH");
                $familiesInCart = $shouldShowCart ? array_fill_keys(Cart::FamiliesInCart(), true) : [];
                

                if ( $cares->count() > 0 ) {
                    $id=1;

                    foreach ($cares as $care) {
                        $family = $care->getFamily();

                        if ($family === null) {
                            continue;
                        }

                        $familyId = $family->getId();

                        if ($quickSearch) {
                            $elt = ['id' => "family-pastoral-care-id-" . $id++,
                                'text' => $care->getPastoralCareType()->getTitle() . " : " . $family->getName(),
                                'uri' => $rootPath . "/v2/pastoralcare/family/" . $care->getFamilyId()];
                        } else {

                            $members = $family->getPeopleSorted();

                            $res_members = [];
                            $globalMembers = "";

                            foreach ($members as $member) {
                                $res_members[] = $member->getId();
                                $globalMembers .= '• <a href="' . $rootPath . '/v2/people/person/view/' . $member->getId() . '">' . $member->getFirstName() . " " . $member->getLastName() . "</a><br>";
                            }

                            $inCart = isset($familiesInCart[$familyId]);

                            $res = "";
                            if ($shouldShowCart) {
                                $res .= '<a href="' . $rootPath . '/v2/pastoralcare/family/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if ($shouldShowCart) {
                                    $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $familyId . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    . '                </span>';
                                if ($shouldShowCart) {
                                    $res .= '                </a>';
                                }
                            } else {
                                if ($shouldShowCart) {
                                    $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $familyId . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    . '                </span>';
                                if ($shouldShowCart) {
                                    $res .= '                </a>';
                                }
                            }

                            $elt = [
                                "id" => $familyId,
                                "img" => $family->getJPGPhotoDatas(),
                                "searchresult" => _("Family Pastoral Care") . ' : <a href="' . $rootPath . '/v2/people/family/view/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $family->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                "address" => (!$shouldSeePrivacyData) ? _('Private Data') : $family->getFamilyString($includeFamilyHoh),
                                "type" => " " . _($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => "",
                                "ProNames" => "",
                                "FamilyRole" => "",
                                "members" => $res_members,
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
