<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Pledge as BasePledge;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Connection\ConnectionInterface;

use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'pledge_plg' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Pledge extends BasePledge
{
    public function preDelete(ConnectionInterface $con = NULL): bool
    {
      $deposit = DepositQuery::create()->findOneById($this->getDepid());
      
      if ( ($ret = parent::preDelete($con)) ) {
          if ($deposit != null && $deposit->getClosed()) {
            throw new PropelException('Cannot delete a payment from a closed deposit', 500);
          }
          
          return $ret;
      }
    }
    
    public function toArray(string $keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false): array
    {
      $array = parent::toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, $includeForeignObjects);
      $family = $this->getFamily();
      
      if($family)
      {
        $array['FamilyString']=$family->getFamilyString();
      }
      
      return $array;
    }
}
