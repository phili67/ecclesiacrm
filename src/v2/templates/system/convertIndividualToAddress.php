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

<style>
.timeline {
  position: relative;
  margin: 0 0 2rem 0;
  padding: 0;
  list-style: none;
}
.timeline:before {
  content: '';
  position: absolute;
  left: 32px;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #e9ecef;
  border-radius: 2px;
}
.timeline-item {
  position: relative;
  margin-bottom: 2rem;
  padding-left: 70px;
}
.timeline-item:last-child { margin-bottom: 0; }
.timeline-badge {
  position: absolute;
  left: 16px;
  top: 0;
  width: 32px;
  height: 32px;
  background: #0d6efd;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  z-index: 1;
}
.timeline-content {
  background: #fff;
  border-radius: 0.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  padding: 1rem 1.5rem;
  border-left: 4px solid #0d6efd;
}
.timeline-success { border-left-color: #198754; }
.timeline-error { border-left-color: #dc3545; }
.timeline-badge-success { background: #198754; }
.timeline-badge-error { background: #dc3545; }
.timeline-summary {
  background: #f8f9fa;
  border-radius: 0.5rem;
  padding: 1rem 1.5rem;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}
.timeline-summary .badge {
  font-size: 0.7rem;
  padding: 0.7em 1.2em;
}
</style>


<div class="card card-primary card-outline shadow-sm mb-4">
    <div class="card-header border-0">
        <h3 class="card-title mb-0"><i class="fa-solid fa-people-roof"></i> <?= _("Family") . " ID" ?></h3>
    </div>
    <div class="card-body">
        <?php
        // Get list of people that are not assigned to a family
        $ormList = PersonQuery::create()
            ->filterByFamId(0)
            ->orderByLastName()
            ->orderByFirstName()
            ->find();
        $hasError = false;
        $converted = 0;
        $total = $ormList->count();
        ?>
        <div class="timeline-summary mb-4">
            <span class="badge bg-primary"><i class="fas fa-database me-1"></i> <?= _("Last entry ID") ?> : <b><?= $iFamilyID ?></b> (<?= $name ?>)</span>
            <span class="badge bg-info text-dark"><i class="fas fa-users me-1"></i> <?= _("To convert") ?> : <b><?= $total ?></b></span>
        </div>
        <ul class="timeline">
        <?php
        foreach ($ormList as $per) {
            if ($per->getId() == 1) continue; // in the case of the super Admin continue
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
            $badgeClass = 'timeline-badge-success';
            $contentClass = 'timeline-success';
            $icon = 'fa-check';
            $msg = _("Successfully converted:") . ' <b>' . $per->getFirstName() . ' ' . $per->getLastName() . '</b> (per_ID = ' . $per->getId() . ') ' . _("is now part of the") . ' <b>' . $per->getLastName() . '</b> ' . _("Family") . ' (fam_ID = ' . $fam->getId() . ')';
            if ($iNewFamilyID != $iFamilyID) {
                $hasError = true;
                $badgeClass = 'timeline-badge-error';
                $contentClass = 'timeline-error';
                $icon = 'fa-times';
                $msg = '<b>'._("Error with family ID").'</b>';
            }
            ?>
            <li class="timeline-item">
                <span class="timeline-badge <?= $badgeClass ?>"><i class="fas <?= $icon ?>"></i></span>
                <div class="timeline-content <?= $contentClass ?>">
                    <div class="fw-bold mb-1"><i class="fas fa-user me-1"></i><?= $per->getFirstName() . " " . $per->getLastName() ?> <span class="badge bg-light text-dark ms-2">per_ID = <?= $per->getId() ?></span></div>
                    <div><?= $msg ?></div>
                </div>
            </li>
            <?php
            $converted++;
            if ($hasError || !$bDoAll) {
                break;
            }
        }
        ?>
        </ul>
    </div>
    <div class="bg-light border-0 text-center">
        <?php if ($total > 0 && !$hasError) { ?>
        <div class="row justify-content-center my-3 g-3">
            <div class="col-auto">
                <a class="btn btn-lg btn-primary d-flex align-items-center gap-2 shadow-sm" href="<?= $sRootPath?>/v2/system/convert/individual/address">
                    <i class="fas fa-forward fa-lg"></i>
                    <span><?= _('Convert Next') ?></span>
                </a>
            </div>
            <div class="col-auto">
                <a class="btn btn-lg btn-success d-flex align-items-center gap-2 shadow-sm" href="<?= $sRootPath?>/v2/system/convert/individual/address/True">
                    <i class="fas fa-check fa-lg"></i>
                    <span><?= _('Convert All') ?></span>
                </a>
            </div>
        </div>
        <?php } elseif (!$hasError) { ?>
            <div class="alert alert-info my-3 py-3 rounded-pill d-inline-flex align-items-center gap-2 px-4 shadow-sm" style="font-size:1.1em;">
                <span class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:2em;height:2em;"><i class="fas fa-info-circle text-info"></i></span>
                <span><?= _( "Nothing to convert !") ?></span>
            </div>
        <?php } ?>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
