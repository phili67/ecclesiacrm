<?php
/*******************************************************************************
 *
 *  filename    : MenuBar.php
 *  website     : http://www.ecclesiacrm.com
 *  function    : List all Church Events
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorizaion
 *  Updated     : 2022-03-12
 *
 ******************************************************************************/

namespace EcclesiaCRM\MenuBar;

use EcclesiaCRM\Base\GroupManagerPersonQuery;
use EcclesiaCRM\Base\Person2group2roleP2g2rQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\MenuLinkQuery;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginMenuBarQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\Service\MailChimpService;

use EcclesiaCRM\SessionUser;

use Propel\Runtime\ActiveQuery\Criteria;

class MenuBar extends Menu
{
    private $_title;
    private $_maxStr = 21;// maximum numbers of char in the menu items


    // Simple : Constructor
    public function __construct($title)
    {
        $this->_title = $title;
        $this->createMenuBar();
    }

    private function addPluginMenus($type, $main_menu=null, $category_position = 'after_category_menu', $callBack = null ) {
        $plugins = PluginQuery::create()
            ->filterByCategory($type)
            ->filterByCategoryPosition($category_position)
            ->findByActiv(true);

        $isPluginEnabledForuser = false;

        foreach ($plugins as $plugin) {
            $name = $plugin->getName();

            if ( !( SessionUser::getUser()->isEnableForPlugin($plugin->getName())
                or SessionUser::getUser()->isAdminEnableForPlugin($plugin->getName()) ) ) break;

            $isPluginEnabledForuser = true;

            $menuBarItems = PluginMenuBarQuery::create()->filterByName($plugin->getName())->find();
            $first_One = true;            
            $menu = null;
            foreach ($menuBarItems as $menuBarItem) {
                if (!is_null($menuBarItem->getLinkParentId())) continue;
                $grp_sec = true;
                if ( SessionUser::getUser()->isAdminEnableForPlugin($plugin->getName()) ) {
                    // a plugin admin is locally a menu administrator
                    $grp_sec = true;
                } else if ( !is_null($menuBarItem->getGrpSec()) and $menuBarItem->getGrpSec() != '' ) {
                    $grp_sec = SessionUser::getUser()->getUserMainSettingByString($menuBarItem->getGrpSec());
                }
                if ($first_One) {
                    if ($grp_sec == false) {
                        break;
                    }
                    $menu = new Menu (dgettext("messages-".$name, $menuBarItem->getDisplayName()),
                        $menuBarItem->getIcon(), $menuBarItem->getURL(), $grp_sec , ($category_position == 'inside_category_menu')?$main_menu:null);

                    if ($category_position == 'after_category_menu') {
                        $this->addMenu($menu);
                    }

                    /*if ($menu_count > 1 and $plugin->getCategoryPosition() != 'after_category_menu') {
                        $menuItem = new Menu (_($menuBarItem->getDisplayName()), "fas fa-tachometer-alt", $menuBarItem->getURL(), true, $menu);
                    }*/
                    $first_One = false;
                } else {
                    $menuItem = new Menu (dgettext("messages-".$name, $menuBarItem->getDisplayName()), $menuBarItem->getIcon(), $menuBarItem->getURL(), $grp_sec, $menu);
                }

                $menuLinks = PluginMenuBarQuery::create()->findByLinkParentId($menuBarItem->getId());
                foreach ($menuLinks as $menuLink) {
                    if (!is_null($menuLink->getURL())) {
                        // we are in a case of a link : see mysql/install.sql => Table plugin_menu_bar : plgn_mb_parent_ID comment
                        if (!is_null($menuItem)) {
                            $menuItem->addLink($menuLink->getURL());
                        } else {
                            $menu->addLink($menuLink->getURL());
                        }
                    }
                }        
            }
        }

        return $isPluginEnabledForuser;
    }

    private function addHomeArea()
    {
        // home Area
        $menu = new Menu (_("Private Space"), "fas fa-home", "", true);

        $menuItem = new Menu (_("Home"), "fas fa-user", "v2/people/person/view/" . SessionUser::getUser()->getPersonId(), true, $menu);
        $menuItem->addLink( "v2/people/person/view/" . SessionUser::getUser()->getPersonId() . "/Group");
        
        $menuItem = new Menu (_("Change Password"), "fas fa-key", "v2/users/change/password", true, $menu);
        $menuItem = new Menu (_("Change Settings"), "fas fa-cog", "v2/users/settings", true, $menu);
        $menuItem = new Menu (_("Documents"), "fas fa-file", "v2/people/person/view/" . SessionUser::getUser()->getPersonId() . "/Documents", true, $menu);

        if (SystemConfig::getBooleanValue("bEnabledMenuLinks")) {
            $this->addPersonMenuLinks($menu);
        }

        $this->addPluginMenus('Personal', $menu, 'inside_category_menu');
        $this->addMenu($menu);       
        $this->addPluginMenus('Personal', $menu, 'after_category_menu');
    }

