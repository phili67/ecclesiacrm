<?php

namespace EcclesiaCRM\dto;

use EcclesiaCRM\Config;
use EcclesiaCRM\dto\ConfigItem;
use EcclesiaCRM\data\Countries;

class SystemConfig
{
    /**
     * @var Config[]
     */
    private static $configs;
    private static $categories;

    private static function getSupportedLocales()
    {
        $localesFile = file_get_contents(SystemURLs::getDocumentRoot() . "/locale/locales.json");
        $locales = json_decode($localesFile, true);
        $languagesChoices = [];
        foreach ($locales as $key => $value) {
            array_push($languagesChoices, _($key) . ":" . $value["locale"]);
        }

        return ["Choices" => $languagesChoices];
    }

    public static function getMonoLogLevels()
    {
        return [
            "Choices" => [
                _("NONE") . ":0",
                _("DEBUG") . ":100",
                _("INFO") . ":200",
                _("NOTICE") . ":250",
                _("WARNING") . ":300",
                _("ERROR") . ":400",
                _("CRITICAL") . ":500",
                _("ALERT") . ":550",
                _("EMERGENCY") . ":600"
            ]
        ];
    }

    public static function getNameChoices()
    {
        return [
            "Choices" => [
                _("Title FirstName MiddleName LastName") . ":0",
                _("Title FirstName MiddleInitial. LastName") . ":1",
                _("LastName, Title FirstName MiddleName") . ":2",
                _("LastName, Title FirstName MiddleInitial") . ":3",
                _("FirstName MiddleName LastName") . ":4",
                _("Title FirstName LastName") . ":5",
                _("LastName, Title FirstName") . ":6"
            ]
        ];
    }

    public static function getAddressChoices()
    {
        return [
            "Choices" => [
                _("Address city state zip") . ":0",
                _("Address zip city state") . ":1"
            ]
        ];
    }

