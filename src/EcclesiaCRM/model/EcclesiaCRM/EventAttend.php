<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\EventAttend as BaseEventAttend;

use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Skeleton subclass for representing a row from the 'event_attend' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class EventAttend extends BaseEventAttend
{
    public function postInsert(?ConnectionInterface $con = null): void
    {
        if (!is_null($this->getPerson()->getFamily())) {
            $this->getPerson()->getFamily()->createTimeLineNote('event_attend', $this->getEvent()->getTitle(), $this->getEvent()->getId());
        } else {
            $this->getPerson()->createTimeLineNote('event_attend', $this->getEvent()->getTitle(), $this->getEvent()->getId());
        }        
    }

    public function postUpdate(?ConnectionInterface $con = null): void
    {
        if (!is_null($this->getPerson()->getFamily())) {
            $this->getPerson()->getFamily()->createTimeLineNote('event_attend', $this->getEvent()->getTitle(), $this->getEvent()->getId());
        } else {
            $this->getPerson()->createTimeLineNote('event_attend', $this->getEvent()->getTitle(), $this->getEvent()->getId());
        }        
    }

    public function preDelete(?ConnectionInterface $con = null): bool
    {
        if (!is_null($this->getPerson()->getFamily())) {
            $this->getPerson()->getFamily()->createTimeLineNote('event_attend', $this->getEvent()->getTitle(), $this->getEvent()->getId());
        } else {
            $this->getPerson()->createTimeLineNote('event_attend', $this->getEvent()->getTitle(), $this->getEvent()->getId());
        }     
        return true;
    }
}
