<?php
/*******************************************************************************
*
*  filename    : ConvertIndividualToFamily.php
*  website     : http://www.ecclesiacrm.com
*  description : utility to convert individuals to families
*
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
$sPageTitle = gettext('Convert Individuals to Addresses');

require 'Include/Header.php';

$iUserID = SessionUser::getUser()->getPersonId();

// find the family ID so we can associate to person record
$lastEntry = FamilyQuery::create()
    ->addAsColumn('iFamilyID', 'MAX('.FamilyTableMap::COL_FAM_ID.')')
    ->findOne();

$iFamilyID = $lastEntry->getiFamilyID();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= _("Family")." ID" ?></h3>
    </div>
    <div class="card-body">
        <?= $iFamilyID ?>

<?php
// Get list of people that are not assigned to a family
$ormList = PersonQuery::create()
    ->filterByFamId(0)
    ->orderByLastName()
    ->orderByFirstName()
    ->find();

foreach ($ormList as $per) {
    if ($per->getId() == 1) continue; // in the case of the super Admin continue
    ?>

    <br><br><br>
    *****************************************

    <?php

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
        ?>
        <br><br>Error with family ID
        <?php
        break;
    }

    ?>
    <br><br>

<?php
    // Now update person record
    $person = PersonQuery::create()->findOneById($per->getId());

    $person->setFamId($fam->getId());
    $person->setEditedBy($iUserID);

    $person->save();
    ?>

    <br><br><br>
    <?= $person->getFirstName()." ".$person->getLastName()." (per_ID = ".$person->getId().") "._("is now part of the")." " ?>
    <?= $person->getLastName()." "._("Family")." (fam_ID = ".$fam->getId().")<br>" ?>
    *****************************************

        <?php
    if (!$bDoAll) {
        break;
    }
}
?>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-md-2">
                <a class="btn btn-primary" href="ConvertIndividualToFamily.php"><?= _('Convert Next') ?></a>
            </div>
            <div class="col-md-2">
                <a class="btn btn-success" href="ConvertIndividualToFamily.php?all=true"><?= _('Convert All') ?></a><br>
            </div>
        </div>
    </div>
</div>

<?php  require 'Include/Footer.php'; ?>
