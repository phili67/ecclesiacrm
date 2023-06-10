<?php

/*******************************************************************************
 *
 *  filename    : templates/carttobadge.php
 *  last change : 2023-06-09
 *  description : manage the badges
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use EcclesiaCRM\Utils\LabelUtils;
use EcclesiaCRM\Utils\MiscUtils;

$imgs = MiscUtils::getImagesInPath ('../Images/background');

require $sRootDocument . '/Include/Header.php';

if ($typeProblem) {
    ?>
    <div class="alert alert-danger">
        <i class="fas fa-ban"></i>
        <?= _("Only PNG and JPEG are managed actually !!") ?>
    </div>
    
    <?php
    }
    ?>
    
    <div class="card">
          <div class="card-header border-1">
              <h3 class="card-title"><?= _('Generate Badges') ?></h3>
          </div>
          <form method="post" action="<?= $sRootPath ?>/Reports/PDFBadge.php" name="labelform"  enctype="multipart/form-data">
          <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <?= _("Title") ?>
                </div>
                <div class="col-md-6">
                   <input type="text" name="mainTitle" id="mainTitle" maxlength="255" size="3" value="<?= $_COOKIE['mainTitle'] ?>" class= "form-control form-control-sm" placeholder="<?= _("Title") ?>">
                </div>
              </div><br>
              <div class="row">
                <div class="col-md-6">
                  <?= _("Second Title") ?>
                </div>
                <div class="col-md-6">
                   <input type="text" name="secondTitle" id="secondTitle" maxlength="255" size="3" value="<?= $_COOKIE['secondTitle'] ?>" class= "form-control form-control-sm" placeholder="<?= _("Second Title") ?>">
                </div>
              </div><br>
              <div class="row">
                <div class="col-md-6">
                    <?= _("Third Title") ?>
                </div>
                <div class="col-md-6">
                   <input type="text" name="thirdTitle" id="thirdTitle" maxlength="255" size="3" value="<?= $_COOKIE['thirdTitle'] ?>" class= "form-control form-control-sm" placeholder="<?= _("Third Title") ?>">
                </div>
              </div><br>
              <div class="row">
                <div class="col-md-6">
                   <?= _('Title color') ?>
                </div>
                <div class="col-md-6">
                      <div class="input-group my-colorpicker-global my-colorpicker-title colorpicker-element" data-id="38,44">
                        <input id="checkBox" type="hidden" name="title-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                        <span class="editCalendarName" data-id="38,44"><?= _('Chose your color') ?>:</span>
                        <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                           <i style="background-color: rgb(26, 43, 94);"></i>
                        </div>
                      </div>
                </div>
              </div><br>
              <div class="row">
                <div class="col-md-6">
                  <?= _('BackGround color') ?>
                </div>
                <div class="col-md-6">
                      <div class="input-group my-colorpicker-global my-colorpicker-back colorpicker-element" data-id="38,44">
                        <input id="checkBox" type="hidden" name="backgroud-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                        <span class="editCalendarName" data-id="38,44"><?= _('Chose your color') ?>:</span>
                        <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                           <i style="background-color: rgb(26, 43, 94);"></i>
                        </div>
                      </div>
                </div>
              </div><br>
              <div class="row">
                <div class="col-md-6">
                       <?= _("Image") ?>
                </div>
                <div class="col-md-6">
                      <?php
                          $image = (empty($_COOKIE["image"]))?'scleft1.png':$_COOKIE["image"];
                      ?>
                      <input type="text" name="image" id="image" maxlength="255" size="3" value="<?= $image ?>" class= "form-control form-control-sm" placeholder="<?= _("Sunday School Name") ?>">
                </div>
              </div>
    
              <div class="row">
                <div class="col-md-6">
                </div>
                <div class="col-md-6">
    
                    (<b><?= _("Pictures in the Image folder: ") ?></b>
                    <?php
                      foreach ($imgs as $img) {
                        $name = str_replace("../Images/background/","",$img);
                        echo  '<a href="#" class="add-file" data-name="'. $name .'">'.$name . '</a>  <a class="delete-file" data-name="'. $name .'"><i style="cursor:pointer; color:red;" class="icon far fa-trash-alt"></i></a>, ';
                      }
    
                      if (count($imgs) == 0) {
                        echo _("None");
                      }
                    ?>
                    )
    
                </div>
              </div><br>
    
              <div class="row">
                <div class="col-md-6">
                  <?= _("Upload") ?>
                </div>
                <div class="col-md-3">
                  <input type="file" id="stickerBadgeInputFile" name="stickerBadgeInputFile" style="margin-top:3px">
                </div>
                <div class="col-md-3">
                  <?= _("and")?> &nbsp;&nbsp;&nbsp;<input type="submit" class="btn btn-xs btn-success" name="SubmitUpload" value="<?= _("Upload") ?>">
                </div>
              </div><br>
    
              <div class="row">
                <div class="col-md-6">
                      <?= _("Image Position") ?>
                </div>
                <div class="col-md-6">
                       <select name="imagePosition" class="form-control form-control-sm">
                         <option value="Left" <?= ($_COOKIE["imagePosition"] == 'Left')?'selected':'' ?>><?= _('Left') ?></option>
                         <option value="Center" <?= ($_COOKIE["imagePosition"] == 'Center')?'selected':'' ?>><?= _('Center') ?></option>
                         <option value="Right" <?= ($_COOKIE["imagePosition"] == 'Right')?'selected':'' ?>><?= _('Right') ?></option>
                      </select>
                </div>
              </div><br>
                  <?php
                    LabelUtils::LabelSelect('labeltype',_('Badge Type'));
                    LabelUtils::FontSelect('labelfont');
                    LabelUtils::FontSizeSelect('labelfontsize','('._("default").' 24)');
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
      <!-- /.card-body -->
    </div>
    
    <?php require $sRootDocument . '/Include/Footer.php'; ?>
    
    <script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
    <link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">
    
    <script nonce="<?= $CSPNonce ?>">
        var back = "<?= (empty($_COOKIE["sBackgroudColor"]))?'#F99':$_COOKIE["sBackgroudColor"] ?>";
        var title = "<?= (empty($_COOKIE["sTitleColor"]))?'#3A3':$_COOKIE["sTitleColor"] ?>";
    </script>

    <script src="<?= $sRootPath ?>/skin/js/BadgeSticker.js"></script>


