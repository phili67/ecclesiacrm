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

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeFamilies")) {
            try {
                $families = FamilyQuery::create();

                if (!$this->global_search) {
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
                    ->where(PropertyTableMap::COL_PRO_CLASS . "='f' AND (" . PropertyTableMap::COL_PRO_NAME . " LIKE '%".$qry."%' OR " . Record2propertyR2pTableMap::COL_R2P_VALUE . " LIKE '%".$qry."%' )");


                $families->find();

                if (!is_null($families)) {
                    $id = 1;

                    foreach ($families as $family) {
                        if ($family->getPeople()->count() == 1) {// we avoid a one person family
                            continue;
                        }

                        $elt = [
                            "id" => 'family-props-id-' . $id++,
                            "text" => $family->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                            "uri" => $family->getViewURI()
                        ];

                        if ($this->global_search) {
                            $members = $family->getPeopleSorted();

                            $res_members = [];
                            $globalMembers = "";

                            foreach ($members as $member) {
                                $res_members[] = $member->getId();
                                $globalMembers .= '• <a href="' . SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $member->getId() . '">' . $member->getFirstName() . " " . $member->getLastName() . "</a><br>";
                            }

                            $inCart = Cart::FamilyInCart($family->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/FamilyEditor.php?FamilyID=' . $family->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fa fa-square fa-stack-2x"></i>'
                                . '<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $family->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fa fa-square fa-stack-2x"></i>'
                                    . '                <i class="fa fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    . '                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '                </a>';
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $family->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fa fa-square fa-stack-2x"></i>'
                                    . '                <i class="fa fa-remove fa-stack-1x fa-inverse"></i>'
                                    . '                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '               </a>';
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $family->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fa fa-square fa-stack-2x"></i>'
                                . '<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                "id" => $family->getId(),
                                "img" => '<img src="/api/families/' . $family->getId() . '/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">',
                                "searchresult" => _("Family") . ' : <a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $family->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $family->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $family->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                                "type" => _($this->getGlobalSearchType()),
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
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}


