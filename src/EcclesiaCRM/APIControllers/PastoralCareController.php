<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\Utils\InputUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\PastoralCare;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareType;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserQuery;


class PastoralCareController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPastoralCareListForUser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!( SessionUser::getUser()->isPastoralCareEnabled() and SessionUser::getUser()->isMenuOptionsEnabled() and array_key_exists('UserID', $args) )) {
            return $response->withStatus(401);
        }


        if (isset ($args['UserID'])) {
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

            return $response->withJson(['ListOfMembers' => $ormPastors->toArray()]);
        }

        return $response->withStatus(401);
    }


    public function getAllPastoralCare(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        return $response->write(PastoralCareTypeQuery::Create()->find()->toJSON());
    }

    public function deletePastoralCareType(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->pastoralCareTypeId) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) {
            $pstCareType = PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId);

            if ($pstCareType != null) {
                $pstCareType->delete();
            }

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function createPastoralCareType(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->Visible) && isset ($input->Title) && isset ($input->Description) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) {
            $pstCareType = new PastoralCareType();

            $pstCareType->setVisible($input->Visible);
            $pstCareType->setTitle($input->Title);
            $pstCareType->setDesc($input->Description);
            $pstCareType->setComment(' ');

            $pstCareType->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function setPastoralCareType(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->pastoralCareTypeId) && isset ($input->Visible)
            && isset ($input->Title) && isset ($input->Description) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) {
            $pstCareType = PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId);

            $pstCareType->setVisible($input->Visible);
            $pstCareType->setTitle($input->Title);
            $pstCareType->setDesc($input->Description);

            $pstCareType->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function editPastoralCareType(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->pastoralCareTypeId) && SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled()) {
            return $response->write(PastoralCareTypeQuery::Create()->findOneById($input->pastoralCareTypeId)->toJSON());
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function addPastoralCarePerson(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->typeID) && isset ($input->personID) && isset ($input->currentPastorId)
            && isset ($input->visibilityStatus) && isset ($input->noteText)
            && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = new PastoralCare();

            $pstCare->setTypeId($input->typeID);

            $pstCare->setPersonId($input->personID);
            $pstCare->setPastorId($input->currentPastorId);

            $pastor = PersonQuery::Create()->findOneById($input->currentPastorId);

            if ($pastor != null) {
                $pstCare->setPastorName($pastor->getFullName());
            }

            $date = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $pstCare->setDate($date->format('Y-m-d H:i:s'));

            $pstCare->setVisible($input->visibilityStatus);
            $pstCare->setText(InputUtils::FilterHTML($input->noteText));

            $pstCare->save();

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function deletePastoralCarePerson(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->ID) && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = PastoralCareQuery::create()->findOneByID($input->ID);

            if ($pstCare != null) {
                $pstCare->delete();
            }

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function getPastoralCareInfoPerson(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->ID) && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = PastoralCareQuery::create()->leftJoinWithPastoralCareType()->findOneByID($input->ID);

            $typeDesc = $pstCare->getPastoralCareType()->getTitle() . ((!empty($pstCare->getPastoralCareType()->getDesc())) ? " (" . $pstCare->getPastoralCareType()->getDesc() . ")" : "");

            return $response->withJson(["id" => $pstCare->getId(), "typeid" => $pstCare->getTypeId(), "typedesc" => $typeDesc, "visible" => $pstCare->getVisible(), "text" => $pstCare->getText()]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function modifyPastoralCarePerson(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->ID) && isset ($input->typeID) && isset ($input->personID)
            && isset ($input->currentPastorId)
            && isset ($input->visibilityStatus) && isset ($input->noteText)
            && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = PastoralCareQuery::create()->findOneByID($input->ID);

            $pstCare->setTypeId($input->typeID);

            $pstCare->setPersonId($input->personID);
            $pstCare->setPastorId($input->currentPastorId);

            $pastor = PersonQuery::Create()->findOneById($input->currentPastorId);

            if ($pastor != null) {
                $pstCare->setPastorName($pastor->getFullName());
            }

            $date = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $pstCare->setDate($date->format('Y-m-d H:i:s'));

            $pstCare->setVisible($input->visibilityStatus);
            $pstCare->setText(InputUtils::FilterHTML($input->noteText));

            $pstCare->save();

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }


    public function addPastoralCareFamily(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->typeID) && isset ($input->familyID) && isset ($input->currentPastorId)
            && isset ($input->visibilityStatus) && isset ($input->noteText) && isset ($input->includeFamMembers)
            && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = new PastoralCare();

            $pstCare->setTypeId($input->typeID);

            $pstCare->setFamilyId($input->familyID);
            $pstCare->setPastorId($input->currentPastorId);

            $pastor = PersonQuery::Create()->findOneById($input->currentPastorId);

            if ($pastor != null) {
                $pstCare->setPastorName($pastor->getFullName());
            }

            $date = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $pstCare->setDate($date->format('Y-m-d H:i:s'));

            $pstCare->setVisible($input->visibilityStatus);
            $pstCare->setText(InputUtils::FilterHTML($input->noteText));

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

                    $date = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
                    $pstCare->setDate($date->format('Y-m-d H:i:s'));

                    $pstCare->setVisible($input->visibilityStatus);
                    $pstCare->setText(InputUtils::FilterHTML($input->noteText));

                    $pstCare->save();
                }
            }

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function deletePastoralCareFamily(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->ID) && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = PastoralCareQuery::create()->findOneByID($input->ID);

            if ($pstCare != null) {
                $pstCare->delete();
            }

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function getPastoralCareInfoFamily(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->ID) && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = PastoralCareQuery::create()->leftJoinWithPastoralCareType()->findOneByID($input->ID);

            $typeDesc = $pstCare->getPastoralCareType()->getTitle() . ((!empty($pstCare->getPastoralCareType()->getDesc())) ? " (" . $pstCare->getPastoralCareType()->getDesc() . ")" : "");

            return $response->withJson(["id" => $pstCare->getId(), "typeid" => $pstCare->getTypeId(), "typedesc" => $typeDesc, "visible" => $pstCare->getVisible(), "text" => $pstCare->getText()]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function modifyPastoralCareFamily(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->ID) && isset ($input->typeID) && isset ($input->familyID)
            && isset ($input->currentPastorId)
            && isset ($input->visibilityStatus) && isset ($input->noteText)
            && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pstCare = PastoralCareQuery::create()->findOneByID($input->ID);

            $pstCare->setTypeId($input->typeID);

            $pstCare->setFamilyId($input->familyID);
            $pstCare->setPastorId($input->currentPastorId);

            $pastor = PersonQuery::Create()->findOneById($input->currentPastorId);

            if ($pastor != null) {
                $pstCare->setPastorName($pastor->getFullName());
            }

            $date = new \DateTime('now', new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $pstCare->setDate($date->format('Y-m-d H:i:s'));

            $pstCare->setVisible($input->visibilityStatus);
            $pstCare->setText(InputUtils::FilterHTML($input->noteText));

            $pstCare->save();

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }

    public function pastoralcareMembersDashboard(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
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

        if (!is_null($users)) {
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
                        break;
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
                    ->findByPastorId($user->getId());

                $res[] = ['LastName' => $user->getLastName(), 'FirstName' => $user->getFirstName(), 'PersonID' => $user->getPersonID(), 'Visits' => $ormPastors->count(), 'UserID' => $user->getId()];
            }
            return $response->withJson(["Pastors" => $res]);
        }

        return $response->withStatus( 404);
    }

    public function personNeverBeenContacted(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $pcs = $this->container->get('PastoralCareService');

        $range = $pcs->getRange();

        $members = $pcs->getPersonNeverBeenContacted($range['realDate']);

        if (!is_null($members)) {
            return $response->withJson(["PersonNeverBeenContacted" => $members->toArray()]);
        }

        return $response->withStatus( 404);
    }

    public function familyNeverBeenContacted(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $pcs = $this->container->get('PastoralCareService');

        $range = $pcs->getRange();

        $members = $pcs->getFamiliesNeverBeenContacted($range['realDate']);

        if (!is_null($members)) {
            return $response->withJson(["FamilyNeverBeenContacted" => $members->toArray()]);
        }

        return $response->withStatus( 404);
    }

    public function singleNeverBeenContacted(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $pcs = $this->container->get('PastoralCareService');

        $range = $pcs->getRange();

        $members = $pcs->getSingleNeverBeenContacted($range['realDate']);

        if (!is_null($members)) {
            return $response->withJson(["SingleNeverBeenContacted" => $members->toArray()]);
        }

        return $response->withStatus( 404);
    }

    public function retiredNeverBeenContacted(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $pcs = $this->container->get('PastoralCareService');

        $range = $pcs->getRange();

        $members = $pcs->getRetiredNeverBeenContacted($range['realDate']);

        if (!is_null($members)) {
            return $response->withJson(["RetiredNeverBeenContacted" => $members->toArray()]);
        }

        return $response->withStatus( 404);
    }

    public function youngNeverBeenContacted(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $pcs = $this->container->get('PastoralCareService');

        $range = $pcs->getRange();

        $members = $pcs->getYoungNeverBeenContacted($range['realDate']);

        if (!is_null($members)) {
            return $response->withJson(["YoungNeverBeenContacted" => $members->toArray()]);
        }

        return $response->withStatus( 404);
    }

    public function getPersonByClassificationPastoralCare(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isPastoralCareEnabled() and SessionUser::getUser()->isMenuOptionsEnabled() and array_key_exists('type', $args ))) {
            return $response->withStatus(401);
        }

        $pcs = $this->container->get('PastoralCareService');

        $members = null;

        if (!isset ($args['type']) || $args['type'] == '1') {
            $members = $pcs->getPersonClassificationNotBeenReached();
        } else {
            $members = $pcs->getPersonClassificationNotBeenReached(true);
        }


        if (!is_null($members)) {
            return $response->withJson(["MembersClassicationsList" => $members]);
        }

        return $response->withStatus( 404);
    }


    public function createRandomlyPastoralCare(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->typeID) && SessionUser::getUser()->isPastoralCareEnabled()) {
            $pcs = $this->container->get('PastoralCareService');
            $range = $pcs->getRange();

            switch ($input->typeID) {
                case 1: // person
                    $person = $pcs->getPersonNeverBeenContacted($range['realDate'], true);

                    if (!is_null($person)) {
                        return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                    }
                    break;
                case 2: // family
                    $family = $pcs->getFamiliesNeverBeenContacted($range['realDate'], true);

                    if (!is_null($family)) {
                        return $response->withJson(['status' => "success", "familyID" => $family->getId()]);
                    }

                    break;
                case 3: // old person
                    $person = $pcs->getRetiredNeverBeenContacted($range['realDate'], true);

                    if (!is_null($person)) {
                        return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                    }
                    break;
                case 4: // young person
                    $person = $pcs->getYoungNeverBeenContacted($range['realDate'], true);

                    if (!is_null($person)) {
                        return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                    }
                    break;
                case 5: // single person
                    $family = $pcs->getSingleNeverBeenContacted($range['realDate'], true);

                    if (!is_null($family)) {
                        $person = PersonQuery::create()->findOneByFamId($family->getId());
                        return $response->withJson(['status' => "success", "personID" => $person->getId()]);
                    }
                    break;
            }

            return $response->withJson(['status' => "failed"]);

        }

        return $response->withJson(['status' => "failed"]);
    }
}
