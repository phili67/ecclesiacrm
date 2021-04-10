<?php

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\CartController;

$app->group('/cart', function (RouteCollectorProxy $group) {

/*
 * @! Get all people in Cart
 */
    $group->get('/', CartController::class . ':getAllPeopleInCart' );
/*
 * @! Get user info by id
 * #! param: ref->array :: Persons id in array ref
 * #! param: id->int :: Family id
 * #! param: id->int :: Group id
 * #! param: id->int :: removeFamily id
 * #! param: id->int :: studentGroup id
 * #! param: id->int :: teacherGroup id
 */
    $group->post('/', CartController::class . ':cartOperation' );
    $group->post('/interectPerson', CartController::class . ':cartIntersectPersons' );
    $group->post('/emptyToGroup', CartController::class . ':emptyCartToGroup' );
    $group->post('/emptyToEvent', CartController::class . ':emptyCartToEvent' );
    $group->post('/emptyToNewGroup', CartController::class . ':emptyCartToNewGroup' );
    $group->post('/removeGroup', CartController::class . ':removeGroupFromCart' );
    $group->post('/removeGroups', CartController::class . ':removeGroupsFromCart' );
    $group->post('/removeStudentGroup', CartController::class . ':removeStudentsGroupFromCart' );
    $group->post('/removeTeacherGroup', CartController::class . ':removeTeachersGroupFromCart' );
    $group->post('/addAllStudents', CartController::class . ':addAllStudentsToCart' );
    $group->post('/addAllTeachers', CartController::class . ':addAllTeachersToCart' );
    $group->post('/removeAllStudents', CartController::class . ':removeAllStudentsFromCart' );
    $group->post('/removeAllTeachers', CartController::class . ':removeAllTeachersFromCart' );
    $group->post('/delete', CartController::class . ':deletePersonCart' );
    $group->post('/deactivate', CartController::class . ':deactivatePersonCart' );

/*
 * @! Remove all People in the Cart
 */
    $group->delete('/', CartController::class . ':removePersonCart' );

});


