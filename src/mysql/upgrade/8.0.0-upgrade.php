<?php
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/8.0.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot()."/Include/GetGroupArray.php");
unlink(SystemURLs::getDocumentRoot()."/RPCdummy.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/Reports/ChurchInfoReport.php");

unlink(SystemURLs::getDocumentRoot()."/ListEvents.php");
unlink(SystemURLs::getDocumentRoot()."/GetText.php");
unlink(SystemURLs::getDocumentRoot()."/skin/js/event/ListEvent.js");

unlink(SystemURLs::getDocumentRoot()."/EditEventAttendees.php");

MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/external/font-awesome/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/external/jquery-photo-uploader/");

unlink(SystemURLs::getDocumentRoot()."/Images/Bank.png");
unlink(SystemURLs::getDocumentRoot()."/Images/Group.png");
unlink(SystemURLs::getDocumentRoot()."/Images/Money.png");

// 2022-02-07 now jitsi meeting is now a plugin !
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/PersonLastMeeting.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/PersonLastMeetingQuery.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/PersonMeeting.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/PersonMeetingQuery.php");

unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/PersonLastMeeting.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/PersonLastMeetingQuery.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/PersonMeeting.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Base/PersonMeetingQuery.php");

unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Map/PersonLastMeetingTableMap.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/Map/PersonMeetingTableMap.php");

// now we exclude the
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/js/meeting/");

unlink(SystemURLs::getDocumentRoot()."/external/routes/verify.php");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/external/templates/verify/");

// 2023-05-01 now the systemsettings are in v2 arch
unlink(SystemURLs::getDocumentRoot()."/SystemSettings.php");

// 2023-05-07
unlink(SystemURLs::getDocumentRoot()."/PersonView.php");

// 2023-05-08
unlink(SystemURLs::getDocumentRoot()."/FamilyView.php");
unlink(SystemURLs::getDocumentRoot()."/SettingsIndividual.php");

// 2023-05-11
unlink(SystemURLs::getDocumentRoot()."/UserEditor.php");

// 2023-05-14
unlink(SystemURLs::getDocumentRoot()."/UserPasswordChange.php");

// 2023-05-15
unlink(SystemURLs::getDocumentRoot()."/UpdateAllLatLon.php");

// 2023-05-18
unlink(SystemURLs::getDocumentRoot()."/GeoPage.php"); 

// 2023-05-18
unlink(SystemURLs::getDocumentRoot()."/favicon.ico"); 

unlink(SystemURLs::getDocumentRoot()."/PaddleNumEditor.php"); 
unlink(SystemURLs::getDocumentRoot()."/GroupPropsFormRowOps.php"); 
unlink(SystemURLs::getDocumentRoot()."/DonationFundEditor.php"); 
unlink(SystemURLs::getDocumentRoot()."/IntegrityCheck.php"); 
unlink(SystemURLs::getDocumentRoot()."/Checkin.php");
unlink(SystemURLs::getDocumentRoot()."/GroupEditor.php");
unlink(SystemURLs::getDocumentRoot()."/DepositSlipEditor.php");
unlink(SystemURLs::getDocumentRoot()."/FindDepositSlip.php");

// 2023-05-28
unlink(SystemURLs::getDocumentRoot()."/DirectoryReports.php");
unlink(SystemURLs::getDocumentRoot()."/GroupReports.php");

// 2023-05-30
unlink(SystemURLs::getDocumentRoot()."/LettersAndLabels.php");
unlink(SystemURLs::getDocumentRoot()."/ReminderReport.php");
unlink(SystemURLs::getDocumentRoot()."/QueryList.php");
unlink(SystemURLs::getDocumentRoot()."/QueryView.php");
unlink(SystemURLs::getDocumentRoot()."/QuerySQL.php");

// 2023-06-03
unlink(SystemURLs::getDocumentRoot()."/EventNames.php");
unlink(SystemURLs::getDocumentRoot()."/EditEventTypes.php");

// 2023-06-06
unlink(SystemURLs::getDocumentRoot()."/ManageEnvelopes.php");
unlink(SystemURLs::getDocumentRoot()."/FinancialReports.php");

unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/Reports/PDF_CertificatesReport.php");

// 2023-06-08
unlink(SystemURLs::getDocumentRoot()."/FundRaiserEditor.php");
unlink(SystemURLs::getDocumentRoot()."/GroupPropsEditor.php");

// 2023-06-10
unlink(SystemURLs::getDocumentRoot()."/CartToBadge.php");
unlink(SystemURLs::getDocumentRoot()."/GroupPropsFormEditor.php");
unlink(SystemURLs::getDocumentRoot()."/ReportList.php");

// 2023-06-13
unlink(SystemURLs::getDocumentRoot()."/OptionManager.php");
unlink(SystemURLs::getDocumentRoot()."/PrintView.php");
unlink(SystemURLs::getDocumentRoot()."/PrintPastoralCarePerson.php");

// 2023-06-17
unlink(SystemURLs::getDocumentRoot()."/CartToFamily.php");
unlink(SystemURLs::getDocumentRoot()."/TaxReport.php");

// 2023-06-18
unlink(SystemURLs::getDocumentRoot()."/FamilyEditor.php");
unlink(SystemURLs::getDocumentRoot()."/PersonEditor.php");

// 2023-06-19
unlink(SystemURLs::getDocumentRoot()."/AutoPaymentEditor.php");
unlink(SystemURLs::getDocumentRoot()."/ElectronicPaymentList.php");

// 2023-06-20
unlink(SystemURLs::getDocumentRoot()."/PersonCustomFieldsEditor.php");
unlink(SystemURLs::getDocumentRoot()."/FamilyCustomFieldsEditor.php");

// 2023-06-21
unlink(SystemURLs::getDocumentRoot()."/CanvassEditor.php");
unlink(SystemURLs::getDocumentRoot()."/CanvassAutomation.php");
unlink(SystemURLs::getDocumentRoot()."/ConvertIndividualToAddress.php");
unlink(SystemURLs::getDocumentRoot()."/BatchWinnerEntry.php");

// 2023-06-24
unlink(SystemURLs::getDocumentRoot()."/CSVExport.php");
unlink(SystemURLs::getDocumentRoot()."/CSVCreateFile.php");

// 2023-06-25
unlink(SystemURLs::getDocumentRoot()."/AutoPaymentClearAccounts.php");

// 2023-06-26
unlink(SystemURLs::getDocumentRoot()."/Images/+.png");
unlink(SystemURLs::getDocumentRoot()."/Images/downarrow.gif");
unlink(SystemURLs::getDocumentRoot()."/Images/uparrow.gif");
unlink(SystemURLs::getDocumentRoot()."/Images/x.gif");

unlink(SystemURLs::getDocumentRoot()."/EventAttendance.php");

// 2023-06-27
unlink(SystemURLs::getDocumentRoot()."/FamilyVerify.php");

// 2023-06-28
unlink(SystemURLs::getDocumentRoot()."/USISTAddressVerification.php");

// 2023-07-03
unlink(SystemURLs::getDocumentRoot()."/PledgeEditor.php");

// 2023-07-05
unlink(SystemURLs::getDocumentRoot()."/SelectDelete.php");

// 2023-09-23
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot()."/skin/js/jquery-photo-uploader/");

// 2023-10-07
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/MenuConfig.php");
unlink(SystemURLs::getDocumentRoot()."/EcclesiaCRM/model/EcclesiaCRM/MenuConfigQuery.php");

// 2024-02-13
unlink(SystemURLs::getDocumentRoot()."/Reports/USISTAddressReport.php");

$logger->info("End of delete :  all unusefull files");
?>
