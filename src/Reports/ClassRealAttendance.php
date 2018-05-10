<?php
/*******************************************************************************
*
*  filename    : Reports/ClassRealAttendance.php
*  description : Creates a PDF for a Sunday School Class Attendance List
*  Udpdated    : 2018-05-09
*                Philippe Logel all rights reserved not MIT Licence
******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';


use EcclesiaCRM\Reports\PDF_RealAttendance;
use EcclesiaCRM\Utils\InputUtils;

//Security
if (!isset($_SESSION['user'])) {
    Redirect('Menu.php');
    exit;
}

// we get all the params
$iGroupID = InputUtils::LegacyFilterInput($_GET['groupID']);

$groups = explode(',', $iGroupID);

$withPictures = InputUtils::LegacyFilterInput($_GET['withPictures'], 'int');
$iExtraStudents = InputUtils::LegacyFilterInputArr($_GET, 'ExtraStudents', 'int');
$iFYID = $_SESSION['idefaultFY'];// $iFYID = InputUtils::LegacyFilterInput($_GET['FYID'], 'int'); //
$startDate = $_GET['start'];
$endDate   = $_GET['end'];

$pdfRealAttendees = new PDF_RealAttendance($groups,$withPictures,$iExtraStudents,$iFYID,$startDate,$endDate);

$pdfRealAttendees->render();