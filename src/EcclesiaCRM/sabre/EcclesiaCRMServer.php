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

   /**
     * Sets up the server.
     *
     * If a Sabre\DAV\Tree object is passed as an argument, it will
     * use it as the directory tree. If a Sabre\DAV\INode is passed, it
     * will create a Sabre\DAV\Tree and use the node as the root.
     *
     * If nothing is passed, a Sabre\DAV\SimpleCollection is created in
     * a Sabre\DAV\Tree.
     *
     * If an array is passed, we automatically create a root node, and use
     * the nodes in the array as top-level children.
     *
     * @param Tree|INode|array|null $treeOrNode The tree object
     *
     * @throws Exception
     */
   function __construct($treeOrNode = null,$authBackend = null) {
     $this->authBackend = $authBackend;

     parent::__construct($treeOrNode);

     $this->on('beforeUnbind',array($this, 'beforeUnbind'));
     $this->on('beforeBind',array($this, 'beforeBind'));
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

    function beforeBind($uri) {
        /*$currentUser = UserQuery::create()->findOneByUserName($this->authBackend->getLoginName());
        $userName = $currentUser->getUserName();
        $userPathPublic = "home/".$userName."/public/";

        $fileName = basename($uri);
        $real_extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (str_starts_with(  $uri, $userPathPublic )) {
            $extension = MiscUtils::SanitizeExtension(pathinfo($fileName, PATHINFO_EXTENSION));
        } else {
            $extension = $real_extension;
        }

        if ($extension != $real_extension) {
            return false;
        }*/

        // due to a bug with : ́e special char that aren't to the right format : é
        /*if(preg_match('/[^\x20-\x7f]/', $uri))
         return false;
        else
         return true;
        */

        return true;
    }

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

