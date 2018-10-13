<?php

// Routes

use EcclesiaCRM\Utils\InputUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use Propel\Runtime\Propel;
use Sabre\VObject;
use EcclesiaCRM\dto\SystemConfig;


$app->group('/calendar', function () {
     $this->get('/events/{userID}/{uri}', function ($request, $response, $args) {
        if (!EcclesiaCRM\dto\SystemConfig::getBooleanValue("bEnableExternalCalendarAPI"))
        {
          return $response->withStatus(404);
        }

        $params = $request->getQueryParams();
        
        // we get the PDO for the Sabre connection from the Propel connection
        $pdo = Propel::getConnection();
      
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
        $principalBackend = new PrincipalPDO($pdo->getWrappedConnection());

        $calendars = $calendarBackend->getCalendarsForUser('principals/'.$args['userID']);
        
        $data = "";
        $DisplayName = "";
        $color       = "";
        
        foreach ($calendars as $calendar) {
          if ($calendar['uri'] == $args['uri']) {// the calendar is found
            $DisplayName = $calendar['{DAV:}displayname'];
            $color = $calendar['{http://apple.com/ns/ical/}calendar-color'];

            $events = $calendarBackend->getCalendarObjects($calendar['id']);
            foreach ($events as $event) {
              $res = $calendarBackend->getCalendarObject($calendar['id'],$event['uri']);
      
              $vcalendar = VObject\Reader::read($res['calendardata']);
              
              // we expand the recurence events
              $newVCalendar = $vcalendar->expand(new DateTime('2000-01-01'), new DateTime('2032-12-31'));
              
              foreach ($vcalendar->VEVENT as $sevent) {
                $data .= $sevent->serialize();
              }
            }
            
            break;
          }
        }
        
        echo "BEGIN:VCALENDAR\n";
        echo "VERSION:2.0\n";
        echo "PRODID:-//EcclesiaCRM.// VObject ".VObject\Version::VERSION."//EN\n";
        echo "CALSCALE:GREGORIAN\n";
        echo "METHOD:PUBLISH\n";
        echo "X-WR-CALNAME:".$DisplayName."\n";
        echo "X-APPLE-CALENDAR-COLOR:".$color."\n";
        echo "X-WR-TIMEZONE:".SystemConfig::getValue('sTimeZone')."\n";
        echo "CALSCALE:GREGORIAN\n";
        echo "BEGIN:VTIMEZONE\n";
        echo "TZID:Europe/Paris\n";
        
        //echo "BEGIN:DAYLIGHT\n";
        //echo "TZOFFSETFROM:+0100\n";
        //echo "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\n";
        //echo "DTSTART:19810329T020000\n";
        //echo "TZNAME:UTC+2\n";
        //echo "TZOFFSETTO:+0200\n";
        //echo "END:DAYLIGHT\n";
        
        //echo "BEGIN:STANDARD\n";
        //echo "TZOFFSETFROM:+0200\n";
        //echo "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\n";
        //echo "DTSTART:19961027T030000\n";
        //echo "TZNAME:UTC+1\n";
        //echo "TZOFFSETTO:+0100\n";
        //echo "END:STANDARD\n";
        
        echo "END:VTIMEZONE\n";
        echo $data;
        echo "END:VCALENDAR";
    });
});
