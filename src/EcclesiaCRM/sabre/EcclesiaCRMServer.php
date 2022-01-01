<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated : 2018/05/13
//

namespace EcclesiaCRM\PersonalServer;

// Include the function library
// Very important this constant !!!!
// be carefull with the webdav constant !!!!
define("webdav", "1");
require dirname(__FILE__).'/../../Include/Config.php';

use Sabre\DAV;
use Sabre\HTTP\RequestInterface;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\MiscUtils;

class EcclesiaCRMServer extends DAV\Server
{
   protected $authBackend;

   function __construct($treeOrNode = null,$authBackend = null) {
     $this->authBackend = $authBackend;

     parent::__construct($treeOrNode);

     $this->on('beforeUnbind',array($this, 'beforeUnbind'));
     //$this->on('beforeBind',array($this, 'beforeBind'));
   }

   function createFile($uri, $data, &$etag = null) {
      if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
        $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());
        $currentUser->createTimeLineNote("dav-create-file",MiscUtils::convertUnicodeAccentuedString2UTF8($uri));
      }

      return parent::createFile($uri, $data, $etag);
   }

   function createCollection($uri, $mkCol) {
      if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
        $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());
        $currentUser->createTimeLineNote("dav-create-directory",MiscUtils::convertUnicodeAccentuedString2UTF8($uri));
      }

      parent::createCollection($uri,$mkCol);
    }

    function updateFile($uri, $data, &$etag = null) {
        if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
           $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());
           $currentUser->createTimeLineNote("dav-update-file",MiscUtils::convertUnicodeAccentuedString2UTF8($uri));
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
           $currentUser->updateFolder($oldPath,MiscUtils::convertUnicodeAccentuedString2UTF8($res['destination']));
      }

      return $res;
    }

    function updateProperties($path, array $properties) {
      $res = parent::updateProperties($path, $properties);

      return $res;
    }

    /*function beforeBind($uri) { // due to a bug with : ́e special char that aren't to the right format : é
       if(preg_match('/[^\x20-\x7f]/', $uri))
         return false;
       else
         return true;

    }*/

    function beforeUnbind($uri) {
      if ($uri == "home/".$this->authBackend->getLoginName()."/public") {
        return false;
      }

      if (strpos($uri,"._") == false && strpos($uri,".DS_Store") == false) {
           $currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());
           $currentUser->deleteTimeLineNote("dav-delete-file",MiscUtils::convertUnicodeAccentuedString2UTF8($uri));
      }

      return true;
    }
}

