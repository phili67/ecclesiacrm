<?php
/*******************************************************************************
 *
 *  filename    : gdprdarastructure.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker, 2018 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\ListOptionQuery;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-primary card-body">
  <div class="row ">
      <div class="col-sm-2" style="vertical-align: middle;">
         <a class="btn btn-app" href="<?= $sRootPath ?>/Reports/GDPR/GDPRDataStructureExport.php"><i class="fas fa-print"></i> <?= _("Printable Page") ?></a>
      </div>
    </div>
</div>

  <div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <?= _("To validate each text fields, use the tab or enter key !!!") ?>
  </div>


<div class="card box-primary">
  <div class="card-header border-0">
    <h3 class="card-title">
      <label><?= _("Informations about the Data Structure for Persons, Families and Pastoral Cares") ?></label>
    </h3>
  </div>
  <div class="card-body">
    <table class="table table-hover dt-responsive" id="gdpr-data-structure-table" style="width:100%;">
      <thead>
        <tr>
            <th><b><?= _('Informations') ?></b></th>
            <th><b><?= _('For') ?></b></th>
            <th><b><?= _('Type') ?></b></th>
            <th><b><?= _('Comment') ?></b></th>
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
                <td><?= _($personInfo->getName()) ?></td>
                <td><?= _("Person") ?></td>
                <td><?= _($dataType->getOptionName()) ?></td>
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
                <td><?= _("Custom Person") ?></td>
                <td><?= _($dataType->getOptionName()) ?></td>
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
                <td><?= _("Person Property") ?></td>
                <td><?= _($dataType->getOptionName()) ?></td>
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
                <td><?= _($familyInfo->getName()) ?></td>
                <td><?= _("Family") ?></td>
                <td><?= _($dataType->getOptionName()) ?></td>
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
                <td><?= _("Custom Family") ?></td>
                <td><?= _($dataType->getOptionName()) ?></td>
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
                <td><?= _("Family Property") ?></td>
                <td><?= _($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $familyProperty->getProId() ?>" size="70" maxlength="140" class="form-control" value="<?= $familyProperty->getProComment() ?>" data-id="<?= $familyProperty->getProId() ?>" data-type="familyProperty"></td>
            </tr>
      <?php
        }


        foreach ($pastoralCareTypes as $pastoralCareType) {
      ?>
            <tr>
                <td><?= $pastoralCareType->getTitle() ?> <?= !empty($pastoralCareType->getDesc())?"(".$pastoralCareType->getDesc().")":"" ?></td>
                <td><?= _("Pastoral Care") ?></td>
                <td><?= _("Text Field (100 char)") ?></td>
                <td><input type="text" name="<?= $pastoralCareType->getId() ?>" size="70" maxlength="140" class="form-control" value="<?= $pastoralCareType->getComment() ?>" data-id="<?= $pastoralCareType->getId() ?>" data-type="pastoralCare"></td>
            </tr>
      <?php
        }
      ?>
        </tbody>
    </table>
  </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/gdpr/GDPRDataStructure.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
