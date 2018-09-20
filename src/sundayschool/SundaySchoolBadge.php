<?php
/*******************************************************************************
 *
 *  filename    : SundaySchoolReports.php
 *  last change : 2018-09-11
 *  description : form to invoke Sunday School reports
 *
 *  Copyright : Philippe Logel all rights reserved
 *
 ******************************************************************************/

// Include the function library
require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\GroupQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\utils\OutputUtils;
use EcclesiaCRM\utils\LabelUtils;
use EcclesiaCRM\PersonQuery;


if ( !($_SESSION['user']->isSundayShoolTeachForGroup() && $_SESSION['bExportSundaySchoolPDF']) ) {
    Redirect('Menu.php');
    exit;
}

$iGroupID = InputUtils::LegacyFilterInput($_GET['groupId'], 'int');
$useCart = InputUtils::LegacyFilterInput($_GET['cart'], 'int');

$group = GroupQuery::Create()->findOneById ($iGroupID);

// Get all the sunday school classes
$groups = GroupQuery::create()
                    ->orderByName(Criteria::ASC)
                    ->filterByType(4)
                    ->find();
                    
// Set the page title and include HTML header
$sPageTitle = gettext('Sunday School Badge for').' : '.$group->getName();
require '../Include/Header.php';

if (!($_SESSION['user']->isAdmin() || $_SESSION['bExportSundaySchoolPDF'] )) {
   Redirect('Menu.php');
   exit;
}

?>

<div class="callout callout-info"><?= gettext("When you add some properties to a person they will be add to the badge.") ?></div>

<?php
   if (count($_SESSION['aPeopleCart']) == 0) {
      $useCart = 0;
   }
      
   if ($useCart == 1) {
        $allPersons = "";
      
        foreach ($_SESSION['aPeopleCart'] as $personId) {
          $person = PersonQuery::Create()->findOneById ($personId);
          
          $allPersons .= $person->getFullName().",";
        }
?>
  <div class="callout callout-warning"><?= gettext("You're about to create babges only for this people")." : <b>".$allPersons."</b> ".gettext("who are in the cart. If you don't want to do this, empty the cart, and reload the page.") ?></div>
<?php
   }
?>


<div class="box">
      <div class="box-header with-border">
          <h3 class="box-title"><?= _('Generate Badges') ?></h3>
      </div>
      <form method="get" action="<?= SystemURLs::getRootPath() ?>/Reports/PDFBadgeSundaySchool.php" name="labelform">
      <input id="groupId" name="groupId" type="hidden" value="<?= $iGroupID?>">
      <input id="useCart" name="useCart" type="hidden" value="<?= $useCart?>">
      <div class="box-body">
          <div class="row">
            <div class="col-md-6">
                   <?= gettext("Sunday School Name") ?>
            </div>
            <div class="col-md-6">                  
               <input type="text" name="sundaySchoolName" id="sundaySchoolName" maxlength="255" size="3" value="<?= $_COOKIE['sundaySchoolNameSC'] ?>" class="form-control" placeholder="<?= gettext("Sunday School Name") ?>">
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-6">                  
                   <?= gettext('Title color') ?>
            </div>
            <div class="col-md-6">                  
                  <div class="input-group my-colorpicker-global my-colorpicker-title colorpicker-element" data-id="38,44">
                    <input id="checkBox" type="hidden" name="title-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                    <span class="editCalendarName" data-id="38,44"><?= gettext('Chose your color') ?>:</span>
                    <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                       <i style="background-color: rgb(26, 43, 94);"></i>
                    </div>
                  </div>
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-6">                  
                   <?= gettext('BackGround color') ?>
            </div>
            <div class="col-md-6">                  
                  <div class="input-group my-colorpicker-global my-colorpicker-back colorpicker-element" data-id="38,44">
                    <input id="checkBox" type="hidden" name="backgroud-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                    <span class="editCalendarName" data-id="38,44"><?= gettext('Chose your color') ?>:</span>
                    <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                       <i style="background-color: rgb(26, 43, 94);"></i>
                    </div>
                  </div>
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-6">                  
                   <?= gettext("Image") ?>
            </div>
            <div class="col-md-6">                  
                  <?php
                      $image = (empty($_COOKIE["imageSC"]))?'scleft1.png':$_COOKIE["imageSC"];
                  ?>
                  <input type="text" name="image" id="image" maxlength="255" size="3" value="<?= $image ?>" class="form-control" placeholder="<?= gettext("Sunday School Name") ?>">
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-6">                  
            </div>
            <div class="col-md-6">                  
                   <b>(<?= gettext("Add your images to the CRM Images folder. By default scleft1.png, scleft2.png and sccenter.jpg.") ?>)</b>
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-6">                  
                  <?= gettext("Image Position") ?>
            </div>
            <div class="col-md-6">                  
                   <select name="imagePosition" class="form-control input-sm">
                     <option value="Left" <?= ($_COOKIE["imagePositionSC"] == 'Left')?'selected':'' ?>><?= gettext('Left') ?></option>
                     <option value="Center" <?= ($_COOKIE["imagePositionSC"] == 'Center')?'selected':'' ?>><?= gettext('Center') ?></option>
                     <option value="Right" <?= ($_COOKIE["imagePositionSC"] == 'Right')?'selected':'' ?>><?= gettext('Right') ?></option>
                  </select>
            </div>
          </div><br>
                <?php
                LabelUtils::LabelSelect('labeltype',gettext('Badge Type'));
                LabelUtils::FontSelect('labelfont');
                LabelUtils::FontSizeSelect('labelfontsize','('.gettext("default").' 24)');
                LabelUtils::StartRowStartColumn();
                ?>
      <div class="row">
        <div class="col-md-5"></div>
        <div class="col-md-4">
        <input type="submit" class="btn btn-primary" value="<?= _('Generate Badges') ?>" name="Submit">
        </div>
      </div>
    </div>
  </form>
  <!-- /.box-body -->
</div>

<?php
require '../Include/Footer.php';
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.css" rel="stylesheet">

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var back = "<?= (empty($_COOKIE["sBackgroudColorSC"]))?'#F99':$_COOKIE["sBackgroudColorSC"] ?>";
    var title = "<?= (empty($_COOKIE["sTitleColorSC"]))?'#3A3':$_COOKIE["sTitleColorSC"] ?>";
    
    $(".my-colorpicker-back").colorpicker({
      color:back,
      inline:false,
      horizontal:true,
      right:true
    });
    
    $(".my-colorpicker-title").colorpicker({
      color:title,
      inline:false,
      horizontal:true,
      right:true
    });

</script>


