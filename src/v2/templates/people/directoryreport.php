<?php
/*******************************************************************************
 *
 *  filename    : templates/directoryreport.php
 *  last change : 2023-05-28
 *  description : form to invoke directory report
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt
 *  Copyright 2004-2012 Michael Wilt
 *  Copyright 2022-2023 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
 
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonCustomMasterQuery;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-2 mb-md-0"><i class="fas fa-address-book mr-2"></i><?= _('Directory Report') ?></h3>
            <span class="badge badge-info"><?= _('Filters') ?></span>
        </div>
    </div>
    <form method="POST" action="<?= $sRootPath?>/Reports/DirectoryReport.php">
    <div class="card-body">

            <?php

            // Get classifications for the selects
            $ormClassifications = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById(1);

            //Get Family Roles for the drop-down
            $ormFamilyRoles = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById(2);

            // Get Field Security List Matrix
            $ormSecurityGrps = ListOptionQuery::Create()
                ->orderByOptionSequence()
                ->findById(5);

            foreach ($ormSecurityGrps as $ormSecurityGrp) {
                $aSecurityType[$ormSecurityGrp->getOptionId()] = $ormSecurityGrp->getOptionName();
            }

            // Get all the Groups
            $ormGroups = GroupQuery::Create()->orderByName()->find();

            // Get the list of custom person fields
            $ormCustomFields = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();
            $numCustomFields = $ormCustomFields->count();

            $aDefaultClasses = explode(',', SystemConfig::getValue('sDirClassifications'));
            $aDirRoleHead = explode(',', SystemConfig::getValue('sDirRoleHead'));
            $aDirRoleSpouse = explode(',', SystemConfig::getValue('sDirRoleSpouse'));
            $aDirRoleChild = explode(',', SystemConfig::getValue('sDirRoleChild'));

            ?>
            <?php if (!empty($cartdir)) { ?>
                <div class="card card-light mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title h6 mb-0"><?= _('Cart Filters') ?></h3>
                    </div>
                    <div class="card-body py-2">
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Exclude Inactive Families') ?></label>
                            <div class="col-sm-8">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="bExcludeInactive" value="1" checked>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Select classifications to include') ?></label>
                            <div class="col-sm-8">
                                <small class="text-muted d-block mb-1"><?= _('Use Ctrl Key to select multiple') ?></small>
                                <select name="sDirClassifications[]" size="5" multiple class="form-control form-control-sm">
                                    <option value="0"><?= _("Unassigned") ?></option>
                                    <?php foreach ($ormClassifications as $rsClassification) { ?>
                                        <option value="<?= $rsClassification->getOptionId() ?>" <?= (in_array($rsClassification->getOptionId(), $aDefaultClasses)) ? ' selected' : '' ?>><?= _($rsClassification->getOptionName()) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Group Membership') ?></label>
                            <div class="col-sm-8">
                                <small class="text-muted d-block mb-1"><?= _('Use Ctrl Key to select multiple') ?></small>
                                <select name="GroupID[]" size="5" multiple class="form-control form-control-sm">
                                    <?php foreach ($ormGroups as $group) { ?>
                                        <option value="<?= $group->getId() ?>"> <?= $group->getName() ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="card card-light mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title h6 mb-0"><?= _('Family Roles') ?></h3>
                </div>
                <div class="card-body py-2">
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Which role is the head of household?') ?></label>
                        <div class="col-sm-8">
                            <small class="text-muted d-block mb-1"><?= _('Use Ctrl Key to select multiple') ?></small>
                            <select name="sDirRoleHead[]" size="5" multiple class="form-control form-control-sm">
                                <?php foreach ($ormFamilyRoles as $ormFamilyRole) { ?>
                                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleHead)) ? ' selected' : '' ?>><?= _($ormFamilyRole->getOptionName()) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Which role is the spouse?') ?></label>
                        <div class="col-sm-8">
                            <small class="text-muted d-block mb-1"><?= _('Use Ctrl Key to select multiple') ?></small>
                            <select name="sDirRoleSpouse[]" size="5" multiple class="form-control form-control-sm">
                                <?php foreach ($ormFamilyRoles as $ormFamilyRole) { ?>
                                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleSpouse)) ? ' selected' : '' ?>><?= _($ormFamilyRole->getOptionName()) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Which role is a child?') ?></label>
                        <div class="col-sm-8">
                            <small class="text-muted d-block mb-1"><?= _('Use Ctrl Key to select multiple') ?></small>
                            <select name="sDirRoleChild[]" size="5" multiple class="form-control form-control-sm">
                                <?php foreach ($ormFamilyRoles as $ormFamilyRole) { ?>
                                    <option value="<?= $ormFamilyRole->getOptionId() ?>" <?= (in_array($ormFamilyRole->getOptionId(), $aDirRoleChild)) ? ' selected' : '' ?>><?= _($ormFamilyRole->getOptionName()) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-light mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title h6 mb-0"><?= _('Information to Include') ?></h3>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2 small text-uppercase text-muted font-weight-bold"><?= _('Standard Fields') ?></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirAddress" value="1" checked><label class="form-check-label"><?= _('Address') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirWedding" value="1" checked><label class="form-check-label"><?= _('Wedding Date') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirBirthday" value="1" checked><label class="form-check-label"><?= _('Birthday') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirFamilyPhone" value="1" checked><label class="form-check-label"><?= _('Family Home Phone') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirFamilyWork" value="1" checked><label class="form-check-label"><?= _('Family Work Phone') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirFamilyCell" value="1" checked><label class="form-check-label"><?= _('Family Cell Phone') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirFamilyEmail" value="1" checked><label class="form-check-label"><?= _('Family Email') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirPersonalPhone" value="1" checked><label class="form-check-label"><?= _('Personal Home Phone') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirPersonalWork" value="1" checked><label class="form-check-label"><?= _('Personal Work Phone') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirPersonalCell" value="1" checked><label class="form-check-label"><?= _('Personal Cell Phone') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirPersonalEmail" value="1" checked><label class="form-check-label"><?= _('Personal Email') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirPersonalWorkEmail" value="1" checked><label class="form-check-label"><?= _('Personal Work/Other Email') ?></label></div>
                            <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bDirPhoto" value="1" checked><label class="form-check-label"><?= _('Photos') ?></label></div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2 small text-uppercase text-muted font-weight-bold"><?= _('Custom Fields') ?></div>
                            <?php
                            if ($numCustomFields > 0) {
                                foreach ($ormCustomFields as $ormCustomField) {
                                    if (($aSecurityType[$ormCustomField->getCustomFieldSec()] == 'bAll') || ($_SESSION[$aSecurityType[$ormCustomField->getCustomFieldSec()]])) {
                            ?>
                                        <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="bCustom<?= $ormCustomField->getCustomOrder() ?>" value="1" checked><label class="form-check-label"><?= $ormCustomField->getCustomName() ?></label></div>
                            <?php
                                    }
                                }
                            } else {
                            ?>
                                <p class="text-muted small mb-0"><?= _('No custom fields available') ?></p>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-light mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title h6 mb-0"><?= _('Layout Options') ?></h3>
                </div>
                <div class="card-body py-2">
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Number of Columns') ?></label>
                        <div class="col-sm-8">
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="NumCols" value="1"><label class="form-check-label">1 <?= _('col') ?></label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="NumCols" value="2" checked><label class="form-check-label">2 <?= _('cols') ?></label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="NumCols" value="3"><label class="form-check-label">3 <?= _('cols') ?></label></div>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Paper Size') ?></label>
                        <div class="col-sm-8">
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="PageSize" value="letter" checked><label class="form-check-label">Letter (8.5x11)</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="PageSize" value="legal"><label class="form-check-label">Legal (8.5x14)</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="PageSize" value="a4"><label class="form-check-label">A4</label></div>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Font Size') ?></label>
                        <div class="col-sm-8">
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="FSize" value="6"><label class="form-check-label">6</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="FSize" value="8"><label class="form-check-label">8</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="FSize" value="10" checked><label class="form-check-label">10</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="FSize" value="12"><label class="form-check-label">12</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="FSize" value="14"><label class="form-check-label">14</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="FSize" value="16"><label class="form-check-label">16</label></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-light mb-0">
                <div class="card-header py-2">
                    <h3 class="card-title h6 mb-0"><?= _('Title page') ?></h3>
                </div>
                <div class="card-body py-2">
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Use Title Page') ?></label>
                        <div class="col-sm-8 pt-1">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="bDirUseTitlePage" value="1"></div>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Church Name') ?></label>
                        <div class="col-sm-8"><input type="text" name="sEntityName" value="<?= SystemConfig::getValue('sEntityName') ?>" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Address') ?></label>
                        <div class="col-sm-8"><input type="text" name="sEntityAddress" value="<?= SystemConfig::getValue('sEntityAddress') ?>" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('City') ?></label>
                        <div class="col-sm-8"><input type="text" name="sEntityCity" value="<?= SystemConfig::getValue('sEntityCity') ?>" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('State') ?></label>
                        <div class="col-sm-8"><input type="text" name="sEntityState" value="<?= SystemConfig::getValue('sEntityState') ?>" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Zip') ?></label>
                        <div class="col-sm-8"><input type="text" name="sEntityZip" value="<?= SystemConfig::getValue('sEntityZip') ?>" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Phone') ?></label>
                        <div class="col-sm-8"><input type="text" name="sEntityPhone" value="<?= SystemConfig::getValue('sEntityPhone') ?>" class="form-control form-control-sm"></div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold"><?= _('Disclaimer') ?></label>
                        <div class="col-sm-8"><textarea name="sDirectoryDisclaimer" cols="35" class="form-control form-control-sm" rows="4\"><?= SystemConfig::getValue('sDirectoryDisclaimer1') . ' ' . SystemConfig::getValue('sDirectoryDisclaimer2') ?></textarea></div>
                    </div>
                </div>
            </div>
                        </tr>
                        <tr>
                            <td><?= _('Disclaimer') ?>
                            <td><textarea Name="sDirectoryDisclaimer" cols="35" class= "form-control form-control-sm"
                                          rows="4"><?= SystemConfig::getValue('sDirectoryDisclaimer1') . ' ' . SystemConfig::getValue('sDirectoryDisclaimer2') ?></textarea>
                        </tr>

                    </table>
                </div>
            </div>


            <?php
            if (!empty($cartdir)) {
                ?>
                <input type="hidden" name="cartdir" value="M">
                <?php
            }
            ?>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <div class="btn-group" role="group" aria-label="Directory actions">
            <button type="submit" class="btn btn-primary" name="Submit">
                <i class="fas fa-file-alt mr-1"></i><?= _('Create Directory') ?>
            </button>
            <button type="button" class="btn btn-outline-secondary" name="Cancel"
                    onclick="javascript:document.location='<?= $sRootPath ?>/v2/people/dashboard';">
                <i class="fas fa-times mr-1"></i><?= _('Cancel') ?>
            </button>
        </div>
    </div>
    </form>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


