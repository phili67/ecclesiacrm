<?php
/*******************************************************************************
*
*  filename    : Reports/ConfirmReport.php
*  last change : 2026-06-11 Philippe Logel
*  description : Creates a PDF with all the confirmation letters asking member
*                families to verify the information in the database.

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\Reports\PDF_ConfirmReport;

use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

if (!SessionUser::getUser()->isCreateDirectoryEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

$pdf = new PDF_ConfirmReport();
        
$pdf->run();