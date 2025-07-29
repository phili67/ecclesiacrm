<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2025/07/28
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\SessionUser;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\dto\Photo;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\FamilyCustomQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\FileSystemUtils;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;

use EcclesiaCRM\UserQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;


use EcclesiaCRM\Backup\RestoreBackup;
use EcclesiaCRM\Backup\CreateBackup;
use EcclesiaCRM\Backup\DownloadManager;
use EcclesiaCRM\dto\SystemConfig;

class SystemBackupRestoreController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function backup (ServerRequest $request, Response $response, array $args): Response {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

        $input = (object) $request->getParsedBody();

        $logger = $this->container->get('Logger');

        $logger->info("Start normal Backup");

        $cmd = "php ".SystemURLs::getDocumentRoot()."/backuptools/backup.php iRemote=".$input->iRemote." iArchiveType='".$input->iArchiveType."' bEncryptBackup=".(($input->bEncryptBackup==true)?"true":"false")." password='".$input->password."'";

        shell_exec($cmd. "> /dev/null 2>/dev/null &" );// execute commande without 

        $date = file_get_contents(SystemURLs::getDocumentRoot().'/tmp_attach/backup_in_progress.txt');

        $logger->info("Stop normal Backup");

        return $response->withJson(['result' => true, 'in_progress' => 'true', 'start' => $date]);
    }

    public function backupRemote (ServerRequest $request, Response $response, array $args): Response {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

        // without parameters the backup is done on the remote server
        $input = (object) $request->getParsedBody();

        $logger = $this->container->get('Logger');

        $logger->info("Start remote Backup");

       $cmd = "php ".SystemURLs::getDocumentRoot()."/backuptools/backup.php iRemote=".$input->iRemote." iArchiveType='".$input->iArchiveType."' bEncryptBackup=".(($input->bEncryptBackup==true)?"true":"false")." password='".$input->password."'";

        shell_exec($cmd. "> /dev/null 2>/dev/null &" );// execute commande without 

        $date = file_get_contents(SystemURLs::getDocumentRoot().'/tmp_attach/backup_in_progress.txt');

        $logger->info("Stop remote Backup");

        return $response->withJson(['result' => true, 'in_progress' => 'true', 'start' => $date]);
    }

    public function restore (ServerRequest $request, Response $response, array $args): Response {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

        $fileName = $_FILES['restoreFile'];

        $restoreJob = new RestoreBackup($fileName);
        $restore = $restoreJob->run();

        return $response->withJson(get_object_vars($restore));
    }

    public function download (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }       

        $filename = $args['filename'];

        return DownloadManager::run($response, $filename);    
    }

    public function clearPeopleTables (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

        $logger = $this->container->get('Logger');

        $connection = Propel::getConnection();

        $curUserId = $_SESSION["user"]->getId();

        $logger->info("People DB Clear started ");

        FamilyCustomQuery::create()->deleteAll($connection);
        $logger->info("Family custom deleted ");

        FamilyQuery::create()->deleteAll($connection);
        $logger->info("Families deleted");

        // Delete Family Photos
        FileSystemUtils::deleteFiles(SystemURLs::getImagesRoot() . "/Family/", Photo::getValidExtensions());
        FileSystemUtils::deleteFiles(SystemURLs::getImagesRoot() . "/Family/thumbnails/", Photo::getValidExtensions());
        $logger->info("family photos deleted");

        Person2group2roleP2g2rQuery::create()->deleteAll($connection);
        $logger->info("Person Group Roles deleted");

        PersonCustomQuery::create()->deleteAll($connection);
        $logger->info("Person Custom deleted");

        PersonVolunteerOpportunityQuery::create()->deleteAll($connection);
        $logger->info("Person Volunteer deleted");

        UserQuery::create()->filterByPersonId($curUserId, Criteria::NOT_EQUAL)->delete($connection);
        $logger->info("Users aide from person logged in deleted");

        PersonQuery::create()->filterById($curUserId, Criteria::NOT_EQUAL)->delete($connection);
        $logger->info("Persons aide from person logged in deleted");

        // Delete Person Photos
        FileSystemUtils::deleteFiles(SystemURLs::getImagesRoot() . "/Person/", Photo::getValidExtensions());
        FileSystemUtils::deleteFiles(SystemURLs::getImagesRoot() . "/Person/thumbnails/", Photo::getValidExtensions());
        $logger->info("people photos deleted");

        NoteQuery::create()->filterByPerId($curUserId, Criteria::NOT_EQUAL)->delete($connection);
        $logger->info("Notes deleted");

        // we empty the cart, to reset all
        $_SESSION['aPeopleCart'] = [];

        return $response->withJson(['success' => true, 'msg' => gettext('The people and families has been cleared from the database.')]);
    }
    
    public function getBackupResult (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

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

        return $response->withJson([
            'BackupDone' => $BackupDone,
            'Backup_In_Progress' => $Backup_In_Progress,
            'RemoteBackup' => $RemoteBackup,
            'message' => $message,
            'Backup_Result_Datas' => $Backup_Result_Datas
        ]);
    }
}
