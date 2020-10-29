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

                if (!$this->global_search) {
                    $addresses->limit(SystemConfig::getValue("iSearchIncludeAddressesMax"))->find();
                }

                if (!is_null($addresses))
                {
                    $id=1;

                    foreach ($addresses as $address) {
                        $elt = ['id' => 'address-id-'.$id++,
                            'text'=>$address->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH"),false),
                            'uri'=>$address->getViewURI()
                        ];

                        if ($this->global_search) {
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
                                .'<i class="fa fa-square fa-stack-2x"></i>'
                                .'<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>'
                                .'</span>';

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $address->getId() . '">';
                                }

                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fa fa-square fa-stack-2x"></i>'
                                    .'                <i class="fa fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    .'                </span>';

                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '                </a>';
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $address->getId() . '">';
                                }

                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fa fa-square fa-stack-2x"></i>'
                                    .'                <i class="fa fa-remove fa-stack-1x fa-inverse"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '               </a>';
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $address->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fa fa-square fa-stack-2x"></i>'
                                .'<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                "id" => $address->getId(),
                                "img" => '<img src="/api/families/'.$address->getId().'/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">',
                                "searchresult" => _("Addresse").' : <a href="'.SystemURLs::getRootPath().'/FamilyView.php?FamilyID='.$address->getId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.$address->getName().'</a>'." "._("Members")." : <br>".$globalMembers,
                                "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled())?_('Private Data'):$address->getAddress(),
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