    private static function buildConfigs()
    {
        return array(
            "sLogLevel" => new ConfigItem(4, "sLogLevel", "choice", "100", _("Event Log severity to write, used by ORM and App Logs. sLogLevel >= 300 : ORM is set in debug mode."), "", json_encode(SystemConfig::getMonoLogLevels())),
            "sDirClassifications" => new ConfigItem(5, "sDirClassifications", "text", "1,2,4,5", _("Include only these classifications in the directory, comma seperated")),
            "sDirRoleHead" => new ConfigItem(6, "sDirRoleHead", "text", "1", _("These are the family role numbers designated as head of house")),
            "sDirRoleSpouse" => new ConfigItem(7, "sDirRoleSpouse", "text", "2", _("These are the family role numbers designated as spouse")),
            "sDirRoleChild" => new ConfigItem(8, "sDirRoleChild", "text", "3", _("These are the family role numbers designated as child")),
            "iSessionTimeout" => new ConfigItem(9, "iSessionTimeout", "number", "3600", _("Session timeout length in seconds. Set to zero to disable session timeouts.")),
            "aFinanceQueries" => new ConfigItem(10, "aFinanceQueries", "text", "30,31,32", _("Queries for which user must have finance permissions to use:")),
            "bCSVAdminOnly" => new ConfigItem(11, "bCSVAdminOnly", "boolean", "1", _("Should only administrators have access to the CSV export system and directory report?")),
            "iMinPasswordLength" => new ConfigItem(13, "iMinPasswordLength", "number", "6", _("Minimum length a user may set their password to")),
            "iMinPasswordChange" => new ConfigItem(14, "iMinPasswordChange", "number", "4", _("Minimum amount that a new password must differ from the old one (# of characters changed). Set to zero to disable this feature")),
            "aDisallowedPasswords" => new ConfigItem(15, "aDisallowedPasswords", "text", "password,god,jesus,church,christian", _("A comma-seperated list of disallowed (too obvious) passwords.")),
            "iMaxFailedLogins" => new ConfigItem(16, "iMaxFailedLogins", "number", "5", _("Maximum number of failed logins to allow before a user account is locked.. Once the maximum has been reached, an administrator must re-enable the account.. This feature helps to protect against automated password guessing attacks.. Set to zero to disable this feature.")),
            "iPDFOutputType" => new ConfigItem(20, "iPDFOutputType", "choice", "1", _("PDF handling mode.. 1 = Save File dialog. 2 = Open in current browser window"), "", '{"Choices":["1","2"]}'),
            "sDefaultCity" => new ConfigItem(21, "sDefaultCity", "text", "", _("Default City")),
            "sDefaultState" => new ConfigItem(22, "sDefaultState", "text", "", _("Default State - Must be 2-letter abbreviation!")),
            "sDefaultCountry" => new ConfigItem(23, "sDefaultCountry", "choice", "", "", "", json_encode(["Choices" => Countries::getNames()])),
            "sToEmailAddress" => new ConfigItem(26, "sToEmailAddress", "text", "", _("Default account for receiving a copy of all emails")),
            "iSMTPTimeout" => new ConfigItem(24, "iSMTPTimeout", "number", "10", _("SMTP Server timeout in sec")),
            "sSMTPHost" => new ConfigItem(27, "sSMTPHost", "text", "", _("SMTP Server Address (mail.server.com:25)")),
            "bSMTPAuth" => new ConfigItem(28, "bSMTPAuth", "boolean", "0", _("Does your SMTP server require auththentication (username/password)?")),
            "sSMTPUser" => new ConfigItem(29, "sSMTPUser", "text", "", _("SMTP Username")),
            "sSMTPPass" => new ConfigItem(30, "sSMTPPass", "password", "", _("SMTP Password")),
            "bShowFamilyData" => new ConfigItem(33, "bShowFamilyData", "boolean", "1", _("Unavailable person info inherited from assigned family for display?. This option causes certain info from a person's assigned family record to be. displayed IF the corresponding info has NOT been entered for that person. ")),
            "bGZIP" => new ConfigItem(36, "bGZIP", "boolean", "gzip", _("gzip format export allowed")),
            "bZIP" => new ConfigItem(37, "bZIP", "boolean", "zip", _("zip format export allowed")),
            "sPGP" => new ConfigItem(38, "sPGP", "choice", "GPG", _("By default GPG (GnuPG) a hybrid-encryption software program or Internal encryption software. You could avoid to use with the None choice."), "", '{"Choices":["None", "GPG","Internal"]}'),
            "sLanguage" => new ConfigItem(39, "sLanguage", "choice", "en_US", _("Internationalization (I18n) support"), "", json_encode(SystemConfig::getSupportedLocales())),
            "iFYMonth" => new ConfigItem(40, "iFYMonth", "choice", "1", _("First month of the fiscal year"), "", '{"Choices":["1","2","3","4","5","6","7","8","9","10","11","12"]}'),
            "sNominatimLink" => new ConfigItem(41, "sNominatimLink", "text", "https://nominatim.openstreetmap.org", _("Link of the nominatim server : https://nominatim.openstreetmap.org"), "https://OpenStreetMap.openstreetmap.org"),
            "sGoogleMapKey" => new ConfigItem(44, "sGoogleMapKey", "text", "", _("Google map API requires a unique key"), "https://developers.google.com/maps/documentation/javascript/get-api-key"),
            "sBingMapKey" => new ConfigItem(10000, "sBingMapKey", "text", "", _("Bing map API requires a unique key"), "https://www.microsoft.com/maps/create-a-bing-maps-key.aspx"),
            "iMapZoom" => new ConfigItem(10001, "iMapZoom", "number", "12", _("Google/OpenStreetMap/BingMaps Maps Zoom")),
            "iLittleMapZoom" => new ConfigItem(10002, "iLittleMapZoom", "number", "15", _("Google/OpenStreetMap/BingMaps Litle Maps Zoom")),
            "iChurchLatitude" => new ConfigItem(45, "iChurchLatitude", "number", "", _("Latitude of the church, used to center the Google map")),
            "iChurchLongitude" => new ConfigItem(46, "iChurchLongitude", "number", "", _("Longitude of the church, used to center the Google map")),
            "bHidePersonAddress" => new ConfigItem(47, "bHidePersonAddress", "boolean", "1", _("Set true to disable entering addresses in Person Editor.  Set false to enable entering addresses in Person Editor.")),
            "bHideFriendDate" => new ConfigItem(48, "bHideFriendDate", "boolean", "0", _("Set true to disable entering Friend Date in Person Editor.  Set false to enable entering Friend Date in Person Editor.")),
            "bHideFamilyNewsletter" => new ConfigItem(49, "bHideFamilyNewsletter", "boolean", "0", _("Set true to disable management of newsletter subscriptions in the Family Editor.")),
            "bHideWeddingDate" => new ConfigItem(50, "bHideWeddingDate", "boolean", "0", _("Set true to disable entering Wedding Date in Family Editor.  Set false to enable entering Wedding Date in Family Editor.")),
            "bHideLatLon" => new ConfigItem(51, "bHideLatLon", "boolean", "0", _("Set true to disable entering Latitude and Longitude in Family Editor.  Set false to enable entering Latitude and Longitude in Family Editor.  Lookups are still performed, just not displayed.")),
            "bUseDonationEnvelopes" => new ConfigItem(52, "bUseDonationEnvelopes", "boolean", "0", _("Set true to enable use of donation envelopes")),
            "sHeader" => new ConfigItem(53, "sHeader", "textarea", "", _("Enter in HTML code which will be displayed as a header at the top of each page. Be sure to close your tags! Note: You must REFRESH YOUR BROWSER A SECOND TIME to view the new header.")),
            "sISTusername" => new ConfigItem(54, "sISTusername", "text", "username", _("Intelligent Search Technolgy, Ltd. CorrectAddress Username"), "https://www.intelligentsearch.com/Hosted/User"),
            "sISTpassword" => new ConfigItem(55, "sISTpassword", "password", "", _("Intelligent Search Technolgy, Ltd. CorrectAddress Password"), "https://www.intelligentsearch.com/Hosted/User"),
            "sMapProvider" => new ConfigItem(56, "sMapProvider", "choice", "OpenStreetMap", _("Select GeoCoder Provider"), "https://github.com/geocoder-php/Geocoder/blob/3.x/README.md#address-based-providers", '{"Choices":["OpenStreetMap", "GoogleMaps", "BingMaps"]}'),
            "iChecksPerDepositForm" => new ConfigItem(57, "iChecksPerDepositForm", "number", "14", _("Number of checks for Deposit Slip Report")),
            "bUseScannedChecks" => new ConfigItem(58, "bUseScannedChecks", "boolean", "0", _("Set true to enable use of scanned checks")),
            "sDistanceUnit" => new ConfigItem(64, "sDistanceUnit", "choice", "miles", _("Unit used to measure distance, miles or km."), "", '{"Choices":["' . _("miles") . '","' . _("kilometers") . '"]}'),
            "sTimeZone" => new ConfigItem(65, "sTimeZone", "choice", "America/New_York", _("Time zone") . " : " . _("CalDAV protocol to work with php date_default_timezone_set function default settings : america/new_york"), "http://php.net/manual/en/timezones.php", json_encode(["Choices" => timezone_identifiers_list()])),
            "bForceUppercaseZip" => new ConfigItem(67, "bForceUppercaseZip", "boolean", "0", _("Make user-entered zip/postcodes UPPERCASE when saving to the database.")),
            "bEnableNonDeductible" => new ConfigItem(72, "bEnableNonDeductible", "boolean", "0", _("Enable non-deductible payments")),
            "sElectronicTransactionProcessor" => new ConfigItem(73, "sElectronicTransactionProcessor", "choice", "Vanco", _("Electronic Transaction Processor"), '', '{"Choices":["' . _("Vanco") . '","' . _("Authorize.NET") . '"]}'),
            "bEnableSelfRegistration" => new ConfigItem(80, "bEnableSelfRegistration", "boolean", "0", _("Set true to enable family self registration.")),
            "sPhoneFormat" => new ConfigItem(100, "sPhoneFormat", "text", "(999) 999-9999"),
            "sPhoneFormatWithExt" => new ConfigItem(101, "sPhoneFormatWithExt", "text", "(999) 999-9999 x99999"),
            "sPhoneFormatCell" => new ConfigItem(111, "sPhoneFormatCell", "text", "(999) 999-9999"),
            "sDateFormatLong" => new ConfigItem(102, "sDateFormatLong", "choice", "Y-m-d", _("For defining the date, per default : m/d/Y, In French : d/m/Y for example."), '', '{"Choices":["Y-m-d","d/m/Y","m-d-Y","m/d/Y"]}'),
            "sTimeFormat" => new ConfigItem(112, "sTimeFormat", "text", "%l:%M %p", _("This part is important for the time Line date and time")),
            "bTimeEnglish" => new ConfigItem(1051, "bTimeEnglish", "boolean", "1", _("Set the time in English format or 24 hours.")),
            "sDateFormatNoYear" => new ConfigItem(103, "sDateFormatNoYear", "choice", "m/d", _("For defining the date, per default : m/d, In French : d/m for example."), '', '{"Choices":["m/d","d/m","m-d","d-m"]}'),
            "sDateFormatShort" => new ConfigItem(104, "sDateFormatShort", "text", "j/m/y"),
            "sDateTimeFormat" => new ConfigItem(105, "sDateTimeFormat", "text", "j/m/y g:i a", _("php format for the date, in English : j/m/y g:i a")),
            "sDateFilenameFormat" => new ConfigItem(106, "sDateFilenameFormat", "text", "Ymd-Gis"),
            "sDatePickerPlaceHolder" => new ConfigItem(109, "sDatePickerPlaceHolder", "choice", "yyyy-mm-dd", _("For defining the date in Date-Picker, per default : yyyy-mm-dd, In French : dd/mm/yyyy for example."), '', '{"Choices":["yyyy-mm-dd","dd/mm/yyyy","mm-dd-yyyy","mm/dd/yyyy"]}'),
            "sDatePickerFormat" => new ConfigItem(110, "sDatePickerFormat", "choice", "Y-m-d", _("For defining the date in Date-Picker, per default : Y-m-d, In French : d/m/Y for example."), '', '{"Choices":["Y-m-d","d/m/Y","m-d-Y","m/d/Y"]}'),
            "bRegistered" => new ConfigItem(999, "bRegistered", "boolean", "0", _("EcclesiaCRM has been registered.  The EcclesiaCRM team uses registration information to track usage.  This information is kept confidential and never released or sold.  If this field is true the registration option in the admin menu changes to update registration.")),
            "leftX" => new ConfigItem(1001, "leftX", "number", "20", _("Left Margin (1 = 1/100th inch)")),
            "incrementY" => new ConfigItem(1002, "incrementY", "number", "4", _("Line Thickness (1 = 1/100th inch")),
            "sChurchName" => new ConfigItem(1003, "sChurchName", "text", "", _("Church Name")),
            "sChurchAddress" => new ConfigItem(1004, "sChurchAddress", "text", "", _("Church Address")),
            "sChurchCity" => new ConfigItem(1005, "sChurchCity", "text", "", _("Church City")),
            "sChurchState" => new ConfigItem(1006, "sChurchState", "text", "", _("Church State")),
            "sChurchZip" => new ConfigItem(1007, "sChurchZip", "text", "", _("Church Zip")),
            "sChurchPhone" => new ConfigItem(1008, "sChurchPhone", "text", "", _("Church Phone")),
            "sChurchEmail" => new ConfigItem(1009, "sChurchEmail", "text", "", _("Church Email")),
            "sHomeAreaCode" => new ConfigItem(1010, "sHomeAreaCode", "text", "", _("Home area code of the church")),
            "sTaxReport1" => new ConfigItem(1011, "sTaxReport1", "text", "This letter shows our record of your payments for", _("Verbage for top line of tax report. Dates will be appended to the end of this line.")),
            "sTaxReport2" => new ConfigItem(1012, "sTaxReport2", "text", "Thank you for your help in making a difference. We greatly appreciate your gift!", _("Verbage for bottom line of tax report.")),
            "sTaxReport3" => new ConfigItem(1013, "sTaxReport3", "text", "If you have any questions or corrections to make to this report, please contact the church at the above number during business hours, 9am to 4pm, M-F.", _("Verbage for bottom line of tax report.")),
            "sTaxSigner" => new ConfigItem(1014, "sTaxSigner", "text", "", _("Tax Report signer")),
            "sReminder1" => new ConfigItem(1015, "sReminder1", "text", "This letter shows our record of your pledge and payments for fiscal year", _("Verbage for the pledge reminder report")),
            "sReminderSigner" => new ConfigItem(1016, "sReminderSigner", "text", "", _("Pledge Reminder Signer")),
            "sReminderNoPledge" => new ConfigItem(1017, "sReminderNoPledge", "text", "Pledges: We do not have record of a pledge for from you for this fiscal year.", _("Verbage for the pledge reminder report - No record of a pledge")),
            "sReminderNoPayments" => new ConfigItem(1018, "sReminderNoPayments", "text", "Payments: We do not have record of a pledge for from you for this fiscal year.", _("Verbage for the pledge reminder report - No record of payments")),
            "sConfirm1" => new ConfigItem(1019, "sConfirm1", "text", "This letter shows the information we have in our database with respect to your family.  Please review, mark-up as necessary, and return this form to the church office.", _("Verbage for the database information confirmation and correction report")),
            "sConfirm2" => new ConfigItem(1020, "sConfirm2", "text", "Thank you very much for helping us to update this information.  If you want on-line access to the church database please provide your email address and a desired password and we will send instructions.", _("Verbage for the database information confirmation and correction report")),
            "sConfirm3" => new ConfigItem(1021, "sConfirm3", "text", "Email _____________________________________ Password ________________", _("Verbage for the database information confirmation and correction report")),
            "sConfirm4" => new ConfigItem(1022, "sConfirm4", "text", "[  ] I no longer want to be associated with the church (check here to be removed from our records).", _("Verbage for the database information confirmation and correction report")),
            "sConfirm5" => new ConfigItem(1023, "sConfirm5", "text", "", _("Verbage for the database information confirmation and correction report")),
            "sConfirm6" => new ConfigItem(1024, "sConfirm6", "text", "", _("Verbage for the database information confirmation and correction report")),
            "sConfirmSigner" => new ConfigItem(1025, "sConfirmSigner", "text", "", _("Database information confirmation and correction report signer")),
            "sPledgeSummary1" => new ConfigItem(1026, "sPledgeSummary1", "text", "Summary of pledges and payments for the fiscal year", _("Verbage for the pledge summary report")),
            "sPledgeSummary2" => new ConfigItem(1027, "sPledgeSummary2", "text", " as of", _("Verbage for the pledge summary report")),
            "sDirectoryDisclaimer1" => new ConfigItem(1028, "sDirectoryDisclaimer1", "text", "Every effort was made to insure the accuracy of this directory.  If there are any errors or omissions, please contact the church office.\n\nThis directory is for the use of the people of", _("Verbage for the directory report")),
            "sDirectoryDisclaimer2" => new ConfigItem(1029, "sDirectoryDisclaimer2", "text", ", and the information contained in it may not be used for business or commercial purposes.", _("Verbage for the directory report")),
            "bDirLetterHead" => new ConfigItem(1030, "bDirLetterHead", "text", "../Images/church_letterhead.jpg", _("Church Letterhead path and file")),
            "sZeroGivers" => new ConfigItem(1031, "sZeroGivers", "text", "This letter shows our record of your payments for", _("Verbage for top line of tax report. Dates will be appended to the end of this line.")),
            "sZeroGivers2" => new ConfigItem(1032, "sZeroGivers2", "text", "Thank you for your help in making a difference. We greatly appreciate your gift!", _("Verbage for bottom line of tax report.")),
            "sZeroGivers3" => new ConfigItem(1033, "sZeroGivers3", "text", "If you have any questions or corrections to make to this report, please contact the church at the above number during business hours, 9am to 4pm, M-F.", _("Verbage for bottom line of tax report.")),
            "sChurchChkAcctNum" => new ConfigItem(1034, "sChurchChkAcctNum", "text", "", _("Church Checking Account Number")),
            "bEnableGravatarPhotos" => new ConfigItem(1035, "bEnableGravatarPhotos", "boolean", "0", _("lookup user images on Gravatar when no local image is present")),
            "bEnableExternalBackupTarget" => new ConfigItem(1036, "bEnableExternalBackupTarget", "boolean", "0", _("Enable Remote Backups to Cloud Services")),
            "sExternalBackupType" => new ConfigItem(1037, "sExternalBackupType", "choice", "", _("Cloud Service Type (Supported values: WebDAV, Local)"), "", '{"Choices":["' . _("WebDAV") . '","' . _("Local") . '"]}'),
            "sExternalBackupEndpoint" => new ConfigItem(1038, "sExternalBackupEndpoint", "text", "", _("Remote Backup Endpoint")),
            "sExternalBackupUsername" => new ConfigItem(1039, "sExternalBackupUsername", "text", "", _("Remote Backup Username")),
            "sExternalBackupPassword" => new ConfigItem(1040, "sExternalBackupPassword", "password", "", _("Remote Backup Password")),
            "sExternalBackupAutoInterval" => new ConfigItem(1041, "sExternalBackupAutoInterval", "text", "", _("Interval in Hours for Automatic Remote Backups")),
            "sLastBackupTimeStamp" => new ConfigItem(1042, "sLastBackupTimeStamp", "text", "", _("Last Backup Timestamp")),
            "sQBDTSettings" => new ConfigItem(1043, "sQBDTSettings", "json", '{"date1":{"x":"12","y":"42"},"date2X":"185","leftX":"64","topY":"7","perforationY":"97","amountOffsetX":"35","lineItemInterval":{"x":"49","y":"7"},"max":{"x":"200","y":"140"},"numberOfItems":{"x":"136","y":"68"},"subTotal":{"x":"197","y":"42"},"topTotal":{"x":"197","y":"68"},"titleX":"85"}', _("QuickBooks Deposit Ticket Settings")),
            "bEnableIntegrityCheck" => new ConfigItem(1044, "bEnableIntegrityCheck", "boolean", "1", _("Enable Integrity Check")),
            "iIntegrityCheckInterval" => new ConfigItem(1045, "iIntegrityCheckInterval", "number", "168", _("Interval in Hours for Integrity Check")),
            "sLastIntegrityCheckTimeStamp" => new ConfigItem(1046, "sLastIntegrityCheckTimeStamp", "text", "", _("Last Integrity Check Timestamp")),
            "sChurchCountry" => new ConfigItem(1047, "sChurchCountry", "choice", "", "", "", json_encode(["Choices" => Countries::getNames()])),
            "sConfirmSincerely" => new ConfigItem(1048, "sConfirmSincerely", "text", "Sincerely", _("Used to end a letter before Signer")),
            "sDear" => new ConfigItem(1049, "sDear", "text", "Dear", _("Text before name in emails/reports")),
            "sGoogleTrackingID" => new ConfigItem(1050, "sGoogleTrackingID", "text", "", _("Google Analytics Tracking Code")),
            "sMailChimpApiKey" => new ConfigItem(2000, "sMailChimpApiKey", "text", "", "", "http://kb.mailchimp.com/accounts/management/about-api-keys"),
            "sDepositSlipType" => new ConfigItem(2001, "sDepositSlipType", "choice", "QBDT", _("Deposit ticket type.  QBDT - Quickbooks"), "", '{"Choices":["QBDT"]}'),
            "bAllowEmptyLastName" => new ConfigItem(2010, "bAllowEmptyLastName", "boolean", "0", _("Set true to allow empty lastname in Person Editor.  Set false to validate last name and inherit from family when left empty.")),
            "iPersonNameStyle" => new ConfigItem(2020, "iPersonNameStyle", "choice", "4", "", "", json_encode(SystemConfig::getNameChoices())),
            "bDisplayBillCounts" => new ConfigItem(2002, "bDisplayBillCounts", "boolean", "1", _("Display bill counts on deposit slip")),
            "sCloudURL" => new ConfigItem(2003, "sCloudURL", "text", "http://demo.ecclesiacrm.com/", _("EcclesiaCRM Cloud Access URL")),
            "sNexmoAPIKey" => new ConfigItem(2012, "sNexmoAPIKey", "text", "", _("Nexmo SMS API Key")),
            "sNexmoAPISecret" => new ConfigItem(2005, "sNexmoAPISecret", "password", "", _("Nexmo SMS API Secret")),
            "sNexmoFromNumber" => new ConfigItem(2006, "sNexmoFromNumber", "text", "", _("Nexmo SMS From Number")),
            "sOLPURL" => new ConfigItem(2007, "sOLPURL", "text", "http://192.168.1.1:4316", _("OpenLP URL")),
            "sOLPUserName" => new ConfigItem(2008, "sOLPUserName", "text", "", _("OpenLP Username")),
            "sOLPPassword" => new ConfigItem(2009, "sOLPPassword", "password", "", _("OpenLP Password")),
            "sKioskVisibilityTimestamp" => new ConfigItem(2011, "sKioskVisibilityTimestamp", "text", "", _("KioskVisibilityTimestamp")),
            "bEnableLostPassword" => new ConfigItem(2004, "bEnableLostPassword", "boolean", "1", _("Show/Hide Lost Password Link on the login screen")),
            "sChurchWebSite" => new ConfigItem(2013, "sChurchWebSite", "text", "", _("Your Church's Website")),
            "sChurchFB" => new ConfigItem(2014, "sChurchFB", "text", "", _("Your Church's Facebook Page")),
            "sChurchTwitter" => new ConfigItem(2015, "sChurchTwitter", "text", "", _("Your Church's Twitter Page")),
            "bEnableGooglePhotos" => new ConfigItem(2016, "bEnableGooglePhotos", "boolean", "1", _("lookup user images on Google when no local image is present")),
            "sNewPersonNotificationRecipientIDs" => new ConfigItem(2018, "sNewPersonNotificationRecipientIDs", "text", "", _("Comma Separated list of PersonIDs of people to notify when a new family or person is added")),
            "bEnableExternalCalendarAPI" => new ConfigItem(2017, "bEnableExternalCalendarAPI", "boolean", "0", _("Allow unauthenticated reads of events from the external calendar API")),
            "bSearchIncludePersons" => new ConfigItem(2019, "bSearchIncludePersons", "boolean", "1", _("Search People")),
            "bSearchIncludeFamilies" => new ConfigItem(2021, "bSearchIncludeFamilies", "boolean", "1", _("Search Family")),
            "bSearchIncludeFamilyHOH" => new ConfigItem(2022, "bSearchIncludeFamilyHOH", "boolean", "1", _("Show Family Head of House Names")),
            "bSearchIncludeGroups" => new ConfigItem(2023, "bSearchIncludeGroups", "boolean", "1", _("Search Groups")),
            "bSearchIncludeDeposits" => new ConfigItem(2024, "bSearchIncludeDeposits", "boolean", "1", _("Search Deposits")),
            "bSearchIncludePayments" => new ConfigItem(2025, "bSearchIncludePayments", "boolean", "1", _("Search Payments")),
            "bSearchIncludeAddresses" => new ConfigItem(2026, "bSearchIncludeAddresses", "boolean", "1", _("Search Addresses")),
            "iSearchIncludePersonsMax" => new ConfigItem(2027, "iSearchIncludePersonsMax", "number", "15", _("Maximum number of People")),
            "iSearchIncludeFamiliesMax" => new ConfigItem(2028, "iSearchIncludeFamiliesMax", "number", "15", _("Maximum number of Families")),
            "iSearchIncludeFamilyHOHMax" => new ConfigItem(2029, "iSearchIncludeFamilyHOHMax", "number", "15", _("Maximum number of Family H.O.H Names")),
            "iSearchIncludeGroupsMax" => new ConfigItem(2030, "iSearchIncludeGroupsMax", "number", "15", _("Maximum number of Groups")),
            "iSearchIncludeDepositsMax" => new ConfigItem(2031, "iSearchIncludeDepositsMax", "number", "5", _("Maximum number of Deposits")),
            "iSearchIncludePaymentsMax" => new ConfigItem(2032, "iSearchIncludePaymentsMax", "number", "5", _("Maximum number of Payments")),
            "iSearchIncludeAddressesMax" => new ConfigItem(2033, "iSearchIncludeAddressesMax", "number", "15", _("Maximum number of Addresses")),
            "bSearchIncludePastoralCare" => new ConfigItem(2060, "bSearchIncludePastoralCare", "boolean", "1", _("Search Pastoral Care")),
            "iSearchIncludePastoralCareMax" => new ConfigItem(2061, "iSearchIncludePastoralCareMax", "number", "5", _("Maximum number of Pastoral Care results")),
            "iPhotoHeight" => new ConfigItem(2034, "iPhotoHeight", "number", "400", _("Height to use for images")),
            "iPhotoWidth" => new ConfigItem(2035, "iPhotoWidth", "number", "400", _("Width to use for images")),
            "iThumbnailWidth" => new ConfigItem(2036, "iPhotoWidth", "number", "100", _("Width to use for thumbnails")),
            "iInitialsPointSize" => new ConfigItem(2037, "iInitialsPointSize", "number", "150", _("Point size to use for initials thumbnails")),
            "iPhotoClientCacheDuration" => new ConfigItem(2038, "iPhotoClientCacheDuration", "number", "3600", _("Client cache seconds for images")),
            "iRemotePhotoCacheDuration" => new ConfigItem(2039, "iRemotePhotoCacheDuration", "number", "72 hours", _("Server cache time for remote images")),
            "bHSTSEnable" => new ConfigItem(20142, "bHSTSEnable", "boolean", "0", _("Require that this EcclesiaCRM Database is accessed over HTTPS")),
            "bEventsOnDashboardPresence" => new ConfigItem(2042, "bEventsOnDashboardPresence", "boolean", "1", _("Show Birthdates Anniversaries on start up of the CRM")),
            "iEventsOnDashboardPresenceTimeOut" => new ConfigItem(2043, "iEventsOnDashboardPresenceTimeOut", "number", "10", _("Number of seconds after page load until the banner disappears, default 10 seconds")),
            "bPHPMailerAutoTLS" => new ConfigItem(2045, "bPHPMailerAutoTLS", "boolean", "0", _("Automatically enable SMTP encryption if offered by the relaying server.")),
            "sPHPMailerSMTPSecure" => new ConfigItem(2046, "sPHPMailerSMTPSecure", "choice", " ", _("Set the encryption system to use - ssl (deprecated) or tls"), "", '{"Choices":["None: ","TLS:tls","SSL:ssl"]}'),
            "iDashboardPageServiceIntervalTime" => new ConfigItem(2047, "iDashboardPageServiceIntervalTime", "number", "60", _("Dashboard & page Service dynamic asynchronous refresh interval, default 60 second")),
            "bCheckedAttendees" => new ConfigItem(2048, "bCheckedAttendees", "boolean", "1", _("When you make the attendance all the kids are checked by default")),
            "bCheckedAttendeesCurrentUser" => new ConfigItem(2049, "bCheckedAttendeesCurrentUser", "boolean", "0", _("When you make the attendance the current user isn't choosed by default")),
            "bStateUnusefull" => new ConfigItem(2050, "bStateUnusefull", "boolean", "0", _("Hide dropdown states when they are unusefull")),
            "sCurrency" => new ConfigItem(2051, "sCurrency", "text", "$", _("The currency symbol you want to use in the deposit in $ per default")),
            "sUnsubscribeStart" => new ConfigItem(2052, "sUnsubscribeStart", "text", "If you do not want to receive these emails from", _("If you do not want to receive these emails from")),
            "sUnsubscribeEnd" => new ConfigItem(2053, "sUnsubscribeEnd", "text", "in the future, please contact the church admins", _("in the future, please contact the church admins")),
            "iDocumentTimeLeft" => new ConfigItem(2054, "iDocumentTimeLeft", "number", "30", _("Time in minutes when a share document can't be opened.")),
            "sGdprDpoSigner" => new ConfigItem(2056, "sGdprDpoSigner", "text", "", _("The DPO administrator for the GDPR")),
            "bGDPR" => new ConfigItem(2057, "bGDPR", "boolean", "0", _("GDPR option")),
            "sGdprDpoSignerEmail" => new ConfigItem(2058, "sGdprDpoSignerEmail", "text", "", _("The DPO email")),
            "iGdprExpirationDate" => new ConfigItem(2059, "iGdprExpirationDate", "number", "3", _("The private datas should be deleted after the iGdprExpirationDate in Year.")),
            "bEnabledMenuLinks" => new ConfigItem(2070, "bEnabledMenuLinks", "boolean", "0", _("Show custom links on the left menu.")),
            "bEnabledSundaySchool" => new ConfigItem(2071, "bEnabledSundaySchool", "boolean", "1", _("Enable Sunday School left menu.")),
            "bEnabledFinance" => new ConfigItem(2072, "bEnabledFinance", "boolean", "1", _("Enable Finance menu")),
            "bEnabledEvents" => new ConfigItem(2073, "bEnabledEvents", "boolean", "1", _("Enable Events menu.")),
            "bEnabledFundraiser" => new ConfigItem(2075, "bEnabledFundraiser", "boolean", "1", _("Enable Fundraiser menu.")),
            "bEnabledEmail" => new ConfigItem(2076, "bEnabledEmail", "boolean", "1", _("Enable Email menu.")),
            "bEnabledDav" => new ConfigItem(2077, "bEnabledDav", "boolean", "1", _("Enable WebDav, CardDav and CalDav support.")),
            "bEnabledDavWebBrowser" => new ConfigItem(2078, "bEnabledDavWebBrowser", "boolean", "0", _("Enable WebDav, CardDav and CalDav support through a Browser.")),
            "iMailChimpApiMaxMembersCount" => new ConfigItem(2079, "iMailChimpApiMaxMembersCount", "number", "500", _("Max count of members in a MailChimp List.")),
            "bMailChimpWithAddressPhone" => new ConfigItem(2080, "bMailChimpWithAddressPhone", "boolean", "0", _("Add Address, Phone.")),
            "bThumbnailIconPresence" => new ConfigItem(2081, "bThumbnailIconPresence", "boolean", "0", _("Add a thumbnail icon for the people in the datatables.")),
            "bSearchIncludePledges" => new ConfigItem(2082, "bSearchIncludePledges", "boolean", "0", _("Search Pledges")),
            "iSearchIncludePledgesMax" => new ConfigItem(2083, "bSearchIncludePledgesMax", "number", "5", _("Maximum number of Pledges results")),
            "bSearchFinancesGDPR" => new ConfigItem(2084, "bSearchFinancesGDPR", "boolean", "1", _("Allow to get the name in the search sequence in case of pledges and deposits")),
            "sPastoralcarePeriod" => new ConfigItem(2085, "sPastoralcarePeriod", "choice", "Yearly 1", _("'Yearly 1' : from 1 january to 31 december, '365' : in a range from now-365 days to now, 'Yearly 2' : from september to september."), "", '{"Choices":["Yearly 1", "Yearly 2", "365"]}'),
            "sJitsiDomain" => new ConfigItem(2086, "sJitsiDomain", "text", "meet.jit.si", _("The jitsi domain name, by default : meet.jit.si")),
            "sJitsiDomainScriptPath" => new ConfigItem(2087, "sJitsiDomainScriptPath", "text", "https://meet.jit.si/external_api.js", _("The jitsi domain name script path, by default : https://meet.jit.si/external_api.js")),
            "bPastoralcareStats" => new ConfigItem(2088, "bPastoralcareStats", "boolean", "0", _("Get the statistics of calls/visits (for each pastors counselors)")),
            "iPersonAddressStyle" => new ConfigItem(2089, "iPersonAddressStyle", "choice", "", _("Set the export address)"), "", json_encode(SystemConfig::getAddressChoices()))
        );
    }

