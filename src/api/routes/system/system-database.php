<?php

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
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Slim\Middleware\Request\Auth\AdminRoleAuthMiddleware;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;
use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Backup\RestoreBackup;
use EcclesiaCRM\Backup\CreateBackup;
use EcclesiaCRM\Backup\BackupType;

// Routes

$app->group('/database', function () {
    $this->post('/backup', function ($request, $response, $args) {
        $input = (object) $request->getParsedBody();

        $createBackup = new CreateBackup($input);
        $backup = $createBackup->run();

        echo json_encode($backup);
    });

    $this->post('/backupRemote', function() use ($app, $systemService) {
        // without parameters the backup is done on the remote server

        $createBackup = new CreateBackup();
        $backup = $createBackup->run();

        echo json_encode($backup);
    });

    $this->post('/restore', function ($request, $response, $args) {
        $fileName = $_FILES['restoreFile'];

        $restoreJob = new RestoreBackup($fileName);
        $restore = $restoreJob->run();

        echo json_encode(get_object_vars($restore));
    });

    $this->get('/download/{filename}', function ($request, $response, $args) {
        $filename = $args['filename'];
        $this->SystemService->download($filename);
        exit;// bug resolution for safari
    });

    $this->delete('/people/clear', 'clearPeopleTables');
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
