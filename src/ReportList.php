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
use EcclesiaCRM\EventTypes;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\Map\EventTableMap;
use EcclesiaCRM\Map\EventTypesTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemConfig;

// Security
if ( !( $_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') || SystemConfig::getBooleanValue('bEnabledSundaySchool') ) ) {
    Redirect('Menu.php');
    exit;
}

//Set the page title
$sPageTitle = gettext('Report Menu');

$today = getdate();
$year = $today['year'];

require 'Include/Header.php';
?>
  <!-- ./col -->
<?php 
    if ($_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) {
?>
<div class="row">
    <div class="col-lg-12">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><?= gettext('Financial Reports') ?></h3>
        </div>
        <div class="box-body">
          <p>
            <a class="MediumText" href="FinancialReports.php">
          </p>
          <?php
          if ($_SESSION['user']->isAdmin()) {
              echo '<p>';
              echo '<a class="MediumText" href="CanvassAutomation.php">';
              echo gettext('Canvass Automation').'</a><br>';
              echo gettext('Automated support for conducting an every-member canvass.');
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
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><?= gettext('Event Attendance Reports') ?></h3>
        </div>
        <div class="box-body">
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
