<?php

/* Philippe Logel */

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use Propel\Runtime\Propel;

class OutputUtils
{

    public static function GetLinkMapFromAddress($address)
    {
        if (SessionUser::getUser()->MapExternalProvider() == "AppleMaps") {
            return '<a href="http://maps.apple.com/?q=' . $address . '&z=' . SystemConfig::getValue("iLittleMapZoom") . '" target="_blank">' . $address . '</a>';
        } elseif (SessionUser::getUser()->MapExternalProvider() == "GoogleMaps") {
            return '<a href="http://maps.google.com/?q=' . $address . '" target="_blank">' . $address . '</a>';
        } elseif (SessionUser::getUser()->MapExternalProvider() == "BingMaps") {
            return '<a href="https://www.bing.com/maps?where1=' . $address . '&sty=c" target="_blank">' . $address . '</a>';
        }
    }

    public static function GetLinkMapFromCoordinates($lat, $lng, $address)
    {
        if (SessionUser::getUser()->MapExternalProvider() == "AppleMaps") {
            return '<a href="http://maps.apple.com/?daddr=' . $lat . ',' . $lng . '&z=' . SystemConfig::getValue("iLittleMapZoom") . '"  target="_blank">' . $address . '</a>';
        } elseif (SessionUser::getUser()->MapExternalProvider() == "GoogleMaps") {
            return '<a href="http://maps.google.com/maps?q=' . $lat . ',' . $lng . '" target="_blank">' . $address . '</a>';
        } elseif (SessionUser::getUser()->MapExternalProvider() == "BingMaps") {
            //return '<a href="https://www.bing.com/maps?cp=' . $lat . '~' . $lng . '&lvl='. SystemConfig::getValue("iLittleMapZoom"). '&style=c" target="_blank">'.$address.'</a>';
            return '<a href="https://www.bing.com/maps?where1=' . $address . '&sty=c" target="_blank">' . $address . '</a>';
        }
    }

    public static function GetRouteFromCoordinates($lat_to, $lng_to)
    {
        if (SessionUser::getUser()->MapExternalProvider() == "AppleMaps") {
            return '<a href="http://maps.apple.com/?daddr=' . $lat_to . ',' . $lng_to . '&z=' . SystemConfig::getValue("iLittleMapZoom") . '"  target="_blank">' . _('Direct me') . '</a>';
        } elseif (SessionUser::getUser()->MapExternalProvider() == "GoogleMaps") {
            return '<a target="_blank" href="https://www.google.com/maps/dir/Current+Location/' . $lat_to . ',' . $lng_to . '">' . _('Direct me') . '</a>';
        } elseif (SessionUser::getUser()->MapExternalProvider() == "BingMaps") {
            //return '<a href="https://www.bing.com/maps?cp=' . $lat . '~' . $lng . '&lvl='. SystemConfig::getValue("iLittleMapZoom"). '&style=c" target="_blank">'.$address.'</a>';
            /*
           https://www.bing.com/maps?Rtp=adr.Seattle,WA~adr.One%20Microsoft%20Way,Redmond,WA
           https://www.bing.com/maps?Rtp=~adr.One%20Microsoft%20Way,Redmond,WA
           https://www.bing.com/maps?rtp=pos.42.2_-122.3~pos.55.2_-127.0
            */
            return '<a href="https://www.bing.com/maps?Rtp=~pos.' . $lat_to . '_' . $lng_to . '" target="_blank">' . _('Direct me') . '</a>';
        }
    }


