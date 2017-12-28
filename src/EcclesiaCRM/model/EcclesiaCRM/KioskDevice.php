<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\KioskDevice as BaseKioskDevice;

use EcclesiaCRM\dto\KioskAssignmentTypes;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\Event;

use EcclesiaCRM\EventAttendQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\ConfigQuery;
use EcclesiaCRM\Family;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Utils\MiscUtils;


class KioskDevice extends BaseKioskDevice
{
  
  public function getActiveAssignment()
  {
    return $this->getKioskAssignments()[0];
  }
  
  public function setAssignment($assignmentType,$eventId)
  {
    $assignment = $this->getActiveAssignment();
    if (is_null($assignment))
    {
      $assignment = new KioskAssignment();
      $assignment->setKioskDevice($this);
    }
    $assignment->setAssignmentType($assignmentType);
    $assignment->setEventId($eventId);
    $assignment->save();
  }
  
  public function heartbeat()
  {
    $this->setLastHeartbeat(date('Y-m-d H:i:s'))
      ->save();
    
    $assignmentJSON = null;
    $assignment = $this->getActiveAssignment();
    
    if (isset($assignment) && $assignment->getAssignmentType() == dto\KioskAssignmentTypes::EVENTATTENDANCEKIOSK )
    {
      $assignment->getEvent();
      $assignmentJSON = $assignment->toJSON();
    }
    
    
    return array(
        "Accepted"=>$this->getAccepted(),
        "Name"=>$this->getName(),
        "Assignment"=>$assignmentJSON,
        "Commands"=>$this->getPendingCommands()
      );
  }
  
  public function getPendingCommands()
  {
    $commands = parent::getPendingCommands();
    $this->setPendingCommands(null);
    $this->save();
    return $commands;
  }

  public function reloadKiosk()
  {
    $this->setPendingCommands("Reload");
    $this->save();
    return true;
  }
  
  public function identifyKiosk()
  {
    $this->setPendingCommands("Identify");
    $this->save();
    return true;
  }
  
  public function preInsert(\Propel\Runtime\Connection\ConnectionInterface $con = null) {
    if (!isset($this->Name))
    {
      $this->setName(Utils\MiscUtils::random_word());
    }
    return true;
  }

}
