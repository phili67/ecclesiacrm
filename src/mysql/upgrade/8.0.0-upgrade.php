<?php
// pour le debug on se met au bon endroit : https://192.168.151.205/mysql/upgrade/8.0.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\Propel;
use EcclesiaCRM\MyPDO\CardDavPDO;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\UserQuery;
use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\CardDav\VcardUtils;


$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot() . "/Include/GetGroupArray.php");
unlink(SystemURLs::getDocumentRoot() . "/RPCdummy.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/Reports/ChurchInfoReport.php");

unlink(SystemURLs::getDocumentRoot() . "/ListEvents.php");
unlink(SystemURLs::getDocumentRoot() . "/GetText.php");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/event/ListEvent.js");

unlink(SystemURLs::getDocumentRoot() . "/EditEventAttendees.php");

MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/external/font-awesome/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/external/jquery-photo-uploader/");

unlink(SystemURLs::getDocumentRoot() . "/Images/Bank.png");
unlink(SystemURLs::getDocumentRoot() . "/Images/Group.png");
unlink(SystemURLs::getDocumentRoot() . "/Images/Money.png");

// 2022-02-07 now jitsi meeting is now a plugin !
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/PersonLastMeeting.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/PersonLastMeetingQuery.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/PersonMeeting.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/PersonMeetingQuery.php");

unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/Base/PersonLastMeeting.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/Base/PersonLastMeetingQuery.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/Base/PersonMeeting.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/Base/PersonMeetingQuery.php");

unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/Map/PersonLastMeetingTableMap.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/Map/PersonMeetingTableMap.php");

// now we exclude the
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/js/meeting/");

unlink(SystemURLs::getDocumentRoot() . "/external/routes/verify.php");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/external/templates/verify/");

// 2023-05-01 now the systemsettings are in v2 arch
unlink(SystemURLs::getDocumentRoot() . "/SystemSettings.php");

// 2023-05-07
unlink(SystemURLs::getDocumentRoot() . "/PersonView.php");

// 2023-05-08
unlink(SystemURLs::getDocumentRoot() . "/FamilyView.php");
unlink(SystemURLs::getDocumentRoot() . "/SettingsIndividual.php");

// 2023-05-11
unlink(SystemURLs::getDocumentRoot() . "/UserEditor.php");

// 2023-05-14
unlink(SystemURLs::getDocumentRoot() . "/UserPasswordChange.php");

// 2023-05-15
unlink(SystemURLs::getDocumentRoot() . "/UpdateAllLatLon.php");

// 2023-05-18
unlink(SystemURLs::getDocumentRoot() . "/GeoPage.php");

// 2023-05-18
unlink(SystemURLs::getDocumentRoot() . "/favicon.ico");

unlink(SystemURLs::getDocumentRoot() . "/PaddleNumEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/GroupPropsFormRowOps.php");
unlink(SystemURLs::getDocumentRoot() . "/DonationFundEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/IntegrityCheck.php");
unlink(SystemURLs::getDocumentRoot() . "/Checkin.php");
unlink(SystemURLs::getDocumentRoot() . "/GroupEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/DepositSlipEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/FindDepositSlip.php");

// 2023-05-28
unlink(SystemURLs::getDocumentRoot() . "/DirectoryReports.php");
unlink(SystemURLs::getDocumentRoot() . "/GroupReports.php");

// 2023-05-30
unlink(SystemURLs::getDocumentRoot() . "/LettersAndLabels.php");
unlink(SystemURLs::getDocumentRoot() . "/ReminderReport.php");
unlink(SystemURLs::getDocumentRoot() . "/QueryList.php");
unlink(SystemURLs::getDocumentRoot() . "/QueryView.php");
unlink(SystemURLs::getDocumentRoot() . "/QuerySQL.php");

// 2023-06-03
unlink(SystemURLs::getDocumentRoot() . "/EventNames.php");
unlink(SystemURLs::getDocumentRoot() . "/EditEventTypes.php");

