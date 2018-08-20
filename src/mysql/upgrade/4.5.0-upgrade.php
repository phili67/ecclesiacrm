<?php 
  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : old unuseful files");

  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/UserProfileQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/UserProfile.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/UserProfileQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/UserProfile.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Map/UserProfileTableMap.php");
    
  $logger->info("End Delete the old unuseful files");
?>