    private static function buildCategories()
    {
        return array(
            _('Church Information') => ["sChurchName", "sChurchAddress", "sChurchCity", "sChurchState", "sChurchZip", "sChurchCountry", "sChurchPhone", "sChurchEmail", "sHomeAreaCode", "sTimeZone", "iChurchLatitude", "iChurchLongitude", "sChurchWebSite", "sChurchFB", "sChurchTwitter"],
            _('Enabled Features') => ["bEnabledEvents", "bEnabledSundaySchool", "bEnabledEmail", "bEnabledFinance", "bEnabledFundraiser", "bEnabledMenuLinks"],
            _('System Settings') => ["sLogLevel", "bRegistered", "bCSVAdminOnly", "sHeader", "bEnableIntegrityCheck", "iIntegrityCheckInterval", "sLastIntegrityCheckTimeStamp", "iPhotoClientCacheDuration", "bHSTSEnable", "iDocumentTimeLeft", "bThumbnailIconPresence"],
            _('Localization') => ["sLanguage", "bStateUnusefull", "sDistanceUnit", "sPhoneFormat", "sPhoneFormatWithExt", "sPhoneFormatCell", "bTimeEnglish", "sDateFormatLong", "sTimeFormat", "sDateFormatNoYear", "sDateFormatShort", "sDateTimeFormat", "sDateFilenameFormat", "sDatePickerFormat", "sDatePickerPlaceHolder"],
            _('User setup') => ["iMinPasswordLength", "iMinPasswordChange", "iMaxFailedLogins", "iSessionTimeout", "aDisallowedPasswords", "bEnableLostPassword"],
            _('People Setup') => ["sDirClassifications", "sDirRoleHead", "sDirRoleSpouse", "sDirRoleChild", "sDefaultCity", "sDefaultState", "sDefaultCountry", "bShowFamilyData"/*,"bHidePersonAddress"*/, "bHideFriendDate", "bHideFamilyNewsletter", "bHideWeddingDate", "bHideLatLon", "bForceUppercaseZip", "bEnableSelfRegistration", "bAllowEmptyLastName", "iPersonNameStyle", "iPersonAddressStyle", "sNewPersonNotificationRecipientIDs"],
            _('Email Setup') => ["sSMTPHost", "bSMTPAuth", "sSMTPUser", "sSMTPPass", "iSMTPTimeout", "sToEmailAddress", "bPHPMailerAutoTLS", "sPHPMailerSMTPSecure"],
            _('Map Settings') => ["sMapProvider", "sNominatimLink", "sGoogleMapKey", "sBingMapKey", "iMapZoom", "iLittleMapZoom", "sISTusername", "sISTpassword"],
            _('Pastoral Care') => ["sPastoralcarePeriod", "bPastoralcareStats"],
            _('Sundayschool Attendance') => ["bCheckedAttendees", "bCheckedAttendeesCurrentUser"],
            _('Integration') => ["sMailChimpApiKey", "iMailChimpApiMaxMembersCount", "bMailChimpWithAddressPhone", "sJitsiDomain", "sJitsiDomainScriptPath", "sGoogleTrackingID", "bEnableGravatarPhotos", "bEnableGooglePhotos", "iRemotePhotoCacheDuration", "sNexmoAPIKey", "sNexmoAPISecret", "sNexmoFromNumber", "sOLPURL", "sOLPUserName", "sOLPPassword", "bEnabledDav", "bEnabledDavWebBrowser", "bEnableExternalCalendarAPI"],
            _('GDPR') => ["bGDPR", "sGdprDpoSigner", "sGdprDpoSignerEmail", "iGdprExpirationDate", "bSearchFinancesGDPR"],
            _('Quick Search') => ["bSearchIncludePersons", "iSearchIncludePersonsMax", "bSearchIncludeAddresses", "iSearchIncludeAddressesMax", "bSearchIncludeFamilies", "iSearchIncludeFamiliesMax", "bSearchIncludeFamilyHOH", "iSearchIncludeFamilyHOHMax", "bSearchIncludeGroups", "iSearchIncludeGroupsMax", "bSearchIncludeDeposits", "iSearchIncludeDepositsMax", "bSearchIncludePledges", "iSearchIncludePledgesMax", "bSearchIncludePayments", "iSearchIncludePaymentsMax", "bSearchIncludePastoralCare", "iSearchIncludePastoralCareMax"],
            _('Report Settings') => ["sQBDTSettings", "sTaxSigner", "sReminderSigner", "leftX", "incrementY", "sTaxReport1", "sTaxReport2", "sTaxReport3", "sReminder1", "sReminderNoPledge", "sReminderNoPayments", "sConfirm1", "sConfirm2", "sConfirm3", "sConfirm4", "sConfirm5", "sConfirm6", "sDear", "sConfirmSincerely", "sConfirmSigner", "sUnsubscribeStart", "sUnsubscribeEnd", "sPledgeSummary1", "sPledgeSummary2", "sDirectoryDisclaimer1", "sDirectoryDisclaimer2", "bDirLetterHead", "sZeroGivers", "sZeroGivers2", "sZeroGivers3", "iPDFOutputType"],
            _('Financial Settings') => ["sCurrency", "sDepositSlipType", "iChecksPerDepositForm", "bDisplayBillCounts", "bUseScannedChecks", "sElectronicTransactionProcessor", "bEnableNonDeductible", "iFYMonth", "bUseDonationEnvelopes", "aFinanceQueries"],
            _('Backup') => ["sLastBackupTimeStamp", "bEnableExternalBackupTarget", "bGZIP", "bZIP", "sPGP", "sExternalBackupType", "sExternalBackupAutoInterval", "sExternalBackupEndpoint", "sExternalBackupUsername", "sExternalBackupPassword"],
            _('Users Specific Schedule Tasks') => ["bEventsOnDashboardPresence", "iEventsOnDashboardPresenceTimeOut", "iDashboardPageServiceIntervalTime"]
        );
    }

