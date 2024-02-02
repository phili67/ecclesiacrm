<?php
/*******************************************************************************
*
*  filename    : Reports/ConfimLabels.php
*  last change : 2003-08-30
*  description : Creates a PDF with all the mailing labels for the confirm data letter

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\Reports\PDF_Label;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;

use Propel\Runtime\ActiveQuery\Criteria;

if (isset($_SESSION['POST_Datas'])) {
    $_POST = $_SESSION['POST_Datas'];
    unset($_SESSION['POST_Datas']);
}

$sLabelFormat = InputUtils::LegacyFilterInput($_GET['labeltype']);
$bRecipientNamingMethod = $_GET['recipientnamingmethod'];
setcookie('labeltype', $sLabelFormat, time() + 60 * 60 * 24 * 90, '/');

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

$pdf = new PDF_Label($sLabelFormat);


$sFontInfo = MiscUtils::FontFromName($_POST['labelfont']);
setcookie('labelfont', $_POST['labelfont'], time() + 60 * 60 * 24 * 90, '/');
$sFontSize = $_POST['labelfontsize'];
setcookie('labelfontsize', $sFontSize, time() + 60 * 60 * 24 * 90, '/');
$pdf->SetFont($sFontInfo[0], $sFontInfo[1]);
if ($sFontSize != 'default') {
    $pdf->Set_Char_Size($sFontSize);
}

$cnt = 0;

if ($exportType == "family") {    
    $families = FamilyQuery::create();

    if ($_GET['familyId']) {
        $fams = explode(",", $_GET['familyId']);
        $families->filterById($fams);
    }

    $families->filterByDateDeactivated(NULL);

    // Get all the families
    $families->orderByName()->orderByZip()->find();

    $cnt += $families->count();

    foreach ($families as $family) {
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
    // Get all the families which receive the newsletter by mail
    $families = FamilyQuery::create();

    if ($_GET['familyId']) {
        $fams = explode(",", $_GET['familyId']);
        $families->filterById($fams);
    }

    $families->filterByDateDeactivated(NULL);

    // Get all the families
    $families->orderByName()->find();// ->orderByZip()

    foreach ($families as $family) {
        //Get the family members for this family
            $ormFamilyMembers = PersonQuery::create()
            ->filterByDateDeactivated(NULL)
            ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('ClassName', ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONNAME))
            ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::COL_PER_FMR_ID, ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_ID), 2)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('FamRole', ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONNAME))
            ->filterByFamId($family->getId())
            ->orderByFmrId();

        if ($classList != "*") {
            $ormFamilyMembers->filterByClsId($classList);
        }

        if ($minAge != 0 or $maxAge != 130) {
            $ormFamilyMembers->where('DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL ' . $minAge . ' YEAR) <= CURDATE() AND DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL (' . $maxAge . '+1) YEAR) >= CURDATE()');
        }

        $ormFamilyMembers->find();

        $cnt += $ormFamilyMembers->count();

        foreach ($ormFamilyMembers as $person) {
            if ($bRecipientNamingMethod == "familyname") {
                $labelText = $person->getName();
            } else {
                $labelText = $pdf->MakeSalutation($person->getID(), "person");
            }
            if ($family->getAddress1() != '') {
                $labelText .= "\n".$person->getFamily()->getAddress1();
            }
            if ($family->getAddress2() != '') {
                $labelText .= "\n".$person->getFamily()->getAddress2();
            }
            $labelText .= sprintf("\n%s, %s  %s", $family->getCity(), $family->getState(), $family->getZip());
    
            if ($family->getCountry() != '' && $person->getFamily()->getCountry() != 'USA' && $person->getFamily()->getCountry() != 'United States') {
                $labelText .= "\n".$person->getFamily()->getCountry();
            }
    
            $pdf->Add_PDF_Label($labelText);
        }
    }
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('ConfirmDataLabels'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
