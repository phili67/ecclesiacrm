<?php
/*******************************************************************************
 *
 *  filename    : NoteEditor.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker, 2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GdprInfoQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PropertyQuery;

// Set the page title and include HTML header
$sPageTitle = gettext('GDPR Data Structure');

if (!($_SESSION['user']->isGdrpDpoEnabled())) {
  Redirect('Menu.php');
  exit;
}

// for persons
$personCustMasts = PersonCustomMasterQuery::Create()
      ->orderByCustomName()
      ->find();
      
$personInfos = GdprInfoQuery::Create()->filterByAbout('Person')->find();

$personProperties = PropertyQuery::Create()->filterByProClass('p')->find();

// for families
$familyCustMasts = FamilyCustomMasterQuery::Create()
      ->orderByCustomName()
      ->find();

$familyInfos = GdprInfoQuery::Create()->filterByAbout('Family')->find();

$familyProperties = PropertyQuery::Create()->filterByProClass('f')->find();

// for pastoral care
$pastoralCareTypes = PastoralCareTypeQuery::Create()->find();

require 'Include/Header.php';

?>

<div class="box box-primary box-body">
  <div class="row ">
      <div class="col-sm-2" style="vertical-align: middle;">
         <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/GDPRDataStructureExport.php"><i class="fa fa-print"></i> <?= gettext("Printable Page") ?></a>
      </div>
    </div>
</div>

  <div class="alert alert-info">
    <i class="fa fa-info-circle"></i>
    <?= gettext("To validate each text fields, use the tab or enter key !!!") ?>
  </div>


<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">
      <label><?= gettext("Informations about the Data Structure for Persons, Families and Pastoral Cares") ?></label>
    </h3>
  </div>
  <div class="box-body">
    <table class="table table-hover dt-responsive" id="gdpr-data-structure-table" style="width:100%;">
      <thead>
        <tr>
            <th><b><?= gettext('Informations') ?></b></th>
            <th><b><?= gettext('For') ?></b></th>
            <th><b><?= gettext('Type') ?></b></th>
            <th><b><?= gettext('Comment') ?></b></th>
        </tr>
      </thead>
      <tbody>
      <?php
      
        foreach ($personInfos as $personInfo) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personInfo->getTypeId())
            ->findOneById(4);
      ?>
            <tr>
                <td><?= gettext($personInfo->getName()) ?></td>
                <td><?= gettext("Person") ?></td>
                <td><?= gettext($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $personInfo->getId() ?>" size="70" maxlength="140" class="form-control" value="<?= $personInfo->getComment() ?>" data-id="<?= $personInfo->getId() ?>" data-type="person"></td>
            </tr>

      <?php
        }

        foreach ($personCustMasts as $personCustMast) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personCustMast->getTypeId())
            ->findOneById(4);
      ?>
            <tr>
                <td><?= $personCustMast->getCustomName() ?></td>
                <td><?= gettext("Custom Person") ?></td>
                <td><?= gettext($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $personCustMast->getId() ?>" size="70" maxlength="140" class="form-control" value="<?= $personCustMast->getCustomComment() ?>" data-id="<?= $personCustMast->getId() ?>" data-type="personCustom"></td>
            </tr>
      <?php
        }

        foreach ($personProperties as $personProperty) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personProperty->getProPrtId())
            ->findOneById(4);
      ?>
            <tr>
                <td><?= $personProperty->getProName()." (".$personProperty->getProDescription().")" ?></td>
                <td><?= gettext("Person Property") ?></td>
                <td><?= gettext($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $personProperty->getProId() ?>" size="70" maxlength="140" class="form-control" value="<?= $personProperty->getProComment() ?>" data-id="<?= $personProperty->getProId() ?>" data-type="personProperty"></td>
            </tr>
            
      <?php
        }       

        foreach ($familyInfos as $familyInfo) {
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyInfo->getTypeId())
            ->findOneById(4);
      ?>
            <tr>
                <td><?= gettext($familyInfo->getName()) ?></td>
                <td><?= gettext("Family") ?></td>
                <td><?= gettext($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $familyInfo->getId() ?>" size="70" maxlength="140" class="form-control" value="<?= $familyInfo->getComment() ?>" data-id="<?= $familyInfo->getId() ?>" data-type="family"></td>
            </tr>

      <?php
        }

        foreach ($familyCustMasts as $familyCustMast) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyCustMast->getTypeId())
            ->findOneById(4);
      ?>
            <tr>
                <td><?= $familyCustMast->getCustomName() ?></td>
                <td><?= gettext("Custom Family") ?></td>
                <td><?= gettext($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $personCustMast->getId() ?>" size="70" maxlength="140" class="form-control" value="<?= $familyCustMast->getCustomComment() ?>" data-id="<?= $familyCustMast->getId() ?>" data-type="familyCustom"></td>
            </tr>
      <?php
        }

        foreach ($familyProperties as $familyProperty) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($familyProperty->getProPrtId())
            ->findOneById(4);
      ?>
            <tr>
                <td><?= $familyProperty->getProName()." (".$familyProperty->getProDescription().")" ?></td>
                <td><?= gettext("Family Property") ?></td>
                <td><?= gettext($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $familyProperty->getProId() ?>" size="70" maxlength="140" class="form-control" value="<?= $familyProperty->getProComment() ?>" data-id="<?= $familyProperty->getProId() ?>" data-type="familyProperty"></td>
            </tr>
      <?php
        } 
        
        
        foreach ($pastoralCareTypes as $pastoralCareType) { 
      ?>
            <tr>
                <td><?= $pastoralCareType->getTitle() ?> <?= !empty($pastoralCareType->getDesc())?"(".$pastoralCareType->getDesc().")":"" ?></td>
                <td><?= gettext("Pastoral Care") ?></td>
                <td><?= gettext("Text Field (100 char)") ?></td>
                <td><input type="text" name="<?= $pastoralCareType->getId() ?>" size="70" maxlength="140" class="form-control" value="<?= $pastoralCareType->getComment() ?>" data-id="<?= $pastoralCareType->getId() ?>" data-type="pastoralCare"></td>
            </tr>
      <?php
        } 
      ?>
        </tbody>
    </table>
  </div>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/GDPRDataStructure.js"></script>

<?php require 'Include/Footer.php' ?>