    /**
     * @param Config[] $configs
     */
    public static function init($configs = null)
    {
        self::$configs = self::buildConfigs();
        self::$categories = self::buildCategories();
        if (!empty($configs)) {
            self::scrapeDBConfigs($configs);
        }
    }

    public static function isInitialized()
    {
        return isset(self::$configs);
    }

    public static function getCategories()
    {
        return self::$categories;
    }

    private static function scrapeDBConfigs($configs)
    {
        foreach ($configs as $config) {
            if (isset(self::$configs[$config->getName()])) {
                //if the current config set defined by code contains the current config retreived from the db, then cache it
                self::$configs[$config->getName()]->setDBConfigObject($config);
            } else {
                //there's a config item in the DB that doesn't exist in the current code.
                //delete it
                $config->delete();
            }
        }
    }

    public static function getConfigItem($name)
    {
        return self::$configs[$name];
    }

    public static function getValue($name)
    {
        if (isset(self::$configs[$name])) {
            return self::$configs[$name]->getValue();
        } else {
            throw new \Exception (_("An invalid configuration name has been requested") . ": " . $name);
        }
    }

    public static function getBooleanValue($name)
    {
        if (isset(self::$configs[$name])) {
            return self::$configs[$name]->getBooleanValue();
        } else {
            throw new \Exception (_("An invalid configuration name has been requested") . ": " . $name);
        }

    }

