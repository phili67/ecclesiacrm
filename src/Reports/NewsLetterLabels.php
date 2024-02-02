<?php
/*******************************************************************************
*
*  filename    : Reports/NewsLetterLabels.php
*  last change : 2003-08-30
*  description : Creates a PDF with all the newletter mailing labels

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\Reports\PDF_NewsletterLabels;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\InputUtils;

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;

use EcclesiaCRM\Map\PersonTableMap;


if (isset($_SESSION['POST_Datas'])) {
    $_POST = $_SESSION['POST_Datas'];
    unset($_SESSION['POST_Datas']);
}

$sLabelFormat = InputUtils::LegacyFilterInput($_POST['labeltype']);
$bRecipientNamingMethod = $_POST['recipientnamingmethod'];
setcookie('labeltype', $sLabelFormat, time() + 60 * 60 * 24 * 90, '/');

// Instantiate the directory class and build the report.
$pdf = new PDF_NewsletterLabels($sLabelFormat);

$sFontInfo = MiscUtils::FontFromName($_POST['labelfont']);
setcookie('labelfont', $_POST['labelfont'], time() + 60 * 60 * 24 * 90, '/');
$sFontSize = $_POST['labelfontsize'];
setcookie('labelfontsize', $sFontSize, time() + 60 * 60 * 24 * 90, '/');
$pdf->SetFont($sFontInfo[0], $sFontInfo[1]);
if ($sFontSize != 'default') {
    $pdf->Set_Char_Size($sFontSize);
}

$exportType = 'family';

if (isset($_POST['letterandlabelsnamingmethod'])) {
    $exportType = $_POST['letterandlabelsnamingmethod'];
}

$minAge = 18;
if (isset($_POST['minAge'])) {
    $minAge = InputUtils::FilterInt($_POST['minAge']);
}

$maxAge = 130;
if (isset($_POST['maxAge'])) {
    $maxAge = InputUtils::FilterInt($_POST['maxAge']);
}

$classList = "*";
if (isset($_POST['classList'])) {
    $classList = $_POST['classList'];
}


if ($exportType == "family") {
    $ormFamilies = FamilyQuery::create();

    if ($_GET['familyId']) {
        $families = explode(",", $_GET['familyId']);
        $ormFamilies->filterById($families);
    }

    $ormFamilies->filterByDateDeactivated(NULL);

    $ormFamilies->filterBySendNewsletter("TRUE");

    // Get all the families
    $ormFamilies->orderByName()->find();

    foreach ($ormFamilies as $family) {
        if ($bRecipientNamingMethod == "familyname") {
            $labelText = $family->getName();
        } else {
            $labelText = $pdf->MakeSalutation($family->getID());
        }
        if ($family->getAddress1() != '') {
            $labelText .= "\n".$family->getAddress1();
        }
        if ($family->getAddress2() != '') {
            $labelText .= "\n".$family->getAddress2();
        }
        $labelText .= sprintf("\n%s, %s  %s", $family->getCity(), $family->getState(), $family->getZip());
    
        if ($family->getCountry() != '' && $family->getCountry() != 'USA' && $family->getCountry() != 'United States') {
            $labelText .= "\n".$family->getCountry();
        }
    
        $pdf->Add_PDF_Label($labelText);
    }

} else if ($exportType == "person") {
    //Get the family members for this family
    $ormFamilyMembers = PersonQuery::create()
        ->filterByDateDeactivated(NULL)
        ->filterBySendNewsletter("TRUE");

    if ($classList != "*") {
        $ormFamilyMembers->filterByClsId($classList);
    }

    if ($minAge != 0 or $maxAge != 130) {
        $ormFamilyMembers->where('DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL ' . $minAge . ' YEAR) <= CURDATE() AND DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL (' . $maxAge . '+1) YEAR) >= CURDATE()');
    }

    $ormFamilyMembers->find();

    foreach ($ormFamilyMembers as $person) {
        if ( is_null ($person->getFamily()) ) continue;

        $labelText = $person->getFamily()->getName(). " " . $person->getFirstName();

        if ($person->getFamily()->getAddress1() != '') {
            $labelText .= "\n".$person->getFamily()->getAddress1();
        }
        if ($person->getFamily()->getAddress2() != '') {
            $labelText .= "\n".$person->getFamily()->getAddress2();
        }
        $labelText .= sprintf("\n%s, %s  %s", $person->getFamily()->getCity(), $person->getFamily()->getState(), $person->getFamily()->getZip());

        if ($person->getFamily()->getCountry() != '' && $person->getFamily()->getCountry() != 'USA' && $person->getFamily()->getCountry() != 'United States') {
            $labelText .= "\n".$person->getFamily()->getCountry();
        }

        $pdf->Add_PDF_Label($labelText);
    }        
}


header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('NewsLetterLabels'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
