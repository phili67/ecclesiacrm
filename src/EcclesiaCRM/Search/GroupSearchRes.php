<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemURLs;
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
                $currentUser = SessionUser::getUser();
                $rootPath = SystemURLs::getRootPath();
                $shouldShowCart = $currentUser->isShowCartEnabled();
                $groupsInCart = $shouldShowCart ? array_fill_keys(Cart::GroupsInCart(), true) : [];
                $quickSearch = $this->isQuickSearch();
                $groupTypeLabel = " " . ((mb_strtolower($qry) == _('sunday group') || mb_strtolower($qry) == _('sunday groups')) ? _("Sunday Groups") : _($this->getGlobalSearchType()));

                $groups = GroupQuery::create();                

                if (mb_strtolower($qry) == mb_strtolower(_('group')) || mb_strtolower($qry) == mb_strtolower(_('groups'))) { // we search all the groups                    
                    $groups->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . $rootPath . '/v2/group/",Group.Id,"/view")', 'uri');
                } else if (mb_strtolower($qry) == mb_strtolower(_('sunday group')) || mb_strtolower($qry) == mb_strtolower(_('sunday groups'))) { // we search all the sunday groups
                    $groups->filterByType(4) // a sunday group type
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . $rootPath . '/v2/sundayschool/",Group.Id,"/view")', 'uri');
                } else {
                    $groups->filterByName("%$qry%", Criteria::LIKE)
                        ->withColumn('grp_Name', 'displayName')
                        ->withColumn('CONCAT("' . $rootPath . '/v2/group/",Group.Id,"/view")', 'uri');
                }
                $groups->orderByName();

                if ($quickSearch) {
                    $groups->limit(SystemConfig::getValue("iSearchIncludeGroupsMax"));
                }

                $groups = $groups->find();


                if ($groups->count() > 0) {
                    $id = 1;
                    $membersByGroupId = [];

                    if (!$quickSearch) {
                        $groupIds = [];

                        foreach ($groups as $group) {
                            $groupIds[] = $group->getId();
                        }

                        if (!empty($groupIds)) {
                            $groupMembers = Person2group2roleP2g2rQuery::create()
                                ->filterByGroupId($groupIds, Criteria::IN)
                                ->find();

                            foreach ($groupMembers as $groupMember) {
                                $membersByGroupId[$groupMember->getGroupId()][] = $groupMember->getPersonId();
                            }
                        }
                    }

                    foreach ($groups as $group) {
                        $groupId = $group->getId();
                        $groupType = $group->getType();
                        $displayName = $group->getVirtualColumn('displayName');
                        $groupUri = $group->getVirtualColumn('uri');

                        $classification = "";
                        switch ($groupType) {
                            case 4:
                                $classification = _("Sunday Group");
                                break;
                            default:
                                $classification = _("Group");
                        }

                        if ($quickSearch) {
                            $elt = [
                                'id' => 'group-' . $id++,
                                'text' => $displayName,
                                'uri' => $groupUri
                            ];
                        } else {
                            $res_members = $membersByGroupId[$groupId] ?? [];
                            $inCart = isset($groupsInCart[$groupId]);

                            $res = $this->buildActionButtons([
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => $rootPath . '/v2/group/editor/' . $groupId,
                                    'icon' => '<i class="fas fa-pencil-alt"></i>',
                                    'classes' => 'btn btn-sm btn-outline-primary',
                                    'label' => '',
                                    'datas' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('Edit')]
                                ],
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => $rootPath . '/v2/group/' . $groupId . '/view',
                                    'icon' => '<i class="fas fa-search-plus"></i>',
                                    'classes' => 'btn btn-sm btn-outline-secondary',
                                    'label' => '',
                                    'tooltip' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('View')]
                                ],
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => '',
                                    'icon' => (!$inCart ? '<i class="fas fa-cart-plus fa-inverse"></i>' : '<i class="fas fa-times"></i>'),
                                    'classes' => 'btn btn-sm btn-primary' . ($inCart ? ' RemoveFromGroupCart' : ' AddToGroupCart'),
                                    'label' => '',
                                    'datas' => ['cartgroupid' => $groupId, 'toggle' => 'tooltip', 'placement' => 'top', 'title' => (!$inCart ? _('Add to Cart') : _('Remove from Cart'))]
                                ]
                            ]);                            

                            $elt = [
                                "id" => $groupId,
                                "img" => '<i class="fas fa-users fa-2x"></i>',
                                "searchresult" => '<a href="' . $rootPath . $groupUri . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $displayName . '</a>',
                                "address" => "",
                                "type" => $groupTypeLabel,
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
                        $seenGroupIds = [];

                        foreach ($members as $member) {
                            $groupId = $member->getGroupId();

                            if (isset($seenGroupIds[$groupId])) {
                                continue;
                            }

                            $seenGroupIds[$groupId] = true;

                            $classification = "";
                            switch ($member->getGroupType()) {
                                case 4:
                                    $classification = _("Sunday Group");
                                    break;
                                default:
                                    $classification = _("Group");
                            }

                            if ($quickSearch) {
                                $elt = [
                                    'id' => 'group-' . $id++,
                                    'text' => $member->getDisplayName(),
                                    'uri' => $member->getUri()
                                ];
                            } else {                                
                                $inCart = isset($groupsInCart[$groupId]);

                                $res = $this->buildActionButtons([
                                    [
                                        'activate' => $shouldShowCart,
                                        'type' => 'a',
                                        'href' => $rootPath . '/v2/group/editor/' . $groupId,
                                        'icon' => '<i class="fas fa-pencil-alt"></i>',
                                        'classes' => 'btn btn-sm btn-outline-primary',
                                        'label' => '',
                                        'datas' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('Edit')]
                                    ],
                                    [
                                        'activate' => $shouldShowCart,
                                        'type' => 'a',
                                        'href' => $rootPath . '/v2/group/' . $groupId . '/view',
                                        'icon' => '<i class="fas fa-search-plus"></i>',
                                        'classes' => 'btn btn-sm btn-outline-secondary',
                                        'label' => '',
                                        'tooltip' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('View')]
                                    ],
                                    [
                                        'activate' => $shouldShowCart,
                                        'type' => 'a',
                                        'href' => '',
                                        'icon' => (!$inCart ? '<i class="fas fa-cart-plus fa-inverse"></i>' : '<i class="fas fa-times"></i>'),
                                        'classes' => 'btn btn-sm btn-primary' . ($inCart ? ' RemoveFromGroupCart' : ' AddToGroupCart'),
                                        'label' => '',
                                        'datas' => ['cartgroupid' => $groupId, 'toggle' => 'tooltip', 'placement' => 'top', 'title' => (!$inCart ? _('Add to Cart') : _('Remove from Cart'))]
                                    ]
                                ]);                                

                                $elt = [
                                    "id" => $groupId,
                                    "img" => '<i class="fas fa-users fa-2x"></i>',
                                    "searchresult" => '<a href="' . $rootPath . '/v2/group/' . $groupId . '/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . $member->getDisplayName() . '</a>',
                                    "address" => "",
                                    "type" => $groupTypeLabel,
                                    "realType" => $this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => $classification,
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => [$member->getPersonId()],
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
