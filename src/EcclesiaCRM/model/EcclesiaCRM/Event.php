<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Event as BaseEvent;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\MyPDO\CalDavPDO;

use Propel\Runtime\Propel;


/**
 * Skeleton subclass for representing a row from the 'events_event' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Event extends BaseEvent
{

  public function checkInPerson($PersonId)
  {
    $AttendanceRecord = EventAttendQuery::create()
            ->filterByEvent($this)
            ->filterByPersonId($PersonId)
            ->findOneOrCreate();

    $AttendanceRecord->setEvent($this)
      ->setPersonId($PersonId)
      ->setCheckinDate(date('Y-m-d H:i:s'))
      ->setCheckoutDate(null)
      ->save();

    return array("status"=>"success");

  }

  public function getLatitude()
  {
     $LatLong = explode(' commaGMAP ', $this->getCoordinates());

     return $LatLong[0];
  }

  public function getLongitude()
  {
     $LatLong = explode(' commaGMAP ', $this->getCoordinates());

     return $LatLong[1];
  }

  public function getAlarm()
  {
      // we get the PDO for the Sabre connection from the Propel connection
      $pdo = Propel::getConnection();

      // We set the BackEnd for sabre Backends
      $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());

      $data = $calendarBackend->extractCalendarData($this->getCalendardata());

      return $data['alarm'];
  }

  public function checkOutPerson($PersonId)
  {
    $AttendanceRecord = EventAttendQuery::create()
            ->filterByEvent($this)
            ->filterByPersonId($PersonId)
            ->filterByCheckinDate(NULL,  Criteria::NOT_EQUAL)
            ->findOne();

    $AttendanceRecord->setEvent($this)
      ->setPersonId($PersonId)
      ->setCheckoutDate(date('Y-m-d H:i:s'))
      ->save();

    return array("status"=>"success");

  }

  public function getEventURI()
  {
    if(SessionUser::getUser()->isAdmin())
      return SystemURLs::getRootPath()."/EventEditor.php?calendarAction=".$this->getID();
    else
      return '';
  }
}
