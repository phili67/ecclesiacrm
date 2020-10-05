<?php
/***********************************************************************
 *
 * This source code can only be called through :
 * http://www.mydomain.com/ecclesiacrm/FamilyVerify.php?FamilyID=274
 *
 ***********************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';



use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Utils\InputUtils;

//Get the FamilyID out of the querystring
$iFamilyID = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');

$family =  FamilyQuery::create()
    ->findOneById($iFamilyID);

$family->verify();

header('Location: '.$family->getViewURI());
exit;
