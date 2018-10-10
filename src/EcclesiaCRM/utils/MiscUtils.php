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

/**
 * return true when the path in the basePath is a real file
 * @param string $path string $basePath
 */  
  public static function getDirectoriesInPath($dir) {
    $files = glob( ".".$dir . "*", GLOB_ONLYDIR );
    
    return $files;
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
    
    $icon = "fa-file-o bg-blue";
    
    switch (strtolower($extension)) {
      case "doc":
      case "docx":
      case "odt":
        $icon = 'fa-file-word-o bg-blue';
        break;
      case "xls":
      case "xlsx":
      case "ods":
        $icon = ' fa-file-excel-o bg-green';
        break;
      case "xls":
      case "xlsx":
      case "ods":
        $icon = ' fa-file-powerpoint-o bg-red';
        break;
      case "jpg":
      case "jpeg":
      case "png":
        $icon = 'fa-file-photo-o bg-aqua';
        break;
      case "txt":
      case "ps1":
      case "c":
      case "cpp":
      case "php":
      case "js":
      case "mm":
      case "vcf":
        $icon = 'fa-file-code-o';
        break;
      case "pdf":
        $icon = 'fa-file-pdf-o  bg-red';
        break;
      case "mp3":
      case "m4a":
      case "oga":
      case "wav":
        $icon = 'fa-file-sound-o  bg-green';
        break;
      case  "mp4":
        $icon = 'fa-file-video-o  bg-blue';
        break;
      case  "ogg":
        $icon = 'fa-file-video-o   bg-blue';
        break;
      case "mov":
        $icon = 'fa-file-video-o  bg-blue';
        break;        
    }
    
    return $icon;
  }
  
  public static function embedFiles ($path) {
    $uuid = MiscUtils::gen_uuid();

    $filename = basename($path);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    $res = gettext("File")." : <a href=\"".$path."\">\"".$filename."\"</a><br>";
    
    switch (strtolower($extension)) {
      case "jpg":
      case "jpeg":
      case "png":
        $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        $res .= '<img src="'.$path.'" style="width: 500px"/>';
        $res .= "</div>";
        break;
      case "txt":
      case "ps1":
      case "c":
      case "cpp":
      case "php":
      case "js":
      case "mm":
      case "vcf":
        $content = file_get_contents( dirname(__FILE__)."/../..".$path );
        $content = nl2br(mb_convert_encoding($content, 'UTF-8',mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)));
        
        $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">'.$content.'</div>';
        break;
      case "pdf":
        $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        $res .= "<object data=\"".$path."\" type=\"application/pdf\" style=\"width: 500px;height:500px\">";
        $res .= "<embed src=\"".$path."\" type=\"application/pdf\" />\n";
        $res .= "</object>";
        $res .= "</div>";
        break;
      case "mp3":
      case "m4a":
      case "oga":
      case "wav":
        $res .= " type : $extension<br>";
        $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        $res .= "<audio src=\"".$path."\" controls=\"controls\" preload=\"none\" style=\"width: 200px;\">".gettext("Your browser does not support the audio element.")."</audio>";
        $res .= "</div>";
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
        $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        $res .= "<video width=\"320\" height=\"240\" controls  preload=\"none\">\n";
        $res .= "<source src=\"".$path."\" type=\"video/ogg\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        $res .= "</div>";
        break;
      case "mov":
        $res .= "type : $extension<br>";
        $res .= '<a href="#'. $uuid . '" data-toggle="collapse" class="btn btn-xs btn-warning">' . gettext("Expand") . '</a><br><div id="' . $uuid . '" class="collapse" style="font-size:12px">';
        $res .= "<video src=\"".$path."\"\n";
        $res .= "     controls\n";
        $res .= "     autoplay\n";
        $res .= "     height=\"270\" width=\"480\"  preload=\"none\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        $res .= "</div>";
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
        $type = gettext("Classic Document");
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