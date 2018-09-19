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
    
    
    foreach ($thisClassChildren as $child) {
    }

    echo "{\"ClassroomStudents\":".json_encode($thisClassChildren)."}";
  });
});
