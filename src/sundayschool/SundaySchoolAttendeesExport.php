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
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\PropertyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

if ( !( SessionUser::getUser()->isCSVExportEnabled() || SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$delimiter = SessionUser::getUser()->CSVExportDelemiter();
$charset   = SessionUser::getUser()->CSVExportCharset();

$iGroupID  = $_GET['groupID'];

$startDate = $_GET['start'];
$endDate   = $_GET['end'];

// we start to build the CSV file
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: text/csv;charset='.$charset);
header('Content-Disposition: attachment; filename=SundaySchool-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
header('Content-Transfer-Encoding: binary');

$delimiter = $delimiter;

$out = fopen('php://output', 'w');

//add BOM to fix UTF-8 in Excel 2016 but not under, so the problem is solved with the charset variable
if ($charset == "UTF-8") {
    fputs($out, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
}

$labelArr = [];
$labelArr[] = InputUtils::translate_special_charset("First Name",$charset);
$labelArr[] = InputUtils::translate_special_charset("Last Name",$charset);
$labelArr[] = InputUtils::translate_special_charset("Birth Date",$charset);
$labelArr[] = InputUtils::translate_special_charset("Gender",$charset);
$labelArr[] = InputUtils::translate_special_charset("Age",$charset);
$labelArr[] = InputUtils::translate_special_charset("Phone",$charset);
$labelArr[] = InputUtils::translate_special_charset("Group",$charset);
$labelArr[] = InputUtils::translate_special_charset("Notes",$charset);
/*$labelArr[] = InputUtils::translate_special_charset("Photo");*/
/*$labelArr[] = InputUtils::translate_special_charset("Follow");
$labelArr[] = InputUtils::translate_special_charset("Re-inscription",$charset);*/
$labelArr[] = InputUtils::translate_special_charset("Stats",$charset);

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


$maxNbrEvents = 0;

$sizeArray = [];

/*for ($i=0;$i < 150;$i++) {
  $sizeArray[$i] = 0;
}*/

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

      $positionSize = 0;
      foreach ($activeEvents as $activeEvent) {// we loop in the events of the year
        $eventAttendees = EventAttendQuery::create()
              ->filterByPersonId($person->getId())
              ->filterByEventId($activeEvent->getId())
              ->find();

        foreach ($eventAttendees as $eventAttendee) {
          if (!empty($eventAttendee->getCheckoutDate())) {
            $lineDates[] = 1;
            $sizeArray[$positionSize]++;
            $lineRealPresence++;
          } else {
            $lineDates[] = 0;
          }

          $lineNbrEvents++;
          $positionSize++;
        }
      }

      if ($maxNbrEvents < $lineNbrEvents) {
        $maxNbrEvents = $lineNbrEvents;
      }

      $lineArr[] = InputUtils::translate_special_charset($person->getFirstName(),$charset);
      $lineArr[] = InputUtils::translate_special_charset($person->getLastName(),$charset);
      $lineArr[] = InputUtils::translate_special_charset(OutputUtils::FormatDate($person->getBirthDate()->format("Y-m-d")),$charset);
      $lineArr[] = InputUtils::translate_special_charset(($person->getGender() == 1)?_("Boy"):_("Girl"),$charset);
      $lineArr[] = $person->getAge();
      $lineArr[] = $homePhone;
      $lineArr[] = $group->getName();
      $lineArr[] = $props;
      $lineArr[] = "\"".$lineRealPresence."/".$lineNbrEvents."\"";

      $lineArr = array_merge($lineArr,$lineDates);

      fputcsv($out, $lineArr, $delimiter);
    }
}

$base = ['','','','','','','','',_('Totals')];

for ($i=0;$i < $maxNbrEvents;$i++) {
  $base[$i+9] = $sizeArray[$i];
}

fputcsv($out, $base, $delimiter);

fclose($out);

ob_end_flush();
