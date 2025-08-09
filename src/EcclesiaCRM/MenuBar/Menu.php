<?php
/*******************************************************************************
*
*  filename    : Menu.php
*  website     : http://www.ecclesiacrm.com
*  function    : List all Church Events
*
*  This code is under copyright not under MIT Licence
*  copyright   : 2018 Philippe Logel all right reserved not MIT licence
*                This code can't be incorporated in another software without authorizaion
*  Updated     : 2018/06/3
*
******************************************************************************/

namespace EcclesiaCRM\MenuBar;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

class Menu {
    private $_title;                // "event"
    private $_uri     = '';         // email/Dashboard.php
    private $_icon;                 // "fas fa-ticket-alt fa-calendar pull-right"
    private $_links   = [];         // all the links from the menu and all the menu items
    private $_sec_grp;              // bAll, bAdmin, ....
    private $_parent  = null;       // the menu or MenuItem, a parent should have all the links of his child
    private $_session_var;          //  ?????
    private $_badges  = [];         // all the badges icon : [['class' => 'label bg-blue pull-right','id' => 'AnniversaryNumber', 'value' => '0'] .....]
    private $_menus  = [];
    private $_subMenus = [];        // all the subMenus
    private $_class = null;
    private $_uuid = 0;
    private $_maxStr = 27;// maximum numbers of char in the menu items


    // Simple : Constructor
    public function __construct($title,$icon,$uri,$sec_grp,$parent=null,$class=null)// we can set the parent and the class of the link
    {
      $this->_parent  = $parent;
      /*if (mb_strlen($title)>$this->_maxStr) {
          $title = mb_substr($title, 0, $this->_maxStr-3)." …";
      }*/
      $this->_title   = $title;
      $this->_icon    = $icon;
      $this->_uri     = $uri;
      $this->addLink($uri);
      $this->_sec_grp = $sec_grp;
      $this->_class   = $class;
      $this->_uuid = MiscUtils::gen_uuid();

      // We have to add the new menu to the parent menu
      if ($parent != null && $sec_grp) {// we add all only if sec_grp is set to true
        $parent->addSubMenu($this);
      }
    }

    public function addMenu ($menu)
    {
        array_push($this->_menus, $menu);
    }

    private function removeSubMenu (Menu $menu)
    {
        if (count($menu->_subMenus) == 0) {// we are at the end
          array_splice($menu->_badges, 0, count($menu->_badges));
          array_splice($menu->_links, 0, count($menu->_links)); 
          $menu->_class = null;
          $menu->_parent = null;
          $menu->_uri = '';
          $menu->_title   = '';
          $menu->_icon    = null;
          $menu->_sec_grp = null;        
          $menu->_uuid = -1;
        } else {
          foreach ($menu->_subMenus as $m) {
            $this->removeSubMenu ($m);
          }
        }
    }

    public function removeMenu (Menu $menu_to_delete) 
    {
        $place = 0;
        foreach ($this->_menus as $menu) {
          if ($menu->_uuid == $menu_to_delete->_uuid) {
            $this->removeSubMenu($menu);                          
            array_splice($this->_menus, $place, $place);
            break;
          }
          $place ++;
        }
    }

    public function deleteLastMenu () 
    {       
      $place = count($this->_menus) - 1;
      $this->removeSubMenu($this->_menus[$place]);
      array_splice($this->_menus, $place, $place);
    }

    public function addSubMenu($menu)
    {
      array_push($this->_subMenus, $menu);
    }

    public function subMenu()
    {
      return $this->_subMenus;
    }

    public function getClass()
    {
      return $this->_class;
    }

    public function setTitle($title)
    {
      $this->_title = $title;
    }

    public function getTitle()
    {
      return $this->_title;
    }

    public function setUri($uri)
    {
      $this->_uri = $uri;

      $this->addLink($uri);
    }

    public function getUri()
    {
      return $this->_uri;
    }

    public function addBadge($class,$id,$value,$data_id="") {// the class, id, value, and a special data-id field
    // examples:
    // <small class="label bg-blue pull-right" id="AnniversaryNumber">0</small>                =>  $menu->addBadge('label bg-blue pull-right','AnniversaryNumber',0);
    // <small class="label bg-red pull-right" id="BirthdateNumber">3</small>                   =>  $menu->addBadge('label bg-red pull-right','BirthdateNumber',0);
    // <small class="label bg-yellow pull-right" id="EventsNumber">0</small>                   =>  $menu->addBadge('label bg-yellow pull-right','EventsNumber',0);
    // <small class="badge pull-right bg-green count-deposit">3</small>                        =>  $menu->addBadge('badge pull-right bg-green count-deposit','',$numberDeposit);
    // <small class="badge pull-right bg-blue current-deposit" data-id="8">Current : 8</small> =>  $menu->addBadge('badge pull-right bg-blue current-deposit','',gettext("Current")." : ".$_SESSION['iCurrentDeposit'],$_SESSION['iCurrentDeposit']);

      $elt = ['class'=> $class,
              'id'   => $id,
              'value'  => $value,
              'data-id' => $data_id];

      array_push($this->_badges, $elt);
    }

    public function getBadges()
    {
      return $this->_badges;
    }

    public function clearBadges()
    {
      unset($this->_badges);
      $this->_badges = array();
    }

