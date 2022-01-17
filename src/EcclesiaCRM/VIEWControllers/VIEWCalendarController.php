<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

class VIEWCalendarController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderCalendar (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/calendar/');

        return $renderer->render($response, 'calendar.php', $this->argumentsCalendarArray());
    }

    public function argumentsCalendarArray ()
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

    public function renderCalendarEventsList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/calendar/');

        return $renderer->render($response, 'eventslist.php', $this->argumentsCalendarEventsListArray());
    }

    public function argumentsCalendarEventsListArray ()
    {
        $eventTypes = EventTypesQuery::Create()
            ->orderByName()
            ->find();

        $lat = OutputUtils::number_dot(ChurchMetaData::getChurchLatitude());
        $lng = OutputUtils::number_dot(ChurchMetaData::getChurchLongitude());

        $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
        $sMapProvider   = SystemConfig::getValue('sMapProvider');
        $sGoogleMapKey  = SystemConfig::getValue('sGoogleMapKey');

        $eType = 'All';

        if ($eType == '0') {
            $sPageTitle = _('Listing Events of Type = ')._("Personal Calendar");
        } elseif ($eType != 'All') {
            $eventType = EventTypesQuery::Create()->findOneById($eType);

            $sPageTitle = _('Listing Events of Type = ').$eventType->GetName();
        } else {
            $sPageTitle = _('Listing All Church Events');
        }

        $paramsArguments = ['sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => $sPageTitle,
            'eventTypes'  => $eventTypes,
            'eType'       => $eType,
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
}
