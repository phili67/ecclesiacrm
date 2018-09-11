<?php
/*******************************************************************************
*
*  filename    : Reports/PDFLabel.php
*  website     : http://www.ecclesiacrm.com
*  description : Creates a PDF document containing the addresses of
*                The people in the Cart
*
*  Copyright 2003  Jason York
*
*  Portions based on code by LPA (lpasseb@numericable.fr)
*  and Steve Dillon (steved@mad.scientist.com) from www.fpdf.org
*
*  Copyright : Philippe Logel all rights reserved
*
******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';
require '../Include/ReportFunctions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\PDF_Label;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutpuUtils;
use EcclesiaCRM\Service\SundaySchoolService;

function GenerateLabels(&$pdf, $iGroupId, $sundayschoolName,$sFirstNameFontSize,$image, $title_red, $title_gren, $title_blue, $back_red, $back_gren, $back_blue,$sImagePosition)
{
    $sundaySchoolService = new SundaySchoolService();

    $rsTeachers = $sundaySchoolService->getClassByRole($iGroupId, 'Teacher');
    $thisClassChildren = $sundaySchoolService->getKidsFullDetails($iGroupId);

    //print_r ($thisClassChildren);
    
    foreach ($thisClassChildren as $kid) {
        $pdf->Add_PDF_Label_SundaySchool($sundayschoolName, $kid['LastName'], $kid['firstName'],$kid['sundayschoolClass'],$sFirstNameFontSize, $image, $title_red, $title_gren, $title_blue, $back_red, $back_gren, $back_blue,$sImagePosition);
    }
} // end of function GenerateLabels

// Main body of PHP file begins here

// Standard format

$iGroupId = InputUtils::LegacyFilterInput($_GET['groupId'], 'int');

// sunday school name
$sundaySchoolName = InputUtils::LegacyFilterInput($_GET['sundaySchoolName'], 'char',255);
setcookie('sundaySchoolName', $sundaySchoolName, time() + 60 * 60 * 24 * 90, '/');

// background color
$sBackgroudColor = InputUtils::LegacyFilterInput($_GET['backgroud-color'], 'char',255);
setcookie('sBackgroudColor', $sBackgroudColor, time() + 60 * 60 * 24 * 90, '/');

// image
$sImage = InputUtils::LegacyFilterInput($_GET['image'], 'char',255);
setcookie('image', $sImage, time() + 60 * 60 * 24 * 90, '/');

$sImagePosition = InputUtils::LegacyFilterInput($_GET['imagePosition'], 'char',255);
setcookie('imagePosition', $sImagePosition, time() + 60 * 60 * 24 * 90, '/');

// transform the hex color in RGB
list($back_red, $back_gren, $back_blue) = sscanf($sBackgroudColor, "#%02x%02x%02x");

// title color
$sTitleColor = InputUtils::LegacyFilterInput($_GET['title-color'], 'char',255);

setcookie('sTitleColor', $sTitleColor, time() + 60 * 60 * 24 * 90, '/');

// transform the hex color in RGB
list($title_red, $title_gren, $title_blue) = sscanf($sTitleColor, "#%02x%02x%02x");

$startcol = InputUtils::LegacyFilterInput($_GET['startcol'], 'int');
if ($startcol < 1) {
    $startcol = 1;
}

$startrow = InputUtils::LegacyFilterInput($_GET['startrow'], 'int');
if ($startrow < 1) {
    $startrow = 1;
}

$sLabelType = InputUtils::LegacyFilterInput($_GET['labeltype'], 'char', 10);

if ($sLabelType == gettext('Tractor') ) {
  $sLabelType = 'Tractor';
}

setcookie('labeltype', $sLabelType, time() + 60 * 60 * 24 * 90, '/');

$pdf = new PDF_Label($sLabelType, $startcol, $startrow);

$sFontInfo = FontFromName($_GET['labelfont']);
setcookie('labelfont', $_GET['labelfont'], time() + 60 * 60 * 24 * 90, '/');

// set the Font Size for the FirstName
$sFontSize = $_GET['labelfontsize'];
setcookie('labelfontsize', $sFontSize, time() + 60 * 60 * 24 * 90, '/');
$pdf->SetFont($sFontInfo[0], $sFontInfo[1]);

if ($sFontSize == gettext('default')) {
    $sFontSize = '20';
}

$pdf->Set_Char_Size(10);

// Manually add a new page if we're using offsets
if ($startcol > 1 || $startrow > 1) {
    $pdf->AddPage();
}

// à gérer par la suite
$image = '../Images/'.$sImage;

$aLabelList = unserialize(GenerateLabels($pdf, $iGroupId, $sundaySchoolName,$sFontSize,$image,$title_red, $title_gren, $title_blue, $back_red, $back_gren, $back_blue,$sImagePosition));

header('Pragma: public');  // Needed for IE when using a shared SSL certificate

if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('Labels-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}

exit();
