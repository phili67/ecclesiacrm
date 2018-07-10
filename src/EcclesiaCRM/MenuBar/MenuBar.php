<?php
/*******************************************************************************
*
*  filename    : MenuBar.php
*  website     : http://www.ecclesiacrm.com
*  function    : List all Church Events
*
*  This code is under copyright not under MIT Licence
*  copyright   : 2018 Philippe Logel all right reserved not MIT licence
*                This code can't be incoprorated in another software without any authorizaion
*  Updated     : 2018/06/3
*
******************************************************************************/

namespace EcclesiaCRM\MenuBar;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Map\GroupTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\DepositQuery;



class MenuBar {
    private $_title;
    private $_menus = [];
    private $_maxStr = 25;// maximum numbers of char in the menu items
    
    
    // Simple : Constructor
    public function __construct($title)
    {
      $this->_title = $title;
      $this->createMenuBar();
    }
    
    public function addMenu ($menu)
    {
      array_push($this->_menus, $menu);
    }
    
    private function addGroups()
    {
      // the assigned Groups
      $menu = new Menu ("Groups","fa fa-tag","#",true);
      
      $menuItem = new Menu ("List Groups","fa fa-angle-double-right","GroupList.php",$_SESSION['user']->isAddRecordsEnabled(),$menu);
      
      $listOptions = ListOptionQuery::Create()
                    ->filterById(3)
                    ->orderByOptionName()
                    ->find();

      foreach ($listOptions as $listOption) {
          if ($listOption->getOptionId() != 4) {// we avoid the sundaySchool, it's done under
              $groups=GroupQuery::Create()
                  ->filterByType($listOption->getOptionId())
                  ->orderByName()
                  ->find();

              if ($groups->count()>0) {// only if the groups exist : !empty doesn't work !
                 
                  $menuItem = new Menu ($listOption->getOptionName(),"fa fa-user-o","#",true,$menu);
                  
                  foreach ($groups as $group) {
                      $str = $group->getName();
                      if (strlen($str)>$this->_maxStr) {
                          $str = substr($str, 0, $this->_maxStr-3)." ...";
                      }

                      $menuItemItem = new Menu ($str,"fa fa-angle-double-right","GroupView.php?GroupID=" . $group->getID(),true,$menuItem);
                      $menuItemItem->addLink("GroupEditor.php?GroupID=" . $group->getID());
                      $menuItemItem->addLink("GroupPropsFormEditor.php?GroupID=" . $group->getID());
                      if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
                        $menuItemItem->addLink("MapUsingLeaflet.php?GroupID=" . $group->getID());
                      } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
                        $menuItemItem->addLink("MapUsingGoogle.php?GroupID=" . $group->getID());
                      } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
                        $menuItemItem->addLink("MapUsingBing.php?GroupID=" . $group->getID());
                      }
                  }
              }
          }
      }
      
      // now we're searching the unclassified groups
      $groups=GroupQuery::Create()
                  ->filterByType(0)
                  ->orderByName()
                  ->find();

      if ($groups->count()>0) {// only if the groups exist : !empty doesn't work !
          $menuItem = new Menu ("Unassigned","fa fa-user-o","#",true,$menu);

          foreach ($groups as $group) {
              $menuItemItem = new Menu ( $group->getName(),"fa fa-angle-double-right","GroupView.php?GroupID=" . $group->getID(),true,$menuItem);
              $menuItemItem->addLink("GroupEditor.php?GroupID=" . $group->getID());
              $menuItemItem->addLink("GroupPropsFormEditor.php?GroupID=" . $group->getID());
          }
      }
      
      $menuItem = new Menu ("Group Assignment Helper","fa fa-angle-double-right","SelectList.php?mode=groupassign",true,$menu);
            
      $this->addMenu($menu);
    }
    
    private function addSundaySchoolGroups()
    {
      //Get the Properties assigned to all the sunday Group
      $ormAssignedProperties = Record2propertyR2pQuery::Create()
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                      ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_RECORD_ID,GroupTableMap::COL_GRP_ID,Criteria::LEFT_JOIN)
                      ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                      ->addAsColumn("GroupId",GroupTableMap::COL_GRP_ID)
                      ->addAsColumn("GroupName",GroupTableMap::COL_GRP_NAME)
                      ->where(PropertyTableMap::COL_PRO_CLASS." = 'm' AND ".GroupTableMap::COL_GRP_TYPE." = '4' AND ". PropertyTypeTableMap::COL_PRT_NAME." = 'MENU'")
                      ->addAscendingOrderByColumn('ProName')
                      ->addAscendingOrderByColumn('groupName')
                      ->find();

      //Get the sunday groups not assigned by properties
      $ormWithoutAssignedProperties = GroupQuery::Create()
                      ->addJoin(GroupTableMap::COL_GRP_ID,Record2propertyR2pTableMap::COL_R2P_RECORD_ID,Criteria::LEFT_JOIN)
                      ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                      ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                      ->addAsColumn('PrtName',PropertyTypeTableMap::COL_PRT_NAME)
                      ->addAsColumn("GroupId",GroupTableMap::COL_GRP_ID)
                      ->addAsColumn("GroupName",GroupTableMap::COL_GRP_NAME)
                      ->where("((".Record2propertyR2pTableMap::COL_R2P_RECORD_ID." IS NULL) OR (".PropertyTypeTableMap::COL_PRT_NAME." != 'Menu')) AND ".GroupTableMap::COL_GRP_TYPE." = '4'")
                      ->addAscendingOrderByColumn('groupName')
                      ->find();
                      
      $menu = new Menu ("Sunday School","fa fa-child","#",true);
      
      $menuItem = new Menu ("Dashboard","fa fa-angle-double-right","sundayschool/SundaySchoolDashboard.php",true,$menu);
      
      
      $property = '';
      foreach ($ormAssignedProperties as $ormAssignedProperty) {
          if ($ormAssignedProperty->getProName() != $property) {
            $menuItem = new Menu ($ormAssignedProperty->getProName(),"fa fa-user-o","#",true,$menu);
            
            $property = $ormAssignedProperty->getProName();
          }

          $str = gettext($ormAssignedProperty->getGroupName());
          if (strlen($str)>$this->_maxStr) {
              $str = substr($str, 0, $this->_maxStr-3)." ...";
          }
          
          $menuItemItem = new Menu ($str,"fa fa-angle-double-right","sundayschool/SundaySchoolClassView.php?groupId=" . $ormAssignedProperty->getGroupId(),true,$menuItem);
          $menuItemItem->addLink("GroupEditor.php?GroupID=" . $ormAssignedProperty->getGroupId());
          $menuItemItem->addLink("GroupView.php?GroupID=" . $ormAssignedProperty->getGroupId());
      }
      
      // the non assigned group to a group property
      if ($ormWithoutAssignedProperties->count()>0) {// only if the groups exist : !empty doesn't work !
          $menuItem = new Menu ("Unassigned","fa fa-user-o","#",true,$menu);
          
          foreach ($ormWithoutAssignedProperties as $ormWithoutAssignedProperty) {
              $str = gettext($ormWithoutAssignedProperty->getGroupName());
              if (strlen($str)>$this->_maxStr) {
                  $str = substr($str, 0, $this->_maxStr-3)." ...";
              }
              
              $menuItemItem = new Menu ($str,"fa fa-angle-double-right","sundayschool/SundaySchoolClassView.php?groupId=" . $ormWithoutAssignedProperty->getGroupId(),true,$menuItem);
              $menuItemItem->addLink("GroupEditor.php?GroupID=" . $ormWithoutAssignedProperty->getGroupId());
              $menuItemItem->addLink("GroupView.php?GroupID=" . $ormWithoutAssignedProperty->getGroupId());
          }
      }
      
      $this->addMenu($menu);
    }
    
    private function createMenuBar ()
    {
      // home Area
      $menu = new Menu ("Personal area","fa fa-home","",true);

        $menuItem = new Menu ("Home","fa fa-user","PersonView.php?PersonID=".$_SESSION['user']->getPersonId(),true,$menu);
        $menuItem = new Menu ("Change Password","fa fa-key","UserPasswordChange.php",true,$menu);
        $menuItem = new Menu ("Change Settings","fa fa-gear","SettingsIndividual.php",true,$menu);
        $menuItem = new Menu ("Documents","fa fa-file","PersonView.php?PersonID=".$_SESSION['user']->getPersonId()."&documents=true",true,$menu);

      $this->addMenu($menu);
      
      // the Events Menu
      $menu = new Menu ("Events","fa fa-ticket fa-calendar pull-right&quot;","",true);
        // add the badges
        $menu->addBadge('label bg-blue pull-right','AnniversaryNumber',0);
        $menu->addBadge('label bg-red pull-right','BirthdateNumber',0);
        $menu->addBadge('label bg-yellow pull-right','EventsNumber',0);

        $menuItem = new Menu ("Calendar","fa fa-calendar fa-calendar pull-left&quot;","Calendar.php",true,$menu);
        if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
          $menuItem = new Menu ("View on Map","fa fa-map-o","MapUsingLeaflet.php",true,$menu);
        } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
          $menuItem = new Menu ("View on Map","fa fa-map-o","MapUsingGoogle.php",true,$menu);
        } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
          $menuItem = new Menu ("View on Map","fa fa-map-o","MapUsingBing.php",true,$menu);
        }

        $menuItem = new Menu ("List Church Events","fa fa-angle-double-right","ListEvents.php",true,$menu);
        $menuItem = new Menu ("List Event Types","fa fa-angle-double-right","EventNames.php",$_SESSION['user']->isAdmin(),$menu);
        $menuItem = new Menu ("Check-in and Check-out","fa fa-angle-double-right","Checkin.php",true,$menu);
      
      $this->addMenu($menu);
      
      // the People menu
      $menu = new Menu ("People","fa fa-users","#",true);
      
        $menuItem = new Menu ("Dashboard","fa fa-angle-double-right","PeopleDashboard.php",$_SESSION['user']->isAddRecordsEnabled(),$menu);        
        $menuItem->addLink("MapUsingGoogle.php?GroupID=-1");
        $menuItem->addLink("GeoPage.php");
        $menuItem->addLink("UpdateAllLatLon.php");
        
        $menuItem = new Menu ("Add New Person","fa fa-angle-double-right","PersonEditor.php",$_SESSION['user']->isAddRecordsEnabled(),$menu);
        $menuItem = new Menu ("View All Persons","fa fa-angle-double-right","SelectList.php?mode=person",true,$menu);
        $menuItem = new Menu ("Add New Family","fa fa-angle-double-right","FamilyEditor.php",$_SESSION['user']->isAddRecordsEnabled(),$menu);
        $menuItem = new Menu ("View Active Families","fa fa-angle-double-right","FamilyList.php",true,$menu);
        $menuItem = new Menu ("View Inactive Families","fa fa-angle-double-right","FamilyList.php?mode=inactive",true,$menu);

      $this->addMenu($menu);
      
      // we add the groups
      $this->addGroups();
      
      // we add the sundayschool groups
      $this->addSundaySchoolGroups();
      
      
      // the Email
      $menu = new Menu ("Email","fa fa-envelope","email/Dashboard.php",true);
      $this->addMenu($menu);
      
      // The deposit      
      $menu = new Menu ("Deposit","fa fa-bank","#",$_SESSION['user']->isFinanceEnabled());
        // add the badges
        $deposit = DepositQuery::Create()->findOneById($_SESSION['iCurrentDeposit']);
        $deposits = DepositQuery::Create()->find();
        
        $numberDeposit = 0;
        
        if (!empty($deposits)) {
          $numberDeposit = $deposits->count();
        }

        //echo '<small class="badge pull-right bg-green count-deposit">'.$numberDeposit. "</small>".((!empty($deposit))?('<small class="badge pull-right bg-blue current-deposit" data-id="'.$_SESSION['iCurrentDeposit'].'">'.gettext("Current")." : ".$_SESSION['iCurrentDeposit'] . "</small>"):"")."\n";
        $menu->addBadge('badge pull-right bg-green count-deposit','',$numberDeposit);
        if (!empty($deposit)) {
          $menu->addBadge('badge pull-right bg-blue current-deposit','',gettext("Current")." : ".$_SESSION['iCurrentDeposit'],$_SESSION['iCurrentDeposit']);
        }

        $menuItem = new Menu ("Envelope Manager","fa fa-angle-double-right","ManageEnvelopes.php",$_SESSION['user']->isFinanceEnabled(),$menu);
        $menuItem = new Menu ("View All Deposits","fa fa-angle-double-right","FindDepositSlip.php",$_SESSION['user']->isFinanceEnabled(),$menu);
        $menuItem = new Menu ("Deposit Reports","fa fa-angle-double-right","FinancialReports.php",$_SESSION['user']->isFinanceEnabled(),$menu);
        $menuItem = new Menu (gettext("Edit Deposit Slip").' : <small class="badge pull-right bg-blue current-deposit-item"> #'.$_SESSION['iCurrentDeposit'].'</small>',"fa fa-angle-double-right","DepositSlipEditor.php?DepositSlipID=".$_SESSION['iCurrentDeposit'],$_SESSION['user']->isFinanceEnabled(),$menu,"deposit-current-deposit-item");
      
      if ($_SESSION['user']->isFinanceEnabled()) {
        $this->addMenu($menu);
      }
      
      // the menu Fundraisers
      $menu = new Menu ("Fundraiser","fa fa-money","#",$_SESSION['user']->isFinanceEnabled());

        $menuItem = new Menu ("View All Fundraisers","fa fa-angle-double-right","FindFundRaiser.php",$_SESSION['user']->isFinanceEnabled(),$menu);
        $menuItem = new Menu ("Create New Fundraiser","fa fa-angle-double-right","FundRaiserEditor.php?FundRaiserID=-1",$_SESSION['user']->isFinanceEnabled(),$menu);
        $menuItem = new Menu ("Edit Fundraiser","fa fa-angle-double-right","FundRaiserEditor.php",$_SESSION['user']->isFinanceEnabled(),$menu);
        $menuItem = new Menu ("View Buyers","fa fa-angle-double-right","PaddleNumList.php",$_SESSION['user']->isFinanceEnabled(),$menu);
        $menuItem = new Menu ("Add Donors to Buyer List","fa fa-angle-double-right","AddDonors.php",$_SESSION['user']->isFinanceEnabled(),$menu);

      if ($_SESSION['user']->isFinanceEnabled()) {
        $this->addMenu($menu);
      }
      
      // the menu report
      $menu = new Menu ("Data/Reports","fa fa-file-pdf-o","#",$_SESSION['user']->isAdmin());

        $menuItem = new Menu ("Reports Menu","fa fa-angle-double-right","ReportList.php",$_SESSION['user']->isAdmin(),$menu);
        $menuItem = new Menu ("Query Menu","fa fa-angle-double-right","QueryList.php",$_SESSION['user']->isAdmin(),$menu);

      if ($_SESSION['user']->isAdmin()) {
        $this->addMenu($menu);
      }

    }
    
    private function is_treeview_Opened($links)
    {
      $link = $_SERVER['REQUEST_URI'];
      
      foreach($links as $l) {
         if (!strcmp(SystemURLs::getRootPath() . "/" . $l,$link)) {
            return " active";
         }             
      }
      
      return "";
    }
    
    private function is_treeview_menu_open ($links)
    {
      $link = $_SERVER['REQUEST_URI'];
      
      foreach($links as $l) {
         if (!strcmp(SystemURLs::getRootPath() . "/" . $l,$link)) {
            return "class=\"treeview-menu menu-open\" style=\"display: block;\"";
         }             
      }
      
      return "class=\"treeview-menu menu-open\"";
    }
    
    private function is_li_class_active ($links)
    {
      $link = $_SERVER['REQUEST_URI'];
      
      foreach($links as $l) {
         if (!strcmp(SystemURLs::getRootPath() . "/" . $l,$link)) {
            return "class=\"active\"";
         }             
      }
      
      return "";
    }
    
    private function addSubMenu($menus)
    {
      foreach ($menus as $menu) {
        echo "<li ".$this->is_li_class_active($menu->getLinks())."><a href=\"".SystemURLs::getRootPath() . "/" . $menu->getUri()."\" ".(($menu->getClass() != null)?"class=\"".$menu->getClass()."\"":"")."><i class=\"".$menu->getIcon()."\"></i>".gettext($menu->getTitle())."</a>\n";
        
        if (count($menu->subMenu()) > 0) {
          echo "<ul ".$this->is_treeview_menu_open($menu->getLinks()).">\n";
            $this->addSubMenu($menu->subMenu());
          echo "</ul>\n";
        }
        
        echo "</li>\n";
      }
    }
    
    public function renderMenu()
    {
      // render all the menus submenus etc ...
      echo "<ul class=\"sidebar-menu\">\n";
      foreach ($this->_menus as $menu) {
        if (count($menu->subMenu()) == 0) {
          echo "<li ".$this->is_li_class_active($menu->getLinks()).">\n";
          echo "<a href=\"".SystemURLs::getRootPath() . "/" . $menu->getUri()."\">\n";
          echo "<i class=\"".$menu->getIcon()."\"></i> <span>".gettext($menu->getTitle())."</span>\n";
          echo "</a>\n";
          echo "</li>\n";
        } else {// we are in the case of a treeview
          echo "<li class=\"treeview".$this->is_treeview_Opened($menu->getLinks())."\">";
            echo "<a href=\"".SystemURLs::getRootPath() . "/" . $menu->getUri()."\">\n";
            echo " <i class=\"".$menu->getIcon()."\"></i>\n";
            echo " <span>".gettext($menu->getTitle())."</span>\n";
            echo " <i class=\"fa fa-angle-left pull-right\"></i>\n";
            if (count($menu->getBadges()) > 0) {
              foreach ($menu->getBadges() as $badge) {
                if ($badge['id'] != ''){
                  echo "<small class=\"".$badge['class']."\" id=\"".$badge['id']."\">".$badge['value']."</small>\n";
                } else if ($badge['data-id'] != ''){
                  echo "<small class=\"".$badge['class']."\" data-id=\"".$badge['data-id']."\">".$badge['value']."</small>\n"; 
                } else {
                  echo "<small class=\"".$badge['class']."\">".$badge['value']."</small>\n";
                }
              }
            }
            echo "</a>\n";
            echo "<ul ".$this->is_treeview_menu_open($menu->getLinks()).">\n";
              $this->addSubMenu($menu->subMenu());
            echo "</ul>\n";
          echo "</li>\n";
        }          
      }
      echo "</ul>\n";
    }
}