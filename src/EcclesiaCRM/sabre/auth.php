<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated : 2018/05/13
//

namespace EcclesiaCRM\Auth;

// Include the function library
// Very important this constant !!!!
// be carefull with the webdav constant !!!!
define("webdav", "1");
require dirname(__FILE__).'/../../Include/Config.php';

use Sabre\DAV\Auth\Backend\AbstractBasic as BaseAbstractBasic;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\dto\SystemURLs;


class BasicAuth extends BaseAbstractBasic
{
  protected $user = '';
  protected $homeDir = '';
  
  function validateUserPass($username, $password)
  {
    $currentUser = UserQuery::create()->findOneByUserName($username);
    
    if ($currentUser != null && $currentUser->isPasswordValid($password) && $currentUser->getPerson()->getDateDeactivated() == null) {
        // ici on fait un login
        $rootDir = '';
       
        if (SystemURLs::getRootPath() != '') {
           $rootDir = SystemURLs::getRootPath().'/';
        }
         
        $this->user = $username;
        $this->homeDir = $currentUser->getHomedir();
        
        return true;
    } else {
        return false;    
    }
  }
  
  public function getLoginName()
  {
    return $this->user;
  }
  
  public function getHomeFolderName()
  {
    return $this->homeDir;
  }
}

