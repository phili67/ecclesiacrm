<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
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

        $createBackup = new CreateBackup($input);
        $backup = $createBackup->run();

        return $response->write(json_encode(get_object_vars($backup)));
    }

    public function backupRemote (ServerRequest $request, Response $response, array $args): Response {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

        // without parameters the backup is done on the remote server
        $input = (object) $request->getParsedBody();

        $logger = $this->container->get('Logger');

        $logger->info("Start remote Backup");

        $createBackup = new CreateBackup($input);
        $backup = $createBackup->run();

        $logger->info("Stop remote Backup");

        return $response->write(json_encode(get_object_vars($backup)));
    }

    public function restore (ServerRequest $request, Response $response, array $args): Response {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

        $fileName = $_FILES['restoreFile'];

        $restoreJob = new RestoreBackup($fileName);
        $restore = $restoreJob->run();

        return $response->write(json_encode(get_object_vars($restore)));
    }

    public function download (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::isAdmin() ) {
            return $response->withStatus(401);
        }

        $filename = $args['filename'];
        DownloadManager::run($filename);
        exit;// bug resolution for safari
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
}
