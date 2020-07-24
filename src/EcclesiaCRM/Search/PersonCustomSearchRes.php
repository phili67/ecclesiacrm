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
                $searchLikeString = '%'.$qry.'%';

                // Get the lists of custom person fields
                $ormPersonCustomFields = PersonCustomMasterQuery::create()
                    ->orderByCustomOrder()
                    ->find();


                if (!is_null($ormPersonCustomFields) && $ormPersonCustomFields->count() > 0) {
                    $personsCustom = PersonCustomQuery::create();

                    if (SystemConfig::getBooleanValue('bGDPR')) {
                        $personsCustom->usePersonQuery()
                            ->filterByDateDeactivated(null)
                            ->endUse();
                    }

                    foreach ($ormPersonCustomFields as $customfield) {
                        $personsCustom->withColumn($customfield->getCustomField());
                        $personsCustom->where($customfield->getCustomField() . " LIKE ?", $searchLikeString, \PDO::PARAM_STR);
                        $personsCustom->_or();
                    }

                    if (!$this->global_search) {
                        $personsCustom->limit(SystemConfig::getValue("iSearchIncludePersonsMax"))
                            ->find();
                    }

                    if (!is_null($personsCustom)) {
                        $id = 1;

                        foreach ($personsCustom as $per) {
                            $elt = ['id' => 'person-custom-id-' . $id++,
                                'text' => $per->getPerson()->getFullName(),
                                'uri' => $per->getPerson()->getViewURI()
                            ];


                            if ($this->global_search) {
                                $fam = $per->getPerson()->getFamily();

                                $address = "";
                                if (!is_null($fam)) {
                                    $address = '<a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $fam->getID() . '">' .
                                        $fam->getName() . MiscUtils::FormatAddressLine($per->getPerson()->getFamily()->getAddress1(), $per->getPerson()->getFamily()->getCity(), $per->getPerson()->getFamily()->getState()) .
                                        "</a>";
                                }

                                $inCart = Cart::PersonInCart($per->getPerson()->getId());

                                $res = "";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res = '<a href="' . SystemURLs::getRootPath() . '/PersonEditor.php?PersonID=' . $per->getPerson()->getId() . '" data-toggle="tooltip" data-placement="top" data-original-title="' . _('Edit') . '">';
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
                                        $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $per->getPerson()->getId() . '">';
                                    }
                                    $res .= "                <span class=\"fa-stack\">\n"
                                        . "                <i class=\"fa fa-square fa-stack-2x\"></i>\n"
                                        . "                <i class=\"fa fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                        . "                </span>\n";
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= "                </a>  ";
                                    }
                                } else {
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $per->getPerson()->getId() . '">';
                                    }
                                    $res .= "                <span class=\"fa-stack\">\n"
                                        . "                <i class=\"fa fa-square fa-stack-2x\"></i>\n"
                                        . "                <i class=\"fa fa-remove fa-stack-1x fa-inverse\"></i>\n"
                                        . "                </span>\n";
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= "                </a>  ";
                                    }
                                }
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '&nbsp;<a href="' . SystemURLs::getRootPath() . '/PrintView.php?PersonID=' . $per->getPerson()->getId() . '"  data-toggle="tooltip" data-placement="top" data-original-title="' . _('Print') . '">';
                                }
                                $res .= '<span class="fa-stack">'
                                    . '<i class="fa fa-square fa-stack-2x"></i>'
                                    . '<i class="fa fa-print fa-stack-1x fa-inverse"></i>'
                                    . '</span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '</a>';
                                }

                                $elt = [
                                    "id" => $per->getPerson()->getId(),
                                    "img" => '<img src="/api/persons/' . $per->getPerson()->getId() . '/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">',
                                    "searchresult" => '<a href="' . SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $per->getPerson()->getId() . '" data-toggle="tooltip" data-placement="top" data-original-title="' . _('Edit') . '">' . OutputUtils::FormatFullName($per->getPerson()->getTitle(), $per->getPerson()->getFirstName(), $per->getPerson()->getMiddleName(), $per->getPerson()->getLastName(), $per->getPerson()->getSuffix(), 3) . '</a>',
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
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