    // Wrapper for number_format that uses the locale information
    // There are three modes: money, integer, and intmoney (whole number money)
    public static function formatNumber($iNumber, $sMode = 'integer', $currency_vis = false)
    {
        $aLocaleInfo = Bootstrapper::getRealLocalInfo();

        $currency = $aLocaleInfo['currency_symbol'];;

        if ($currency == '') {
            $currency = '$';
        }

        $res = "";
        switch ($sMode) {
            case 'money':
                $res =  ($currency_vis ? $currency : '') . ' ' . number_format((float)$iNumber, 2, $aLocaleInfo['decimal_point'], $aLocaleInfo['mon_thousands_sep']);
                break;
            case 'intmoney':
                $res =  ($currency_vis ? $currency : '') . ' ' . number_format((float)$iNumber, 0, '', $aLocaleInfo['mon_thousands_sep']);
                break;

            case 'float':
                $iDecimals = 2; // need to calculate # decimals in original number
                $res = number_format((float)$iNumber, $iDecimals, $aLocaleInfo['decimal_point'], $aLocaleInfo['mon_thousands_sep']);
                break;

            case 'integer':
            default:
                $res = number_format((float)$iNumber, 0, '', $aLocaleInfo['mon_thousands_sep']);
                break;
        }

        return $res;
    }


    public static function money_localized($number)
    {
        return OutputUtils::formatNumber($number, 'money');
    }

    public static function number_localized($number)
    {
        return OutputUtils::formatNumber($number, 'float');
    }

    public static function number_dot($number)
    {
        if ($number == NULL) return 0;

        return str_replace(",", ".", "'".$number."'");
    }

