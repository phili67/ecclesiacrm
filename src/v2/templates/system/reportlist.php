<?php

/*******************************************************************************
 *
 *  filename    : reportlist.php
 *  last change : 2023-06-10
 *  website     : http://www.ecclesiacrm.com
 *                © 2023 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\EventTypesTableMap;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use Propel\Runtime\ActiveQuery\Criteria;

require $sRootDocument . '/Include/Header.php';
?>

  <!-- ./col -->
  <?php
    if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) {
?>
<div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header border-1">
          <h3 class="card-title"><?= _('Financial Reports') ?></h3>
        </div>
        <div class="card-body">
          <p>
            <a class="MediumText" href="<?= $sRootPath ?>/v2/deposit/financial/reports">
          </p>
          <?php
          if (SessionUser::getUser()->isAdmin()) {
            ?>
              <p>
              <a class="MediumText" href="<?= $sRootPath ?>/v2/people/canvass/automation">
              <?= _('Canvass Automation') ?></a><br>
              <?= _('Automated support for conducting an every-member canvass.') ?>
              <?php
          } ?>
        </div>
      </div>
    </div><!-- ./col -->
    <?php
}

//Conditionally Display the Event Reports, only if there are actually events in the database.  Otherwise, Don't render the Event reports section.
if ( SystemConfig::getBooleanValue('bEnabledSundaySchool') ) {
  $ormOpps = EventTypesQuery::Create()
                  ->addJoin(EventTypesTableMap::COL_TYPE_ID, EventTableMap::COL_EVENT_TYPE,Criteria::RIGHT_JOIN)
                  ->setDistinct(EventTypesTableMap::COL_TYPE_ID)
                  ->orderById()
                  ->find();

  if (!empty($ormOpps) && $ormOpps->count() > 0) {
      ?>
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header border-1">
          <h3 class="card-title"><?= _('Event Attendance Reports') ?></h3>
        </div>
        <div class="card-body">
          <?php
          // List all events
          foreach ($ormOpps as $ormOpp) {
            ?>
              &nbsp;&nbsp;&nbsp;<a href="<?= $sRootPath ?>/v2/system/event/attendance/List/<?=$ormOpp->getId()?>/<?= $ormOpp->getName() ?>" title="List All <?=
            $ormOpp->getName() ?> Events"><strong><?= $ormOpp->getName()?></strong></a><br>
            <?php
          } ?>
        </div>
      </div>
    </div>
    <?php
  }
}
  ?>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
