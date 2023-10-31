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
use EcclesiaCRM\Reports\PDF_Badge;


use EcclesiaCRM\MyPDO\CardDavPDO;
use Propel\Runtime\Propel;

class PeopleGroupController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setGroupSepecificPropertyStatus (ServerRequest $request, Response $response, array $args): Response {
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

    public function roles (ServerRequest $request, Response $response, array $args): Response {
        $groupID = $args['groupID'];
        $roleName = $request->getParsedBody()['roleName'];
        $groupService = $this->container->get("GroupService");
        return $response->write(json_encode($groupService->addGroupRole($groupID, $roleName)));
    }

    public function deleteRole (ServerRequest $request, Response $response, array $args): Response {
        $groupID = $args['groupID'];
        $roleID = $args['roleID'];

        $logger = $this->container->get('Logger');

        $logger->info("GID : ".$groupID." roleID : ".$roleID);

        $groupService = $this->container->get("GroupService");
        return $response->write(json_encode($groupService->deleteGroupRole($groupID, $roleID)));
    }

    public function getAllGroups (ServerRequest $request, Response $response, array $args): Response {
        if ( !(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isManageGroups()) ) {
            $ids = SessionUser::getUser()->getGroupManagerIds();

            return $response->write(GroupQuery::create()->groupByName()->findById($ids)->toJSON());
        }

        return $response->write(GroupQuery::create()->groupByName()->find()->toJSON());
    }

    public function defaultGroup (ServerRequest $request, Response $response, array $args): Response {
        $res = GroupQuery::create()->orderByName()->findOne()->getId();

        return $response->withJson($res);
    }