    public function addGDPRMenu()
    {
        // the GDPR Menu
        $menu = new Menu (_("GDPR"), "fas fa-shield-alt", "", true);

        if (SessionUser::getUser()->isGdrpDpoEnabled() && SystemConfig::getBooleanValue('bGDPR')) {
            $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/gdpr", true, $menu);
            $menuItem = new Menu (_("Data Structure"), "fas fa-user-secret", "v2/gdpr/gdprdatastructure", true, $menu);
            $menuItem = new Menu (_("View Inactive Persons"), "fas fa-users", "v2/personlist/GDRP", true, $menu);
            $menuItem = new Menu (_("View Inactive Families"), "fas fa-users-slash", "v2/familylist/GDRP", true, $menu);

            $this->addPluginMenus('GDPR', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('GDPR', $menu, 'after_category_menu');
        }
    }

    public function addEventMenu()
    {
        // the Events Menu
        $menu = new Menu (_("Events") . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "fa fa-calendar", "", true);
            
        if (SystemConfig::getBooleanValue("bEnabledEvents")) {
            // add the badges
            $menu->addBadge('badge badge-warning', 'EventsNumber', 0);
            $menu->addBadge('badge badge-danger', 'BirthdateNumber', 0);
            $menu->addBadge('badge badge-primary', 'AnniversaryNumber', 0);// badge à la place de label

            $menuItem = new Menu (_("Calendar"), "far fa-calendar-alt pull-left&quot;", "v2/calendar", true, $menu);

            if (SessionUser::getUser()->isShowMapEnabled()) {
                $menuItem = new Menu (_("View on Map"), "far fa-map", "v2/map/-2", true, $menu);
            }

            $menuItem = new Menu (_("List Church Events"), "far fa-calendar", "v2/calendar/events/list", true, $menu);
            $menuItem = new Menu (_("List Event Types"), "fas fa-cog", "v2/calendar/events/names", SessionUser::getUser()->isAdmin(), $menu);
            $menuItem->addLink("v2/calendar/events/types/edit");
            $menuItem = new Menu (_("Call the Register"), "fas fa-bullhorn", "v2/calendar/events/checkin", true, $menu);

            $this->addPluginMenus('Events', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('Events', $menu, 'after_category_menu');
        }         
    }

    private function addMediasMenu() : void {
        // the People menu
        $menu = new Menu (_("Medias"), ["fa-solid fa-photo-film","fas fa-music", "fa-solid fa-person-chalkboard"], "#", true);

        $ret1 = $this->addPluginMenus('MEDIAS', $menu, 'inside_category_menu');
        $this->addMenu($menu);       
        $ret2 = $this->addPluginMenus('MEDIAS', $menu, 'after_category_menu');    
        if ($ret1 == false and $ret2 == false) {
            // we have to purge the menu            
            $this->deleteLastMenu();
        }       

        return;
    }

    private function addPeopleMenu() : void
    {
        // the People menu
        $menu = new Menu (_("People") . " & " . _("Families"), ["fas fa-male", "fas fa-female", "fas fa-child"], "#", true);

        $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/people/dashboard", SessionUser::getUser()->isAddRecordsEnabled(), $menu);

        $menuItem->addLink("v2/people/geopage");
        $menuItem->addLink("v2/people/UpdateAllLatLon");
        $menuItem->addLink("members/self-register.php");
        $menuItem->addLink("members/self-verify-updates.php");
        $menuItem->addLink("members/online-pending-verify.php");
        $menuItem->addLink("v2/group/reports");
        $menuItem->addLink("v2/people/directory/report/Cart+Directory");
        $menuItem->addLink("v2/people/directory/report");
        $menuItem->addLink("v2/people/ReminderReport");
        $menuItem->addLink("v2/people/LettersAndLabels");
        $menuItem->addLink("v2/system/USISTAddress/Verification");


        $menuItem = new Menu (_("Meta Search Engine"), "fas fa-search", "v2/people/list/none", true, $menu);
        if (SessionUser::getUser()->isShowMapEnabled()) {
            $menuItem = new Menu (_("View on Map"), "far fa-map", "v2/map/-1", true, $menu);
        }

        if (SessionUser::getUser()->isCreateDirectoryEnabled()) {
            $menuItem = new Menu (_("Directory reports"), "fas fa-book", "v2/people/directory/report", true, $menu);
        }

        if (SessionUser::getUser()->isEditRecordsEnabled()) {
            $menuItem = new Menu (_("Persons"), "fas fa-angle-double-right", "#", true, $menu);
            $menuItemItem = new Menu (_("Add New Person"), "fas fa-tachometer-alt", "v2/people/person/editor", SessionUser::getUser()->isAddRecordsEnabled(), $menuItem);
            $menuItemItem = new Menu (_("View Single Persons"), "fas fa-user", "v2/people/list/singles", true, $menuItem);
            $menuItemItem = new Menu (_("View Active Persons"), "far fa-circle", "v2/people/list/person", true, $menuItem);
            $menuItemItem->addLink("v2/personlist");
            $menuItemItem = new Menu (_("View Inactive Persons"), "fas fa-user-slash", "v2/personlist/inactive", true, $menuItem);

            $menuItem = new Menu (_("Families"), "fas fa-angle-double-right", "#", true, $menu);
            $menuItemItem = new Menu (_("Add New Family"), "fas fa-tachometer-alt", "v2/people/family/editor", SessionUser::getUser()->isAddRecordsEnabled(), $menuItem);
            $menuItemItem = new Menu (_("View Active Families"), "fas fa-user-friends", "v2/people/list/family", true, $menuItem);
            $menuItemItem->addLink("v2/familylist");
            $menuItemItem = new Menu (_("View Inactive Families"), "fas fa-users-slash", "v2/familylist/inactive", true, $menuItem);

            $menuItem = new Menu (_("Empty Addresses"), "fas fa-angle-double-right", "v2/familylist/empty", true, $menu);

            if (SessionUser::getUser()->isAdmin()) {
                $menuItem = new Menu (_("Convert Individual to Address"), "fas fa-angle-double-right", "v2/system/convert/individual/address", true, $menu);
                $menuItem->addLink("v2/system/convert/individual/address/True");
            }
        }

        $this->addPluginMenus('PEOPLE', $menu, 'inside_category_menu');
        $this->addMenu($menu);       
        $this->addPluginMenus('PEOPLE', $menu, 'after_category_menu');
    }

    private function addHeadGroupMenus (bool $mainDashboard = true) : Menu
    {
        // the assigned Groups
        $menu = new Menu (_("Groups"), "fas fa-users", "#", true);

        if ($mainDashboard) {
            $menuItem = new Menu (_("List Groups"), "fas fa-tachometer-alt", "v2/group/list", SessionUser::getUser()->isAddRecordsEnabled(), $menu);
            $menuItem->addLink("v2/system/option/manager/grptypes");
            $menuItem->addLink("v2/system/option/manager/grptypes/3");
            $menuItem->addLink("v2/system/option/manager/grptypes/3#");
        } else {
            $menu->addLink("v2/system/option/manager/grptypes");
            $menu->addLink("v2/system/option/manager/grptypes/3");
            $menu->addLink("v2/system/option/manager/grptypes/3#");
        }

        return $menu;
    }
    private function addGroups()
    {        
        if ( SessionUser::getUser()->isSeePrivacyData() or SessionUser::getUser()->isManageGroups() ) {
            $menu = $this->addHeadGroupMenus();

            $listOptions = ListOptionQuery::Create()
                ->filterById(3) // the group category
                ->filterByOptionType('normal')
                ->orderByOptionSequence()
                ->find();

            foreach ($listOptions as $listOption) {
                $groups = GroupQuery::Create()
                    ->useGroupTypeQuery()
                    ->filterByListOptionId($listOption->getOptionId())
                    ->endUse()
                    ->filterByType(3)// normal groups
                    ->filterByActive(1)
                    ->orderByName()
                    ->find();

                if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !

                    $menuItem = new Menu ($listOption->getOptionName(), "far fa-user", "#", true, $menu);

                    foreach ($groups as $group) {
                        $str = $group->getName();
                        if (mb_strlen($str) > $this->_maxStr) {
                            $str = mb_substr($str, 0, $this->_maxStr - 3) . " …";
                        }

                        $menuItemItem = new Menu ($str, "far fa-circle", "v2/group/" . $group->getID() . "/view", true, $menuItem);
                        $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/props/Form/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/1/normal");
                        $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/0/normal");

                        if (SessionUser::getUser()->isShowMapEnabled()) {
                            $menuItemItem->addLink("v2/map/" . $group->getID());
                        }
                    }
                }
            }

            // now we're searching the unclassified groups
            if (SessionUser::getUser()->isManageGroupsEnabled()) {
                $groups = GroupQuery::Create()
                    ->useGroupTypeQuery()
                        ->filterByListOptionId(0)
                    ->endUse()
                    ->filterByType(3) // normal groups
                    ->filterByActive(1)
                    ->orderByName()
                    ->find();

                if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
                    $menuItem = new Menu (_("Unassigned"), "far fa-user", "#", true, $menu);

                    foreach ($groups as $group) {
                        $menuItemItem = new Menu ($group->getName(), "fas fa-angle-double-right", "v2/group/" . $group->getID() . "/view", true, $menuItem);
                        $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/props/Form/editor/" . $group->getID());
                    }
                }
            }

            // now we're searching the unactive groups
            if (SessionUser::getUser()->isManageGroupsEnabled()) {
                $groups = GroupQuery::Create()
                    ->filterByType(3) // normal groups
                    ->filterByActive(0)
                    ->orderByName()
                    ->find();

                if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
                    $menuItem = new Menu (_("Disabled"), "far fa-user", "#", true, $menu);

                    foreach ($groups as $group) {
                        $menuItemItem = new Menu ($group->getName(), "fas fa-angle-double-right", "v2/group/" . $group->getID() . "/view", true, $menuItem);
                        $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/props/Form/editor/" . $group->getID());
                    }
                }
        
                $menuItem = new Menu (_("Group Assignment Helper"), "far fa-circle", "v2/people/list/groupassign", true, $menu);
            }
        } else {
            // groupe managers
            $persons = GroupManagerPersonQuery::create()
                        ->usePersonQuery()
                        ->filterById(SessionUser::getUser()->getPersonId())
                        ->endUse()
                        ->find();

            $groupIds = [];
            foreach ($persons as $person) {
                $groupIds[] = $person->getGroupId();
            }

            $groups = GroupQuery::Create()
                ->filterById($groupIds)
                ->filterByType(3) // normal groups
                ->orderByName()
                ->find();

            $listOptions = ListOptionQuery::Create()
                ->filterById(3) // the group category
                ->filterByOptionType('normal')
                ->orderByOptionSequence()
                ->find();

            $menu = null;       
            
            $first_Time = true;

            foreach ($listOptions as $listOption) {
                $groups = GroupQuery::Create()
                    ->useGroupTypeQuery()
                        ->filterByListOptionId($listOption->getOptionId())
                    ->endUse()
                    ->filterById($groupIds)
                    ->filterByType(3)// normal groups
                    ->filterByActive(1)
                    ->orderByName()
                    ->find();

                if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
                    if ( $first_Time ) {
                        $menu = $this->addHeadGroupMenus();
                        $subMenu = new Menu (_("Group manager"), "fas fa-folder", "#", true, $menu);  
                        $first_Time = false;
                    }
            

                    $menuItem = new Menu ($listOption->getOptionName(), "far fa-user", "#", true, $subMenu);

                    foreach ($groups as $group) {
                        $str = $group->getName();
                        if (mb_strlen($str) > $this->_maxStr) {
                            $str = mb_substr($str, 0, $this->_maxStr - 3) . " …";
                        }

                        $menuItemItem = new Menu ($str, "far fa-circle", "v2/group/" . $group->getID() . "/view", true, $menuItem);
                        $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/props/Form/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/1/normal");
                        $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/0/normal");

                        if (SessionUser::getUser()->isShowMapEnabled()) {
                            $menuItemItem->addLink("v2/map/" . $group->getID());
                        }
                    }
                }
            }
            

