<?php 
  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : old css files");

  unlink(SystemURLs::getDocumentRoot()."/skin/churchcrm.scss");
  unlink(SystemURLs::getDocumentRoot()."/skin/churchcrm.min.css.map");
  unlink(SystemURLs::getDocumentRoot()."/skin/churchcrm.min.css");
  
  $logger->info("End Delete the old old css files");
?>