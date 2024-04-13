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
use EcclesiaCRM\dto\Photo;


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
                $searchLikeString = '%'.$qry.'%';

                // Get the lists of custom person fields
                $ormFamiliesCustomFields = FamilyCustomMasterQuery::create()
                    ->orderByCustomOrder()
                    ->find();

                if (!is_null($ormFamiliesCustomFields) && $ormFamiliesCustomFields->count() > 0) {

                    $familiesCustom = FamilyCustomQuery::create();

                    if (SystemConfig::getBooleanValue('bGDPR')) {
                        $familiesCustom->useFamilyQuery()
                            ->filterByDateDeactivated(null)
                            ->endUse();
                    }

                    foreach ($ormFamiliesCustomFields as $customfield ) {
                        $familiesCustom->withColumn($customfield->getCustomField());
                        $familiesCustom->where($customfield->getCustomField()." LIKE ?",$searchLikeString,\PDO::PARAM_STR );
                        $familiesCustom->_or();
                    }

                    if ( $this->isQuickSearch() ) {
                        $familiesCustom->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"));
                    }

                    $familiesCustom->find();

                    if ( $familiesCustom->count() > 0)
                    {
                        $id=1;

                        foreach ($familiesCustom as $fam) {
                            if ( $this->isQuickSearch() ) {
                                $elt = ['id' => 'family-custom-id-' . $id++,
                                    "text" => $fam->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                                    "uri" => $fam->getFamily()->getViewURI()
                                ];
                            } else {
                                $members = $fam->getFamily()->getPeopleSorted();

                                $res_members = [];
                                $globalMembers = "";

                                foreach ($members as $member) {
                                    $res_members[] = $member->getId();
                                    $globalMembers .= '• <a href="'.SystemURLs::getRootPath().'/v2/people/person/view/'.$member->getId().'">'.$member->getFirstName()." ".$member->getLastName()."</a><br>";
                                }

                                $inCart = Cart::FamilyInCart($fam->getFamily()->getId());

                                $res = "";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/editor/' . $fam->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
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
                                        $res .= '<a class="AddToFamilyCart" data-cartfamilyid="' . $fam->getFamily()->getId() . '">';
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
                                        $res .= '<a class="RemoveFromFamilyCart" data-cartfamilyid="' . $fam->getFamily()->getId() . '">';
                                    }
                                    $res  .= '                <span class="fa-stack">'
                                        .'                <i class="fas fa-square fa-stack-2x"></i>'
                                        .'                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                        .'                </span>';

                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '               </a>';
                                    }
                                }

                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $fam->getFamily()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                                }
                                $res .= '<span class="fa-stack">'
                                    .'<i class="fas fa-square fa-stack-2x"></i>'
                                    .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                    .'</span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '</a>&nbsp;';
                                }

                                $photo = new Photo("Family",$fam->getFamily()->getId());
                                $datas = base64_encode($photo->getPhotoBytes());     
                                $img = '<img src="data:image/png;base64, ' . $datas . '" class="initials-image direct-chat-img " width="10px" height="10px" />';           

                                $elt = [
                                    "id" => $fam->getFamily()->getId(),
                                    "img" => $fam->getFamily()->getPNGPhotoDatas(),
                                    "searchresult" => _("Family").' : <a href="'.SystemURLs::getRootPath().'/v2/people/family/view/'.$fam->getFamily()->getId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.$fam->getFamily()->getName().'</a>'." "._("Members")." : <br>".$globalMembers,
                                    "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled())?_('Private Data'):$fam->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
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
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
