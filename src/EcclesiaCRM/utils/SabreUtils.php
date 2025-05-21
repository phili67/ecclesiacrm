<?php

namespace EcclesiaCRM\WebDav\Utils;

use EcclesiaCRM\Base\CollectionsinstancesQuery;
use EcclesiaCRM\Base\CollectionsQuery;
use EcclesiaCRM\Base\UserQuery;

use Sabre\DAV\Sharing\Plugin as SPlugin;

use EcclesiaCRM\dto\SystemURLs;

class SabreUtils {

    /**
     * the path should be like : '/.../server.php/home/admin/wsl.png'
     */
    public static function getPrincipalsFromPath ($path) {
        $res = explode("/", str_replace("/" . SystemURLs::getDocumentRoot() . "/server.php/home/", "",$path));
        return "principals/".$res[0];
    }

    /**
     * the path should be like : 'home/admin/wsl1.png'
     */
    public static function getPrincipalsFromUri ($path) {
        $res = explode("/", str_replace("home/", "", $path));
        return "principals/".$res[0];
    }

    /**
     * check the file permission and check in the path if the right is writeable
     * 
     * String : principalURI (principals/admin)
     * String : $oldPath (home/....)
     */
    public static function fileOrCollectionACL ($principalURI, $path)
    {
        $path = str_replace("//","/", $path);
        
        $userName = explode("/", $principalURI)[1];// now we get the username
        $user = UserQuery::create()->findOneByUserName($userName);

        $path = $user->getUserRootDir() . "/" . str_replace("home/", "", $path);

        $collectionInstance = CollectionsinstancesQuery::create()
            ->findOneByGuestpath($path);

        if (!is_null($collectionInstance)) {
            // we have to check, in the case of a folder that it is not a root folder for another user
            $ownerPathWorkCollection = SystemURLs::getDocumentRoot() . "/" . $collectionInstance->getCollections()->getOwnerpath();
            
            $ownerUser = UserQuery::create()->findOneByPersonId($collectionInstance->getCollections()->getPerson()->getId());

            $ownerPath =  SystemURLs::getDocumentRoot() . "/" . $ownerUser->getUserDir();

            if ($ownerPathWorkCollection == $ownerPath) {
                // if it's a home folder of another user : it's only readable
                return SPlugin::ACCESS_READ;
            }

            return $collectionInstance->getAccess();
        } else {
            // we're in a case of file folder inside a sub-directory
            do {                
                $url_to_array = parse_url($path);
                $path = dirname($url_to_array['path']);
                $collectionInstance = CollectionsinstancesQuery::create()
                    ->findOneByGuestpath($path);

                if (!is_null($collectionInstance)) {
                    // we are in a case of a folder which contains the file ... and we return the right access
                    return $collectionInstance->getAccess();
                }
                $lastTerm = basename($path);
            } while ($lastTerm != "userdir");
        }

        return SPlugin::ACCESS_READWRITE;
    }

    /**
     * move shared file or Folder from an old path to a new
     * 
     * String : principalURI (principals/admin)
     * String : $oldPath (home/....)
     * String : $newpath (home/....)
     */
    public static function moveSharedFileOrCollection ($principalURI, $oldPath, $newPath)
    {
        $userName = explode("/", $principalURI)[1];// now we get the username
        $user = UserQuery::create()->findOneByUserName($userName);

        $oldPath = $user->getUserRootDir() . "/" . str_replace("home/", "", $oldPath);
        $newPath = $user->getUserRootDir() . "/" . str_replace("home/", "", $newPath);

        // first we try to move the owner collection : file or directory 
        $collection = CollectionsQuery::create()->findOneByOwnerpath($oldPath);
        if (!is_null($collection)) {
            $collection->setOwnerpath($newPath);
            $collection->save();

            // we have to change all the lnk for all child user
            $collectionInstances = CollectionsinstancesQuery::create()
                ->findByCollectionsId($collection->getId());

            foreach ($collectionInstances as $collectionInstance) {
                $newPath = SystemURLs::getDocumentRoot() . "/" .$newPath;
                $guestPath = SystemURLs::getDocumentRoot()."/".$collectionInstance->getGuestpath();

                if (is_link($guestPath)) {
                    unlink($guestPath);
                }

                symlink($newPath , $guestPath);                                
            }

        } else {
            $collectionInstance = CollectionsinstancesQuery::create()
                ->findOneByGuestpath($oldPath);

            if (!is_null($collectionInstance)) {
                $collectionInstance->setGuestpath($newPath);
                $collectionInstance->save();

                // we create the lnk .... unusefull
                $ownerId = $collectionInstance->getCollections()->getOwnerid();
                $ownerPath = $collectionInstance->getCollections()->getOwnerpath();

                $oldPath = SystemURLs::getDocumentRoot() . "/" .$oldPath;            
                $ownerPath = SystemURLs::getDocumentRoot()."/". $ownerPath;                                
                $newPath = SystemURLs::getDocumentRoot() . "/" .$newPath;         
                
                if (is_link($oldPath)) {
                    unlink($oldPath);
                }

                symlink($ownerPath , $newPath);                
            }
        }
    }

    /**
     * delete shared file or Folder from an old path to a new
     * this assume that : SabreUtils::moveSharedFileOrCollection is already used
     * 
     * String : principalURI (principals/admin)
     * String : $oldPath (home/....)
     */
    public static function removeSharedFileOrCollection ($principalURI, $oldPath)
    {
        $userName = explode("/", $principalURI)[1];// now we get the username
        $user = UserQuery::create()->findOneByUserName($userName);

        $oldPath = $user->getUserRootDir() . "/" . str_replace("home/", "", $oldPath);

        // first we try to move the owner collection : file or directory 
        $collection = CollectionsQuery::create()->findOneByOwnerpath($oldPath);
        if (!is_null($collection)) {            
            $collection->delete();
        } else {
            $collectionInstance = CollectionsinstancesQuery::create()
                ->findOneByGuestpath($oldPath);

            if (!is_null($collectionInstance)) {
                $collectionId = $collectionInstance->getCollectionsId();                
                $collectionInstance->delete();
                
                 $collectionInstances = CollectionsinstancesQuery::create()
                    ->findByCollectionsId($collectionId);
                
                if ($collectionInstances->count() == 0) {
                    // the is no more shared file or folder on this shared collection
                    // it's times to delete
                    $collection = CollectionsQuery::create()->findOneById($collectionId);
                    if (!is_null($collection)){
                        $collection->delete();
                    }
                }
            }
        }
    }
}