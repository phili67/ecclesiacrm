<?php

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use \DateTime;

class InputUtils {

  private static $AllowedHTMLTags = '<a><b><i><u><h1><h2><h3><h4><h5><h6><pre><address><img><table><td><tr><ol><li><ul><p><sub><sup><s><hr><span><blockquote><div><small><big><tt><code><kbd><samp><del><ins><cite><q><iframe><caption><thead><th><video>';//<input>';

  // Processes and Validates custom field data based on its type.
  //
  // Returns false if the data is not valid, true otherwise.
  //
  public static function validateCustomField($type, &$data, $col_Name, &$aErrors)
  {
      $aLocaleInfo = Bootstrapper::getRealLocalInfo();

      $bErrorFlag = false;
      $aErrors[$col_Name] = '';

      switch ($type) {
      // Validate a date field
      case 2:
          // this part will work with each date format
          // Philippe logel
          $data = InputUtils::FilterDate($data);

        if (strlen($data) > 0) {
            $dateString = InputUtils::parseAndValidateDate($data);
            if ($dateString === false) {
                $aErrors[$col_Name] = _('Not a valid date');
                $bErrorFlag = true;
            } else {
                $data = $dateString;
            }
        }
        break;

      // Handler for 4-digit year
      case 6:
        if (strlen($data) != 0) {
            if (!is_numeric($data) || strlen($data) != 4) {
                $aErrors[$col_Name] = _('Invalid Year');
                $bErrorFlag = true;
            } elseif ($data > 2155 || $data < 1901) {
                $aErrors[$col_Name] = _('Out of range: Allowable values are 1901 to 2155');
                $bErrorFlag = true;
            }
        }
        break;

      // Handler for integer numbers
      case 8:
        if (strlen($data) != 0) {
            if ($aLocaleInfo['thousands_sep']) {
                $data = preg_replace('/'.$aLocaleInfo['thousands_sep'].'/i', '', $data);  // remove any thousands separators
            }
            if (!is_numeric($data)) {
                $aErrors[$col_Name] = _('Invalid Number');
                $bErrorFlag = true;
            } elseif ($data < -2147483648 || $data > 2147483647) {
                $aErrors[$col_Name] = _('Number too large. Must be between -2147483648 and 2147483647');
                $bErrorFlag = true;
            }
        }
        break;

      // Handler for money amounts
      case 10:
        if (strlen($data) != 0) {
            if ($aLocaleInfo['mon_thousands_sep']) {
                $data = preg_replace('/'.$aLocaleInfo['mon_thousands_sep'].'/i', '', $data);
            }
            if (!is_numeric($data)) {
                $aErrors[$col_Name] = _('Invalid Number');
                $bErrorFlag = true;
            } elseif ($data > 999999999.99) {
                $aErrors[$col_Name] = _('Money amount too large. Maximum is $999999999.99');
                $bErrorFlag = true;
            }
        }
        break;

      // Otherwise ignore.. some types do not need validation or filtering
      default:
        break;
    }

      return !$bErrorFlag;
  }

  public static function LegacyFilterInputArr($arr, $key, $type = 'string', $size = 1)
  {
      if (array_key_exists($key, $arr)) {
          return InputUtils::LegacyFilterInput($arr[$key], $type, $size);
      } else {
          return InputUtils::LegacyFilterInput('', $type, $size);
      }
  }

