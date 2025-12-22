<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\PersonVolunteerOpportunity as BasePersonVolunteerOpportunity;
use EcclesiaCRM\MyPDO\CardDavPDO;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Skeleton subclass for representing a row from the 'person2volunteeropp_p2vo' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class PersonVolunteerOpportunity extends BasePersonVolunteerOpportunity
{
    public function preDelete(?ConnectionInterface $con = null): bool
    {
        // we'll connect to sabre to create the group
        // We set the BackEnd for sabre Backends
        $carddavBackend = new CardDavPDO();

        $addressbookId = $carddavBackend->getAddressBookForGroup ($this->getVolunteerOpportunityId())['id'];

        $carddavBackend->deleteCardForPerson($addressbookId,$this->getPersonId());
        
        return true;
    }
}
