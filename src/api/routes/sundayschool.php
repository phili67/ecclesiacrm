<?php

/******************************************************************************
*
*  filename    : api/routes/sundayschool.php
*  last change : Copyright all right reserved 2018/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address, 
*                 groups, families, etc...
*
******************************************************************************/

use EcclesiaCRM\Service\SundaySchoolService;


// Routes sharedocument

$app->group('/sundayschool', function () {
  
  $this->post('/getallstudents/{groupId:[0-9]+}',function($request,$response,$args) {
    $sundaySchoolService = new SundaySchoolService();
    
    $thisClassChildren = $sundaySchoolService->getKidsFullDetails($args['groupId']);
    
    echo "{\"ClassroomStudents\":".json_encode($thisClassChildren)."}";
  });
  
  $this->post('/getAllGendersForDonut/{groupId:[0-9]+}',function($request,$response,$args) {
    $sundaySchoolService = new SundaySchoolService();
    
    $genderChartArray = [];
    foreach ($sundaySchoolService->getKidsGender($args['groupId']) as $gender => $kidsCount) {
        array_push($genderChartArray, ["label" => gettext($gender), "data" => $kidsCount]);
    }
    return $response->withJson($genderChartArray);
  });

  $this->post('/getAllStudentsForChart/{groupId:[0-9]+}',function($request,$response,$args) {
    $sundaySchoolService = new SundaySchoolService();
    
    $birthDayMonthChartArray = [];
		foreach ($sundaySchoolService->getKidsBirthdayMonth($args['groupId']) as $birthDayMonth => $kidsCount) {
		    $res[0] = gettext($birthDayMonth);
		    $res[1] = $kidsCount;
		    
				$birthDayMonthChartArray[] = $res;
		}
    
    return  $response->withJson($birthDayMonthChartArray);
    
    /*$birthDayMonthChartArray = "";
		/*foreach ($sundaySchoolService->getKidsBirthdayMonth($args['groupId']) as $birthDayMonth => $kidsCount) {
				$birthDayMonthChartArray .= "['".gettext($birthDayMonth)."',".$kidsCount."].";
		}*/

    //return  $response->withJson(["cocuou"=>"toto"]);
  });
});
