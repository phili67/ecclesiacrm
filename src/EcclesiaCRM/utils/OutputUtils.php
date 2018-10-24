<?php

/* Philippe Logel */

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemConfig;

class OutputUtils {

  public static function translate_currency_fpdf($string)
  {
    if ($string == "€")
      return chr(128)." ";
    if ($string == "£")
      return chr(163)." ";
    
    return $string;  
  }
  
  public static function translate_text_fpdf($string)
  {
    if (!empty($string))
      return utf8_decode($string);//iconv('UTF-8', 'windows-1252', gettext($string));
    
    return "";
  }
  
  
  // Wrapper for number_format that uses the locale information
  // There are three modes: money, integer, and intmoney (whole number money)
  public function formatNumber($iNumber, $sMode = 'integer',$currency_vis=false)
  {
      //$aLocaleInfo = localeconv();
      global $aLocaleInfo;
      
      $currency = $aLocaleInfo['currency_symbol'];
      
      if ($currency == '') {
         $currency = '$';
      }

      switch ($sMode) {
      case 'money':
        return ($currency_vis?$currency:'').' '.number_format($iNumber, 2, $aLocaleInfo['decimal_point'], $aLocaleInfo['mon_thousands_sep']);
        break;

      case 'intmoney':
        return ($currency_vis?$currency:'').' '.number_format($iNumber, 0, '', $aLocaleInfo['mon_thousands_sep']);
        break;

      case 'float':
        $iDecimals = 2; // need to calculate # decimals in original number
        return number_format($iNumber, $iDecimals, $aLocaleInfo['decimal_point'], $aLocaleInfo['mon_thousands_sep']);
        break;

      case 'integer':
      default:
        return number_format($iNumber, 0, '', $aLocaleInfo['mon_thousands_sep']);
        break;
    }
  }

  
  public function money_localized ($number)
  {
    return OutputUtils::formatNumber($number,'money');
  }
  
  public function number_localized($number)
  {
    return OutputUtils::formatNumber($number,'float');
  }
  
  public function number_dot ($number)
  {
    return str_replace(",",".",$number);
  }  
  
  public function securityFilter($fieldSec)
  {
    switch ($fieldSec) {
      case 1: // bAll
        return true;
        break;
      case 2: // bAdmin
        if ($_SESSION['user']->isAdmin()) {
          return true;
        }
        break;
      case 3: // bAddRecords        
        if ($_SESSION['user']->isAddRecordsEnabled()) {
          return true;
        }
        break;
      case 4: // bEditRecords        
        if ($_SESSION['user']->isEditRecordsEnabled()) {
          return true;
        }
        break;
      case 5: // bDeleteRecords        
        if ($_SESSION['user']->isDeleteRecordsEnabled()) {
          return true;
        }
        break;
      case 6: // bMenuOptions        
        if ($_SESSION['user']->isMenuOptionsEnabled()) {
          return true;
        }
        break;
      case 7: // bManageGroups        
        if ($_SESSION['user']->isManageGroupsEnabled()) {
          return true;
        }
        break;
      case 8: // bFinance        
        if ($_SESSION['user']->isFinanceEnabled()) {
          return true;
        }
        break;
      case 9: // bNotes        
        if ($_SESSION['user']->isNotesEnabled()) {
          return true;
        }
        break;
      /*case 10: // bCommunication        
        if ($_SESSION['user']->isNotesEnabled()) {
          return true;
        }
        break;*/
      case 11: // bCanvasser        
        if ($_SESSION['user']->isCanvasserEnabled()) {
          return true;
        }
        break;
    }
        
    return false;
  }
  
  public static function convertCurrency($cur) 
  {
    define('EURO', chr(128));
    
    switch ($cur) {
      case "€":
        $cur = "euro";//.EURO;
        break;
    }
    
    return $cur;
  }
    
