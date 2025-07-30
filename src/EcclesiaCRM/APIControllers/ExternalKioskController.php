<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\Notification;

use EcclesiaCRM\dto\Photo;

use Slim\Views\PhpRenderer;

class ExternalKioskController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAll(ServerRequest $request, Response $response, array $args): Response
    {
        $renderer = new PhpRenderer("templates/kioskDevices/");
        $pageObjects = array("sRootPath" => $_SESSION['sRootPath']);
        return $renderer->render($response, "sunday-school-class-view.php", $pageObjects);
    }

    public function heartbeat(ServerRequest $request, Response $response, array $args): Response
    {
        $kiosk = $this->container->get('kiosk');

        if (is_null($kiosk)) {
            $response->withJson(array(
                "Accepted" => "no",
                "Name" => "",
                "Assignment" => "",
                "Commands" => ""
            ));
        }

        return $response->withJson($kiosk->heartbeat());
    }

    public function checkin(ServerRequest $request, Response $response, array $args): Response
    {
        $kiosk = $this->container->get('kiosk');

        $input = (object)$request->getParsedBody();
        $status = $kiosk->getActiveAssignment()->getEvent()->checkInPerson($input->PersonId);
        return $response->withJSON($status);
    }

    public function uncheckin(ServerRequest $request, Response $response, array $args): Response
    {
        $kiosk = $this->container->get('kiosk');

        $input = (object)$request->getParsedBody();
        $status = $kiosk->getActiveAssignment()->getEvent()->unCheckInPerson($input->PersonId);
        return $response->withJSON($status);
    }

    public function checkout(ServerRequest $request, Response $response, array $args): Response
    {
        $kiosk = $this->container->get('kiosk');

        $input = (object)$request->getParsedBody();
        $status = $kiosk->getActiveAssignment()->getEvent()->checkOutPerson($input->PersonId);
        return $response->withJSON($status);
    }

    public function uncheckout(ServerRequest $request, Response $response, array $args): Response
    {
        $kiosk = $this->container->get('kiosk');

        $input = (object)$request->getParsedBody();
        $status = $kiosk->getActiveAssignment()->getEvent()->unCheckOutPerson($input->PersonId);
        return $response->withJSON($status);
    }

    public function triggerNotification(ServerRequest $request, Response $response, array $args): Response
    {
        $kiosk = $this->container->get('kiosk');

        $input = (object)$request->getParsedBody();

        $Person = PersonQuery::create()
            ->findOneById($input->PersonId);

        $Notification = new Notification();
        $Notification->setPerson($Person);
        $Notification->setRecipients($Person->getFamily()->getAdults());
        $Notification->setProjectorText($kiosk->getActiveAssignment()->getEvent()->getType() . "-" . $Person->getId());
        $Status = $Notification->send();

        return $response->withJSON($Status);
    }


    public function activeClassMembers(ServerRequest $request, Response $response, array $args): Response
    {
        $kiosk = $this->container->get('kiosk');

        $res = $kiosk->getActiveAssignment()->getActiveGroupMembers();

        if (!is_null($res)) {
            return $response->write($kiosk->getActiveAssignment()->getActiveGroupMembers()->toJSON());
        }

        return $response;
    }


    public function activeClassMemberPhotos(ServerRequest $request, Response $response, array $args): Response
    {
        $photo = new Photo("Person", $args['PersonId']);
        return $response->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
    }

}
