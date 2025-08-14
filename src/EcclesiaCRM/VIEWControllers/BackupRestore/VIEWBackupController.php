<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2025/07/28
//


namespace EcclesiaCRM\VIEWControllers\BackupRestore;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

class VIEWBackupController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderBackup (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/BackupRestore/');

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'backup.php', $this->argumentsBackupArray());
    }

    public function argumentsBackupArray ()
    {        
        $BackupDone = $RemoteBackup = $Backup_In_Progress = false;
        $message = "";
        $Backup_Result_Datas = [];

        $backup_result_url = SystemURLs::getDocumentRoot().'/tmp_attach/backup_result.json';
        
        if (file_exists(SystemURLs::getDocumentRoot().'/tmp_attach/backup_result.json')) {
            $BackupDone = true;
            $message = _("Backup Complete, Ready for Download.");
            $content = file_get_contents($backup_result_url);
            $Backup_Result_Datas =  json_decode($content,true); 
            
        }        

        if (file_exists(SystemURLs::getDocumentRoot().'/tmp_attach/backup_in_progress.txt')) {
            $Backup_In_Progress = true;
            $message = _("Background backup In progress");
        }

        if (SystemConfig::getBooleanValue('bEnableExternalBackupTarget') && SystemConfig::getValue('sExternalBackupAutoInterval') > 0) {
            $RemoteBackup = true;
            
            $message = _("Backup Generated and copied to remote server");
        }

        $paramsArguments = [ 
            'sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => _('Backup Database')."/CRM",
            'hasGZIP' => SystemConfig::getBooleanValue('bGZIP'),
            'hasZIP' => SystemConfig::getBooleanValue('bZIP'),
            'encryptionMethod' => SystemConfig::getValue('sPGP'),
            'BackupDone' => $BackupDone,
            'Backup_In_Progress' => $Backup_In_Progress,
            'RemoteBackup' => $RemoteBackup,
            'message' => $message,
            'Backup_Result_Datas' => $Backup_Result_Datas
        ];

        return $paramsArguments;
    }


}
