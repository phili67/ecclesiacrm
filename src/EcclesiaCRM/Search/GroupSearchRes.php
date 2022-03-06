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

                if ( mb_strtolower($qry) == mb_strtolower(_('group')) || mb_strtolower($qry) == mb_strtolower(_('groups')) ) {// we search all the groups
                    $groups = GroupQuery::create()
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')
                        ->select(['displayName', 'uri', 'Id']);
                } else if ( mb_strtolower($qry) == mb_strtolower(_('sunday group')) || mb_strtolower($qry) == mb_strtolower(_('sunday groups')) ) {// we search all the sunday groups
                    $groups = GroupQuery::create()
                        ->filterByType(4)// a sunday group type
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/sundayschool/",Group.Id,"/view")', 'uri')
                        ->select(['displayName', 'uri', 'Id']);
                } else {
                    $groups = GroupQuery::create()
                        ->filterByName("%$qry%", Criteria::LIKE)
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')
                        ->select(['displayName', 'uri', 'Id']);
                }

                if (!$this->isGlobalSearch()) {
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

                        if ($this->isGlobalSearch()) {
                            $members = Person2group2roleP2g2rQuery::create()->findByGroupId($group['Id']);

                            $res_members = [];

                            foreach ($members as $member) {
                                $res_members[] = $member->getPersonId();
                            }

                            $inCart = Cart::GroupInCart($group['Id']);

                            $res = "";
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . '/GroupEditor.php?GroupID=' . $group['Id'] . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart === false) {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="AddToGroupCart" data-cartgroupid="' . $group['Id'] . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a class="RemoveFromGroupCart" data-cartgroupid="' . $group['Id'] . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    .'                <i class="fas fa-square fa-stack-2x"></i>'
                                    .'                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    .'                </span>';
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= "                </a>  ";
                                }
                            }

                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '<a href="' . SystemURLs::getRootPath() . $group['uri'] . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                .'<i class="fas fa-square fa-stack-2x"></i>'
                                .'<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                .'</span>';
                            if (SessionUser::getUser()->isShowCartEnabled()) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                "id" => $group['Id'],
                                "img" => '<i class="fas fa-users fa-2x"></i>',
                                "searchresult" => '<a href="'.SystemURLs::getRootPath().$group['uri'].'" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">'.$group['displayName'].'</a>',
                                "address" => "",
                                "type" => " ".((mb_strtolower($qry) == _('sunday group') || mb_strtolower($qry) == _('sunday groups') )?_("Sunday Groups"):_($this->getGlobalSearchType())),
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
