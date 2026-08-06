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

    public function allowed (): bool
    {
        return SessionUser::getUser()->isSeePrivacyDataEnabled();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeFamilies")) {
            try {
                $searchNeedle = strtolower($qry);

                $ormFamCustomFields = FamilyCustomMasterQuery::Create()
                    ->orderByCustomOrder()
                    ->find();
                
                $res = [];
                $id = 1;
                
                $famCustomQuery = FamilyCustomQuery::Create()
                    ->useFamilyQuery()
                    ->filterByDateDeactivated(null)
                    ->endUse();

                foreach ($ormFamCustomFields as $customfield) {
                    $famCustomQuery->withColumn($customfield->getCustomField());
                }

                $rootPath = SystemURLs::getRootPath();
                $famCustoms = $famCustomQuery->find();
                $currentUser = SessionUser::getUser();
                $shouldShowCart = $currentUser->isShowCartEnabled();
                $familiesInCart = $shouldShowCart ? array_fill_keys(Cart::FamiliesInCart(), true) : [];


                foreach ($famCustoms as $famCustom) {
                    foreach ($ormFamCustomFields as $customfield) {
                        $customFieldName = $customfield->getCustomField();
                        $rawFieldData = $famCustom->getVirtualColumn($customFieldName);

                        if (!is_null($rawFieldData)) {
                            $currentFieldData = OutputUtils::displayCustomField($customfield->getTypeId(), trim($rawFieldData), $customfield->getCustomSpecial(), false);
                            if ($currentFieldData !== null && strstr(strtolower($currentFieldData), $searchNeedle)) {
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
                                        $globalMembers .= '• <a href="' . $rootPath . '/v2/people/person/view/' . $member->getId() . '">' . $member->getFirstName() . " " . $member->getLastName() . "</a><br>";
                                    }

                                    $inCart = Cart::FamilyInCart($famCustom->getFamily()->getId());

                                    $res = "";
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '<a href="' . $rootPath . '/v2/people/family/editor/' . $famCustom->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                                    }
                                    $res .= '<span class="fa-stack">'
                                        . '<i class="fas fa-square fa-stack-2x"></i>'
                                        . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                        . '</span>';

                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '</a>&nbsp;';
                                    }

                                     $res = $this->buildActionButtons([
                                        [
                                            'activate' => $shouldShowCart,
                                            'type' => 'a',
                                            'href' => $rootPath . '/v2/people/family/editor/' . $famCustom->getFamily()->getId(),
                                            'icon' => '<i class="fas fa-pencil-alt"></i>',
                                            'classes' => 'btn btn-sm btn-outline-primary',
                                            'label' => '',
                                            'datas' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('Edit')]
                                        ],
                                        [
                                            'activate' => $shouldShowCart,
                                            'type' => 'a',
                                            'href' => $rootPath . '/v2/people/family/view/' . $famCustom->getFamily()->getId(),
                                            'icon' => '<i class="fas fa-search-plus"></i>',
                                            'classes' => 'btn btn-sm btn-outline-secondary',
                                            'label' => '',
                                            'tooltip' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('View')]
                                        ],
                                        [
                                            'activate' => $shouldShowCart,
                                            'type' => 'a',
                                            'href' => '',
                                            'icon' => (!$inCart ? '<i class="fas fa-cart-plus fa-inverse"></i>' : '<i class="fas fa-times"></i>'),
                                            'classes' => 'btn btn-sm btn-primary' . ($inCart ? ' RemoveFromFamilyCart' : ' AddToFamilyCart'),
                                            'label' => '',
                                            'datas' => ['cartfamilyid' => $famCustom->getFamily()->getId(), 'toggle' => 'tooltip', 'placement' => 'top', 'title' => (!$inCart ? _('Add to Cart') : _('Remove from Cart'))]
                                        ]
                                    ]);                                     

                                    $elt = [
                                        "id" => $famCustom->getFamily()->getId(),
                                        "img" => $famCustom->getFamily()->getJPGPhotoDatas(),
                                        "searchresult" => _("Family") . ' : <a href="' . $rootPath . '/v2/people/family/view/' . $famCustom->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $famCustom->getFamily()->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                        "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $famCustom->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                                        "type" => _($this->getGlobalSearchType()),
                                        "realType" => $this->getGlobalSearchType(),
                                        "Gender" => "",
                                        "Classification" => trim((string) $currentFieldData),
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