  //
  // Formats the data for a custom field for display-only uses
  //
  public static function displayCustomField($type, $data, $special,$with_link=true,$cvs_create=false)
  {
      global $cnInfoCentral;

      switch ($type) {
      // Handler for boolean fields
      case 1:
        if ($data == 'true') {
            return gettext('Yes');
        } elseif ($data == 'false') {
            return gettext('No');
        }
        break;

      // Handler for date fields
      case 2:
        if ($cvs_create) {
          return $data;
        }
        return OutputUtils::change_date_for_place_holder($data);
        break;
      // Handler for text fields, years, seasons, numbers
      case 3:
      case 4:
      case 6:
      case 8:
        return $data;
        break;
      case 10:
        if ($with_link) {
           return OutputUtils::number_localized($data)." ".SystemConfig::getValue("sCurrency");
        } else {
          return OutputUtils::number_localized($data)." ".OutputUtils::convertCurrency(SystemConfig::getValue("sCurrency"));
        }
        break;
      // Handler for extended text fields (MySQL type TEXT, Max length: 2^16-1)
      case 5:
        /*if (strlen($data) > 100) {
            return mb_substr($data, 0, 100) . "...";
        }else{
            return $data;
        }
        */
        return $data;
        break;

      // Handler for season.  Capitalize the word for nicer display.
      case 7:
        if ($data != null) {
          return gettext(ucfirst(gettext($data)));
        } else {
          return gettext("None");
        }
        break;
      // Handler for "person from group"
      case 9:
        if ($data > 0) {
            $sSQL = 'SELECT per_FirstName, per_LastName FROM person_per WHERE per_ID ='.$data;
            $rsTemp = RunQuery($sSQL);
            extract(mysqli_fetch_array($rsTemp));
            if ($with_link) {
              return '<a target="_top" href="PersonView.php?PersonID='.$data.'">'.$per_FirstName.' '.$per_LastName.'</a>';
            } else {
              return $per_FirstName.' '.$per_LastName;
            }
        } else {
            return '';
        }
        break;

      // Handler for phone numbers
      case 11:
        if ($with_link) {
          return '<a href="tel:'.$data.'">'.ExpandPhoneNumber($data, $special, $dummy).'</a>';
        } else {
          return ExpandPhoneNumber($data, $special, $dummy);
        }
        break;

      // Handler for custom lists
      case 12:
        if ($data > 0) {
            $sSQL = "SELECT lst_OptionName FROM list_lst WHERE lst_ID = $special AND lst_OptionID = $data";
            $rsTemp = RunQuery($sSQL);
            extract(mysqli_fetch_array($rsTemp));

            return $lst_OptionName;
        } else {
            return '';
        }
        break;

      // Otherwise, display error for debugging.
      default:
        return gettext('Invalid Editor ID!');
        break;
    }
  }

  
  //
  // Generates an HTML form <input> line for a custom field
  //

