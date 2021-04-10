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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use EcclesiaCRM\Group;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\ListOptionQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\GroupManagerPerson;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\GroupTypeQuery;
use EcclesiaCRM\GroupType;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\MiscUtils;


use EcclesiaCRM\MyPDO\CardDavPDO;
use Propel\Runtime\Propel;

class PeopleGroupController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setGroupSepecificPropertyStatus (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $input = $request->getParsedBody();
        $groupService = $this->container->get("GroupService");
        if ($input['GroupSpecificPropertyStatus']) {
            $groupService->enableGroupSpecificProperties($groupID);
            return $response->withJson(['status' => 'group specific properties enabled']);
        } else {
            $groupService->disableGroupSpecificProperties($groupID);
            return $response->withJson(['status' => 'group specific properties disabled']);
        }
    }

    public function roles (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $roleName = $request->getParsedBody()['roleName'];
        $groupService = $this->container->get("GroupService");
        return $response->write(json_encode($groupService->addGroupRole($groupID, $roleName)));
    }

    public function deleteRole (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $roleID = $args['roleID'];

        $logger = $this->container->get('Logger');

        $logger->info("GID : ".$groupID." roleID : ".$roleID);

        $groupService = $this->container->get("GroupService");
        return $response->write(json_encode($groupService->deleteGroupRole($groupID, $roleID)));
    }

    public function getAllGroups (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        return $response->write(GroupQuery::create()->groupByName()->find()->toJSON());
    }

    public function defaultGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $res = GroupQuery::create()->orderByName()->findOne()->getId();

        return $response->withJson($res);
    }

    public function groupproperties (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $ormAssignedProperties = Record2propertyR2pQuery::Create()
            ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
            ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
            ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
            ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
            ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
            ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
            ->where(PropertyTableMap::COL_PRO_CLASS."='g'")
            ->addAscendingOrderByColumn('ProName')
            ->addAscendingOrderByColumn('ProTypeName')
            ->findByR2pRecordId($args['groupID']);

        return $response->write($ormAssignedProperties->toJSON());
    }

    public function addressBook (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        // we get the group
        $group = GroupQuery::create()->findOneById ($args['groupId']);

        // We set the BackEnd for sabre Backends
        $carddavBackend = new CardDavPDO();

        $addressbook = $carddavBackend->getAddressBookForGroup ($args['groupId']);

        $filename = $group->getName().".vcf";

        $output = $carddavBackend->generateVCFForAddressBook($addressbook['id']);
        $size = strlen($output);

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Length',$size)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Expires', '0');


        $response->getBody()->write($output);

        return $response;
    }

    public function searchGroup(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $query = $args['query'];

        $searchLikeString = '%'.$query.'%';


        $groups = GroupQuery::create()
            ->filterByName($searchLikeString, Criteria::LIKE)
            //->orderByName()
            ->find();


        $return = [];

        if (!empty($groups))
        {
            $data = [];
            $id=0;

            foreach ($groups as $group) {
                $values['id'] = $id++;
                $values['objid'] = $group->getId();
                $values['text'] = $group->getName();
                $values['uri'] = SystemURLs::getRootPath()."/v2/group/".$group->getId()."/view";

                array_push($return, $values);
            }
        }
        return $response->withJson($return);
    }

    public function deleteAllManagers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $options = (object) $request->getParsedBody();

        if ( isset ($options->groupID) ) {
            $managers = GroupManagerPersonQuery::Create()->filterByGroupId($options->groupID)->find();

            if ($managers != null) {
                $managers->delete();
            }
            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function deleteManager (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $options = (object) $request->getParsedBody();

        if ( isset ($options->groupID) && isset ($options->personID) ) {
            $manager = GroupManagerPersonQuery::Create()->filterByPersonID($options->personID)->filterByGroupId($options->groupID)->findOne();

            if ($manager != null) {
                $manager->delete();
            }

            $managers = GroupManagerPersonQuery::Create()->filterByGroupId($options->groupID)->find();

            if ($managers->count()) {
                $data = [];

                foreach ($managers as $manager) {

                    $elt = ['name'=> $manager->getPerson()->getFullName(),
                        'personID'=>$manager->getPerson()->getId()];

                    array_push($data, $elt);

                }

                return $response->withJson($data);
            } else {
                return $response->withJson(['status' => "empty"]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function getManagers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $option = (object) $request->getParsedBody();

        if (isset ($option->groupID)) {
            $managers = GroupManagerPersonQuery::Create()->findByGroupId($option->groupID);

            if ($managers->count()) {
                $data = [];

                foreach ($managers as $manager) {
                    if (!$manager->getPerson()->isDeactivated()) {
                        $elt = ['name'=> $manager->getPerson()->getFullName(),
                            'personID'=>$manager->getPerson()->getId()];

                        array_push($data, $elt);
                    }
                }

                return $response->withJson($data);
            } else {
                return $response->withJson(['status' => "empty"]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function addManager (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $options = (object)$request->getParsedBody();

        if (isset ($options->personID) && isset($options->groupID)) {
            $groupManager = new GroupManagerPerson();

            $groupManager->setPersonId($options->personID);
            $groupManager->setGroupId($options->groupID);

            $groupManager->save();

            return $response->withJson(['status' => "success".$options->groupID." ".$options->personID]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function groupsInCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupsInCart = [];
        $groups = GroupQuery::create()->find();
        foreach ($groups as $group) {
            if ($group->checkAgainstCart()) {
                array_push($groupsInCart, $group->getId());
            }
        }
        return $response->withJson(['groupsInCart' => $groupsInCart]);
    }

    public function newGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupSettings = (object) $request->getParsedBody();
        $group = new Group();
        if ($groupSettings->isSundaySchool) {
            $group->setType(4);// now each sunday school group has a type of 4
        } else {
            $group->setType(3);// now each normal group has a type of 3
        }

        $group->setName($groupSettings->groupName);
        $group->save();


        $groupType = new GroupType();

        if (!is_null($groupType)) {
            $groupType->setGroupId ($group->getId());
            $groupType->setListOptionId (0);
            $groupType->save();
        }

        return $response->write( $group->toJSON() );
    }

    public function updateGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findOneById($groupID);
        $group->setName($input->groupName);

        $groupType = GroupTypeQuery::Create()->findOneByGroupId ($groupID);

        if (!is_null($groupType)) {
            $groupType->setListOptionId ($input->groupType);
            $groupType->save();
        } else {
            $groupType = new GroupType();

            $groupType->setGroupId ($groupID);
            $groupType->setListOptionId ($input->groupType);

            $groupType->save();
        }

        $group->setDescription($input->description);

        $group->save();

        return $response->write( $group->toJSON() );
    }

    public function groupInfo (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        return $response->write( GroupQuery::create()->findOneById($args['groupID'])->toJSON());
    }

    public function groupCartStatus (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        return $response->write( GroupQuery::create()->findOneById($args['groupID'])->checkAgainstCart());
    }

    public function deleteGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        GroupQuery::create()->findOneById($groupID)->delete();
        return $response->withJson(['status'=>'success']);
    }

    public function groupMembers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $members = Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->usePersonQuery()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->endUse()
            ->findByGroupId($groupID);


        // we loop to find the information in the family to add adresses etc ... this is now unusefull, the address is created automatically
        foreach ($members as $member)
        {
            $p = $member->getPerson();
            $fam = $p->getFamily();

            // Philippe Logel : this is usefull when a person don't have a family : ie not an address
            if (!is_null($fam)
                && !is_null($fam->getAddress1())
                && !is_null($fam->getAddress2())
                && !is_null($fam->getCity())
                && !is_null($fam->getState())
                && !is_null($fam->getZip())
            )
            {
                $p->setAddress1 ($fam->getAddress1());
                $p->setAddress2 ($fam->getAddress2());

                $p->setCity($fam->getCity());
                $p->setState($fam->getState());
                $p->setZip($fam->getZip());
            }
        }

        return $response->write($members->toJSON());
    }

    public function groupEvents (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $members = Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->findByGroupId($groupID);
        return $response->write($members->toJSON());
    }

    public function removePersonFromGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $person = PersonQuery::create()->findPk($userID);
        $group = GroupQuery::create()->findPk($groupID);
        $groupRoleMemberships = $group->getPerson2group2roleP2g2rs();

        $groupService = $this->container->get("GroupService");

        foreach ($groupRoleMemberships as $groupRoleMembership) {
            if ($groupRoleMembership->getPersonId() == $person->getId()) {
                $groupService->removeUserFromGroup($groupID, $person->getId());
                //$groupRoleMembership->delete();
                $note = new Note();
                $note->setText(_("Deleted from group"). ": " . $group->getName());
                $note->setType("group");
                $note->setEntered(SessionUser::getUser()->getPersonId());
                $note->setPerId($person->getId());
                $note->save();
            }
        }
        return $response->withJson(['success' => 'true']);
    }

    public function addPersonToGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $person = PersonQuery::create()->findPk($userID);
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findPk($groupID);
        $p2g2r = Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($groupID)
            ->filterByPersonId($userID)
            ->findOneOrCreate();
        if($input->RoleID)
        {
            $p2g2r->setRoleId($input->RoleID);
        }
        else
        {
            $p2g2r->setRoleId($group->getDefaultRole());
        }

        $group->addPerson2group2roleP2g2r($p2g2r);
        $group->save();
        $note = new Note();
        $note->setText(_("Added to group"). ": " . $group->getName());
        $note->setType("group");
        $note->setEntered(SessionUser::getUser()->getPersonId());
        $note->setPerId($person->getId());
        $note->save();
        $members = Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->filterByPersonId($input->PersonID)
            ->findByGroupId($groupID);

        return  $response->write($members->toJSON());
    }

    public function addTeacherToGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $person = PersonQuery::create()->findPk($userID);
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findPk($groupID);
        $p2g2r = Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($groupID)
            ->filterByPersonId($userID)
            ->findOneOrCreate();
        if($input->RoleID)
        {
            $p2g2r->setRoleId($input->RoleID);
        }
        else
        {
            $p2g2r->setRoleId($group->getDefaultRole());
        }

        $group->addPerson2group2roleP2g2r($p2g2r);
        $group->save();
        $note = new Note();
        $note->setText(_("Added to group"). ": " . $group->getName());
        $note->setType("group");
        $note->setEntered(SessionUser::getUser()->getPersonId());
        $note->setPerId($person->getId());
        $note->save();
        $members = Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->filterByPersonId($input->PersonID)
            ->findByGroupId($groupID);

        return  $response->write($members->toJSON());
    }

    public function userRoleByUserId (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $roleID = $request->getParsedBody()['roleID'];
        $membership = Person2group2roleP2g2rQuery::create()->filterByGroupId($groupID)->filterByPersonId($userID)->findOne();
        $membership->setRoleId($roleID);
        $membership->save();

        return  $response->write($membership->toJSON());
    }

    public function rolesByRoleId (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $roleID = $args['roleID'];
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findOneById($groupID);
        if (isset($input->groupRoleName)) {
            $groupRole = ListOptionQuery::create()->filterById($group->getRoleListId())->filterByOptionId($roleID)->findOne();
            $groupRole->setOptionName($input->groupRoleName);
            $groupRole->save();
            return $response->withJson(['success' => true]);
        } elseif (isset($input->groupRoleOrder)) {
            $groupRole = ListOptionQuery::create()->filterById($group->getRoleListId())->filterByOptionId($roleID)->findOne();
            $groupRole->setOptionSequence($input->groupRoleOrder);
            $groupRole->save();
            return $response->withJson(['success' => true]);
        }
        return  $response->withJson(['success' => false]);
    }

    public function allRoles (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $group = GroupQuery::create()->findOneById($groupID);
        $roles = ListOptionQuery::create()->filterById($group->getRoleListId())->orderByOptionName()->find();
        return $response->write($roles->toJSON());
    }

    public function defaultRoleForGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $roleID = $request->getParsedBody()['roleID'];
        $group = GroupQuery::create()->findPk($groupID);
        $group->setDefaultRole($roleID);
        $group->save();
        return $response->withJson(['success' => true]);
    }

    public function settingsActiveValue (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $flag = $args['value'];
        if ($flag == "true" || $flag == "false") {
            $group = GroupQuery::create()->findOneById($groupID);
            if ($group != null) {
                $group->setActive($flag);
                $group->save();
            } else {
                return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid group id']);
            }
            return $response->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid status value']);
        }
    }

    public function settingsEmailExportVvalue(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupID = $args['groupID'];
        $flag = $args['value'];
        if ($flag == "true" || $flag == "false") {
            $group = GroupQuery::create()->findOneById($groupID);
            if ($group != null) {
                $group->setIncludeInEmailExport($flag);
                $group->save();
            } else {
                return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid group id']);
            }
            return $response->withJson(['status' => "success"]);
        } else {
            return $response->withStatus(500)->withJson(['status' => "error", 'reason' => 'invalid export value']);
        }
    }

    public function deleteGroupField(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
            return $response->withStatus(404);
        }

        $values = (object)$request->getParsedBody();

        if ( isset ($values->PropID) && isset ($values->Field) && isset ($values->GroupID) )
        {
            // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
            $groupPropMstr = GroupPropMasterQuery::Create()->filterByGroupId ($values->GroupID)->findOneByField ($values->Field);

            if ( !is_null ($groupPropMstr) && $groupPropMstr->getTypeId() == 12 ) {
                $list = ListOptionQuery::Create()->findById($groupPropMstr->getSpecial());
                if( !is_null($list) ) {
                    $list->delete();
                }
            }

            // this can't be propeled
            $connection = Propel::getConnection();
            $sSQL = 'ALTER TABLE `groupprop_'.$values->GroupID.'` DROP `'.$values->Field.'` ;';
            $connection->exec($sSQL);

            // now we can delete the GroupPropMasterQuery
            $groupPropMstr->delete();


            $allGroupPropMstr = GroupPropMasterQuery::Create()->findByGroupId ($values->GroupID);
            $numRows = $allGroupPropMstr->count();

            // Shift the remaining rows up by one, unless we've just deleted the only row
            if ($numRows != 0) {
                for ($reorderRow = $values->PropID + 1; $reorderRow <= $numRows + 1; $reorderRow++) {
                    $fisrtGroupPropMstr = GroupPropMasterQuery::Create()->filterByGroupId ($values->GroupID)->findOneByPropId ($reorderRow);

                    if ( !is_null ($fisrtGroupPropMstr) ){
                        $fisrtGroupPropMstr->setPropId($reorderRow - 1)->save();
                    }
                }
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function upactionGroupField (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
            return $response->withStatus(404);
        }

        $values = (object)$request->getParsedBody();

        if ( isset ($values->PropID) && isset ($values->Field) && isset ($values->GroupID) )
        {
            // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
            $fisrtGroupPropMstr = GroupPropMasterQuery::Create()->filterByGroupId ($values->GroupID)->findOneByPropId ($values->PropID - 1);
            $fisrtGroupPropMstr->setPropId($values->PropID)->save();

            $secondGroupPropMstr = GroupPropMasterQuery::Create()->filterByGroupId ($values->GroupID)->findOneByField ($values->Field);
            $secondGroupPropMstr->setPropId($values->PropID - 1)->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function downactionGroupField (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
            return $response->withStatus(404);
        }

        $values = (object)$request->getParsedBody();

        if ( isset ($values->PropID) && isset ($values->Field) && isset ($values->GroupID) )
        {
            // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
            $fisrtGroupPropMstr = GroupPropMasterQuery::Create()->filterByGroupId ($values->GroupID)->findOneByPropId ($values->PropID + 1);
            $fisrtGroupPropMstr->setPropId($values->PropID)->save();

            $secondGroupPropMstr = GroupPropMasterQuery::Create()->filterByGroupId ($values->GroupID)->findOneByField ($values->Field);
            $secondGroupPropMstr->setPropId($values->PropID+1)->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function groupSundaySchool (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        $iGroupId = $args['groupID'];

        if ( !(SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled()
            || SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupId) || SessionUser::getUser()->isMenuOptionsEnabled()) ) {
            return $response->withStatus(404);
        }

        $sundaySchoolService = $this->container->get('SundaySchoolService');

        $rsTeachers = $sundaySchoolService->getClassByRole($iGroupId, 'Teacher');

        $TeachersEmails = [];
        $KidsEmails = [];
        $ParentsEmails = [];

        $thisClassChildren = $sundaySchoolService->getKidsFullDetails($iGroupId);

        foreach ($thisClassChildren as $child) {
            if ($child['dadEmail'] != '') {
                array_push($ParentsEmails, $child['dadEmail']);
            }
            if ($child['momEmail'] != '') {
                array_push($ParentsEmails, $child['momEmail']);
            }
            if ($child['kidEmail'] != '') {
                array_push($KidsEmails, $child['kidEmail']);
            }
        }

        $teachersProps = [];
        foreach ($rsTeachers as $teacher) {
            array_push($TeachersEmails, $teacher['per_Email']);

            $ormPropLists = GroupPropMasterQuery::Create()
                ->filterByPersonDisplay('true')
                ->orderByPropId()
                ->findByGroupId($iGroupId);

            $props = '';
            if ( $ormPropLists->count() > 0 ) {
                $person = PersonQuery::create()->findOneById($teacher['per_ID']);
                $sPhoneCountry = MiscUtils::SelectWhichInfo($person->getCountry(), (!is_null($person->getFamily()))?$person->getFamily()->getCountry():null, false);

                $sSQL = 'SELECT * FROM groupprop_' . $iGroupId . ' WHERE per_ID = ' . $teacher['per_ID'];

                $connection = Propel::getConnection();
                $statement = $connection->prepare($sSQL);
                $statement->execute();
                $aPersonProps = $statement->fetch(PDO::FETCH_BOTH);


                if ($ormPropLists->count() > 0) {
                    foreach ($ormPropLists as $ormPropList) {
                        $currentData = trim($aPersonProps[$ormPropList->getField()]);
                        if (strlen($currentData) > 0) {
                            $prop_Special = $ormPropList->getSpecial();

                            if ($ormPropList->getTypeId() == 11) {
                                $prop_Special = $sPhoneCountry;
                            }

                            $props = $ormPropList->getName() /*. ":" . OutputUtils::displayCustomField($ormPropList->getTypeId(), $currentData, $prop_Special)*/ . ", ";
                        }
                    }
                }
            }

            array_push($teachersProps, [$teacher['per_ID'] => substr($props, 0, -2)]);
        }

        $allEmails = array_unique(array_merge($ParentsEmails, $KidsEmails, $TeachersEmails));
        $sEmailLink = implode(SessionUser::getUser()->MailtoDelimiter(), $allEmails).',';

        $roleEmails = new \stdClass();

        $roleEmails->Parents = implode(SessionUser::getUser()->MailtoDelimiter(), $ParentsEmails).',';
        $roleEmails->Teachers = implode(SessionUser::getUser()->MailtoDelimiter(), $TeachersEmails).',';
        $roleEmails->Kids = implode(SessionUser::getUser()->MailtoDelimiter(), $KidsEmails).',';

        // Add default email if default email has been set and is not already in string
        if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
            $sEmailLink .= SessionUser::getUser()->MailtoDelimiter().SystemConfig::getValue('sToEmailAddress');
        }
        $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

        $emailLink = mb_substr($sEmailLink, 0, -3);

        $dropDown = new \stdClass();
        $dropDown->allNormal    = MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:');
        $dropDown->allNormalBCC = MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:?bcc=');

        return $response->withJson(['success' => true, "teachers" => $rsTeachers, "teachersProps" => $teachersProps,  "kids" => $thisClassChildren, "roleEmails" => $roleEmails, "emailLink" => $emailLink, "dropDown" => $dropDown]);
    }
}
