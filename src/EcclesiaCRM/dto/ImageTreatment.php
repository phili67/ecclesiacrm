<?php

namespace EcclesiaCRM\dto;

class ImageTreatment
{
  public static function imageCreateFromAny($filepath) { 
     $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize() 
     
     $allowedTypes = array( 
         1,   // [] gif 
         2,   // [] jpg 
         3,   // [] png 
         6    // [] bmp 
     ); 
     
     if (!in_array($type, $allowedTypes)) { 
         return false; 
     }
     
     switch ($type) { 
         case 1 : 
             $im = imageCreateFromGif($filepath); 
         break; 
         case 2 : 
             $im = imageCreateFromJpeg($filepath); 
         break; 
         case 3 : 
             $im = imageCreateFromPng($filepath); 
         break; 
         case 6 : 
             $im = imageCreateFromBmp($filepath); 
         break; 
     }
     
     return ['image' => $im,'type' => $type];
  }

  public static function saveImageCreateFromAny($rec,$filepath) { 
       if ($rec == false) {
          return false;
       }
       switch ($rec['type']) { 
           case 1 : 
               $im = imageCreateFromGif($filepath); 
           break; 
           case 2 : 
               imagejpeg($rec['image'],$filepath);
           break; 
           case 3 : 
               $im = imageCreateFromPng($filepath); 
           break; 
           case 6 : 
               $im = imageCreateFromBmp($filepath); 
           break; 
       }
     
       return ['image' => $im,'type' => $rec['type']];
  }
}