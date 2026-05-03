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
                $currentUser = SessionUser::getUser();
                $searchNeedle = mb_strtolower($qry);
                $matchAll = str_contains($qry, '*') && trim(str_replace('*', '', $qry)) === '';

                $ormFamCustomFields = FamilyCustomMasterQuery::Create()
                    ->orderByCustomOrder()
                    ->find();

                if ($ormFamCustomFields->count() === 0) {
                    return;
                }

                $id = 1;

                $shouldShowCart = $currentUser->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $shouldSeePrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $includeFamilyHoh = SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH");
                $isQuickSearch = $this->isQuickSearch();
                $familiesInCart = $shouldShowCart ? array_fill_keys(Cart::FamiliesInCart(), true) : [];

                $famCustoms = FamilyCustomQuery::Create()
                    ->setDistinct()
                    ->leftJoinWithFamily()
                    ->useFamilyQuery()
                        ->filterByDateDeactivated(null)
                        ->leftJoinWithPerson()
                    ->endUse();

                foreach ($ormFamCustomFields as $customfield) {
                    $famCustoms->withColumn($customfield->getCustomField());
                }

                $famCustoms = $famCustoms->find();

                foreach ($famCustoms as $famCustom) {
                    $family = $famCustom->getFamily();

                    if ($family === null) {
                        continue;
                    }

                    foreach ($ormFamCustomFields as $customfield) {
                        $customFieldName = $customfield->getCustomField();
                        $fieldValue = $famCustom->getVirtualColumn($customFieldName);

                        if (is_null($fieldValue)) {
                            continue;
                        }

                        $currentFieldData = OutputUtils::displayCustomField(
                            $customfield->getTypeId(),
                            trim($fieldValue),
                            $customfield->getCustomSpecial(),
                            false
                        );

                        if ($currentFieldData === null) {
                            continue;
                        }

                        $normalizedFieldData = mb_strtolower((string) $currentFieldData);

                        if (!$matchAll && mb_strpos($normalizedFieldData, $searchNeedle) === false) {
                            continue;
                        }

                        $familyId = $family->getId();

                        if ($isQuickSearch) {
                            $elt = [
                                'id' => 'family-custom-id-' . $id++,
                                'text' => $family->getFamilyString($includeFamilyHoh),
                                'uri' => $family->getViewURI()
                            ];
                        } else {
                            $members = $family->getPeopleSorted();
                            $res_members = [];
                            $globalMembers = '';

                            foreach ($members as $member) {
                                $memberId = $member->getId();
                                $res_members[] = $memberId;
                                $globalMembers .= '• <a href="' . $rootPath . '/v2/people/person/view/' . $memberId . '">' . $member->getFirstName() . ' ' . $member->getLastName() . '</a><br>';
                            }

                            $inCart = isset($familiesInCart[$familyId]);

                            $res = '';
                            if ($shouldShowCart) {
                                $res .= '<a href="' . $rootPath . '/v2/people/family/editor/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                . '</span>';

                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if (!$inCart) {
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
                                    $res .= '               </a>';
                                }
                            }

                            if ($shouldShowCart) {
                                $res .= '<a href="' . $rootPath . '/v2/people/family/view/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                'id' => $familyId,
                                'img' => $family->getJPGPhotoDatas(),
                                'searchresult' => _("Family") . ' : <a href="' . $rootPath . '/v2/people/family/view/' . $familyId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $family->getName() . '</a> ' . _("Members") . ' : <br>' . $globalMembers,
                                'address' => (!$shouldSeePrivacyData) ? _('Private Data') : $family->getFamilyString($includeFamilyHoh),
                                'type' => _($this->getGlobalSearchType()),
                                'realType' => $this->getGlobalSearchType(),
                                'Gender' => '',
                                'Classification' => '',
                                'ProNames' => '',
                                'FamilyRole' => '',
                                'members' => $res_members,
                                'actions' => $res
                            ];
                        }

                        $this->results[] = $elt;
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
