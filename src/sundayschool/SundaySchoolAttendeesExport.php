<?php

/*******************************************************************************
 *
 *  filename    : SundaySchoolAttendeesExport.php
 *  last change : 2018-02-12
 *  description : export of the attendees for the sundayschool
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2018 Logel Philippe all rights reserved
 *
 ******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';


use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\PropertyQuery;
use Propel\Runtime\ActiveQuery\Criteria;


if (!($_SESSION['bExportCSV'] || $_SESSION['bAdmin'])) {
    Redirect('Menu.php');
    exit;
}
  

$iGroupID = $_GET['groupID'];

$startDate = $_GET['start'];
$endDate   = $_GET['end'];

// we start to build the CSV file
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: text/csv;charset='.SystemConfig::getValue("sCSVExportCharset"));
header('Content-Disposition: attachment; filename=SundaySchool-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
header('Content-Transfer-Encoding: binary');

$delimiter = SystemConfig::getValue("sCSVExportDelemiter");

$out = fopen('php://output', 'w');

//add BOM to fix UTF-8 in Excel 2016 but not under, so the problem is solved with the sCSVExportCharset variable
if (SystemConfig::getValue("sCSVExportCharset") == "UTF-8") {
    fputs($out, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
}
  
$labelArr = [];
$labelArr[] = InputUtils::translate_special_charset("First Name");
$labelArr[] = InputUtils::translate_special_charset("Last Name");
$labelArr[] = InputUtils::translate_special_charset("Birth Date");
$labelArr[] = InputUtils::translate_special_charset("Gender");
$labelArr[] = InputUtils::translate_special_charset("Age");
$labelArr[] = InputUtils::translate_special_charset("Phone");
$labelArr[] = InputUtils::translate_special_charset("Group");
$labelArr[] = InputUtils::translate_special_charset("Notes");
/*$labelArr[] = InputUtils::translate_special_charset("Photo");*/
/*$labelArr[] = InputUtils::translate_special_charset("Follow");
$labelArr[] = InputUtils::translate_special_charset("Re-inscription");*/
$labelArr[] = InputUtils::translate_special_charset("Stats");

$activeEvents = EventQuery::Create()
    ->filterByGroupId($iGroupID)
    ->filterByInActive(1, Criteria::NOT_EQUAL)
    ->Where('event_start BETWEEN "'.$startDate.'" AND "'.$endDate.'"')// We filter only the events from the current month : date('Y')
    ->orderByStart()
    ->find();

$group = GroupQuery::Create()->findOneById($iGroupID);

foreach ($activeEvents as $activeEvent) {// we loop in the events of the year
  $labelArr[] = $activeEvent->getStart()->format("Y-m-d");
}


fputcsv($out, $labelArr, $delimiter);


$groupRoleMemberships = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
                            ->joinWithPerson()
                            ->orderBy(PersonTableMap::COL_PER_FIRSTNAME) // I've try to reproduce ORDER BY per_LastName, per_FirstName
                            ->findByGroupId($iGroupID);
                            

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
    
    
    if ($lst_OptionName == 'Student') {
      $assignedProperties = Record2propertyR2pQuery::Create()
                  ->findByR2pRecordId($person->getId());

      $props = "";
      foreach ($assignedProperties as $assproperty) {
        $property = PropertyQuery::Create()->findOneByProId ($assproperty->getR2pProId());
        $props.= $property->getProName().", ";
      }
      
      foreach ($activeEvents as $activeEvent) {// we loop in the events of the year
        $eventAttendees = EventAttendQuery::create()
              ->filterByPersonId($person->getId())
              ->filterByEventId($activeEvent->getId())
              ->find();
              
        foreach ($eventAttendees as $eventAttendee) {
          if (!empty($eventAttendee->getCheckoutDate())) {
            $lineDates[] = 1;
            $lineRealPresence++;
          } else {
            $lineDates[] = 0;
          }

          $lineNbrEvents++;
        }
      }
    
      $lineArr[] = InputUtils::translate_special_charset($person->getFirstName());
      $lineArr[] = InputUtils::translate_special_charset($person->getLastName());
      $lineArr[] = InputUtils::translate_special_charset(OutputUtils::FormatDate($person->getBirthDate()->format("Y-m-d")));
      $lineArr[] = InputUtils::translate_special_charset(($person->getGender() == 1)?gettext("Boy"):gettext("Girl"));
      $lineArr[] = $person->getAge();
      $lineArr[] = $homePhone;
      $lineArr[] = $group->getName();
      $lineArr[] = $props;
      $lineArr[] = "\"".$lineRealPresence."/".$lineNbrEvents."\"";
      
      $lineArr = array_merge($lineArr,$lineDates);
      
      
      fputcsv($out, $lineArr, $delimiter);
      
      
    }
}

fclose($out);

ob_end_flush();