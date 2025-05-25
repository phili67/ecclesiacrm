<?php

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\PledgeQuery;

use PhpOffice\PhpWord\IOFactory;
use Propel\Runtime\Propel;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\ListItem;
use \PhpOffice\PhpWord\Element\AbstractContainer;
use EcclesiaCRM\PluginQuery;

use Propel\Runtime\ActiveQuery\Criteria;

use DOMNode;


class MiscUtils
{
    const types = ["a", "b", "i", "u", "h1", "h2", "h2", "hr", "img", "p", "ul", "ol", "li", "table", "tbody", "theader", "tr", "td", "strong", "em"];

    const extensions_to_sanitize = ["php", "jar", "js", "exe", "py", "com", "sh", "bash", "rb", "ahk", "apk", "pl"];

    // Constants
    const aPropTypes = [
        1  => 'True / False',
        2  => 'Date',
        3  => 'Text Field (50 char)',
        4  => 'Text Field (100 char)',
        5  => 'Text Field (Long)',
        6  => 'Year',
        7  => 'Season',
        8  => 'Number',
        9  => 'Person from Group',
        10 => 'Money',
        11 => 'Phone Number',
        12 => 'Custom Drop-Down List'
    ];

    /**
     * A PHP function that will generate a secure random password.
     *
     * @param int $length The length that you want your random password to be.
     * @return string The random password.
     */
    public static function random_password($length){
        //A list of characters that can be used in our
        //random password.
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
        //Create a blank string.
        $password = '';
        //Get the index of the last character in our $characters string.
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        //Loop from 1 to the $length that was specified.
        foreach(range(1, $length) as $i){
            $password .= $characters[random_int(0, $characterListLength)];
        }
        return $password;

    }

    public static function PropTypes ($type) {
        return _(self::aPropTypes[$type]);
    }

    public static function ProTypeCount() {
        return count(self::aPropTypes);
    }

