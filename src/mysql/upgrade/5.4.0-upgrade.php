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
  
  // clarification of the api directory  
  unlink(SystemURLs::getDocumentRoot()."/api/routes/volunteeropportunity.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/users.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/userrole.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/timerjobs.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/systemupgrade.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/system.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/sharedocument.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/roles.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/register.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/public-data.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/properties.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/pledges.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/persons.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/people.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/payments.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/pastoralcare.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/menulinks.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/mapicons.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/issues.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/groups.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/gdrp.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/filemanager.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/families.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/eventsV2.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/donationfunds.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/deposits.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/database.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/dashboard.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/custom-fields.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/ckeditor.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/calendarV2.php");
  unlink(SystemURLs::getDocumentRoot()."/api/routes/attendees.php");
  
  $logger->info("End of Reset :  all unusefull files");
?>
