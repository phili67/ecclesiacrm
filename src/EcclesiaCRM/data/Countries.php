<?php
/**
 * Created by PhpStorm.
 * User: georg
 * Date: 11/12/2016
 * Time: 12:00 PM.
 */

namespace EcclesiaCRM\data;

class Countries
{
    private static function getArray ()
    {
       $countries = ['AF' => gettext('Afghanistan (‫افغانستان‬‎)'), 'AX' => gettext('Åland Islands (Åland)'), 'AL' => gettext('Albania (Shqipëri)'), 
        'DZ' => gettext('Algeria (‫الجزائر‬‎)'), 'AS' => gettext('American Samoa'), 'AD' => gettext('Andorra'), 
        'AO' => gettext('Angola'), 'AI' => gettext('Anguilla'), 'AQ' => gettext('Antarctica'), 'AG' => gettext('Antigua and Barbuda'), 
        'AR' => gettext('Argentina'), 'AM' => gettext('Armenia (Հայաստան)'), 'AW' => gettext('Aruba'), 'AC' => gettext('Ascension Island'), 
        'AU' => gettext('Australia'), 'AT' => gettext('Austria (Österreich)'), 'AZ' => gettext('Azerbaijan (Azərbaycan)'), 
        'BS' => gettext('Bahamas'), 'BH' => gettext('Bahrain (‫البحرين‬‎)'), 'BD' => gettext('Bangladesh (বাংলাদেশ)'), 'BB' => gettext('Barbados'), 
        'BY' => gettext('Belarus (Беларусь)'), 'BE' => gettext('Belgium (België)'), 'BZ' => gettext('Belize'), 'BJ' => gettext('Benin (Bénin)'), 
        'BM' => gettext('Bermuda'), 'BT' => gettext('Bhutan (འབྲུག)'), 'BO' => gettext('Bolivia'), 'BA' => gettext('Bosnia and Herzegovina (Босна и Херцеговина)'), 
        'BW' => gettext('Botswana'), 'BV' => gettext('Bouvet Island'), 'BR' => gettext('Brazil (Brasil)'), 'IO' => gettext('British Indian Ocean Territory'), 
        'VG' => gettext('British Virgin Islands'), 'BN' => gettext('Brunei'), 'BG' => gettext('Bulgaria (България)'), 'BF' => gettext('Burkina Faso'), 
        'BI' => gettext('Burundi (Uburundi)'), 'KH' => gettext('Cambodia (កម្ពុជា)'), 'CM' => gettext('Cameroon (Cameroun)'), 'CA' => gettext('Canada'), 
        'IC' => gettext('Canary Islands (islas Canarias)'), 'CV' => gettext('Cape Verde (Kabu Verdi)'), 'BQ' => gettext('Caribbean Netherlands'), 
        'KY' => gettext('Cayman Islands'), 'CF' => gettext('Central African Republic (République centrafricaine)'), 'EA' => gettext('Ceuta and Melilla (Ceuta y Melilla)'), 
        'TD' => gettext('Chad (Tchad)'), 'CL' => gettext('Chile'), 'CN' => gettext('China (中国)'), 'CX' => gettext('Christmas Island'), 'CP' => gettext('Clipperton Island'), 
        'CC' => gettext('Cocos (Keeling) Islands (Kepulauan Cocos (Keeling))'), 'CO' => gettext('Colombia'), 'KM' => gettext('Comoros (‫جزر القمر‬‎)'), 
        'CD' => gettext('Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)'), 'CG' => gettext('Congo (Republic) (Congo-Brazzaville)'), 'CK' => gettext('Cook Islands'), 
        'CR' => gettext('Costa Rica'), 'CI' => gettext('Côte d’Ivoire'), 'HR' => gettext('Croatia (Hrvatska)'), 'CU' => gettext('Cuba'), 'CW' => gettext('Curaçao'), 'CY' => gettext('Cyprus (Κύπρος)'), 
        'CZ' => gettext('Czech Republic (Česká republika)'), 'DK' => gettext('Denmark (Danmark)'), 'DG' => gettext('Diego Garcia'), 'DJ' => gettext('Djibouti'), 'DM' => gettext('Dominica'), 
        'DO' => gettext('Dominican Republic (República Dominicana)'), 'EC' => gettext('Ecuador'), 'EG' => gettext('Egypt (‫مصر‬‎)'), 'SV' => gettext('El Salvador'), 
        'GQ' => gettext('Equatorial Guinea (Guinea Ecuatorial)'), 'ER' => gettext('Eritrea'), 'EE' => gettext('Estonia (Eesti)'), 'ET' => gettext('Ethiopia'), 
        'FK' => gettext('Falkland Islands (Islas Malvinas)'), 'FO' => gettext('Faroe Islands (Føroyar)'), 'FJ' => gettext('Fiji'), 'FI' => gettext('Finland (Suomi)'), 
        'FR' => gettext('France'), 'GF' => gettext('French Guiana (Guyane française)'), 'PF' => gettext('French Polynesia (Polynésie française)'), 
        'TF' => gettext('French Southern Territories (Terres australes françaises)'), 'GA' => gettext('Gabon'), 'GM' => gettext('Gambia'), 'GE' => gettext('Georgia (საქართველო)'), 
        'DE' => gettext('Germany (Deutschland)'), 'GH' => gettext('Ghana (Gaana)'), 'GI' => gettext('Gibraltar'), 'GR' => gettext('Greece (Ελλάδα)'), 'GL' => gettext('Greenland (Kalaallit Nunaat)'), 
        'GD' => gettext('Grenada'), 'GP' => gettext('Guadeloupe'), 'GU' => gettext('Guam'), 'GT' => gettext('Guatemala'), 'GG' => gettext('Guernsey'), 'GN' => gettext('Guinea (Guinée)'), 'GW' => gettext('Guinea-Bissau (Guiné Bissau)'), 
        'GY' => gettext('Guyana'), 'HT' => gettext('Haiti'), 'HM' => gettext('Heard & McDonald Islands'), 'HN' => gettext('Honduras'), 'HK' => gettext('Hong Kong (香港)'), 'HU' => gettext('Hungary (Magyarország)'), 
        'IS' => gettext('Iceland (Ísland)'), 'IN' => gettext('India (भारत)'), 'ID' => gettext('Indonesia'), 'IR' => gettext('Iran (‫ایران‬‎)'), 'IQ' => gettext('Iraq (‫العراق‬‎)'), 'IE' => gettext('Ireland'), 
        'IM' => gettext('Isle of Man'), 'IL' => gettext('Israel (‫ישראל‬‎)'), 'IT' => gettext('Italy (Italia)'), 'JM' => gettext('Jamaica'), 'JP' => gettext('Japan (日本)'), 'JE' => gettext('Jersey'), 'JO' => gettext('Jordan (‫الأردن‬‎)'), 
        'KZ' => gettext('Kazakhstan (Казахстан)'), 'KE' => gettext('Kenya'), 'KI' => gettext('Kiribati'), 'XK' => gettext('Kosovo (Kosovë)'), 'KW' => gettext('Kuwait (‫الكويت‬‎)'), 'KG' => gettext('Kyrgyzstan (Кыргызстан)'), 
        'LA' => gettext('Laos (ລາວ)'), 'LV' => gettext('Latvia (Latvija)'), 'LB' => gettext('Lebanon (‫لبنان‬‎)'), 'LS' => gettext('Lesotho'), 'LR' => gettext('Liberia'), 'LY' => gettext('Libya (‫ليبيا‬‎)'), 
        'LI' => gettext('Liechtenstein'), 'LT' => gettext('Lithuania (Lietuva)'), 'LU' => gettext('Luxembourg'), 'MO' => gettext('Macau (澳門)'), 'MK' => gettext('Macedonia (FYROM) (Македонија)'), 
        'MG' => gettext('Madagascar (Madagasikara)'), 'MW' => gettext('Malawi'), 'MY' => gettext('Malaysia'), 'MV' => gettext('Maldives'), 'ML' => gettext('Mali'), 'MT' => gettext('Malta'), 'MH' => gettext('Marshall Islands'), 
        'MQ' => gettext('Martinique'), 'MR' => gettext('Mauritania (‫موريتانيا‬‎)'), 'MU' => gettext('Mauritius (Moris)'), 'YT' => gettext('Mayotte'), 'MX' => gettext('Mexico (México)'), 'FM' => gettext('Micronesia'), 
        'MD' => gettext('Moldova (Republica Moldova)'), 'MC' => gettext('Monaco'), 'MN' => gettext('Mongolia (Монгол)'), 'ME' => gettext('Montenegro (Crna Gora)'), 'MS' => gettext('Montserrat'), 'MA' => gettext('Morocco (‫المغرب‬‎)'), 
        'MZ' => gettext('Mozambique (Moçambique)'), 'MM' => gettext('Myanmar (Burma) (မြန်မာ)'), 'NA' => gettext('Namibia (Namibië)'), 'NR' => gettext('Nauru'), 'NP' => gettext('Nepal (नेपाल)'), 
        'NL' => gettext('Netherlands (Nederland)'), 'NC' => gettext('New Caledonia (Nouvelle-Calédonie)'), 'NZ' => gettext('New Zealand'), 'NI' => gettext('Nicaragua'), 'NE' => gettext('Niger (Nijar)'), 
        'NG' => gettext('Nigeria'), 'NU' => gettext('Niue'), 'NF' => gettext('Norfolk Island'), 'MP' => gettext('Northern Mariana Islands'), 'KP' => gettext('North Korea (조선 민주주의 인민 공화국)'), 
        'NO' => gettext('Norway (Norge)'), 'OM' => gettext('Oman (‫عُمان‬‎)'), 'PK' => gettext('Pakistan (‫پاکستان‬‎)'), 'PW' => gettext('Palau'), 'PS' => gettext('Palestine (‫فلسطين‬‎)'), 'PA' => gettext('Panama (Panamá)'), 
        'PG' => gettext('Papua New Guinea'), 'PY' => gettext('Paraguay'), 'PE' => gettext('Peru (Perú)'), 'PH' => gettext('Philippines'), 'PN' => gettext('Pitcairn Islands'), 'PL' => gettext('Poland (Polska)'), 
        'PT' => gettext('Portugal'), 'PR' => gettext('Puerto Rico'), 'QA' => gettext('Qatar (‫قطر‬‎)'), 'RE' => gettext('Réunion (La Réunion)'), 'RO' => gettext('Romania (România)'), 'RU' => gettext('Russia (Россия)'), 
        'RW' => gettext('Rwanda'), 'BL' => gettext('Saint Barthélemy (Saint-Barthélemy)'), 'SH' => gettext('Saint Helena'), 'KN' => gettext('Saint Kitts and Nevis'), 'LC' => gettext('Saint Lucia'), 
        'MF' => gettext('Saint Martin (Saint-Martin (partie française))'), 'PM' => gettext('Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)'), 'WS' => gettext('Samoa'), 
        'SM' => gettext('San Marino'), 'ST' => gettext('São Tomé and Príncipe (São Tomé e Príncipe)'), 'SA' => gettext('Saudi Arabia (‫المملكة العربية السعودية‬‎)'), 
        'SN' => gettext('Senegal (Sénégal)'), 'RS' => gettext('Serbia (Србија)'), 'SC' => gettext('Seychelles'), 'SL' => gettext('Sierra Leone'), 'SG' => gettext('Singapore'), 'SX' => gettext('Sint Maarten'), 
        'SK' => gettext('Slovakia (Slovensko)'), 'SI' => gettext('Slovenia (Slovenija)'), 'SB' => gettext('Solomon Islands'), 'SO' => gettext('Somalia (Soomaaliya)'), 'ZA' => gettext('South Africa'), 
        'GS' => gettext('South Georgia & South Sandwich Islands'), 'KR' => gettext('South Korea (대한민국)'), 'SS' => gettext('South Sudan (‫جنوب السودان‬‎)'), 'ES' => gettext('Spain (España)'), 
        'LK' => gettext('Sri Lanka (ශ්‍රී ලංකාව)'), 'VC' => gettext('St. Vincent & Grenadines'), 'SD' => gettext('Sudan (‫السودان‬‎)'), 'SR' => gettext('Suriname'), 'SJ' => gettext('Svalbard and Jan Mayen (Svalbard og Jan Mayen)'), 
        'SZ' => gettext('Swaziland'), 'SE' => gettext('Sweden (Sverige)'), 'CH' => gettext('Switzerland (Schweiz)'), 'SY' => gettext('Syria (‫سوريا‬‎)'), 'TW' => gettext('Taiwan (台灣)'), 
        'TJ' => gettext('Tajikistan'), 'TZ' => gettext('Tanzania'), 'TH' => gettext('Thailand (ไทย)'), 'TL' => gettext('Timor-Leste'), 'TG' => gettext('Togo'), 'TK' => gettext('Tokelau'), 'TO' => gettext('Tonga'), 
        'TT' => gettext('Trinidad and Tobago'), 'TA' => gettext('Tristan da Cunha'), 'TN' => gettext('Tunisia (‫تونس‬‎)'), 'TR' => gettext('Turkey (Türkiye)'), 'TM' => gettext('Turkmenistan'), 
        'TC' => gettext('Turks and Caicos Islands'), 'TV' => gettext('Tuvalu'), 'UM' => gettext('U.S. Outlying Islands'), 'VI' => gettext('U.S. Virgin Islands'), 'UG' => gettext('Uganda'), 
        'UA' => gettext('Ukraine (Україна)'), 'AE' => gettext('United Arab Emirates (‫الإمارات العربية المتحدة‬‎)'), 'GB' => gettext('United Kingdom'), 'US' => gettext('United States'), 
        'UY' => gettext('Uruguay'), 'UZ' => gettext('Uzbekistan (Oʻzbekiston)'), 'VU' => gettext('Vanuatu'), 'VA' => gettext('Vatican City (Città del Vaticano)'), 'VE' => gettext('Venezuela'), 
        'VN' => gettext('Vietnam (Việt Nam)'), 'WF' => gettext('Wallis and Futuna'), 'EH' => gettext('Western Sahara (‫الصحراء الغربية‬‎)'), 'YE' => gettext('Yemen (‫اليمن‬‎)'), 'ZM' => gettext('Zambia'), 'ZW' => gettext('Zimbabwe')];
        
      return $countries;
    }    
    
    public static function getNames()
    {
       
        
        return array_values(self::getArray());
    }

    public static function getAll()
    {
        return self::getArray();
    }
}
