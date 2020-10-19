<?php
/*******************************************************************************
*
*  filename    : ConvertIndividualToFamily.php
*  website     : http://www.ecclesiacrm.com
*  description : utility to convert individuals to families
*
*  Must be run manually by an administrator.  Type this URL.
*    http://www.mydomain.com/ecclesiacrm/ConvertIndividualToFamily.php
*
*  By default this script does one at a time.  To do all entries
*  at once use this URL
*    http://www.mydomain.com/ConvertIndividualToFamily.php?all=true
*
*  Your URL may vary.  Replace "ecclesiacrm" with $sRootPath
*
*  Contributors:
*  2007 Ed Davis
*  2020 Philippe Logel Propeled
******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\Family;

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;

use EcclesiaCRM\Map\FamilyTableMap;

// Security
if (!SessionUser::getUser()->isAdmin()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if ($_GET['all'] == 'true') {
    $bDoAll = true;
}

//Set the page title
$sPageTitle = gettext('Convert Individuals to Families');

require 'Include/Header.php';

$iUserID = SessionUser::getUser()->getPersonId();

// find the family ID so we can associate to person record
$lastEntry = FamilyQuery::create()
    ->addAsColumn('iFamilyID', 'MAX('.FamilyTableMap::COL_FAM_ID.')')
    ->findOne();

$iFamilyID = $lastEntry->getiFamilyID();


echo $iFamilyID;


// Get list of people that are not assigned to a family
$ormList = PersonQuery::create()
    ->filterByFamId(0)
    ->orderByLastName()
    ->orderByFirstName()
    ->find();

foreach ($ormList as $per) {

    if ($per->getId() == 1) continue; // in the case of the super Admin continue

    echo '<br><br><br>';
    echo '*****************************************';

    $fam = new Family();

    $fam->setName($per->getLastName());
    $fam->setAddress1($per->getAddress1());
    $fam->setAddress2($per->getAddress2());
    $fam->setCity($per->getCity());
    $fam->setState($per->getState());
    $fam->setZip($per->getZip());
    $fam->setCountry($per->getCountry());
    $fam->setHomePhone($per->getHomePhone());
    $fam->setDateEntered(new DateTime());
    $fam->setEnteredBy($iUserID);

    $fam->save();

    $iFamilyID++; // increment family ID

    //Get the key back
    $lastEntry = FamilyQuery::create()
        ->addAsColumn('iNewFamilyID', 'MAX('.FamilyTableMap::COL_FAM_ID.')')
        ->findOne();

    $iNewFamilyID = $lastEntry->getiNewFamilyID();

    if ($iNewFamilyID != $iFamilyID) {
        echo '<br><br>Error with family ID';

        break;
    }

    echo '<br><br>';

    // Now update person record
    $person = PersonQuery::create()->findOneById($per->getId());

    $person->setFamId($fam->getId());
    $person->setEditedBy($iUserID);

    $person->save();

    echo '<br><br><br>';
    echo $person->getFirstName()." ".$person->getLastName()." (per_ID = ".$person->getId().") is now part of the ";
    echo $person->getLastName()." Family (fam_ID = ".$fam->getId().")<br>";
    echo '*****************************************';

    if (!$bDoAll) {
        break;
    }
}
echo '<br><br>';

echo '<a href="ConvertIndividualToFamily.php">'.gettext('Convert Next').'</a><br><br>';
echo '<a href="ConvertIndividualToFamily.php?all=true">'.gettext('Convert All').'</a><br>';

require 'Include/Footer.php';
