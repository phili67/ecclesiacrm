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

<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-shield-alt mr-1"></i><?= _('GDPR Data Structure') ?></h3>
        <a class="btn btn-sm btn-info" href="<?= $sRootPath ?>/Reports/GDPR/GDPRDataStructureExport.php">
            <i class="fas fa-file-export mr-1"></i><?= _("Export") ?>
        </a>
    </div>
    <div class="card-body py-2">
        <div class="alert alert-info mb-0">
            <i class="fas fa-lightbulb mr-2"></i>
            <?= _("To validate each text fields, use the tab or enter key !!!") ?>
        </div>
    </div>
</div>

<div class="card card-outline card-info shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i><?= _("Data Structure Information") ?></h3>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped table-sm dt-responsive" id="gdpr-data-structure-table" style="width:100%;">
            <thead class="thead-light">
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
                <td><input type="text" name="<?= $personInfo->getId() ?>" size="70" maxlength="140" class= "form-control form-control-sm" value="<?= $personInfo->getComment() ?>" data-id="<?= $personInfo->getId() ?>" data-type="person"></td>
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
                <td><input type="text" name="<?= $personCustMast->getId() ?>" size="70" maxlength="140" class= "form-control form-control-sm" value="<?= $personCustMast->getCustomComment() ?>" data-id="<?= $personCustMast->getId() ?>" data-type="personCustom"></td>
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
                <td><input type="text" name="<?= $personProperty->getProId() ?>" size="70" maxlength="140" class= "form-control form-control-sm" value="<?= $personProperty->getProComment() ?>" data-id="<?= $personProperty->getProId() ?>" data-type="personProperty"></td>
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
                <td><input type="text" name="<?= $familyInfo->getId() ?>" size="70" maxlength="140" class= "form-control form-control-sm" value="<?= $familyInfo->getComment() ?>" data-id="<?= $familyInfo->getId() ?>" data-type="family"></td>
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
                <td><input type="text" name="<?= $personCustMast->getId() ?>" size="70" maxlength="140" class= "form-control form-control-sm" value="<?= $familyCustMast->getCustomComment() ?>" data-id="<?= $familyCustMast->getId() ?>" data-type="familyCustom"></td>
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
                <td><input type="text" name="<?= $familyProperty->getProId() ?>" size="70" maxlength="140" class= "form-control form-control-sm" value="<?= $familyProperty->getProComment() ?>" data-id="<?= $familyProperty->getProId() ?>" data-type="familyProperty"></td>
            </tr>
      <?php
        }


        foreach ($pastoralCareTypes as $pastoralCareType) {
      ?>
            <tr>
                <td><?= $pastoralCareType->getTitle() ?> <?= !empty($pastoralCareType->getDesc())?"(".$pastoralCareType->getDesc().")":"" ?></td>
                <td><?= _("Pastoral Care") ?></td>
                <td><?= _("Text Field (100 char)") ?></td>
                <td><input type="text" name="<?= $pastoralCareType->getId() ?>" size="70" maxlength="140" class= "form-control form-control-sm" value="<?= $pastoralCareType->getComment() ?>" data-id="<?= $pastoralCareType->getId() ?>" data-type="pastoralCare"></td>
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
