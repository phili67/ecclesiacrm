<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\GroupManagerPersonQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;


class PersonGroupManagerSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Group Manager");
        parent::__construct($global, "Group Manager");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $searchLikeString = '%'.$qry.'%';

                /*
                 *
                 * if (SystemConfig::getBooleanValue('bGDPR')) {
                        $person_Props->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                   }
                 */


                if ( mb_strtolower($qry) == 'groupmasters' || mb_strtolower($qry) == 'groupmaster'
                    || mb_strtolower($qry) == 'groupmanagers' || mb_strtolower($qry) == 'groupmanager' ) {// we search all the GroupMasters
                    $persons = GroupManagerPersonQuery::create()
                        ->usePersonQuery()
                        ->filterByDateDeactivated(null)
                        ->endUse();
                } else {
                    $persons = GroupManagerPersonQuery::create()
                        ->usePersonQuery()
                        ->filterByFirstName($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByLastName($searchLikeString, Criteria::LIKE)
                        ->_and()->filterByDateDeactivated(null)
                        ->endUse();
                }


                if ( !$this->global_search ) {
                    $persons->limit(SystemConfig::getValue("iSearchIncludePersonsMax"))
                        ->find();
                } else {
                    $persons->find();
                }

                if ( !is_null($persons) ) {

                    $id = 1;

                    foreach ($persons as $per) {
                        $elt = ['id' => 'person-group-manager-id-' . $id++,
                            'text' => $per->getPerson()->getFullName(),
                            'uri' => "/v2/group/".$per->getGroup()->getId()."/view"
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
                                "searchresult" => _("Group")." : ". '<a href="'.SystemURLs::getRootPath().'/v2/group/'.$per->getGroup()->getId().'/view" data-toggle="tooltip" data-placement="top" data-original-title="' . _('Edit') . '">'.$per->getGroup()->getName().'</a>'
                                            ." (".'<a href="' . SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $per->getPerson()->getId() . '" data-toggle="tooltip" data-placement="top" data-original-title="' . _('Edit') . '">' . OutputUtils::FormatFullName($per->getPerson()->getTitle(), $per->getPerson()->getFirstName(), $per->getPerson()->getMiddleName(), $per->getPerson()->getLastName(), $per->getPerson()->getSuffix(), 3) . '</a>'.")",
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

                    //LoggerUtils::getAppLogger()->info("toto".print_r($this->results,1));

                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}



