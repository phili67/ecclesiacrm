<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;


class AddressSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Addresses');
        parent::__construct($global, "Addresses");
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isSeePrivacyDataEnabled();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeAddresses")) {
            try {
                $currentUser = SessionUser::getUser();
                $rootPath = SystemURLs::getRootPath();
                $showCart = $currentUser->isShowCartEnabled();
                $showPrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $includeFamilyHoh = SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH");
                $maxResults = (int) SystemConfig::getValue("iSearchIncludeAddressesMax");
                $searchNeedle = mb_strtolower($qry);
                $searchLikeString = '%' . str_replace('*', '%', $qry) . '%';
                $matchAll = str_contains($qry, '*') && trim(str_replace('*', '', $qry)) === '';
                $isStringSearch = $this->isStringSearch();
                $isQuickSearch  = $this->isQuickSearch();
                $familiesInCart = $showCart ? array_fill_keys(Cart::FamiliesInCart(), true) : [];

                $addresses = FamilyQuery::create()
                    ->setDistinct()
                    ->leftJoinWithPerson();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $addresses->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }

                $addresses->filterByCity($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByAddress1($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByAddress2($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByZip($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByState($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByName($searchLikeString, Criteria::LIKE);

                if ($isQuickSearch) {
                    $addresses->limit($maxResults);
                }

                $addresses = $addresses->find();

                if ($addresses->count() > 0) {
                    $id = 1;
                    $res_buffer = [];

                    foreach ($addresses as $address) {
                        $addressId = $address->getId();

                        if ($isQuickSearch) {
                            $elt = ['id' => 'address-id-' . $id++,
                                'text' => $address->getFamilyString($includeFamilyHoh, false),
                                'uri' => $address->getViewURI()
                            ];

                            $this->results[] = $elt;
                            continue;
                        }

                        $members = $address->getPeopleSorted();
                        $res_members = [];
                        $globalMembers = '';

                        foreach ($members as $member) {
                            $memberId = $member->getId();
                            $res_members[] = $memberId;
                            $globalMembers .= '• <a href="' . $rootPath . '/v2/people/person/view/' . $memberId . '">' . $member->getFirstName() . ' ' . $member->getLastName() . '</a><br>';
                        }

                        $inCart = isset($familiesInCart[$addressId]);

                        $res = '';
                        if ($showCart) {
                            $res .= '<a href="' . $rootPath . '/v2/people/family/editor/' . $addressId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                        }
                        $res .= '<span class="fa-stack">'
                            . '<i class="fas fa-square fa-stack-2x"></i>'
                            . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                            . '</span>';

                        if ($showCart) {
                            $res .= '</a>&nbsp;';
                            if (!$inCart) {
                                $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $addressId . '">';
                            } else {
                                $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $addressId . '">';
                            }
                        }

                        $res .= '<span class="fa-stack">'
                            . '<i class="fas fa-square fa-stack-2x"></i>'
                            . (!$inCart
                                ? '<i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                : '<i class="fas fa-times fa-stack-1x fa-inverse"></i>')
                            . '</span>';

                        if ($showCart) {
                            $res .= '</a>';
                            $res .= '<a href="' . $rootPath . '/v2/people/family/view/' . $addressId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                        }

                        $res .= '<span class="fa-stack">'
                            . '<i class="fas fa-square fa-stack-2x"></i>'
                            . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                            . '</span>';

                        if ($showCart) {
                            $res .= '</a>&nbsp;';
                        }

                        if ($isStringSearch) {
                            $tableOfRes = [$address->getName(), $address->getState(),
                                $address->getAddress1(), $address->getAddress2(), $address->getCountry(), $address->getCity(), $address->getZip()];

                            foreach ($tableOfRes as $item) {
                                $normalizedItem = mb_strtolower((string) $item);
                                if ($item !== null && ($matchAll || mb_strpos($normalizedItem, $searchNeedle) !== false) && !isset($res_buffer[$normalizedItem])) {
                                    $elt = ['id' => 'searchname-address-id-' . $id++,
                                        'text' => $item,
                                        'uri' => ""];
                                    $this->results[] = $elt;
                                    $res_buffer[$normalizedItem] = true;
                                }
                            }
                        } else {
                            $elt = [
                                'id' => $addressId,
                                'img' => $address->getJPGPhotoDatas(),
                                'searchresult' => _('Addresse') . ' : <a href="' . $rootPath . '/v2/people/family/view/' . $addressId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $address->getName() . '</a> ' . _('Members') . ' : <br>' . $globalMembers,
                                'address' => (!$showPrivacyData) ? _('Private Data') : $address->getAddress(),
                                'type' => _($this->getGlobalSearchType()),
                                'realType' => $this->getGlobalSearchType(),
                                'Gender' => '',
                                'Classification' => '',
                                'ProNames' => '',
                                'FamilyRole' => '',
                                'members' => $res_members,
                                'actions' => $res
                            ];
                            $this->results[] = $elt;
                        }
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
