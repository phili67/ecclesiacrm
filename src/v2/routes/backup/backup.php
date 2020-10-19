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

use Slim\Views\PhpRenderer;

$app->group('/backup', function () {
    $this->get('', 'renderBackup');
    $this->get('/', 'renderBackup');
});

function renderBackup (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/backup/');

    if ( !( SessionUser::getUser()->isAdmin() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'backup.php', argumentsBackupArray());
}

function argumentsBackupArray ()
{
   $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
                        'sRootDocument' => SystemURLs::getDocumentRoot(),
                        'sPageTitle'  => _('Backup Database'),
                        'hasGZIP' => SystemConfig::getBooleanValue('bGZIP'),
                        'hasZIP' => SystemConfig::getBooleanValue('bZIP'),
                        'encryptionMethod' => SystemConfig::getValue('sPGP')
                      ];

   return $paramsArguments;
}