    /**
     * Remove the directory and its content (all files and subdirectories), useFull in system upgrade.
     * @param string $path the directory name
     */
    public static function removeDirectory($path)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? self::removeDirectory($file) : unlink($file);
        }
        rmdir($path);
    }

    // Generates SQL for custom field update
    //
    // $special is currently only used for the phone country and the list ID for custom drop-down choices.
    //
    public static function sqlCustomField(&$sSQL, $type, $data, $col_Name, $special)
    {
        switch ($type) {
            // boolean
            case 1:
                switch ($data) {
                    case 'false':
                        $data = "'false'";
                        break;
                    case 'true':
                        $data = "'true'";
                        break;
                    default:
                        $data = 'NULL';
                        break;
                }

                $sSQL .= $col_Name . ' = ' . $data . ', ';
                break;

            // date
            case 2:
                if (strlen($data) > 0) {
                    $sSQL .= $col_Name . ' = "' . $data . '", ';
                } else {
                    $sSQL .= $col_Name . ' = NULL, ';
                }
                break;

            // year
            case 6:
                if (strlen($data) > 0) {
                    $sSQL .= $col_Name . " = '" . $data . "', ";
                } else {
                    $sSQL .= $col_Name . ' = NULL, ';
                }
                break;

            // season
            case 7:
                if ($data != 'none') {
                    $sSQL .= $col_Name . " = '" . $data . "', ";
                } else {
                    $sSQL .= $col_Name . ' = NULL, ';
                }
                break;

            // integer, money
            case 8:
            case 10:
                if (strlen($data) > 0) {
                    $sSQL .= $col_Name . " = '" . $data . "', ";
                } else {
                    $sSQL .= $col_Name . ' = NULL, ';
                }
                break;

            // list selects
            case 9:
            case 12:
                if ($data != 0) {
                    $sSQL .= $col_Name . " = '" . $data . "', ";
                } else {
                    $sSQL .= $col_Name . ' = NULL, ';
                }
                break;

            // strings
            case 3:
            case 4:
            case 5:
                if (strlen($data) > 0) {
                    $sSQL .= $col_Name . " = '" . $data . "', ";
                } else {
                    $sSQL .= $col_Name . ' = NULL, ';
                }
                break;

            // phone
            case 11:
                if (strlen($data) > 0) {
                    if (!isset($_POST[$col_Name . 'noformat'])) {
                        $sSQL .= $col_Name . " = '" . MiscUtils::CollapsePhoneNumber($data, $special) . "', ";
                    } else {
                        $sSQL .= $col_Name . " = '" . $data . "', ";
                    }
                } else {
                    $sSQL .= $col_Name . ' = NULL, ';
                }
                break;

            default:
                $sSQL .= $col_Name . " = '" . $data . "', ";
                break;
        }
    }
    //Function to check email
    //From http://www.tienhuis.nl/php-email-address-validation-with-verify-probe
    //Functions checkndsrr and getmxrr are not enabled on windows platforms & therefore are disabled
    //Future use may be to enable a Admin option to enable these options
    //domainCheck verifies domain is valid using dns, verify uses SMTP to verify actual account exists on server

    public static function checkEmail($email, $domainCheck = false, $verify = false, $return_errors = false)
    {
        global $checkEmailDebug;
        if ($checkEmailDebug) {
            echo '<pre>';
        }
        // Check syntax with regex
        if (preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/', $email, $matches)) {
            $user = $matches[1];
            $domain = $matches[2];
            // Check availability of DNS MX records
            if ($domainCheck && function_exists('checkdnsrr')) {
                // Construct array of available mailservers
                if (getmxrr($domain, $mxhosts, $mxweight)) {
                    for ($i = 0; $i < count($mxhosts); $i++) {
                        $mxs[$mxhosts[$i]] = $mxweight[$i];
                    }
                    asort($mxs);
                    $mailers = array_keys($mxs);
                } elseif (checkdnsrr($domain, 'A')) {
                    $mailers[0] = gethostbyname($domain);
                } else {
                    $mailers = [];
                }
                $total = count($mailers);
                // Query each mailserver
                if ($total > 0 && $verify) {
                    // Check if mailers accept mail
                    for ($n = 0; $n < $total; $n++) {
                        // Check if socket can be opened
                        if ($checkEmailDebug) {
                            echo "Checking server $mailers[$n]...\n";
                        }
                        $connect_timeout = 2;
                        $errno = 0;
                        $errstr = 0;
                        $probe_address = SystemConfig::getValue('sToEmailAddress');
                        // Try to open up socket
                        if ($sock = @fsockopen($mailers[$n], 25, $errno, $errstr, $connect_timeout)) {
                            $response = fgets($sock);
                            if ($checkEmailDebug) {
                                echo "Opening up socket to $mailers[$n]... Succes!\n";
                            }
                            stream_set_timeout($sock, 5);
                            $meta = stream_get_meta_data($sock);
                            if ($checkEmailDebug) {
                                echo "$mailers[$n] replied: $response\n";
                            }
                            $cmds = [
                                'HELO ' . SystemConfig::getValue('sSMTPHost'), // Be sure to set this correctly!
                                "MAIL FROM: <$probe_address>",
                                "RCPT TO: <$email>",
                                'QUIT',
                            ];
                            // Hard error on connect -> break out
                            if (!$meta['timed_out'] && !preg_match('/^2\d\d[ -]/', $response)) {
                                $error = "Error: $mailers[$n] said: $response\n";
                                break;
                            }
                            foreach ($cmds as $cmd) {
                                $before = microtime(true);
                                fwrite($sock, "$cmd\r\n");
                                $response = fgets($sock, 4096);
                                $t = 1000 * (microtime(true) - $before);
                                if ($checkEmailDebug) {
                                    echo htmlentities("$cmd\n$response") . '(' . sprintf('%.2f', $t) . " ms)\n";
                                }
                                if (!$meta['timed_out'] && preg_match('/^5\d\d[ -]/', $response)) {
                                    $error = "Unverified address: $mailers[$n] said: $response";
                                    break 2;
                                }
                            }
                            fclose($sock);
                            if ($checkEmailDebug) {
                                echo "Succesful communication with $mailers[$n], no hard errors, assuming OK";
                            }
                            break;
                        } elseif ($n == $total - 1) {
                            $error = "None of the mailservers listed for $domain could be contacted";
                        }
                    }
                } elseif ($total <= 0) {
                    $error = "No usable DNS records found for domain '$domain'";
                }
            }
        } else {
            $error = 'Address syntax not correct';
        }
        if ($checkEmailDebug) {
            echo '</pre>';
        }
        //echo "</pre>";
        if ($return_errors) {
            // Give back details about the error(s).
            // Return FALSE if there are no errors.
            // Keep this in mind when using it like:
            // if(checkEmail($addr)) {
            // Because of this strange behaviour this
            // is not default ;-)
            if (isset($error)) {
                return htmlentities($error);
            } else {
                return false;
            }
        } else {
            // 'Old' behaviour, simple to understand
            if (isset($error)) {
                return false;
            } else {
                return true;
            }
        }
    }  // Generate a nicely formatted string for "FamilyName - Address / City, State" with available data


    public static function FormatAddressLine($Address, $City, $State)
    {
        $sText = '';

        if ($Address != '' || $City != '' || $State != '') {
            $sText = ' - ';
        }
        $sText .= $Address;
        if ($Address != '' && ($City != '' || $State != '')) {
            $sText .= ' / ';
        }
        $sText .= $City;
        if ($City != '' && $State != '') {
            $sText .= ', ';
        }
        $sText .= $State;

        return $sText;
    }

    public static function getFamilyList($sDirRoleHead, $sDirRoleSpouse, $classification = 0, $sSearchTerm = 0)
    {
        if ($classification) {
            if ($sSearchTerm) {
                $whereClause = " WHERE per_cls_ID='" . $classification . "' AND fam_Name LIKE '%" . $sSearchTerm . "%' ";
            } else {
                $whereClause = " WHERE per_cls_ID='" . $classification . "' ";
            }
            $sSQL = "SELECT fam_ID, fam_Name, fam_Address1, fam_City, fam_State FROM family_fam LEFT JOIN person_per ON fam_ID = per_fam_ID $whereClause ORDER BY fam_Name";
        } else {
            if ($sSearchTerm) {
                $whereClause = " WHERE fam_Name LIKE '%" . $sSearchTerm . "%' ";
            } else {
                $whereClause = '';
            }
            $sSQL = "SELECT fam_ID, fam_Name, fam_Address1, fam_City, fam_State FROM family_fam $whereClause ORDER BY fam_Name";
        }

        $connection = Propel::getConnection();

        // Get data for the form as it now exists..
        $pdoFamilies = $connection->prepare($sSQL);
        $pdoFamilies->execute();

        // Build Criteria for Head of Household
        if (!$sDirRoleHead) {
            $sDirRoleHead = '1';
        }
        $head_criteria = ' per_fmr_ID = ' . $sDirRoleHead;
        // If more than one role assigned to Head of Household, add OR
        $head_criteria = str_replace(',', ' OR per_fmr_ID = ', $head_criteria);
        // Add Spouse to criteria
        if (intval($sDirRoleSpouse) > 0) {
            $head_criteria .= " OR per_fmr_ID = $sDirRoleSpouse";
        }
        // Build array of Head of Households and Spouses with fam_ID as the key
        $sSQL = 'SELECT per_FirstName as head_firstname, per_fam_ID as head_famid FROM person_per WHERE per_DateDeactivated IS NULL AND per_fam_ID > 0 AND (' . $head_criteria . ') ORDER BY per_fam_ID';

        $pdo_head = $connection->prepare($sSQL);
        $pdo_head->execute();

        $aHead = [];
        while ($aRow = $pdo_head->fetch(\PDO::FETCH_ASSOC)) {
            if ($aRow['head_firstname'] && isset($aHead[$aRow['head_famid']])) {
                $aHead[$aRow['head_famid']] .= ' & ' . $aRow['head_firstname'];
            } elseif ($aRow['head_firstname']) {
                $aHead[$aRow['head_famid']] = $aRow['head_firstname'];
            }
        }

        $familyArray = [];
        while ($aRow = $pdoFamilies->fetch(\PDO::FETCH_ASSOC)) {
            $name = $aRow['fam_Name'];
            if (isset($aHead[$aRow['fam_ID']])) {
                $name .= ', ' . $aHead[$aRow['fam_ID']];
            }
            $name .= ' ' . MiscUtils::FormatAddressLine($aRow['fam_Address1'], $aRow['fam_City'], $aRow['fam_State']);

            $familyArray[$aRow['fam_ID']] = $name;
        }

        return $familyArray;
    }

    public static function buildFamilySelect($iFamily, $sDirRoleHead, $sDirRoleSpouse)
    {
        //Get Families for the drop-down
        $familyArray = MiscUtils::getFamilyList($sDirRoleHead, $sDirRoleSpouse);
        $html = "";
        foreach ($familyArray as $fam_ID => $fam_Data) {
            $html .= '<option value="' . $fam_ID . '"';
            if ($iFamily == $fam_ID) {
                $html .= ' selected';
            }
            $html .= '>' . $fam_Data;
        }

        return $html;
    }


    /**
     * Remove the directory and its content (all files and subdirectories).
     * @param string $dir the directory name
     */
    public static function delTree($dir)
    {
        if (!is_dir($dir)) return false;

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }


    /**
     * Unicode to UTF8 real real string
     * @param string $dir the directory name
     */
    public static function convertUnicodeAccentuedString2UTF8($string)
    {
        $uriUtf8 = str_replace(
            ["á", "à", "â", "ä", "À", "Ä", "Â", "ç", "Ç", "é", "è", "ê", "É", "È", "Ê", "Ë", "í", "ì", "ï", "Ï", "î", "Î"], // this are two byte char
            ["á", "à", "â", "ä", "À", "Ä", "Â", "ç", "Ç", "é", "è", "ê", "É", "È", "Ê", "Ë", "í", "ì", "ï", "Ï", "î", "Î"], // this are one byte char
            $string);

        return $uriUtf8;
    }

    /**
     * UTF8 to unicode real real string
     * @param string $dir the directory name
     */
    public static function convertUTF8AccentuedString2Unicode($string)
    {
        $uriUnicode = str_replace(
            ["á", "à", "â", "ä", "À", "Ä", "Â", "ç", "Ç", "é", "è", "ê", "É", "È", "Ê", "Ë", "í", "ì", "ï", "Ï", "î", "Î"], // this are one byte char
            ["á", "à", "â", "ä", "À", "Ä", "Â", "ç", "Ç", "é", "è", "ê", "É", "È", "Ê", "Ë", "í", "ì", "ï", "Ï", "î", "Î"], //this are two byte char
            $string);

        return $uriUnicode;
    }

    public static function pathToPathWithIcons($path)
    {
        $items = explode('/', $path);

        $res = "";
        $len = count($items);

        $first = true;
        for ($i = 0; $i < $len; $i++) {
            if ($first == true) {
                $res = "<i class='fas fa-home text-aqua'></i> " . _("Home");

                if ($len > 2) {
                    $res .= ' <i class="fas fa-caret-right"></i>';
                }
                $first = false;
            }

            if (!empty ($items[$i])) {
                $res .= "&nbsp;&nbsp;<i class='far fa-folder text-yellow'></i> " . $items[$i];

                if ($i != $len - 2) {
                    $res .= "&nbsp;&nbsp;<i class='fas fa-caret-right'></i>";
                }
            }
        }

        return $res;
    }

    /**
     * return all the directories in
     * @param string $path string $basePath
     */
    public static function getDirectoriesInPath($dir)
    {
        $dirs = glob("." . $dir . "*", GLOB_ONLYDIR);

        return $dirs;
    }

    /**
     * return all the directories in
     * @param string $path string $basePath
     */
    public static function getImagesInPath($dir)
    {
        $files = glob($dir . "/*.{jpg,gif,png,html,htm,php,ini}", GLOB_BRACE);

        return $files;
    }


    /**
     * Converts bytes into human readable file size.
     *
     * @param string $bytes
     * @return string human readable file size (2,87 Мб)
     * @author Mogilev Arseny
     */
    public static function FileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        $result = "";
        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                //$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                $result = OutputUtils::number_localized($result) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    /**
     * return true when the path in the basePath is a real file
     * @param string $path string $basePath
     */
    public static function isRealFile($path, $basePath)
    {
        $test = str_replace($basePath, "", $path);

        $res = strstr($test, "/");

        if (strlen($res) > 0) {
            return false;
        }

        return true;
    }

    public static function getRealDirectory($path, $basePath)
    {
        return str_replace("." . $basePath, "", $path);
    }

    private static function FileIconTimeLine($ext) {
        switch (strtolower($ext)) {
            case "doc":
            case "docx":
            case "odt":
                $icon =  ' far fa-file-word text-blue ';
                break;
                break;
            case "ics":
                $icon =  ' far fa-calendar text-red';
                break;
            case "sql":
                $icon =  ' fas fa-database text-red';
                break;
            case "xls":
            case "xlsx":
            case "ods":
            case "csv":
                $icon =  ' far fa-file-excel text-olive';
                break;
            case "ppt":
            case "pptx":
            case "ods":
                $icon =  ' far fa-file-powerpoint text-red';
                break;
            case "jpg":
            case "jpeg":
                $icon =  ' far fa-file-image text-teal';
                break;
            case "png":
                $icon =  ' far fa-file-image text-teal';
                break;
            case "txt":
            case "ps1":
            case "c":
            case "cpp":
            case "php":
            case "js":
            case "mm":
            case "vcf":
            case "py":
            case "mm":
            case "swift":
            case "sh":
            case "ru":
            case "asp":
            case "m":
            case "vbs":
            case "admx":
            case "adml":
                $icon =  'fas fa-file-code text-black';
                break;
            case "pdf":
                $icon =  'far fa-file-pdf  text-red';
                break;
            case "mp3":
            case "m4a":
            case "oga":
            case "wav":
                $icon =  'fas fa-file-music  text-green';
                break;
            case  "mp4":
                $icon =  'fas fa-video text-blue';
                break;
            case  "ogg":
                $icon =  'fas fa-video  text-blue';
                break;
            case "mov":
                $icon =  'fas fa-video text-blue';
                break;
            default:
                $icon =  "far fa-file text-blue";
                break;
        }

        return $icon . " bg-gray-light";
    }

    public static function FileIcon($path, $timeline=false)
    {
        $filename = basename($path);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $globalPath = SystemURLs::getRootPath() . "/Images/Icons/";

        if ($timeline) {
            return MiscUtils::FileIconTimeLine($extension);
        }

        switch (strtolower($extension)) {
            case "doc":
            case "docx":
            case "odt":
                $icon =  "DOC.png";//' far fa-file-word text-blue ';
                break;
            case "zip":
                $icon =  "ZIP.png";//' far fa-file-word text-blue ';
                break;
            case "ics":
                $icon =  "ICS.png";//' far fa-calendar text-red';
                break;
            case "sql":
                $icon =  "SQL.png";//' fas fa-database text-red';
                break;
            case "xls":
            case "xlsx":
            case "ods":
            case "csv":
                $icon =  "XLS.png";//' far fa-file-excel text-olive';
                break;
            case "ppt":
            case "pptx":
            case "ods":
                $icon =  "PPT.png";//' far fa-file-powerpoint text-red';
                break;
            case "jpg":
            case "jpeg":
                $icon =  "JPG.png";//
                break;
            case "png":
                $icon =  "PNG.png"; //' far fa-file-image text-teal';
                break;
            case "gif":
                $icon =  "GIF.png"; //' far fa-file-image text-teal';
                break;
            case "txt":
            case "ps1":
            case "c":
            case "cpp":
            case "php":
            case "js":
            case "mm":
            case "vcf":
            case "py":
            case "mm":
            case "swift":
            case "sh":
            case "ru":
            case "asp":
            case "m":
            case "vbs":
            case "admx":
            case "adml":
                $icon =  "CODE.png"; //'fas fa-file-code text-black';
                break;
            case "pdf":
                $icon =  "PDF.png"; // 'far fa-file-pdf  text-red';
                break;
            case "mp3":
            case "m4a":
            case "oga":
            case "wav":
                $icon =  "MP3.png"; // 'fas fa-file-music  text-green';
                break;
            case  "mp4":
                $icon =  "MP4.png"; // 'fas fa-video text-blue';
                break;
            case  "ogg":
                $icon =  "OGG.png"; //'fas fa-video  text-blue';
                break;
            case "mov":
                $icon =  "MOV.png"; // 'fas fa-video text-blue';
                break;
            default:
                $icon =  "FILE.png"; // "far fa-file text-blue";
                break;
        }

        $globalPath .= $icon;

        return $globalPath;

        //return $icon . " bg-gray-light";
    }

    public static function simpleEmbedFiles($path, $realPath = NULL, $height = '200px')
    {
        $uuid = MiscUtils::gen_uuid();

        $filename = basename($path);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $res = ($extension == "") ? (_("Folder") . " : " . $filename) : (_("File") . " : <a href=\"" . $path . "\">\"" . $filename . "\"</a><br>");

        switch (strtolower($extension)) {
            /*case "doc":
            case "docx":
              $writers = array('Word2007' => 'docx', 'ODText' => 'odt', 'RTF' => 'rtf', 'HTML' => 'html', 'PDF' => 'pdf');

              // Read contents
              $phpWord = \PhpOffice\PhpWord\IOFactory::load(dirname(__FILE__)."/../..".$realPath);

              // Save file
              //$res .=  $phpWord;
              ob_start();
              //echo write($phpWord, 'php://output', $writers);
              $res .= ob_end_clean();
              break;*/
            case "jpg":
            case "jpeg":
            case "png":
                //$res .= '<img src="' . $path . '" style="display: flex;justify-content: center;height: '.$height.'"/>';
                $res .= '<img src="' . $path . '" style="width: 100%"/>';
                break;
            case "txt":
            case "ps1":
            case "c":
            case "cpp":
            case "php":
            case "js":
            case "mm":
            case "vcf":
            case "py":
            case "ru":
            case "m":
            case "vbs":
            case "admx":
            case "adml":
            case "ics":
            case "csv":
            case "sql":
                $content = file_get_contents(dirname(__FILE__) . "/../.." . $realPath);
                $content = nl2br(mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)));

                $res .= '<div style="overflow: auto; width:100%; height:240px;border:1px;border-style: solid;border-color: lightgray;">';
                $res .= $content;
                $res .= '</div>';
                break;
            case "pdf":
                $res .= "<object data=\"" . $realPath . "\" type=\"application/pdf\" style=\"width: 100%;\">";
                $res .= "<embed src=\"" . $realPath . "\" type=\"application/pdf\" />\n";
                $res .= "<p>" . _("You've to use a PDF viewer or download the file here ") . ': <a href="' . $realPath . '">télécharger le fichier.</a></p>';
                $res .= "</object>";
                break;
            case "mp3":
                $res .= " type : $extension<br>";
                //$res .= "<audio src=\"".$path."\" controls=\"controls\" preload=\"auto\" style=\"width: 100%;\" type=\"audio/mp3\">"._("Your browser does not support the audio element.")."</audio>";
                //$res .= "<audio><source src=\"".$path."\" type=\"audio/mpeg\"><p>"._("Your browser does not support the audio element.")."</p></source></audio>";
                $res .= "<audio src=\"" . $path . "\" controls=\"controls\" preload=\"none\" style=\"width: 100%;\">" . _("Your browser does not support the audio element.") . "</audio>";
                break;
            case "oga":
            case "wav":
                $res .= " type : $extension<br>";
                $res .= "<audio src=\"" . $path . "\" controls=\"controls\" preload=\"auto\" style=\"width: 100%;\">" . _("Your browser does not support the audio element.") . "</audio>";
                break;
            case "m4a":
                $res .= " type : $extension<br>";
                $res .= "<audio src=\"" . $realPath . "\" controls=\"controls\" preload=\"auto\" style=\"width: 100%;\">" . _("Your browser does not support the audio element.") . "</audio>";
                break;
            case  "mp4":
                $res .= "type : $extension<br>";
                $res .= "<video width=\"100%\" controls  preload=\"auto\">\n";
                $res .= "<source src=\"" . $realPath . "\" type=\"video/mp4\">\n";
                $res .= _("Your browser does not support the video tag.") . "\n";
                $res .= "</video>";
                break;
            case  "ogg":
                $res .= "type : $extension<br>";
                $res .= "<video width=\"100%\" height=\"240\" controls  preload=\"auto\">\n";
                $res .= "<source src=\"" . $realPath . "\" type=\"video/ogg\">\n";
                $res .= _("Your browser does not support the video tag.") . "\n";
                $res .= "</video>";
                break;
            case "mov":
                $res .= "type : $extension<br>";
                $res .= "<video src=\"" . $realPath . "\"\n";
                $res .= "     controls\n";
                $res .= "     autoplay\n";
                $res .= "     height=\"270\" width=\"100%\"  preload=\"none\">\n";
                $res .= _("Your browser does not support the video tag.") . "\n";
                $res .= "</video>";
                break;
        }

        return $res;
    }

    public static function embedFiles($path)
    {
        $isexpandable = true;

        $uuid = MiscUtils::gen_uuid();

        $filename = basename($path);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $res = _("File") . " : <a href=\"" . $path . "\">\"" . $filename . "\"</a><br>";

        if (!$isexpandable) return;

        switch (strtolower($extension)) {
            case "jpg":
            case "jpeg":
            case "png":
                if ($isexpandable) {
                    $res .= '<a href="#' . $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . _("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
                }
                $res .= '<img src="' . $path . '" style="width: 500px"/>';
                if ($isexpandable) {
                    $res .= "</div>";
                }
                break;
            case "txt":
            case "ps1":
            case "c":
            case "cpp":
            case "php":
            case "js":
            case "mm":
            case "vcf":
            case "py":
            case "mm":
            case "swift":
            case "sh":
            case "ru":
            case "asp":
            case "m":
            case "vbs":
            case "admx":
            case "adml":
            case "ics":
            case "csv":
            case "sql":
                $content = file_get_contents(dirname(__FILE__) . "/../.." . $path);
                $content = nl2br(mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)));

                if ($isexpandable) {
                    $res .= '<a href="#' . $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . _("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
                }
                $res .= $content;
                if ($isexpandable) {
                    $res .= '</div>';
                }
                break;
            case "pdf":
                if ($isexpandable) {
                    $res .= '<a href="#' . $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . _("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
                }
                $res .= "<object data=\"" . $path . "\" type=\"application/pdf\" style=\"width: 500px;height:500px\">";
                $res .= "<embed src=\"" . $path . "\" type=\"application/pdf\" />\n";
                $res .= "</object>";
                if ($isexpandable) {
                    $res .= "</div>";
                }
                break;
            case "mp3":
            case "m4a":
            case "oga":
            case "wav":
                $res .= " type : $extension<br>";
                if ($isexpandable) {
                    $res .= '<a href="#' . $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . _("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
                }
                $res .= "<audio src=\"" . $path . "\" controls=\"controls\" preload=\"none\" style=\"width: 200px;\">" . _("Your browser does not support the audio element.") . "</audio>";
                if ($isexpandable) {
                    $res .= "</div>";
                }
                break;
            case  "mp4":
                $res .= "type : $extension<br>";
                $res .= '<a href="#' . $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . _("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
                $res .= "<video width=\"320\" height=\"240\" controls  preload=\"none\">\n";
                $res .= "<source src=\"" . $path . "\" type=\"video/mp4\">\n";
                $res .= _("Your browser does not support the video tag.") . "\n";
                $res .= "</video>";
                $res .= "</div>";
                break;
            case  "ogg":
                $res .= "type : $extension<br>";
                if ($isexpandable) {
                    $res .= '<a href="#' . $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . _("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
                }
                $res .= "<video width=\"320\" height=\"240\" controls  preload=\"none\">\n";
                $res .= "<source src=\"" . $path . "\" type=\"video/ogg\">\n";
                $res .= _("Your browser does not support the video tag.") . "\n";
                $res .= "</video>";
                if ($isexpandable) {
                    $res .= "</div>";
                }
                break;
            case "mov":
                $res .= "type : $extension<br>";
                if ($isexpandable) {
                    $res .= '<a href="#' . $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . _("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
                }
                $res .= "<video src=\"" . $path . "\"\n";
                $res .= "     controls\n";
                $res .= "     autoplay\n";
                $res .= "     height=\"270\" width=\"480\"  preload=\"none\">\n";
                $res .= _("Your browser does not support the video tag.") . "\n";
                $res .= "</video>";
                if ($isexpandable) {
                    $res .= "</div>";
                }
                break;
        }

        return $res;
    }

    public static function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


    public static function noteType($notetype)
    {
        $type = '';

        switch ($notetype) {
            case 'note':
            case 'document':
                $type = _("Classic Document");
                break;
            case 'video':
                $type = _("Classic Video");
                break;
            case 'file':
                $type = _("Classic File");
                break;
            case 'audio':
                $type = _("Classic Audio");
                break;
        }

        return $type;
    }

    public static function urlExist($url = 0)
    {
        $file_headers = @get_headers($url);
        if ($file_headers[0] == 'HTTP/1.1 404 Not Found')
            return false;

        return true;
    }

    public static function random_color_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    public static function random_color()
    {
        return MiscUtils::random_color_part() . MiscUtils::random_color_part() . MiscUtils::random_color_part();
    }

    public static function random_word($length = 6)
    {
        $cons = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'z', 'pt', 'gl', 'gr', 'ch', 'ph', 'ps', 'sh', 'st', 'th', 'wh');
        $cons_cant_start = array('ck', 'cm', 'dr', 'ds', 'ft', 'gh', 'gn', 'kr', 'ks', 'ls', 'lt', 'lr', 'mp', 'mt', 'ms', 'ng', 'ns', 'rd', 'rg', 'rs', 'rt', 'ss', 'ts', 'tch');
        $vows = array('a', 'e', 'i', 'o', 'u', 'y', 'ee', 'oa', 'oo');
        $current = (mt_rand(0, 1) == '0' ? 'cons' : 'vows');
        $word = '';
        while (strlen($word) < $length) {
            if (strlen($word) == 2) $cons = array_merge($cons, $cons_cant_start);
            $rnd = ${$current}[mt_rand(0, count(${$current}) - 1)];
            if (strlen($word . $rnd) <= $length) {
                $word .= $rnd;
                $current = ($current == 'cons' ? 'vows' : 'cons');
            }
        }
        return $word;
    }

    public static function getRandomCache($baseCacheTime, $variability)
    {
        $var = rand(0, $variability);
        $dir = rand(0, 1);
        if ($dir) {
            return $baseCacheTime - $var;
        } else {
            return $baseCacheTime + $var;
        }

    }

    public static function getPhotoCacheExpirationTimestamp()
    {
        $cacheLength = SystemConfig::getValue('iPhotoClientCacheDuration');
        $cacheLength = MiscUtils::getRandomCache($cacheLength, 0.5 * $cacheLength);
        //echo time() +  $cacheLength;
        //die();
        return time() + $cacheLength;
    }

    public static function FontFromName($fontname)
    {
        $fontinfo = explode(' ', $fontname);
        switch (count($fontinfo)) {
            case 1:
                return [$fontinfo[0], ''];
            case 2:
                return [$fontinfo[0], mb_substr($fontinfo[1], 0, 1)];
            case 3:
                return [$fontinfo[0], mb_substr($fontinfo[1], 0, 1) . mb_substr($fontinfo[2], 0, 1)];
        }
    }

    // Formats a fiscal year string
    public static function MakeFYString($iFYID)
    {
        $monthNow = date('m');

        if (SystemConfig::getValue('iFYMonth') == 1) {
            return 1996 + $iFYID;
        } else {
            return 1995 + $iFYID . '/' . mb_substr(1996 + $iFYID, 2, 2);
        }
    }

    // Returns the current fiscal year
    public static function CurrentFY()
    {
        $yearNow = date('Y');
        $monthNow = date('m');
        $FYID = $yearNow - 1996;
        if ($monthNow >= SystemConfig::getValue('iFYMonth') && SystemConfig::getValue('iFYMonth') > 1) {
            $FYID += 1;
        }

        return $FYID;
    }

    // PrintFYIDSelect: make a fiscal year selection menu.
    public static function PrintFYIDSelect($iFYID, $selectName)
    {
        echo '<select class= "form-control form-control-sm" name="' . $selectName . '">';
        echo '<option value="0">' . _('Select Fiscal Year') . '</option>';

        for ($fy = 1; $fy < MiscUtils::CurrentFY() + 2; $fy++) {
            echo '<option value="' . $fy . '"';
            if ($iFYID == $fy) {
                echo ' selected';
            }
            echo '>';
            echo MiscUtils::MakeFYString($fy);
        }
        echo '</select>';
    }

    //
    // Collapses a formatted phone number as long as the Country is known
    // Eg. for United States:  555-555-1212 Ext. 123 ==> 5555551212e123
    //
    // Need to add other countries besides the US...
    //
    public static function CollapsePhoneNumber($sPhoneNumber, $sPhoneCountry)
    {
        switch ($sPhoneCountry) {
            case 'United States':
                $sCollapsedPhoneNumber = '';
                $bHasExtension = false;

                // Loop through the input string
                for ($iCount = 0; $iCount <= strlen($sPhoneNumber); $iCount++) {

                    // Take one character...
                    $sThisCharacter = mb_substr($sPhoneNumber, $iCount, 1);

                    // Is it a number?
                    if (ord($sThisCharacter) >= 48 && ord($sThisCharacter) <= 57) {
                        // Yes, add it to the returned value.
                        $sCollapsedPhoneNumber .= $sThisCharacter;
                    } // Is the user trying to add an extension?
                    elseif (!$bHasExtension && ($sThisCharacter == 'e' || $sThisCharacter == 'E')) {
                        // Yes, add the extension identifier 'e' to the stored string.
                        $sCollapsedPhoneNumber .= 'e';
                        // From now on, ignore other non-digits and process normally
                        $bHasExtension = true;
                    }
                }
                break;

            default:
                $sCollapsedPhoneNumber = $sPhoneNumber;
                break;
        }

        return $sCollapsedPhoneNumber;
    }

    //
    // Expands a collapsed phone number into the proper format for a known country.
    //
    // If, during expansion, an unknown format is found, the original will be returned
    // and the a boolean flag $bWeird will be set.  Unfortunately, because PHP does not
    // allow for pass-by-reference in conjunction with a variable-length argument list,
    // a dummy variable will have to be passed even if this functionality is unneeded.
    //
    // Need to add other countries besides the US...
    //
    public static function ExpandPhoneNumber($sPhoneNumber, $sPhoneCountry, &$bWeird)
    {
        // this is normally unusefull

        /*$bWeird = false;
        $length = strlen($sPhoneNumber);

        switch ($sPhoneCountry) {
          case 'United States':
            if ($length == 0) {
                return '';
            } // 7 digit phone # with extension
            elseif (mb_substr($sPhoneNumber, 7, 1) == 'e') {
                return mb_substr($sPhoneNumber, 0, 3).'-'.mb_substr($sPhoneNumber, 3, 4).' Ext.'.mb_substr($sPhoneNumber, 8, 6);
            } // 10 digit phone # with extension
            elseif (mb_substr($sPhoneNumber, 10, 1) == 'e') {
                return mb_substr($sPhoneNumber, 0, 3).'-'.mb_substr($sPhoneNumber, 3, 3).'-'.mb_substr($sPhoneNumber, 6, 4).' Ext.'.mb_substr($sPhoneNumber, 11, 6);
            } elseif ($length == 7) {
                return mb_substr($sPhoneNumber, 0, 3).'-'.mb_substr($sPhoneNumber, 3, 4);
            } elseif ($length == 10) {
                return mb_substr($sPhoneNumber, 0, 3).'-'.mb_substr($sPhoneNumber, 3, 3).'-'.mb_substr($sPhoneNumber, 6, 4);
            } // Otherwise, there is something weird stored, so just leave it untouched and set the flag
            else {
                $bWeird = true;

                return $sPhoneNumber;
            }
            break;

          // If the country is unknown, we don't know how to format it, so leave it untouched
          default:
            return $sPhoneNumber;
        }*/

        return $sPhoneNumber;
    }

    //
    // Returns the correct address to use via the sReturnAddress arguments.
    // Function value returns 0 if no info was given, 1 if person info was used, and 2 if family info was used.
    // We do address lines 1 and 2 in together because seperately we might end up with half family address and half person address!
    //
    public static function SelectWhichAddress(&$sReturnAddress1, &$sReturnAddress2, $sPersonAddress1, $sPersonAddress2, $sFamilyAddress1, $sFamilyAddress2, $bFormat = false)
    {
        if (SystemConfig::getValue('bShowFamilyData')) {
            if ($bFormat) {
                $sFamilyInfoBegin = "<span style='color: red;'>";
                $sFamilyInfoEnd = '</span>';
            }

            if ($sPersonAddress1 || $sPersonAddress2) {
                $sReturnAddress1 = $sPersonAddress1;
                $sReturnAddress2 = $sPersonAddress2;

                return 1;
            } elseif ($sFamilyAddress1 || $sFamilyAddress2) {
                if ($bFormat) {
                    if ($sFamilyAddress1) {
                        $sReturnAddress1 = $sFamilyInfoBegin . $sFamilyAddress1 . $sFamilyInfoEnd;
                    } else {
                        $sReturnAddress1 = '';
                    }
                    if ($sFamilyAddress2) {
                        $sReturnAddress2 = $sFamilyInfoBegin . $sFamilyAddress2 . $sFamilyInfoEnd;
                    } else {
                        $sReturnAddress2 = '';
                    }

                    return 2;
                } else {
                    $sReturnAddress1 = $sFamilyAddress1;
                    $sReturnAddress2 = $sFamilyAddress2;

                    return 2;
                }
            } else {
                $sReturnAddress1 = '';
                $sReturnAddress2 = '';

                return 0;
            }
        } else {
            if ($sPersonAddress1 || $sPersonAddress2) {
                $sReturnAddress1 = $sPersonAddress1;
                $sReturnAddress2 = $sPersonAddress2;

                return 1;
            } else {
                $sReturnAddress1 = '';
                $sReturnAddress2 = '';

                return 0;
            }
        }
    }

    /******************************************************************************
     * Returns the proper information to use for a field.
     * Person info overrides Family info if they are different.
     * If using family info and bFormat set, generate HTML tags for text color red.
     * If neither family nor person info is available, return an empty string.
     *****************************************************************************/
    public static function SelectWhichInfo($sPersonInfo, $sFamilyInfo, $bFormat = false)
    {
        $finalData = '';
        $isFamily = false;

        if (SystemConfig::getValue('bShowFamilyData')) {
            if ($sPersonInfo != '') {
                $finalData = $sPersonInfo;
            } elseif ($sFamilyInfo != '') {
                $isFamily = true;
                $finalData = $sFamilyInfo;
            }
        } elseif ($sPersonInfo != '') {
            $finalData = $sPersonInfo;
        }

        if ($bFormat && $isFamily) {
            $finalData = $finalData . "<i class='fas  fa-tree'></i>";
        }

        return $finalData;
    }

    public static function generateGroupRoleEmailDropdown($roleEmails, $href)
    {
        $res = "";

        foreach ($roleEmails as $role => $Email) {
            if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($Email, SystemConfig::getValue('sToEmailAddress'))) {
                $Email .= SessionUser::getUser()->MailtoDelimiter() . SystemConfig::getValue('sToEmailAddress');
            }
            $Email = urlencode($Email);  // Mailto should comply with RFC 2368

            $res .= '<a class="dropdown-item" href="' . $href . mb_substr($Email, 0, -3) . '" class="dropdown-item">' . _($role) . '</a>';

        }

        return $res;
    }

    public static function ConvertToStringBoolean($sInput)
    {
        if (empty($sInput)) {
            return false;
        } else {
            if (is_numeric($sInput)) {
                if ($sInput == 1) {
                    return 'true';
                } else {
                    return 'false';
                }
            } else {
                $sInput = strtolower($sInput);
                if (in_array(strtolower($sInput), ['true', 'yes', 'si'])) {
                    return 'true';
                } else {
                    return 'false';
                }
            }
        }
    }

    public static function ConvertFromBoolean($sInput)
    {
        if ($sInput) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function ChopLastCharacter($sText)
    {
        return mb_substr($sText, 0, mb_strlen($sText) - 1);
    }

    public static function AlternateRowStyle($sCurrentStyle)
    {
        if ($sCurrentStyle == 'RowColorA') {
            return 'RowColorB';
        } else {
            return 'RowColorA';
        }
    }

    public static function genGroupKey($methodSpecificID, $famID, $fundIDs, $date)
    {
        $uniqueNum = 0;
        while (1) {
            $GroupKey = $methodSpecificID . '|' . $uniqueNum . '|' . $famID . '|' . $fundIDs . '|' . $date;

            $pledgeSearch = PledgeQuery::Create()
                ->withColumn('COUNT(plg_GroupKey)', 'NumGroupKeys')
                ->filterByPledgeorpayment("Payment")
                ->_and()->filterByGroupkey($GroupKey)
                ->findOne();

            if (!is_null($pledgeSearch) && $pledgeSearch->getNumGroupKeys()) {
                ++$uniqueNum;
            } else {
                return $GroupKey;
            }
        }
    }

    public static function requireUserGroupMembership($allowedRoles = null)
    {
        if (isset($_SESSION['updateDataBase']) && $_SESSION['updateDataBase'] == true) {// we don't have to interfer with this test
            return true;
        }

        if (!$allowedRoles) {
            throw new \Exception('Role(s) must be defined for the function which you are trying to access.  End users should never see this error unless something went horribly wrong.');
        }
        if ($_SESSION[$allowedRoles] || SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isAddRecordsEnabled()) {  //most of the time the API endpoint will specify a single permitted role, or the user is an admin
            // new SessionUser::getUser()->isAddRecordsEnabled() : Philippe Logel
            return true;
        } elseif (is_array($allowedRoles)) {  //sometimes we might have an array of allowed roles.
            foreach ($allowedRoles as $role) {
                if ($_SESSION[$role]) {
                    // The current allowed role is in the user's session variable
                    return true;
                }
            }
        }

        //if we get to this point in the code, then the user is not authorized.
        throw new \Exception('User is not authorized to access ' . debug_backtrace()[1]['function'], 401);
    }

    public static function generateRandomString($length = 15)
    {
        return substr(sha1(rand()), 0, $length);
    }

    public static function createWordImageDir ()
    {
        //Vérifier toutes les parties phpWord
        $wordPath = "/Images/Word_Export_IMG/";
        $wordImagesDirectory = SystemURLs::getDocumentRoot() . $wordPath;

        if ( !file_exists($wordImagesDirectory) ) {
            mkdir($wordImagesDirectory, 0777, true);
        }
    }

    public static function removeWordImageDir ()
    {
        //Vérifier toutes les parties phpWord
        $wordPath = "/Images/Word_Export_IMG/";
        $wordImagesDirectory = SystemURLs::getDocumentRoot() . $wordPath;

        MiscUtils::removeDirectory($wordImagesDirectory);
    }

    /**
     *
     * Render an entire \DOMDocument
     *
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $section
     * @param \DOMNode
     * @param string $extras for ol and ul
     *
     * @return everything is in the section
     *
     * See : PeoplePersonController.php : saveNoteAsWordFile
     *
     */

    public static function RenderDOMNode(AbstractContainer $section, DOMNode $domNode, $extras = null) {
        $wordPath = "/Images/Word_Export_IMG/";
        $wordImagesDirectory = SystemURLs::getDocumentRoot() . $wordPath;

        foreach ($domNode->childNodes as $node)
        {
            if ( in_array($node->nodeName, self::types) ) {
                $array = false;
                foreach ($node->attributes as $attr) {
                    $array[$attr->localName] = $attr->nodeValue;
                }

                switch ($node->nodeName) {
                    case 'h1':
                        $extras = null;
                        $section->addText(
                            $node->nodeValue,
                            array('name' => 'Tahoma', 'size' => 14)
                        );
                        break;
                    case 'p':
                        $extras = null;
                        $newElement = $section->addTextRun(array('name' => 'courier', 'size' => 10));
                        /*if($node->hasChildNodes()) {
                            self::RenderDOMNode($newElement, $node, $extras);
                        }*/
                        break;
                    case 'img':
                        $extras = null;
                        $height = $height = 100;

                        if (isset ($array['style']) ){
                            // [style] => height:23px; width:23px
                            $buff = explode (";",$array['style']);
                            $height = str_replace(["px"," "],"",explode(":",$buff[0])[1]);
                            $width = str_replace(["px"," "],"",explode(":",$buff[1])[1]);
                        }

                        // we make a copy in the case of a link : http:// ....
                        $old_src = $array['src'];
                        $path = explode("?", $old_src);

                        $path_parts = pathinfo($path[0]);
                        $new_file = MiscUtils::generateRandomString(15).".".$path_parts['extension'];

                        copy($old_src, $wordImagesDirectory . $new_file);

                        $section->addImage(
                            $wordImagesDirectory . $new_file,
                            array(
                                'width'         => $width,
                                'height'        => $height,
                                'marginTop'     => -1,
                                'marginLeft'    => -1,
                                'wrappingStyle' => 'behind'
                            )
                        );
                        break;
                    case 'li':
                        if ($extras == 'ol') {
                            $numberStyleList = array('listType' => ListItem::TYPE_NUMBER);
                            $section->addListItem($node->nodeValue, 0, null, $numberStyleList);
                        } else {
                            $numberStyleList = array('listType' => ListItem::TYPE_BULLET_EMPTY);
                            $section->addListItem($node->nodeValue, 0, null, $numberStyleList);
                        }
                        break;
                    case 'a':
                        $extras = null;
                        $section->addLink($array['href'], $node->nodeValue,
                            array('name' => 'courier', 'size' => 10, 'color' => '0000FF')
                        );
                        break;
                    case 'ul':
                        $extras = 'ul';
                        break;
                    case 'ol':
                        $extras = 'ol';
                        break;
                    case 'strong':
                    case 'b':
                        $section->addText(
                            $node->nodeValue,
                            array('name' => 'courier', 'size' => 10, 'bold' => true)
                        );
                        break;
                    case 'em':
                    case 'i':
                        $section->addText(
                            $node->nodeValue,
                            array('name' => 'courier', 'size' => 10, 'italic' => true)
                        );
                        break;
                    case 'u':
                        $section->addText(
                            $node->nodeValue,
                            array('name' => 'courier', 'size' => 10,'underline' => 'single')
                        );
                        break;
                }
            }

            if($node->hasChildNodes()) {
                self::RenderDOMNode($section, $node, $extras);
            }
        }
    }

    public static function saveHtmlAsWordFile ($userName, $realNoteDir, $currentpath, $html, $title = null) {

        $html = str_replace(array("\n", "\r"), '', $html);

        MiscUtils::removeWordImageDir();

        MiscUtils::createWordImageDir();

        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        // we create the phpWord wrapper
        $pw = new PhpWord();

        // the fonts
        $fontStyleName = 'NormalFontStyle';
        $pw->addFontStyle(
            $fontStyleName,
            array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232')
        );

        $fontStyleName = 'BoldFontStyle';
        $pw->addFontStyle(
            $fontStyleName,
            array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232', 'bold' => true)
        );

        // [THE HTML]
        $section = $pw->addSection();

        MiscUtils::RenderDOMNode ($section, $doc);

        // we set a random title
        if (is_null($title)) {
            $title = "note_" . MiscUtils::generateRandomString(5);
        }

        // [SAVE FILE ON THE SERVER]
        $filePath = $userName . $currentpath . $title.".docx";
        $tmpFile = dirname(__FILE__)."/../../".$realNoteDir."/".$filePath;

        // Saving the document as OOXML file...
        $objWriter = IOFactory::createWriter($pw, 'Word2007');
        $objWriter->save($tmpFile);

        MiscUtils::removeWordImageDir();

        return ['tmpFile' => $tmpFile, 'FilePath' => $filePath, 'title' => $title];
    }

    // Quality is a number between 0 (best compression) and 100 (best quality)
    public static function png2jpg($originalFile, $outputFile, $quality=100) {
        $image = imagecreatefrompng($originalFile);
        imagejpeg($image, $outputFile, $quality);
        imagedestroy($image);
    }

    public static function replace_img_src($img_tag) {

        //Vérifier toutes les parties phpWord
        $wordPath = "/Images/Word_Export_IMG/";
        $wordImagesDirectory = SystemURLs::getDocumentRoot() . $wordPath;
        $rootPath = SystemURLs::getRootPath();

        if ( !empty($rootPath) ) {
            $rootPath = $rootPath."/";
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML(mb_convert_encoding($img_tag, 'HTML-ENTITIES', 'UTF-8'));
        $tags = $doc->getElementsByTagName('img');

        $url = 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://' . $rootPath . $_SERVER['HTTP_HOST']."$wordPath";

        foreach ($tags as $tag) {
            $old_src = $tag->getAttribute('src');

            $path = explode("?", $old_src);

            $path_parts = pathinfo($path[0]);
            $new_file = MiscUtils::generateRandomString(15).".".$path_parts['extension'];
            //$new_file = MiscUtils::generateRandomString(15).".jpg";

            /*if ($path_parts['extension'] != 'png') {
                MiscUtils::png2jpg($old_src, $wordImagesDirectory . $new_file);
            } else {*/
            copy($old_src, $wordImagesDirectory . $new_file);
            //}

            $new_src_url = $url . $new_file;//'website.com/assets/'.$old_src;

            $tag->setAttribute('src', $new_src_url);
            $tag->setAttribute('alt', "coucou");
        }

        $res = $doc->saveHTML();

        $body = $doc->getElementsByTagName('body');
        if ( $body && 0<$body->length ) {
            $body = $body->item(0);
            $res = $doc->savehtml($body);

            $res = str_replace(["<body>", "</body>"], "", $res);
        }

        $res = preg_replace("/<img([^>]+)\>/i", "<img $1 />", $res);
        $res = str_replace(["<hr>", "<br>"], ["<hr/>", "<br/>"], $res);

        LoggerUtils::getAppLogger()->info("code html apres : " . $res);

        return $res;
    }

    public static function saveHtmlAsWordFilePhpWord ($userName, $realNoteDir, $currentpath, $html, $title = null)
    {
        MiscUtils::removeWordImageDir();

        MiscUtils::createWordImageDir();

        $pw = new PhpWord();

        // [THE HTML]
        $section = $pw->addSection();

        $options = null;

        /*$options['styles'] = [
            'head1' => ['name' => 'Tahoma', 'size' => 14, 'color' => '1B2232', 'bold' => true],
            'paragraph' => ['name' => 'courier', 'size' => 10, 'color' => '111111', 'bold' => true],
            'font' =>  ['name' => 'courier', 'size' => 10, 'color' => '111111', 'bold' => true]
        ];*/
        //PhpWordHTMLExtension::addHtml($section, MiscUtils::replace_img_src($html) , false, false, $options);
        Html::addHtml( $section, MiscUtils::replace_img_src($html) , false, false, $options);

        // we set a random title
        if (is_null($title)) {
            $title = "note_".MiscUtils::generateRandomString(5);
        }

        // we set a random title

        // [SAVE FILE ON THE SERVER]
        $filePath = $userName . $currentpath . $title.".docx";
        $tmpFile = dirname(__FILE__)."/../../".$realNoteDir."/".$filePath;

        $pw->save($tmpFile, "Word2007");

        MiscUtils::removeWordImageDir();

        return ['tmpFile' => $tmpFile, 'FilePath' => $filePath, 'title' => $title];
    }

    public static function RunQuery($sSQL, $bStopOnError = true)
    {
        global $cnInfoCentral;
        mysqli_query($cnInfoCentral, "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        if ($result = mysqli_query($cnInfoCentral, $sSQL)) {
            return $result;
        } elseif ($bStopOnError) {
            if (SystemConfig::getValue('sLogLevel') == "100") { // debug level
                die(_('Cannot execute query.')."<p>$sSQL<p>".mysqli_error($cnInfoCentral));
            } else {
                die('Database error or invalid data');
            }
        } else {
            return false;
        }
    }

    public static function pluginInformations ()
    {
        if (SessionUser::getCurrentPageName() == 'v2/dashboard') {
            // only dashboard plugins are loaded on the maindashboard page
            $plugins = PluginQuery::create()
                ->filterByCategory('Dashboard', Criteria::EQUAL )
                ->findByActiv(true);


        } else {
            $plugins = PluginQuery::create()
                ->filterByCategory('Dashboard', Criteria::NOT_EQUAL )
                ->findByActiv(true);
        }

        $pluginNames = "false";
        $isMailerAvalaible = "false";

        if ( $plugins->count() > 0 ) {
            $pluginNames = "{";
            foreach ($plugins as $plugin) {
                $pluginNames .= "'" . $plugin->getName() . "':'window.CRM." . $plugin->getName() . "_i18keys', ";
                if ( $plugin->isMailer() and SessionUser::getUser()->isAdminEnableForPlugin($plugin->getName() )) {
                    $isMailerAvalaible = "true";
                }
            }

            $pluginNames = substr($pluginNames, 0, -2);

            $pluginNames .= "}";
        }

        return ["pluginNames" => $pluginNames, "isMailerAvalaible" => $isMailerAvalaible];
    }

    public static function SanitizeExtension ($ext)
    {
        if (in_array($ext, self::extensions_to_sanitize) ) {
            return "txt";
        }
        return $ext;
    }

    public static function mb_ucfirst ($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    // check if https is available
    public static function isSecure() {
        return
          (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || $_SERVER['SERVER_PORT'] == 443;
    }
}
