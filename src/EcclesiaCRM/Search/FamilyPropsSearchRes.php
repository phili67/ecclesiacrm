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
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;



class FamilyPropsSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Families Properties');
        parent::__construct($global, 'Families Properties');
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isSeePrivacyDataEnabled();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeFamilies")) {
            try {
                $currentUser = SessionUser::getUser();
                $showCart = $currentUser->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $showPrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $includeFamilyHoh = SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH");
                $quickSearch = $this->isQuickSearch();
                $familiesInCart = $showCart ? array_fill_keys(Cart::FamiliesInCart(), true) : [];
                $normalizedQuery = str_replace('*', '%', $qry);
                $searchLikeString = '%' . $normalizedQuery . '%';
                $families = FamilyQuery::create()
                    ->setDistinct()
                    ->leftJoinWithPerson();

                if ($quickSearch) {
                    $families->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"));
                }


                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $families->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }

                $families->addJoin(FamilyTableMap::COL_FAM_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID, Criteria::LEFT_JOIN)
                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
                    ->addAsColumn('ProPrtId', PropertyTableMap::COL_PRO_PRT_ID)
                    ->addAsColumn('ProName', PropertyTableMap::COL_PRO_NAME)
                    ->addAsColumn('ProValue', Record2propertyR2pTableMap::COL_R2P_VALUE)
                    ->addAsColumn('ProDesc', PropertyTableMap::COL_PRO_DESCRIPTION)
                    ->addAsColumn('ProPrompt', PropertyTableMap::COL_PRO_PROMPT)
                    //->addAsColumn('ProTypeName', PropertyTypeTableMap::COL_PRT_NAME)
                    //->addAsColumn('ProTypeDesc', PropertyTypeTableMap::COL_PRT_DESCRIPTION)
                    //->addAsColumn('ProTypeName', PropertyTypeTableMap::COL_PRT_NAME)
                    ->where(PropertyTableMap::COL_PRO_CLASS . "='f' AND (" . PropertyTableMap::COL_PRO_NAME . " LIKE '" . $searchLikeString . "' OR " . Record2propertyR2pTableMap::COL_R2P_VALUE . " LIKE '" . $searchLikeString . "' )"
                        . "OR " . FamilyTableMap::COL_FAM_NAME . " LIKE '" . $searchLikeString . "'");

                $families = $families->find();
                    

                if ( $families->count() > 0 ) {
                    $id = 1;

                    foreach ($families as $family) {
                        if ($family->getPeople()->count() == 1) {// we avoid a one person family
                            continue;
                        }

                        $familyId = $family->getId();

                        if ($quickSearch) {
                            $elt = [
                                "id" => 'family-props-id-' . $id++,
                                "text" => $family->getFamilyString($includeFamilyHoh),
                                "uri" => $family->getViewURI()
                            ];

                        } else {
                            $members = $family->getPeopleSorted();

                            $res_members = [];
                            $globalMembers = "";

                            foreach ($members as $member) {
                                $memberId = $member->getId();
                                $res_members[] = $memberId;
                                $globalMembers .= '• <a href="' . $rootPath . '/v2/people/person/view/' . $memberId . '">' . $member->getFirstName() . ' ' . $member->getLastName() . '</a><br>';
                            }

                            $inCart = isset($familiesInCart[$familyId]);

                            $res = "";
                            if ($showCart) {
                                $res .= '<a href="' . $rootPath . '/v2/people/family/editor/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($showCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if ($showCart) {
                                    $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $familyId . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    . '                </span>';
                                if ($showCart) {
                                    $res .= '                </a>';
                                }
                            } else {
                                if ($showCart) {
                                    $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $familyId . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    . '                </span>';
                                if ($showCart) {
                                    $res .= '               </a>';
                                }
                            }

                            if ($showCart) {
                                $res .= '<a href="' . $rootPath . '/v2/people/family/view/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($showCart) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                "id" => $familyId,
                                "img" => $family->getJPGPhotoDatas(),
                                "searchresult" => _("Family") . ' : <a href="' . $rootPath . '/v2/people/family/view/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $family->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                "address" => (!$showPrivacyData) ? _('Private Data') : $family->getFamilyString($includeFamilyHoh),
                                "type" => _($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => $family->getProName() . (!empty($family->getProValue()) ? " : " . $family->getProValue() : ""),
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