// 2023-06-06
unlink(SystemURLs::getDocumentRoot() . "/ManageEnvelopes.php");
unlink(SystemURLs::getDocumentRoot() . "/FinancialReports.php");

unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/Reports/PDF_CertificatesReport.php");

// 2023-06-08
unlink(SystemURLs::getDocumentRoot() . "/FundRaiserEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/GroupPropsEditor.php");

// 2023-06-10
unlink(SystemURLs::getDocumentRoot() . "/CartToBadge.php");
unlink(SystemURLs::getDocumentRoot() . "/GroupPropsFormEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/ReportList.php");

// 2023-06-13
unlink(SystemURLs::getDocumentRoot() . "/OptionManager.php");
unlink(SystemURLs::getDocumentRoot() . "/PrintView.php");
unlink(SystemURLs::getDocumentRoot() . "/PrintPastoralCarePerson.php");

// 2023-06-17
unlink(SystemURLs::getDocumentRoot() . "/CartToFamily.php");
unlink(SystemURLs::getDocumentRoot() . "/TaxReport.php");

// 2023-06-18
unlink(SystemURLs::getDocumentRoot() . "/FamilyEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/PersonEditor.php");

// 2023-06-19
unlink(SystemURLs::getDocumentRoot() . "/AutoPaymentEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/ElectronicPaymentList.php");

// 2023-06-20
unlink(SystemURLs::getDocumentRoot() . "/PersonCustomFieldsEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/FamilyCustomFieldsEditor.php");

// 2023-06-21
unlink(SystemURLs::getDocumentRoot() . "/CanvassEditor.php");
unlink(SystemURLs::getDocumentRoot() . "/CanvassAutomation.php");
unlink(SystemURLs::getDocumentRoot() . "/ConvertIndividualToAddress.php");
unlink(SystemURLs::getDocumentRoot() . "/BatchWinnerEntry.php");

// 2023-06-24
unlink(SystemURLs::getDocumentRoot() . "/CSVExport.php");
unlink(SystemURLs::getDocumentRoot() . "/CSVCreateFile.php");

// 2023-06-25
unlink(SystemURLs::getDocumentRoot() . "/AutoPaymentClearAccounts.php");

// 2023-06-26
unlink(SystemURLs::getDocumentRoot() . "/Images/+.png");
unlink(SystemURLs::getDocumentRoot() . "/Images/downarrow.gif");
unlink(SystemURLs::getDocumentRoot() . "/Images/uparrow.gif");
unlink(SystemURLs::getDocumentRoot() . "/Images/x.gif");

unlink(SystemURLs::getDocumentRoot() . "/EventAttendance.php");

// 2023-06-27
unlink(SystemURLs::getDocumentRoot() . "/FamilyVerify.php");

// 2023-06-28
unlink(SystemURLs::getDocumentRoot() . "/USISTAddressVerification.php");

// 2023-07-03
unlink(SystemURLs::getDocumentRoot() . "/PledgeEditor.php");

// 2023-07-05
unlink(SystemURLs::getDocumentRoot() . "/SelectDelete.php");

// 2023-09-23
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/js/jquery-photo-uploader/");

// 2023-10-07
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/MenuConfig.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/model/EcclesiaCRM/MenuConfigQuery.php");

// 2024-02-13
unlink(SystemURLs::getDocumentRoot() . "/Reports/USISTAddressReport.php");

// 2024-03-16
unlink(SystemURLs::getDocumentRoot() . "/skin/external/bootstrap-show-password");
unlink(SystemURLs::getDocumentRoot() . "/Login.php");
unlink(SystemURLs::getDocumentRoot() . "/Logoff.php");

// 2024-04-13
unlink(SystemURLs::getDocumentRoot() . "/skin/js/initial.js");

// 2025-04-17
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/sabre/CalDavPDO.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/sabre/CardDavPDO.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/sabre/VCalendarExtension.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/sabre/VObjectExtract.php");

