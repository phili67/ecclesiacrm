<?php


namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Map\GroupTableMap;



class GroupPropsSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Groups Properties');
        parent::__construct($global, 'Groups Properties');
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeGroups")) {
            try {
                $groups = GroupQuery::create();

                if (!$this->global_search) {
                    $groups->limit(SystemConfig::getValue("iSearchIncludeGroupsMax"));
                }

                $groups->addJoin(GroupTableMap::COL_GRP_ID, Record2propertyR2pTableMap::COL_R2P_RECORD_ID, Criteria::LEFT_JOIN)
                    ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
                    ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
                    ->addAsColumn('ProPrtId', PropertyTableMap::COL_PRO_PRT_ID)
                    ->addAsColumn('ProName', PropertyTableMap::COL_PRO_NAME)
                    ->addAsColumn('ProValue', Record2propertyR2pTableMap::COL_R2P_VALUE)
                    ->addAsColumn('ProDesc', PropertyTableMap::COL_PRO_DESCRIPTION)
                    ->addAsColumn('ProPrompt', PropertyTableMap::COL_PRO_PROMPT)
                    //->addAsColumn('ProTypeName', PropertyTypeTableMap::COL_PRT_NAME)
                    //->addAsColumn('ProTypeDesc', PropertyTypeTableMap::COL_PRT_DESCRIPTION)
                    //->addAsColumn('ProTypeName', PropertyTypeTableMap::COL_PRT_NAME)
                    ->where(PropertyTableMap::COL_PRO_CLASS . "='g' AND (" . PropertyTableMap::COL_PRO_NAME . " LIKE '%".$qry."%' OR " . Record2propertyR2pTableMap::COL_R2P_VALUE . " LIKE '%".$qry."%' )");


                $groups->find();

                if (!is_null($groups)) {
                    $id = 1;

                    foreach ($groups as $group) {
                        $elt = ['id'=>'group-props-'.$id++,
                            'text'=>$group->getName(),
                            'uri'=> "/v2/group/" . $group->getId() . "/view"];

                        if ($this->global_search) {
                            $members = Person2group2roleP2g2rQuery::create()->findByGroupId($group->getId());

                            $res_members = [];

                            foreach ($members as $member) {
                                $res_members[] = $member->getPersonId();
                            }

                            $inCart = Cart::GroupInCart($group->getId());

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/GroupEditor.php?GroupID=' . $group->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fa fa-square fa-stack-2x"></i>'
                                .'<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart === false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToGroupCart" data-cartgroupid="' . $group->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fa fa-square fa-stack-2x"></i>'
                                    .'                <i class="fa fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromGroupCart" data-cartgroupid="' . $group->getId() . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fa fa-square fa-stack-2x"></i>'
                                    .'                <i class="fa fa-remove fa-stack-1x fa-inverse"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/group/' . $group->getId() . '/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fa fa-square fa-stack-2x"></i>'
                                .'<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                "id" => $group->getId(),
                                "img" => '<img src="/Images/Group.png" class="initials-image direct-chat-img " width="10px" height="10px">',
                                "searchresult" => '<a href="'.SystemURLs::getRootPath().'/v2/group/'.$group->getId().'/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">'.$group->getName().'</a>',
                                "address" => "",
                                "type" => " "._($this->getGlobalSearchType()),
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


