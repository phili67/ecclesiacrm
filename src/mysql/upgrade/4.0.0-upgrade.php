<?php 

// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/4.0.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Group;
use EcclesiaCRM\CalendarinstancesQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\Event;
use EcclesiaCRM\UserProfileQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\UserConfigQuery;
use EcclesiaCRM\UserConfig;
use EcclesiaCRM\UserProfile;

use Propel\Runtime\ActiveQuery\Criteria;

use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Sharing;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\VObject;
use EcclesiaCRM\MyVCalendar;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL;

use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;

$logger = LoggerUtils::getAppLogger();

// new way to manage events
// we get the PDO for the Sabre connection from the Propel connection
$pdo = Propel::getConnection();         
        
// We set the BackEnd for sabre Backends
$calendarBackend = new CalDavPDO($pdo->getWrappedConnection());

$logger->info("Start to translate the event from the old system to the new");

$groupOne = GroupQuery::Create()->findOne();

$new_groupId = 0;

if ( empty($group) ) {// we find all the events where not belongs to a group
  $newGroup = new Group();
  
  $newGroup->setName("From EcclesiaCRM3 without Group");
  $newGroup->save();
  
  $new_groupId = $newGroup->getId();
  
  $events = EventQuery::Create()->filterByGroupId(NULL)->find();
  
  foreach ($events as $event) {
    $event->setGroupId($new_groupId);
    $event->save();
  }
}
  
