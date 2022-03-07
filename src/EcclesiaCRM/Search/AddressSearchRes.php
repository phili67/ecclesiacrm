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

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeAddresses")) {
            try {
                $searchLikeString = '%'.$qry.'%';
                $addresses = FamilyQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $addresses->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }


                $addresses->filterByCity($searchLikeString, Criteria::LIKE)->
                _or()->filterByAddress1($searchLikeString, Criteria::LIKE)->
                _or()->filterByAddress2($searchLikeString, Criteria::LIKE)->
                _or()->filterByZip($searchLikeString, Criteria::LIKE)->
                _or()->filterByState($searchLikeString, Criteria::LIKE);

                $isStringSearch = $this->isStringSearch();
                $isQuickSearch  = $this->isQuickSearch();


                if ( $isQuickSearch ) {
                    $addresses->limit(SystemConfig::getValue("iSearchIncludeAddressesMax"))->find();
                }

                if ( $addresses->count() > 0 )
                {
                    $id=1;
                    $res_buffer = [];

                    foreach ($addresses as $address) {
                        if ( $isQuickSearch ) {
                            $elt = ['id' => 'address-id-' . $id++,
                                'text' => $address->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH"), false),
                                'uri' => $address->getViewURI()
                            ];

                            array_push($this->results, $elt);
                        } else {
                            $members = $address->getPeopleSorted();

                            $res_members = [];
                            $globalMembers = "";

                            foreach ($members as $member) {
                                $res_members[] = $member->getId();
                                $globalMembers .= '• <a href="' . SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $member->getId() . '">' . $member->getFirstName() . " " . $member->getLastName() . "</a><br>";
                            }

                            $inCart = Cart::FamilyInCart($address->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/FamilyEditor.php?FamilyID=' . $address->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                .'</span>';

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $address->getId() . '">';
                                }

                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    .'                </span>';

                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '                </a>';
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $address->getId() . '">';
                                }

                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '               </a>';
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $address->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($isStringSearch) {
                                $tableOfRes = [$address->getName(), $address->getState(),
                                    $address->getAddress1(), $address->getAddress2(), $address->getCountry(), $address->getCity(), $address->getZip()];

                                foreach ($tableOfRes as $item) {
                                    if (mb_strpos(mb_strtolower($item), mb_strtolower($qry)) !== false and !in_array(mb_strtolower($item), $res_buffer)) {
                                        $elt = ['id' => 'searchname-address-id-' . $id++,
                                            'text' => $item,
                                            'uri' => ""];
                                        array_push($this->results, $elt);
                                        array_push($res_buffer, mb_strtolower($item));
                                    }
                                }
                            } else {
                                $elt = [
                                    "id" => $address->getId(),
                                    "img" => '<img src="/api/families/' . $address->getId() . '/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">',
                                    "searchresult" => _("Addresse") . ' : <a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $address->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $address->getName() . '</a>' . " " . _("Members") . " : <br>" . $globalMembers,
                                    "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $address->getAddress(),
                                    "type" => _($this->getGlobalSearchType()),
                                    "realType" => $this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => "",
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => $res_members,
                                    "actions" => $res
                                ];
                                array_push($this->results, $elt);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
