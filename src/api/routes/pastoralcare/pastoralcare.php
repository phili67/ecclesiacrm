<?php

/*******************************************************************************
 *
 *  filename    : pastoralecare.php api
 *  last change : 2020-06-24
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *  Updated : 2020-07-07
 *
 ******************************************************************************/

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemConfig;


use EcclesiaCRM\PastoralCare;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareType;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Service\PastoralCareService;


$app->group('/pastoralcare', function (RouteCollectorProxy $group) {

    $group->post('/', 'getAllPastoralCare' );
    $group->post('/deletetype', 'deletePastoralCareType' );
    $group->post('/createtype', 'createPastoralCareType' );
    $group->post('/settype', 'setPastoralCareType' );
    $group->post('/edittype', 'editPastoralCareType' );

    $group->post('/person/add', 'addPastoralCarePerson' );
    $group->post('/person/delete', 'deletePastoralCarePerson' );
    $group->post('/person/getinfo', 'getPastoralCareInfoPerson' );
    $group->post('/person/modify', 'modifyPastoralCarePerson' );

    $group->post('/family/add', 'addPastoralCareFamily' );
    $group->post('/family/delete', 'deletePastoralCareFamily' );
    $group->post('/family/getinfo', 'getPastoralCareInfoFamily' );
    $group->post('/family/modify', 'modifyPastoralCareFamily' );

    $group->post('/members', 'pastoralcareMembersDashboard' );
    $group->post('/personNeverBeenContacted', 'personNeverBeenContacted' );
    $group->post('/familyNeverBeenContacted', 'familyNeverBeenContacted' );
    $group->post('/singleNeverBeenContacted', 'singleNeverBeenContacted' );
    $group->post('/retiredNeverBeenContacted', 'retiredNeverBeenContacted' );
    $group->post('/youngNeverBeenContacted', 'youngNeverBeenContacted' );

    $group->post('/createRandomly', 'createRandomlyPastoralCare');

    $group->post('/getPersonByClassification', 'getPersonByClassificationPastoralCare' );

    $group->post('/getPersonByClassification/{type:[0-9]+}', 'getPersonByClassificationPastoralCare' );

    $group->get('/getlistforuser/{UserID:[0-9]+}', 'getPastoralCareListForUser' );

});

function getPastoralCareListForUser(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }


    if ( isset ($args['UserID']) ) {
        $choice = SystemConfig::getValue('sPastoralcarePeriod');

        $date = new \DateTime('now');

        switch ($choice) {
            case 'Yearly 1':// choice 1 : Year-01-01 to Year-12-31
                $realDate = $date->format('Y') . "-01-01";

                $start = new \DateTime($realDate);

                $startPeriod = $start->format('Y-m-d');

                $start->add(new \DateInterval('P1Y'));
                $start->sub(new \DateInterval('P1D'));

                $endPeriod = $start->format('Y-m-d');
                break;
            case '365': // choice 2 : one year before now
                $date->add(new \DateInterval('P1D'));
                $endPeriod = $date->format('Y-m-d');
                $date->sub(new \DateInterval('P366D'));
                $startPeriod = $date->format('Y-m-d');
                break;
            case 'Yearly 2':// choice 3 : from september to september
                if ((int)$date->format('m') < 9) {
                    $realDate = ($date->format('Y') - 1) . "-09-01";
                } else {
                    $realDate = $date->format('Y') . "-09-01";
                }

                $start = new \DateTime($realDate);

                $startPeriod = $start->format('Y-m-d');

                $start->add(new \DateInterval('P1Y'));
                $start->sub(new \DateInterval('P1D'));

                $endPeriod = $start->format('Y-m-d');
                break;
        }

        $ormPastors = PastoralCareQuery::Create()
            ->filterByDate(array("min" => $startPeriod, "max" => $endPeriod))
            ->findByPastorId($args['UserID']);

        $response->withJson(['ListOfMembers' => $ormPastors->toArray()]);
    }


}


function getAllPastoralCare(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    return PastoralCareTypeQuery::Create()->find()->toJSON();
}

function deletePastoralCareType (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->pastoralCareTypeId) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
        $pstCareType = PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId);

        if ($pstCareType != null) {
            $pstCareType->delete();
        }

        return $response->withJson(['status' => "success"]);

    }

    return $response->withJson(['status' => "failed"]);
}

