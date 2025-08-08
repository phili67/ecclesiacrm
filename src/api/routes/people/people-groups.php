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
      * #! param: id->int :: groupId
      */
    $group->get('/addressbook/extract/{groupId:[0-9]+}', PeopleGroupController::class . ":addressBook" );


    /*
      * @! search informations in the group
      * #! param: id->string :: query
      */
    $group->get('/search/{query}', PeopleGroupController::class . ":searchGroup" );

    /*
     * @! delete all managers of a groupId
     * #! param: id->int :: groupID
     */
    $group->post('/deleteAllManagers', PeopleGroupController::class . ":deleteAllManagers" );
    /*
     * @! delete a manager (personID) of a group (groupId)
     * #! param: id->int :: personID
     * #! param: id->int :: groupID
     */
    $group->post('/deleteManager', PeopleGroupController::class . ":deleteManager" );
    /*
     * @! get group managers of a group (groupId)
     * #! param: id->int :: personID
     */
    $group->post('/getmanagers', PeopleGroupController::class . ":getManagers" );
    /*
     * @! get group managers of a group (groupId)
     * #! param: id->int :: personID
     * #! param: id->int :: groupID
     */
    $group->post('/addManager', PeopleGroupController::class . ":addManager" );

    /*
     * @! get group managers of a group (groupId)
     * #! param: id->int :: personID
     * #! param: id->int :: groupID
     */
    $group->get('/groupsInCart', PeopleGroupController::class . ":groupsInCart" );

    /*
     * @! create a new group
     * #! param: id->int :: isSundaySchool
     * #! param: id->string :: groupName
     */
    $group->post('/', PeopleGroupController::class . ":newGroup" );
    /*
     * @! create a new group
     * #! param: id->int :: groupID
     * #! param: id->int :: isSundaySchool
     * #! param: id->int :: groupType
     * #! param: id->string :: description
     */
    $group->post('/{groupID:[0-9]+}', PeopleGroupController::class . ":updateGroup" );
    /*
     * @! group info
     * #! param: id->int :: groupID
     */
    $group->get('/{groupID:[0-9]+}', PeopleGroupController::class . ":groupInfo" );
    /*
     * @! get group cart status
     * #! param: id->int :: groupID
     */
    $group->get('/{groupID:[0-9]+}/cartStatus', PeopleGroupController::class . ":groupCartStatus" );
    /*
     * @! delete a group
     * #! param: id->int :: groupID
     */
    $group->delete('/{groupID:[0-9]+}', PeopleGroupController::class . ":deleteGroup" );
    /*
     * @! get all group members
     * #! param: id->int :: groupID
     */
    $group->get('/{groupID:[0-9]+}/members', PeopleGroupController::class . ":groupMembers" );

    /*
     * @! get all group members
     * #! param: id->int :: groupID
     */
    $group->get('/{groupID:[0-9]+}/events', PeopleGroupController::class . ":groupEvents" );


    /*
     * @! remove one person from the group
     * #! param: id->int :: groupID
     * #! param: ref->int :: personID
     */
    $group->delete('/{groupID:[0-9]+}/removeperson/{personID:[0-9]+}', PeopleGroupController::class . ":removePersonFromGroup" );

    /*
     * @! remove all selected members of the group
     * #! param: id->int :: groupID
     * #! param: ref->array :: Persons id in array ref (possible value)
     */
    $group->delete('/removeselectedpersons', PeopleGroupController::class . ":removeSelectedPersons" );

    /*
     * @! add a member of the group
     * #! param: id->int :: groupID
     * #! param: ref->int :: person id 
     */
    $group->post('/{groupID:[0-9]+}/addperson/{personID:[0-9]+}', PeopleGroupController::class . ":addPersonToGroup" );
    
    /*
     * @! add a add teacher of the group
     * #! param: id->int :: groupID
     * #! param: ref->int :: person id 
     */
    $group->post('/{groupID:[0-9]+}/addteacher/{personID:[0-9]+}', PeopleGroupController::class . ":addTeacherToGroup" );



    /*
     * @! set person role in the group
     * #! param: id->int :: groupID
     * #! param: ref->int :: person id 
     */
    $group->post('/{groupID:[0-9]+}/userRole/{personID:[0-9]+}', PeopleGroupController::class . ":userRoleByPersonId" );

     /*
     * @! set role id in the group
     * #! param: id->int :: groupID
     * #! param: ref->int :: role id 
     */
    $group->post('/{groupID:[0-9]+}/roles/{roleID:[0-9]+}', PeopleGroupController::class . ":rolesByRoleId" );
    
    /*
     * @! get all role the group
     * #! param: id->int :: groupID
     */
    $group->get('/{groupID:[0-9]+}/roles', PeopleGroupController::class . ":allRoles" );

    /*
     * @! get default role in the group
     * #! param: id->int :: groupID
     */
    $group->post('/{groupID:[0-9]+}/defaultRole', PeopleGroupController::class . ":defaultRoleForGroup" );


     /*
     * @! delete role id in the group
     * #! param: id->int :: groupID
     * #! param: ref->int :: role id 
     */
    $group->delete('/{groupID:[0-9]+}/roles/{roleID:[0-9]+}', PeopleGroupController::class . ":deleteRole" );

    /*
     * @! add group role name
     * #! param: id->int :: groupID
     * #! param: ref->string :: roleName
     */
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

    $group->post( '/emptygroup', PeopleGroupController::class . ":emptygroup" );

    /*
     * @! get all sundayschool teachers
     * #! param: id->string  :: title as string (color)
     * #! param: id->string  :: titlePosition (Right | Left | Center)
     * #! param: id->int     :: titleFontSize (default 8)
     * #! param: id->string  :: back as string (color)
     * #! param: id->string  :: sundaySchoolName as string
     * #! param: id->string  :: sundaySchoolNamePosition (Right | Left | Center)
     * #! param: id->int     :: sundaySchoolNameFontSize (default 15)
     * #! param: id->string  :: labelfont as string
     * #! param: id->string  :: labeltype as string
     * #! param: id->int     :: labelfontsize as int
     * #! param: id->boolean :: useQRCode as int
     * #! param: id->int     :: groupID as int
     * #! param: id->string  :: imageName as int
     * #! param: id->string  :: imagePosition as (Right | Left | Center)
     */
    $group->post( '/render/sundayschool/badge', PeopleGroupController::class . ":renderBadge" );
});
