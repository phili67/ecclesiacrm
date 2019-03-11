<?php 
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/5.4.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

  use Propel\Runtime\Propel;
  use EcclesiaCRM\Utils\LoggerUtils;
  use EcclesiaCRM\dto\SystemURLs;
  use EcclesiaCRM\Utils\MiscUtils;

  $logger = LoggerUtils::getAppLogger();
  
  $logger->info("Start to delete : all unusefull files");
  
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/adminlte/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/bootstrap-toggle/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/bootstrap-validator/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/bootbox/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/editor/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/randomcolor/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/ionicons/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/locale/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/jquery-ui/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/jquery-photo-uploader/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/fullcalendar/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/fastclick/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/font-awesome/");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/moment/");

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
  
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/i18next/");
  
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
  unlink(SystemURLs::getDocumentRoot()."/api/routes/autopayement.php");
  
  // old unusefull file
  unlink(SystemURLs::getDocumentRoot()."/skin/js/PledgeEditor.js");

  // clarification of the js directory  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/CalendarSideBar.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/CalendarV2.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/EventEditor.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/GoogleMapEvent.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/OpenStreetMapEvent.js");

  unlink(SystemURLs::getDocumentRoot()."/skin/js/Checkin.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/EventNames.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/Events.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ListEvent.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/BingMapEvent.js");  
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ckeditor/calendar_event_editor_config.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ckeditor/campaign_editor_config.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ckeditor/event_editor_config.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ckeditor/note_editor_config.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ckeditorextension.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ckeditorExtraPlugin/icons/hidpi/.DS_Store");
  MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/js/ckeditorExtraPlugin/");

  unlink(SystemURLs::getDocumentRoot()."/skin/js/Checkin.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/EventNames.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/Events.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/ListEvent.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/EditEventAttendees.js");
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/FamilyCustomFieldsEditor.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/FundList.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/IconPicker.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/MenuLinksList.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/OptionManager.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/PastoralCareList.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/PersonCustomFieldsEditor.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/VolunteerOpportunity.js");

  unlink(SystemURLs::getDocumentRoot()."/skin/js/GroupEditor.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/GroupList.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/GroupRoles.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/GroupView.js");
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/FamilyList.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/FamilyVerify.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/FamilyView.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/MemberView.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/PersonEditor.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/PersonList.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/PersonView.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/PastoralCare.js");
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/DepositSlipEditor.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/FinancialReports.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/FindDepositSlip.js");
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/IssueReporter.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/Kiosk.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/KioskJSOM.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/setup.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/SystemSettings.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/Tooltips.js");
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/UserEditor.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/UserList.js");
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/GDPRDataStructure.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/GDRPDashboard.js");
  
  unlink(SystemURLs::getDocumentRoot()."/skin/js/SundaySchoolClassView.js");
  unlink(SystemURLs::getDocumentRoot()."/skin/js/SundaySchoolReports.js");
  
  $logger->info("End of Reset :  all unusefull files");
?>
