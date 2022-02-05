<?php
/*******************************************************************************
 *
 *  filename    : ReportList.php
 *  last change : 2003-03-20
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt
  *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';


use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\EventTypesTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


// Security
if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') || SystemConfig::getBooleanValue('bEnabledSundaySchool') ) ) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

//Set the page title
$sPageTitle = _('Report Menu');

$today = getdate();
$year = $today['year'];

require 'Include/Header.php';
?>
  <!-- ./col -->
<?php
    if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) {
?>
<div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header border-0">
          <h3 class="card-title"><?= _('Financial Reports') ?></h3>
        </div>
        <div class="card-body">
          <p>
            <a class="MediumText" href="FinancialReports.php">
          </p>
          <?php
          if (SessionUser::getUser()->isAdmin()) {
              echo '<p>';
              echo '<a class="MediumText" href="CanvassAutomation.php">';
              echo _('Canvass Automation').'</a><br>';
              echo _('Automated support for conducting an every-member canvass.');
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
        <div class="card-header border-0">
          <h3 class="card-title"><?= _('Event Attendance Reports') ?></h3>
        </div>
        <div class="card-body">
          <?php
          // List all events
          foreach ($ormOpps as $ormOpp) {
              echo '&nbsp;&nbsp;&nbsp;<a href="EventAttendance.php?Action=List&Event='.
            $ormOpp->getId().'&Type='.$ormOpp->getName().'" title="List All '.
            $ormOpp->getName().' Events"><strong>'.$ormOpp->getName().
            '</strong></a>'."<br>\n";
          } ?>
        </div>
      </div>
    </div>
    <?php
  }
}
  ?>
</div>


<?php require 'Include/Footer.php' ?>
