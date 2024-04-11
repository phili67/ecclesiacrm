<?php

/*******************************************************************************
 *
 *  filename    : Reports/ConfirmReportEmail.php
 *  last change : 2020-10-09 Philippe Logel
 *  description : Create emails with all the confirmation letters asking member
 *                families to verify the information in the database.
 *
 *  Test : http://url/Reports/ConfirmReportEmail.php?familyId=274
 *
 ******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\Reports\EmailUsers;

if (!SessionUser::getUser()->isCreateDirectoryEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

// it's possible to add the families : fams as a string
$fams = NULL;
$persons = NULL;

if (isset($_SESSION['POST_Datas'])) {
    $_POST = $_SESSION['POST_Datas'];
    unset($_SESSION['POST_Datas']);
}

$exportType = 'family';

if (isset($_POST['letterandlabelsnamingmethod'])) {
    $exportType = $_POST['letterandlabelsnamingmethod'];
}

if (isset($_GET['fams'])) {
    $fams = explode(",", $_GET['fams']);
}

if (isset($_GET['persons'])) {
    $persons = explode(",", $_GET['persons']);
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

$fams = $_POST['familiesId'];
if (strlen($fams) > 0) {
    $fams = explode(",",$_POST['familiesId']);
} else {
    $fams = Null;
}

$persons = $_POST['personsId'];
if (strlen($persons) > 0) {
    $persons = explode(",",$persons);
} else {
    $persons = Null;
}

$fams_to_contact = new EmailUsers($fams, $persons);

$familyEmailSent = $fams_to_contact->renderAndSend($exportType, $minAge, $maxAge, $classList);

if ($_GET['familyId']) {
    RedirectUtils::Redirect('v2/people/family/view/' . $_GET['familyId']);
} else {
    RedirectUtils::Redirect('v2/people/LettersAndLabels');
}
