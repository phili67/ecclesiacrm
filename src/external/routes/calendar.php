<?php

// Routes

use Slim\Http\Response;
use Slim\Http\ServerRequest;

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\MyPDO\CalDavPDO;
use Sabre\VObject;
use EcclesiaCRM\dto\SystemConfig;


$app->group('/calendar', function (RouteCollectorProxy $group) {
    $group->get('/events/{userID}/{uri}', function (ServerRequest $request, Response $response, array $args) {
        if (!EcclesiaCRM\dto\SystemConfig::getBooleanValue("bEnableExternalCalendarAPI"))
        {
          return $response->withStatus(404);
        }

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();

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

        $filename = "Calendar-".$args['userID'].".ics";

        $output = "BEGIN:VCALENDAR\n";
        $output .= "VERSION:2.0\n";
        $output .= "PRODID:-//EcclesiaCRM.// VObject ".VObject\Version::VERSION."//EN\n";
        $output .=  "CALSCALE:GREGORIAN\n";
        $output .=  "METHOD:PUBLISH\n";
        $output .=  "X-WR-CALNAME:".$DisplayName."\n";
        $output .=  "X-APPLE-CALENDAR-COLOR:".$color."\n";
        $output .=  "X-WR-TIMEZONE:".SystemConfig::getValue('sTimeZone')."\n";
        $output .=  "CALSCALE:GREGORIAN\n";
        $output .=  "BEGIN:VTIMEZONE\n";
        $output .=  "TZID:Europe/Paris\n";

        //$output .=  "BEGIN:DAYLIGHT\n";
        //$output .=  "TZOFFSETFROM:+0100\n";
        //$output .=  "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\n";
        //$output .=  "DTSTART:19810329T020000\n";
        //$output .=  "TZNAME:UTC+2\n";
        //$output .=  "TZOFFSETTO:+0200\n";
        //$output .=  "END:DAYLIGHT\n";

        //$output .=  "BEGIN:STANDARD\n";
        //$output .=  "TZOFFSETFROM:+0200\n";
        //$output .=  "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\n";
        //$output .=  "DTSTART:19961027T030000\n";
        //$output .=  "TZNAME:UTC+1\n";
        //$output .=  "TZOFFSETTO:+0100\n";
        //$output .=  "END:STANDARD\n";

        $output .=  "END:VTIMEZONE\n";
        $output .=  $data;
        $output .=  "END:VCALENDAR";

        $size = strlen($output);

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Length',$size)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Expires', '0');


        $response->getBody()->write($output);

        return $response;
    });
});
