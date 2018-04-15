<?php

namespace EcclesiaCRM\Utils;
use EcclesiaCRM\dto\SystemConfig;

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
  
  public static function embedFiles ($path) {
    $filename = basename($path);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    $res = "<a href=\"".$path."\"><i class=\"fa fa-file-o\"></i> \"".$filename."\"</a><br>";    
    
    switch (strtolower($extension)) {
      case "jpg":
      case "jpeg":
      case "png":
        $res .= "<img src=\"".$path."\" style=\"width: 500px\"/>";
        break;
      case "pdf":
        $res .= "<object data=\"".$path."\" type=\"application/pdf\" style=\"width: 500px;height:500px\">";
        $res .= "<embed src=\"".$path."\" type=\"application/pdf\" />\n";
        $res .= "</object>";
        break;
      case "mp3":
      case "m4a":
      case "oga":
      case "wav":
        $res .= " type : $extension<br><audio src=\"".$path."\" controls=\"controls\" preload=\"none\" style=\"width: 200px;\">".gettext("Your browser does not support the audio element.")."</audio>";        
        break;
      case  "mp4":
        $res .= "type : $extension<br><video width=\"320\" height=\"240\" controls  preload=\"none\">\n";
        $res .= "<source src=\"".$path."\" type=\"video/mp4\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        break;
      case  "ogg":
        $res .= "type : $extension<br><video width=\"320\" height=\"240\" controls  preload=\"none\">\n";
        $res .= "<source src=\"".$path."\" type=\"video/ogg\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        break;
      case "mov":
        $res .= "type : $extension<br><video src=\"".$path."\"\n";
        $res .= "     controls\n";
        $res .= "     autoplay\n";
        $res .= "     height=\"270\" width=\"480\"  preload=\"none\">\n";
        $res .= gettext("Your browser does not support the video tag.")."\n";
        $res .= "</video>";
        break;        
    }
    
    return $res;
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

}
?>