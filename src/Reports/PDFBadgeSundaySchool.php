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
use EcclesiaCRM\Reports\PDF_Badge;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutpuUtils;
use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;


function GenerateLabels(&$pdf, $iGroupId, $useCart=0, $sundayschoolName,$sFirstNameFontSize,$image, $title_red, $title_gren, $title_blue, $back_red, $back_gren, $back_blue,$sImagePosition)
{
    $sundaySchoolService = new SundaySchoolService();

    $rsTeachers = $sundaySchoolService->getClassByRole($iGroupId, 'Teacher');
    $thisClassChildren = $sundaySchoolService->getKidsFullDetails($iGroupId);

    foreach ($thisClassChildren as $kid) {
        if ( !($useCart == 0 || ($useCart==1 && in_array($kid['kidId'], $_SESSION['aPeopleCart'])) ) ) 
          continue;
        
        $assignedProperties = Record2propertyR2pQuery::Create()
                            ->findByR2pRecordId($kid['kidId']);
                            
        $props = "";
        if (!empty($assignedProperties)) {
            foreach ($assignedProperties as $assproperty) {
                $property = PropertyQuery::Create()->findOneByProId ($assproperty->getR2pProId());
                $props.= $property->getProName()."\n";
            }
                
            //$props = chop($props, "\n");
                    
            if (strlen($props)>0) {
                $props = " !!! ".$props;
            }
        }
        
        $pdf->Add_PDF_Badge($sundayschoolName, $kid['LastName'], $kid['firstName'],$kid['sundayschoolClass'],$props,$sFirstNameFontSize, $image, $title_red, $title_gren, $title_blue, $back_red, $back_gren, $back_blue,$sImagePosition);
    }
} // end of function GenerateLabels

// Main body of PHP file begins here

if ( !empty($_FILES["stickerBadgeInputFile"]["name"]) ) {
  $sImage = basename($_FILES["stickerBadgeInputFile"]["name"]);
  
  $target_file = '../Images/background/' . basename($_FILES["stickerBadgeInputFile"]["name"]);
  
  $file_type = $_FILES['stickerBadgeInputFile']['type']; //returns the mimetype

  $allowed = array("image/jpeg", "image/png");
  if(in_array($file_type, $allowed)) {
    if (move_uploaded_file($_FILES['stickerBadgeInputFile']['tmp_name'], $target_file)) {
    }
    
    setcookie('imageSC', $sImage , time() + 60 * 60 * 24 * 90, '/');
  
    $page = str_replace("&typeProblem=1","",$_SERVER['HTTP_REFERER']);

    header('Location: ' . $page);
    
    exit;
  }
  
  header('Location: ' . $_SERVER['HTTP_REFERER'] . "&typeProblem=1");

  exit;
}


// Standard format
$iGroupId = InputUtils::LegacyFilterInput($_POST['groupId'], 'int');
$useCart = InputUtils::LegacyFilterInput($_POST['useCart'], 'int');

// sunday school name
$sundaySchoolName = InputUtils::FilterString($_POST['sundaySchoolName']);
setcookie('sundaySchoolNameSC', $sundaySchoolName, time() + 60 * 60 * 24 * 90, '/');

// background color
$sBackgroudColor = InputUtils::LegacyFilterInput($_POST['backgroud-color'], 'char',255);
setcookie('sBackgroudColorSC', $sBackgroudColor, time() + 60 * 60 * 24 * 90, '/');

// image
$sImage = InputUtils::LegacyFilterInput($_POST['image'], 'char',255);
setcookie('imageSC', $sImage, time() + 60 * 60 * 24 * 90, '/');

$sImagePosition = InputUtils::LegacyFilterInput($_POST['imagePosition'], 'char',255);
setcookie('imagePositionSC', $sImagePosition, time() + 60 * 60 * 24 * 90, '/');

// transform the hex color in RGB
list($back_red, $back_gren, $back_blue) = sscanf($sBackgroudColor, "#%02x%02x%02x");

// title color
$sTitleColor = InputUtils::LegacyFilterInput($_POST['title-color'], 'char',255);

setcookie('sTitleColorSC', $sTitleColor, time() + 60 * 60 * 24 * 90, '/');

// transform the hex color in RGB
list($title_red, $title_gren, $title_blue) = sscanf($sTitleColor, "#%02x%02x%02x");

$startcol = InputUtils::LegacyFilterInput($_POST['startcol'], 'int');
if ($startcol < 1) {
    $startcol = 1;
}

$startrow = InputUtils::LegacyFilterInput($_POST['startrow'], 'int');
if ($startrow < 1) {
    $startrow = 1;
}

$sLabelType = InputUtils::LegacyFilterInput($_POST['labeltype'], 'char', 10);

if ($sLabelType == gettext('Tractor') ) {
  $sLabelType = 'Tractor';
}

setcookie('labeltypeSC', $sLabelType, time() + 60 * 60 * 24 * 90, '/');

$pdf = new PDF_Badge($sLabelType, $startcol, $startrow);

$sFontInfo = MiscUtils::FontFromName($_POST['labelfont']);
setcookie('labelfontSC', $_POST['labelfont'], time() + 60 * 60 * 24 * 90, '/');

// set the Font Size for the FirstName
$sFontSize = $_POST['labelfontsize'];
setcookie('labelfontsizeSC', $sFontSize, time() + 60 * 60 * 24 * 90, '/');
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
if ($sImage != '') {
  $image = '../Images/background/'.$sImage;
}

$aLabelList = unserialize(GenerateLabels($pdf, $iGroupId, $useCart, $sundaySchoolName,$sFontSize,$image,$title_red, $title_gren, $title_blue, $back_red, $back_gren, $back_blue,$sImagePosition));

header('Pragma: public');  // Needed for IE when using a shared SSL certificate

if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('Labels-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}

exit();
