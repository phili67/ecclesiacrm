<?php
/*******************************************************************************
*
*  filename    : Reports/ClassRealAttendance.php
*  last change : 2013-02-22
*  description : Creates a PDF for a Sunday School Class Attendance List
*  Udpdated    : 2018-02-27
*                Philippe Logel
******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';
require '../Include/ReportFunctions.php';

use EcclesiaCRM\Reports\PDF_Attendance;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\Person2group2roleP2g2r;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Map\PersonTableMap;
use Propel\Runtime\ActiveQuery\Criteria;


// we get all the params
$iGroupID = InputUtils::LegacyFilterInput($_GET['groupID']);
$withPictures = InputUtils::LegacyFilterInput($_GET['withPictures'], 'int');
$iExtraStudents = InputUtils::LegacyFilterInputArr($_GET, 'ExtraStudents', 'int');
$iFYID = $_SESSION['idefaultFY'];// $iFYID = InputUtils::LegacyFilterInput($_GET['FYID'], 'int'); //
$startDate = $_GET['start'];
$endDate   = $_GET['end'];

// ????
$FYString = MakeFYString($iFYID);

// we will construct the labels
$labelArr = [];
$labelArr['firstName'] = OutputUtils::translate_text_fpdf("First Name");
$labelArr['lastName'] = OutputUtils::translate_text_fpdf("Last Name");
$labelArr['birthDate'] = OutputUtils::translate_text_fpdf("Birth Date");
$labelArr['gender'] = OutputUtils::translate_text_fpdf("Gender");
$labelArr['age'] = OutputUtils::translate_text_fpdf("Age");
$labelArr['homePhone'] = OutputUtils::translate_text_fpdf("Phone");
$labelArr['groupName'] = OutputUtils::translate_text_fpdf("Group");
$labelArr['props'] = OutputUtils::translate_text_fpdf("Notes");
/*$labelArr[] = OutputUtils::translate_text_fpdf("Photo");*/
/*$labelArr[] = OutputUtils::translate_text_fpdf("Follow");
$labelArr[] = OutputUtils::translate_text_fpdf("Re-inscription");*/
$labelArr['stats'] = OutputUtils::translate_text_fpdf("Stats");

// we filter all the events which belongs to a group
$activeEvents = EventQuery::Create()
    ->filterByGroupId($iGroupID)
    ->filterByInActive(1, Criteria::NOT_EQUAL)
    ->Where('event_start BETWEEN "'.$startDate.'" AND "'.$endDate.'"')// We filter only the events from the current month : date('Y')
    ->orderByStart()
    ->find();

$date_count = 0; 
foreach ($activeEvents as $activeEvent) {// we loop in the events of the year
  $labelArr['date'.$date_count++] = OutputUtils::change_date_for_place_holder($activeEvent->getStart()->format("Y-m-d"));
}

// Instantiate the class and build the report.
$yTitle = 20;
$yTeachers = $yTitle + 6;
$nameX = 10;
$epd = 3;

$pdf = new PDF_Attendance();

//  uset($aStudents);
//Get the data on this group
$group = GroupQuery::Create()->findOneById($iGroupID);

$reportHeader = str_pad($group->getName(), 95).$FYString;

// Build the teacher string- first teachers, then the liaison
$teacherString = gettext('Teachers').': ';
$bFirstTeacher = true;
$iTeacherCnt = 0;
$iMaxTeachersFit = 4;
$iStudentCnt = 0;

$groupRoleMemberships = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
                            ->joinWithPerson()
                            ->orderBy(PersonTableMap::COL_PER_FIRSTNAME) // I've try to reproduce ORDER BY per_LastName, per_FirstName
                            ->findByGroupId($iGroupID);

$aStudents = [];

foreach ($groupRoleMemberships as $groupRoleMembership) {
    $lineArr = [];
    $lineRealPresence = 0;
    $lineNbrEvents = 0;
    $lineDates = [];
    
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
    
    if ($lst_OptionName == 'Student') {// we will draw only the students
      $assignedProperties = Record2propertyR2pQuery::Create()
                  ->findByR2pRecordId($person->getId());

      $props = "";
      foreach ($assignedProperties as $assproperty) {
        $property = PropertyQuery::Create()->findOneByProId ($assproperty->getR2pProId());
        $props.= $property->getProName().", ";
      }
      
      $date_count = 0; 

      foreach ($activeEvents as $activeEvent) {// we loop in the events of the year
        $eventAttendees = EventAttendQuery::create()
              ->filterByPersonId($person->getId())
              ->filterByEventId($activeEvent->getId())
              ->find();
              
        foreach ($eventAttendees as $eventAttendee) {
          if (!empty($eventAttendee->getCheckoutDate())) {
            $lineDates['date'.$date_count++] = 1;
            $lineRealPresence++;
          } else {
            $lineDates['date'.$date_count++] = 0;
          }
          
          $lineNbrEvents++;
        }
        
      }
      // we create the Thumbnail in case
      //$person->getPhoto()->createThumbnail();

    
      $lineArr['firstName'] = $person->getFirstName();
      $lineArr['lastName'] = $person->getLastName();
      $lineArr['birthDate'] = OutputUtils::FormatDate($person->getBirthDate()->format("Y-m-d"));
      $lineArr['gender'] = ($person->getGender() == 1)?gettext("Boy"):gettext("Girl");
      $lineArr['age'] = $person->getAge(false);
      $lineArr['homePhone'] = $homePhone;
      $lineArr['groupName'] = $group->getName();
      $lineArr['props'] = $props;
      $lineArr['stats'] = $lineRealPresence."/".$lineNbrEvents;
      $lineArr['photos'] = $person->getPhoto()->getThumbnailURI();
      
      $lineArr = array_merge($lineArr,$lineDates);
      
      $aStudents[] = $lineArr;
    }
}
    
$y = $pdf->DrawRealAttendanceCalendar($nameX, $y + 6, $labelArr, $aStudents, gettext('Students'), $iExtraStudents,
                               $startDate, $endDate, $reportHeader, $withPictures);

        
header('Pragma: public');  // Needed for IE when using a shared SSL certificate
if ($iPDFOutputType == 1) {
    $pdf->Output('ClassAttendance'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
