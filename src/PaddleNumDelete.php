<?php
/*******************************************************************************
*
*  filename    : Reports/PaddleNumDelete.php
*  last change : 2009-04-17
*  description : Deletes a specific paddle number holder
*  copyright   : Copyright 2009 Michael Wilt

******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;

use EcclesiaCRM\PaddleNumQuery;

$iPaddleNumID = InputUtils::LegacyFilterInput($_GET['PaddleNumID'], 'int');
$linkBack = InputUtils::LegacyFilterInput($_GET['linkBack'], 'string');

$iFundRaiserID = $_SESSION['iCurrentFundraiser'];

$pn = PaddleNumQuery::create()
    ->filterById($iPaddleNumID)
    ->findOneByFrId($iFundRaiserID);

if (!is_null($pn)) {
    $pn->delete();
}

RedirectUtils::Redirect($linkBack);
