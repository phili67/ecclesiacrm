<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\CartController;

$app->group('/cart', function (RouteCollectorProxy $group) {

    /*
     * @! Get all people in Cart
     */
    $group->get('/', CartController::class . ':getAllPeopleInCart' );
    /*
     * @! cart operations
     * #! param: ref->array :: Persons arrray of ids (possible value)
     * #! param: id->int :: Family (ID) of the person (possible value)
     * #! param: id->array :: Families (array of ids) (possible value)
     * #! param: id->int :: Group id (possible value)
     * #! param: id->int :: removeFamily id (possible value)
     * #! param: id->array :: removeFamilies (array of ids) (possible value)
     * #! param: id->int :: studentGroup id
     * #! param: id->int :: teacherGroup id
     */
    $group->post('/', CartController::class . ':cartOperation' );
    /*
     * @! Get user info by id
     * #! param: ref->array :: Persons id in array ref (possible value)
     */
    $group->post('/interectPerson', CartController::class . ':cartIntersectPersons' );
    /*
     * @! Empty cart to a group
     * #! param: ref->int :: groupID
     * #! param: ref->int :: groupRoleID
     */
    $group->post('/emptyToGroup', CartController::class . ':emptyCartToGroup' );
    /*
     * @! Empty cart to event
     * #! param: ref->int :: eventID
     */
    $group->post('/emptyToEvent', CartController::class . ':emptyCartToEvent' );
    /*
     * @! Empty cart to a new group
     * #! param: ref->string :: groupName
     */
    $group->post('/emptyToNewGroup', CartController::class . ':emptyCartToNewGroup' );
    /*
     * @! Remove all group members Ids from the cart
     * #! param: ref->int :: Group (Id)
     */
    $group->post('/removeGroup', CartController::class . ':removeGroupFromCart' );
    /*
     * @! Remove all groups members Ids from the cart
     * #! param: ref->array :: Groups (array of group Id)
     */
    $group->post('/removeGroups', CartController::class . ':removeGroupsFromCart' );
    /*
     * @! Remove students by group Id from the cart
     * #! param: ref->int :: Group (group Id)
     */
    $group->post('/removeStudentGroup', CartController::class . ':removeStudentsGroupFromCart' );
    /*
     * @! Remove teachers by group Id from the cart
     * #! param: ref->int :: Group (group Id)
     */
    $group->post('/removeTeacherGroup', CartController::class . ':removeTeachersGroupFromCart' );
    /*
     * @! Add all students to cart
     */
    $group->post('/addAllStudents', CartController::class . ':addAllStudentsToCart' );
    /*
     * @! Add all teachers to cart
     */
    $group->post('/addAllTeachers', CartController::class . ':addAllTeachersToCart' );
    /*
     * @! Remove all students from the cart
     */
    $group->post('/removeAllStudents', CartController::class . ':removeAllStudentsFromCart' );
    /*
     * @! Remove all teachers from the cart
     */
    $group->post('/removeAllTeachers', CartController::class . ':removeAllTeachersFromCart' );
    /*
     * @! Remove persons from the cart
     * #! param: ref->array :: Persons (array of persons ids)
     */
    $group->post('/delete', CartController::class . ':deletePersonCart' );
    /*
     * @! De-activate persons from the cart
     * #! param: ref->array :: Persons (array of persons ids)
     */
    $group->post('/deactivate', CartController::class . ':deactivatePersonCart' );
    /*
     * @! Extract persons in the cart to vcard format
     */
    $group->get( '/addressbook/extract', CartController::class . ":addressBook" );
    /*
     * @! Remove all People in the Cart
     */
    $group->delete('/', CartController::class . ':removePersonCart' );

});


