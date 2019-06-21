<?php

/*******************************************************************************
 *
 *  filename    : route : sundayschool.php
 *  last change : 2019-06-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2019 Philippe Logel
 *
 ******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Service\DashboardService;
use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\SessionUser;
use Slim\Views\PhpRenderer;


$app->group('/sundayschool', function () {
    $this->get('', 'sundayschoolDashboard' );
    $this->get('/', 'sundayschoolDashboard' );
    $this->get('/dashboard', 'sundayschoolDashboard' );
    $this->get('/{groupId:[0-9]+}/view', 'sundayschoolView' );
});

function sundayschoolDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sundayschool/');

    if ( !( SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    return $renderer->render($response, 'sundayschooldashboard.php', argumentsSundayschoolDashboardArray());
}

function argumentsSundayschoolDashboardArray ()
{
    $dashboardService = new DashboardService();
    $sundaySchoolService = new SundaySchoolService();

    $groupStats = $dashboardService->getGroupStats();

    $kidsWithoutClasses = $sundaySchoolService->getKidsWithoutClasses();
    $classStats         = $sundaySchoolService->getClassStats();
    $classes            = $groupStats['sundaySchoolClasses'];
    $teachers           = 0;
    $kids               = 0;
    $families           = 0;
    $maleKids           = 0;
    $femaleKids         = 0;
    $familyIds          = [];

    foreach ($classStats as $class) {
        $kids = $kids + $class['kids'];
        $teachers = $teachers + $class['teachers'];
        $classKids = $sundaySchoolService->getKidsFullDetails($class['id']);
        foreach ($classKids as $kid) {
            array_push($familyIds, $kid['fam_id']);
            if ($kid['kidGender'] == '1') {
                $maleKids++;
            } elseif ($kid['kidGender'] == '2') {
                $femaleKids++;
            }
        }
    }

    //Set the page title
    $sPageTitle    = _('Sunday School Dashboard');

    $sRootDocument = SystemURLs::getDocumentRoot();
    $CSPNonce = SystemURLs::getCSPNonce();

    $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
        'sRootDocument'             => $sRootDocument,
        'CSPNonce'                  => $CSPNonce,
        'sPageTitle'                => $sPageTitle,
        'classes'                   => $classes,
        'classStats'                => $classStats,
        'kidsWithoutClasses'        => $kidsWithoutClasses,
        'familyIds'                 => $familyIds,
        'maleKids'                  => $maleKids,
        'femaleKids'                => $femaleKids,
        'teachers'                  => $teachers,
        'kids'                      => $kids,
        'groupStats'                => $groupStats,
        'femaleKids'                => $femaleKids,
        'familyIds'                 => $familyIds,
        'classKids'                 => $classKids,
        'isVolunteerOpportunityEnabled' => SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()
    ];

    return $paramsArguments;
}

function sundayschoolView (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/sundayschool/');
    
    if ( !( SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    $groupId = $args['groupId'];
    
    return $renderer->render($response, 'sundayschoolview.php', argumentsSundayschoolViewArray($groupId));
}

function argumentsSundayschoolViewArray ($iGroupId)
{
    $sundaySchoolService = new SundaySchoolService();

    $iGroupName = GroupQuery::Create()
        ->findOneById ($iGroupId)
        ->getName();

    $birthDayMonthChartArray = [];
    foreach ($sundaySchoolService->getKidsBirthdayMonth($iGroupId) as $birthDayMonth => $kidsCount) {
        array_push($birthDayMonthChartArray, "['"._($birthDayMonth)."', ".$kidsCount.' ]');
    }
    $birthDayMonthChartJSON = implode(',', $birthDayMonthChartArray);

    $genderChartArray = [];
    foreach ($sundaySchoolService->getKidsGender($iGroupId) as $gender => $kidsCount) {
        array_push($genderChartArray, "{label: '"._($gender)."', data: ".$kidsCount.'}');
    }
    $genderChartJSON = implode(',', $genderChartArray);

    $rsTeachers = $sundaySchoolService->getClassByRole($iGroupId, 'Teacher');

    //Set the page title
    $sPageTitle = _('Sunday School').': '.$iGroupName;
    $sRootDocument  = SystemURLs::getDocumentRoot();

    $CSPNonce = SystemURLs::getCSPNonce();

    $paramsArguments = ['sRootPath'                     => SystemURLs::getRootPath(),
                        'sRootDocument'                 => $sRootDocument,
                        'CSPNonce'                      => $CSPNonce,
                        'sPageTitle'                    => $sPageTitle,
                        'iGroupId'                      => $iGroupId,
                        'rsTeachers'                    => $rsTeachers,
                        'genderChartJSON'               => $genderChartJSON,
                        'birthDayMonthChartJSON'        => $birthDayMonthChartJSON
                       ];

   return $paramsArguments;
}