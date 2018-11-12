<?php

/*******************************************************************************
*
*  filename    : EcclesiaCRM/Reports/PDF_Attendance.php
*  last change : 2017-10-23
*  description : Creates a PDF for a Sunday School Class Attendance List
*  Udpdated    : 2018-02-26
*                Philippe Logel
******************************************************************************/

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;


class PDF_Attendance extends ChurchInfoReport
{
/////////////////////////////////////////////////////////////////////////////
//
// function modified by S. Shaffer 3/2006 to change the following
// (1) handle the case where teh list of names covers more than one page
// (2) rearranged some of the code to make it clearer for multi-page
//
//    for information contact Steve Shaffer at stephen@shaffers4christ.com
//
/////////////////////////////////////////////////////////////////////////////

    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);

        $this->incrementY = 6;
        $this->SetMargins(0, 0);

        $this->SetFont('Times', '', 14);
        $this->SetAutoPageBreak(false);
        $this->AddPage();
    }

    public function DrawAttendanceCalendar($nameX, $yTop, $persons, $tTitle, $extraLines,
                                    $tFirstSunday, $tLastSunday,
                                    $tNoSchool1, $tNoSchool2, $tNoSchool3, $tNoSchool4,
                                    $tNoSchool5, $tNoSchool6, $tNoSchool7, $tNoSchool8, $rptHeader,$with_img)
    {
        $startMonthX = 60;
        $dayWid = 7;
        
        //if ($with_img)
          $yIncrement = 10; // normaly 6
        /*else
          $yIncrement = 6;*/
        
        $yTitle = 20;
        $yTeachers = $yTitle + $yIncrement;
        $nameX = 10+$yIncrement/2;
        unset($NameList);
        $numMembers = 0;
        $aNameCount = 0;
        
        $MaxLinesPerPage = -3.75*$yIncrement+58.5; // 36  lines for a yIncrement of 6, 21 lines for a yIncrement of 10, y=-3.75x+58.5
        
        $fontTitleTitle = 16;
        
        //if ($with_img)
          $fontTitleNormal = 11;
        /*else
          $fontTitleNormal = 10;*/

        $aNoSchoolX = [];
        $noSchoolCnt = 0;
        
//
//  determine how many pages will be includes in this report
//

//
// First cull the input names array to remove duplicates, then extend the array to include the requested
// number of blank lines
//
        $prevThisPerson = '';
        $aNameCount = 0;
        for ($row = 0; $row < count($persons); $row++) {
            $person = $persons[$row];
            $thisPerson = $person['fullName'];
            //$thisName = $person->getLastName()."\n".$person->getFirstName()." ".$person->getMiddleName();
            
             // Special handling for person listed twice- only show once in the Attendance Calendar
             // This happens when a child is listed in two different families (parents divorced and
             // both active in the church)
            if ($thisPerson != $prevThisName) {
                $PersonList[$aNameCount]      = $thisPerson;
                $lastPersonList[$aNameCount]  = $person['lastName'];
                $firstPersonList[$aNameCount] = $person['firstName']." ".$person['middleName'];
                $imgList[$aNameCount]         = $person['photos'];
                $homePhoneList[$aNameCount]   = $person['homePhone'];
                $birthDateList[$aNameCount]   = $person['birthDate'];
                $genderDateList[$aNameCount]  = $person['gender'];
                $ageList[$aNameCount]         = $person['age'];
                $propList[$aNameCount++]      = $person['props'];
              //      echo "adding {$thisName} to PersonList at {$aNameCount}\n\r";
            }
            $prevThisPerson = $thisPerson;
        }
//
// add extra blank lines to the array
//
    for ($i = 0; $i < $extraLines; $i++) {
        $PersonList[$aNameCount]      = '   ';
        $lastPersonList[$aNameCount]  = '   ';
        $firstPersonList[$aNameCount] = '   ';
        $imgList[$aNameCount++]       = '';
    }

    $numMembers = count($PersonList);
    $nPages = ceil($numMembers / $MaxLinesPerPage);
  
  
    //  echo "nPages = {$nPages} \n\r";
    //
    // Main loop which draws each page
    //
    for ($p = 0; $p < $nPages; $p++) {
      //
      //   Paint the title section- class name and year on the top, then teachers/liaison
      //
      if ($p > 0) {
        $this->AddPage();
      }
      $this->SetFont('Times', 'B', $fontTitleTitle);
      $this->WriteAt($nameX, $yTitle, $rptHeader);
        
      $this->SetLineWidth(0.5);
      //$this->Line($nameX-5, $yTeachers - 0.45, 195, $yTeachers - 0.45); // unusefull
      $yMonths = $yTop;
      $yDays = $yTop + $yIncrement;
      $y = $yDays + $yIncrement;
  //
  //  put title on the page
  //
      $this->SetFont('Times', 'B', $fontTitleNormal);
      $this->WriteAt($nameX, $yDays + 1, $tTitle);
      $this->SetFont('Times', '', $fontTitleNormal);

  //
  // calculate the starting and ending rows for the page
  //
      $pRowStart = $p * $MaxLinesPerPage;
      $pRowEnd = min(((($p + 1) * $MaxLinesPerPage)), $numMembers);
  //    echo "pRowStart = {$pRowStart} and pRowEnd= {$pRowEnd}\n\r";
  //
  // Write the names down the page and draw lines between
  //

      $this->SetLineWidth(0.25);
      for ($row = $pRowStart; $row < $pRowEnd; $row++) {
        $this->SetFont('Times', 'B', $fontTitleNormal-1);
        $this->WriteAt($nameX, $y + 1, $lastPersonList[$row]);
        $this->WriteAt($nameX, $y + 5, $firstPersonList[$row]);
        $this->SetFont('Times', 'B', $fontTitleNormal);
        
        // we draw the gender
        $this->SetFont('Times', 'B', $fontTitleNormal-3);
        if ($genderDateList[$row] != '') {
          $this->WriteAt($nameX+24, $y+0.75, "(".substr($genderDateList[$row],0,1).")");
        }
        $this->SetFont('Times', '', $fontTitleNormal);
        
        // the phone number
        $this->SetFont('Times', '', $fontTitleNormal-5);
        $this->WriteAt($nameX+28.25, $y + 0.75, $homePhoneList[$row]);
        $this->SetFont('Times', '', $fontTitleNormal);
        
        // the birthDate
        $this->SetFont('Times', '', $fontTitleNormal-4);
        if ($birthDateList[$row] != '')
          $this->WriteAt($nameX+24, $y + 3.5, "(".$birthDateList[$row].")");
        $this->SetFont('Times', '', $fontTitleNormal);
        
        // the Age 
        $this->SetFont('Times', '', $fontTitleNormal-2);
        $this->WriteAt($nameX-(($with_img == true)?14.5:5), $y + 2.5, $ageList[$row]);
        $this->SetFont('Times', '', $fontTitleNormal);

        $this->SetFont('Times', '', $fontTitleNormal-6);
        if ($propList[$row] != '') {
          $this->WriteAt($nameX+24, $y + 6, $propList[$row]);
        }
        $this->SetFont('Times', '', $fontTitleNormal);

        if($with_img == true) 
        {
          //$this->SetLineWidth(0.5);
          $this->Line($nameX-$yIncrement,$y,$nameX,$y);
          $this->Line($nameX-$yIncrement,$y+$yIncrement,$nameX,$y+$yIncrement);
          $this->Line($nameX-$yIncrement,$y,$nameX,$y);
          $this->Line($nameX-$yIncrement,$y,$nameX-$yIncrement,$y+$yIncrement);
          
          // we build the cross in the case of there's no photo
          //$this->SetLineWidth(0.25);
          $this->Line($nameX-$yIncrement,$y+$yIncrement,$nameX,$y);
          $this->Line($nameX-$yIncrement,$y,$nameX,$y+$yIncrement);


          if ($PersonList[$row] != '   ' && strlen($imgList[$row]) > 5 && file_exists($imgList[$row]))
          {
            list($width, $height) = getimagesize($imgList[$row]);
            $factor = $yIncrement/$height;
            $nw = $width*$factor;
            $nh = $yIncrement;
        
            $this->Image($imgList[$row], $nameX-$nw , $y, $nw,$nh,'JPG');
          }
        }
        
        $y += $yIncrement;
      }
  //
  // write a totals text at the bottom
  //
      $this->SetFont('Times', 'B', $fontTitleNormal);
      $this->WriteAt($nameX, $y + 1, gettext('Totals'));
      $this->SetFont('Times', '', $fontTitleNormal);

      $bottomY = $y + $yIncrement;
  //
  // Paint the calendar grid
  //
      $dayCounter = 0;
      $monthCounter = 0;
      $dayX = $startMonthX;
      $monthX = $startMonthX;
      $noSchoolCnt = 0;
      $heavyVerticalXCnt = 0;
      $lightVerticalXCnt = 0;

      $tWhichSunday = $tFirstSunday;
      $dWhichSunday = strtotime($tWhichSunday);

      $dWhichMonthDate = $dWhichSunday;
      $whichMonth = date('n', $dWhichMonthDate);

      $doneFlag = false;

      while (!$doneFlag) {
        $dayListX[$dayCounter] = $dayX;
      
        $dayListNum[$dayCounter] = date('d', $dWhichSunday);

        if ($tWhichSunday == $tNoSchool1) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
        if ($tWhichSunday == $tNoSchool2) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
        if ($tWhichSunday == $tNoSchool3) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
        if ($tWhichSunday == $tNoSchool4) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
        if ($tWhichSunday == $tNoSchool5) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
        if ($tWhichSunday == $tNoSchool6) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
        if ($tWhichSunday == $tNoSchool7) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
        if ($tWhichSunday == $tNoSchool8) {
          $aNoSchoolX[$noSchoolCnt++] = $dayX;
        }
      
        if (date('n', $dWhichSunday) != $whichMonth) { // Finish the previous month
          $this->WriteAt($monthX, $yMonths + 1, substr(gettext(date('F', $dWhichMonthDate)),0,3));
          $aHeavyVerticalX[$heavyVerticalXCnt++] = $monthX;
          $whichMonth = date('n', $dWhichSunday);
          $dWhichMonthDate = $dWhichSunday;
          $monthX = $dayX;
        } else {
          $aLightVerticalX[$lightVerticalXCnt++] = $dayX;
        }
        $dayX += $dayWid;
        ++$dayCounter;

  //             if ($tWhichSunday == $tLastSunday) $doneFlag = true;
  //
  //      replaced this conditional to correct a problem where begin and end dates were not the same
  //      day of week
  //
        if (strtotime($tWhichSunday) >= strtotime($tLastSunday)) {
          $doneFlag = true;
        }

  //   Increment the date by one week
  //
        $sundayDay = date('d', $dWhichSunday);
        $sundayMonth = date('m', $dWhichSunday);
        $sundayYear = date('Y', $dWhichSunday);
        $dWhichSunday = mktime(0, 0, 0, $sundayMonth, $sundayDay + 7, $sundayYear);
        $tWhichSunday = date('Y-m-d', $dWhichSunday);
      }
      $aHeavyVerticalX[$heavyVerticalXCnt++] = $monthX;
      $this->WriteAt($monthX, $yMonths + 1, substr(gettext(date('F', $dWhichMonthDate)),0,3));

      $rightEdgeX = $dayX;

        // Draw vertical lines now that we know how far down the list goes

        // Draw the left-most vertical line heavy, through the month row
        $this->SetLineWidth(0.5);
      $this->Line($nameX, $yMonths, $nameX, $bottomY);

          // Draw the left-most line between the people and the calendar
          $lineTopY = $yMonths;
      $this->Line($startMonthX, $lineTopY, $startMonthX, $bottomY);

          // Draw the vertical lines in the grid based on X coords stored above
      $this->SetLineWidth(0.5);
      for ($i = 0; $i < $heavyVerticalXCnt; $i++) {
        $this->Line($aHeavyVerticalX[$i], $lineTopY, $aHeavyVerticalX[$i], $bottomY);
      }

      $lineTopY = $yDays;
      $this->SetLineWidth(0.25);
      for ($i = 0; $i < $lightVerticalXCnt; $i++) {
        $this->Line($aLightVerticalX[$i], $lineTopY, $aLightVerticalX[$i], $bottomY);
      }

      // Draw the right-most vertical line heavy, through the month row
      $this->SetLineWidth(0.5);
      $this->Line($dayX, $yMonths, $dayX, $bottomY);

          // Fill the no-school days
      $this->SetFillColor(200, 200, 200);
      $this->SetLineWidth(0.25);
      for ($i = 0; $i < count($aNoSchoolX); $i++) {
        $this->Rect($aNoSchoolX[$i], $yDays, $dayWid, $bottomY - $yDays, 'FD');
      }

      for ($i = 0; $i < $dayCounter; $i++) {
        $this->WriteAt($dayListX[$i], $yDays + 1, $dayListNum[$i]);
      }

      // Draw heavy lines to delimit the Months and totals
      $this->SetLineWidth(0.5);
      $this->Line($nameX, $yMonths, $rightEdgeX, $yMonths);
      $this->Line($nameX, $yMonths + $yIncrement, $rightEdgeX, $yMonths + $yIncrement);
      $this->Line($nameX, $yMonths + 2 * $yIncrement, $rightEdgeX, $yMonths + 2 * $yIncrement);
      $yBottom = $yMonths + (($numMembers + $extraLines + 2) * $yIncrement);
      $this->Line($nameX, $yBottom, $rightEdgeX, $yBottom);
      $this->Line($nameX, $yBottom + $yIncrement, $rightEdgeX, $yBottom + $yIncrement);
  //
  //  add in horizontal lines between names
  //
      $y = $yTop;
      for ($s = $pRowStart; $s < $pRowEnd + 4; $s++) {
        $this->Line($nameX, $y, $rightEdgeX, $y);
        $y += $yIncrement;
      }

      //$this->AddPage();
    }

    return $bottomY;
  }
  
    public function DrawRealAttendanceCalendar($nameX, $yTop, $labels, $aNames, $tTitle, $extraLines,
                                    $tFirstSunday, $tLastSunday,
                                    $rptHeader, $with_img,$maxNbrEvents)
    {      
        $startMonthX = 60;
        $dayWid = 7;
        
        $yIncrement = 10; // normaly 6
        
        $yTitle = 20;
        $yTeachers = $yTitle + $yIncrement;
        $nameX = 10+$yIncrement/2;
        $numMembers = 0;
        $aNameCount = 0;
        
        $MaxLinesPerPage = -3.75*$yIncrement+58.5; // 36  lines for a yIncrement of 6, 21 lines for a yIncrement of 10, y=-3.75x+58.5
        
        $fontTitleTitle = 16;
        
        $fontTitleNormal = 11;

        $aNoSchoolX = [];
        $noSchoolCnt = 0;
        
        unset($realList);
        
//
//  determine how many pages will be includes in this report
//

//
// First cull the input names array to remove duplicates, then extend the array to include the requested
// number of blank lines
//
        $prevThisName = '';
        $aNameCount = 0;
        for ($row = 0; $row < count($aNames); $row++) {
            $thisName = $aNames[$row]['firstName']." ".$aNames[$row]['lastName'];
            //$thisName = $person->getLastName()."\n".$person->getFirstName()." ".$person->getMiddleName();
            
             // Special handling for person listed twice- only show once in the Attendance Calendar
             // This happens when a child is listed in two different families (parents divorced and
             // both active in the church)
            if ($thisName != $prevThisName) {
                $realList[$aNameCount++] = $aNames[$row];                
              //      echo "adding {$thisName} to realList at {$aNameCount}\n\r";
            }
            $prevThisName = $thisName;
        }
        
//
// add extra blank lines to the array
//
    for ($i = 0; $i < $extraLines; $i++) {
        $realList[$aNameCount++] = $labels;
    }

    $numMembers = count($realList);
    $nPages = ceil($numMembers / $MaxLinesPerPage);
    
    $yTop=30;
    
    //  echo "nPages = {$nPages} \n\r";
    //
    // Main loop which draws each page
    //
    for ($p = 0; $p < $nPages; $p++) {
      //
      //   Paint the title section- class name and year on the top, then teachers/liaison
      //
      if ($p > 0) {
        $this->AddPage();
      }
      $this->SetFont('Times', 'B', $fontTitleTitle);
      $this->WriteAt($nameX, $yTitle, $rptHeader);
        
      $this->SetLineWidth(0.5);
      //$this->Line($nameX-5, $yTeachers - 0.45, 195, $yTeachers - 0.45); // unusefull
      $yMonths = $yTop;
      $yDays = $yTop + $yIncrement;
      $y = $yDays + $yIncrement;
  //
  //  put title on the page
  //
      $this->SetFont('Times', 'B', $fontTitleNormal);
      $this->WriteAt($nameX, $yDays + 1, $tTitle);
      $this->SetFont('Times', '', $fontTitleNormal);
      
  //
  // Now we draw the Labels
  //
  
  $datePlace = 0;
  
  foreach ($labels as $key => $value) {
    switch ($key) {
      case 'firstName':
        break;
      case 'lastName':
        break;
      case 'photos':
        break;
      case 'gender':
        break;
      case 'birthDate':
        break;
      case 'homePhone':
        break;
      case 'props':
        break;    
      case 'age':
        $this->SetFont('Times', 'B', $fontTitleNormal);
        $this->TextWithDirection($nameX+50, $y - 5.5, $value,'U');
        $this->SetFont('Times', '', $fontTitleNormal);
        break;
      case 'stats':
        $this->SetFont('Times', 'B', $fontTitleNormal);
        $this->TextWithDirection($nameX+57, $y -5.5, $value,'U');
        $this->SetFont('Times', '', $fontTitleNormal);
        break;
      case 'groupName':
        break;              
      default:
        // we are in case of dates
        $this->SetFont('Arial', 'B', $fontTitleNormal-2);
        $this->TextWithDirection($nameX+64+$datePlace, $y - 2, $value,'U');
        $this->SetFont('Times', '', $fontTitleNormal);
        $datePlace+=7;
    }
  }

      
  //
  // calculate the starting and ending rows for the page
  //
      $pRowStart = $p * $MaxLinesPerPage;
      $pRowEnd = min(((($p + 1) * $MaxLinesPerPage)), $numMembers);
  //    echo "pRowStart = {$pRowStart} and pRowEnd= {$pRowEnd}\n\r";
  //
  // Write the names down the page and draw lines between
  //

      $this->SetLineWidth(0.25);
      $sizeArray = [];   
            
      for ($i=0;$i < $maxNbrEvents;$i++) {
        $sizeArray[$i] = 0;
      }
      
      $real_count = 0;
      
      for ($row = $pRowStart; $row < $pRowEnd; $row++) {
        $real_count++;
        $datePlace = 0;
        $positionSize  = 0;
        foreach ($realList[$row] as $key => $value) {
          switch ($key) {
            case 'firstName':
              $this->SetFont('Times', 'B', $fontTitleNormal-2);
              if ($value != OutputUtils::translate_text_fpdf("First Name")) {
                $this->WriteAt($nameX, $y + 1, $value);
              }
              $this->SetFont('Times', '', $fontTitleNormal);
              break;
            case 'lastName':
              $this->SetFont('Times', '', $fontTitleNormal-5);
              if ($value != OutputUtils::translate_text_fpdf("Last Name")) {
                $this->WriteAt($nameX, $y + 5, $value);
              }
              $this->SetFont('Times', '', $fontTitleNormal);
              break;
            case 'photos':
              if($with_img == true) 
              {
                //$this->SetLineWidth(0.5);
                $this->Line($nameX-$yIncrement,$y,$nameX,$y);
                $this->Line($nameX-$yIncrement,$y+$yIncrement,$nameX,$y+$yIncrement);
                $this->Line($nameX-$yIncrement,$y,$nameX,$y);
                $this->Line($nameX-$yIncrement,$y,$nameX-$yIncrement,$y+$yIncrement);
          
                // we build the cross in the case of there's no photo
                $this->Line($nameX-$yIncrement,$y+$yIncrement,$nameX,$y);
                $this->Line($nameX-$yIncrement,$y,$nameX,$y+$yIncrement);


                if ($value != '   ' && strlen($value) > 5 && file_exists($value))
                {
                  list($width, $height) = getimagesize($value);
                  $factor = $yIncrement/$height;
                  $nw = $width*$factor;
                  $nh = $yIncrement;
        
                  $this->Image($value, $nameX-$nw , $y, $nw,$nh,'JPG');
                }
              }
              break;
            case 'gender':
              $this->SetFont('Times', 'B', $fontTitleNormal-3);
              if ($value != OutputUtils::translate_text_fpdf("Gender")) {
                $this->WriteAt($nameX+24, $y+0.75, "(".substr($value,0,1).")");
              }
              $this->SetFont('Times', '', $fontTitleNormal);
              break;
            case 'birthDate':
              $this->SetFont('Times', '', $fontTitleNormal-4);
              if ($value != OutputUtils::translate_text_fpdf("Birth Date"))
                $this->WriteAt($nameX+24, $y + 3.5, "(".$value.")");
              $this->SetFont('Times', '', $fontTitleNormal);
              break;
            case 'homePhone':
              $this->SetFont('Times', '', $fontTitleNormal-5);
              $this->WriteAt($nameX+28.25, $y + 0.75, $value);
              $this->SetFont('Times', '', $fontTitleNormal);
              break;
            case 'props':
              $this->SetFont('Times', '', $fontTitleNormal-6);
              if ($value != OutputUtils::translate_text_fpdf("Notes")) {
                $this->WriteAt($nameX+24, $y + 6, $value);
              }
              $this->SetFont('Times', '', $fontTitleNormal);
              break;    
            case 'age':
              $this->SetFont('Times', '', $fontTitleNormal-2);
              $this->WriteAt($nameX-(($with_img == true)?14.5:5), $y + 2.5, $value);
              $this->SetFont('Times', '', $fontTitleNormal);
              break;
            case 'stats':
              $this->SetFont('Times', '', $fontTitleNormal-2);
              if ($value != 'Stats') {
                $this->WriteAt($nameX+52.5, $y + 2.5, $value."/".$maxNbrEvents);
              }
              $this->SetFont('Times', '', $fontTitleNormal);
              break;
            case 'groupName':
              break;              
            default:
              // we are in case of dates
              $this->SetFont('Arial', '', $fontTitleNormal-2);
              if ($value == 1) {
                $this->WriteAt($nameX+60+$datePlace+1, $y + 2.5, "x");
                $sizeArray[$positionSize]++;
              }
              $positionSize++;
              //$this->TextWithDirection($nameX+56, $y + 2, $value,'D');
              $this->SetFont('Times', '', $fontTitleNormal);
              $datePlace+=7;
              
          }
        }
        
        $y += $yIncrement;
      }
      
      
  //
  // write a totals text at the bottom
  //
      $this->SetFont('Times', 'B', $fontTitleNormal);
      $this->WriteAt($nameX, $y + 1, gettext('Totals'));
      $this->SetFont('Times', '', $fontTitleNormal);
      
      $datePlace = 0;
      
      $this->SetFont('Times', '', $fontTitleNormal-2);
      foreach ($sizeArray as $c) {        
        $this->TextWithDirection($nameX + 64 + $datePlace, $y + 9, $c."/".$real_count,'U');        
        $datePlace+=7;
      }
      $this->SetFont('Times', '', $fontTitleNormal);

      $bottomY = $y + $yIncrement;
  //
  // Paint the calendar grid
  //
      $dayX = $startMonthX;
      $monthX = $startMonthX;
      $heavyVerticalXCnt = 0;
      $lightVerticalXCnt = 0;

      $numberVertLines = 0;
      
      while ($numberVertLines++<21) {
        $aHeavyVerticalX[$heavyVerticalXCnt++] = $monthX;
        $whichMonth = date('n', $dWhichSunday);

        $monthX = $dayX;        
        $dayX += $dayWid;
      }
      $aHeavyVerticalX[$heavyVerticalXCnt++] = $monthX;

      $rightEdgeX = $dayX;

      // Draw vertical lines now that we know how far down the list goes

      // Draw the left-most vertical line heavy, through the month row
      $this->SetLineWidth(0.5);
      $this->Line($nameX, $yMonths, $nameX, $bottomY);

      // Draw the left-most line between the people and the calendar
      $lineTopY = $yTop;
      $this->Line($startMonthX, $lineTopY, $startMonthX, $bottomY);

      // Draw the vertical lines in the grid based on X coords stored above
      $this->SetLineWidth(0.25);
      for ($i = 0; $i < $heavyVerticalXCnt; $i++) {
        if ($i == 3) {
          $this->SetLineWidth(0.5);
        } else {
          $this->SetLineWidth(0.25);
        }
        $this->Line($aHeavyVerticalX[$i], $lineTopY, $aHeavyVerticalX[$i], $bottomY);
      }

      // Draw the right-most vertical line heavy, through the month row
      $this->SetLineWidth(0.25);
      $this->Line($dayX, $yMonths, $dayX, $bottomY);

          // Fill the no-school days
      $this->SetFillColor(200, 200, 200);
      $this->SetLineWidth(0.25);
      for ($i = 0; $i < count($aNoSchoolX); $i++) {
        $this->Rect($aNoSchoolX[$i], $yDays, $dayWid, $bottomY - $yDays, 'FD');
      }

      // Draw heavy lines to delimit the Months and totals
      $this->SetLineWidth(0.5);
      $this->Line($nameX, $yMonths, $rightEdgeX, $yMonths);
      $this->Line($nameX, $yMonths + 2 * $yIncrement, $rightEdgeX, $yMonths + 2 * $yIncrement);
      $yBottom = $yMonths + (($numMembers + $extraLines + 2) * $yIncrement);
      
      // this part is unusefull
      //$this->Line($nameX, $yBottom, $rightEdgeX, $yBottom);
      //$this->Line($nameX, $yBottom + $yIncrement, $rightEdgeX, $yBottom + $yIncrement);
  //
  //  add in horizontal lines between names
  //
      $y = $yTop;
      for ($s = $pRowStart; $s < $pRowEnd + 4; $s++) {
        if (!($y == $yTop || $y == $yTop+$yIncrement)) {
          $this->Line($nameX, $y, $rightEdgeX, $y);
        }
        $y += $yIncrement;
      }

      //$this->AddPage();
    }

    return $bottomY;
  }
}
