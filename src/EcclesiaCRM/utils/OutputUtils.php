<?php

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemConfig;

class OutputUtils {

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
}

?>