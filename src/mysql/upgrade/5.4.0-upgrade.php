<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.4.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;

  function removeDirectory($path) {
    $files = glob($path . '/*');
    foreach ($files as $file) {
      is_dir($file) ? removeDirectory($file) : unlink($file);
    }
    rmdir($path);
    return;
  }

  $logger = LoggerUtils::getAppLogger();
  
  $logger->info("Start to delete : all unusefull files");
  
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/adminlte/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/bootstrap-toggle/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/bootstrap-validator/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/bootbox/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/editor/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/randomcolor/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/ionicons/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/locale/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/jquery-ui/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/jquery-photo-uploader/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/fullcalendar/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/fastclick/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/font-awesome/");
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/moment/");

  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/test/.DS_Store");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/src/.DS_Store");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.DS_Store");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.babelrc");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.codeclimate.yml");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.coveralls.yml");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.editorconfig");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.eslintignore");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.eslintignore");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.eslintrc");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.npmignore");
  unlink(SystemURLs::getDocumentRoot()."/skin/i18next/.travis.yml");
  
  removeDirectory(SystemURLs::getDocumentRoot()."/skin/i18next/");
  
  $logger->info("End of Reset :  all unusefull files");
?>
