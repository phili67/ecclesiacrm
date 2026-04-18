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
use EcclesiaCRM\Group;
use EcclesiaCRM\Map\GroupTableMap;

class GroupSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Groups');
        parent::__construct($global, "Groups");
    }

    public function allowed(): bool
    {
        return SessionUser::getUser()->isManageGroupsEnabled();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeGroups")) {
            try {

                $groups = GroupQuery::create();                

                if (mb_strtolower($qry) == mb_strtolower(_('group')) || mb_strtolower($qry) == mb_strtolower(_('groups'))) { // we search all the groups                    
                    $groups->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri');
                } else if (mb_strtolower($qry) == mb_strtolower(_('sunday group')) || mb_strtolower($qry) == mb_strtolower(_('sunday groups'))) { // we search all the sunday groups
                    $groups->filterByType(4) // a sunday group type
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/sundayschool/",Group.Id,"/view")', 'uri');
                } else {
                    $groups->filterByName("%$qry%", Criteria::LIKE)
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri');
                }


                $groups->select(['displayName', 'uri', 'Id', 'Type'])
                    ->orderByName();

                if ($this->isQuickSearch()) {
                    $groups->limit(SystemConfig::getValue("iSearchIncludeGroupsMax"));
                }

                $groups->find();

                $shouldShowCart = SessionUser::getUser()->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $shouldSeePrivacyData = SessionUser::getUser()->isSeePrivacyDataEnabled();                


                if ($groups->count() > 0) {
                    $id = 1;

                    foreach ($groups as $group) {
                        $classification = "";
                        switch ($group['Type']) {
                            case 4:
                                $classification = _("Sunday Group");
                                break;
                            default:
                                $classification = _("Group");
                        }

                        if ($this->isQuickSearch()) {
                            $elt = [
                                'id' => 'group-' . $id++,
                                'text' => $group['displayName'],
                                'uri' => $group['uri']
                            ];
                        } else {
                            $members = Person2group2roleP2g2rQuery::create()->findByGroupId($group['Id']);

                            $res_members = [];

                            foreach ($members as $member) {
                                $res_members[] = $member->getPersonId();
                            }

                            $inCart = Cart::GroupInCart($group['Id']);

                            $res = "";
                            if ($shouldShowCart) {
                                $res .= '<a href="' . $rootPath . '/v2/group/editor/' . $group['Id'] . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if ($inCart === false) {
                                if ($shouldShowCart) {
                                    $res .= '<a class="AddToGroupCart" data-cartgroupid="' . $group['Id'] . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                    . '                </span>';
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if ($shouldShowCart) {
                                    $res .= '<a class="RemoveFromGroupCart" data-cartgroupid="' . $group['Id'] . '">';
                                }
                                $res .= '                <span class="fa-stack">'
                                    . '                <i class="fas fa-square fa-stack-2x"></i>'
                                    . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                    . '                </span>';
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            }

                            if ($shouldShowCart) {
                                $res .= '<a href="' . $rootPath . $group['uri'] . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            $elt = [
                                "id" => $group['Id'],
                                "img" => '<i class="fas fa-users fa-2x"></i>',
                                "searchresult" => '<a href="' . $rootPath . $group['uri'] . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $group['displayName'] . '</a>',
                                "address" => "",
                                "type" => " " . ((mb_strtolower($qry) == _('sunday group') || mb_strtolower($qry) == _('sunday groups')) ? _("Sunday Groups") : _($this->getGlobalSearchType())),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => $classification,
                                "ProNames" => "",
                                "FamilyRole" => "",
                                "members" => $res_members,
                                "actions" => $res
                            ];
                        }

                        array_push($this->results, $elt);
                    }
                } else {
                    $members = Person2group2roleP2g2rQuery::create()
                        ->joinWithPerson()
                        ->usePersonQuery()
                            ->filterByDateDeactivated(null) // GDRP, when a person is completely deactivated
                            ->filterByLastName("%$qry%", Criteria::LIKE)
                            ->_or()->filterByFirstName("%$qry%", Criteria::LIKE)    
                        ->endUse()
                        ->useGroupQuery()
                            ->setDistinct(GroupTableMap::COL_GRP_ID)
                            ->withColumn('grp_ID', 'groupId')
                            ->withColumn('grp_Type', 'groupType')
                            ->withColumn('grp_Name', 'displayName')
                            ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')                        
                        ->endUse()
                        ->find();

                    if ($members->count() > 0) {
                        $id = 1;

                        foreach ($members as $member) {
                            $classification = "";
                            switch ($member->getGroupType()) {
                                case 4:
                                    $classification = _("Sunday Group");
                                    break;
                                default:
                                    $classification = _("Group");
                            }

                            if ($this->isQuickSearch()) {
                                $elt = [
                                    'id' => 'group-' . $id++,
                                    'text' => $member->getDisplayName(),
                                    'uri' => $member->getUri()
                                ];
                            } else {                                
                                $inCart = Cart::GroupInCart($member->getGroupId());

                                $res = "";
                                if ($shouldShowCart) {
                                    $res .= '<a href="' . $rootPath . '/v2/group/editor/' . $member->getGroupId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                                }
                                $res .= '<span class="fa-stack">'
                                    . '<i class="fas fa-square fa-stack-2x"></i>'
                                    . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                    . '</span>';
                                if ($shouldShowCart) {
                                    $res .= '</a>&nbsp;';
                                }

                                if ($inCart === false) {
                                    if ($shouldShowCart) {
                                        $res .= '<a class="AddToGroupCart" data-cartgroupid="' . $member->getGroupId() . '">';
                                    }
                                    $res .= '                <span class="fa-stack">'
                                        . '                <i class="fas fa-square fa-stack-2x"></i>'
                                        . '                <i class="fas fa-stack-1x fa-inverse fa-cart-plus"></i>'
                                        . '                </span>';
                                    if ($shouldShowCart) {
                                        $res .= "                </a>  ";
                                    }
                                } else {
                                    if ($shouldShowCart) {
                                        $res .= '<a class="RemoveFromGroupCart" data-cartgroupid="' . $member->getGroupId() . '">';
                                    }
                                    $res .= '                <span class="fa-stack">'
                                        . '                <i class="fas fa-square fa-stack-2x"></i>'
                                        . '                <i class="fas fa-times fa-stack-1x fa-inverse"></i>'
                                        . '                </span>';
                                    if ($shouldShowCart) {
                                        $res .= "                </a>  ";
                                    }
                                }

                                if ($shouldShowCart) {
                                    $res .= '<a href="' . $rootPath . '/v2/group/' . $member->getGroupId() . '/view" data-toggle="tooltip" data-placement="top" title="' . _('View') . '">';
                                }
                                $res .= '<span class="fa-stack">'
                                    . '<i class="fas fa-square fa-stack-2x"></i>'
                                    . '<i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>'
                                    . '</span>';
                                if ($shouldShowCart) {
                                    $res .= '</a>&nbsp;';
                                }

                                $elt = [
                                    "id" => $member->getGroupId(),
                                    "img" => '<i class="fas fa-users fa-2x"></i>',
                                    "searchresult" => '<a href="' . $rootPath . '/v2/group/' . $member->getGroupId() . '/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $member->getDisplayName() . '</a>',
                                    "address" => "",
                                    "type" => " " . ((mb_strtolower($qry) == _('sunday group') || mb_strtolower($qry) == _('sunday groups')) ? _("Sunday Groups") : _($this->getGlobalSearchType())),
                                    "realType" => $this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => $classification,
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => [$member->getPersonId()],
                                    "actions" => $res
                                ];
                                
                            }

                            if (!in_array($elt['id'], array_column($this->results, 'id'))) {                                
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
