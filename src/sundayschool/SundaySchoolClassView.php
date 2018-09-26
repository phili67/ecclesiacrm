<?php

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\Cart;

$sundaySchoolService = new SundaySchoolService();

$iGroupId = '-1';
$iGroupName = 'Unknown';
if (isset($_GET['groupId'])) {
    $iGroupId = InputUtils::LegacyFilterInput($_GET['groupId'], 'int');
}

$ormSundaySchoolClass = GroupQuery::Create()
                        ->findOneById ($iGroupId);

$iGroupName = $ormSundaySchoolClass->getName();

$birthDayMonthChartArray = [];
foreach ($sundaySchoolService->getKidsBirthdayMonth($iGroupId) as $birthDayMonth => $kidsCount) {
    array_push($birthDayMonthChartArray, "['".gettext($birthDayMonth)."', ".$kidsCount.' ]');
}
$birthDayMonthChartJSON = implode(',', $birthDayMonthChartArray);

$genderChartArray = [];
foreach ($sundaySchoolService->getKidsGender($iGroupId) as $gender => $kidsCount) {
    array_push($genderChartArray, "{label: '".gettext($gender)."', data: ".$kidsCount.'}');
}
$genderChartJSON = implode(',', $genderChartArray);

$rsTeachers = $sundaySchoolService->getClassByRole($iGroupId, 'Teacher');
$sPageTitle = gettext('Sunday School').': '.$iGroupName;

$TeachersEmails = [];
$KidsEmails = [];
$ParentsEmails = [];

$thisClassChildren = $sundaySchoolService->getKidsFullDetails($iGroupId);

foreach ($thisClassChildren as $child) {
    if ($child['dadEmail'] != '') {
        array_push($ParentsEmails, $child['dadEmail']);
    }
    if ($child['momEmail'] != '') {
        array_push($ParentsEmails, $child['momEmail']);
    }
    if ($child['kidEmail'] != '') {
        array_push($KidsEmails, $child['kidEmail']);
    }
}

foreach ($rsTeachers as $teacher) {
    array_push($TeachersEmails, $teacher['per_Email']);
}

require '../Include/Header.php';
?>

<?php  
  if ($_SESSION['user']->isAddRecords()) {
?>
  <div class="callout callout-info info"><?= gettext("To add students to this class, simply add them with the select field at the bottom of this page.") ?></div>
  <div class="callout callout-warning edition-mode" style="display: none;"><?= gettext("You're now in edition mode. To see the entire page again, click the button") ?>   <button type="button" class="btn btn-default exit-edition-mode" data-widget="collapse"><?= gettext("Exit") ?></button></div>
<?php
  }