    public static function securityFilter($fieldSec)
    {
        switch ($fieldSec) {
            case 1: // bAll
                return true;
                break;
            case 2: // bAdmin
                if (SessionUser::getUser()->isAdmin()) {
                    return true;
                }
                break;
            case 3: // bAddRecords
                if (SessionUser::getUser()->isAddRecordsEnabled()) {
                    return true;
                }
                break;
            case 4: // bEditRecords
                if (SessionUser::getUser()->isEditRecordsEnabled()) {
                    return true;
                }
                break;
            case 5: // bDeleteRecords
                if (SessionUser::getUser()->isDeleteRecordsEnabled()) {
                    return true;
                }
                break;
            case 6: // bMenuOptions
                if (SessionUser::getUser()->isMenuOptionsEnabled()) {
                    return true;
                }
                break;
            case 7: // bManageGroups
                if (SessionUser::getUser()->isManageGroupsEnabled()) {
                    return true;
                }
                break;
            case 8: // bFinance
                if (SessionUser::getUser()->isFinanceEnabled()) {
                    return true;
                }
                break;
            case 9: // bNotes
                if (SessionUser::getUser()->isNotesEnabled()) {
                    return true;
                }
                break;
            /*case 10: // bCommunication
              if (SessionUser::getUser()->isNotesEnabled()) {
                return true;
              }
              break;*/
            case 11: // bCanvasser
                if (SessionUser::getUser()->isCanvasserEnabled()) {
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
    public static function displayCustomField($type, $data, $special, $with_link = true, $cvs_create = false)
    {
        global $cnInfoCentral;

        switch ($type) {
            // Handler for boolean fields
            case 1:
                if ($data == 'true') {
                    return _('Yes');
                } elseif ($data == 'false') {
                    return _('No');
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
                    return OutputUtils::number_localized($data) . " " . SystemConfig::getValue("sCurrency");
                } else {
                    return OutputUtils::number_localized($data) . " " . OutputUtils::convertCurrency(SystemConfig::getValue("sCurrency"));
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
                    return _(ucfirst(_($data)));
                } else {
                    return _("None");
                }
                break;
            // Handler for "person from group"
            case 9:
                if ($data > 0) {
                    $person = PersonQuery::Create()->findOneById($data);
                    if (is_null($person)) {
                        return '';
                    }
                    if ($with_link) {
                        return '<a target="_top" href="v2/people/person/view/' . $data . '">' . $person->getFirstName() . ' ' . $person->getLastName() . '</a>';
                    } else {
                        return $person->getFirstName() . ' ' . $person->getLastName();
                    }
                } else {
                    return '';
                }
                break;

            // Handler for phone numbers
            case 11:
                if ($with_link) {
                    return '<a href="tel:' . $data . '">' . MiscUtils::ExpandPhoneNumber($data, $special, $dummy) . '</a>';
                } else {
                    return MiscUtils::ExpandPhoneNumber($data, $special, $dummy);
                }
                break;

            // Handler for custom lists
            case 12:
                if ($data > 0) {
                    $list = ListOptionQuery::Create()->filterById($special)->findOneByOptionId($data);
                    return $list->getOptionName();
                } else {
                    return '';
                }
                break;

            // Otherwise, display error for debugging.
            default:
                return _('Invalid Editor ID!');
                break;
        }
    }


    //
    // Generates an HTML form <input> line for a custom field
    //

    public static function formCustomField($type, $fieldname, $data, $special, $bFirstPassFlag=true)
    {
        switch ($type) {
            // Handler for boolean fields
            case 1:
                echo '<div class="form-group">' .
                    '<div class="radio"><label><input type="radio" Name="' . $fieldname . '" value="true"' . ($data == 'true' ? 'checked' : '') . '>' . _('Yes') . '</label></div>' .
                    '<div class="radio"><label><input type="radio" Name="' . $fieldname . '" value="false"' . ($data == 'false' ? 'checked' : '') . '>' . _('No') . '</label></div>' .
                    '<div class="radio"><label><input type="radio" Name="' . $fieldname . '" value=""' . (($data == "null" or $data == "Null" or $data == "NULL") ? 'checked' : '') . '>' . _('Unknown') . '</label></div>' .
                    '</div>';
                break;
            // Handler for date fields
            case 2:
                // code rajouté par Philippe Logel
                echo '<div class="input-group mb-2">' .
                    '<div class="input-group-prepend">' .
                    '<span class="input-group-text"> <i class="fas fa-calendar"></i></span>' .
                    '</div>' .
                    '<input class=" form-control  form-control-sm date-picker" type="text" id="' . $fieldname . '" Name="' . $fieldname . '" value="' . OutputUtils::change_date_for_place_holder($data) . '" placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '"> ' .
                    '</div>';
                break;

            // Handler for 50 character max. text fields
            case 3:
                echo '<input class= "form-control form-control-sm" type="text" Name="' . $fieldname . '" maxlength="50" size="50" value="' . htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8') . '">';
                break;

            // Handler for 100 character max. text fields
            case 4:
                echo '<textarea class= "form-control form-control-sm" Name="' . $fieldname . '" cols="40" rows="2" onKeyPress="LimitTextSize(this, 100)">' . htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8') . '</textarea>';
                break;

            // Handler for extended text fields (MySQL type TEXT, Max length: 2^16-1)
            case 5:
                echo '<textarea class= "form-control form-control-sm" Name="' . $fieldname . '" cols="60" rows="4" onKeyPress="LimitTextSize(this, 65535)">' . htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8') . '</textarea>';
                break;

            // Handler for 4-digit year
            case 6:
                echo '<input class= "form-control form-control-sm" type="text" Name="' . $fieldname . '" maxlength="4" size="6" value="' . $data . '">';
                break;

            // Handler for season (drop-down selection)
            case 7:
                echo "<select name=\"$fieldname\" class=\"form-control form-control-sm\" >";
                echo '  <option value="none">' . _('Select Season') . '</option>';
                echo '  <option value="winter"';
                if ($data == 'winter') {
                    echo ' selected';
                }
                echo '>' . _('Winter') . '</option>';
                echo '  <option value="spring"';
                if ($data == 'spring') {
                    echo ' selected';
                }
                echo '>' . _('Spring') . '</option>';
                echo '  <option value="summer"';
                if ($data == 'summer') {
                    echo 'selected';
                }
                echo '>' . _('Summer') . '</option>';
                echo '  <option value="fall"';
                if ($data == 'fall') {
                    echo ' selected';
                }
                echo '>' . _('Fall') . '</option>';
                echo '</select>';
                break;

            // Handler for integer numbers
            case 8:
                echo '<input class= "form-control form-control-sm" type="text" Name="' . $fieldname . '" maxlength="11" size="15" value="' . $data . '">';
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
                          WHERE p2g2r_grp_ID = ' . $special . ' AND per_DateDeactivated IS NULL ORDER BY per_FirstName';// GDPR per_DateDeactivated IS NULL

                    $connection = Propel::getConnection();
                    $statement = $connection->prepare($sSQL);
                    $statement->execute();

                    echo '<select name="' . $fieldname . '" class="form-control form-control-sm" >';
                    echo '<option value="0"';
                    if ($data <= 0) {
                        echo ' selected';
                    }
                    echo '>' . _('Unassigned') . '</option>';
                    echo '<option value="0">-----------------------</option>';

                    while ($aRow = $statement->fetch(\PDO::FETCH_BOTH)) {
                        extract($aRow);

                        echo '<option value="' . $per_ID . '"';
                        if ($data == $per_ID) {
                            echo ' selected';
                        }
                        echo '>' . $per_FirstName . '&nbsp;' . $per_LastName . '</option>';
                    }

                    echo '</select>';
                } else {
                    echo _("This custom field isn't configured correctly");
                }
                break;

            // Handler for money amounts
            case 10:
                echo '<table width=100%><tr><td><input class= "form-control form-control-sm"  type="number" step="any" Name="' . $fieldname . '" maxlength="13" size="16" value="' . $data . '"></td><td>&nbsp;' . SystemConfig::getValue("sCurrency") . "</td></tr></table>";
                break;

            // Handler for phone numbers
            case 11:

                // This is silly. Perhaps MiscUtils::ExpandPhoneNumber before this function is called!
                // this business of overloading the special field is really troublesome when trying to follow the code.
                if ($bFirstPassFlag) {
                    // in this case, $special is the phone country
                    $data = MiscUtils::ExpandPhoneNumber($data, $special, $bNoFormat_Phone);
                }
                if (isset($_POST[$fieldname . 'noformat'])) {
                    $bNoFormat_Phone = true;
                }

                echo '<div class="input-group mb-2">' .
                    '<div class="input-group-prepend">' .
                    '<span class="input-group-text"> <i class="fas fa-phone"></i></span>' .
                    '</div>' .
                    '<input class= "form-control form-control-sm"  type="text" Name="' . $fieldname . '" maxlength="30" size="30" value="' . htmlentities(stripslashes($data), ENT_NOQUOTES, 'UTF-8') . '" data-inputmask="\'mask\': \'' . SystemConfig::getValue('sPhoneFormat') . '\'" data-mask>' .
                    '</div>' .
                    '<input type="checkbox" name="' . $fieldname . 'noformat" value="1"';

                if ($bNoFormat_Phone) {
                    echo ' checked';
                }
                echo '>' . _('Do not auto-format');


                break;

            // Handler for custom lists
            case 12:
                // Get Field Security List Matrix
                $listOptions = ListOptionQuery::Create()
                    ->orderByOptionSequence()
                    ->findById($special);

                echo '<select class="form-control form-control-sm" name="' . $fieldname . '">';
                echo '<option value="0" selected>' . _('Unassigned') . '</option>';
                echo '<option value="0">-----------------------</option>';

                foreach ($listOptions as $listOption) {
                    echo '<option value="' . $listOption->getOptionId() . '"';
                    if ($data == $listOption->getOptionId()) {
                        echo ' selected';
                    }
                    echo '>' . $listOption->getOptionName() . '</option>';
                }

                echo '</select>';
                break;

            // Otherwise, display error for debugging.
            default:
                echo '<b>' . _('Error: Invalid Editor ID!') . '</b>';
                break;
        }
    }

    public static function change_date_for_place_holder($string)
    {
        if ($string == "1900-01-01") {
            $string = "";
        }
        return ((strtotime($string) != "") ? date(SystemConfig::getValue("sDatePickerFormat"), strtotime($string)) : strtotime($string));
    }

    public static function change_time_for_place_holder($string)
    {
        $bTimeEnglish = SystemConfig::getBooleanValue("bTimeEnglish");

        try {
            $d = new \DateTime($string);
        } catch (\Exception $e) {
            return "";
        }

        return $d->format('H:i');
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
            $fmt .= " " . $fmt_time;
        }

        return $fmt;
    }

    public static function FormatAge($Month, $Day, $Year, $Flags)
    {
        if (($Flags & 1)) { //||!SessionUser::getUser()->isSeePrivacyDataEnabled()
            return;
        }

        if ($Year > 0) {
            if ($Year == date('Y')) {
                $monthCount = date('m') - $Month;
                if ($Day > date('d')) {
                    $monthCount--;
                }
                if ($monthCount == 1) {
                    return _('1 m old');
                } else {
                    return $monthCount . ' ' . _('m old');
                }
            } elseif ($Year == date('Y') - 1) {
                $monthCount = 12 - $Month + date('m');
                if ($Day > date('d')) {
                    $monthCount--;
                }
                if ($monthCount >= 12) {
                    return _('1 yr old');
                } elseif ($monthCount == 1) {
                    return _('1 m old');
                } else {
                    return $monthCount . ' ' . _('m old');
                }
            } elseif ($Month > date('m') || ($Month == date('m') && $Day > date('d'))) {
                return date('Y') - 1 - $Year . ' ' . _('yrs old');
            } else {
                return date('Y') - $Year . ' ' . _('yrs old');
            }
        } else {
            return _('Unknown');
        }
    }

    public static function FormatAgeFromDate($birthDate)
    {
        return date_diff(date_create($birthDate), date_create('now'))->y;
    }

    //
    // Formats an age suffix: age in years, or in months if less than one year old
    //
    public static function FormatAgeSuffix($birthDate, $Flags)
    {
        if ($Flags == 1) {
            return '';
        }

        $ageSuffix = _('Unknown');

        $now = new \DateTime();
        $age = $now->diff($birthDate);

        if ($age->y < 1) {
            if ($age->m > 1) {
                $ageSuffix = _('mos old');
            } else {
                $ageSuffix = _('mo old');
            }
        } else {
            if ($age->y > 1) {
                $ageSuffix = _('yrs old');
            } else {
                $ageSuffix = _('yr old');
            }
        }

        return $ageSuffix;
    }

    // Reinstated by Todd Pillars for Event Listing
    // Takes MYSQL DateTime
    // bWithtime 1 to be displayed
    public static function FormatDate($dDate, $bWithTime = false)
    {
        if ($dDate == '' || $dDate == '0000-00-00 00:00:00' || $dDate == '0000-00-00' || $dDate == '1900-01-01') {
            return '';
        }

        if (strlen($dDate) == 10) { // If only a date was passed append time
            $dDate = $dDate . ' 12:00:00';
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

        setlocale(LC_ALL, SystemConfig::getValue("sLanguage").".utf8");
        return strftime("$fmt", strtotime($dDate));
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
                $dBirthMonth = '0' . $per_BirthMonth;
            } else {
                $dBirthMonth = $per_BirthMonth;
            }
            if ($per_BirthDay < 10) {
                $dBirthDay = '0' . $per_BirthDay;
            } else {
                $dBirthDay = $per_BirthDay;
            }

            $dBirthDate = $dBirthMonth . $sSeparator . $dBirthDay;
            if (is_numeric($birthYear)) {
                $dBirthDate = $birthYear . $sSeparator . $dBirthDate;
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

            return date_create($birthYear . '-' . $month . '-' . $day);
        }

        return date_create();
    }

    // Added for AddEvent.php
    public static function createTimeDropdown($start, $stop, $mininc, $hoursel, $minsel)
    {

        $bTimeEnglish = SystemConfig::getBooleanValue("bTimeEnglish");

        for ($hour = $start; $hour <= $stop; $hour++) {
            if ($hour == 0) {
                $disphour = 12;
                $ampm = 'AM';
            } elseif ($hour == 12) {
                $disphour = 12;
                $ampm = 'PM';
            } elseif ($hour >= 13 && $hour <= 21 && $bTimeEnglish == true) {
                $test = $hour - 12;
                $disphour = ' ' . $test;
                $ampm = 'PM';
            } elseif ($hour >= 22 && $hour <= 23 && $bTimeEnglish == true) {
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
                if ($hour >= 1 && $hour <= 9) {
                    if ($min >= '0' && $min <= '9') {
                        if ($hour == $hoursel && $min == $minsel) {
                            echo '<option value="0' . $hour . ':0' . $min . ':00" selected> ' . $disphour . ':0' . $min . ' ' . $ampm . '</option>' . "\n";
                        } else {
                            echo '<option value="0' . $hour . ':0' . $min . ':00"> ' . $disphour . ':0' . $min . ' ' . $ampm . '</option>' . "\n";
                        }
                    } else {
                        if ($hour == $hoursel && $min == $minsel) {
                            echo '<option value="0' . $hour . ':' . $min . ':00" selected> ' . $disphour . ':' . $min . ' ' . $ampm . '</option>' . "\n";
                        } else {
                            echo '<option value="0' . $hour . ':' . $min . ':00"> ' . $disphour . ':' . $min . ' ' . $ampm . '</option>' . "\n";
                        }
                    }
                } else {
                    if ($min >= '0' && $min <= '9') {
                        if ($hour == $hoursel && $min == $minsel) {
                            echo '<option value="' . $hour . ':0' . $min . ':00" selected>' . $disphour . ':0' . $min . ' ' . $ampm . '</option>' . "\n";
                        } else {
                            echo '<option value="' . $hour . ':0' . $min . ':00">' . $disphour . ':0' . $min . ' ' . $ampm . '</option>' . "\n";
                        }
                    } else {
                        if ($hour == $hoursel && $min == $minsel) {
                            echo '<option value="' . $hour . ':' . $min . ':00" selected>' . $disphour . ':' . $min . ' ' . $ampm . '</option>' . "\n";
                        } else {
                            echo '<option value="' . $hour . ':' . $min . ':00">' . $disphour . ':' . $min . ' ' . $ampm . '</option>' . "\n";
                        }
                    }
                }
            }
        }
    }

    // Returns a string of a person's full name, formatted as specified by $Style
    // $Style = 0  :  "Title FirstName MiddleName LastName, Suffix"
    // $Style = 1  :  "Title FirstName MiddleInitial. LastName, Suffix"
    // $Style = 2  :  "LastName, Title FirstName MiddleName, Suffix"
    // $Style = 3  :  "LastName, Title FirstName MiddleInitial., Suffix"
    //
    public static function FormatFullName($Title, $FirstName, $MiddleName, $LastName, $Suffix, $Style)
    {
        $nameString = '';

        switch ($Style) {

            case 0:
                if ($Title) {
                    $nameString .= $Title . ' ';
                }
                $nameString .= $FirstName;
                if ($MiddleName) {
                    $nameString .= ' ' . $MiddleName;
                }
                if ($LastName) {
                    $nameString .= ' ' . $LastName;
                }
                if ($Suffix) {
                    $nameString .= ', ' . $Suffix;
                }
                break;

            case 1:
                if ($Title) {
                    $nameString .= $Title . ' ';
                }
                $nameString .= $FirstName;
                if ($MiddleName) {
                    $nameString .= ' ' . mb_strtoupper(mb_substr($MiddleName, 0, 1)) . '.';
                }
                if ($LastName) {
                    $nameString .= ' ' . $LastName;
                }
                if ($Suffix) {
                    $nameString .= ', ' . $Suffix;
                }
                break;

            case 2:
                if ($LastName) {
                    $nameString .= $LastName . ', ';
                }
                if ($Title) {
                    $nameString .= $Title . ' ';
                }
                $nameString .= $FirstName;
                if ($MiddleName) {
                    $nameString .= ' ' . $MiddleName;
                }
                if ($Suffix) {
                    $nameString .= ', ' . $Suffix;
                }
                break;

            case 3:
                if ($LastName) {
                    $nameString .= $LastName . ', ';
                }
                if ($Title) {
                    $nameString .= $Title . ' ';
                }
                $nameString .= $FirstName;
                if ($MiddleName) {
                    $nameString .= ' ' . mb_strtoupper(mb_substr($MiddleName, 0, 1)) . '.';
                }
                if ($Suffix) {
                    $nameString .= ', ' . $Suffix;
                }
                break;
        }

        return $nameString;
    }
}