    public function setIcon($icon)
    {
      $this->_icon = $icon;
    }

    public function getIcon()
    {
        if ($this->_icon == "fas fa-families") {
            return '<i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i>';
        }
        return   ' <i class="'.$this->_icon.'"></i>';
    }

    public function addLink($link)
    {
      array_push($this->_links, $link);

      if ($this->_parent != null) {
        $this->_parent->addLink($link);
      }
    }

    public function getLinks()
    {
      return $this->_links;
    }

    public function clearLinks()
    {
      unset($this->_links);
      $this->_links = array();
    }

    public function setSecurityGroup($sec_grp)
    {
      $this->_sec_grp = $sec_grp;
    }

    public function getSecurityGroup()
    {
      return $this->_sec_grp;
    }

    public function setSessionVar($session_var)
    {
      $this->_session_var = $session_var;
    }

    public function getSessionVar()
    {
      return $this->_session_var;
    }

    private function is_treeview_Opened($links)
    {
        $link = $_SERVER['REQUEST_URI'];

        foreach($links as $l) {
            if (!strcmp(SystemURLs::getRootPath() . "/" . $l,$link)) {
                return " has-treeview menu-open";
            }
        }

        return "";
    }

    private function is_treeview_menu_open ($links)
    {
        $link = $_SERVER['REQUEST_URI'];

        foreach($links as $l) {
            if (!strcmp(SystemURLs::getRootPath() . "/" . $l,$link)) {
                return 'class="nav nav-treeview" style="display: block;"';
            }
        }

        return 'class="nav nav-treeview" style="display: none;"';
    }

    private function is_link_active ($links,$is_menu=false,$class=null)
    {
        $link = $_SERVER['REQUEST_URI'];

        foreach($links as $l) {
            if (!strcmp(SystemURLs::getRootPath() . "/" . $l,$link)) {
                return " active";
            } else if ($is_menu) {
                return "";
            }
        }

        return "";
    }

    private function buildSubMenu($menus)
    {
        foreach ($menus as $menu) {
            $url = $menu->getUri();
            $real_link = true;

            if (strpos($menu->getUri(),"http") === false) {
                $real_link = false;
                $url = SystemURLs::getRootPath() . (($url != "#")?"/":"") . $url;
            }

            echo '<li class="nav-item'.(($menu->getClass() != null)?" ".$menu->getClass():"").'">';
            echo '<a href="'.$url."\" ".(($real_link==true)?'target="_blank"':'').' class="nav-link '.$this->is_link_active($menu->getLinks(),(count($menu->subMenu()) > 0)?true:false).'">'.$menu->getIcon()." <p>"._($menu->getTitle())."</p>";
            if (count($menu->subMenu()) > 0) {
                echo '<i class="fas fa-angle-left right"></i>';
            }

            echo "</a>\n";

            if (count($menu->subMenu()) > 0) {
                echo "<ul ".$this->is_treeview_menu_open($menu->getLinks()).">\n";
                $this->buildSubMenu($menu->subMenu());
                echo "</ul>\n";
            }

            echo "</li>\n";
        }
    }

    public function renderMenu()
    {
        // render all the menus submenus etc …
        //echo '<nav class="mt-2"></nav><ul class="nav nav-pills nav-sidebar flex-column" data-widget="tree" role="menu" data-accordion="false">';
        foreach ($this->_menus as $menu) {
            if (count($menu->subMenu()) == 0) {
                echo '<li class="nav-item">';
                echo '<a href="'.SystemURLs::getRootPath() . '/' . $menu->getUri().'" class="nav-link'.$this->is_link_active($menu->getLinks(),false,$menu->getClass()).'">';
                echo $menu->getIcon()." <p>"._($menu->getTitle())."</p>\n";
                echo "</a>\n";
                echo "</li>\n";
            } else {// we are in the case of a treeview
                echo "<li class=\"nav-item has-treeview ".$this->is_treeview_Opened($menu->getLinks()).(($menu->getClass() != null)?" ".$menu->getClass():"")."\">";
                echo '<a href="#" class="nav-link '.$this->is_link_active($menu->getLinks(),false,$menu->getClass()).'">';// the menu keep his link #
                echo " ".$menu->getIcon()."\n";
                echo " <p>"._($menu->getTitle());
                echo ' <i class="fas fa-angle-left right"></i>'."\n";
                if (count($menu->getBadges()) > 0) {
                    foreach ($menu->getBadges() as $badge) {
                        if ($badge['id'] != ''){
                            echo "<span class=\"".$badge['class']."\" id=\"".$badge['id']."\">".$badge['value']."</span>\n";
                        } else if ($badge['data-id'] != ''){
                            echo "<span class=\"".$badge['class']."\" data-id=\"".$badge['data-id']."\">".$badge['value']."</span>\n";
                        } else {
                            echo "<span class=\"".$badge['class']."\">".$badge['value']."</span>\n";
                        }
                    }
                }
                echo "</p>\n";
                echo "</a>\n";
                echo "<ul ".$this->is_treeview_menu_open($menu->getLinks()).">\n";
                $this->buildSubMenu($menu->subMenu());
                echo "</ul>\n";
                echo "</li>\n";
            }
        }
        //echo "</ul></nav>\n";
    }

}
