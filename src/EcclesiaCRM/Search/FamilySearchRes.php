<?php


namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Map\FamilyTableMap;


class FamilySearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Families');
        parent::__construct($global, 'Families');
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeFamilies")) {
            try {
                $searchLikeString = '%'.$qry.'%';

                $subQuery = FamilyQuery::create()
                    ->withColumn('Family.Id','FamId')
                    ->leftJoinPerson()
                    ->usePersonQuery()
                        ->filterByDateDeactivated( null)
                        ->withColumn('COUNT(Person.Id)','cnt')
                    ->endUse()
                    ->filterByDateDeactivated(NULL)
                    ->groupById(FamilyTableMap::COL_FAM_ID);

                $families = FamilyQuery::create()
                    ->addSelectQuery($subQuery, 'res'); // only real family with more than one member will be showed here

                if ( !( mb_strtolower($qry) == mb_strtolower(_('families')) || mb_strtolower($qry) == mb_strtolower(_('family'))
                        || mb_strtolower($qry) == mb_strtolower(_('single')) || mb_strtolower($qry) == mb_strtolower(_('singles')) ) ) {
                    $families->filterByName("%$qry%", Criteria::LIKE)
                        ->_or()->filterByHomePhone($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByCellPhone($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByWorkPhone($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByEmail($searchLikeString, Criteria::LIKE);
                }

                $compareOp = ">";
                if ( mb_strtolower($qry) == mb_strtolower(_('single')) || mb_strtolower($qry) == mb_strtolower(_('singles')) ) {
                    $compareOp = "=";
                }

                $isGlobalSearch = $this->isGlobalSearch();
                $isStringSearch = $this->isStringSearch();
                $isQuickSearch  = $this->isQuickSearch();

                if ( $isQuickSearch ) {
                    $families->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"))
                        ->where('res.cnt'.$compareOp.'1 AND Family.Id=res.FamId')->find();
                } else {
                    $families
                        ->where('res.cnt'.$compareOp.'1 AND Family.Id=res.FamId')->find();
                }

                if ( $families->count() > 0 )
                {
                    $id=1;
                    $res_buffer = [];

                    foreach ($families as $family)
                    {
                        if ( $isQuickSearch ) {
                          $elt=[
                              "id" => 'family-id-'.$id++,
                              "text" => $family->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                              "uri" => $family->getViewURI()
                          ];

                          array_push($this->results,$elt);
                        } else {
                            $members = $family->getPeopleSorted();

                            $res_members = [];
                            $globalMembers = "";

                            foreach ($members as $member) {
                                $res_members[] = $member->getId();
                                $globalMembers .= '• <a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $member->getId() . '">' . $member->getFirstName() . " " . $member->getLastName() . "</a><br>";
                            }

                            $inCart = Cart::FamilyInCart($family->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/editor/' . $family->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $family->getId() . '">';
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
                                    $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $family->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    . '                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '               </a>';
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $family->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ( $isStringSearch ) {
                                $tableOfRes = [$family->getName(), $family->getEmail(),
                                    $family->getHomePhone(), $family->getCellPhone(), $family->getWorkPhone(), $family->getState()];

                                if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                                    array_merge($tableOfRes, [_($family->getFamilyString())]);
                                }

                                foreach ($tableOfRes as $item) {
                                    if (mb_strpos(mb_strtolower($item), mb_strtolower($qry)) !== false and !in_array($item, $res_buffer)) {
                                        $elt = ['id' => 'searchname-family-id-' . ($id++),
                                            'text' => $item,
                                            'uri' => ""];
                                        array_push($this->results, $elt);
                                        array_push($res_buffer, $item);
                                    }
                                }
                            } elseif ( $isGlobalSearch ) {                                
                                $elt = [
                                    "id" => $family->Id(),
                                    "img" => $family->getPNGPhotoDatas(),
                                    "searchresult" => _("Family") . ' : <a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $family->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $family->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                    "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $family->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                                    "type" => (mb_strtolower($qry) == mb_strtolower(_('single')) || mb_strtolower($qry) == mb_strtolower(_('singles'))) ? _("Singles") : _($this->getGlobalSearchType()),
                                    "realType" => $this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => "",
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => $res_members,
                                    "actions" => $res
                                ];

                                array_push($this->results,$elt);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}