?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Sunday School Class Functions') ?></h3>
  </div>
  <div class="box-body">
    <?php
    $allEmails = array_unique(array_merge($ParentsEmails, $KidsEmails, $TeachersEmails));
    $roleEmails->Parents = implode($sMailtoDelimiter, $ParentsEmails).',';
    $roleEmails->Teachers = implode($sMailtoDelimiter, $TeachersEmails).',';
    $roleEmails->Kids = implode($sMailtoDelimiter, $KidsEmails).',';
    $sEmailLink = implode($sMailtoDelimiter, $allEmails).',';
    // Add default email if default email has been set and is not already in string
    if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
        $sEmailLink .= $sMailtoDelimiter.SystemConfig::getValue('sToEmailAddress');
    }
    $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

    if ($bEmailMailto) { // Does user have permission to email groups
      // Display link
      ?>
      <div class="btn-group">
        <a class="btn btn-app" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>"><i
            class="fa fa-send-o"></i><?= gettext('Email') ?></a>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only"><?= gettext('Toggle Dropdown') ?></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <?php generateGroupRoleEmailDropdown($roleEmails, 'mailto:') ?>
        </ul>
      </div>

      <div class="btn-group">
        <a class="btn btn-app" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>"><i
            class="fa fa-send"></i><?= gettext('Email (BCC)') ?></a>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only"><?= gettext('Toggle Dropdown') ?></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <?php generateGroupRoleEmailDropdown($roleEmails, 'mailto:?bcc=') ?>
        </ul>
      </div>
      <?php
    }
    ?>
    <!-- <a class="btn btn-success" data-toggle="modal" data-target="#compose-modal"><i class="fa fa-pencil"></i> Compose Message</a>  This doesn't really work right now...
    <a class="btn btn-app" href="../GroupView.php?GroupID=<?= $iGroupId ?>"><i
        class="fa fa-user-plus"></i><?= gettext('Add Students') ?> </a>-->

  <a class="btn btn-app" href="../GroupEditor.php?GroupID=<?= $iGroupId?>"><i class="fa fa-pencil"></i><?= gettext("Edit this Class") ?></a>
  <?php 
  if ($_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled() || $_SESSION['user']->isSundayShoolTeacherForGroup($iGroupId)) {
  ?>
    <a class="btn btn-app bg-aqua makeCheckOut <?= (count($thisClassChildren) == 0)?"disabled":"" ?>" id="makeCheckOut" data-makecheckoutgroupid="<?= $iGroupId ?>" data-makecheckoutgroupname="<?= $iGroupName ?>"> <i class="fa fa-calendar-check-o"></i> <span class="cartActionDescription"><?= gettext('Make Check-out') ?></span></a>  
  <?php 
    }
  ?>
  <?php 
  if ($_SESSION['user']->isAdmin() || ($_SESSION['user']->isSundayShoolTeacherForGroup($iGroupId) && ($_SESSION['bExportSundaySchoolCSV'] || $_SESSION['bExportCSV'])) ) {
  ?>
    <a class="btn btn-app bg-green exportCheckOutCSV <?= (count($thisClassChildren) == 0)?"disabled":"" ?>"  data-makecheckoutgroupid="<?= $iGroupId ?>" > <i class="fa fa-file-excel-o"></i> <span class="cartActionDescription"><?= gettext("Export Attendance") ?></span></a>
  <?php
   }
   if ($_SESSION['user']->isAdmin() || ($_SESSION['user']->isSundayShoolTeacherForGroup($iGroupId) && $_SESSION['bExportSundaySchoolPDF']) ) {
  ?>  
    <a class="btn btn-app bg-red exportCheckOutPDF <?= (count($thisClassChildren) == 0)?"disabled":"" ?>"  data-makecheckoutgroupid="<?= $iGroupId ?>" > <i class="fa fa-file-pdf-o"></i> <span class="cartActionDescription"><?= gettext("Export Attendance") ?></span></a>
    
    <a class="btn btn-app bg-purple" id="studentbadge" data-groupid="<?= $iGroupId ?>" > <i class="fa fa-file-picture-o"></i> <span class="cartActionDescription"><?= gettext("Student Badges") ?></span></a>
  <?php 
    }
  ?>
  <?php
    if (Cart::StudentInCart($iGroupId) && $_SESSION['user']->isShowCartEnabled()){
  ?>
    <a class="btn btn-app RemoveStudentsFromGroupCart" id="AddStudentsToGroupCart" data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= gettext("Remove Students from Cart") ?></span></a>
  <?php 
    } else if ($_SESSION['user']->isShowCartEnabled()) {
   ?>
    <a class="btn btn-app AddStudentsToGroupCart <?= (count($thisClassChildren) == 0)?"disabled":"" ?>" id="AddStudentsToGroupCart" data-cartstudentgroupid="<?= $iGroupId ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= gettext("Add Students to Cart") ?></span></a>    
  <?php
    }
  ?>
  <?php
    if (Cart::TeacherInCart($iGroupId) && $_SESSION['user']->isShowCartEnabled()) {
  ?>
    <a class="btn btn-app RemoveFromTeacherGroupCart" id="AddToTeacherGroupCart" data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= gettext("Remove Teachers from Cart") ?></span></a>    
  <?php 
    } else if ($_SESSION['user']->isShowCartEnabled()) {
  ?>
    <a class="btn btn-app AddToTeacherGroupCart <?= (count($rsTeachers) == 0)?"disabled":"" ?>" id="AddToTeacherGroupCart" data-cartteachergroupid="<?= $iGroupId ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= gettext("Add Teachers to Cart") ?></span></a>
  <?php 
   }

  ?>
  </div>
