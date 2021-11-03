<?php
/*******************************************************************************
*
*  filename    : Reports/NewsLetterLabels.php
*  last change : 2003-08-30
*  description : Creates a PDF with all the newletter mailing labels

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\PDF_NewsletterLabels;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Utils\MiscUtils;

$sLabelFormat = InputUtils::LegacyFilterInput($_GET['labeltype']);
$bRecipientNamingMethod = $_GET['recipientnamingmethod'];
setcookie('labeltype', $sLabelFormat, time() + 60 * 60 * 24 * 90, '/');

// Instantiate the directory class and build the report.
$pdf = new PDF_NewsletterLabels($sLabelFormat);

$sFontInfo = MiscUtils::FontFromName($_GET['labelfont']);
setcookie('labelfont', $_GET['labelfont'], time() + 60 * 60 * 24 * 90, '/');
$sFontSize = $_GET['labelfontsize'];
setcookie('labelfontsize', $sFontSize, time() + 60 * 60 * 24 * 90, '/');
$pdf->SetFont($sFontInfo[0], $sFontInfo[1]);
if ($sFontSize != 'default') {
    $pdf->Set_Char_Size($sFontSize);
}

// get only the persons who wants the news letter
$onlyPersons = PersonQuery::Create()// Get the persons and not the family with the news letter
             ->filterBySendNewsletter('TRUE')
             ->_and()
             ->useFamilyQuery()
               ->filterBySendNewsletter('FALSE')
             ->endUse()
             ->find();

// Get all the families which receive the newsletter by mail
$families = FamilyQuery::create()
        ->filterBySendNewsletter("TRUE")
        ->orderByZip()
        ->find();

foreach ($onlyPersons as $person) {
    if ( is_null ($person->getFamily()) ) continue;

    $labelText = $person->getFamily()->getName(). " " . $person->getFirstName() . ' (' . _('Person') . ')';

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


header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('NewsLetterLabels'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
