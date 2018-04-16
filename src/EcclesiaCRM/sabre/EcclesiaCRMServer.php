<?php

/* copyright 2018 Philippe Logel all rights reserved */

namespace EcclesiaCRM\PersonalServer;

use Sabre\DAV;
use Sabre\HTTP\RequestInterface;

// Include the function library
// Very important this constant !!!!
// be carefull with the webdav constant !!!!
define("webdav", "1");
require dirname(__FILE__).'/../../Include/Config.php';

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\User;
use EcclesiaCRM\Note;
use EcclesiaCRM\dto\SystemURLs;


class EcclesiaCRMServer extends DAV\Server
{
   protected $authBackend;
   
   function __construct($treeOrNode = null,$authBackend = null) {
     $this->authBackend = $authBackend;
     
     parent::__construct($treeOrNode);

     /*$this->on('beforeUnbind',function($path) {
         error_log("beforeUnbind = ".$path." " .$this->authBackend->getHomeFolderName()."\n\n", 3, "/var/log/mes-erreurs.log");
     
         return true;
     });*/
     $this->on('beforeUnbind',array($this, 'beforeUnbind'));     
   }
   
   function createFile($uri, $data, &$etag = null) {
      if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
        $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());    
        $currentUser->createTimeLineNote("dav-create-file",$uri);
      }
      
      return parent::createFile($uri, $data, $etag);
   }
   
   function createCollection($uri, $mkCol) {
      if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
        $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());    
        $currentUser->createTimeLineNote("dav-create-directory",$uri);
      }
        
      parent::createCollection($uri,$mkCol);
    }
    
    function updateFile($uri, $data, &$etag = null) {
        if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
           $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());    
           $currentUser->createTimeLineNote("dav-update-file",$uri);
        }
      
        return parent::updateFile($uri, $data, $etag);
    }
    
    function getCopyAndMoveInfo(RequestInterface $request) {
      // this is the old path
      $oldPath = $request->getPath();
     
      // we search the new path, it will in the destination part
      $res = parent::getCopyAndMoveInfo($request);
      
      /*return [
            'destination'       => $destination,
            'destinationExists' => !!$destinationNode,
            'destinationNode'   => $destinationNode,
      ];*/
      
      if (strpos($res['destination'],"._") == false && strpos($res['destination'],".DS_Store") == false) {
           $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());    
           $currentUser->createTimeLineNote("dav-move-copy-file",$res['destination']);
           $currentUser->updateFolder($oldPath,$res['destination']);
      }
      
      return $res;
    }
    
    function updateProperties($path, array $properties) {
      $res = parent::updateProperties($path, $properties);
      
      return $res;
    }
    
    function beforeUnbind($uri) {       
      if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
           $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());    
           $currentUser->deleteTimeLineNote("dav-delete-file",$uri);
      }
     
      return true;
     }

    
}

