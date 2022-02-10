<?php


//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace Plugins\VIEWControllers;

use PluginStore\PluginPrefJitsiMeetingQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use PluginStore\PersonJitsiMeetingQuery;
use PluginStore\PersonLastJitsiMeetingQuery;

use PluginStore\PluginPrefJitsiMeeting;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

class VIEWMeetingController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderMeetingDashboard (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer(__DIR__.'/../../v2/templates');

        if ( !( SessionUser::getUser()->isMeetingEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'meetingdashboard.php', $this->argumentsMeetingArray());
    }


    public function argumentsMeetingArray ()
    {
        $sPageTitle = _("Meeting Dashboard");

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $personId = SessionUser::getUser()->getPersonId();

        $lpm = PersonLastJitsiMeetingQuery::create()->findOneByPersonId($personId);

        $roomName = '';

        if (!is_null($lpm)) {
            $pm = PersonJitsiMeetingQuery::create()->findOneById($lpm->getPersonMeetingId());

            $roomName = $pm->getCode();
        }

        $allRooms = PersonJitsiMeetingQuery::create()->findByPersonId($personId);

        // there's only one settings
        $setting = PluginPrefJitsiMeetingQuery::create()->findOne();

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'sCSPNonce'            => $sCSPNonce,
            'roomName'             => $roomName,
            'allRooms'             => (!is_null($allRooms))?$allRooms->toArray():null,
            'domain'               => $setting->getDomain(),
            'domainscriptpath'     => $setting->getDomainScriptPath()
        ];
        return $paramsArguments;
    }


}
