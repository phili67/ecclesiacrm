<?php
// Routes
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\PeopleGroupController;

$app->group('/groups', function (RouteCollectorProxy $group) {
 /*
 * @! Get all the Groups
 */
    $group->get('/', PeopleGroupController::class . ":getAllGroups" );

 /*
 * @! Get the first Group of the list
 */
    $group->get('/defaultGroup' , PeopleGroupController::class . ":defaultGroup");

 /*
 * @! Get all the properties of a group
 */
    $group->post('/groupproperties/{groupID:[0-9]+}', PeopleGroupController::class . ":groupproperties" );

 /*
 * @! get addressbook from a groupID through the url
 */
    $group->get('/addressbook/extract/{groupId:[0-9]+}', PeopleGroupController::class . ":addressBook" );

    $group->get('/search/{query}', PeopleGroupController::class . ":searchGroup" );

    $group->post('/deleteAllManagers', PeopleGroupController::class . ":deleteAllManagers" );
    $group->post('/deleteManager', PeopleGroupController::class . ":deleteManager" );
    $group->post('/getmanagers', PeopleGroupController::class . ":getManagers" );
    $group->post('/addManager', PeopleGroupController::class . ":addManager" );

    $group->get('/groupsInCart', PeopleGroupController::class . ":groupsInCart" );

    $group->post('/', PeopleGroupController::class . ":newGroup" );
    $group->post('/{groupID:[0-9]+}', PeopleGroupController::class . ":updateGroup" );
    $group->get('/{groupID:[0-9]+}', PeopleGroupController::class . ":groupInfo" );
    $group->get('/{groupID:[0-9]+}/cartStatus', PeopleGroupController::class . ":groupCartStatus" );
    $group->delete('/{groupID:[0-9]+}', PeopleGroupController::class . ":deleteGroup" );
    $group->get('/{groupID:[0-9]+}/members', PeopleGroupController::class . ":groupMembers" );

    $group->get('/{groupID:[0-9]+}/events', PeopleGroupController::class . ":groupEvents" );

    $group->delete('/{groupID:[0-9]+}/removeperson/{userID:[0-9]+}', PeopleGroupController::class . ":removePersonFromGroup" );
    $group->post('/{groupID:[0-9]+}/addperson/{userID:[0-9]+}', PeopleGroupController::class . ":addPersonToGroup" );
    $group->post('/{groupID:[0-9]+}/addteacher/{userID:[0-9]+}', PeopleGroupController::class . ":addTeacherToGroup" );

    $group->post('/{groupID:[0-9]+}/userRole/{userID:[0-9]+}', PeopleGroupController::class . ":userRoleByUserId" );
    $group->post('/{groupID:[0-9]+}/roles/{roleID:[0-9]+}', PeopleGroupController::class . ":rolesByRoleId" );
    $group->get('/{groupID:[0-9]+}/roles', PeopleGroupController::class . ":allRoles" );


    $group->post('/{groupID:[0-9]+}/defaultRole', PeopleGroupController::class . ":defaultRoleForGroup" );

    $group->delete('/{groupID:[0-9]+}/roles/{roleID:[0-9]+}', PeopleGroupController::class . ":deleteRole" );
    $group->post('/{groupID:[0-9]+}/roles', PeopleGroupController::class . ":roles" );
    $group->post('/{groupID:[0-9]+}/setGroupSpecificPropertyStatus', PeopleGroupController::class . ":setGroupSepecificPropertyStatus" );
    $group->post('/{groupID:[0-9]+}/settings/active/{value}', PeopleGroupController::class . ":settingsActiveValue" );
    $group->post('/{groupID:[0-9]+}/settings/email/export/{value}', PeopleGroupController::class . ":settingsEmailExportVvalue" );

 /*
 * @! delete Group Specific property custom field
 * #! param: id->int :: PropID as id
 * #! param: id->int :: Field as id
 * #! param: id->int :: GroupId as id
 */
    $group->post('/deletefield', PeopleGroupController::class . ":deleteGroupField" );
 /*
 * @! delete Group Specific property custom field
 * #! param: id->int :: PropID as id
 * #! param: id->int :: Field as id
 * #! param: id->int :: GroupId as id
 */
    $group->post('/upactionfield', PeopleGroupController::class . ":upactionGroupField" );
 /*
 * @! delete Group Specific property custom field
 * #! param: id->int :: PropID as id
 * #! param: id->int :: Field as id
 * #! param: id->int :: GroupId as id
 */
    $group->post('/downactionfield', PeopleGroupController::class . ":downactionGroupField" );

    /*
     * @! get all sundayschool teachers
     * #! param: id->int :: groupID as id
     */

    $group->get('/{groupID:[0-9]+}/sundayschool', PeopleGroupController::class . ":groupSundaySchool" );
});
