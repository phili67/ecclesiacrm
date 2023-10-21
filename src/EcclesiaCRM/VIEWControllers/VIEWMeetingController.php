<?php


//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\PersonMeetingQuery;
use EcclesiaCRM\PersonLastMeetingQuery;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

class VIEWMeetingController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderMeetingDashboard (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/meeting');

        return $renderer->render($response, 'meetingdashboard.php', $this->argumentsMeetingArray());
    }


    public function argumentsMeetingArray ()
    {
        $sPageTitle = _("Meeting Dashboard");

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $personId = SessionUser::getUser()->getPersonId();

        $lpm = PersonLastMeetingQuery::create()->findOneByPersonId($personId);

        $roomName = '';

        if (!is_null($lpm)) {
            $pm = PersonMeetingQuery::create()->findOneById($lpm->getPersonMeetingId());

            $roomName = $pm->getCode();
        }

        $allRooms = PersonMeetingQuery::create()->findByPersonId($personId);

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'sCSPNonce'            => $sCSPNonce,
            'roomName'             => $roomName,
            'allRooms'             => (!is_null($allRooms))?$allRooms->toArray():null
        ];
        return $paramsArguments;
    }


}
