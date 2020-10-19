<?php

/*******************************************************************************
 *
 *  filename    : route/backup.php
 *  last change : 2019-11-21
 *  description : manage the backup
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\dto\ChurchMetaData;

use Slim\Views\PhpRenderer;

$app->group('/dashboard', function () {
    $this->get('', 'renderDashboard');
    $this->get('/', 'renderDashboard');
});

function renderDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/dashboard/');

    return $renderer->render($response, 'maindashboard.php', argumentsFashboardArray());
}

function argumentsFashboardArray ()
{
   $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
                        'sRootDocument' => SystemURLs::getDocumentRoot(),
                        'sPageTitle'  => $sPageTitle = _('Welcome to') . ' ' . ChurchMetaData::getChurchName()
                      ];

   return $paramsArguments;
}
