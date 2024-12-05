<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\Base\FamilyCustomMasterQuery;
use EcclesiaCRM\Base\FamilyCustomQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\OutputUtils;


class FamilyCustomSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Family Custom Field");
        parent::__construct($global, "Family Custom Field");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeFamilies")) {
            try {
                $ormFamCustomFields = FamilyCustomMasterQuery::Create()
                    ->orderByCustomOrder()
                    ->find();
                
                $res = [];
                $id = 1;
                
                foreach ($ormFamCustomFields as $customfield) {
                    $famCustoms = FamilyCustomQuery::Create()
                        ->useFamilyQuery()
                        ->filterByDateDeactivated(null)
                        ->endUse()
                        ->withcolumn($customfield->getCustomField())
                        ->find();

                    foreach ($famCustoms as $famCustom) {
                        if (!is_null($famCustom->getVirtualColumn($customfield->getCustomField()))) {
                            $currentFieldData = OutputUtils::displayCustomField($customfield->getTypeId(), trim($famCustom->getVirtualColumn($customfield->getCustomField())), $customfield->getCustomSpecial(), false);
                            if (strstr(strtolower($currentFieldData), strtolower($qry))) {
                                if ($this->isQuickSearch()) {
                                    $elt = [
                                        'id' => 'family-custom-id-' . $id++,
                                        "text" => $famCustom->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                                        "uri" => $famCustom->getFamily()->getViewURI()
                                    ];
                                } else {
                                    $members = $famCustom->getFamily()->getPeopleSorted();

                                    $res_members = [];
                                    $globalMembers = "";

                                    foreach ($members as $member) {
                                        $res_members[] = $member->getId();
                                        $globalMembers .= '• <a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $member->getId() . '">' . $member->getFirstName() . " " . $member->getLastName() . "</a><br>";
                                    }

                                    $inCart = Cart::FamilyInCart($famCustom->getFamily()->getId());

                                    $res = "";
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/editor/' . $famCustom->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
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
                                            $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $famCustom->getFamily()->getId() . '">';
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
                                            $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $famCustom->getFamily()->getId() . '">';
                                        }
                                        $res  .= '                <span class="fa-stack">'
                                            . '                <i class="fas fa-square fa-stack-2x"></i>'
                                            . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                            . '                </span>';

                                        if (SessionUser::getUser()->isShowCartEnabled()) {
                                            $res .= '               </a>';
                                        }
                                    }

                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $famCustom->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                                    }
                                    $res .= '<span class="fa-stack">'
                                        . '<i class="fas fa-square fa-stack-2x"></i>'
                                        . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                        . '</span>';
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '</a>&nbsp;';
                                    }

                                    $elt = [
                                        "id" => $famCustom->getFamily()->getId(),
                                        "img" => $famCustom->getFamily()->getJPGPhotoDatas(),
                                        "searchresult" => _("Family") . ' : <a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $famCustom->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $famCustom->getFamily()->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                        "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $famCustom->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
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
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