function createPastoralCareType(Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->Visible) && isset ($input->Title) && isset ($input->Description) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
        $pstCareType = new PastoralCareType();

        $pstCareType->setVisible($input->Visible);
        $pstCareType->setTitle($input->Title);
        $pstCareType->setDesc($input->Description);

        $pstCareType->save();

        return $response->withJson(['status' => "success"]);
    }

    return $response->withJson(['status' => "failed"]);
}

function setPastoralCareType (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->pastoralCareTypeId) && isset ($input->Visible)
        && isset ($input->Title) && isset ($input->Description) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
        $pstCareType = PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId);

        $pstCareType->setVisible($input->Visible);
        $pstCareType->setTitle($input->Title);
        $pstCareType->setDesc($input->Description);

        $pstCareType->save();

        return $response->withJson(['status' => "success"]);
    }

    return $response->withJson(['status' => "failed"]);
}

function editPastoralCareType (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->pastoralCareTypeId)  && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ){
        return PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId)->toJSON();
    }

    return $response->withJson(['status' => "failed"]);
}

function addPastoralCarePerson (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->typeID)  && isset ($input->personID) && isset ($input->currentPastorId)
        && isset ($input->visibilityStatus) && isset ($input->noteText)
        && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = new PastoralCare();

        $pstCare->setTypeId($input->typeID);

        $pstCare->setPersonId($input->personID);
        $pstCare->setPastorId($input->currentPastorId);

        $pastor = PersonQuery::Create()->findOneById ($input->currentPastorId);

        if ($pastor != null) {
            $pstCare->setPastorName($pastor->getFullName());
        }

        $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
        $pstCare->setDate($date->format('Y-m-d H:i:s'));

        $pstCare->setVisible($input->visibilityStatus);
        $pstCare->setText($input->noteText);

        $pstCare->save();

        return $response->withJson(['status' => "success"]);

    }

    return $response->withJson(['status' => "failed"]);
}

function deletePastoralCarePerson (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->ID)  && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = PastoralCareQuery::create()->findOneByID ($input->ID);

        if ($pstCare != null) {
            $pstCare->delete();
        }

        return $response->withJson(['status' => "success"]);

    }

    return $response->withJson(['status' => "failed"]);
}

function getPastoralCareInfoPerson (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->ID) && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = PastoralCareQuery::create()->leftJoinWithPastoralCareType()->findOneByID ($input->ID);

        $typeDesc = $pstCare->getPastoralCareType()->getTitle().((!empty($pstCare->getPastoralCareType()->getDesc()))?" (".$pstCare->getPastoralCareType()->getDesc().")":"");

        return $response->withJson(["id"=> $pstCare->getId(),"typeid" => $pstCare->getTypeId(),"typedesc" => $typeDesc,"visible" => $pstCare->getVisible(),"text" => $pstCare->getText()]);

    }

    return $response->withJson(['status' => "failed"]);
}

function modifyPastoralCarePerson (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->ID) && isset ($input->typeID)  && isset ($input->personID)
        && isset ($input->currentPastorId)
        && isset ($input->visibilityStatus) && isset ($input->noteText)
        && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = PastoralCareQuery::create()->findOneByID($input->ID);

        $pstCare->setTypeId($input->typeID);

        $pstCare->setPersonId($input->personID);
        $pstCare->setPastorId($input->currentPastorId);

        $pastor = PersonQuery::Create()->findOneById ($input->currentPastorId);

        if ($pastor != null) {
            $pstCare->setPastorName($pastor->getFullName());
        }

        $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
        $pstCare->setDate($date->format('Y-m-d H:i:s'));

        $pstCare->setVisible($input->visibilityStatus);
        $pstCare->setText($input->noteText);

        $pstCare->save();

        return $response->withJson(['status' => "success"]);

    }

    return $response->withJson(['status' => "failed"]);
}


function addPastoralCareFamily (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->typeID)  && isset ($input->familyID) && isset ($input->currentPastorId)
        && isset ($input->visibilityStatus) && isset ($input->noteText) && isset ($input->includeFamMembers)
        && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = new PastoralCare();

        $pstCare->setTypeId($input->typeID);

        $pstCare->setFamilyId($input->familyID);
        $pstCare->setPastorId($input->currentPastorId);

        $pastor = PersonQuery::Create()->findOneById ($input->currentPastorId);

        if ($pastor != null) {
            $pstCare->setPastorName($pastor->getFullName());
        }

        $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
        $pstCare->setDate($date->format('Y-m-d H:i:s'));

        $pstCare->setVisible($input->visibilityStatus);
        $pstCare->setText($input->noteText);

        $pstCare->save();

        // add the members too
        if ($input->includeFamMembers) {
            $persons = PersonQuery::Create()->findByFamId($input->familyID);
            foreach ($persons as $person) {
                $pstCare = new PastoralCare();

                $pstCare->setTypeId($input->typeID);

                $pstCare->setPersonId($person->getId());
                $pstCare->setPastorId($input->currentPastorId);

                if ($pastor != null) {
                    $pstCare->setPastorName($pastor->getFullName());
                }

                $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
                $pstCare->setDate($date->format('Y-m-d H:i:s'));

                $pstCare->setVisible($input->visibilityStatus);
                $pstCare->setText($input->noteText);

                $pstCare->save();
            }
        }

        return $response->withJson(['status' => "success"]);

    }

    return $response->withJson(['status' => "failed"]);
}

