<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\Base\PersonQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;


class PersonPropsSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Person Properties");
        parent::__construct($global,"Person Properties");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {

                // it's possible to include the properties not
                $not_like = "";
                if ($qry[0] == "!") {
                    $qry = substr($qry,1);
                    $not_like = "NOT ";
                }
                $searchLikeString = '%'.$qry.'%';

                $person_Props = PersonQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $person_Props->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                }

                $person_Props->addJoin(PersonTableMap::COL_PER_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID,Criteria::LEFT_JOIN)
                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                    ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                    ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                    ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                    ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                    ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                    ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                    ->where(PropertyTableMap::COL_PRO_CLASS."='p' AND (".PropertyTableMap::COL_PRO_NAME." ".$not_like."LIKE '".$searchLikeString."'  OR " . Record2propertyR2pTableMap::COL_R2P_VALUE . " LIKE '%".$qry."%' )") //NOT LIKE 'a%';
                    ->addAscendingOrderByColumn('ProName')
                    ->addAscendingOrderByColumn('ProTypeName');

                if (!$this->isGlobalSearch()) {
                    $person_Props->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $person_Props->find();


                if (!is_null($person_Props))
                {
                    $id=1;

                    foreach ($person_Props as $per) {
                        $elt = ['id' => 'person-props-id-'.$id++,
                            'text' => $per->getFullName()." (".$per->getProName().")",
                            'uri' => $per->getViewURI()
                        ];

                        if ($this->isGlobalSearch()) {
                            $fam = $per->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="'.SystemURLs::getRootPath().'/FamilyView.php?FamilyID='.$fam->getID().'">'.
                                    $fam->getName().MiscUtils::FormatAddressLine($per->getFamily()->getAddress1(), $per->getFamily()->getCity(), $per->getFamily()->getState()).
                                    "</a>";
                            }

                            $inCart = Cart::PersonInCart($per->getId());

                            $res = "";

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . $per->getViewURI() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart == false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $per->getId() . '">';
                                }

                                $res .= "                <span class=\"fa-stack\">\n"
                                    ."                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    ."                <i class=\"fas fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                    ."                </span>\n";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $per->getId() . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">"
                                    ."                <i class=\"fas fa-square fa-stack-2x\"></i>"
                                    ."                <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n"
                                    ."                </span>";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '&nbsp;<a href="' . SystemURLs::getRootPath() . '/PrintView.php?PersonID=' . $per->getId() . '"  data-toggle="tooltip" data-placement="top" title="' . _('Print') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-print fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>';
                            }

                            $elt = [
                                "id" => $per->getId(),
                                "img" => '<img src="/api/persons/'.$per->getId().'/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">',
                                "searchresult" => '<a href="'.SystemURLs::getRootPath().'/PersonView.php?PersonID='.$per->getId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.OutputUtils::FormatFullName($per->getTitle(), $per->getFirstName(), $per->getMiddleName(), $per->getLastName(), $per->getSuffix(), 3).'</a>',
                                "address" => (!SessionUser::getUser()->isSeePrivacyDataEnabled())?_('Private Data'):$address,
                                "type" => " "._($this->getGlobalSearchType()),
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
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
