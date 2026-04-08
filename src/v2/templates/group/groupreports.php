<?php

/*******************************************************************************
 *
 *  filename    : groupreport.php
 *  last change : 2023-05-29
 *  description : report a group
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\GroupPropMasterQuery;

use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<script src="<?= $sRootPath ?>/skin/js/group/GroupRoles.js"></script>

<?php
  if ($iGroupID == -1) {
    $currentUserBelongToGroup = SessionUser::getUser()->belongsToGroup($iGroupID);

    if ($currentUserBelongToGroup == 0) {
        return $response->withStatus(302)->withHeader('Location', $sRootPath . '/v2/dashboard');
    }
?>
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i><?= _('Select the group you would like to report') ?></h3>
                </div>
                <div class="card-body">
                <form method="POST" action="<?= $sRootPath ?>/v2/group/reports">
                    <div class="form-group">
                        <label for="GroupID" class="font-weight-bold"><i class="fas fa-users mr-1 text-primary"></i><?= _('Select Group') ?></label>
                        <select id="GroupID" class="form-control form-control-sm" name="GroupID" onChange="UpdateRoles();">
                            <option value="-1"><?= _('None') ?></option>
                            <?php foreach ($groups as $group) { ?>
                                <option value="<?= $group->getId() ?>"><?= $group->getName() ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="GroupRole" class="font-weight-bold"><i class="fas fa-user-tag mr-1 text-primary"></i><?= _('Select Role') ?></label>
                        <select name="GroupRole" class="form-control form-control-sm" id="GroupRole">
                            <option><?= _('No Role Selected') ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="OnlyCart" name="OnlyCart" value="1">
                            <label class="custom-control-label font-weight-bold" for="OnlyCart">
                                <i class="fas fa-shopping-cart mr-1 text-primary"></i><?= _('Only cart persons?') ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold"><i class="fas fa-file-alt mr-1 text-primary"></i><?= _('Report Model') ?></label>
                        <div class="d-flex flex-column" style="gap:.5rem;">
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="ReportModel1" name="ReportModel" value="1" checked>
                                <label class="custom-control-label" for="ReportModel1"><?= _('Report for group and role selected') ?></label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="ReportModel2" name="ReportModel" value="2">
                                <label class="custom-control-label" for="ReportModel2"><?= _('Report for any role in group selected') ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3 pt-3 border-top" style="gap:.5rem;">
                        <a href="<?= $sRootPath ?>/v2/system/report/list" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                        </a>
                        <button type="submit" name="Submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right mr-1"></i><?= _('Next') ?>
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    $groupName = GroupQuery::Create()->findOneById($iGroupID)->getName();

    ?>
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i><?= _('Select which information you want to include') ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= $sRootPath ?>/Reports/GroupReport.php">
                        <input type="hidden" name="GroupID" value="<?= $iGroupID ?>">
                        <input type="hidden" name="GroupRole" <?php if (array_key_exists('GroupRole', $_POST)) { echo 'value="'.$_POST['GroupRole'].'"'; } ?>>
                        <input type="hidden" name="OnlyCart" <?php if (array_key_exists('OnlyCart', $_POST)) { echo 'value="'.$_POST['OnlyCart'].'"'; } ?>>
                        <input type="hidden" name="ReportModel" value="<?= $_POST['ReportModel'] ?>">

                        <?php $propFields = GroupPropMasterQuery::Create()->orderByPropId()->findByGroupId($iGroupID); ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card card-outline card-info shadow-sm h-100">
                                    <div class="card-header border-0 py-2">
                                        <h6 class="card-title mb-0"><i class="fas fa-info-circle mr-2 text-info"></i><?= _('Standard Info') ?></h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <?php
                                        $stdFields = [
                                            'AddressEnable'    => [_('Address'),    'fa-home'],
                                            'HomePhoneEnable'  => [_('Home Phone'), 'fa-phone'],
                                            'WorkPhoneEnable'  => [_('Work Phone'), 'fa-building'],
                                            'CellPhoneEnable'  => [_('Cell Phone'), 'fa-mobile-alt'],
                                            'EmailEnable'      => [_('Email'),      'fa-envelope'],
                                            'OtherEmailEnable' => [_('Other Email'),'fa-at'],
                                            'GroupRoleEnable'  => [_('GroupRole'),  'fa-user-tag'],
                                        ];
                                        foreach ($stdFields as $name => [$label, $icon]) {
                                            ?>
                                            <div class="custom-control custom-checkbox mb-1">
                                                <input type="checkbox" class="custom-control-input" id="<?= $name ?>" name="<?= $name ?>" value="1">
                                                <label class="custom-control-label" for="<?= $name ?>">
                                                    <i class="fas <?= $icon ?> mr-1 text-muted"></i><?= $label ?>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card card-outline card-secondary shadow-sm h-100">
                                    <div class="card-header border-0 py-2">
                                        <h6 class="card-title mb-0"><i class="fas fa-cogs mr-2 text-secondary"></i><?= _('Group-Specific Property Fields') ?></h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <?php if ($propFields->count() > 0) {
                                            foreach ($propFields as $propField) { ?>
                                                <div class="custom-control custom-checkbox mb-1">
                                                    <input type="checkbox" class="custom-control-input" id="<?= $propField->getField() ?>enable" name="<?= $propField->getField() ?>enable" value="1">
                                                    <label class="custom-control-label" for="<?= $propField->getField() ?>enable">
                                                        <i class="fas fa-tag mr-1 text-muted"></i><?= $propField->getName() ?>
                                                    </label>
                                                </div>
                                            <?php }
                                        } else { ?>
                                            <p class="text-muted small mb-0"><i class="fas fa-info-circle mr-1"></i><?= _('None') ?></p>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3 pt-3 border-top" style="gap:.5rem;">
                            <a href="<?= $sRootPath ?>/v2/group/<?= $iGroupID ?>/view" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
                            </a>
                            <button type="submit" name="Submit" class="btn btn-sm btn-success">
                                <i class="fas fa-file-export mr-1"></i><?= _('Create Report') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

  <?php
    }
  ?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




