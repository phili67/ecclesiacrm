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

                if ( $this->isQuickSearch() ) {
                    $cares->limit(SystemConfig::getValue("iSearchIncludePastoralCareMax"));
                }

                if ( SessionUser::getUser()->isAdmin() ) {
                    $cares->find();
                } else {
                    $cares->findByPastorId(SessionUser::getUser()->getPerson()->getId());
                }

                if ( $cares->count() > 0 ) {
                    $id=1;

                    foreach ($cares as $care) {
                        if ( $this->isQuickSearch() ) {
                            $elt = ['id' => "family-pastoral-care-id-" . $id++,
                                'text' => $care->getPastoralCareType()->getTitle() . " : " . $care->getFamily()->getName(),
                                'uri' => SystemURLs::getRootPath() . "/v2/pastoralcare/family/" . $care->getFamilyId()];
                        } else {

                            $members = $care->getFamily()->getPeopleSorted();

                            $res_members = [];
                            $globalMembers = "";

                            foreach ($members as $member) {
                                $res_members[] = $member->getId();
                                $globalMembers .= '• <a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $member->getId() . '">' . $member->getFirstName() . " " . $member->getLastName() . "</a><br>";
                            }

                            $inCart = Cart::FamilyInCart($care->getFamily()->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/pastoralcare/family/' . $care->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $care->getFamily()->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    . '                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '                </a>';
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $care->getFamily()->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    . '                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '                </a>';
                                }
                            }

                            $elt = [
                                "id" => $care->getFamily()->getId(),
                                "img" => $care->getFamily()->getJPGPhotoDatas(),
                                "searchresult" => _("Family Pastoral Care") . ' : <a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $care->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $care->getFamily()->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $care->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
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
