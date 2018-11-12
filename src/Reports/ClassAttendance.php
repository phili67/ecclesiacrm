<?php
/*******************************************************************************
*
*  filename    : Reports/ClassAttendance.php
*  last change : 2013-02-22
*  description : Creates a PDF for a Sunday School Class Attendance List
*  Udpdated    : 2017-10-23
*                Philippe Logel
******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';
require '../Include/ReportFunctions.php';

use EcclesiaCRM\Reports\PDF_Attendance;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2r;
use EcclesiaCRM\Map\PersonTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\PropertyQuery;

$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID']);
$aGrp     = explode(',', $iGroupID);
$nGrps    = count($aGrp);
//echo $iGroupID;

$iFYID = InputUtils::LegacyFilterInput($_GET['FYID'], 'int');

$tFirstSunday = InputUtils::LegacyFilterInput($_GET['FirstSunday']);
$tLastSunday  = InputUtils::LegacyFilterInput($_GET['LastSunday']);
$tAllRoles    = InputUtils::LegacyFilterInput($_GET['AllRoles'], 'int');
$withPictures = InputUtils::LegacyFilterInput($_GET['withPictures'], 'int');

//echo "all roles ={$tAllRoles}";

$tNoSchool1 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool1');
$tNoSchool2 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool2');
$tNoSchool3 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool3');
$tNoSchool4 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool4');
$tNoSchool5 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool5');
$tNoSchool6 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool6');
$tNoSchool7 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool7');
$tNoSchool8 = InputUtils::LegacyFilterInputArr($_GET, 'NoSchool8');

$iExtraStudents = InputUtils::LegacyFilterInputArr($_GET, 'ExtraStudents', 'int');
$iExtraTeachers = InputUtils::LegacyFilterInputArr($_GET, 'ExtraTeachers', 'int');

$dFirstSunday = strtotime($tFirstSunday);
$dLastSunday  = strtotime($tLastSunday);

$dNoSchool1 = strtotime($tNoSchool1);
$dNoSchool2 = strtotime($tNoSchool2);
$dNoSchool3 = strtotime($tNoSchool3);
$dNoSchool4 = strtotime($tNoSchool4);
$dNoSchool5 = strtotime($tNoSchool5);
$dNoSchool6 = strtotime($tNoSchool6);
$dNoSchool7 = strtotime($tNoSchool7);
$dNoSchool8 = strtotime($tNoSchool8);

// Reformat the dates to get standardized text representation
$tFirstSunday = date('Y-m-d', $dFirstSunday);
$tLastSunday  = date('Y-m-d', $dLastSunday);

$tNoSchool1 = date('Y-m-d', $dNoSchool1);
$tNoSchool2 = date('Y-m-d', $dNoSchool2);
$tNoSchool3 = date('Y-m-d', $dNoSchool3);
$tNoSchool4 = date('Y-m-d', $dNoSchool4);
$tNoSchool5 = date('Y-m-d', $dNoSchool5);
$tNoSchool6 = date('Y-m-d', $dNoSchool6);
$tNoSchool7 = date('Y-m-d', $dNoSchool7);
$tNoSchool8 = date('Y-m-d', $dNoSchool8);

// Instantiate the class and build the report.
$yTitle = 20;
$yTeachers = $yTitle + 6;
$nameX = 10;
$epd = 3;

$pdf = new PDF_Attendance();

for ($i = 0; $i < $nGrps; $i++) {
    $iGroupID = $aGrp[$i];
    //  uset($aStudents);
    if ($i > 0) {
        $pdf->AddPage();
    }
    //Get the data on this group
    $group = GroupQuery::Create()->findOneById($iGroupID);
    
    $FYString = MakeFYString($iFYID);
    
    $reportHeader = str_pad($group->getName(), 95).$FYString;
    
    // Build the teacher string- first teachers, then the liaison
    $teacherString = gettext('Teachers').': ';
    $bFirstTeacher = true;
    $iTeacherCnt = 0;
    $iMaxTeachersFit = 4;
    $iStudentCnt = 0;
    
    $groupRoleMemberships = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
            ->joinWithPerson()
            ->usePersonQuery()
              ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->endUse()                            
            ->orderBy(PersonTableMap::COL_PER_LASTNAME)
            ->_and()->orderBy(PersonTableMap::COL_PER_FIRSTNAME) // I've try to reproduce per_LastName, per_FirstName
            ->findByGroupId($iGroupID);

    if ($tAllRoles != 1) {
        $liaisonString = '';
            
        foreach ($groupRoleMemberships as $groupRoleMembership) {
            $person = $groupRoleMembership->getPerson();
            $family = $person->getFamily();
                        
            $homePhone = "";
            if (!empty($family)) {
                $homePhone = $family->getHomePhone();
        
        
                if (empty($homePhone)) {
                    $homePhone = $family->getCellPhone();
                }
            
                if (empty($homePhone)) {
                    $homePhone = $family->getWorkPhone();
                }
            }
                            
            $groupRole = EcclesiaCRM\ListOptionQuery::create()->filterById($group->getRoleListId())->filterByOptionId($groupRoleMembership->getRoleId())->findOne();
                
            $lst_OptionName = $groupRole->getOptionName();
                            
            if ($lst_OptionName == 'Teacher') {
              $lineDates = [];
            
              $person = $groupRoleMembership->getPerson();
        
              $assignedProperties = Record2propertyR2pQuery::Create()
                          ->findByR2pRecordId($person->getId());

              $props = "";
              foreach ($assignedProperties as $assproperty) {
                $property = PropertyQuery::Create()->findOneByProId ($assproperty->getR2pProId());
                $props.= $property->getProName().", ";
              }
            
              $family = $person->getFamily();
        
              $homePhone = "";
              if (!empty($family)) {
                  $homePhone = $family->getHomePhone();
  
                  if (empty($homePhone)) {
                      $homePhone = $family->getCellPhone();
                  }
      
                  if (empty($homePhone)) {
                      $homePhone = $family->getWorkPhone();
                  }
              }
            
              $lineArr['firstName'] = $person->getFirstName();
              $lineArr['lastName']  = $person->getLastName();
              $lineArr['fullName']  = $person->getFullName();
              $lineArr['birthDate'] = '';
              $lineArr['gender']    = '';
              $lineArr['age']       = '';
              $lineArr['homePhone'] = '';
              $lineArr['groupName'] = '';
              $lineArr['props']     = '';
              $lineArr['photos']    = $person->getPhoto()->getThumbnailURI();
  
              $lineArr = array_merge($lineArr,$lineDates);
  
              $aTeachers[] = $lineArr;
            } elseif ($lst_OptionName == 'Student') {
              $lineDates = [];
            
              $person = $groupRoleMembership->getPerson();
        
              $assignedProperties = Record2propertyR2pQuery::Create()
                          ->findByR2pRecordId($person->getId());

              $props = "";
              foreach ($assignedProperties as $assproperty) {
                $property = PropertyQuery::Create()->findOneByProId ($assproperty->getR2pProId());
                $props.= $property->getProName().", ";
              }
            
              $family = $person->getFamily();
        
              $homePhone = "";
              if (!empty($family)) {
                  $homePhone = $family->getHomePhone();
  
                  if (empty($homePhone)) {
                      $homePhone = $family->getCellPhone();
                  }
      
                  if (empty($homePhone)) {
                      $homePhone = $family->getWorkPhone();
                  }
              }
            
              $lineArr['firstName'] = $person->getFirstName();
              $lineArr['lastName']  = $person->getLastName();
              $lineArr['fullName']  = $person->getFullName();
              $lineArr['birthDate'] = OutputUtils::FormatDate($person->getBirthDate()->format("Y-m-d"));
              $lineArr['gender']    = ($person->getGender() == 1)?gettext("Boy"):gettext("Girl");
              $lineArr['age']       = $person->getAge(false);
              $lineArr['homePhone'] = $homePhone;
              $lineArr['groupName'] = $group->getName();
              $lineArr['props']     = $props;
              $lineArr['photos']    = $person->getPhoto()->getThumbnailURI();
  
              $lineArr = array_merge($lineArr,$lineDates);
  
              $aStudents[] = $lineArr;
            } elseif ($lst_OptionName == gettext('Liaison')) {
                $liaisonString .= gettext('Liaison').':'.$person->getFullName().' '.$pdf->StripPhone($homePhone).' ';
            }
        }
        
        if ($iTeacherCnt < $iMaxTeachersFit) {
            $teacherString .= '  '.$liaisonString;
        }


        $pdf->SetFont('Times', 'B', 12);

        $y = $yTeachers;
        $pdf->WriteAt($nameX, $y, $teacherString);
        $y += 4;

        if ($iTeacherCnt >= $iMaxTeachersFit) {
            $pdf->WriteAt($nameX, $y, $liaisonString);
            $y += 4;
        }

        $y = $pdf->DrawAttendanceCalendar($nameX, $y + 6, $aStudents, gettext('Students'), $iExtraStudents,
                                          $tFirstSunday, $tLastSunday,
                                          $tNoSchool1, $tNoSchool2, $tNoSchool3, $tNoSchool4,
                                          $tNoSchool5, $tNoSchool6, $tNoSchool7, $tNoSchool8, $reportHeader, $withPictures);
        
        
        // we start a new page
        if ($y > $yTeachers+10) {
            $pdf->AddPage();
        }
                                                                        
        $y = $yTeachers;
        $pdf->DrawAttendanceCalendar($nameX, $y + 6, $aTeachers, gettext('Teachers'), $iExtraTeachers,
                                     $tFirstSunday, $tLastSunday,
                                     $tNoSchool1, $tNoSchool2, $tNoSchool3, $tNoSchool4,
                                     $tNoSchool5, $tNoSchool6, $tNoSchool7, $tNoSchool8, '', $withPictures);
    } else {
        //
        // print all roles on the attendance sheet
        //
        $iStudentCnt = 0;
                        
        $aStudents = [];
                        
        foreach ($groupRoleMemberships as $groupRoleMembership) {
            $lineDates = [];
            
            $person = $groupRoleMembership->getPerson();
        
            $assignedProperties = Record2propertyR2pQuery::Create()
                        ->findByR2pRecordId($person->getId());

            $props = "";
            foreach ($assignedProperties as $assproperty) {
              $property = PropertyQuery::Create()->findOneByProId ($assproperty->getR2pProId());
              $props.= $property->getProName().", ";
            }
            
            $family = $person->getFamily();
        
            $homePhone = "";
            if (!empty($family)) {
                $homePhone = $family->getHomePhone();
  
                if (empty($homePhone)) {
                    $homePhone = $family->getCellPhone();
                }
      
                if (empty($homePhone)) {
                    $homePhone = $family->getWorkPhone();
                }
            }
            
            $lineArr['firstName'] = $person->getFirstName();
            $lineArr['lastName']  = $person->getLastName();
            $lineArr['fullName']  = $person->getFullName();
            $lineArr['birthDate'] = OutputUtils::FormatDate($person->getBirthDate()->format("Y-m-d"));
            $lineArr['gender']    = ($person->getGender() == 1)?gettext("Boy"):gettext("Girl");
            $lineArr['age']       = $person->getAge(false);
            $lineArr['homePhone'] = $homePhone;
            $lineArr['groupName'] = $group->getName();
            $lineArr['props']     = $props;
            $lineArr['photos']    = $person->getPhoto()->getThumbnailURI();
  
            $lineArr = array_merge($lineArr,$lineDates);
  
            $aStudents[] = $lineArr;

        }

        $pdf->SetFont('Times', 'B', 12);

        $y = $yTeachers;

        $y = $pdf->DrawAttendanceCalendar($nameX, $y + 6, $aStudents, gettext('All Members'), $iExtraStudents+$iExtraTeachers,
                                          $tFirstSunday, $tLastSunday,
                                          $tNoSchool1, $tNoSchool2, $tNoSchool3, $tNoSchool4,
                                          $tNoSchool5, $tNoSchool6, $tNoSchool7, $tNoSchool8, $reportHeader, $withPictures);
    }
}
        
header('Pragma: public');  // Needed for IE when using a shared SSL certificate
if ($iPDFOutputType == 1) {
    $pdf->Output('ClassAttendance'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
