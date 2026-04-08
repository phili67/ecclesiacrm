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

$mainTitle = $_COOKIE['mainTitle'] ?? '';
$secondTitle = $_COOKIE['secondTitle'] ?? '';
$thirdTitle = $_COOKIE['thirdTitle'] ?? '';
$image = $_COOKIE['image'] ?? 'scleft1.png';
$imagePosition = $_COOKIE['imagePosition'] ?? 'Left';
$titleColor = $_COOKIE['sTitleColor'] ?? '#3A3';
$backgroundColor = $_COOKIE['sBackgroudColor'] ?? '#F99';

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

    <div class="card card-primary card-outline">
      <div class="card-header border-1 d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-id-badge mr-1"></i><?= _('Generate Badges') ?></h3>
        <span class="badge badge-secondary mt-2 mt-sm-0"><?= _('Cart Printing') ?></span>
      </div>
      <form method="post" action="<?= $sRootPath ?>/Reports/PDFBadge.php" name="labelform" enctype="multipart/form-data">
        <div class="card-body">
          <div class="alert alert-info mb-3">
            <i class="fas fa-circle-info mr-1"></i>
            <?= _('Configure text, colors and image, then generate or upload badge assets.') ?>
          </div>

          <div class="card mb-3">
            <div class="card-body py-2">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <div class="small text-muted mb-1"><?= _('Design flow') ?></div>
                  <span class="badge badge-primary mr-1"><?= _('1. Content') ?></span>
                  <span class="badge badge-info mr-1"><?= _('2. Style') ?></span>
                  <span class="badge badge-warning mr-1"><?= _('3. Image') ?></span>
                  <span class="badge badge-success"><?= _('4. Export') ?></span>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                  <button type="button" id="resetBadgeDesigner" class="btn btn-outline-secondary btn-xs">
                    <i class="fas fa-undo mr-1"></i><?= _('Reset Designer') ?>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7">
              <div class="card mb-3">
            <div class="card-header py-2">
              <h4 class="card-title mb-0"><span class="badge badge-primary mr-1">1</span><i class="fas fa-heading mr-1"></i><?= _('Badge Titles') ?></h4>
            </div>
            <div class="card-body">
              <div class="form-group row">
                <label class="col-md-3 col-form-label" for="mainTitle"><?= _('Title') ?></label>
                <div class="col-md-9">
                  <input type="text" name="mainTitle" id="mainTitle" maxlength="255" value="<?= $mainTitle ?>" class="form-control form-control-sm" placeholder="<?= _('Title') ?>">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-md-3 col-form-label" for="secondTitle"><?= _('Second Title') ?></label>
                <div class="col-md-9">
                  <input type="text" name="secondTitle" id="secondTitle" maxlength="255" value="<?= $secondTitle ?>" class="form-control form-control-sm" placeholder="<?= _('Second Title') ?>">
                </div>
              </div>
              <div class="form-group row mb-0">
                <label class="col-md-3 col-form-label" for="thirdTitle"><?= _('Third Title') ?></label>
                <div class="col-md-9">
                  <input type="text" name="thirdTitle" id="thirdTitle" maxlength="255" value="<?= $thirdTitle ?>" class="form-control form-control-sm" placeholder="<?= _('Third Title') ?>">
                </div>
              </div>
              <div class="form-group row mt-2 mb-0">
                <label class="col-md-3 col-form-label"><?= _('Quick presets') ?></label>
                <div class="col-md-9">
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary preset-title" data-main="WELCOME" data-second="SUNDAY SERVICE" data-third="<?= _('Guest') ?>">
                      <?= _('Welcome') ?>
                    </button>
                    <button type="button" class="btn btn-outline-primary preset-title" data-main="VIP" data-second="CONFERENCE" data-third="2026">
                      <?= _('Event') ?>
                    </button>
                    <button type="button" class="btn btn-outline-primary preset-title" data-main="<?= _('Volunteer') ?>" data-second="<?= _('Team') ?>" data-third="<?= _('Staff') ?>">
                      <?= _('Volunteer') ?>
                    </button>
                  </div>
                </div>
              </div>
            </div>
              </div>

              <div class="card mb-3">
            <div class="card-header py-2">
              <h4 class="card-title mb-0"><span class="badge badge-info mr-1">2</span><i class="fas fa-palette mr-1"></i><?= _('Colors') ?></h4>
            </div>
            <div class="card-body">
              <div class="form-group row">
                <label class="col-md-3 col-form-label"><?= _('Title color') ?></label>
                <div class="col-md-9">
                  <div class="input-group my-colorpicker-global my-colorpicker-title colorpicker-element" data-id="38,44">
                    <input id="titleColorInput" type="text" name="title-color" class="form-control form-control-sm" value="<?= $titleColor ?>">
                    <div class="input-group-addon input-group-text">
                      <i style="background-color: <?= $titleColor ?>;"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group row mb-0">
                <label class="col-md-3 col-form-label"><?= _('Background color') ?></label>
                <div class="col-md-9">
                  <div class="input-group my-colorpicker-global my-colorpicker-back colorpicker-element" data-id="38,44">
                    <input id="backgroundColorInput" type="text" name="backgroud-color" class="form-control form-control-sm" value="<?= $backgroundColor ?>">
                    <div class="input-group-addon input-group-text">
                      <i style="background-color: <?= $backgroundColor ?>;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
              </div>

              <div class="card mb-3">
            <div class="card-header py-2">
              <h4 class="card-title mb-0"><span class="badge badge-warning mr-1">3</span><i class="fas fa-image mr-1"></i><?= _('Image') ?></h4>
            </div>
            <div class="card-body">
              <div class="form-group row">
                <label class="col-md-3 col-form-label" for="image"><?= _('Image') ?></label>
                <div class="col-md-9">
                  <input type="text" name="image" id="image" maxlength="255" value="<?= $image ?>" class="form-control form-control-sm" placeholder="<?= _('Image file name') ?>">
                  <small class="text-muted"><?= _('Click a file name below to auto-fill this field.') ?></small>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-md-3 col-form-label"><?= _('Available images') ?></label>
                <div class="col-md-9">
                  <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto;">
                    <?php
                    if (count($imgs) > 0) {
                      foreach ($imgs as $img) {
                        $name = str_replace('../Images/background/', '', $img);
                        echo '<div class="d-flex align-items-center justify-content-between py-1 border-bottom">';
                        echo '<a class="add-file" data-name="' . $name . '"><i class="fas fa-file-image mr-1"></i>' . $name . '</a>';
                        echo '<a class="delete-file text-danger" data-name="' . $name . '" title="' . _('Delete') . '"><i class="fa fa-times"></i></a>';
                        echo '</div>';
                      }
                    } else {
                      echo '<span class="text-muted">' . _('None') . '</span>';
                    }
                    ?>
                  </div>
                </div>
              </div>

              <div class="form-group row mb-0">
                <label class="col-md-3 col-form-label" for="stickerBadgeInputFile"><?= _('Upload') ?></label>
                <div class="col-md-6">
                  <input type="file" id="stickerBadgeInputFile" name="stickerBadgeInputFile" class="form-control-file">
                </div>
                <div class="col-md-3 mt-2 mt-md-0">
                  <button type="submit" class="btn btn-success btn-sm btn-block" name="SubmitUpload">
                    <i class="fas fa-upload mr-1"></i><?= _('Upload') ?>
                  </button>
                </div>
              </div>
            </div>
              </div>

              <div class="card mb-3 mb-lg-0">
            <div class="card-header py-2">
              <h4 class="card-title mb-0"><span class="badge badge-success mr-1">4</span><i class="fas fa-sliders-h mr-1"></i><?= _('Layout') ?></h4>
            </div>
            <div class="card-body">
              <div class="form-group row">
                <label class="col-md-3 col-form-label" for="imagePosition"><?= _('Image Position') ?></label>
                <div class="col-md-9">
                  <select name="imagePosition" id="imagePosition" class="form-control form-control-sm">
                    <option value="Left" <?= ($imagePosition == 'Left') ? 'selected' : '' ?>><?= _('Left') ?></option>
                    <option value="Center" <?= ($imagePosition == 'Center') ? 'selected' : '' ?>><?= _('Center') ?></option>
                    <option value="Right" <?= ($imagePosition == 'Right') ? 'selected' : '' ?>><?= _('Right') ?></option>
                    <option value="Cover" <?= ($imagePosition == 'Cover') ? 'selected' : '' ?>><?= _('Cover (Full)') ?></option>
                  </select>
                  <div class="btn-group btn-group-sm mt-2" role="group">
                    <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Left"><?= _('Left') ?></button>
                    <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Center"><?= _('Center') ?></button>
                    <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Right"><?= _('Right') ?></button>
                    <button type="button" class="btn btn-outline-secondary set-image-position" data-pos="Cover"><?= _('Cover') ?></button>
                  </div>
                </div>
              </div>
              <?php
              LabelUtils::LabelSelect('labeltype', _('Badge Type'));
              LabelUtils::FontSelect('labelfont');
              LabelUtils::FontSizeSelect('labelfontsize', '(' . _('default') . ' 24)');
              LabelUtils::StartRowStartColumn();
              ?>
            </div>
              </div>
            </div>

            <div class="col-lg-5 mt-3 mt-lg-0">
              <div id="preview" class="card card-outline card-info mb-0" style="position: sticky; top: 1rem;">
                <div class="card-header py-2">
                  <h3 class="card-title mb-0"><i class="fa-solid fa-display mr-1"></i><?= _('Preview') ?></h3>
                </div>
                <div class="card-body">
                  <img id="previewImage" class="img-fluid" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer d-flex flex-wrap justify-content-end align-items-center">
          <small class="text-muted mr-auto mb-2 mb-md-0">
            <i class="fas fa-print mr-1"></i><?= _('Generate a PDF using current cart members.') ?>
          </small>
          <button type="submit" class="btn btn-primary" name="Submit">
            <i class="fas fa-id-badge mr-1"></i><?= _('Generate Badges') ?>
          </button>
        </div>
      </form>
    </div>
    
    <?php require $sRootDocument . '/Include/Footer.php'; ?>
    
    <script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
    <link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">
    
    <script nonce="<?= $CSPNonce ?>">
        $(function() {
        window.CRM.rootPath = "<?= $sRootPath ?>";
        window.CRM.back = "<?= $backgroundColor ?>";
        window.CRM.title = "<?= $titleColor ?>";
        window.CRM.image = "<?= $image ?>";
        window.CRM.imagePosition = "<?= $imagePosition ?>";
        window.CRM.mainTitle = "<?= addslashes($mainTitle) ?>";
        window.CRM.secondTitle = "<?= addslashes($secondTitle) ?>";
        window.CRM.thirdTitle = "<?= addslashes($thirdTitle) ?>";
      window.CRM.labeltype = "<?= (empty($_COOKIE['labeltype'])) ? 'Tractor' : $_COOKIE['labeltype'] ?>";
      window.CRM.labelfont = "<?= (empty($_COOKIE['labelfont'])) ? 'Courier' : $_COOKIE['labelfont'] ?>";
      window.CRM.labelfontsize = "<?= (empty($_COOKIE['labelfontsize'])) ? '24' : $_COOKIE['labelfontsize'] ?>";

      $('.preset-title').on('click', function () {
        $('#mainTitle').val($(this).data('main'));
        $('#secondTitle').val($(this).data('second'));
        $('#thirdTitle').val($(this).data('third'));
        if (window.CRM && typeof window.CRM.reloadLabel === 'function') {
          window.CRM.reloadLabel();
        }
      });

      $('.set-image-position').on('click', function () {
        var pos = $(this).data('pos');
        $('#imagePosition').val(pos).trigger('change');
      });

      $('#resetBadgeDesigner').on('click', function () {
        $('#mainTitle').val('');
        $('#secondTitle').val('');
        $('#thirdTitle').val('');
        $('#imagePosition').val('Left').trigger('change');
        $('#titleColorInput').val('#3A3');
        $('#backgroundColorInput').val('#F99');
        window.CRM.title = '#3A3';
        window.CRM.back = '#F99';
        if (window.CRM && typeof window.CRM.reloadLabel === 'function') {
          window.CRM.reloadLabel();
        }
      });
        });
    </script>

    <script src="<?= $sRootPath ?>/skin/js/BadgeSticker.js"></script>


