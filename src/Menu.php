<?php
/*******************************************************************************
*
*  filename    : Menu.php
*  description : menu that appears after login, shows login attempts
*
*  http://www.ecclesiacrm.com/
*  Copyright 2001-2002 Phillip Hullquist, Deane Barker, Michael Wilt
*
*  Additional Contributors:
*  2006 Ed Davis
*  2017 Philippe Logel
*

******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\Service\DashboardService;
use EcclesiaCRM\Service\FinancialService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\Map\PastoralCareTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

// we place this part to avoid a problem during the upgrade process
// Set the page title
$sPageTitle = gettext('Welcome to').' '. ChurchMetaData::getChurchName();

require 'Include/Header.php';

$financialService = new FinancialService();
$dashboardService = new DashboardService();
//Last edited active families
$updatedFamilies = $dashboardService->getUpdatedFamilies(10);
//Newly added active families
$latestFamilies = $dashboardService->getLatestFamilies(10);
//last Edited members from Active families
$updatedMembers = $dashboardService->getUpdatedMembers(12);
//Newly added members from Active families
$latestMembers = $dashboardService->getLatestMembers(12);

if (!($_SESSION['user']->isFinanceEnabled() || $_SESSION['user']->isMainDashboardEnabled() || $_SESSION['user']->isPastoralCareEnabled())) {
   Redirect('PersonView.php?PersonID='.$_SESSION['user']->getPersonId());
   exit;
}

$depositData = false;  //Determine whether or not we should display the deposit line graph
if ($_SESSION['user']->isFinanceEnabled()) {
    $deposits = DepositQuery::create()->filterByDate(['min' =>date('Y-m-d', strtotime('-90 days'))])->find();
    if (count($deposits) > 0) {
        $depositData = $deposits->toJSON();
    }
}

$showBanner = SystemConfig::getValue("bEventsOnDashboardPresence");

$peopleWithBirthDays = MenuEventsCount::getBirthDates();
$Anniversaries = MenuEventsCount::getAnniversaries();
$peopleWithBirthDaysCount = MenuEventsCount::getNumberBirthDates();
$AnniversariesCount = MenuEventsCount::getNumberAnniversaries();


if ($_SESSION['user']->isGdrpDpoEnabled() && SystemConfig::getValue('bGDPR')) {
  // when a person is completely deactivated
  $time = new DateTime('now');
  $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');
 
  $families = FamilyQuery::create()
        ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP
        ->orderByName()
        ->find();

  $persons = PersonQuery::create()
          ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP
          ->_or() // or : this part is unusefull, it's only for debugging
          ->useFamilyQuery()
            ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// RGPD, when a Family is completely deactivated
          ->endUse()
          ->orderByLastName()
          ->find();
              
  if ($persons->count()+$families->count() > 0) {
?>
  <div class="alert alert-gpdr alert-dismissible " id="Menu_RGPD">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
       <h4 class="alert-heading"><?= gettext("GDPR") ?>  (<?= gettext("message for the DPO") ?>)</h4>
       <div class="row">
           <div class="col-sm-1">
           </div>
           <div class="col-sm-5">
            <?php
               if ($persons->count()) {
            ?>
             <?php
                if ( $persons->count() == 1 ) {
             ?>
                <?= $persons->count()." ".gettext("person must be deleted from the CRM.") ?>
            <?php } else { ?>
                <?= $persons->count()." ".gettext("persons must be deleted from the CRM.") ?>
            <?php
                }
            ?>
              <br>
                <b><?= gettext("Click the") ?> <a href="<?= SystemURLs::getRootPath() ?>/PersonList.php?mode=GDRP"><?= gettext("link") ?></a> <?= gettext("to solve the problem.") ?></b>
            <?php
             } else {
            ?>
                <?= gettext("No Person to remove in the CRM.") ?>
            <?php
             }
            ?>
        </div>
        <div class="col-sm-5">
            <?php
               if ($families->count()) {
            ?>
           <?php
                if ( $families->count() == 1 ) {
             ?>
                <?= $families->count()." ".gettext("family must be deleted from the CRM.") ?>
            <?php } else { ?>
                <?= $families->count()." ".gettext("families must be deleted from the CRM.") ?>
            <?php
                }
            ?>
              <br>
                <b><?= gettext("Click the") ?> <a href="<?= SystemURLs::getRootPath() ?>/FamilyList.php?mode=GDRP"><?= gettext("link") ?></a> <?= gettext("to solve the problem.") ?></b>
            <?php
             } else {
            ?>
                <?= gettext("No Family to remove in the CRM.") ?>
            <?php
             }
            ?>
        </div>
        <div class="col-sm-1">
       </div>
    </div>
  </div>
<?php    
  }  
}

if ($showBanner && ($peopleWithBirthDaysCount > 0 || $AnniversariesCount > 0) && $_SESSION['user']->isSeePrivacyDataEnabled()) {
?>
    <div class="alert alert-birthday alert-dismissible " id="Menu_Banner">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

    <?php
        $new_unclassified_row = false;
        $cout_unclassified_people = 0;
        $unclassified = "";
        
        $new_row = false;
        $count_people = 0;
        $classified = "";
        
        $new_row = false;
        $count_people = 0;
        
        foreach ($peopleWithBirthDays as $peopleWithBirthDay) {
          if ($peopleWithBirthDay->getOnlyVisiblePersonView()) {
            if ($new_unclassified_row == false) {
                $unclassified .= '<div class="row">';
                $new_unclassified_row = true;
                $unclassified .= '<div class="col-sm-3">';
                $unclassified .= '<label class="checkbox-inline">';
                
                if ($peopleWithBirthDay->getUrlIcon() != '') { 
                    $unclassified .= '<img src="'.SystemURLs::getRootPath()."/skin/icons/markers/".$peopleWithBirthDay->getUrlIcon().'">';
                }
                
                $unclassified .= '<a href="'.$peopleWithBirthDay->getViewURI().'" class="btn btn-link-menu" style="text-decoration: none">'.$peopleWithBirthDay->getFullNameWithAge().'</a>';
                
                $unclassified .= '</label>';
                $unclassified .= '</div>';

                $cout_unclassified_people+=1;
                $cout_unclassified_people%=4;
                if ($cout_unclassified_people == 0) {
                    $unclassified .= '</div>';
                    $new_unclassified_row = false;
                }
            }

            if ($new_unclassified_row == true) {
                $unclassified .= '</div>';
            }
            continue;
          }
          
          // we now work with the classified date
          if ($new_row == false) {
                $classified .= '<div class="row">';
                $new_row = true;
          }
          
          $classified .= '<div class="col-sm-3">';
          $classified .= '<label class="checkbox-inline">';
          
          if ($peopleWithBirthDay->getUrlIcon() != '') { 
              $classified .= '<img src="'.SystemURLs::getRootPath().'/skin/icons/markers/'.$peopleWithBirthDay->getUrlIcon().'">';
          }
          $classified .= '<a href="'.$peopleWithBirthDay->getViewURI().'" class="btn btn-link-menu" style="text-decoration: none">'.$peopleWithBirthDay->getFullNameWithAge().'</a>';
          $classified .= '</label>';
          $classified .= '</div>';

          $count_people+=1;
          $count_people%=4;
          if ($count_people == 0) {
              $classified .= '</div>';
              $new_row = false;
          }
        }

        if ($new_row == true) {
            $classified .= '</div>';
        }

      if (!empty($classified)) {
     ?>
        <h4 class="alert-heading"><?= gettext("Birthdates of the day") ?></h4>
        <div class="row">
          <?php
             echo $classified;
          ?>
        </div>
    <?php
    } ?>

    <?php if ($AnniversariesCount > 0) {
        if ($peopleWithBirthDaysCount > 0) {
            ?>
            <hr style="background-color: green; height: 1px; border: 0;">
    <?php
        } ?>

        <h4 class="alert-heading"><?= gettext("Anniversaries of the day")?></h4>
        <div class="row">

    <?php
        $new_row = false;
        $count_people = 0;

        foreach ($Anniversaries as $Anniversary) {
            if ($new_row == false) {
                ?>
                <div class="row">

                <?php $new_row = true;
            } ?>
            <div class="col-sm-3">
            <label class="checkbox-inline">
              <a href="<?= $Anniversary->getViewURI() ?>" class="btn btn-link-menu" style="text-decoration: none"><?= $Anniversary->getFamilyString() ?></a>
            </label>
            </div>

            <?php
            $count_people+=1;
            $count_people%=4;
            if ($count_people == 0) {
                ?>
                </div>
            <?php
                $new_row = false;
            }
        }

        if ($new_row == true) {
            ?>
            </div>
        <?php
        } ?>

        </div>
    <?php
    } ?>
    
     <?php if ($unclassified) {
        if ($peopleWithBirthDaysCount > 0) {
            ?>
            <hr  style="background-color: green; height: 1px; border: 0;">
          <?php
              } ?>

              <h4 class="alert-heading"><?= gettext("Unclassified birthdates")?></h4>
              <div class="row">

              <?php
                 echo $unclassified;
              ?>

              </div>
          <?php
    } ?>
  </div>

<?php
}

// The person can see the pastoral care
if ($_SESSION['user']->isPastoralCareEnabled()) {
  $cares = PastoralCareQuery::Create()
                     ->leftJoinPastoralCareType()
                     ->joinPersonRelatedByPersonId()
                     ->groupBy(PastoralCareTableMap::COL_PST_CR_PERSON_ID)
                     ->orderByDate(Criteria::DESC)
                     ->limit(SystemConfig::getValue("bSearchIncludePastoralCareMax"))
                     ->findByPastorId($_SESSION['user']->getPerson()->getId());
  
  if ($cares->count() > 0) {    
  ?>
    <div class="alert alert-pastoral-care alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <h4 class="alert-heading"><?= gettext("Pastoral Care")?></h4>
          <?php
            $count_care = 0;
            $new_row = false;
          
            foreach ($cares as $care) {
              if ($new_row == false) {
                  ?>
                  <div class="row">

                  <?php $new_row = true;
              } ?>
            
              <div class="col-sm-3">
                <label class="checkbox-inline">
                  <a href="<?= SystemURLs::getRootPath() . "/PastoralCare.php?PersonID=".$care->getPersonId() ?>" class="btn btn-link-menu" style="text-decoration: none;"><?= $care->getPersonRelatedByPersonId()->getFullName() ?> (<?= $care->getDate()->format(SystemConfig::getValue('sDateFormatLong'))?>)</a>
                </label>
              </div>

              <?php
              $count_care+=1;
              $count_care%=4;
              if ($count_care == 0) {
                  ?>
                  </div>
              <?php
                  $new_row = false;
              }
          ?>
          <?php
            }
          
            if ($new_row == true) {
              ?>
              </div>
          <?php
          } 
          ?>
    </div>

  <?php
  }
}

?>


<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3 id="familyCountDashboard">
                    0
                </h3>
                <p>
                    <?= gettext('Families') ?>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="<?= SystemURLs::getRootPath() ?>/FamilyList.php" class="small-box-footer">
                <?= gettext('See all Families') ?> <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="peopleStatsDashboard">
                    0
                </h3>
                <p>
                    <?= gettext('People') ?>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-user"></i>
            </div>
            <a href="<?= SystemURLs::getRootPath() ?>/SelectList.php?mode=person" class="small-box-footer">
                <?= gettext('See All People') ?> <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3 id="groupStatsSundaySchool">
                   0
                </h3>
                <p>
                    <?= gettext('Sunday School Classes') ?>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-child"></i>
            </div>
            <a href="<?= SystemURLs::getRootPath() ?>/sundayschool/SundaySchoolDashboard.php" class="small-box-footer">
                <?= gettext('More info') ?> <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-maroon">
            <div class="inner">
                <h3 id="groupsCountDashboard">
                  0
                </h3>
                <p>
                    <?= gettext('Groups') ?>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-gg"></i>
            </div>
            <a href="<?= SystemURLs::getRootPath() ?>/GroupList.php" class="small-box-footer">
                <?= gettext('More info') ?>  <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <?php
      $countAttend = EcclesiaCRM\Base\EventAttendQuery::create()
                    ->filterByCheckoutId(null, \Propel\Runtime\ActiveQuery\Criteria::EQUAL)
                    ->find()
                    ->count();
                    
      if ($countAttend > 0){
    ?>
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-light-blue">
            <div class="inner">
                <h3>
                  <?= $countAttend ?>
                </h3>
                <p>
                    <?= gettext('Attendees Checked In') ?>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-gg"></i>
            </div>
            <a href="<?= SystemURLs::getRootPath() ?>/ListEvents.php" class="small-box-footer">
                <?= gettext('More info') ?>  <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <?php 
    }
    ?>
</div><!-- /.row -->

<?php
if ($depositData && SystemConfig::getBooleanValue('bEnabledFinance')) { // If the user has Finance permissions, then let's display the deposit line chart
?>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="box box-info">
            <div class="box-header">
                <i class="fa fa-money"></i>
                <h3 class="box-title"><?= gettext('Deposit Tracking') ?></h3>
                <div class="box-tools pull-right">
                    <div id="deposit-graph" class="chart-legend"></div>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
                <canvas id="deposit-lineGraph" style="height:125px; width:100%"></canvas>
            </div>
            </div>
    </div>
</div>
<?php
                  }  //END IF block for Finance permissions to include HTML for Deposit Chart
?>

<div class="row">
    <div class="col-lg-6">
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user-plus"></i>
                <h3 class="box-title"><?= gettext('Latest Families') ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                    </button>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body clearfix">
                <div class="table-responsive" style="overflow:hidden">
                    <table class="dataTable table table-striped table-condensed" id="latestFamiliesDashboardItem">
                        <thead>
                        <tr>
                            <th data-field="name"><?= gettext('Family Name') ?></th>
                            <th data-field="address"><?= gettext('Address') ?></th>
                            <th data-field="city"><?= gettext('Created') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-check"></i>
                <h3 class="box-title"><?= gettext('Updated Families') ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                    </button>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body clearfix">
                <div class="table-responsive" style="overflow:hidden">
                    <table class=" dataTable table table-striped table-condensed" id="updatedFamiliesDashboardItem">
                        <thead>
                        <tr>
                            <th data-field="name"><?= gettext('Family Name') ?></th>
                            <th data-field="address"><?= gettext('Address') ?></th>
                            <th data-field="city"><?= gettext('Updated') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="box box-solid">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= gettext('Latest Members') ?></h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <ul class="users-list clearfix">
                        <?php foreach ($latestMembers as $person) {
    ?>
                            <li>
                                <a class="users-list" href="PersonView.php?PersonID=<?= $person->getId() ?>">
                                    <img src="<?= SystemURLs::getRootPath(); ?>/api/persons/<?= $person->getId() ?>/thumbnail"
                                         alt="<?= $person->getFullName() ?>" class="user-image initials-image"
                                         width="85" height="85"/><br/>
                                    <?= $person->getFullName() ?></a>
                                <span class="users-list-date"><?= date_format($person->getDateEntered(), SystemConfig::getValue('sDateFormatLong')); ?>&nbsp;</span>
                            </li>
                            <?php
}
                        ?>
                    </ul>
                    <!-- /.users-list -->
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="box box-solid">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= gettext('Updated Members') ?></h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <ul class="users-list clearfix">
                        <?php foreach ($updatedMembers as $person) {
                            ?>
                            <li>
                                <a class="users-list" href="PersonView.php?PersonID=<?= $person->getId() ?>">
                                    <img src="<?= SystemURLs::getRootPath(); ?>/api/persons/<?= $person->getId() ?>/thumbnail"
                                         alt="<?= $person->getFullName() ?>" class="user-image initials-image"
                                         width="85" height="85"/><br/>
                                    <?= $person->getFullName() ?></a>
                                <span
                                    class="users-list-date"><?= date_format($person->getDateLastEdited(), SystemConfig::getValue('sDateFormatLong')); ?>&nbsp;</span>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <!-- /.users-list -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- this page specific inline scripts -->
<script nonce="<?= SystemURLs::getCSPNonce() ?>">

<?php
  if (SystemConfig::getBooleanValue('bEnabledFinance')) {
    if ($depositData) { // If the user has Finance permissions, then let's display the deposit line chart
?>
    //---------------
    //- LINE CHART  -
    //---------------
    var lineDataRaw = <?= $depositData ?>;

    var lineData = {
        labels: [],
        datasets: [
            {
                data: []
            }
        ]
    };


  $( document ).ready(function() {
    $.each(lineDataRaw.Deposits, function(i, val) {
        lineData.labels.push(moment(val.Date).format(window.CRM.datePickerformat.toUpperCase()));
        lineData.datasets[0].data.push(val.totalAmount);
    });
    options = {
      responsive:true,
      maintainAspectRatio:false
    };
    var lineChartCanvas = $("#deposit-lineGraph").get(0).getContext("2d");
    var lineChart = new Chart(lineChartCanvas).Line(lineData,options);

  });
<?php
    }  //END IF block for Finance permissions to include JS for Deposit Chart
  } // END of bEnabledFinance
?>
</script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  var timeOut = <?= SystemConfig::getValue("iEventsOnDashboardPresenceTimeOut")*1000 ?>;

  $(document).ready (function(){
    $("#myWish").click(function showAlert() {
        $("#Menu_Banner").alert();
        window.setTimeout(function () {
            $("#Menu_Banner").alert('close'); }, timeOut);
       });
    });

    $("#Menu_Banner").fadeTo(timeOut, 500).slideUp(500, function(){
    $("#Menu_Banner").slideUp(500);
});
</script>


<?php
require 'Include/Footer.php';
?>