  public static function assembleYearMonthDay($sYear, $sMonth, $sDay, $pasfut = 'future')
  {
    // This function takes a year, month and day from parseAndValidateDate.  On success this
    // function returns a string in the form "YYYY-MM-DD".  It returns FALSE on failure.
    // The year can be either 2 digit or 4 digit.  If a 2 digit year is passed the $passfut
    // indicates whether to return a 4 digit year in the past or the future.  The parameter
    // $passfut is not needed for the current year.  If unspecified it assumes the two digit year
    // is either this year or one of the next 99 years.

    // Parse the year
    // Take a 2 or 4 digit year and return a 4 digit year.  Use $pasfut to determine if
    // two digit year maps to past or future 4 digit year.
    if (strlen($sYear) == 2) {
        $thisYear = date('Y');
        $twoDigit = mb_substr($thisYear, 2, 2);
        if ($sYear == $twoDigit) {
            // Assume 2 digit year is this year
            $sYear = mb_substr($thisYear, 0, 4);
        } elseif ($pasfut == 'future') {
            // Assume 2 digit year is in next 99 years
            if ($sYear > $twoDigit) {
                $sYear = mb_substr($thisYear, 0, 2).$sYear;
            } else {
                $sNextCentury = $thisYear + 100;
                $sYear = mb_substr($sNextCentury, 0, 2).$sYear;
            }
        } else {
            // Assume 2 digit year was is last 99 years
            if ($sYear < $twoDigit) {
                $sYear = mb_substr($thisYear, 0, 2).$sYear;
            } else {
                $sLastCentury = $thisYear - 100;
                $sYear = mb_substr($sLastCentury, 0, 2).$sYear;
            }
        }
    } elseif (strlen($sYear) == 4) {
        $sYear = $sYear;
    } else {
        return false;
    }

    // Parse the Month
    // Take a one or two character month and return a two character month
    if (strlen($sMonth) == 1) {
        $sMonth = '0'.$sMonth;
    } elseif (strlen($sMonth) == 2) {
        $sMonth = $sMonth;
    } else {
        return false;
    }

    // Parse the Day
    // Take a one or two character day and return a two character day
    if (strlen($sDay) == 1) {
        $sDay = '0'.$sDay;
    } elseif (strlen($sDay) == 2) {
        $sDay = $sDay;
    } else {
        return false;
    }

    $sScanString = $sYear.'-'.$sMonth.'-'.$sDay;
    list($iYear, $iMonth, $iDay) = sscanf($sScanString, '%04d-%02d-%02d');

    if (checkdate($iMonth, $iDay, $iYear)) {
        return $sScanString;
    } else {
        return false;
    }
}

  public static function parseAndValidateDate($data, $locale = 'US', $pasfut = 'future')
  {
    // This function was written because I had no luck finding a PHP
    // function that would reliably parse a human entered date string for
    // dates before 1/1/1970 or after 1/19/2038 on any Operating System.
    //
    // This function has hooks for US English M/D/Y format as well as D/M/Y.  The
    // default is M/D/Y for date.  To change to D/M/Y use anything but "US" for
    // $locale.
    //
    // Y-M-D is allowed if the delimiter is "-" instead of "/"
    //
    // In order to help this function guess a two digit year a "past" or "future" flag is
    // passed to this function.  If no flag is passed the function assumes that two digit
    // years are in the future (or the current year).
    //
    // Month and day may be either 1 character or two characters (leading zeroes are not
    // necessary)

    // Determine if the delimiter is "-" or "/".  The delimiter must appear
    // twice or a FALSE will be returned.

    if (mb_substr_count($data, '-') == 2) {
        // Assume format is Y-M-D
        $iFirstDelimiter = strpos($data, '-');
        $iSecondDelimiter = strpos($data, '-', $iFirstDelimiter + 1);

        // Parse the year.
        $sYear = mb_substr($data, 0, $iFirstDelimiter);

        // Parse the month
        $sMonth = mb_substr($data, $iFirstDelimiter + 1, $iSecondDelimiter - $iFirstDelimiter - 1);

        // Parse the day
        $sDay = mb_substr($data, $iSecondDelimiter + 1);

        // Put into YYYY-MM-DD form
        return InputUtils::assembleYearMonthDay($sYear, $sMonth, $sDay, $pasfut);
    } elseif ((mb_substr_count($data, '/') == 2) && ($locale == 'US')) {
        // Assume format is M/D/Y
        $iFirstDelimiter = strpos($data, '/');
        $iSecondDelimiter = strpos($data, '/', $iFirstDelimiter + 1);

        // Parse the month
        $sMonth = mb_substr($data, 0, $iFirstDelimiter);

        // Parse the day
        $sDay = mb_substr($data, $iFirstDelimiter + 1, $iSecondDelimiter - $iFirstDelimiter - 1);

        // Parse the year
        $sYear = mb_substr($data, $iSecondDelimiter + 1);

        // Put into YYYY-MM-DD form
        return InputUtils::assembleYearMonthDay($sYear, $sMonth, $sDay, $pasfut);
    } elseif (mb_substr_count($data, '/') == 2) {
        // Assume format is D/M/Y
        $iFirstDelimiter = strpos($data, '/');
        $iSecondDelimiter = strpos($data, '/', $iFirstDelimiter + 1);

        // Parse the day
        $sDay = mb_substr($data, 0, $iFirstDelimiter);

        // Parse the month
        $sMonth = mb_substr($data, $iFirstDelimiter + 1, $iSecondDelimiter - $iFirstDelimiter - 1);

        // Parse the year
        $sYear = mb_substr($data, $iSecondDelimiter + 1);

        // Put into YYYY-MM-DD form
        return InputUtils::assembleYearMonthDay($sYear, $sMonth, $sDay, $pasfut);
    }

    // If we made it this far it means the above logic was unable to parse the date.
    // Now try to parse using the function strtotime().  The strtotime() function does
    // not gracefully handle dates outside the range 1/1/1970 to 1/19/2038.  For this
    // reason consider strtotime() as a function of last resort.
    $timeStamp = strtotime($data);
    if ($timeStamp == false || $timeStamp <= 0) {
        // Some Operating Sytems and older versions of PHP do not gracefully handle
        // negative timestamps.  Bail if the timestamp is negative.
        return false;
    }

    // Now use the date() function to convert timestamp into YYYY-MM-DD
    $dateString = date('Y-m-d', $timeStamp);

    if (strlen($dateString) != 10) {
        // Common sense says we have a 10 charater string.  If not, something is wrong
        // and it's time to bail.
        return false;
    }

    if ($dateString > '1970-01-01' && $dateString < '2038-01-19') {
        // Success!
        return $dateString;
    }

    // Should not have made it this far.  Something is wrong so bail.
    return false;
  }

