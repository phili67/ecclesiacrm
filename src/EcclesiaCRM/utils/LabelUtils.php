<?php

/* Copyright : Philippe Logel */

namespace EcclesiaCRM\Utils;

class LabelUtils {

    public static function FontSelect($fieldname)
    {
        $sFPDF_PATH = __DIR__.'/../../vendor/tecnickcom/tcpdf';

        $d = scandir($sFPDF_PATH.'/fonts/', SCANDIR_SORT_DESCENDING);

        $fontnames = [];
        $family = ' ';
        foreach ($d as $entry) {
            $len = strlen($entry);
            if ($len > 3) {
                $ext = pathinfo($entry, PATHINFO_EXTENSION);

                if ($ext != "php") continue;

                $r = file_get_contents ($sFPDF_PATH.'/fonts/'.$entry);
                $res = explode('$name=\'', $r);

                if (count($res) == 1) continue;

                $font = explode ("';",$res[1]);

                $font = $font[0];

                if ($font == 'AlArabiya'
                    or $font == 'FreeSansBold' or $font == 'FreeSansBoldOblique' or $font == 'FreeSansOblique'
                    or $font == 'FreeMonoBold' or $font == 'FreeMonoBoldOblique' or $font == 'FreeMonoOblique'
                    or $font == 'FreeSerifBold' or $font == 'FreeSerifBoldItalic' or $font == 'FreeSerifItalic') continue;// we must exclude ALArabiya font

                if (strpos($font, "PDF")) continue;

                $font = str_replace ("-BoldOblique"," Bold Italic",$font);
                $font = str_replace ("-BoldItalic"," Bold Italic",$font);
                $font = str_replace ("-Bold"," Bold",$font);
                $font = str_replace ("-Oblique"," Italic",$font);
                $font = str_replace ("-Italic"," Italic",$font);
                $font = str_replace ("-Roman","",$font);
                $font = str_replace ("-"," ",$font);

                $fontnames[] = $font;
            }
        }

        $fontnames = array_unique($fontnames);

        sort($fontnames);
    ?>
      <div class="row">
        <div class="col-md-6"><label><?= gettext('Font') ?></label></div>
        <div class="col-md-6">
             <select name="<?= $fieldname ?>" class="form-control form-control-sm" id="<?= $fieldname ?>">
             <?php
                foreach ($fontnames as $n) {
                    $sel = '';
                    if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == $n) {
                        $sel = ' selected';
                    }
                  ?>
                    <option value="<?= $n ?>" <?= $sel ?>><?= gettext("$n") ?></option>
              <?php
                }
              ?>
             </select>
             <br>
        </div>
      </div>
    <?php
    }

    public static function FontSizeSelect($fieldname,$message='', $withTitle = true)
    {
        $sizes = [gettext('default'), 6, 7, 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28,30, 32, 34, 36, 38, 40, 42, 44];
      ?>
        <div class="row">
          <?php if ($withTitle) { ?>
          <div class="col-md-6"><label><?= gettext('Font Size').(!empty($message)?' '.$message:'') ?></label></div>
          <?php } ?>
          <div class="col-md-<?= $withTitle?6:12 ?>">
             <select name="<?= $fieldname ?>" class="form-control form-control-sm" id="<?= $fieldname ?>">
             <?php
                $place = 0;
                foreach ($sizes as $s) {
                    $sel = '';
                    if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == $s
                        or array_key_exists($fieldname."SC", $_COOKIE) && $_COOKIE[$fieldname."SC"] == $s) {
                        $sel = ' selected';
                    }
              ?>
                <option value="<?= ($place == 0)?'12':$s ?>"<?= $sel ?>><?= gettext("$s") ?></option>
            <?php
                    $place++;
                  }
            ?>
            </select>
          </div>
        </div>
        <br>
    <?php
    }

    public static function LabelSelect($fieldname,$title='')
    {
        $labels = ['Tractor' => gettext('Tractor'), 
          'Badge' => 'Badge (70 mm x 40 mm) A4', 
          'Badge2' => 'Badge2 (77 mm x 48 mm) A4', 
          '3670' => '3670 (64 mm x 34 mm) A4', 
          '5160' => '5160 (66.675 mm x 25.4) Letter', 
          '5161' => '5161 (101 mm x 25.4 mm) Letter',
          '5162' => '5162 (100.807mm x 34 mm Letter', 
          '5163' => '5163 (101.6 mm x 50.8 mm Letter',
          '5164' => '5164 (4.0 in x 3.33 in Letter', 
          '8600' => '8600 (66.6 mm x 25.4 mm Letter', 
          'C32019' => 'C32019 (85 mm x 54 mm) A4'];

        if (empty($title)) {
          $title = gettext('Label Type');
        }
    ?>

        <div class="row">
          <div class="col-md-6"><label><?= $title ?></label></div>
          <div class="col-md-6">
            <select name="<?= $fieldname ?>" class="form-control form-control-sm" id="<?= $fieldname ?>">
            <?php
              foreach ($labels as $l => $name) {
                  $sel = '';
                  if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == $l) {
                      $sel = ' selected';
                  }
                ?>
                  <option value="<?= $l ?>" <?= $sel ?>><?= gettext("$name") ?></option>
            <?php
              }
            ?>
            </select>
            <br/>
          </div>
        </div>
    <?php
    }

    public static function LabelGroupSelect($fieldname)
    {
    ?>
        <div class="row">
          <div class="col-md-6">
            <label><?= gettext('Label Grouping') ?></label>
          </div>
          <div class="col-md-6">
            <input name="<?= $fieldname ?>" type="radio" value="indiv" <?= (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == 'indiv')?'checked="checked"':'' ?> /><?= gettext('All Individuals') ?><br>
            <input name="<?= $fieldname ?>" type="radio" value="fam" <?= (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == 'fam')?'checked="checked"':'' ?> /><?= gettext('Grouped by Family') ?><br>
          </div>
        </div>
        <br>
    <?php
    }

    public static function ToParentsOfCheckBox($fieldname)
    {
    ?>
        <div class="row">
          <div class="col-md-6">
            <label><?= gettext('To the parents of') ?></label></div>
          <div class="col-md-6">
            <input name="<?= $fieldname ?>" type="checkbox" id="ToParent" value="1" <?= (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname])?'checked':'' ?>>
          </div>
        </div>
        <br>
    <?php
    }

    public static function StartRowStartColumn()
    {
    ?>
      <div class="row">
        <div class="col-md-6"><label><?= gettext('Start Row') ?></label></div>
        <div class="col-md-6">
          <input type="text" name="startrow" id="startrow" maxlength="2" size="3" value="1" class= "form-control form-control-sm">
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-md-6"><label><?= gettext('Start Column') ?></label></div>
        <div class="col-md-6">
          <input type="text" name="startcol" id="startcol" maxlength="2" size="3" value="1" class= "form-control form-control-sm">
        </div>
      </div>
      <br>
    <?php
    }

    public static function IgnoreIncompleteAddresses()
    {
    ?>
      <div class="row">
        <div class="col-md-6"><?= gettext('Ignore Incomplete<br>Addresses') ?>:</div>
        <div class="col-md-6">
          <input type="checkbox" name="onlyfull" id="onlyfull" value="1" checked>
        </div>
      </div>
      <br>
    <?php
    }

    public static function LabelFileType()
    {
    ?>
      <div class="row">
        <div class="col-md-6"><?= gettext('File Type') ?>:</div>
        <div class="col-md-6">
          <select name="filetype" class="form-control form-control-sm">
            <option value="PDF">PDF</option>
            <option value="CSV">CSV</option>
          </select>
        </div>
      </div>
      <br>
    <?php
    }
}