// we peek all the groups
$groups = GroupQuery::Create()->find();
  
  foreach ($groups as $group) {
    if ($group->getId()) {
      if ($group->getName() != "From EcclesiaCRM3 without Group") {
        // first we create all the calendars
        $returnedId = $calendarBackend->createCalendar('principals/admin', \Sabre\DAV\UUIDUtil::getUUID() , [
                '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
                '{DAV:}displayname'                                               => $group->getName(),
                '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp'         => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),
        ]);
      } else {
        $calendarInstance = CalendarinstancesQuery::Create()->findOneByGroupId( $group->getId() );        
        $returnedId = [$calendarInstance->getCalendarid(),$calendarInstance->getId()];
      }
      
      $calendar = CalendarinstancesQuery::Create()->findOneByCalendarid($returnedId[0]);
      if ($calendar != null) {
        $calendar->setGroupId ($group->getId());
        $calendar->save();
      }
      
      $events = EventQuery::Create()->filterBySize(0)->findByGroupId($group->getId());
      
      foreach ($events as $event) {
        $uuid = \Sabre\DAV\UUIDUtil::getUUID();
        
        $vcalendar = new EcclesiaCRM\MyVCalendar\VCalendarExtension();
        $vcalendar->add(
           'VEVENT', [
            'CREATED'=> (new \DateTime('Now'))->format('Ymd\THis'),
            'DTSTAMP' => (new \DateTime('Now'))->format('Ymd\THis'),
            'DTSTART' => $event->getStart()->format('Ymd\THis'),
            'DTEND' => $event->getEnd()->format('Ymd\THis'),
            'LAST-MODIFIED' => (new \DateTime('Now'))->format('Ymd\THis'),
            'DESCRIPTION' => $event->getDesc(),              
            'SUMMARY' => $event->getTitle(),
            'UID' => $uuid,
            'SEQUENCE' => '0',
            'TRANSP' => 'OPAQUE'
          ]);
          
          $calendar = CalendarinstancesQuery::Create()->findOneByGroupId($group->getId());
          
          $calIDs = [$calendar->getCalendarid(),0];
          
          $etag = $calendarBackend->createCalendarObject($calIDs, $uuid, $vcalendar->serialize());
          
          $newEvent = EventQuery::Create()->findOneByEtag(str_replace('"','',$etag));
          
          $calData = $newEvent->getCalendardata();
          $uri = $newEvent->getUri();
          $calid = $newEvent->getEventCalendarid();
          $lastModify = $newEvent->getLastmodified();
          $etag = $newEvent->getEtag();
          $sze = $newEvent->getSize();
          $componentType = $newEvent->getComponenttype();
          $Uid = $newEvent->getUid();
          
          $newEvent->delete();
          
          $event->setCalendardata($calData);
          $event->setUri($uri);
          $event->setEventCalendarid($calid);
          $event->setLastmodified($lastModify);
          $event->setEtag($etag);
          $event->setSize($sze);
          $event->setComponenttype($componentType);
          $event->setUid($Uid);
          
          $event->save();
      }
      
    }
  }
  
  // we delete the old files
  unlink(SystemURLs::getDocumentRoot()."/skin/js/Calendar.js");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/calendar.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/events.php");
  unlink(SystemURLs::getDocumentRoot()."/calendar.php");
  unlink(SystemURLs::getDocumentRoot()."/EventEditor.php");
  unlink(SystemURLs::getDocumentRoot()."/CartToGroup.php");
  
  // we upgrade the profiles
  $userCFGs = UserProfileQuery::Create()
            ->find();
            
  foreach ($userCFGs as $userCFG) {
    $persmissions = explode(";",$userCFG->getUserProfilePermissions());
    $values       = explode(";",$userCFG->getUserProfileValue());
    
    //echo $userCFG->getUserProfilePermissions()."<br>";
    
    $new_perms = "";    
    $still_updated = false;
    
    foreach ($persmissions as $persmission) {
      $res=explode(":",$persmission);
      $new_perms .= $persmission.";";
      if ($res[0] == 'sMailtoDelimiter') {
        $new_perms .= "bExportSundaySchoolCSV:FALSE;bExportSundaySchoolPDF:FALSE;";
      }
      
      if ($res[0] == 'bShowTooltip') {
        $new_perms .= "sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;";
      }      
      
      if ($res[0] == 'bExportSundaySchoolCSV') {
        $still_updated = true;
        break;
      }
    }
    
    $new_perms .= "bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE";
    
    if ($still_updated == false) {
      echo $new_perms."<br><br>";
      $userCFG->setUserProfilePermissions($new_perms);
    }
    
    //echo $userCFG->getUserProfileValue()."<br>";
    
    $new_values = "";    
    $still_updated = false;
    
    foreach ($values as $value) {
      $res=explode(":",$value);
      $new_values .= $value.";";
      if ($res[0] == 'sMailtoDelimiter') {
        $new_values .= "bExportSundaySchoolCSV:0;bExportSundaySchoolPDF:0;";
      }
      
      if ($res[0] == 'bShowTooltip') {
        $new_values .= "sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;";
      }

      
      if ($res[0] == 'bExportSundaySchoolCSV') {
        $still_updated = true;
        //break;
      }
    }
    
    $new_values .= "bSidebarExpandOnHover:1;bSidebarCollapse:1";

    if ($still_updated == false) {
      echo $new_values."<br><br>";
      $userCFG->setUserProfileValue($new_values);
    }

    $userCFG->save();
    
  }
  
  // last we update the user settings
  $users = UserQuery::Create()->find();
  
  foreach ($users as $user) {
    echo $user->getPersonId()."\n";
    
    $userCfg = UserConfigQuery::Create()->filterByName('bExportSundaySchoolCSV')->findOneByPersonId($user->getPersonId());
    
    if ($userCfg == null) {
      $userCfg = new UserConfig();
      $userCfg->setPersonId($user->getPersonId());
      $userCfg->setName('bExportSundaySchoolCSV');
      $userCfg->setId(3);
      $userCfg->setValue('0');
      $userCfg->setType('boolean');
      $userCfg->setTooltip('User permission to export CSV files for the sunday school');
      $userCfg->setPermission('FALSE');
      $userCfg->save();
    }
    
    $userCfg = UserConfigQuery::Create()->filterByName('bExportSundaySchoolPDF')->findOneByPersonId($user->getPersonId());
    
    if ($userCfg == null) {
      $userCfg = new UserConfig();
      $userCfg->setPersonId($user->getPersonId());
      $userCfg->setId(4);
      $userCfg->setName('bExportSundaySchoolPDF');
      $userCfg->setValue('0');
      $userCfg->setType('boolean');
      $userCfg->setTooltip('User permission to export PDF files for the sunday school');
      $userCfg->setPermission('FALSE');
      $userCfg->save();
    }
    
    $userCfg = UserConfigQuery::Create()->filterByName('bSidebarExpandOnHover')->findOneByPersonId($user->getPersonId());
    
    if ($userCfg == null) {
      $userCfg = new UserConfig();
      $userCfg->setPersonId($user->getPersonId());
      $userCfg->setId(12);
      $userCfg->setName('bSidebarExpandOnHover');
      $userCfg->setValue('1');
      $userCfg->setType('boolean');
      $userCfg->setTooltip('Enable sidebar expand on hover effect for sidebar mini');
      $userCfg->setPermission('TRUE');
      $userCfg->save();
    }

    $userCfg = UserConfigQuery::Create()->filterByName('bSidebarCollapse')->findOneByPersonId($user->getPersonId());
    
    if ($userCfg == null) {
      $userCfg = new UserConfig();
      $userCfg->setPersonId($user->getPersonId());
      $userCfg->setId(13);
      $userCfg->setName('bSidebarCollapse');
      $userCfg->setValue('1');
      $userCfg->setType('boolean');
      $userCfg->setTooltip('The sidebar is collapse by default');
      $userCfg->setPermission('TRUE');
      $userCfg->save();
    }
    
    
    $userCfg = UserConfigQuery::Create()->filterByName('sCSVExportDelemiter')->findOneByPersonId($user->getPersonId());
    
    if ($userCfg == null) {
      $userCfg = new UserConfig();
      $userCfg->setPersonId($user->getPersonId());
      $userCfg->setId(9);
      $userCfg->setName('sCSVExportDelemiter');
      $userCfg->setValue(',');
      $userCfg->setType('text');
      $userCfg->setTooltip('To export to another For european CharSet use ;');
      $userCfg->setPermission('TRUE');
      $userCfg->save();
    }
    
    $userCfg = UserConfigQuery::Create()->filterByName('sCSVExportCharset')->findOneByPersonId($user->getPersonId());
    
    if ($userCfg == null) {
      $userCfg = new UserConfig();
      $userCfg->setPersonId($user->getPersonId());
      $userCfg->setId(10);
      $userCfg->setName('sCSVExportCharset');
      $userCfg->setValue('UTF-8');
      $userCfg->setType('text');
      $userCfg->setTooltip('Default is UTF-8, For european CharSet use Windows-1252 for example for French language.');
      $userCfg->setPermission('TRUE');
      $userCfg->save();
    }
    
  }
  
  $logger->info("End of translate");
?>