            // belonger               
            $assignedGroups = Person2group2roleP2g2rQuery::Create()
                ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
                ->addMultipleJoin(
                    array(
                        array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID, ListOptionTableMap::COL_LST_OPTIONID),
                        array(GroupTableMap::COL_GRP_ROLELISTID, ListOptionTableMap::COL_LST_ID)),
                    Criteria::LEFT_JOIN)
                ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
                ->Where(Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID . ' = ' . SessionUser::getUser()->getPersonId() . ' ORDER BY grp_Name')
                ->addAsColumn('roleName', ListOptionTableMap::COL_LST_OPTIONNAME)
                ->addAsColumn('groupName', GroupTableMap::COL_GRP_NAME)                
                ->addAsColumn('groupId', GroupTableMap::COL_GRP_NAME)
                ->addAsColumn('hasSpecialProps', GroupTableMap::COL_GRP_HASSPECIALPROPS)
                ->find();

            $groupIds = [];
            foreach ($assignedGroups as $group) {
                $groupIds[] = $group->getGroupId();
            }

            $first_Time = true;
            
            foreach ($listOptions as $listOption) {
                $groups = GroupQuery::Create()
                    ->useGroupTypeQuery()
                        ->filterByListOptionId($listOption->getOptionId())
                    ->endUse()
                    ->filterById($groupIds)
                    ->filterByType(3)// normal groups
                    ->filterByActive(1)
                    ->orderByName()
                    ->find();

                if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
                    if ($first_Time) {
                        if ( is_null($menu) ) {
                            $menu = $this->addHeadGroupMenus();
                        }
                        $subMenu = new Menu (_("Personal groups"), "fas fa-folder", "#", true, $menu);   
                        $first_Time = false;
                    }

                    $menuItem = new Menu ($listOption->getOptionName(), "far fa-user", "#", true, $subMenu);

                    foreach ($groups as $group) {
                        $str = $group->getName();
                        if (mb_strlen($str) > $this->_maxStr) {
                            $str = mb_substr($str, 0, $this->_maxStr - 3) . " …";
                        }

                        $menuItemItem = new Menu ($str, "far fa-circle", "v2/group/" . $group->getID() . "/view", true, $menuItem);
                        $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/props/Form/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/1/normal");
                        $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/0/normal");

                        if (SessionUser::getUser()->isShowMapEnabled()) {
                            $menuItemItem->addLink("v2/map/" . $group->getID());
                        }
                    }
                }
            }
        }

        $no_menu = false;
        if ( is_null($menu) ) {
            $no_menu = true;
            $menu = $this->addHeadGroupMenus();
        }
        $ret1 = $this->addPluginMenus('GROUP', $menu, 'inside_category_menu');
        $this->addMenu($menu);       
        $ret2 = $this->addPluginMenus('GROUP', $menu, 'after_category_menu');    
        if ($no_menu == true and $ret1 == false and $ret2 == false) {
            // we have to purge the menu
            $this->deleteLastMenu();
        }        
    }

    private function addSundayGroupHeadMenus(bool $mainDashboard = true): Menu
    {
        $menu = new Menu (_("Sunday School"), "fas fa-child", "#", true);

        if ($mainDashboard) {
            $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/sundayschool/dashboard", true, $menu);
            $menuItem->addLink("v2/sundayschool/reports");
            $menuItem->addLink("v2/system/option/manager/grptypesSundSchool");
            $menuItem->addLink("v2/system/option/manager/grptypesSundSchool/3");
            $menuItem->addLink("v2/system/option/manager/grptypesSundSchool/3#");            
        } else {
            $menu->addLink("v2/system/option/manager/grptypesSundSchool");
            $menu->addLink("v2/system/option/manager/grptypesSundSchool/3");
            $menu->addLink("v2/system/option/manager/grptypesSundSchool/3#");
        }        

        return $menu;
    }

    private function addSundaySchoolGroups()
    {
        if ( SystemConfig::getBooleanValue("bEnabledSundaySchool")  ) {
            if ( SessionUser::getUser()->isSeePrivacyData() or SessionUser::getUser()->isManageGroups() ) {
                $menu = $this->addSundayGroupHeadMenus();

                $listOptions = ListOptionQuery::Create()
                    ->filterById(3) // the group category
                    ->filterByOptionType('sunday_school')
                    ->orderByOptionSequence()
                    ->find();

                foreach ($listOptions as $listOption) {
                    $groups = GroupQuery::Create()
                        ->useGroupTypeQuery()
                        ->filterByListOptionId($listOption->getOptionId())
                        ->endUse()
                        ->filterByType(4)// sunday groups
                        ->orderByName()
                        ->find();

                    if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !

                        $menuItem = new Menu ($listOption->getOptionName(), "fas fa-user", "#", true, $menu);

                        foreach ($groups as $group) {
                            $str = $group->getName();
                            if (mb_strlen($str) > $this->_maxStr) {
                                $str = mb_substr($str, 0, $this->_maxStr - 3) . " …";
                            }

                            $menuItemItem = new Menu ($str, "far fa-circle", "v2/sundayschool/" . $group->getID() . "/view", true, $menuItem);
                            $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                            $menuItemItem->addLink("v2/group/props/Form/editor/" . $group->getID());
                            $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/1/sundayschool");
                            $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/0/sundayschool");

                            if (SessionUser::getUser()->isShowMapEnabled()) {
                                $menuItemItem->addLink("v2/map/" . $group->getID());
                            }
                        }
                    }
                }

                // now we're searching the unclassified groups
                $groups = GroupQuery::Create()
                    ->useGroupTypeQuery()
                    ->filterByListOptionId(0)
                    ->endUse()
                    ->filterByType(4) // sunday group groups
                    ->orderByName()
                    ->find();

                if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
                    $menuItem = new Menu (_("Unassigned"), "far fa-user", "#", true, $menu);

                    foreach ($groups as $group) {
                        $str = _($group->getName());
                        if (mb_strlen($str) > $this->_maxStr) {
                            $str = mb_substr($str, 0, $this->_maxStr - 3) . " …";
                        }

                        $menuItemItem = new Menu ($str, "fas fa-angle-double-right", "v2/sundayschool/" . $group->getID() . "/view", true, $menuItem);
                        $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                        $menuItemItem->addLink("v2/group/" . $group->getID() . "/view");
                    }
                }                
            } else {
                // groupe managers
                $persons = GroupManagerPersonQuery::create()
                            ->usePersonQuery()
                                ->filterById(SessionUser::getUser()->getPersonId())
                            ->endUse()
                            ->find();

                $groupIds = [];
                foreach ($persons as $person) {
                    $groupIds[] = $person->getGroupId();
                }

                $groups = GroupQuery::Create()
                    ->filterById($groupIds)
                    ->orderByName()
                    ->find();

                $listOptions = ListOptionQuery::Create()
                    ->filterById(3) // the group category
                    ->filterByOptionType('sunday_school')
                    ->orderByOptionSequence()
                    ->find();

                $menu = null;       
                
                $first_Time = true;

                foreach ($listOptions as $listOption) {
                    $groups = GroupQuery::Create()
                        ->useGroupTypeQuery()
                            ->filterByListOptionId($listOption->getOptionId())
                        ->endUse()
                        ->filterById($groupIds)
                        ->filterByType(4)// sunday group
                        ->filterByActive(1)
                        ->orderByName()
                        ->find();

                    if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
                        if ( $first_Time ) {
                            $menu = $this->addSundayGroupHeadMenus(false);
                            $subMenu = new Menu (_("Group manager"), "fas fa-folder", "#", true, $menu);  
                            $first_Time = false;
                        }
                

                        $menuItem = new Menu ($listOption->getOptionName(), "far fa-user", "#", true, $subMenu);

                        foreach ($groups as $group) {
                            $str = $group->getName();
                            if (mb_strlen($str) > $this->_maxStr) {
                                $str = mb_substr($str, 0, $this->_maxStr - 3) . " …";
                            }

                            $menuItemItem = new Menu ($str, "fas fa-angle-double-right", "v2/sundayschool/" . $group->getID() . "/view", true, $menuItem);
                            $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                            $menuItemItem->addLink("v2/group/" . $group->getID() . "/view");

                            if (SessionUser::getUser()->isShowMapEnabled()) {
                                $menuItemItem->addLink("v2/map/" . $group->getID());
                            }
                        }
                    }
                }
                

                // belonger               
                $assignedGroups = Person2group2roleP2g2rQuery::Create()
                    ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
                    ->addMultipleJoin(
                        array(
                            array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID, ListOptionTableMap::COL_LST_OPTIONID),
                            array(GroupTableMap::COL_GRP_ROLELISTID, ListOptionTableMap::COL_LST_ID)),
                        Criteria::LEFT_JOIN)
                    ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
                    ->Where(Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID . ' = ' . SessionUser::getUser()->getPersonId() . ' ORDER BY grp_Name')
                    ->addAsColumn('roleName', ListOptionTableMap::COL_LST_OPTIONNAME)
                    ->addAsColumn('groupName', GroupTableMap::COL_GRP_NAME)                
                    ->addAsColumn('groupId', GroupTableMap::COL_GRP_NAME)
                    ->addAsColumn('hasSpecialProps', GroupTableMap::COL_GRP_HASSPECIALPROPS)
                    ->find();

                $groupIds = [];
                foreach ($assignedGroups as $group) {
                    $groupIds[] = $group->getGroupId();
                }

                $first_Time = true;
                
                foreach ($listOptions as $listOption) {
                    $groups = GroupQuery::Create()
                        ->useGroupTypeQuery()
                            ->filterByListOptionId($listOption->getOptionId())
                        ->endUse()
                        ->filterById($groupIds)
                        ->filterByType(4)// // sunday group
                        ->filterByActive(1)
                        ->orderByName()
                        ->find();

                    if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
                        if ($first_Time) {
                            if ( is_null($menu) ) {
                                $menu = $this->addSundayGroupHeadMenus(false);
                            }
                            $subMenu = new Menu (_("Personal groups"), "fas fa-folder", "#", true, $menu);   
                            $first_Time = false;
                        }

                        $menuItem = new Menu ($listOption->getOptionName(), "far fa-user", "#", true, $subMenu);

                        foreach ($groups as $group) {
                            $str = $group->getName();
                            if (mb_strlen($str) > $this->_maxStr) {
                                $str = mb_substr($str, 0, $this->_maxStr - 3) . " …";
                            }

                            $menuItemItem = new Menu ($str, "fas fa-angle-double-right", "v2/sundayschool/" . $group->getID() . "/view", true, $menuItem);
                            $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                            $menuItemItem->addLink("v2/group/" . $group->getID() . "/view");

                            if (SessionUser::getUser()->isShowMapEnabled()) {
                                $menuItemItem->addLink("v2/map/" . $group->getID());
                            }
                        }
                    }
                }

            }

            $no_menu = false;
            if ( is_null($menu) ) {
                $no_menu = true;
                $menu = $this->addHeadGroupMenus();
            }
            
            $ret1 = $this->addPluginMenus('SundaySchool', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $ret2 = $this->addPluginMenus('SundaySchool', $menu, 'after_category_menu');  

            if ($no_menu == true and $ret1 == false and $ret2 == false) {
                // we have to purge the menu            
                $this->deleteLastMenu();
            }   
        }                           
    }

    private function addGlobalMenuLinks()
    {
        if (SystemConfig::getBooleanValue("bEnabledMenuLinks")) {
            $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::ASC)->findByPersonId(null);

            if ($menuLinks->count()) {
                $menu = new Menu (_("Global Custom Menus"), "fas fa-link", "", true, null, "global_custom_menu");
                $menu->addLink("v2/menulinklist");

                foreach ($menuLinks as $menuLink) {
                    $menuItem = new Menu ($menuLink->getName(), "far fa-circle", $menuLink->getUri(), true, $menu);
                }
            } else {
                $menu = new Menu (_("Global Custom Menus"), "fas fa-link", "v2/menulinklist", true, null, "global_custom_menu");
            }

            $this->addMenu($menu);
        }   
    }

    private function addPersonMenuLinks($mainmenu)
    {
        $menuLinks = MenuLinkQuery::Create()->orderByOrder(Criteria::ASC)->findByPersonId(SessionUser::getUser()->getPersonId());

        $menuItem = new Menu (_("Custom Menus"), "fas fa-link", "#", true, $mainmenu, "personal_custom_menu_" . SessionUser::getUser()->getPersonId());
        $menuItem1 = new Menu (_("Dashboard"), "far fa-circle", "v2/menulinklist/" . SessionUser::getUser()->getPersonId(), true, $menuItem);

        foreach ($menuLinks as $menuLink) {
            $menuItemItem1 = new Menu ($menuLink->getName(), "fas fa-angle-double-right", $menuLink->getUri(), true, $menuItem);
        }
    }

    private function addPastoralCare()
    {
        if ( SystemConfig::getBooleanValue("bEnabledPastoralCare") and SessionUser::getUser()->isPastoralCareEnabled() ) {    
            
            $menu = new Menu (_("Pastoral Care"), "fas fa-heartbeat", "#", true);
            
            $menuItem1 = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/pastoralcare/dashboard", true, $menu);
            $menuItem1 = new Menu (_("By Classifications"), "fas fa-sort-amount-up-alt", "v2/pastoralcare/membersList", true, $menu);

            $this->addPluginMenus('PastoralCare', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('PastoralCare', $menu, 'after_category_menu');  
        }
    }

    private function addMeeting()
    {
        $isPluginEnabledForCurrentUser = $this->addPluginMenus('Meeting');
    }

    private function addCommunicationMenu()
    {
        if ( SystemConfig::getBooleanValue("bEnabledEmail") and SessionUser::getUser()->isEmailEnabled() ) {        
            // the Email
            $menu = new Menu (_("Communication"), "fas fa-envelope", "#", true);

            if (SessionUser::getUser()->isMailChimpEnabled()) {
                $mailchimp = new MailChimpService();

                $menuMain = new Menu (_("MailChimp"), "fab fa-mailchimp", "#", SessionUser::getUser()->isMailChimpEnabled(), $menu);

                $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/mailchimp/dashboard", SessionUser::getUser()->isMailChimpEnabled(), $menuMain, "lists_class_main_menu");
                $menuItem->addLink("v2/mailchimp/duplicateemails");
                $menuItem->addLink("v2/mailchimp/notinmailchimpemailspersons");
                $menuItem->addLink("v2/mailchimp/notinmailchimpemailsfamilies");


                $menuItemItem = new Menu (_("Email Lists"), "fas fa-list", "#", true, $menuMain, "lists_class_menu " . (($mailchimp->isLoaded()) ? "" : "hidden"));

                if ($mailchimp->isLoaded()) {// to accelerate the v2/dashboard the first time
                    $mcLists = $mailchimp->getLists();

                    foreach ($mcLists as $list) {
                        $menuItemItemItem = new Menu ($list['name']/*.' <small class="badge pull-right bg-blue current-deposit-item">'.$list['stats']['member_count'].'</small>'*/, "fas fa-mail-bulk", "v2/mailchimp/managelist/" . $list['id'], true, $menuItemItem, "listName" . $list['id']);

                        $campaigns = $mailchimp->getCampaignsFromListId($list['id']);

                        $campaigns = array_merge($campaigns[0], $campaigns[1]);

                        foreach ($campaigns as $campaign) {
                            //$menuItemItemItem = new Menu ($campaign['settings']['title'],"far fa-circle","email/MailChimp/ManageList.php?list_id=".$list['id'],true,$menuItemItemItem);
                            $menuItemItemItem->addLink("v2/mailchimp/campaign/" . $campaign['id']);
                        }
                    }
                } else {// we add just a false item
                    $menuItemItemItem = new Menu ("false item", "far fa-circle", "#", true, $menuItemItem, "#");
                }

                
            }   
            
            $this->addPluginMenus('Communication', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('Communication', $menu, 'after_category_menu');
        }
    }

    private function addDepositMenu()
    {
        $menu = new Menu (_("Deposit") . "&nbsp;&nbsp;&nbsp;", "fa fa-cash-register", "#", SessionUser::getUser()->isFinanceEnabled());

        if (SessionUser::getUser()->isFinanceEnabled()) {
            // add the badges
            $deposit = DepositQuery::Create()->findOneById($_SESSION['iCurrentDeposit']);
            $deposits = DepositQuery::Create()->find();

            $numberDeposit = 0;

            if (!empty($deposits)) {
                $numberDeposit = $deposits->count();
            }

            if (!empty($deposit)) {
                $menu->addBadge('badge badge-primary current-deposit', '', _("Current") . " : " . $_SESSION['iCurrentDeposit'], $_SESSION['iCurrentDeposit']);
            }
            $menu->addBadge('badge badge-success  count-deposit', '', $numberDeposit);

            //https://dev.ecclesiacrm.com/v2/deposit/egive/




            $menuItem = new Menu (_("Envelope Manager"), "fas fa-envelope", "v2/deposit/manage/envelopes", SessionUser::getUser()->isFinanceEnabled(), $menu);
            $menuItem = new Menu (_("View All Deposits"), "fas fa-tachometer-alt", "v2/deposit/find", SessionUser::getUser()->isFinanceEnabled(), $menu);
            $menuItem = new Menu (_("Electronic Payment Listing"), "fas fa-credit-card", "v2/deposit/electronic/payment/list", SessionUser::getUser()->isFinanceEnabled(), $menu);
            $menuItem = new Menu (_("Deposit Reports"), "fas fa-file-pdf", "v2/deposit/financial/reports", SessionUser::getUser()->isFinanceEnabled(), $menu);
            $menuItem = new Menu (_("Giving Report (Tax Statements)"), "fas fa-file-pdf", "v2/deposit/tax/report", SessionUser::getUser()->isFinanceEnabled(), $menu);
            $menuItem = new Menu (_("Edit Deposit Slip") . '   : &nbsp;&nbsp;<small class="badge right badge-primary current-deposit-item"> #' . $_SESSION['iCurrentDeposit'] . '</small>', "fas fa-file-invoice-dollar", "v2/deposit/slipeditor/" . $_SESSION['iCurrentDeposit'], SessionUser::getUser()->isFinanceEnabled(), $menu, "deposit-current-deposit-item");

            $menuItem->addLink("v2/deposit/egive/" . $_SESSION['iCurrentDeposit']);

            $this->addPluginMenus('Deposit', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('Deposit', $menu, 'after_category_menu');
        }        
    }

    private function addEdrive()
    {
        if (SystemConfig::getBooleanValue("bEnabledEdrive") and SessionUser::getUser()->isEDriveEnabled()) {
            $menu = new Menu (_("EDrive"), "fa fa-cloud", "#", SessionUser::getUser()->isFinanceEnabled());

            $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/edrive/dashboard", true, $menu);

            $this->addPluginMenus('EDrive', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('EDrive', $menu, 'after_category_menu');
        }
    }

    private function addFundraiserMenu()
    {
        if (SessionUser::getUser()->isDonationFundEnabled()) {
            $menu = new Menu (_("Fundraiser"), "fas fa-money-check-alt", "#", SessionUser::getUser()->isFinance());

            $menuItem = new Menu (_("Create New Fundraiser"), "fas fa-box", "v2/fundraiser/editor", SessionUser::getUser()->isFinance(), $menu);
            $menuItem = new Menu (_("View All Fundraisers"), "fas fa-eye", "v2/fundraiser/find", SessionUser::getUser()->isFinance(), $menu);
            if (isset($_SESSION['iCurrentFundraiser'])) {
                $menuItem = new Menu (_("Edit Last Fundraiser") . '   : &nbsp;&nbsp;<small class="badge right badge-primary current-deposit-item"> #' . $_SESSION['iCurrentFundraiser'] . '</small>', "far fa-circle", "v2/fundraiser/editor/" . $_SESSION['iCurrentFundraiser'], SessionUser::getUser()->isFinanceEnabled(), $menu, "deposit-current-deposit-item");
            }
            if (isset($_SESSION['iCurrentFundraiser'])) {
                $menuItem->addLink("v2/fundraiser/paddlenum/list/" . $_SESSION['iCurrentFundraiser']);

            }

            if (isset($_SESSION['iCurrentFundraiser'])) {
                $menuItem->addLink('PaddleNumList.php?FundRaiserID=' . $_SESSION['iCurrentFundraiser']);
            }

            $this->addPluginMenus('Funds', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('Funds', $menu, 'after_category_menu');
        }
    }

    private function addVolunteerMenu () {
        if (SystemConfig::getBooleanValue("bEnabledVolunteers")) {
            $menu = new Menu (_("Volunteers"), "fa-brands fa-servicestack", "#", SessionUser::getUser()->isFinanceEnabled());

            $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/volunteeropportunityeditor", true, $menu);

            $this->addPluginMenus('Volunteer', $menu, 'inside_category_menu');
            $this->addMenu($menu);       
            $this->addPluginMenus('Volunteer', $menu, 'after_category_menu');            
        }
    }

    private function createMenuBar()
    {

        $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/dashboard", true);
        $menuItem->addLink("v2/dashboard");

        $this->addMenu($menuItem);

        $this->addHomeArea();   
        $this->addEdrive();     
        $this->addGDPRMenu();        
        $this->addEventMenu();
        $this->addMediasMenu();
        $this->addPeopleMenu();   
        $this->addVolunteerMenu(); 
        $this->addGroups();
        $this->addSundaySchoolGroups();
        $this->addMeeting();        
        $this->addPastoralCare();
        $this->addCommunicationMenu();
        $this->addDepositMenu();
        
        $this->addFundraiserMenu();

        // the menu report
        if (SessionUser::getUser()->isShowMenuQueryEnabled()) {
            $menu = new Menu (_("Data/Reports"), "far fa-file-pdf", "#", SessionUser::getUser()->isShowMenuQueryEnabled());

            $menuItem = new Menu (_("Reports Menu"), "far fa-circle", "v2/system/report/list", SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') || SystemConfig::getBooleanValue('bEnabledSundaySchool'), $menu);
            $menuItem->addLink('v2/people/canvass/automation');

            $menuItem = new Menu (_("Query Menu"), "fas fa-database", "v2/query/list", SessionUser::getUser()->isShowMenuQueryEnabled(), $menu);

            for ($i=1;$i <100;$i++) {
                $menuItem->addLink('v2/query/view/'.$i);
            }

            $menuItem->addLink('v2/query/sql');

            if (SessionUser::getUser()->isShowMenuQueryEnabled()) {
                $this->addMenu($menu);
            }
        }


        if (SystemConfig::getBooleanValue("bEnabledMenuLinks")) {
            $this->addGlobalMenuLinks();
        }

        // we can add all the free menu you want to define after
        $this->addPluginMenus('FreeMenu');
    }
}
