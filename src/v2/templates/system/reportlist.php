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

$hasAnyReport = false;
?>

<div class="row">
    <?php
    if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
        $hasAnyReport = true;
    ?>
    <div class="col-lg-12">
      <div class="card card-primary card-outline">
        <div class="card-header border-1 d-flex align-items-center justify-content-between">
          <h3 class="card-title mb-0"><i class="fas fa-file-invoice-dollar mr-1"></i><?= _('Financial Reports') ?></h3>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item pl-0">
              <a class="font-weight-bold" href="<?= $sRootPath ?>/v2/deposit/financial/reports"><?= _('Deposit Reports') ?></a>
              <div class="small text-muted mt-1"><?= _('Access all financial reporting tools.') ?></div>
            </li>
          <?php
          if (SessionUser::getUser()->isAdmin()) {
            ?>
            <li class="list-group-item pl-0">
              <a class="font-weight-bold" href="<?= $sRootPath ?>/v2/people/canvass/automation"><?= _('Canvass Automation') ?></a>
              <div class="small text-muted mt-1"><?= _('Automated support for conducting an every-member canvass.') ?></div>
            </li>
              <?php
          } ?>
          </ul>
        </div>
      </div>
    </div>
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
      $hasAnyReport = true;
      ?>
    <div class="col-lg-12">
      <div class="card card-info card-outline">
        <div class="card-header border-1">
          <h3 class="card-title mb-0"><i class="fas fa-calendar-check mr-1"></i><?= _('Event Attendance Reports') ?></h3>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
          <?php
          // List all events
          foreach ($ormOpps as $ormOpp) {
            ?>
              <li class="list-group-item pl-0">
                <a class="font-weight-bold" href="<?= $sRootPath ?>/v2/system/event/attendance/List/<?= $ormOpp->getId() ?>/<?= $ormOpp->getName() ?>" title="List All <?= $ormOpp->getName() ?> Events"><?= $ormOpp->getName() ?></a>
                <div class="small text-muted mt-1"><?= _('Open attendance reports for this event type.') ?></div>
              </li>
            <?php
          } ?>
          </ul>
        </div>
      </div>
    </div>
    <?php
  }
}

if (!$hasAnyReport) {
    ?>
    <div class="col-lg-12">
      <div class="alert alert-info mb-0 d-flex align-items-start">
        <i class="fas fa-circle-info mt-1 mr-2"></i>
        <div>
          <strong><?= _('No reports available') ?></strong><br>
          <span><?= _('No report category is currently enabled for your account.') ?></span>
        </div>
      </div>
    </div>
    <?php
}
  ?>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
