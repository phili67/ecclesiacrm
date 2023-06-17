<?php
namespace EcclesiaCRM\dto;

use EcclesiaCRM\data\Countries;

class CountryDropDown extends Countries
{
    public static function getDropDown($selected_country="",$countryname= "Country", $class_form="form-control-sm")
    {
      $country = $countryname;
      $id_input = strtolower($country)."-input";

      $res = "";

      $res .= '<select name="'.$country.'" id="'.$country.'" class="form-control '.$class_form.' select2" id="'.$id_input.'" style="width:100%">';
      $res .= '<option value="">'.gettext('Unassigned').'</option>';
      $res .= '<option value="" disabled>--------------------</option>';
        foreach (self::getNames() as $county) {
          $res .= '<option value="'.$county.'"';
          if ($selected_country == $county) {
            $res .=  'selected';
          }
          $res .=  '>'.gettext($county);
        }
      $res .= '</select>';

      return $res;
    }
}
