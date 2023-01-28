<?php

/******************************************************************************
*
*  filename    : api/routes/sundayschool.php
*  last change : Copyright all right reserved 2018/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SundaySchoolController;

// Routes sundayschool
$app->group('/sundayschool', function (RouteCollectorProxy $group) {

    /*
    * @! Get all students for Group ID
    * #! param: ref->int :: groupId
    */
    $group->post('/getallstudents/{groupId:[0-9]+}', SundaySchoolController::class . ':getallstudentsForGroup' );
    /*
    * @! Get all genders for Group ID (to draw the donut)
    * #! param: ref->int :: groupId
    */
    $group->post('/getAllGendersForDonut/{groupId:[0-9]+}', SundaySchoolController::class . ':getAllGendersForDonut' );
    /*
    * @! Get all students for Group ID (to draw the chart)
    * #! param: ref->int :: groupId
    */
    $group->post('/getAllStudentsForChart/{groupId:[0-9]+}', SundaySchoolController::class . ':getAllStudentsForChart' );

});