    public static function setValue($name, $value)
    {
        if (isset(self::$configs[$name])) {
            self::$configs[$name]->setValue($value);
        } else {
            throw new \Exception (_("An invalid configuration name has been requested") . ": " . $name);
        }

    }

    public static function setValueById($Id, $value)
    {
        $success = false;
        foreach (self::$configs as $configItem) {
            if ($configItem->getId() == $Id) {
                $configItem->setValue($value);
                $success = true;
            }
        }
        if (!$success) {
            throw new \Exception (_("An invalid configuration id has been requested") . ": " . $Id);
        }
    }

    public static function hasValidMailServerSettings()
    {
        $hasValidSettings = true;
        if (empty(self::getValue("sSMTPHost"))) {
            $hasValidSettings = false;
        }

        if (SystemConfig::getBooleanValue("bSMTPAuth") and (empty(self::getValue("sSMTPUser")) or empty(self::getValue("sSMTPPass")))) {
            $hasValidSettings = false;
        }

        return $hasValidSettings;
    }

    public static function hasValidSMSServerSettings()
    {
        return (!empty(self::getValue("sNexmoAPIKey"))) && (!empty(self::getValue("sNexmoAPISecret"))) && (!empty(self::getValue("sNexmoFromNumber")));
    }

    public static function hasValidOpenLPSettings()
    {
        return (!empty(self::getValue("sOLPURL")));
    }

}
