<?php

/*******************************************************************************
 *
 *  filename    : templates/convertIndividualToAddress.php
 *  last change : 2023-06-21
 *  website     : http://www.ecclesiacrm.com
 *                © 2023 Philippe Logel
 *
 ******************************************************************************/

 use EcclesiaCRM\SessionUser;
 
 use EcclesiaCRM\Family;
 
 use EcclesiaCRM\FamilyQuery;
 use EcclesiaCRM\PersonQuery;
 
 use EcclesiaCRM\Map\FamilyTableMap;

require $sRootDocument . '/Include/Header.php';

if ($all == 'True') {
    $bDoAll = true;
}

$iUserID = SessionUser::getUser()->getPersonId();

// find the family ID so we can associate to person record
$lastEntry = FamilyQuery::create()
    ->addAsColumn('iFamilyID', 'MAX('.FamilyTableMap::COL_FAM_ID.')')
    ->findOne();

$iFamilyID = $lastEntry->getiFamilyID();
$name = $lastEntry->getName();
?>

<div class="card">
    <div class="card-header  border-1">
        <h3 class="card-title"><?= _("Family")." ID" ?></h3>
    </div>
    <div class="card-body">
        <?= _("Last entry ID") ?> : <?= $iFamilyID ?> (<?= $name ?>)
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
        <?php if ($ormList->count() > 0) { ?>
        <div class="row">
            <div class="col-md-2">
                <a class="btn btn-primary" href="<?= $sRootPath?>/v2/system/convert/individual/address"><?= _('Convert Next') ?></a>
            </div>
            <div class="col-md-2">
                <a class="btn btn-success" href="<?= $sRootPath?>/v2/system/convert/individual/address/True"><?= _('Convert All') ?></a><br>
            </div>
        </div>
        <?php } else { ?>
            <label><?= _("Nothing to convert !") ?></label>
        <?php } ?>
    </div>
</div>  

<?php require $sRootDocument . '/Include/Footer.php'; ?>
