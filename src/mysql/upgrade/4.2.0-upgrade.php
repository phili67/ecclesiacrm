<?php 
  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : old unuseful files");

  unlink(SystemURLs::getDocumentRoot()."/mysql/upgrade/rebuild_nav_menus.sql");
  
  $logger->info("End Delete the old unuseful files");
?>