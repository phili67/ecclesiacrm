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

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\MenuLinkQuery;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginMenuBarreQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\MailChimpService;

use EcclesiaCRM\SessionUser;


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

    private function addPluginMenus($type, $main_menu=null) {
        $plugins = PluginQuery::create()->filterByCategory($type)->findByActiv(true);

        foreach ($plugins as $plugin) {
            if ( !( SessionUser::getUser()->isEnableForPlugin($plugin->getName())
                or SessionUser::getUser()->isAdminEnableForPlugin($plugin->getName()) ) ) break;

            $menuBarItems = PluginMenuBarreQuery::create()->filterByName($plugin->getName())->find();
            $first_One = true;
            $menu_count = $menuBarItems->count();
            $menu = null;
            foreach ($menuBarItems as $menuBarItem) {
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
                    $menu = new Menu (_($menuBarItem->getDisplayName()),
                        $menuBarItem->getIcon(), $menuBarItem->getURL(), $grp_sec , ($plugin->getCategoryPosition() == 'inside_category_menu')?$main_menu:null);

                    if ($plugin->getCategoryPosition() == 'after_category_menu') {
                        $this->addMenu($menu);
                    }

                    /*if ($menu_count > 1 and $plugin->getCategoryPosition() != 'after_category_menu') {
                        $menuItem = new Menu (_($menuBarItem->getDisplayName()), "fas fa-tachometer-alt", $menuBarItem->getURL(), true, $menu);
                    }*/
                    $first_One = false;
                } else {
                    $menuItem = new Menu (_($menuBarItem->getDisplayName()), $menuBarItem->getIcon(), $menuBarItem->getURL(), $grp_sec, $menu);
                }
            }
        }
    }

    private function addHomeArea()
    {
        // home Area
        $menu = new Menu (_("Private Space"), "fas fa-home", "", true);

        $menuItem = new Menu (_("Home"), "fas fa-user", "v2/people/person/view/" . SessionUser::getUser()->getPersonId(), true, $menu);
        $menuItem = new Menu (_("Change Password"), "fas fa-key", "v2/users/change/password", true, $menu);
        $menuItem = new Menu (_("Change Settings"), "fas fa-cog", "v2/users/settings", true, $menu);
        $menuItem = new Menu (_("Documents"), "fas fa-file", "v2/people/person/view/" . SessionUser::getUser()->getPersonId() . "/Documents", true, $menu);

        if (SessionUser::getUser()->isEDrive()) {
            $menuItem = new Menu (_("EDrive"), "fas fa-cloud", "v2/people/person/view/" . SessionUser::getUser()->getPersonId() . "/eDrive", true, $menu);
        }

        if (SystemConfig::getBooleanValue("bEnabledMenuLinks")) {
            $this->addPersonMenuLinks($menu);
        }

        $this->addPluginMenus('Personal', $menu);

        $this->addMenu($menu);
    }

    public function addGDPRMenu()
    {
        // the GDPR Menu
        $menu = new Menu (_("GDPR"), "fas fa-shield-alt", "", true);
        $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/gdpr", true, $menu);
        $menuItem = new Menu (_("Data Structure"), "fas fa-user-secret", "v2/gdpr/gdprdatastructure", true, $menu);
        $menuItem = new Menu (_("View Inactive Persons"), "fas fa-users", "v2/personlist/GDRP", true, $menu);
        $menuItem = new Menu (_("View Inactive Families"), "fas fa-users-slash", "v2/familylist/GDRP", true, $menu);

        $this->addPluginMenus('GDPR', $menu);

        $this->addMenu($menu);
    }

    public function addEventMenu()
    {
        // the Events Menu
        $menu = new Menu (_("Events") . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "fas fa-ticket &quot;", "", true);
        // add the badges
        $menu->addBadge('badge badge-warning', 'EventsNumber', 0);
        $menu->addBadge('badge badge-danger', 'BirthdateNumber', 0);
        $menu->addBadge('badge badge-primary', 'AnniversaryNumber', 0);// badge à la place de label

        $menuItem = new Menu (_("Calendar"), "far fa-calendar-alt pull-left&quot;", "v2/calendar", true, $menu);

        if (SessionUser::getUser()->isShowMapEnabled()) {
            $menuItem = new Menu (_("View on Map"), "far fa-map", "v2/map/-2", true, $menu);
        }

        $menuItem = new Menu (_("List Church Events"), "far fa-calendar", "v2/calendar/events/list", true, $menu);
        $menuItem = new Menu (_("List Event Types"), "fas fa-cog", "EventNames.php", SessionUser::getUser()->isAdmin(), $menu);
        $menuItem = new Menu (_("Call the Register"), "fas fa-bullhorn", "v2/calendar/events/checkin", true, $menu);

        $this->addPluginMenus('Events', $menu);

        $this->addMenu($menu);
    }

    private function addPeopleMenu()
    {
        // the People menu
        $menu = new Menu (_("People") . " & " . _("Families"), "fas fa-families", "#", true);

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
        $menuItem->addLink("USISTAddressVerification.php");


        $menuItem = new Menu (_("Meta Search Engine"), "fas fa-search", "v2/people/list/none", true, $menu);
        if (SessionUser::getUser()->isShowMapEnabled()) {
            $menuItem = new Menu (_("View on Map"), "far fa-map", "v2/map/-1", true, $menu);
        }

        $menuItem = new Menu (_("Directory reports"), "fas fa-book", "v2/people/directory/report", true, $menu);

        if (SessionUser::getUser()->isEditRecordsEnabled()) {
            $menuItem = new Menu (_("Persons"), "fas fa-angle-double-right", "#", true, $menu);
            $menuItemItem = new Menu (_("Add New Person"), "fas fa-tachometer-alt", "PersonEditor.php", SessionUser::getUser()->isAddRecordsEnabled(), $menuItem);
            $menuItemItem = new Menu (_("View Single Persons"), "fas fa-user", "v2/people/list/singles", true, $menuItem);
            $menuItemItem = new Menu (_("View Active Persons"), "far fa-circle", "v2/people/list/person", true, $menuItem);
            $menuItemItem->addLink("v2/personlist");
            $menuItemItem = new Menu (_("View Inactive Persons"), "fas fa-user-slash", "v2/personlist/inactive", true, $menuItem);

            $menuItem = new Menu (_("Families"), "fas fa-angle-double-right", "#", true, $menu);
            $menuItemItem = new Menu (_("Add New Family"), "fas fa-tachometer-alt", "FamilyEditor.php", SessionUser::getUser()->isAddRecordsEnabled(), $menuItem);
            $menuItemItem = new Menu (_("View Active Families"), "fas fa-user-friends", "v2/people/list/family", true, $menuItem);
            $menuItemItem->addLink("v2/familylist");
            $menuItemItem = new Menu (_("View Inactive Families"), "fas fa-users-slash", "v2/familylist/inactive", true, $menuItem);

            $menuItem = new Menu (_("Empty Addresses"), "fas fa-angle-double-right", "v2/familylist/empty", true, $menu);

            if (SessionUser::getUser()->isAdmin()) {
                $menuItem = new Menu (_("Convert Individual to Address"), "fas fa-angle-double-right", "ConvertIndividualToAddress.php", true, $menu);
            }
        }

        $this->addPluginMenus('PEOPLE', $menu);

        $this->addMenu($menu);
    }

    private function addGroups()
    {
        // the assigned Groups
        $menu = new Menu (_("Groups"), "fas fa-users", "#", true);

        $menuItem = new Menu (_("List Groups"), "fas fa-tachometer-alt", "v2/group/list", SessionUser::getUser()->isAddRecordsEnabled(), $menu);
        $menuItem->addLink("OptionManager.php?mode=grptypes");
        $menuItem->addLink("OptionManager.php?mode=grptypes&ListID=3");


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
                    $menuItemItem->addLink("GroupPropsFormEditor.php?GroupID=" . $group->getID());
                    $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/1/normal");
                    $menuItemItem->addLink("v2/group/" . $group->getID() . "/badge/0/normal");

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
            ->filterByType(3) // normal groups
            ->orderByName()
            ->find();

        if ($groups->count() > 0) {// only if the groups exist : !empty doesn't work !
            $menuItem = new Menu (_("Unassigned"), "far fa-user", "#", true, $menu);

            foreach ($groups as $group) {
                $menuItemItem = new Menu ($group->getName(), "fas fa-angle-double-right", "v2/group/" . $group->getID() . "/view", true, $menuItem);
                $menuItemItem->addLink("v2/group/editor/" . $group->getID());
                $menuItemItem->addLink("GroupPropsFormEditor.php?GroupID=" . $group->getID());
            }
        }

        $menuItem = new Menu (_("Group Assignment Helper"), "far fa-circle", "v2/people/list/groupassign", true, $menu);

        $this->addPluginMenus('GROUP', $menu);

        $this->addMenu($menu);
    }

    private function addSundaySchoolGroups()
    {
        $menu = new Menu (_("Sunday School"), "fas fa-child", "#", true);

        $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/sundayschool/dashboard", true, $menu);
        $menuItem->addLink("v2/sundayschool/reports");
        $menuItem->addLink("OptionManager.php?mode=grptypesSundSchool");
        $menuItem->addLink("OptionManager.php?mode=grptypesSundSchool&ListID=3");


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
                    $menuItemItem->addLink("GroupPropsFormEditor.php?GroupID=" . $group->getID());
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

        $this->addPluginMenus('SundaySchool', $menu);

        $this->addMenu($menu);
    }

    private function addGlobalMenuLinks()
    {
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
        $menu = new Menu (_("Pastoral Care"), "fas fa-heartbeat", "#", true, null, "pastoralcare_menu");
        $menuItem1 = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/pastoralcare/dashboard", true, $menu);
        $menuItem1 = new Menu (_("By Classifications"), "fas fa-sort-amount-up-alt", "v2/pastoralcare/membersList", true, $menu);

        $this->addPluginMenus('PastoralCare', $menu);

        $this->addMenu($menu);
    }

    private function addMeeting()
    {
        $this->addPluginMenus('Meeting');
    }

    private function addMailMenu()
    {
        // the Email
        $menu = new Menu (_("Email"), "fas fa-envelope", "#", true);

        $mailchimp = new MailChimpService();

        $menuMain = new Menu (_("MailChimp"), "fab fa-mailchimp", "#", SessionUser::getUser()->isMailChimpEnabled(), $menu);

        $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "v2/mailchimp/dashboard", SessionUser::getUser()->isMailChimpEnabled(), $menuMain, "lists_class_main_menu");
        $menuItem->addLink("v2/mailchimp/duplicateemails");
        $menuItem->addLink("v2/mailchimp/debug");
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

        $this->addPluginMenus('Mail', $menu);

        $this->addMenu($menu);
    }

    private function addDepositMenu()
    {
        $menu = new Menu (_("Deposit") . "&nbsp;&nbsp;&nbsp;", "fa fa-cash-register", "#", SessionUser::getUser()->isFinanceEnabled());
        // add the badges
        $deposit = DepositQuery::Create()->findOneById($_SESSION['iCurrentDeposit']);
        $deposits = DepositQuery::Create()->find();

        $numberDeposit = 0;

        if (!empty($deposits)) {
            $numberDeposit = $deposits->count();
        }

        //echo '<small class="badge pull-right bg-green count-deposit">'.$numberDeposit. "</small>".((!empty($deposit))?('<small class="badge pull-right bg-blue current-deposit" data-id="'.$_SESSION['iCurrentDeposit'].'">'._("Current")." : ".$_SESSION['iCurrentDeposit'] . "</small>"):"")."\n";
        if (!empty($deposit)) {
            $menu->addBadge('badge badge-primary current-deposit', '', _("Current") . " : " . $_SESSION['iCurrentDeposit'], $_SESSION['iCurrentDeposit']);
        }
        $menu->addBadge('badge badge-success  count-deposit', '', $numberDeposit);


        $menuItem = new Menu (_("Envelope Manager"), "fas fa-envelope", "ManageEnvelopes.php", SessionUser::getUser()->isFinanceEnabled(), $menu);
        $menuItem = new Menu (_("View All Deposits"), "fas fa-tachometer-alt", "v2/deposit/find", SessionUser::getUser()->isFinanceEnabled(), $menu);
        $menuItem = new Menu (_("Electronic Payment Listing"), "fas fa-credit-card", "ElectronicPaymentList.php", SessionUser::getUser()->isFinanceEnabled(), $menu);
        $menuItem = new Menu (_("Deposit Reports"), "fas fa-file-pdf", "FinancialReports.php", SessionUser::getUser()->isFinanceEnabled(), $menu);
        $menuItem = new Menu (_("Edit Deposit Slip") . '   : &nbsp;&nbsp;<small class="badge right badge-primary current-deposit-item"> #' . $_SESSION['iCurrentDeposit'] . '</small>', "fas fa-file-invoice-dollar", "v2/deposit/slipeditor/" . $_SESSION['iCurrentDeposit'], SessionUser::getUser()->isFinanceEnabled(), $menu, "deposit-current-deposit-item");

        $this->addPluginMenus('Deposit', $menu);

        $this->addMenu($menu);
    }

    private function addFundraiserMenu()
    {
        // the menu Fundraisers
        if (!SessionUser::getUser()->isFinanceEnabled()) return;

        $menu = new Menu (_("Fundraiser"), "fas fa-money-check-alt", "#", SessionUser::getUser()->isFinanceEnabled());

        $menuItem = new Menu (_("Create New Fundraiser"), "fas fa-box", "FundRaiserEditor.php?FundRaiserID=-1", SessionUser::getUser()->isFinanceEnabled(), $menu);
        $menuItem = new Menu (_("View All Fundraisers"), "fas fa-eye", "v2/fundraiser/find", SessionUser::getUser()->isFinanceEnabled(), $menu);
        if (isset($_SESSION['iCurrentFundraiser'])) {
            $menuItem = new Menu (_("Edit Last Fundraiser") . '   : &nbsp;&nbsp;<small class="badge right badge-primary current-deposit-item"> #' . $_SESSION['iCurrentFundraiser'] . '</small>', "far fa-circle", "FundRaiserEditor.php?FundRaiserID=" . $_SESSION['iCurrentFundraiser'], SessionUser::getUser()->isFinanceEnabled(), $menu, "deposit-current-deposit-item");
        }
        if (isset($_SESSION['iCurrentFundraiser'])) {
            $menuItem->addLink("v2/fundraiser/paddlenum/list/" . $_SESSION['iCurrentFundraiser']);

        }

        if (isset($_SESSION['iCurrentFundraiser'])) {
            $menuItem->addLink('PaddleNumList.php?FundRaiserID=' . $_SESSION['iCurrentFundraiser']);
        }

        $this->addPluginMenus('Funds', $menu);

        $this->addMenu($menu);
    }

    private function createMenuBar()
    {

        $menuItem = new Menu (_("Dashboard"), "fas fa-tachometer-alt", "menu", true);
        $menuItem->addLink("v2/dashboard");

        $this->addMenu($menuItem);

        $this->addHomeArea();
        if (SessionUser::getUser()->isGdrpDpoEnabled() && SystemConfig::getValue('bGDPR')) {
            $this->addGDPRMenu();
        }

        if (SystemConfig::getBooleanValue("bEnabledEvents")) {
            $this->addEventMenu();
        }

        $this->addPeopleMenu();
        $this->addGroups();

        // we add the sundayschool groups
        if (SystemConfig::getBooleanValue("bEnabledSundaySchool")) {
            $this->addSundaySchoolGroups();
        }

        $this->addMeeting();

        if (SessionUser::getUser()->isPastoralCareEnabled()) {
            $this->addPastoralCare();
        }

        if (SessionUser::getUser()->isMailChimpEnabled()) {
            $this->addMailMenu();
        }

        // The deposit
        if (SystemConfig::getBooleanValue("bEnabledFinance") && SessionUser::getUser()->isFinanceEnabled()) {
            $this->addDepositMenu();
        }

        if (SystemConfig::getBooleanValue("bEnabledFundraiser")) {
            $this->addFundraiserMenu();
        }

        // the menu report
        $menu = new Menu (_("Data/Reports"), "far fa-file-pdf", "#", SessionUser::getUser()->isShowMenuQueryEnabled());

        $menuItem = new Menu (_("Reports Menu"), "far fa-circle", "ReportList.php", SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') || SystemConfig::getBooleanValue('bEnabledSundaySchool'), $menu);
        $menuItem = new Menu (_("Query Menu"), "fas fa-database", "QueryList.php", SessionUser::getUser()->isShowMenuQueryEnabled(), $menu);

        if (SessionUser::getUser()->isShowMenuQueryEnabled()) {
            $this->addMenu($menu);
        }


        if (SystemConfig::getBooleanValue("bEnabledMenuLinks")) {
            $this->addGlobalMenuLinks();
        }

        // we can add all the free menu you want to define
        $this->addPluginMenus('FreeMenu');
    }
}
