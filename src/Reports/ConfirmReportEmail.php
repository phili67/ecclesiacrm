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

$fams = NULL;

if (InputUtils::LegacyFilterInput($_GET['familyId'], 'int')) {
    $fams = explode(",", $_GET['familyId']);
}

$fams_to_contact = new EmailUsers($fams);

$familyEmailSent = $fams_to_contact->renderAndSend();

if ($_GET['familyId']) {
    RedirectUtils::Redirect('v2/people/family/view/' . $_GET['familyId'] . '&PDFEmailed=' . $familyEmailSent);
} /*else {
    RedirectUtils::Redirect('v2/familylist/AllPDFsEmailed/'.$familiesEmailed);
}*/
