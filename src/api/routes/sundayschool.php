<?php

/******************************************************************************
*
*  filename    : api/routes/sundayschool.php
*  last change : Copyright all right reserved 2018/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\dto\Cart;

// Routes sundayschool
$app->group('/sundayschool', function (RouteCollectorProxy $group) {

    $group->post('/getallstudents/{groupId:[0-9]+}', 'getallstudentsForGroup' );
    $group->post('/getAllGendersForDonut/{groupId:[0-9]+}', 'getAllGendersForDonut' );
    $group->post('/getAllStudentsForChart/{groupId:[0-9]+}', 'getAllStudentsForChart' );

});


function getallstudentsForGroup (Request $request, Response $response, array $args) {
  $sundaySchoolService = new SundaySchoolService();

  $thisClassChildren = $sundaySchoolService->getKidsFullDetails($args['groupId']);

  $result = [];
  foreach ($thisClassChildren as $children) {
    if (Cart::PersonInCart($children['kidId'])) {
      $children['inCart']=1;
    } else {
      $children['inCart']=0;
    }

    $result[] = $children;
  }

    return $response->write('{"ClassroomStudents":'.json_encode($result)."}");
}

function getAllGendersForDonut (Request $request, Response $response, array $args) {
  $sundaySchoolService = new SundaySchoolService();

  $genderChartArray = [];
  foreach ($sundaySchoolService->getKidsGender($args['groupId']) as $gender => $kidsCount) {
      array_push($genderChartArray, ["label" => gettext($gender), "data" => $kidsCount]);
  }
  return $response->withJson($genderChartArray);
}

function getAllStudentsForChart (Request $request, Response $response, array $args) {
  $sundaySchoolService = new SundaySchoolService();

  $birthDayMonthChartArray = [];
  foreach ($sundaySchoolService->getKidsBirthdayMonth($args['groupId']) as $birthDayMonth => $kidsCount) {
      $res[0] = gettext($birthDayMonth);
      $res[1] = $kidsCount;

      $birthDayMonthChartArray[] = $res;
  }

  return  $response->withJson($birthDayMonthChartArray);
}
