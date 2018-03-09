<?php 
  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  $connection = Propel::getConnection();
  $logger = LoggerUtils::getAppLogger();

  $logger->info("Start to delete : old unuseful files");

  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/PersonPropertyQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/PersonProperty.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/PersonPropertyQuery.php");
  unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/PersonProperty.php");
  unlink(SystemURLs::getDocumentRoot()."/PropertyAssign.php");
  unlink(SystemURLs::getDocumentRoot()."/PropertyUnassign.php");
  
  $logger->info("End Delete the old unuseful files");
?>
