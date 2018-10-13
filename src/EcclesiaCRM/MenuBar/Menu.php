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

class Menu {
    private $_title;                // "event"
    private $_uri     = '';         // email/Dashboard.php
    private $_icon;                 // "fa fa-ticket fa-calendar pull-right"
    private $_links   = [];         // all the links from the menu and all the menu items
    private $_sec_grp;              // bAll, bAdmin, ....
    private $_name    = '';         // the name in the link for example
    private $_parent  = null;       // the menu or MenuItem, a parent should have all the links of his child
    private $_session_var;          //  ?????
    private $_badges  = [];         // all the badges icon : [['class' => 'label bg-blue pull-right','id' => 'AnniversaryNumber', 'value' => '0'] .....]
    private $_subMenus = [];        // all the subMenus
    private $_class = null;
    
    
    // Simple : Constructor
    public function __construct($title,$icon,$uri,$sec_grp,$parent=null,$class=null)// we can set the parent and the class of the link
    {
      $this->_parent  = $parent;
      $this->_title   = $title;
      $this->_icon    = $icon;
      $this->_uri     = $uri;      
      $this->addLink($uri);
      $this->_sec_grp = $sec_grp;
      $this->_class   = $class;
      
      // We have to add the new menu to the parent menu
      if ($parent != null && $sec_grp) {// we add all only if sec_grp is set to true
        $parent->addMenu($this);
      }
    }
    
    public function addMenu($menu)
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
      return $this->_icon;
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

}