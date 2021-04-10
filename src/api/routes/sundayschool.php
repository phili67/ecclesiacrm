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

    $group->post('/getallstudents/{groupId:[0-9]+}', SundaySchoolController::class . ':getallstudentsForGroup' );
    $group->post('/getAllGendersForDonut/{groupId:[0-9]+}', SundaySchoolController::class . ':getAllGendersForDonut' );
    $group->post('/getAllStudentsForChart/{groupId:[0-9]+}', SundaySchoolController::class . ':getAllStudentsForChart' );

});





