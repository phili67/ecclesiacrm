<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\KioskDeviceQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class KiosksController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function deleteKiosk(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( !( SessionUser::isAdmin() ) ) {
            return $response->withStatus(401);
        }

        $kioskId = $args['kioskId'];

        $kiosk = KioskDeviceQuery::create()
            ->findOneById($kioskId);

        if (!is_null($kiosk)) {
            foreach ($kiosk->getKioskAssignments() as $kioskAssignment) {
                $kioskAssignment->delete();
            }

            $kiosk->delete();
        }

        return $response->withJson(["status" => "success"]);
    }

    public function getKioskDevices(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( !( SessionUser::isAdmin() ) ) {
            return $response->withStatus(401);
        }

        $Kiosks = KioskDeviceQuery::create()
            ->joinWithKioskAssignment(Criteria::LEFT_JOIN)
            ->useKioskAssignmentQuery()
            ->joinWithEvent(Criteria::LEFT_JOIN)
            ->endUse()
            ->find();

        $return = [];

        foreach ($Kiosks as $kiosk) {
            $values['Id'] = $kiosk->getID();
            $values['GUIDHash'] = $kiosk->getGUIDHash();
            $values['Name'] = $kiosk->getName();
            $values['DeviceType'] = $kiosk->getDeviceType();
            $values['LastHeartbeat'] = $kiosk->getLastHeartbeat();
            $values['Accepted'] = $kiosk->getAccepted();
            $values['PendingCommands'] = $kiosk->getPendingCommands();

            $KioskAssignments = [];

            foreach ($kiosk->getKioskAssignments() as $kioskAssignment) {
                $KioskAssignments_values['Id'] = $kioskAssignment->getId();
                $KioskAssignments_values['KioskId'] = $kioskAssignment->getKioskId();
                $KioskAssignments_values['AssignmentType'] = $kioskAssignment->getAssignmentType();
                $KioskAssignments_values['EventId'] = $kioskAssignment->getEventId();
                $KioskAssignments_values['KioskDevice'] = $kioskAssignment->getKioskDevice();

                array_push($KioskAssignments, $KioskAssignments_values);
            }

            $values['KioskAssignments'] = $KioskAssignments;


            array_push($return, $values);
        }

        return $response->withJson(["KioskDevices" => $return]);
    }

    public function allowDeviceRegistration(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( !( SessionUser::isAdmin() ) ) {
            return $response->withStatus(401);
        }

        $window = new \DateTime();
        $window->add(new \DateInterval("PT05S"));
        SystemConfig::setValue("sKioskVisibilityTimestamp", $window->format('Y-m-d H:i:s'));
        return $response->write(json_encode(array("visibleUntil" => $window)));
    }

    public function reloadKiosk(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( !( SessionUser::isAdmin() ) ) {
            return $response->withStatus(401);
        }

        $kioskId = $args['kioskId'];
        $reload = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->reloadKiosk();
        return $response->write(json_encode($reload));
    }

    public function identifyKiosk(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( !( SessionUser::isAdmin() ) ) {
            return $response->withStatus(401);
        }

        $kioskId = $args['kioskId'];
        $identify = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->identifyKiosk();
        return $response->write(json_encode($identify));
    }

    public function acceptKiosk(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( !( SessionUser::isAdmin() ) ) {
            return $response->withStatus(401);
        }

        $kioskId = $args['kioskId'];
        $accept = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->setAccepted(true)
            ->save();
        return $response->write(json_encode($accept));
    }

    public function setKioskAssignment(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( !( SessionUser::isAdmin() ) ) {
            return $response->withStatus(401);
        }

        $kioskId = $args['kioskId'];
        $input = (object)$request->getParsedBody();
        $accept = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->setAssignment($input->assignmentType, $input->eventId);
        return $response->write(json_encode($accept));
    }

}