// 2025-06-01
mkdir(SystemURLs::getDocumentRoot()."/Images/tmp", 0755);

// vonage nexmo is now unusefull
MiscUtils::delTree(SystemURLs::getDocumentRoot() . "/vendor/vonage");

// 2025-07-23
unlink(SystemURLs::getDocumentRoot() . "/skin/js/email/MailChimp/Debug.js");
unlink(SystemURLs::getDocumentRoot() . "/v2/templates/email/mailchimp/debug.php");

// 2025-07-28
unlink(SystemURLs::getDocumentRoot() . "/skin/js/system/SystemSettings.js");

// 2025-07-29
unlink(SystemURLs::getDocumentRoot() . "/Include/slim/settings.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/APIControllers/MeetingController.php");

// 2025-07-31
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/Include/slim/");
unlink(SystemURLs::getDocumentRoot() . "/Include/AuthorizeNetConfig.php");
unlink(SystemURLs::getDocumentRoot() . "/Include/alphaAPI.js");
unlink(SystemURLs::getDocumentRoot() . "/Include/domMenu-IE.js");
unlink(SystemURLs::getDocumentRoot() . "/Include/domMenu.js");
unlink(SystemURLs::getDocumentRoot() . "/Include/domTT.js");
unlink(SystemURLs::getDocumentRoot() . "/Include/winlocalelist.php");
unlink(SystemURLs::getDocumentRoot() . "/api/dependencies.php");
unlink(SystemURLs::getDocumentRoot() . "/external/dependencies.php");
unlink(SystemURLs::getDocumentRoot() . "/ident/dependencies.php");
unlink(SystemURLs::getDocumentRoot() . "/session/dependencies.php");

// we get the PDO for the Sabre connection from the Propel connection
// now we update the CardDav 
// every person in group should have a view on the addrebooks of the group
$pdo = Propel::getConnection();

$carddavBackend   = new CardDavPDO();

$logger->info("Start to delete : start group add to addressbook");

$groups = GroupQuery::create()->find();

$userAdmin = UserQuery::Create()->findOneByPersonId(1);        

foreach ($groups as $group) {
  // first we add the adress book
  $addressbookId = $carddavBackend->createAddressBook(
    'principals/'.strtolower($userAdmin->getUserName()),
    \Sabre\DAV\UUIDUtil::getUUID(),
    [
      '{DAV:}displayname'                                       => $group->getName(),
      '{urn:ietf:params:xml:ns:carddav}addressbook-description' => 'AddressBook description',
    ],
    $group->getId()
  );

  // we filter all the user who are admin or group manager and not the principal admin
  $users = UserQuery::Create()
    ->filterByManageGroups(true)
    ->_or()->filterByAdmin(true)
    ->filterByPersonId(1, CRITERIA::NOT_EQUAL)
    ->find();

  // now we can share the new calendar to the users
  foreach ($users as $user) {
    $carddavBackend->createAddressBookShare(
      'principals/' . $user->getUserName(),
      [
        'addressbookid' => $addressbookId, // require
        '{DAV:}displayname'  => $group->getName(),
        '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description'  => '',
        'href'         => 0,
        'user_id'      => $user->getId(), // require
        'access'       => 3 // '1 = owner, 2 = read, 3 = readwrite',                    
      ]
    );
  }

  // we add all the members to 
  $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
    ->joinWithPerson()
    ->usePersonQuery()
    ->filterByDateDeactivated(null) // RGPD, when a person is completely deactivated
    ->endUse()
    ->findByGroupId($group->getId());

  foreach ($members as $member) {
    $person = $member->getPerson();

    $vcard = VcardUtils::Person2Vcard($person);

    $card = $vcard->serialize();

    $carddavBackend->createCard($addressbookId, 'UUID-' . \Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());
  }
}


$logger->info("End of delete :  all unusefull files");
