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

use EcclesiaCRM\dto\Cart;

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
