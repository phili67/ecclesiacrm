<?php
/*******************************************************************************
 *
 *  filename    : CartToBadge.php
 *  last change : 2018-09-11
 *  description : form to invoke Sunday School reports
 *
 *  Copyright : Philippe Logel all rights reserved
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\utils\OutputUtils;
use EcclesiaCRM\utils\LabelUtils;
use EcclesiaCRM\Utils\MiscUtils;

$imgs = MiscUtils::getImagesInPath ('Images/background');


// Set the page title and include HTML header
$sPageTitle = gettext('Cart to Badges');
require 'Include/Header.php';

if (!($_SESSION['user']->isAdmin() || $_SESSION['bCreateDirectory'] )) {
   Redirect('Menu.php');
   exit;
}

if (isset($_GET['typeProblem'])) {
?>
<div class="alert alert-danger">
    <i class="fa fa-ban"></i>
    <?= gettext("Only PNG and JPEG are managed actually !!") ?>
</div>

<?php
}
?>

<div class="box">
      <div class="box-header with-border">
          <h3 class="box-title"><?= gettext('Generate Badges') ?></h3>
      </div>
      <form method="post" action="<?= SystemURLs::getRootPath() ?>/Reports/PDFBadge.php" name="labelform"  enctype="multipart/form-data">
      <div class="box-body">
          <div class="row">
            <div class="col-md-6">
              <?= gettext("Title") ?>
            </div>
            <div class="col-md-6">
               <input type="text" name="mainTitle" id="mainTitle" maxlength="255" size="3" value="<?= $_COOKIE['mainTitle'] ?>" class="form-control" placeholder="<?= gettext("Title") ?>">
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-6">
              <?= gettext("Second Title") ?>
            </div>
            <div class="col-md-6">
               <input type="text" name="secondTitle" id="secondTitle" maxlength="255" size="3" value="<?= $_COOKIE['secondTitle'] ?>" class="form-control" placeholder="<?= gettext("Second Title") ?>">
            </div>
          </div><br>
          <div class="row">
            <div class="col-md-6">
                <?= gettext("Third Title") ?>
            </div>
            <div class="col-md-6">
               <input type="text" name="thirdTitle" id="thirdTitle" maxlength="255" size="3" value="<?= $_COOKIE['thirdTitle'] ?>" class="form-control" placeholder="<?= gettext("Third Title") ?>">
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
                      $image = (empty($_COOKIE["image"]))?'scleft1.png':$_COOKIE["image"];
                  ?>
                  <input type="text" name="image" id="image" maxlength="255" size="3" value="<?= $image ?>" class="form-control" placeholder="<?= gettext("Sunday School Name") ?>">
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
              
                (<b><?= gettext("Pictures in the Image folder: ") ?></b>
                <?php
                  foreach ($imgs as $img) {
                    $name = str_replace("Images/background/","",$img);
                    echo  '<a href="#" class="add-file" data-name="'. $name .'">'.$name . '</a>  <a class="delete-file" data-name="'. $name .'"><i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></a>, ';
                  }
                  
                  if (count($imgs) == 0) {
                    echo gettext("None");
                  }
                ?>
                )
              
            </div>
          </div><br>

          <div class="row">
            <div class="col-md-6">
              <?= gettext("Upload") ?>
            </div>
            <div class="col-md-3">
              <input type="file" id="stickerBadgeInputFile" name="stickerBadgeInputFile" style="margin-top:3px">
            </div>
            <div class="col-md-3">
              <?= gettext("and")?> &nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-xs btn-success" name="SubmitUpload" value="<?= gettext("Upload") ?>">
            </div>
          </div><br>
          
          <div class="row">
            <div class="col-md-6">
                  <?= gettext("Image Position") ?>
            </div>
            <div class="col-md-6">
                   <select name="imagePosition" class="form-control input-sm">
                     <option value="Left" <?= ($_COOKIE["imagePosition"] == 'Left')?'selected':'' ?>><?= gettext('Left') ?></option>
                     <option value="Center" <?= ($_COOKIE["imagePosition"] == 'Center')?'selected':'' ?>><?= gettext('Center') ?></option>
                     <option value="Right" <?= ($_COOKIE["imagePosition"] == 'Right')?'selected':'' ?>><?= gettext('Right') ?></option>
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
        <input type="submit" class="btn btn-primary" value="<?= gettext('Generate Badges') ?>" name="Submit">
        </div>
      </div>
    </div>
  </form>
  <!-- /.box-body -->
</div>

<?php
require 'Include/Footer.php';
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.css" rel="stylesheet">

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var back = "<?= (empty($_COOKIE["sBackgroudColor"]))?'#F99':$_COOKIE["sBackgroudColor"] ?>";
    var title = "<?= (empty($_COOKIE["sTitleColor"]))?'#3A3':$_COOKIE["sTitleColor"] ?>";
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/BadgeSticker.js"></script>