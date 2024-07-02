<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\PersonCustomMasterQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;


class PersonCustomSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Person Custom Field");
        parent::__construct($global, "Person Custom Field");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $ormPerCustomFields = PersonCustomMasterQuery::Create()
                    ->orderByCustomOrder()
                    ->find();
                
                $res = [];
                $id = 1;
                
                foreach ($ormPerCustomFields as $customfield) {
                    $perCustoms = PersonCustomQuery::Create()
                        ->usePersonQuery()
                            ->filterByDateDeactivated(null)
                        ->endUse()
                        ->withcolumn($customfield->getCustomField())
                        ->find();

                    foreach ($perCustoms as $per) {
                        if (!is_null($per->getVirtualColumn($customfield->getCustomField()))) {
                            $currentFieldData = OutputUtils::displayCustomField($customfield->getTypeId(), trim($per->getVirtualColumn($customfield->getCustomField())), $customfield->getCustomSpecial(), false);
                            if (strstr($currentFieldData, $qry)) {
                                if ( $this->isQuickSearch() ) {
                                    $elt = ['id' => 'person-custom-id-' . $id++,
                                        'text' => $per->getPerson()->getFullName(),
                                        'uri' => $per->getPerson()->getViewURI()
                                    ];
                                } else  {
                                    $fam = $per->getPerson()->getFamily();
    
                                    $address = "";
                                    if (!is_null($fam)) {
                                        $address = '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $fam->getID() . '">' .
                                            $fam->getName() . MiscUtils::FormatAddressLine($per->getPerson()->getFamily()->getAddress1(), $per->getPerson()->getFamily()->getCity(), $per->getPerson()->getFamily()->getState()) .
                                            "</a>";
                                    }
    
                                    $inCart = Cart::PersonInCart($per->getPerson()->getId());
    
                                    $res = "";
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res = '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/editor/' . $per->getPerson()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
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
                                            $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $per->getPerson()->getId() . '">';
                                        }
                                        $res .= "                <span class=\"fa-stack\">\n"
                                            . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                            . "                <i class=\"fas fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                            . "                </span>\n";
                                        if (SessionUser::getUser()->isShowCartEnabled()) {
                                            $res .= "                </a>  ";
                                        }
                                    } else {
                                        if (SessionUser::getUser()->isShowCartEnabled()) {
                                            $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $per->getPerson()->getId() . '">';
                                        }
                                        $res .= "                <span class=\"fa-stack\">\n"
                                            . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                            . "                <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n"
                                            . "                </span>\n";
                                        if (SessionUser::getUser()->isShowCartEnabled()) {
                                            $res .= "                </a>  ";
                                        }
                                    }
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '&nbsp;<a href="' . SystemURLs::getRootPath() . '/v2/people/person/print/' . $per->getPerson()->getId() . '"  data-toggle="tooltip" data-placement="top" title="' . _('Print') . '">';
                                    }
                                    $res .= '<span class="fa-stack">'
                                        . '<i class="fas fa-square fa-stack-2x"></i>'
                                        . '<i class="fas fa-print fa-stack-1x fa-inverse"></i>'
                                        . '</span>';
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '</a>';
                                    }
    
                                    $elt = [
                                        "id" => $per->getPerson()->getId(),
                                        "img" => $per->getPerson()->getJPGPhotoDatas(),
                                        "searchresult" => '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $per->getPerson()->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($per->getPerson()->getTitle(), $per->getPerson()->getFirstName(), $per->getPerson()->getMiddleName(), $per->getPerson()->getLastName(), $per->getPerson()->getSuffix(), 3) . '</a>',
                                        "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $address,
                                        "type" => " " . _($this->getGlobalSearchType()),
                                        "realType" => $this->getGlobalSearchType(),
                                        "Gender" => "",
                                        "Classification" => "",
                                        "ProNames" => "",
                                        "FamilyRole" => "",
                                        "members" => "",
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
