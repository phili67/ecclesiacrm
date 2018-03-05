<?php

/* Philippe Logel */

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemConfig;

class OutputUtils {

  public static function translate_text_fpdf($string)
  {
    return iconv('UTF-8', 'windows-1252', $string);
  }
  
  public static function change_date_for_place_holder($string)
  {
    return ((strtotime($string) != "")?date(SystemConfig::getValue("sDatePickerFormat"), strtotime($string)):strtotime($string));
  }

  public static function FormatDateOutput($bWithTime)
  {
      $fmt = SystemConfig::getValue("sDateFormatLong");
      $fmt_time = SystemConfig::getValue("sTimeFormat");

      $fmt = str_replace("/", " ", $fmt);
    
      $fmt = str_replace("-", " ", $fmt);
    
      $fmt = str_replace("d", "%d", $fmt);
      $fmt = str_replace("m", "%B", $fmt);
      $fmt = str_replace("Y", "%Y", $fmt);
    
      if ($bWithTime) {
          $fmt .= " ".$fmt_time;
      }
    
      return $fmt;
  }

  // Reinstated by Todd Pillars for Event Listing
  // Takes MYSQL DateTime
  // bWithtime 1 to be displayed
  public static function FormatDate($dDate, $bWithTime = false)
  {
      if ($dDate == '' || $dDate == '0000-00-00 00:00:00' || $dDate == '0000-00-00') {
          return '';
      }

      if (strlen($dDate) == 10) { // If only a date was passed append time
          $dDate = $dDate.' 12:00:00';
      }  // Use noon to avoid a shift in daylight time causing
      // a date change.

      if (strlen($dDate) != 19) {
          return '';
      }

      // Verify it is a valid date
      $sScanString = mb_substr($dDate, 0, 10);
      list($iYear, $iMonth, $iDay) = sscanf($sScanString, '%04d-%02d-%02d');

      if (!checkdate($iMonth, $iDay, $iYear)) {
          return 'Unknown';
      }

      $fmt = self::FormatDateOutput($bWithTime);
        
      setlocale(LC_ALL, SystemConfig::getValue("sLanguage"));
      return utf8_encode(strftime("$fmt", strtotime($dDate)));
  }

// Format a BirthDate
// Optionally, the separator may be specified.  Default is YEAR-MN-DY
  public static function FormatBirthDate($per_BirthYear, $per_BirthMonth, $per_BirthDay, $sSeparator, $bFlags)
  {
      if ($bFlags == 1 || $per_BirthYear == '') {  //Person Would Like their Age Hidden or BirthYear is not known.
          $birthYear = '1000';
      } else {
          $birthYear = $per_BirthYear;
      }

      if ($per_BirthMonth > 0 && $per_BirthDay > 0 && $birthYear != 1000) {
          if ($per_BirthMonth < 10) {
              $dBirthMonth = '0'.$per_BirthMonth;
          } else {
              $dBirthMonth = $per_BirthMonth;
          }
          if ($per_BirthDay < 10) {
              $dBirthDay = '0'.$per_BirthDay;
          } else {
              $dBirthDay = $per_BirthDay;
          }

          $dBirthDate = $dBirthMonth.$sSeparator.$dBirthDay;
          if (is_numeric($birthYear)) {
              $dBirthDate = $birthYear.$sSeparator.$dBirthDate;
              if (checkdate($dBirthMonth, $dBirthDay, $birthYear)) {
                  $dBirthDate = self::FormatDate($dBirthDate);
                  if (mb_substr($dBirthDate, -6, 6) == ', 1000') {
                      $dBirthDate = str_replace(', 1000', '', $dBirthDate);
                  }
              }
          }
      } elseif (is_numeric($birthYear) && $birthYear != 1000) {  //Person Would Like Their Age Hidden
          $dBirthDate = $birthYear;
      } else {
          $dBirthDate = '';
      }

      return $dBirthDate;
  }
  
  public static function BirthDate($year, $month, $day, $hideAge)
  {
      if (!is_null($day) && $day != '' &&
      !is_null($month) && $month != ''
    ) {
          $birthYear = $year;
          if ($hideAge) {
              $birthYear = 1900;
          }

          return date_create($birthYear.'-'.$month.'-'.$day);
      }

      return date_create();
  }
  
  public 

// Added for AddEvent.php
function createTimeDropdown($start, $stop, $mininc, $hoursel, $minsel)
{ 

    $sTimeEnglish = SystemConfig::getValue("sTimeEnglish");

    for ($hour = $start; $hour <= $stop; $hour++) {
        if ($hour == '0') {
            $disphour = '12';
            $ampm = 'AM';
        } elseif ($hour == '12') {
            $disphour = '12';
            $ampm = 'PM';
        } elseif ($hour >= '13' && $hour <= '21' && $sTimeEnglish == true) {
            $test = $hour - 12;
            $disphour = ' '.$test;
            $ampm = 'PM';
        } elseif ($hour >= '22' && $hour <= '23' && $sTimeEnglish == true) {
            $disphour = $hour - 12;
            $ampm = 'PM';
        } else {
            $disphour = $hour;
            $ampm = 'AM';
        }
        
        if ($sTimeEnglish == false) {
            $ampm = "";
        }

        for ($min = 0; $min <= 59; $min += $mininc) {
            if ($hour >= '1' && $hour <= '9') {
                if ($min >= '0' && $min <= '9') {
                    if ($hour == $hoursel && $min == $minsel) {
                        echo '<option value="0'.$hour.':0'.$min.':00" selected> '.$disphour.':0'.$min.' '.$ampm.'</option>'."\n";
                    } else {
                        echo '<option value="0'.$hour.':0'.$min.':00"> '.$disphour.':0'.$min.' '.$ampm.'</option>'."\n";
                    }
                } else {
                    if ($hour == $hoursel && $min == $minsel) {
                        echo '<option value="0'.$hour.':'.$min.':00" selected> '.$disphour.':'.$min.' '.$ampm.'</option>'."\n";
                    } else {
                        echo '<option value="0'.$hour.':'.$min.':00"> '.$disphour.':'.$min.' '.$ampm.'</option>'."\n";
                    }
                }
            } else {
                if ($min >= '0' && $min <= '9') {
                    if ($hour == $hoursel && $min == $minsel) {
                        echo '<option value="'.$hour.':0'.$min.':00" selected>'.$disphour.':0'.$min.' '.$ampm.'</option>'."\n";
                    } else {
                        echo '<option value="'.$hour.':0'.$min.':00">'.$disphour.':0'.$min.' '.$ampm.'</option>'."\n";
                    }
                } else {
                    if ($hour == $hoursel && $min == $minsel) {
                        echo '<option value="'.$hour.':'.$min.':00" selected>'.$disphour.':'.$min.' '.$ampm.'</option>'."\n";
                    } else {
                        echo '<option value="'.$hour.':'.$min.':00">'.$disphour.':'.$min.' '.$ampm.'</option>'."\n";
                    }
                }
            }
        }
    }
  }
}

?>