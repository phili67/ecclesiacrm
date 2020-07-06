<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\KioskDeviceQuery;
use Propel\Runtime\ActiveQuery\Criteria;

$app->group('/kiosks', function () {

    $this->get('/', 'getKioskDevices' );
    $this->post('/allowRegistration', 'allowDeviceRegistration' );
    $this->post('/{kioskId:[0-9]+}/reloadKiosk', 'reloadKiosk' );
    $this->post('/{kioskId:[0-9]+}/identifyKiosk', 'identifyKiosk' );
    $this->post('/{kioskId:[0-9]+}/acceptKiosk', 'acceptKiosk' );
    $this->post('/{kioskId:[0-9]+}/setAssignment', 'setKioskAssignment' );
    $this->delete('/{kioskId:[0-9]+}', 'deleteKiosk' );

});

function deleteKiosk (Request $request, Response $response, array $args)
{
    $kioskId = $args['kioskId'];

    $kiosk = KioskDeviceQuery::create()
        ->findOneById($kioskId);

    if ( !is_null ($kiosk) ) {
        foreach ($kiosk->getKioskAssignments() as $kioskAssignment) {
            $kioskAssignment->delete();
        }

        $kiosk->delete();
    }

    return $response->withJson(["status" => "success"]);
}

function getKioskDevices (Request $request, Response $response, array $args) {
    $Kiosks = KioskDeviceQuery::create()
            ->joinWithKioskAssignment(Criteria::LEFT_JOIN)
            ->useKioskAssignmentQuery()
              ->joinWithEvent(Criteria::LEFT_JOIN)
            ->endUse()
            ->find();

    $return = [];

    foreach ($Kiosks as $kiosk){
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

function allowDeviceRegistration (Request $request, Response $response, array $args) {
    $window =new DateTime();
    $window->add(new DateInterval("PT05S"));
    SystemConfig::setValue("sKioskVisibilityTimestamp",$window->format('Y-m-d H:i:s'));
    return $response->write(json_encode(array("visibleUntil"=>$window)));
}

function reloadKiosk (Request $request, Response $response, array $args) {
    $kioskId = $args['kioskId'];
    $reload = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->reloadKiosk();
    return $response->write(json_encode($reload));
}

function identifyKiosk (Request $request, Response $response, array $args) {
    $kioskId = $args['kioskId'];
    $identify = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->identifyKiosk();
    return $response->write(json_encode($identify));
}

function acceptKiosk (Request $request, Response $response, array $args) {
    $kioskId = $args['kioskId'];
    $accept = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->setAccepted(true)
            ->save();
    return $response->write(json_encode($accept));
}

function setKioskAssignment (Request $request, Response $response, array $args) {
    $kioskId = $args['kioskId'];
    $input = (object) $request->getParsedBody();
    $accept = KioskDeviceQuery::create()
            ->findOneById($kioskId)
            ->setAssignment($input->assignmentType, $input->eventId);
    return $response->write(json_encode($accept));
}