  public static function translate_special_charset ($string,$sCSVExportCharset = "UTF-8")
  {
    if (empty($string))
      return "";

    return ($sCSVExportCharset == "UTF-8")?gettext($string):iconv('UTF-8', $sCSVExportCharset, gettext($string));
  }

  public static function FilterString($sInput)
  {
      // or use htmlspecialchars( stripslashes( ))
      $sInput = strip_tags(trim($sInput));
      if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        $sInput = stripslashes($sInput);
      }
      return $sInput;
  }

  public static function FilterStringNonUTF8($sInput)
  {
      $sInput = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $sInput);
      
      return $sInput;
  }

  public static function FilterHTML($sInput)
  {
      $sInput = strip_tags(trim($sInput), self::$AllowedHTMLTags);
      if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        $sInput = stripslashes($sInput);
      }
      return $sInput;
  }

  public static function FilterChar($sInput,$size=1)
  {
     $sInput = mb_substr(trim($sInput), 0, $size);
      if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        $sInput = stripslashes($sInput);
      }

      return $sInput;
  }

  public static function FilterInt($sInput)
  {
     return (int) intval(trim($sInput));
  }

  public static function FilterFloat($sInput)
  {
    $sInput = str_replace("," , ".", $sInput);

    return (float) floatval(trim($sInput));
  }

  public static function FilterDate($sInput)
  {
    // Attempts to take a date in any format and convert it to YYYY-MM-DD format
    // Logel Philippe
    if (empty($sInput))
      return "1900-01-01";
    else {
      $date = DateTime::createFromFormat(SystemConfig::getValue('sDatePickerFormat'), $sInput);
      if ($date != false) {
          return $date->format('Y-m-d');
      } else {
          return null;
      }
    }
  }

  // Sanitizes user input as a security measure
  // Optionally, a filtering type and size may be specified.  By default, strip any tags from a string.
  // Note that a database connection must already be established for the mysqli_real_escape_string function to work.
  public static function LegacyFilterInput($sInput, $type = 'string', $size = 1)
  {
    global $cnInfoCentral;
    if (strlen($sInput) > 0) {
      switch ($type) {
        case 'string':
          return mysqli_real_escape_string($cnInfoCentral, self::FilterString($sInput));
        case 'htmltext':
          return mysqli_real_escape_string($cnInfoCentral, self::FilterHTML($sInput));
        case 'char':
          return mysqli_real_escape_string($cnInfoCentral, self::FilterChar($sInput,$size));
        case 'int':
         return self::FilterInt($sInput);
        case 'float':
          return self::FilterFloat($sInput);
        case 'date':
          return self::FilterDate($sInput);
      }
    }
    else {
      return '';
    }
  }
}
?>
