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
require 'Include/LabelFunctions.php';

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\utils\OutputUtils;

// Set the page title and include HTML header
$sPageTitle = gettext('Cart to Badges');
require 'Include/Header.php';

if (!($_SESSION['user']->isAdmin() || $_SESSION['bCreateDirectory'] )) {
   Redirect('Menu.php');
   exit;
}

?>

<div class="box">
      <div class="box-header with-border">
          <h3 class="box-title"><?= _('Generate Badges') ?></h3>
      </div>
      <form method="get" action="<?= SystemURLs::getRootPath() ?>/Reports/PDFBadge.php" name="labelform">
      <div class="box-body">
          <table class="table table-hover dt-responsive" id="cart-label-table" width="100%">
            <thead>
          <tr>
              <th></th>
              <th></th>
          </tr>
          </thead>
          <tbody>
             <tr>
                <td>
                   <?= gettext("Title") ?>
                </td>
                <td>
                  <input type="text" name="mainTitle" id="mainTitle" maxlength="255" size="3" value="<?= $_COOKIE['mainTitle'] ?>" class="form-control" placeholder="<?= gettext("Title") ?>">
                </td>
             </tr>
             <tr>
                <td>
                   <?= gettext("Second Title") ?>
                </td>
                <td>
                  <input type="text" name="secondTitle" id="secondTitle" maxlength="255" size="3" value="<?= $_COOKIE['secondTitle'] ?>" class="form-control" placeholder="<?= gettext("Second Title") ?>">
                </td>
             </tr>
             <tr>
                <td>
                   <?= gettext("Third Title") ?>
                </td>
                <td>
                  <input type="text" name="thirdTitle" id="thirdTitle" maxlength="255" size="3" value="<?= $_COOKIE['thirdTitle'] ?>" class="form-control" placeholder="<?= gettext("Third Title") ?>">
                </td>
             </tr>
             <tr>
                <td>
                   <?= gettext('Title color') ?>
                </td>
                <td>
                  <div class="input-group my-colorpicker-global my-colorpicker-title colorpicker-element" data-id="38,44">
                    <input id="checkBox" type="hidden" name="title-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                    <span class="editCalendarName" data-id="38,44"><?= gettext('Chose your color') ?>:</span>
                    <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                       <i style="background-color: rgb(26, 43, 94);"></i>
                    </div>
                  </div>
                </td>
              </tr>
             <tr>
                <td>
                   <?= gettext('BackGround color') ?>
                </td>
                <td>
                  <div class="input-group my-colorpicker-global my-colorpicker-back colorpicker-element" data-id="38,44">
                    <input id="checkBox" type="hidden" name="backgroud-color" class="check-calendar" data-id="38,44" checked="" value="#1a2b5e">&nbsp;
                    <span class="editCalendarName" data-id="38,44"><?= gettext('Chose your color') ?>:</span>
                    <div class="input-group-addon" style="border-left: 1px;background-color:lightgray">
                       <i style="background-color: rgb(26, 43, 94);"></i>
                    </div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                   <?= gettext("Image") ?>
                </td>
                <td>
                  <?php
                      $image = (empty($_COOKIE["image"]))?'scleft1.png':$_COOKIE["image"];
                  ?>
                  <input type="text" name="image" id="image" maxlength="255" size="3" value="<?= $image ?>" class="form-control" placeholder="<?= gettext("Sunday School Name") ?>">
                </td>
             </tr>
             <tr>
                <td>
                </td>
                <td>
                   <b>(<?= gettext("Add your images to the CRM Images folder. By default scleft1.png, scleft2.png and sccenter.jpg.") ?>)</b>
                </td>
             </tr>
             <tr>
                <td>
                  <?= gettext("Image Position") ?>
                </td>
                <td>
                   <select name="imagePosition" class="form-control input-sm">
                     <option value="Left" <?= ($_COOKIE["imagePosition"] == 'Left')?'selected':'' ?>><?= gettext('Left') ?></option>
                     <option value="Center" <?= ($_COOKIE["imagePosition"] == 'Center')?'selected':'' ?>><?= gettext('Center') ?></option>
                     <option value="Right" <?= ($_COOKIE["imagePosition"] == 'Right')?'selected':'' ?>><?= gettext('Right') ?></option>
                  </select>
                </td>
             </tr>
                <?php
                LabelSelect('labeltype',gettext('Badge Type'));
                FontSelect('labelfont');
                FontSizeSelect('labelfontsize','('.gettext("default").' 24)');
                StartRowStartColumn();
                ?>
            </tbody>
          </table>
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
require 'Include/Footer.php';
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
<link href="<?= SystemURLs::getRootPath() ?>/skin/adminlte/plugins/colorpicker/bootstrap-colorpicker.css" rel="stylesheet">

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var back = "<?= (empty($_COOKIE["sBackgroudColor"]))?'#F99':$_COOKIE["sBackgroudColor"] ?>";
    var title = "<?= (empty($_COOKIE["sTitleColor"]))?'#3A3':$_COOKIE["sTitleColor"] ?>";
    
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


