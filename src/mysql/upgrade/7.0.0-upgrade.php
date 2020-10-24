<?php
// pour le debug on se met au bon endroit : http://192.168.151.205/mysql/upgrade/7.0.0-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use Propel\Runtime\Propel;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot()."/KioskManager.php");
unlink(SystemURLs::getDocumentRoot()."/v2/templates/sundayschool/sundayschoolbadge.php");
unlink(SystemURLs::getDocumentRoot()."/Reports/PDFBadgeSundaySchool.php");
unlink(SystemURLs::getDocumentRoot()."/v2/routes/people/pastoralcare.php");
unlink(SystemURLs::getDocumentRoot()."/v2/templates/people/pastoralcarefamily.php");
unlink(SystemURLs::getDocumentRoot()."/v2/templates/people/pastoralcareperson.php");
unlink(SystemURLs::getDocumentRoot()."/skin/js/people/PastoralCareFamily.js");
unlink(SystemURLs::getDocumentRoot()."/skin/js/people/PastoralCarePerson.js");
unlink(SystemURLs::getDocumentRoot()."/api/routes/sidebar/sidebar-pastoralcare.php");

// refactor of the fundraiser in v2 arch
unlink(SystemURLs::getDocumentRoot()."/DonatedItemReplicate.php");
unlink(SystemURLs::getDocumentRoot()."/DonatedItemDelete.php");
unlink(SystemURLs::getDocumentRoot()."/DonatedItemEditor.php");
unlink(SystemURLs::getDocumentRoot()."/FindFundRaiser.php");
unlink(SystemURLs::getDocumentRoot()."/PaddleNumDelete.php");
unlink(SystemURLs::getDocumentRoot()."/AddDonors.php");
unlink(SystemURLs::getDocumentRoot()."/PaddleNumEditor");
unlink(SystemURLs::getDocumentRoot()."/PaddleNumList.php");

unlink(SystemURLs::getDocumentRoot()."ConvertIndividualToFamily.php");

unlink(SystemURLs::getDocumentRoot()."/Menu.php");

$logger->info("End of delete :  all unusefull files");
?>
