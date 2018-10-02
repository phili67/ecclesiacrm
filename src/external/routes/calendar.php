<?php

// Routes

use EcclesiaCRM\Utils\InputUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use Propel\Runtime\Propel;
use Sabre\VObject;


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
        
        foreach ($calendars as $calendar) {
          if ($calendar['uri'] == $args['uri']) {
            $events = $calendarBackend->getCalendarObjects($calendar['id']);
            foreach ($events as $event) {
              $res = $calendarBackend->getCalendarObject($calendar['id'],$event['uri']);
      
              $returnValues = $calendarBackend->extractCalendarData($res['calendardata']);
              
              $vcalendar = VObject\Reader::read($res['calendardata']);
              
              $data .= $vcalendar->VEVENT->serialize();
            }
          }
        }
        
        echo "BEGIN:VCALENDAR\n";
        echo "VERSION:2.0\n";
        echo "PRODID:-//EcclesiaCRM.// VObject ".VObject\Version::VERSION."//EN\n";
        echo "CALSCALE:GREGORIAN\n";
        echo $data;
        echo "END:VCALENDAR";
    });
});