    public function groupproperties (ServerRequest $request, Response $response, array $args): Response {
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

    public function addressBook (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !(SessionUser::getUser()->isSeePrivacyDataEnabled() and array_key_exists('groupId', $args)) ) {
            return $response->withStatus(401);
        }

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

    public function searchGroup(ServerRequest $request, Response $response, array $args): Response
    {
        if ( array_key_exists('query', $args) ) {
            return $response->withStatus(401);
        }

        $query = $args['query'];

        $query = filter_var($query, FILTER_SANITIZE_STRING);

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

    public function deleteAllManagers (ServerRequest $request, Response $response, array $args): Response {
        $options = (object) $request->getParsedBody();

        if ( isset ($options->groupID) and SessionUser::getUser()->isManageGroupsEnabled() ) {
            $managers = GroupManagerPersonQuery::Create()->filterByGroupId($options->groupID)->find();

            if ($managers != null) {
                $managers->delete();
            }
            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function deleteManager (ServerRequest $request, Response $response, array $args): Response {
        $options = (object) $request->getParsedBody();

        if ( isset ($options->groupID) and isset ($options->personID) and SessionUser::getUser()->isManageGroupsEnabled() ) {
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

    public function getManagers (ServerRequest $request, Response $response, array $args): Response {
        $option = (object) $request->getParsedBody();

        if ( isset ($option->groupID) and SessionUser::getUser()->isGroupManagerEnabledForId($option->groupID) ) {
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

    public function addManager (ServerRequest $request, Response $response, array $args): Response {
        $options = (object)$request->getParsedBody();

        if (isset ($options->personID) and isset($options->groupID) and SessionUser::getUser()->isGroupManagerEnabledForId($options->groupID) ) {
            $groupManager = new GroupManagerPerson();

            $groupManager->setPersonId($options->personID);
            $groupManager->setGroupId($options->groupID);

            $groupManager->save();

            return $response->withJson(['status' => "success".$options->groupID." ".$options->personID]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function groupsInCart (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::getUser()->isShowCartEnabled() ) {
            return $response->withStatus(401);
        }

        $groupsInCart = [];
        $groups = GroupQuery::create()->find();
        foreach ($groups as $group) {
            if ($group->checkAgainstCart()) {
                array_push($groupsInCart, $group->getId());
            }
        }
        return $response->withJson(['groupsInCart' => $groupsInCart]);
    }

    public function newGroup (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::getUser()->isManageGroupsEnabled() ) {
            return $response->withStatus(401);
        }

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

    public function updateGroup (ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object) $request->getParsedBody();

        if ( !( SessionUser::getUser()->isGroupManagerEnabledForId($args['groupID']) and array_key_exists('groupID', $args)
            and isset($input->groupName) and isset($input->groupType) and isset($input->description)) ) {
            return $response->withStatus(401);
        }

        $groupID = $args['groupID'];

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

    public function groupInfo (ServerRequest $request, Response $response, array $args): Response {
        if ( !( SessionUser::getUser()->isGroupManagerEnabledForId($args['groupID']) and array_key_exists('groupID', $args) ) ) {
            return $response->withStatus(401);
        }
        return $response->write( GroupQuery::create()->findOneById($args['groupID'])->toJSON());
    }

    public function groupCartStatus (ServerRequest $request, Response $response, array $args): Response {
        if ( !( SessionUser::getUser()->isGroupManagerEnabledForId($args['groupID']) and SessionUser::getUser()->isShowCartEnabled()
            and array_key_exists('groupID', $args) ) ) {
            return $response->withStatus(401);
        }
        return $response->write( GroupQuery::create()->findOneById($args['groupID'])->checkAgainstCart());
    }

    public function deleteGroup (ServerRequest $request, Response $response, array $args): Response {
        if ( !( SessionUser::getUser()->isManageGroupsEnabled()
            and array_key_exists('groupID', $args) ) ) {
            return $response->withStatus(401);
        }
        $groupID = $args['groupID'];
        GroupQuery::create()->findOneById($groupID)->delete();
        return $response->withJson(['status'=>'success']);
    }

    public function groupMembers (ServerRequest $request, Response $response, array $args): Response {
        if ( !( SessionUser::getUser()->isGroupManagerEnabledForId($args['groupID'])
            and array_key_exists('groupID', $args) ) ) {
            return $response->withStatus(401);
        }

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

    public function groupEvents (ServerRequest $request, Response $response, array $args): Response {
        if ( !( SessionUser::getUser()->isGroupManagerEnabledForId($args['groupID'])
            and array_key_exists('groupID', $args) ) ) {
            return $response->withStatus(401);
        }

        $groupID = $args['groupID'];
        $members = Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->findByGroupId($groupID);
        return $response->write($members->toJSON());
    }

    public function removePersonFromGroup (ServerRequest $request, Response $response, array $args): Response {
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

    public function addPersonToGroup (ServerRequest $request, Response $response, array $args): Response {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $person = PersonQuery::create()->findPk($userID);
        $input = (object) $request->getParsedBody();
        $group = GroupQuery::create()->findPk($groupID);

        if ($group->getType() == 4 and $person->getFamId() == 0) {
            // sundayschool group should be in a family
            return $response->withJson(['status' => "failed"]);
        }

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

    public function addTeacherToGroup (ServerRequest $request, Response $response, array $args): Response {
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

    public function userRoleByUserId (ServerRequest $request, Response $response, array $args): Response {
        $groupID = $args['groupID'];
        $userID = $args['userID'];
        $roleID = $request->getParsedBody()['roleID'];
        $membership = Person2group2roleP2g2rQuery::create()->filterByGroupId($groupID)->filterByPersonId($userID)->findOne();
        $membership->setRoleId($roleID);
        $membership->save();

        return  $response->write($membership->toJSON());
    }

    public function rolesByRoleId (ServerRequest $request, Response $response, array $args): Response {
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

    public function allRoles (ServerRequest $request, Response $response, array $args): Response {
        $groupID = $args['groupID'];
        $group = GroupQuery::create()->findOneById($groupID);
        $roles = ListOptionQuery::create()->filterById($group->getRoleListId())->orderByOptionName()->find();
        return $response->write($roles->toJSON());
    }

    public function defaultRoleForGroup (ServerRequest $request, Response $response, array $args): Response {
        $groupID = $args['groupID'];
        $roleID = $request->getParsedBody()['roleID'];
        $group = GroupQuery::create()->findPk($groupID);
        $group->setDefaultRole($roleID);
        $group->save();
        return $response->withJson(['success' => true]);
    }

    public function settingsActiveValue (ServerRequest $request, Response $response, array $args): Response {
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

    public function settingsEmailExportVvalue(ServerRequest $request, Response $response, array $args): Response {
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

    public function deleteGroupField(ServerRequest $request, Response $response, array $args): Response {
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

    public function upactionGroupField (ServerRequest $request, Response $response, array $args): Response {
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

    public function downactionGroupField (ServerRequest $request, Response $response, array $args): Response {
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

    public function groupSundaySchool (ServerRequest $request, Response $response, array $args): Response {

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
                $aPersonProps = $statement->fetch(\PDO::FETCH_BOTH);


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

    public function emptygroup (ServerRequest $request, Response $response, array $args): Response
    {
        $values = (object)$request->getParsedBody();

        $groupID = $values->groupID;

        $members = Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->usePersonQuery()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->endUse()
            ->findByGroupId($groupID);


        // we loop to find the information in the family to add adresses etc ... this is now unusefull, the address is created automatically
        foreach ($members as $member) {
            $personId = $member->getPersonId();

            $group = GroupQuery::create()->findPk($groupID);
            $groupRoleMemberships = $group->getPerson2group2roleP2g2rs();

            $groupService = $this->container->get("GroupService");

            foreach ($groupRoleMemberships as $groupRoleMembership) {
                if ($groupRoleMembership->getPersonId() == $personId) {
                    $groupService->removeUserFromGroup($groupID, $personId);
                    //$groupRoleMembership->delete();
                    $note = new Note();
                    $note->setText(_("Deleted from group") . ": " . $group->getName());
                    $note->setType("group");
                    $note->setEntered(SessionUser::getUser()->getPersonId());
                    $note->setPerId($personId);
                    $note->save();
                }
            }
        }
        return $response->withJson(['success' => 'true']);
    }

    public function renderBadge (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !SessionUser::getUser()->isMenuOptionsEnabled() ) {
            return $response->withStatus(404);
        }

        $values = (object)$request->getParsedBody();

        if ( isset ($values->title) && isset ($values->back)
            && isset ($values->labelfont) && isset ($values->labeltype) && isset ($values->labelfontsize) 
            && isset ($values->imagePosition) && isset($values->groupID)
            && isset ($values->sundaySchoolNamePosition) && isset($values->titlePosition))
        {
            ob_start();
            $pdf = new PDF_Badge($values->labeltype, 1, 1, 'mm', true);

            // set the cookies
            setcookie('titlePositionSC', $values->titlePosition, time() + 60 * 60 * 24 * 90, '/');
            setcookie('TitlelabelfontsizeSC', $values->titleFontSize, time() + 60 * 60 * 24 * 90, '/');
            setcookie('sundaySchoolNameSC', $values->sundaySchoolName, time() + 60 * 60 * 24 * 90, '/');
            setcookie('sundaySchoolNamePositionSC', $values->sundaySchoolNamePosition, time() + 60 * 60 * 24 * 90, '/');
            setcookie('SundaySchoolNameFontSizeSC', $values->sundaySchoolNameFontSize, time() + 60 * 60 * 24 * 90, '/');
            setcookie('sBackgroudColorSC', $values->back, time() + 60 * 60 * 24 * 90, '/');
            setcookie('imageSC', $values->imageName, time() + 60 * 60 * 24 * 90, '/');
            setcookie('imagePositionSC', $values->imagePosition, time() + 60 * 60 * 24 * 90, '/');
            setcookie('sTitleColorSC', $values->title, time() + 60 * 60 * 24 * 90, '/');
            setcookie('labeltype', $values->labeltype, time() + 60 * 60 * 24 * 90, '/');
            setcookie('labelfont', $values->labelfont, time() + 60 * 60 * 24 * 90, '/');            
            setcookie('labelfontsize', $values->labelfontsize, time() + 60 * 60 * 24 * 90, '/');

            $sFontInfo = MiscUtils::FontFromName($values->labelfont);

            $pdf->SetFont($sFontInfo[0], $sFontInfo[1]);

            $group = GroupQuery::create()->findOneById($values->groupID);


            list($title_red, $title_gren, $title_blue) = sscanf($values->title, "#%02x%02x%02x");
            list($back_red, $back_gren, $back_blue) = sscanf($values->back, "#%02x%02x%02x");

            if ($values->useQRCode) {
                $pdf->Add_PDF_Badge($values->sundaySchoolName, $values->sundaySchoolNamePosition, $values->titleFontSize, _("Name"), _("First Name"), 
                    $group->getName(),$values->titlePosition, $values->sundaySchoolNameFontSize,
                    "props", $values->labelfontsize, "../Images/background/".$values->imageName, $title_red, $title_gren, $title_blue,
                    $back_red, $back_gren, $back_blue, $values->imagePosition, $values->groupID, "id");
            } else {
                $pdf->Add_PDF_Badge($values->sundaySchoolName,$values->sundaySchoolNamePosition, $values->titleFontSize, _("Name"), _("First Name"), 
                    $group->getName(),$values->titlePosition, $values->sundaySchoolNameFontSize,
                    "props", $values->labelfontsize, "../Images/background/".$values->imageName, $title_red, $title_gren, $title_blue,
                    $back_red, $back_gren, $back_blue, $values->imagePosition);
            }


            // Render and return pdf content as string
            $file = SystemURLs::getDocumentRoot().'/tmp_attach/doc.pdf';            
            
            $content = $pdf->output($file, 'F');

            ob_flush();
            ob_end_clean();            
        
            $image = new \Imagick();
            if ($values->labeltype == 'Tractor') {
                $image->setResolution(100,100);
            } else {
                $image->setResolution(144,144);
            }
            $image->readImage($file.'[0]');
            $image->setImageFormat( "jpg" );
            $image->writeImage(SystemURLs::getDocumentRoot().'/tmp_attach/thumb.jpg'); 

            $imageData = base64_encode($image->getImageBlob());

            $image->clear(); 
            $image->destroy();

            unlink ($file);
            unlink (SystemURLs::getDocumentRoot().'/tmp_attach/thumb.jpg');

            return $response->withJson(['success' => true, "imgData" => "data:image/jpg;base64,".$imageData]);
        }

        return $response->withJson(['success' => false]);
    }
}

