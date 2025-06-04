<?php

namespace EcclesiaCRM\WebDav\Utils;

use EcclesiaCRM\Collections;
use EcclesiaCRM\Collectionsinstances;
use EcclesiaCRM\Base\CollectionsinstancesQuery;
use EcclesiaCRM\Base\CollectionsQuery;
use EcclesiaCRM\Base\UserQuery;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\DAV\Sharing\Plugin as SPlugin;


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
     * share a file or a folder
     * 
     * @param int $ownerPersonId
     * @param string $ownerPaths, example : private/userdir/A99CBDE9-E121-4713-B8D2-D14C50561310/admin/wsl1.png
     * @param string $ownerPrinpals example : principals/admin"
     * @param string $ownerNameCollection example : wsl1.png
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees :
     *   mailto:philippe.logel@gmail.com
     *   principal = null
     *   properties = array(0)
     *   access = 3
     *   comment = null
     *   inviteStatus = null
     */
    public static function shareFileOrDirectory($ownerPersonId, $ownerPaths, $ownerPrinpals, $ownerNameCollection, $sharees): void
    {
        // we create the root file or directory
        $collections = CollectionsQuery::create()
            ->filterByOwnerid($ownerPersonId)
            ->filterByOwnerpath($ownerPaths)
            ->findOneByPrincipaluri($ownerPrinpals);

        if (is_null($collections)) {
            $collections = new Collections();
            
            $collections->setOwnerid($ownerPersonId);
            $collections->setOwnerpath($ownerPaths);            
            $collections->setPrincipaluri($ownerPrinpals);

            $collections->save();
        }   

        foreach($sharees as $sharee) {
            $res = explode(':', $sharee->href);
            if (count($res) != 2) {
                continue;            
            }
            $email = $res[1];

            $guestUser = UserQuery::create()
                ->usePersonQuery()
                    ->filterByWorkEmail($email)
                    ->_or()
                    ->filterByEmail($email)
                ->endUse()
                ->findOne();    
                
            $guestPath = $guestUser->getUserDir() . "/". $ownerNameCollection;
            
            $guestPrincipals = "principals/".$guestUser->getUserName();
            $access = $sharee->access;

            $collectionsInstance = CollectionsinstancesQuery::create()
                ->filterByCollectionsId($collections->getId())
                ->findOneByPrincipaluri($guestPrincipals);


            if ($sharee->access === \Sabre\DAV\Sharing\Plugin::ACCESS_NOACCESS) {
                // if access was set no NOACCESS, it means access for an
                // existing sharee was removed.
                if (!is_null($collectionsInstance)){
                    // we have to purge the link
                    $guestPath = SystemURLs::getDocumentRoot()."/".$guestPath;

                    if (is_link($guestPath)) {
                        unlink($guestPath);
                    }
                    $collectionsInstance->delete();
                }                        
                continue;
            }

            if (is_null($sharee->principal)) {
                // If the server could not determine the principal automatically,
                // we will mark the invite status as invalid.
                $sharee->inviteStatus = \Sabre\DAV\Sharing\Plugin::INVITE_INVALID;
            } else {
                // Because sabre/dav does not yet have an invitation system,
                // every invite is automatically accepted for now.
                $sharee->inviteStatus = \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED;
            }

            $ownerPaths = SystemURLs::getDocumentRoot()."/".$ownerPaths;

            if (is_null($collectionsInstance)) {
                $collectionsInstance = new Collectionsinstances();
                
                $collectionsInstance->setCollectionsId($collections->getId());
                $collectionsInstance->setPrincipaluri($guestPrincipals);
                $collectionsInstance->setShareInvitestatus($sharee->inviteStatus);
                $collectionsInstance->setGuestpath($guestPath);
                $collectionsInstance->setGuestid($guestUser->getPerson()->getId());
                $collectionsInstance->setUri(MiscUtils::gen_uuid());
                $collectionsInstance->setAccess($access);            

                $guestPath = SystemURLs::getDocumentRoot()."/".$guestPath;

                if (is_link($guestPath)) {
                    unlink($guestPath);
                }
                                           
                symlink($ownerPaths , $guestPath);

                $collectionsInstance->save();
            } else {
                // we update the $collectionsInstance
                $collectionsInstance->setCollectionsId($collections->getId());
                $collectionsInstance->setPrincipaluri($guestPrincipals);
                $collectionsInstance->setShareInvitestatus($sharee->inviteStatus);
                $collectionsInstance->setGuestpath($guestPath);
                $collectionsInstance->setGuestid($guestUser->getPerson()->getId());
                //$collectionsInstance->setUri(MiscUtils::gen_uuid()); // is unusefull
                $collectionsInstance->setAccess($access);            

                $guestPath = SystemURLs::getDocumentRoot()."/".$guestPath;

                if (is_link($guestPath)) {
                    unlink($guestPath);
                }
                                           
                symlink($ownerPaths , $guestPath);

                $collectionsInstance->save();
            }
        }
    }

    /**
     * get a file or a directory infos
     * 
     * @param string $ownerPah, example : private/userdir/A99CBDE9-E121-4713-B8D2-D14C50561310/admin/wsl1.png
     * 
     * return \Sabre\DAV\Xml\Element\Sharee[]
     */
    public static function getFileOrDirectoryInfos($ownerPah): array
    {
        $collections = CollectionsQuery::create()
            ->findOneByOwnerpath($ownerPah);

        $result = [];

        if (!is_null($collections)) {
            $collectionsInstances = CollectionsinstancesQuery::create()
                ->findByCollectionsId($collections->getId());

            
            foreach ($collectionsInstances as $collectionsInstance) {
                $ret = explode("/", $collectionsInstance->getPrincipaluri());
                
                if (count($ret) < 2) continue;
                
                $username = $ret[1];
                $user = UserQuery::create()->findOneByUserName($username);

                if (is_null($user)) continue;
                
                $result[] = new Sharee([
                    'href' => "mailto:".$user->getPerson()->getEmail(),
                    'access' => $collectionsInstance->getAccess(),
                    /// Everyone is always immediately accepted, for now.
                    'inviteStatus' => (int) $collectionsInstance->getShareInvitestatus(),
                    'properties' => ['{DAV:}displayname' => $user->getPerson()->getFullName()],
                    'principal' => $collectionsInstance->getPrincipaluri(),
                ]);
            }
        }

        return $result;
    }

    /**
     * check the file permission and check in the path if the right is writeable
     * 
     * @param string $path, example : private/userdir/A99CBDE9-E121-4713-B8D2-D14C50561310/admin/wsl1.png
     * 
     * return int
     */
    public static function getShareAccess ($path): int
    {
        $collections = CollectionsQuery::create()
            ->findOneByOwnerpath($path);

        if (!is_null($collections)) {
            $collectionsInstances = CollectionsinstancesQuery::create()
                ->findOneByCollectionsId($collections->getId());
            
            
            if (!is_null($collectionsInstances))
            {
                return $collectionsInstances->getAccess();
            } else {// there is no more shared file or folder
                $collections->delete();
            }
        }
        

        return SPlugin::ACCESS_NOTSHARED;
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
     * this assume that : SabreUtils::removeSharedFileOrCollection
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

    /**
     * delete shared file or Folder from an old path to a new
     * this assume that : SabreUtils::removeSharedFileOrCollection
     * 
     * String : $ownerPrincipalURI (principals/admin)
     * String : $ownerPath (home/....)
     * String : $guestPrincipalUri (principals/plogel2)
     */
    public static function removeSharedForPersonPrincipal ($ownerPrincipalURI, $ownerPath, $guestPrincipalUri): bool
    {
        $userName = explode("/", $ownerPrincipalURI)[1];// now we get the username
        $user = UserQuery::create()->findOneByUserName($userName);

        $oldPath = $user->getUserRootDir() . "/" . str_replace("home/", "", $ownerPath);

        // first we try to move the owner collection : file or directory 
        $collection = CollectionsQuery::create()->findOneByOwnerpath($oldPath);
        if (!is_null($collection)) {            
            $collectionInstance = CollectionsinstancesQuery::create()
                ->filterByCollectionsId($collection->getId())
                ->findOneByPrincipaluri($guestPrincipalUri);

            if (!is_null($collectionInstance)) {                
                $guestPath = SystemURLs::getDocumentRoot()."/".$collectionInstance->getGuestpath();

                if (is_link($guestPath)) {
                    unlink($guestPath);
                }
                
                $collectionInstance->delete();
                
                return true;
            }

            return false;            
        }

        return true;
    }
}