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

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\dto\Cart;

// Routes sundayschool
$app->group('/sundayschool', function (RouteCollectorProxy $group) {

    $group->post('/getallstudents/{groupId:[0-9]+}', SundaySchoolController::class . ':getallstudentsForGroup' );
    $group->post('/getAllGendersForDonut/{groupId:[0-9]+}', SundaySchoolController::class . ':getAllGendersForDonut' );
    $group->post('/getAllStudentsForChart/{groupId:[0-9]+}', SundaySchoolController::class . ':getAllStudentsForChart' );

});

class SundaySchoolController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getallstudentsForGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $sundaySchoolService = $this->container->get('SundaySchoolService');

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

    public function getAllGendersForDonut (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $sundaySchoolService = $this->container->get('SundaySchoolService');

        $genderChartArray = [];
        foreach ($sundaySchoolService->getKidsGender($args['groupId']) as $gender => $kidsCount) {
            array_push($genderChartArray, ["label" => gettext($gender), "data" => $kidsCount]);
        }
        return $response->withJson($genderChartArray);
    }

    public function getAllStudentsForChart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $sundaySchoolService = $this->container->get('SundaySchoolService');

        $birthDayMonthChartArray = [];
        foreach ($sundaySchoolService->getKidsBirthdayMonth($args['groupId']) as $birthDayMonth => $kidsCount) {
            $res[0] = gettext($birthDayMonth);
            $res[1] = $kidsCount;

            $birthDayMonthChartArray[] = $res;
        }

        return  $response->withJson($birthDayMonthChartArray);
    }
}



