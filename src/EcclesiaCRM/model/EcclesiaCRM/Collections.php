<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Collections as BaseCollections;

use Propel\Runtime\Connection\ConnectionInterface;

use EcclesiaCRM\dto\SystemURLs;

/**
 * Skeleton subclass for representing a row from the 'collections' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Collections extends BaseCollections
{
    /**
     * Code to be run before deleting the object in database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preDelete(?ConnectionInterface $con = null): bool
    {
        $collectionInstances = CollectionsinstancesQuery::create()
                ->findByCollectionsId($this->getId());

        foreach ($collectionInstances as $collectionInstance) {
            $guestPath = SystemURLs::getDocumentRoot()."/".$collectionInstance->getGuestpath();
            unlink($guestPath);
        }

        return parent::preDelete($con);
    }
}