</div>

<div class="box box-success teachers">
  <div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Teachers') ?></h3>

    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body row">
    <?php foreach ($rsTeachers as $teacher) {
        ?>
      <div class="col-sm-2">
        <!-- Begin user profile -->
        <div class="box box-info text-center user-profile-2">
          <div class="user-profile-inner">
            <h4 class="white"><?= $teacher['per_FirstName'].' '.$teacher['per_LastName'] ?></h4>
            <img src="<?= SystemURLs::getRootPath(); ?>/api/persons/<?= $teacher['per_ID'] ?>/thumbnail"
                  alt="User Image" class="user-image initials-image" width="85" height="85" />
            <a href="mailto:<?= $teacher['per_Email'] ?>" type="button" class="btn btn-primary btn-sm btn-block"><i
                class="fa fa-envelope"></i> <?= gettext('Send Message') ?></a>
            <a href="../PersonView.php?PersonID=<?= $teacher['per_ID'] ?>" type="button"
               class="btn btn-primary btn-info btn-block"><i class="fa fa-q"></i><?= gettext('View Profile') ?></a>
          </div>
        </div>
      </div>
    <?php
    } ?>
  </div>
</div>

<?php
   if ($_SESSION['user']->isSundayShoolTeacherForGroup($iGroupId)) {
?>

<div class="box box-info quick-status">
  <div class="box-header  with-border">
    <h3 class="box-title"><?= gettext('Quick Status') ?></h3>

    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body row">
    <div class="col-lg-8">
      <!-- Bar chart -->
      <div class="box box-primary">
        <div class="box-header">
          <i class="fa fa-bar-chart-o"></i>

          <h3 class="box-title"><?= gettext('Birthdays by Month') ?></h3>
        </div>
        <div class="box-body">
          <div class="disableSelection" id="bar-chart" style="width: 100%; height: 300px;"></div>
        </div>
        <!-- /.box-body-->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-lg-4">
      <!-- Donut chart -->
      <div class="box box-primary">
        <div class="box-header">
          <i class="fa fa-bar-chart-o"></i>

          <h3 class="box-title"><?= gettext('Gender') ?></h3>
        </div>
        <div class="box-body">
          <div id="donut-chart" style="width: 100%; height: 300px;"></div>
        </div>
        <!-- /.box-body-->
      </div>
      <!-- /.box -->
    </div>
  </div>
  <div class="box-body row">
    <div class="col-lg-12 text-center">
      <small><?= gettext("Click the chart or Donut parts to interact with the table below.") ?></small>
    </div>
  </div>
</div>

<?php 
  }
?>

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Students') ?></h3>
    <div style="float:right">
      <label><?= gettext("Edition Mode") ?> <input data-size="mini" id="editionMode" type="checkbox" data-toggle="toggle" data-on="<?= gettext("On") ?>" data-off="<?= gettext("Off") ?>">
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body table-responsive">
    <h4 class="birthday-filter" style="display:none;"><?= gettext('Showing students with birthdays in') ?> : <span class="month"></span> <i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></h4>
    <h4 class="gender-filter" style="display:none;"><?= gettext('Showing students with gender') ?> : <span class="type"></span> <i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></h4>
    <table id="sundayschoolTable" class="table table-striped table-bordered data-table" cellspacing="0" width="100%"> </table>
  </div>
</div>

<?php
function implodeUnique($array, $withQuotes)
{
    array_unique($array);
    asort($array);
    if (count($array) > 0) {
        if ($withQuotes) {
            $string = implode("','", $array);

            return "'".$string."'";
        } else {
            return implode(',', $array);
        }
    }

    return '';
}

