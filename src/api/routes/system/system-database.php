<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

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
use EcclesiaCRM\Slim\Middleware\Request\Auth\AdminRoleAuthMiddleware;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;


use EcclesiaCRM\Backup\RestoreBackup;
use EcclesiaCRM\Backup\CreateBackup;
use EcclesiaCRM\Backup\DownloadManager;

// Routes

$app->group('/database', function (RouteCollectorProxy $group) {
    $group->post('/backup', function (Request $request, Response $response, array $args) {
        $input = (object) $request->getParsedBody();

        $createBackup = new CreateBackup($input);
        $backup = $createBackup->run();

        return $response->write(json_encode(get_object_vars($backup)));
    });

    $group->post('/backupRemote', function(Request $request, Response $response, array $args) {
        // without parameters the backup is done on the remote server
        $input = (object) $request->getParsedBody();

        $createBackup = new CreateBackup($input);
        $backup = $createBackup->run();

        return $response->write(json_encode(get_object_vars($backup)));
    });

    $group->post('/restore', function (Request $request, Response $response, array $args) {
        $fileName = $_FILES['restoreFile'];

        $restoreJob = new RestoreBackup($fileName);
        $restore = $restoreJob->run();

        return $response->write(json_encode(get_object_vars($restore)));
    });

    $group->get('/download/{filename}', function (Request $request, Response $response, array $args) {
        $filename = $args['filename'];
        DownloadManager::run($filename);
        exit;// bug resolution for safari
    });

    $group->delete('/people/clear', 'clearPeopleTables');
});

function clearPeopleTables(Request $request, Response $response, array $p_args)
{
    $connection = Propel::getConnection();
    $logger = LoggerUtils::getAppLogger();

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
