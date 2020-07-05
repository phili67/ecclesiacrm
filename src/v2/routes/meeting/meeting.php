<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2019-03-23
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/meeting', function () {
    $this->get('/dashboard', 'renderMeetingDashboard');
});

function renderMeetingDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/meeting');

    if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    return $renderer->render($response, 'meetingdashboard.php', argumentsMeetingArray());
}


function argumentsMeetingArray ()
{
    $sPageTitle = _("Meeting Dashboard");

    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sCSPNonce       = SystemURLs::getCSPNonce();

    $roomName = '';//'Seconde4Test';//CousSNT2029

    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
        'sRootDocument'        => $sRootDocument,
        'sPageTitle'           => $sPageTitle,
        'sCSPNonce'            => $sCSPNonce,
        'roomName'             => $roomName
    ];
    return $paramsArguments;
}