?>

<!-- COMPOSE MESSAGE MODAL -->
<div class="modal fade" id="compose-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content large">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="fa fa-envelope-o"></i><?= gettext('Compose New Message') ?></h4>
      </div>
      <form action="SendEmail.php" method="post">
        <div class="modal-body">
          <div class="form-group">
            <label><?= gettext('Kids Emails') ?></label>
            <input name="email_to" class="form-control email-recepients-kids"
                   value="<?= implodeUnique($KidsEmails, false) ?>">
          </div>
          <div class="form-group">
            <label><?= gettext('Parents Emails') ?></label>
            <input name="email_to_2" class="form-control email-recepients-parents"
                   value="<?= implodeUnique($ParentsEmails, false) ?>">
          </div>
          <div class="form-group">
            <label><?= gettext('Teachers Emails') ?></label>
            <input name="email_cc" class="form-control email-recepients-teachers"
                   value="<?= implodeUnique($TeachersEmails, false) ?>">
          </div>
          <div class="form-group">
            <textarea name="message" id="email_message" class="form-control" placeholder="Message"
                      style="height: 120px;"></textarea>
          </div>
          <div class="form-group">
            <div class="btn btn-success btn-file">
              <i class="fa fa-paperclip"></i><?= gettext('Attachment') ?>
              <input type="file" name="attachment"/>
            </div>
            <p class="help-block"><?= gettext('Max. 32MB') ?></p>
          </div>

        </div>
        <div class="modal-footer clearfix">

          <button type="button" class="btn btn-danger" data-dismiss="modal"><i
              class="fa fa-times"></i><?= gettext('Discard') ?></button>

          <button type="submit" class="btn btn-primary pull-left"><i
              class="fa fa-envelope"></i><?= gettext('Send Message') ?></button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php  
  if ($_SESSION['user']->isAddRecords()) {
?>
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?php echo gettext("Add Members to Sunday Group"); ?>:</h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-1">
        <?= gettext("Add") ?>
      </div>
      <div class="col-md-3">
        <select class="form-control personSearch  select2" name="addGroupMember" style="width:100%"></select>
      </div>
    </div>
  </div>
</div>
<?php
  }
?>

<!-- FLOT CHARTS -->
<script  src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/flot/jquery.flot.min.js"></script>
<!-- FLOT RESIZE PLUGIN - allows the chart to redraw when the window is resized -->
<script  src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/flot/jquery.flot.resize.min.js"></script>
<!-- FLOT PIE PLUGIN - also used to draw donut charts -->
<script  src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/flot/jquery.flot.pie.min.js"></script>
<!-- FLOT CATEGORIES PLUGIN - Used to draw bar charts -->
<script  src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/flot/jquery.flot.categories.min.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  var birthDayMonthChartJSON = [<?= $birthDayMonthChartJSON ?>];
  var genderChartJSON        = [<?= $genderChartJSON ?>];
  var KidsEmails             = [<?= implodeUnique($KidsEmails, true) ?>];
  var TeachersEmails         = [<?= implodeUnique($TeachersEmails, true) ?>];
  var ParentsEmails          = [<?= implodeUnique($ParentsEmails, true) ?>];
  var birthDateColumnText    = '<?= gettext("Birth Date") ?>';
  var genderColumnText       = '<?= gettext("Gender") ?>';
  var sundayGroupId          = <?= $iGroupId ?>;
  var canSeePrivacyData      = <?= ($_SESSION['user']->isSeePrivacyDataEnabled() || $_SESSION['user']->isSundayShoolTeacherForGroup($iGroupId))?1:0 ?>;
  var canDeleteMembers       = <?= $_SESSION['user']->isDeleteRecordsEnabled()?1:0 ?>;
</script>

<script src="<?= SystemURLs::getRootPath(); ?>/skin/js/SundaySchoolClassView.js" ></script>

<?php
require '../Include/Footer.php';
?>


