<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incorporated in another software without authorization
//
//  Updated : 2020/01/26
//

namespace EcclesiaCRM\MyPDO;

use Collator;
use EcclesiaCRM\Bootstrapper;

use Sabre\DAV\Xml\Element\Sharee;
use Sabre\WebDAV\Backend as SabreWebDavBase;

use EcclesiaCRM\Utils\MiscUtils;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Collections;
use EcclesiaCRM\CollectionsQuery;
use EcclesiaCRM\Collectionsinstances;
use EcclesiaCRM\CollectionsinstancesQuery;
use EcclesiaCRM\dto\SystemURLs;

use Sabre\DAV\Sharing\Plugin as SPlugin;


class WebDavPDO extends SabreWebDavBase\PDO
{
    function __construct($pdo=null)
    {
        if (is_null($pdo)) {
            $pdo = Bootstrapper::GetPDO();
        }

        parent::__construct($pdo);
    }

    /**
     * Returns the 'access level' for the instance of this shared resource.
     *
     * The value should be one of the Sabre\DAV\Sharing\Plugin::ACCESS_
     * constants.
     * const ACCESS_NOTSHARED = 0;
     * const ACCESS_SHAREDOWNER = 1;
     * const ACCESS_READ = 2;
     * const ACCESS_READWRITE = 3;
     * const ACCESS_NOACCESS = 4;
     *
     * @return int
     */
    public function getShareAccess($mycol)
    { 
        $path = $mycol->getPath();

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
     * This function must return a URI that uniquely identifies the shared
     * resource. This URI should be identical across instances, and is
     * also used in several other XML bodies to connect invites to
     * resources.
     *
     * This may simply be a relative reference to the original shared instance,
     * but it could also be a urn. As long as it's a valid URI and unique.
     *
     * @return string
     */
    public function getShareResourceUri($mycol):string
    {
        $path = $mycol->getPath();

        $collections = CollectionsQuery::create()
            ->findOneByOwnerpath($path);

        if (!is_null($collections)) {
            $collectionsInstances = CollectionsinstancesQuery::create()
                ->findOneByCollectionsId($collections->getId());
            
            
            if (!is_null($collectionsInstances))
            {
                return '/ns/share/collection/' . $collections->getId();
            } else {// there is no more shared file or folder
                $collections->delete();
            }
        }

        return "None";        
    }

    /**
     * Updates the list of sharees.
     *
     * Every item must be a Sharee object.
     *
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees
     */
    public function updateInvites($mycol, array $sharees)
    {
        $ownerPrinpals = $mycol->getOwner();
        $login = explode("/", $ownerPrinpals)[1];
        
        $user = UserQuery::create()            
                ->findOneByUserName($login);
            
        $ownerPaths = $mycol->getPath();
        $personId  = $user->getPerson()->getId();
        $ownerNameCollection = basename($mycol->getPath());

        // we create the root file or directory
        $collections = CollectionsQuery::create()
            ->filterByOwnerid($personId)
            ->filterByOwnerpath($ownerPaths)
            ->findOneByPrincipaluri($ownerPrinpals);

        if (is_null($collections)) {
            $collections = new Collections();
            
            $collections->setOwnerid($personId);
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

            }
        }
    }

    /**
     * Returns the list of people whom this resource is shared with.
     *
     * Every item in the returned array must be a Sharee object with
     * at least the following properties set:
     *
     * * $href
     * * $shareAccess
     * * $inviteStatus
     *
     * and optionally:
     *
     * * $properties
     *
     * @return \Sabre\DAV\Xml\Element\Sharee[]
     */
    public function getInvites($mycol)
    {
        $ownerPah = $mycol->getPath();

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
}
