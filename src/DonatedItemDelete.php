<?php
/*******************************************************************************
*
*  filename    : Reports/PaddleNumDelete.php
*  last change : 2011-04-03
*  description : Deletes a specific donated item
*  copyright   : Copyright 2009 Michael Wilt

******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;

use EcclesiaCRM\DonatedItemQuery;

$iDonatedItemID = InputUtils::LegacyFilterInput($_GET['DonatedItemID'], 'int');
$linkBack = InputUtils::LegacyFilterInput($_GET['linkBack'], 'string');

$iFundRaiserID = $_SESSION['iCurrentFundraiser'];

$di = DonatedItemQuery::create()
    ->filterByFrId($iFundRaiserID)
    ->findOneById($iDonatedItemID);

if (!is_null($di)) {
    $di->delete();
}
RedirectUtils::Redirect($linkBack);