function deletePastoralCareFamily (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->ID)  && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = PastoralCareQuery::create()->findOneByID ($input->ID);

        if ($pstCare != null) {
            $pstCare->delete();
        }

        return $response->withJson(['status' => "success"]);

    }

    return $response->withJson(['status' => "failed"]);
}

function getPastoralCareInfoFamily (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->ID) && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = PastoralCareQuery::create()->leftJoinWithPastoralCareType()->findOneByID ($input->ID);

        $typeDesc = $pstCare->getPastoralCareType()->getTitle().((!empty($pstCare->getPastoralCareType()->getDesc()))?" (".$pstCare->getPastoralCareType()->getDesc().")":"");

        return $response->withJson(["id"=> $pstCare->getId(),"typeid" => $pstCare->getTypeId(),"typedesc" => $typeDesc,"visible" => $pstCare->getVisible(),"text" => $pstCare->getText()]);

    }

    return $response->withJson(['status' => "failed"]);
}

function modifyPastoralCareFamily (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if (isset ($input->ID) && isset ($input->typeID)  && isset ($input->familyID)
        && isset ($input->currentPastorId)
        && isset ($input->visibilityStatus) && isset ($input->noteText)
        && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pstCare = PastoralCareQuery::create()->findOneByID($input->ID);

        $pstCare->setTypeId($input->typeID);

        $pstCare->setFamilyId($input->familyID);
        $pstCare->setPastorId($input->currentPastorId);

        $pastor = PersonQuery::Create()->findOneById ($input->currentPastorId);

        if ($pastor != null) {
            $pstCare->setPastorName($pastor->getFullName());
        }

        $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
        $pstCare->setDate($date->format('Y-m-d H:i:s'));

        $pstCare->setVisible($input->visibilityStatus);
        $pstCare->setText($input->noteText);

        $pstCare->save();

        return $response->withJson(['status' => "success"]);

    }

    return $response->withJson(['status' => "failed"]);
}

function pastoralcareMembersDashboard(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    $users = UserQuery::create()
        ->filterByPastoralCare(true)
        ->filterByPersonId(1, \Propel\Runtime\ActiveQuery\Criteria::NOT_EQUAL)
        ->usePersonQuery()
        ->addAsColumn('PersonID', \EcclesiaCRM\Map\PersonTableMap::COL_PER_ID)
        ->addAsColumn('LastName', \EcclesiaCRM\Map\PersonTableMap::COL_PER_LASTNAME)
        ->addAsColumn('FirstName', \EcclesiaCRM\Map\PersonTableMap::COL_PER_FIRSTNAME)
        ->endUse()
        ->find();

    if ( !is_null($users) ) {
        $res = [];
        foreach ($users as $user) {

            $choice = SystemConfig::getValue('sPastoralcarePeriod');

            $date = new \DateTime('now');

            switch ($choice) {
                case 'Yearly 1':// choice 1 : Year-01-01 to Year-12-31
                    $realDate = $date->format('Y') . "-01-01";

                    $start = new \DateTime($realDate);

                    $startPeriod = $start->format('Y-m-d');

                    $start->add(new \DateInterval('P1Y'));
                    $start->sub(new \DateInterval('P1D'));

                    $endPeriod = $start->format('Y-m-d');
                    break;
                case '365': // choice 2 : one year before now
                    $date->add(new \DateInterval('P1D'));
                    $endPeriod = $date->format('Y-m-d');
                    $date->sub(new \DateInterval('P366D'));
                    $startPeriod = $date->format('Y-m-d');
                    break;                    break;
                case 'Yearly 2':// choice 3 : from september to september
                    if ((int)$date->format('m') < 9) {
                        $realDate = ($date->format('Y') - 1) . "-09-01";
                    } else {
                        $realDate = $date->format('Y') . "-09-01";
                    }

                    $start = new \DateTime($realDate);

                    $startPeriod = $start->format('Y-m-d');

                    $start->add(new \DateInterval('P1Y'));
                    $start->sub(new \DateInterval('P1D'));

                    $endPeriod = $start->format('Y-m-d');
                    break;
            }

            $ormPastors = PastoralCareQuery::Create()
                ->filterByDate(array("min" => $startPeriod, "max" => $endPeriod))
                ->findByPastorId($user->getId());

            $res[] = ['LastName' => $user->getLastName(), 'FirstName' => $user->getFirstName(), 'PersonID' => $user->getPersonID(), 'Visits' => $ormPastors->count(), 'UserID' => $user->getId()];
        }
        return $response->withJson(["Pastors" => $res]);
    }

    return null;
}