  public static function formCustomField($type, $fieldname, $data, $special, $bFirstPassFlag)
  {
    switch ($type) {
    // Handler for boolean fields
    case 1:
      echo '<div class="form-group">'.
        '<div class="radio"><label><input type="radio" Name="'.$fieldname.'" value="true"'.($data == 'true' ? 'checked' : '').'>'.gettext('Yes').'</label></div>'.
        '<div class="radio"><label><input type="radio" Name="'.$fieldname.'" value="false"'.($data == 'false' ? 'checked' : '').'>'.gettext('No').'</label></div>'.
        '<div class="radio"><label><input type="radio" Name="'.$fieldname.'" value=""'.(strlen($data) == 0 ? 'checked' : '').'>'.gettext('Unknown').'</label></div>'.
        '</div>';
      break;
    // Handler for date fields
    case 2:
        // code rajouté par Philippe Logel
      echo '<div class="input-group">'.
        '<div class="input-group-addon">'.
        '<i class="fa fa-calendar"></i>'.
        '</div>'.
        '<input class="form-control date-picker" type="text" id="'.$fieldname.'" Name="'.$fieldname.'" value="'.OutputUtils::change_date_for_place_holder($data).'" placeholder="'.SystemConfig::getValue("sDatePickerPlaceHolder").'"> '.
        '</div>';
      break;

    // Handler for 50 character max. text fields
    case 3:
      echo '<input class="form-control" type="text" Name="'.$fieldname.'" maxlength="50" size="50" value="'.htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8').'">';
      break;

    // Handler for 100 character max. text fields
    case 4:
      echo '<textarea class="form-control" Name="'.$fieldname.'" cols="40" rows="2" onKeyPress="LimitTextSize(this, 100)">'.htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8').'</textarea>';
      break;

    // Handler for extended text fields (MySQL type TEXT, Max length: 2^16-1)
    case 5:
      echo '<textarea class="form-control" Name="'.$fieldname.'" cols="60" rows="4" onKeyPress="LimitTextSize(this, 65535)">'.htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8').'</textarea>';
      break;

    // Handler for 4-digit year
    case 6:
      echo '<input class="form-control" type="text" Name="'.$fieldname.'" maxlength="4" size="6" value="'.$data.'">';
      break;

    // Handler for season (drop-down selection)
    case 7:
      echo "<select name=\"$fieldname\" class=\"form-control input-sm\" >";
      echo '  <option value="none">'.gettext('Select Season').'</option>';
      echo '  <option value="winter"';
      if ($data == 'winter') {
          echo ' selected';
      }
      echo '>'.gettext('Winter').'</option>';
      echo '  <option value="spring"';
      if ($data == 'spring') {
          echo ' selected';
      }
      echo '>'.gettext('Spring').'</option>';
      echo '  <option value="summer"';
      if ($data == 'summer') {
          echo 'selected';
      }
      echo '>'.gettext('Summer').'</option>';
      echo '  <option value="fall"';
      if ($data == 'fall') {
          echo ' selected';
      }
      echo '>'.gettext('Fall').'</option>';
      echo '</select>';
      break;

    // Handler for integer numbers
    case 8:
      echo '<input class="form-control" type="text" Name="'.$fieldname.'" maxlength="11" size="15" value="'.$data.'">';
      break;

    // Handler for "person from group"
    case 9:
      if (!empty($special)) {
        // ... Get First/Last name of everyone in the group, plus their person ID ...
        // In this case, prop_Special is used to store the Group ID for this selection box
        // This allows the group special-property designer to allow selection from a specific group

        $sSQL = 'SELECT person_per.per_ID, person_per.per_FirstName, person_per.per_LastName
                          FROM person2group2role_p2g2r
                          LEFT JOIN person_per ON person2group2role_p2g2r.p2g2r_per_ID = person_per.per_ID
                          WHERE p2g2r_grp_ID = '.$special.' ORDER BY per_FirstName';

        $rsGroupPeople = RunQuery($sSQL);

        echo '<select name="'.$fieldname.'" class="form-control input-sm" >';
        echo '<option value="0"';
        if ($data <= 0) {
            echo ' selected';
        }
        echo '>'.gettext('Unassigned').'</option>';
        echo '<option value="0">-----------------------</option>';

        while ($aRow = mysqli_fetch_array($rsGroupPeople)) {
            extract($aRow);

            echo '<option value="'.$per_ID.'"';
            if ($data == $per_ID) {
                echo ' selected';
            }
            echo '>'.$per_FirstName.'&nbsp;'.$per_LastName.'</option>';
        }

        echo '</select>';
      } else {
        echo gettext("This custom field isn't configured correctly");
      }
      break;

    // Handler for money amounts
    case 10:
      echo '<table width=100%><tr><td><input class="form-control"  type="number" step="any" Name="'.$fieldname.'" maxlength="13" size="16" value="'.$data.'"></td><td>&nbsp;'.SystemConfig::getValue("sCurrency")."</td></tr></table>";
      break;

    // Handler for phone numbers
    case 11:

      // This is silly. Perhaps ExpandPhoneNumber before this function is called!
      // this business of overloading the special field is really troublesome when trying to follow the code.
      if ($bFirstPassFlag) {
          // in this case, $special is the phone country
          $data = ExpandPhoneNumber($data, $special, $bNoFormat_Phone);
      }
      if (isset($_POST[$fieldname.'noformat'])) {
          $bNoFormat_Phone = true;
      }

            echo '<div class="input-group">';
      echo '<div class="input-group-addon">';
      echo '<i class="fa fa-phone"></i>';
      echo '</div>';
      echo '<input class="form-control"  type="text" Name="'.$fieldname.'" maxlength="30" size="30" value="'.htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8').'" data-inputmask="\'mask\': \''.SystemConfig::getValue('sPhoneFormat').'\'" data-mask>';
      echo '<br><input type="checkbox" name="'.$fieldname.'noformat" value="1"';
      if ($bNoFormat_Phone) {
          echo ' checked';
      }
      echo '>'.gettext('Do not auto-format');
      echo '</div>';
      break;

    // Handler for custom lists
    case 12:
      $sSQL = "SELECT * FROM list_lst WHERE lst_ID = $special ORDER BY lst_OptionSequence";
      $rsListOptions = RunQuery($sSQL);
      
      echo '<select class="form-control input-sm" name="'.$fieldname.'">';
      echo '<option value="0" selected>'.gettext('Unassigned').'</option>';
      echo '<option value="0">-----------------------</option>';

      while ($aRow = mysqli_fetch_array($rsListOptions)) {
          extract($aRow);
          echo '<option value="'.$lst_OptionID.'"';
          if ($data == $lst_OptionID) {
              echo ' selected';
          }
          echo '>'.$lst_OptionName.'</option>';
      }

      echo '</select>';
      break;

    // Otherwise, display error for debugging.
    default:
      echo '<b>'.gettext('Error: Invalid Editor ID!').'</b>';
      break;
  }
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

    $bTimeEnglish = SystemConfig::getValue("bTimeEnglish");

    for ($hour = $start; $hour <= $stop; $hour++) {
        if ($hour == '0') {
            $disphour = '12';
            $ampm = 'AM';
        } elseif ($hour == '12') {
            $disphour = '12';
            $ampm = 'PM';
        } elseif ($hour >= '13' && $hour <= '21' && $bTimeEnglish == true) {
            $test = $hour - 12;
            $disphour = ' '.$test;
            $ampm = 'PM';
        } elseif ($hour >= '22' && $hour <= '23' && $bTimeEnglish == true) {
            $disphour = $hour - 12;
            $ampm = 'PM';
        } else {
            $disphour = $hour;
            $ampm = 'AM';
        }
        
        if ($bTimeEnglish == false) {
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