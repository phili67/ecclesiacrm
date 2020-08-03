<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Base\Person2group2roleP2g2r;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemURLs;


class GroupSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Groups');
        parent::__construct($global, "Groups");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeGroups")) {
            try {

                if ( mb_strtolower($qry) == _('group') || mb_strtolower($qry) == _('groups') ) {// we search all the GroupMasters
                    $groups = GroupQuery::create()
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')
                        ->select(['displayName', 'uri', 'Id']);
                } else {
                    $groups = GroupQuery::create()
                        ->filterByName("%$qry%", Criteria::LIKE)
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')
                        ->select(['displayName', 'uri', 'Id']);
                }

                if (!$this->global_search) {
                    $groups->limit(SystemConfig::getValue("iSearchIncludeGroupsMax"));
                }

                $groups->find();


                if (!is_null($groups))
                {
                    $id=1;

                    foreach ($groups as $group) {
                        $elt = ['id'=>'group-'.$id++,
                            'text'=>$group['displayName'],
                            'uri'=>$group['uri']];

                        if ($this->global_search) {
                            $members = Person2group2roleP2g2rQuery::create()->findByGroupId($group['Id']);

                            $res_members = [];

                            foreach ($members as $member) {
                                $res_members[] = $member->getPersonId();
                            }

                            $inCart = Cart::GroupInCart($group['Id']);

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/GroupEditor.php?GroupID=' . $group['Id'] . '" data-toggle="tooltip" data-placement="top" data-original-title="' . _('Edit') . '">';
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
                                    $res .= '<a class="AddToGroupCart" data-cartgroupid="' . $group['Id'] . '">';
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
                                    $res .= '<a class="RemoveFromGroupCart" data-cartgroupid="' . $group['Id'] . '">';
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
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/v2/group/' . $group['Id'] . '/view" data-toggle="tooltip" data-placement="top" data-original-title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fa fa-square fa-stack-2x"></i>'
                                .'<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                "id" => $group['Id'],
                                "img" => '<img src="/Images/Group.png" class="initials-image direct-chat-img " width="10px" height="10px">',
                                "searchresult" => '<a href="'.SystemURLs::getRootPath().'/v2/group/'.$group['Id'].'/view" data-toggle="tooltip" data-placement="top" data-original-title="' . _('Edit') . '">'.$group['displayName'].'</a>',
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
