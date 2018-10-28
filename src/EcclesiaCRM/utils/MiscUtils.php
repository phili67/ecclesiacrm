<?php

namespace EcclesiaCRM\Utils;
use EcclesiaCRM\dto\SystemConfig;

use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Sharing;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\VObject;
use EcclesiaCRM\MyVCalendar;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL;
use EcclesiaCRM\Utils\OutputUtils;

class MiscUtils {
  
  
/**
 * Remove the directory and its content (all files and subdirectories).
 * @param string $dir the directory name
 */
  public static function delTree($dir) { 
   $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
      (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
  } 
  
  public static function pathToPathWithIcons ($path) {
    $items = explode('/', $path);
    
    $res = "";
    $len = count($items);
    
    $first = true;
    for ($i=0;$i<$len;$i++) {
      if ($first == true) {
        $res = "<i class='fa fa-home text-aqua'></i> ".gettext("Home");
        
        if ($len > 2) {
           $res .= " <i class='fa fa-caret-right'></i>";
        }
        $first = false;
      }
      
      if (!empty ($items[$i]) ){
        $res .= "&nbsp;&nbsp;<i class='fa fa-folder-o text-yellow'></i> ".$items[$i];
        
        if ($i != $len-2) {
          $res .= "&nbsp;&nbsp;<i class='fa fa-caret-right'></i>";
        }
      }
    }
    
    return $res;
  }

/**
 * return all the directories in
 * @param string $path string $basePath
 */  
  public static function getDirectoriesInPath($dir) {
    $dirs = glob( ".".$dir . "*", GLOB_ONLYDIR );
    
    return $dirs;
  }
  
/**
 * return all the directories in
 * @param string $path string $basePath
 */  
  public static function getImagesInPath($dir) {
    $files = glob($dir."/*.{jpg,gif,png,html,htm,php,ini}", GLOB_BRACE);
    
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

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            //$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            $result = OutputUtils::number_localized($result)." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

/**
 * return true when the path in the basePath is a real file
 * @param string $path string $basePath
 */  
  public static function isRealFile ($path,$basePath) {
    $test = str_replace($basePath, "", $path);
    
    $res = strstr( $test, "/");
    
    if ( strlen($res) > 0 ) {
      return false;
    }
    
    return true;
  }
  
  public static function getRealDirectory ($path,$basePath) {
    return str_replace(".".$basePath, "", $path);
  }
  
  public static function FileIcon ($path)
  {
    $filename = basename($path);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    switch (strtolower($extension)) {
      case "doc":
      case "docx":
      case "odt":
        $icon = 'fa-file-word-o text-blue ';
        break;
      case "ics":
        $icon = 'fa-calendar-o text-red';
        break;
      case "sql":
        $icon = 'fa-database text-red';
        break;
      case "xls":
      case "xlsx":
      case "ods":
      case "csv":
        $icon = ' fa-file-excel-o text-olive';
        break;
      case "xls":
      case "xlsx":
      case "ods":
        $icon = ' fa-file-powerpoint-o text-red';
        break;
      case "jpg":
      case "jpeg":
      case "png":
        $icon = 'fa-file-photo-o text-teal';
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
        $icon = 'fa-file-code-o text-black';
        break;
      case "pdf":
        $icon = 'fa-file-pdf-o  text-red';
        break;
      case "mp3":
      case "m4a":
      case "oga":
      case "wav":
        $icon = 'fa-file-sound-o  text-green';
        break;
      case  "mp4":
        $icon = 'fa-file-video-o  text-blue';
        break;
      case  "ogg":
        $icon = 'fa-file-video-o   text-blue';
        break;
      case "mov":
        $icon = 'fa-file-video-o  text-blue';
        break;
      default:
        $icon = "fa-file-o text-blue";
        break;        
    }
    
    return $icon." bg-gray-light";
  }
  
  public static function simpleEmbedFiles ($path,$realPath=nil) {
    $uuid = MiscUtils::gen_uuid();

    $filename = basename($path);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    $res = ($extension == "")?(gettext("Folder")." : ".$filename):(gettext("File")." : <a href=\"".$path."\">\"".$filename."\"</a><br>");
    
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
        $res .= '<img src="'.$path.'" style="width: 100%"/>';
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
        $content = file_get_contents( dirname(__FILE__)."/../..".$realPath );
        $content = nl2br(mb_convert_encoding($content, 'UTF-8',mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)));
        
        $res .= '<div style="overflow: auto; width:100%; height:240px;border:1px;border-style: solid;border-color: lightgray;">';
        $res .= $content;
        $res .= '</div>';
        break;
      case "pdf":
        $res .= "<object data=\"".$realPath."\" type=\"application/pdf\" style=\"width: 100%;height:300px\">";
        $res .= "<embed src=\"".$realPath."\" type=\"application/pdf\" />\n";
        $res .= "<p>".gettext("You've to use a PDF viewer or download the file here ").': <a href="'.$realPath.'">télécharger le fichier.</a></p>';
        $res .= "</object>";
        break;
      case "mp3":
        $res .= " type : $extension<br>";
        //$res .= "<audio src=\"".$path."\" controls=\"controls\" preload=\"auto\" style=\"width: 100%;\" type=\"audio/mp3\">".gettext("Your browser does not support the audio element.")."</audio>";
        //$res .= "<audio><source src=\"".$path."\" type=\"audio/mpeg\"><p>".gettext("Your browser does not support the audio element.")."</p></source></audio>";
        $res .= "<audio src=\"".$path."\" controls=\"controls\" preload=\"none\" style=\"width: 100%;\">".gettext("Your browser does not support the audio element.")."</audio>";
        break;
      case "oga":
      case "wav":
        $res .= " type : $extension<br>";
        $res .= "<audio src=\"".$path."\" controls=\"controls\" preload=\"auto\" style=\"width: 100%;\">".gettext("Your browser does not support the audio element.")."</audio>";
        break;
      case "m4a":
        $res .= " type : $extension<br>";
        $res .= "<audio src=\"".$realPath."\" controls=\"controls\" preload=\"auto\" style=\"width: 100%;\">".gettext("Your browser does not support the audio element.")."</audio>";
        break;
      case  "mp4":
        $res .= "type : $extension<br>";
        $res .= "<video width=\"100%\" controls  preload=\"auto\">\n";
        $res .= "<source src=\"".$realPath."\" type=\"video/mp4\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        break;
      case  "ogg":
        $res .= "type : $extension<br>";
        $res .= "<video width=\"100%\" height=\"240\" controls  preload=\"auto\">\n";
        $res .= "<source src=\"".$realPath."\" type=\"video/ogg\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        break;
      case "mov":
        $res .= "type : $extension<br>";
        $res .= "<video src=\"".$realPath."\"\n";
        $res .= "     controls\n";
        $res .= "     autoplay\n";
        $res .= "     height=\"270\" width=\"100%\"  preload=\"none\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        break;
    }
    
    return $res;
  }
  public static function embedFiles ($path) {
    $isexpandable = true;
    
    $uuid = MiscUtils::gen_uuid();

    $filename = basename($path);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    $res = gettext("File")." : <a href=\"".$path."\">\"".$filename."\"</a><br>";
    
    if (!$isexpandable) return;
    
    switch (strtolower($extension)) {
      case "jpg":
      case "jpeg":
      case "png":
        if ($isexpandable) {
          $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        }
        $res .= '<img src="'.$path.'" style="width: 500px"/>';
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
        $content = file_get_contents( dirname(__FILE__)."/../..".$path );
        $content = nl2br(mb_convert_encoding($content, 'UTF-8',mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)));
        
        if ($isexpandable) {
          $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        }
        $res .= $content;
        if ($isexpandable) {
          $res .= '</div>';
        }
        break;
      case "pdf":
        if ($isexpandable) {
          $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        }
        $res .= "<object data=\"".$path."\" type=\"application/pdf\" style=\"width: 500px;height:500px\">";
        $res .= "<embed src=\"".$path."\" type=\"application/pdf\" />\n";
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
          $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        }
        $res .= "<audio src=\"".$path."\" controls=\"controls\" preload=\"none\" style=\"width: 200px;\">".gettext("Your browser does not support the audio element.")."</audio>";
        if ($isexpandable) {
          $res .= "</div>";
        }
        break;
      case  "mp4":
        $res .= "type : $extension<br>";
        $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        $res .= "<video width=\"320\" height=\"240\" controls  preload=\"none\">\n";
        $res .= "<source src=\"".$path."\" type=\"video/mp4\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        $res .= "</div>";
        break;
      case  "ogg":
        $res .= "type : $extension<br>";
        if ($isexpandable) {
          $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        }
        $res .= "<video width=\"320\" height=\"240\" controls  preload=\"none\">\n";
        $res .= "<source src=\"".$path."\" type=\"video/ogg\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        if ($isexpandable) {
          $res .= "</div>";
        }
        break;
      case "mov":
        $res .= "type : $extension<br>";
        if ($isexpandable) {
          $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        }
        $res .= "<video src=\"".$path."\"\n";
        $res .= "     controls\n";
        $res .= "     autoplay\n";
        $res .= "     height=\"270\" width=\"480\"  preload=\"none\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        if ($isexpandable) {
          $res .= "</div>";
        }
        break;        
    }
    
    return $res;
  }
  
  public static function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }

  
  public static function noteType($notetype) {
    $type = '';
    
    switch ($notetype) {
      case 'note':
        $type = gettext("Classic Note");
        break;
      case 'video':
        $type = gettext("Classic Video");
        break;
      case 'file':
        $type = gettext("Classic File");
        break;
      case 'audio':
        $type = gettext("Classic Audio");
        break;
    }
    
    return $type;
  }
 
  public static function urlExist( $url=0) {
    $file_headers = @get_headers($url);
    if($file_headers[0] == 'HTTP/1.1 404 Not Found')
       return false;

    return true;
  }

  public static function random_color_part()
  {
    return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
  }

  public static function random_color()
  {
    return MiscUtils::random_color_part().MiscUtils::random_color_part().MiscUtils::random_color_part();
  }

  public static function random_word( $length = 6 ) {
      $cons = array( 'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'z', 'pt', 'gl', 'gr', 'ch', 'ph', 'ps', 'sh', 'st', 'th', 'wh' );
      $cons_cant_start = array( 'ck', 'cm', 'dr', 'ds','ft', 'gh', 'gn', 'kr', 'ks', 'ls', 'lt', 'lr', 'mp', 'mt', 'ms', 'ng', 'ns','rd', 'rg', 'rs', 'rt', 'ss', 'ts', 'tch');
      $vows = array( 'a', 'e', 'i', 'o', 'u', 'y','ee', 'oa', 'oo');
      $current = ( mt_rand( 0, 1 ) == '0' ? 'cons' : 'vows' );
      $word = '';
      while( strlen( $word ) < $length ) {
          if( strlen( $word ) == 2 ) $cons = array_merge( $cons, $cons_cant_start );
          $rnd = ${$current}[ mt_rand( 0, count( ${$current} ) -1 ) ];
          if( strlen( $word . $rnd ) <= $length ) {
              $word .= $rnd;
              $current = ( $current == 'cons' ? 'vows' : 'cons' );
          }
      }
      return $word;
  }
  
  public static function getRandomCache($baseCacheTime,$variability){
    $var = rand(0,$variability);
    $dir = rand(0,1);
    if ($dir) {
      return $baseCacheTime - $var;
    }
    else{
      return $baseCacheTime + $var;
    }
    
  }
  
  public static function getPhotoCacheExpirationTimestamp() {
    $cacheLength = SystemConfig::getValue(iPhotoClientCacheDuration);
    $cacheLength = MiscUtils::getRandomCache($cacheLength,0.5*$cacheLength);
    //echo time() +  $cacheLength;
    //die();
    return time() + $cacheLength ;
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
        return [$fontinfo[0], mb_substr($fontinfo[1], 0, 1).mb_substr($fontinfo[2], 0, 1)];
    }
  }
}
?>