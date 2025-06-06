<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incorporated in another software without authorization
//
//  Updated : 2020/01/26
//

namespace EcclesiaCRM\MyPDO;

use EcclesiaCRM\Bootstrapper;

use Sabre\WebDAV\Backend as SabreWebDavBase;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\CollectionsQuery;
use EcclesiaCRM\CollectionsinstancesQuery;
use EcclesiaCRM\WebDav\Utils\SabreUtils;


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
        return SabreUtils::getShareAccess($mycol->getPath());
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
        $ownerPersonId  = $user->getPerson()->getId();
        $ownerNameCollection = basename($mycol->getPath());

        SabreUtils::shareFileOrDirectory($ownerPersonId, $ownerPaths, $ownerPrinpals, $ownerNameCollection, $sharees);
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
    public function getInvites($mycol): array
    {
        return SabreUtils::getFileOrDirectoryInfos($mycol->getPath());
    }
}
