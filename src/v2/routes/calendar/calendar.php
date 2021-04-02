<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/calendar', function (RouteCollectorProxy $group) {
    $group->get('', 'renderCalendar');
    $group->get('/', 'renderCalendar');
});

function renderCalendar (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/calendar/');

    return $renderer->render($response, 'calendar.php', argumentsCalendarArray());
}

function argumentsCalendarArray ()
{
   $eventTypes = EventTypesQuery::Create()
      ->orderByName()
      ->find();

   $lat = OutputUtils::number_dot(ChurchMetaData::getChurchLatitude());
   $lng = OutputUtils::number_dot(ChurchMetaData::getChurchLongitude());

   $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
   $sMapProvider   = SystemConfig::getValue('sMapProvider');
   $sGoogleMapKey  = SystemConfig::getValue('sGoogleMapKey');

   $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
                       'sRootDocument' => SystemURLs::getDocumentRoot(),
                       'sPageTitle'  => _('Church Calendar'),
                       'eventTypes'  => $eventTypes,
                       'coordinates' => [
                           'lat' => $lat,
                           'lng' => $lng
                        ],
                       'iLittleMapZoom' => $iLittleMapZoom,
                       'sGoogleMapKey'  => $sGoogleMapKey,
                       'sMapProvider'   => $sMapProvider,
                       'sessionUsr'     => SessionUser::getUser()
                      ];

   return $paramsArguments;
}
