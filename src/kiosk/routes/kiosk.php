<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

use Slim\Views\PhpRenderer;
use EcclesiaCRM\PersonQuery;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use EcclesiaCRM\dto\Notification;
use EcclesiaCRM\dto\Photo;



$app->get('/', function (Request $request, Response $response, array $args) use ($app) {
    $renderer = new PhpRenderer("templates/kioskDevices/");
    $pageObjects = array("sRootPath" => $_SESSION['sRootPath']);
    return $renderer->render($response, "sunday-school-class-view.php", $pageObjects);
});

$app->get('/heartbeat', function (Request $request, Response $response, array $args) use ($app) {
    if (is_null($app->kiosk)) {
        return array(
            "Accepted" => "no",
            "Name" => "",
            "Assignment" => "",
            "Commands" => ""
        );
    }

    return $response->write(json_encode($app->kiosk->heartbeat()));
});

$app->post('/checkin', function (Request $request, Response $response, array $args) use ($app) {

    $input = (object)$request->getParsedBody();
    $status = $app->kiosk->getActiveAssignment()->getEvent()->checkInPerson($input->PersonId);
    return $response->withJSON($status);
});

$app->post('/uncheckin', function (Request $request, Response $response, array $args) use ($app) {

    $input = (object)$request->getParsedBody();
    $status = $app->kiosk->getActiveAssignment()->getEvent()->unCheckInPerson($input->PersonId);
    return $response->withJSON($status);
});

$app->post('/checkout', function (Request $request, Response $response, array $args) use ($app) {
    $input = (object)$request->getParsedBody();
    $status = $app->kiosk->getActiveAssignment()->getEvent()->checkOutPerson($input->PersonId);
    return $response->withJSON($status);
});

$app->post('/uncheckout', function (Request $request, Response $response, array $args) use ($app) {
    $input = (object)$request->getParsedBody();
    $status = $app->kiosk->getActiveAssignment()->getEvent()->unCheckOutPerson($input->PersonId);
    return $response->withJSON($status);
});

$app->post('/triggerNotification', function (Request $request, Response $response, array $args) use ($app) {
    $input = (object)$request->getParsedBody();

    $Person = PersonQuery::create()
        ->findOneById($input->PersonId);

    $Notification = new Notification();
    $Notification->setPerson($Person);
    $Notification->setRecipients($Person->getFamily()->getAdults());
    $Notification->setProjectorText($app->kiosk->getActiveAssignment()->getEvent()->getType() . "-" . $Person->getId());
    $Status = $Notification->send();

    return $response->withJSON($Status);
});


$app->get('/activeClassMembers', function (Request $request, Response $response, array $args) use ($app) {
    $res = $app->kiosk->getActiveAssignment()->getActiveGroupMembers();

    if (!is_null($res)) {
        return $response->write($app->kiosk->getActiveAssignment()->getActiveGroupMembers()->toJSON());
    }

    return $response;
});


$app->get('/activeClassMember/{PersonId}/photo', function (ServerRequestInterface $request, ResponseInterface $response, $args) use ($app) {
    $photo = new Photo("Person", $args['PersonId']);
    return $response->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
});


