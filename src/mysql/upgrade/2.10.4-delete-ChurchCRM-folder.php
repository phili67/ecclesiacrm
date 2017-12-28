<?php 

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : ChurchCRM folder");

  function delTree($dir) { 
   $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
  } 

  delTree(SystemURLs::getDocumentRoot()."/ChurchCRM");
  
  $logger->info("Delete the old ChurchCRM folder");
?>