function personNeverBeenContacted(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    $pcs = new PastoralCareService();

    $range = $pcs->getRange();

    $members = $pcs->getPersonNeverBeenContacted($range['realDate']);

    if ( !is_null($members) ) {
        return $response->withJson(["PersonNeverBeenContacted" => $members->toArray()]);
    }

    return null;
}

function familyNeverBeenContacted(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    $pcs = new PastoralCareService();

    $range = $pcs->getRange();

    $members = $pcs->getFamiliesNeverBeenContacted($range['realDate']);

    if ( !is_null($members) ) {
        return $response->withJson(["FamilyNeverBeenContacted" => $members->toArray()]);
    }

    return null;
}

function singleNeverBeenContacted(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    $pcs = new PastoralCareService();

    $range = $pcs->getRange();

    $members = $pcs->getSingleNeverBeenContacted($range['realDate']);

    if ( !is_null($members) ) {
        return $response->withJson(["SingleNeverBeenContacted" => $members->toArray()]);
    }

    return null;
}

function retiredNeverBeenContacted(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    $pcs = new PastoralCareService();

    $range = $pcs->getRange();

    $members = $pcs->getRetiredNeverBeenContacted($range['realDate']);

    if ( !is_null($members) ) {
        return $response->withJson(["RetiredNeverBeenContacted" => $members->toArray()]);
    }

    return null;
}

function youngNeverBeenContacted(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    $pcs = new PastoralCareService();

    $range = $pcs->getRange();

    $members = $pcs->getYoungNeverBeenContacted($range['realDate']);

    if ( !is_null($members) ) {
        return $response->withJson(["YoungNeverBeenContacted" => $members->toArray()]);
    }

    return null;
}

function getPersonByClassificationPastoralCare(Request $request, Response $response, array $args) {
    if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() ) ) {
        return $response->withStatus(401);
    }

    $pcs = new PastoralCareService();

    $members = null;

    if (!isset ($args['type']) || $args['type'] == '1') {
        $members = $pcs->getPersonClassificationNotBeenReached();
    } else {
        $members = $pcs->getPersonClassificationNotBeenReached(true);
    }


    if ( !is_null($members) ) {
        return $response->withJson(["MembersClassicationsList" => $members]);
    }
}


function createRandomlyPastoralCare (Request $request, Response $response, array $args) {
    $input = (object)$request->getParsedBody();

    if ( isset ($input->typeID) && SessionUser::getUser()->isPastoralCareEnabled() ){
        $pcs = new PastoralCareService();
        $range = $pcs->getRange();

        switch ($input->typeID) {
            case 1: // person
                $person = $pcs->getPersonNeverBeenContacted($range['realDate'],true);

                if ( !is_null($person) ) {
                    return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                }
                break;
            case 2: // family
                $family = $pcs->getFamiliesNeverBeenContacted($range['realDate'],true);

                if ( !is_null($family) ) {
                    return $response->withJson(['status' => "success", "familyID" => $family->getId()]);
                }

                break;
            case 3: // old person
                $person = $pcs->getRetiredNeverBeenContacted($range['realDate'],true);

                if ( !is_null($person) ) {
                    return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                }
                break;
            case 4: // young person
                $person = $pcs->getYoungNeverBeenContacted($range['realDate'],true);

                if ( !is_null($person) ) {
                    return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                }
                break;
            case 5: // single person
                $family = $pcs->getSingleNeverBeenContacted($range['realDate'],true);

                if ( !is_null($family) ) {
                    $person = PersonQuery::create()->findOneByFamId($family->getId());
                    return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                }
                break;
        }

        return $response->withJson(['status' => "failed"]);

    }

    return $response->withJson(['status' => "failed"]);
}
