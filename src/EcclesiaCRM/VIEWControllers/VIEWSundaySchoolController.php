<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\Service\DashboardItemService;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\SessionUser;
use Slim\Views\PhpRenderer;

class VIEWSundaySchoolController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sundayschoolDashboard (ServerRequest $request, Response $response, array $args): Response
    {
        $renderer = new PhpRenderer('templates/sundayschool/');

        if ( !( SystemConfig::getBooleanValue("bEnabledSundaySchool") ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'sundayschooldashboard.php', $this->argumentsSundayschoolDashboardArray());
    }

    public function argumentsSundayschoolDashboardArray ()
    {
        $sundaySchoolService = new SundaySchoolService();
        $dsiS = new DashboardItemService();

        $kidsWithoutClasses = $sundaySchoolService->getKidsWithoutClasses();
        $classStats         = $sundaySchoolService->getClassStats();

        //Set the page title
        $sPageTitle    = _('Sunday School Dashboard');

        $sRootDocument = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'classStats'                => $classStats,
            'kidsWithoutClasses'        => $kidsWithoutClasses,
            'sundaySchoolCountStats'    => $dsiS->getAllItems()['sundaySchoolCountStats'],
            'isVolunteerOpportunityEnabled' => SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()
        ];

        return $paramsArguments;
    }

    public function sundayschoolView (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/sundayschool/');

        if ( !( SystemConfig::getBooleanValue("bEnabledSundaySchool") ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $groupId = $args['groupId'];

        return $renderer->render($response, 'sundayschoolview.php', $this->argumentsSundayschoolViewArray($groupId));
    }

    public function argumentsSundayschoolViewArray ($iGroupId)
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
            'iGroupName'                    => $iGroupName,
            'rsTeachers'                    => $rsTeachers,
            'genderChartJSON'               => $genderChartJSON,
            'birthDayMonthChartJSON'        => $birthDayMonthChartJSON
        ];

        return $paramsArguments;
    }

    public function sundayschoolReports (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/sundayschool/');

        if ( !( SystemConfig::getBooleanValue("bEnabledSundaySchool") && SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }


        return $renderer->render($response, 'sundayschoolreports.php', $this->argumentsSundayschoolReportsArray());
    }

    public function argumentsSundayschoolReportsArray ()
    {
        // Get all the sunday school classes
        $groups = GroupQuery::create()
            ->orderByName(Criteria::ASC)
            ->filterByType(4)// only sunday groups
            ->find();


        if ( isset($_POST['SubmitPhotoBook']) || isset($_POST['SubmitClassList']) || isset($_POST['SubmitClassAttendance']) || isset($_POST['SubmitRealClassAttendance']) ) {
            $iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');

            $dFirstSunday = InputUtils::LegacyFilterInput($_POST['FirstSunday'], 'date');
            $dLastSunday  = InputUtils::LegacyFilterInput($_POST['LastSunday'], 'date');
            $dNoSchool1   = InputUtils::LegacyFilterInput($_POST['NoSchool1'], 'date');
            $dNoSchool2   = InputUtils::LegacyFilterInput($_POST['NoSchool2'], 'date');
            $dNoSchool3   = InputUtils::LegacyFilterInput($_POST['NoSchool3'], 'date');
            $dNoSchool4     = InputUtils::LegacyFilterInput($_POST['NoSchool4'], 'date');
            $dNoSchool5     = InputUtils::LegacyFilterInput($_POST['NoSchool5'], 'date');
            $dNoSchool6     = InputUtils::LegacyFilterInput($_POST['NoSchool6'], 'date');
            $dNoSchool7     = InputUtils::LegacyFilterInput($_POST['NoSchool7'], 'date');
            $dNoSchool8     = InputUtils::LegacyFilterInput($_POST['NoSchool8'], 'date');
            $iExtraStudents = InputUtils::LegacyFilterInput($_POST['ExtraStudents'], 'int');
            $iExtraTeachers = InputUtils::LegacyFilterInput($_POST['ExtraTeachers'], 'int');
            $_SESSION['idefaultFY'] = $iFYID;

            $bAtLeastOneGroup = false;

            if (!empty($_POST['GroupID'])) {
                $count = 0;
                foreach ($_POST['GroupID'] as $Grp) {
                    $aGroups[$count++] = InputUtils::LegacyFilterInput($Grp, 'int');
                }
                $aGrpID = implode(',', $aGroups);
                $bAtLeastOneGroup = true;
            }

            $allroles = InputUtils::LegacyFilterInput($_POST['allroles']);
            $withPictures = InputUtils::LegacyFilterInput($_POST['withPictures']);

            $currentUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
            $currentUser->setCalStart($dFirstSunday);
            $currentUser->setCalEnd($dLastSunday);
            $currentUser->setCalNoSchool1($dNoSchool1);
            $currentUser->setCalNoSchool2($dNoSchool2);
            $currentUser->setCalNoSchool3($dNoSchool3);
            $currentUser->setCalNoSchool4($dNoSchool4);
            $currentUser->setCalNoSchool5($dNoSchool5);
            $currentUser->setCalNoSchool6($dNoSchool6);
            $currentUser->setCalNoSchool7($dNoSchool7);
            $currentUser->setCalNoSchool7($dNoSchool8);
            $currentUser->save();

            if ($bAtLeastOneGroup && isset($_POST['SubmitPhotoBook']) && $aGrpID != 0) {
                RedirectUtils::Redirect('Reports/PhotoBook.php?GroupID='.$aGrpID.'&FYID='.$iFYID.'&FirstSunday='.$dFirstSunday.'&LastSunday='.$dLastSunday.'&AllRoles='.$allroles.'&pictures='.$withPictures);
            } elseif ($bAtLeastOneGroup && isset($_POST['SubmitClassList']) && $aGrpID != 0) {
                RedirectUtils::Redirect('Reports/ClassList.php?GroupID='.$aGrpID.'&FYID='.$iFYID.'&FirstSunday='.$dFirstSunday.'&LastSunday='.$dLastSunday.'&AllRoles='.$allroles.'&pictures='.$withPictures);
            } elseif ($bAtLeastOneGroup && isset($_POST['SubmitClassAttendance']) && $aGrpID != 0) {
                $toStr = 'Reports/ClassAttendance.php?';
                //        $toStr .= "GroupID=" . $iGroupID;
                $toStr .= 'GroupID='.$aGrpID;
                $toStr .= '&FYID='.$iFYID;
                $toStr .= '&FirstSunday='.$dFirstSunday;
                $toStr .= '&LastSunday='.$dLastSunday;
                $toStr .= '&AllRoles='.$allroles;
                $toStr .= '&withPictures='.$withPictures;
                if ($dNoSchool1) {
                    $toStr .= '&NoSchool1='.$dNoSchool1;
                }
                if ($dNoSchool2) {
                    $toStr .= '&NoSchool2='.$dNoSchool2;
                }
                if ($dNoSchool3) {
                    $toStr .= '&NoSchool3='.$dNoSchool3;
                }
                if ($dNoSchool4) {
                    $toStr .= '&NoSchool4='.$dNoSchool4;
                }
                if ($dNoSchool5) {
                    $toStr .= '&NoSchool5='.$dNoSchool5;
                }
                if ($dNoSchool6) {
                    $toStr .= '&NoSchool6='.$dNoSchool6;
                }
                if ($dNoSchool7) {
                    $toStr .= '&NoSchool7='.$dNoSchool7;
                }
                if ($dNoSchool8) {
                    $toStr .= '&NoSchool8='.$dNoSchool8;
                }
                if ($iExtraStudents) {
                    $toStr .= '&ExtraStudents='.$iExtraStudents;
                }
                if ($iExtraTeachers) {
                    $toStr .= '&ExtraTeachers='.$iExtraTeachers;
                }
                RedirectUtils::Redirect($toStr);
            } elseif ($bAtLeastOneGroup && isset($_POST['SubmitRealClassAttendance']) && $aGrpID != 0) {
                $toStr = 'Reports/ClassRealAttendance.php?';
                //        $toStr .= "GroupID=" . $iGroupID;
                $toStr .= 'groupID='.$aGrpID;
                $toStr .= '&idefaultFY='.$iFYID;
                $toStr .= '&start='.$year."-01-01";
                $toStr .= '&end='.$year."-12-31";
                $toStr .= '&withPictures='.$withPictures;
                $toStr .= '&ExtraStudents='.($iExtraStudents+$iExtraTeachers);

                RedirectUtils::Redirect($toStr);
            } elseif (!$bAtLeastOneGroup || $aGrpID == 0) {
                $message = '<p class="alert alert-danger"><span class="fas fa-exclamation-triangle">' ._('At least one group must be selected to make class lists or attendance sheets.') . '</span></p>';
            }
        } else {
            $iFYID = $_SESSION['idefaultFY'];
            $iGroupID = 0;
            $currentUser = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());

            if ($currentUser->getCalStart() != null) {
                $dFirstSunday = $currentUser->getCalStart()->format('Y-m-d');
            }
            if ($currentUser->getCalEnd() != null) {
                $dLastSunday = $currentUser->getCalEnd()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool1() != null) {
                $dNoSchool1 = $currentUser->getCalNoSchool1()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool2() != null) {
                $dNoSchool2 = $currentUser->getCalNoSchool2()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool3() != null) {
                $dNoSchool3 = $currentUser->getCalNoSchool3()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool4() != null) {
                $dNoSchool4 = $currentUser->getCalNoSchool4()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool5() != null) {
                $dNoSchool5 = $currentUser->getCalNoSchool5()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool6() != null) {
                $dNoSchool6 = $currentUser->getCalNoSchool6()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool7() != null) {
                $dNoSchool7 = $currentUser->getCalNoSchool7()->format('Y-m-d');
            }
            if ($currentUser->getCalNoSchool8() != null) {
                $dNoSchool8 = $currentUser->getCalNoSchool8()->format('Y-m-d');
            }

            $iExtraStudents = 0;
            $iExtraTeachers = 0;
        }

        $dFirstSunday = OutputUtils::change_date_for_place_holder($dFirstSunday);
        $dLastSunday  = OutputUtils::change_date_for_place_holder($dLastSunday);
        $dNoSchool1   = OutputUtils::change_date_for_place_holder($dNoSchool1);
        $dNoSchool2   = OutputUtils::change_date_for_place_holder($dNoSchool2);
        $dNoSchool3   = OutputUtils::change_date_for_place_holder($dNoSchool3);
        $dNoSchool4   = OutputUtils::change_date_for_place_holder($dNoSchool4);
        $dNoSchool5   = OutputUtils::change_date_for_place_holder($dNoSchool5);
        $dNoSchool6   = OutputUtils::change_date_for_place_holder($dNoSchool6);
        $dNoSchool7   = OutputUtils::change_date_for_place_holder($dNoSchool7);
        $dNoSchool8   = OutputUtils::change_date_for_place_holder($dNoSchool6);

        //Set the page title
        $sPageTitle = _('Sunday School Reports');
        $sRootDocument  = SystemURLs::getDocumentRoot();

        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'     => SystemURLs::getRootPath(),
            'sRootDocument'                 => $sRootDocument,
            'CSPNonce'                      => $CSPNonce,
            'sPageTitle'                    => $sPageTitle,
            'groups'                        => $groups,
            'iExtraStudents'                => $iExtraStudents,
            'iExtraTeachers'                => $iExtraTeachers,
            'iGroupID'                      => $iGroupID,
            'iFYID'                         => $iFYID,
            'message'                       => $message,
            'dFirstSunday'                  => $dFirstSunday,
            'dLastSunday'                   => $dLastSunday,
            'dNoSchool1'                    => $dNoSchool1,
            'dNoSchool2'                    => $dNoSchool2,
            'dNoSchool3'                    => $dNoSchool3,
            'dNoSchool4'                    => $dNoSchool4,
            'dNoSchool5'                    => $dNoSchool5,
            'dNoSchool6'                    => $dNoSchool6,
            'dNoSchool7'                    => $dNoSchool7,
            'dNoSchool8'                    => $dNoSchool8
        ];

        return $paramsArguments;
    }
}
