<?php

/* Copyright : Philippe Logel */

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemConfig;

class LabelUtils {

    public function FontSelect($fieldname)
    {
        $sFPDF_PATH = __DIR__.'/../../vendor/setasign/fpdf';

        $d = scandir($sFPDF_PATH.'/font/', SCANDIR_SORT_DESCENDING);
    
        $fontnames = [];
        $family = ' ';
        foreach ($d as $entry) {
            $len = strlen($entry);
            if ($len > 3) {
                $r = file_get_contents ($sFPDF_PATH.'/font/'.$entry);
                $res = explode('$name = \'', $r);
                $font = explode ("';",$res[1]);
            
                $font = $font[0];
            
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

        sort($fontnames);
    ?>
      <div class="row">
        <div class="col-md-6"><?= gettext('Font') ?>:</div>
        <div class="col-md-6">
             <select name="<?= $fieldname ?>" class="form-control input-sm">
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

    public function FontSizeSelect($fieldname,$message='')
    {
        $sizes = [gettext('default'), 6, 7, 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26];
      ?>
        <div class="row">
          <div class="col-md-6"><?= gettext('Font Size').(!empty($message)?' '.$message:'') ?>:</div>
          <div class="col-md-6">
             <select name="<?= $fieldname ?>" class="form-control input-sm">
             <?php
                foreach ($sizes as $s) {
                    $sel = '';
                    if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == $s) {
                        $sel = ' selected';
                    }
              ?>
                <option value="<?= $s ?>"<?= $sel ?>><?= gettext("$s") ?></option>
            <?php
                }
            ?>
            </select>
          </div>
        </div>
        <br>
    <?php
    }

    public function LabelSelect($fieldname,$title='')
    {
        $labels = [gettext('Tractor') => gettext('Tractor'), 'Badge' => 'Badge (65 mm x 37 mm) A4', '3670' => '3670 (64 mm x 34 mm) A4', '5160' => '5160', '5161' => '5161', '5162' => '5162', '5163' => '5163', '5164' => '5164', '8600' => '8600', 'C32019' => 'C32019 (85 mm x 54 mm) A4'];
    
        if (empty($title)) {
          $title = gettext('Label Type');
        }
    ?>
    
        <div class="row">
          <div class="col-md-6"><?= $title ?>:</div>
          <div class="col-md-6">
            <select name="<?= $fieldname ?>" class="form-control input-sm">
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

    public function LabelGroupSelect($fieldname)
    {
    ?>
        <div class="row">
          <div class="col-md-6"><?= gettext('Label Grouping') ?></div>
          <div class="col-md-6">
            <input name="<?= $fieldname ?>" type="radio" value="indiv" <?= (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == 'indiv')?'checked="checked"':'' ?> /><?= gettext('All Individuals') ?><br>
            <input name="<?= $fieldname ?>" type="radio" value="fam" <?= (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == 'fam')?'checked="checked"':'' ?> /><?= gettext('Grouped by Family') ?><br>
          </div>
        </div>
        <br>
    <?php
    }

    public function ToParentsOfCheckBox($fieldname)
    {
    ?>
        <div class="row">
          <div class="col-md-6"><?= gettext('To the parents of') ?>:</div>
          <div class="col-md-6">
            <input name="<?= $fieldname ?>" type="checkbox" id="ToParent" value="1" <?= (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname])?'checked':'' ?>>
          </div>
        </div>
        <br>
    <?php
    }

    public function StartRowStartColumn()
    {
    ?>
      <div class="row">
        <div class="col-md-6"><?= gettext('Start Row') ?>:</div>
        <div class="col-md-6">
          <input type="text" name="startrow" id="startrow" maxlength="2" size="3" value="1" class="form-control">
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-md-6"><?= gettext('Start Column') ?>:</div>
        <div class="col-md-6">
          <input type="text" name="startcol" id="startcol" maxlength="2" size="3" value="1" class="form-control">
        </div>
      </div>
      <br>
    <?php
    }

    public function IgnoreIncompleteAddresses()
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

    public function LabelFileType()
    {
    ?>
      <div class="row">
        <div class="col-md-6"><?= gettext('File Type') ?>:</div>
        <div class="col-md-6">
          <select name="filetype" class="form-control input-sm">
            <option value="PDF">PDF</option>
            <option value="CSV">CSV</option>
          </select>
        </div>
      </div>
      <br>
    <?php
    }
}