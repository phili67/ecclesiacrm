<?php

/*******************************************************************************
 *
 *  filename    : templates/csvExport.php
 *  last change : 2023-06-24
 *  website     : http://www.ecclesiacrm.com
 *                          © 2023 Philippe Logel
 *
 ******************************************************************************/

 use EcclesiaCRM\Utils\OutputUtils;
 use EcclesiaCRM\dto\SystemConfig;
 
 use EcclesiaCRM\ListOptionQuery;
 use EcclesiaCRM\GroupQuery;
 use EcclesiaCRM\PersonCustomMasterQuery;
 use EcclesiaCRM\FamilyCustomMasterQuery;
 

//Get Classifications for the drop-down
$ormClassifications = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(1);

//Get Family Roles for the drop-down
$ormFamilyRoles = ListOptionQuery::Create()
    ->orderByOptionSequence()
    ->findById(2);

// Get all the Groups
$groups = GroupQuery::Create()->orderByName()->find();

// the custom fields
$customFields = PersonCustomMasterQuery::Create()->orderByCustomOrder()->find();
$numCustomFields = $customFields->count();

$famCustomFields = FamilyCustomMasterQuery::Create()->orderByCustomOrder()->find();
$numFamCustomFields = $famCustomFields->count();

// Set the page title and include HTML header
$sPageTitle = _('CSV Export');
require $sRootDocument . '/Include/Header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h3 class="h4 mb-1"><i class="fas fa-file-csv mr-2 text-primary"></i><?= _('CSV Export') ?></h3>
        <p class="text-muted mb-0"><?= _('Choose fields, apply filters, and generate your export file.') ?></p>
    </div>
    <span class="badge badge-light border px-3 py-2"><?= _('System Export Tool') ?></span>
</div>

<style>
    .csv-export-modern .custom-control-label {
        cursor: pointer;
        font-weight: 500;
    }

    .csv-export-modern .custom-switch .custom-control-label::before {
        top: .15rem;
    }

    .csv-export-modern .custom-switch .custom-control-label::after {
        top: calc(.15rem + 2px);
    }
</style>

<form method="post" action="<?= $sRootPath ?>/Reports/CSVCreateFile.php" class="csv-export-modern">
    <div class="card card-outline card-primary">
        <div class="card-header border-1">
            <h3 class="card-title"><i class="fas fa-list-check mr-1"></i><?= _('Field Selection') ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-id" name="Id" value="1">
                        <label class="custom-control-label" for="csv-id"><?= _('Id') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-4 pt-1">
                        <span class="mr-2 font-weight-bold"><?= _('Last Name') ?></span>
                        <span class="badge badge-danger"><?= _('Required') ?></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-title" name="Title" value="1">
                        <label class="custom-control-label" for="csv-title"><?= _('Title') ?></label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-firstname" name="FirstName" value="1" checked>
                        <label class="custom-control-label" for="csv-firstname"><?= _('First Name') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-middlename" name="MiddleName" value="1">
                        <label class="custom-control-label" for="csv-middlename"><?= _('Middle Name') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-suffix" name="Suffix" value="1">
                        <label class="custom-control-label" for="csv-suffix"><?= _('Suffix') ?></label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-address1" name="Address1" value="1" checked>
                        <label class="custom-control-label" for="csv-address1"><?= _('Address') ?> 1</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-address2" name="Address2" value="1" checked>
                        <label class="custom-control-label" for="csv-address2"><?= _('Address') ?> 2</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-city" name="City" value="1" checked>
                        <label class="custom-control-label" for="csv-city"><?= _('City') ?></label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-state" name="State" value="1" checked>
                        <label class="custom-control-label" for="csv-state"><?= _('State') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-zip" name="Zip" value="1" checked>
                        <label class="custom-control-label" for="csv-zip"><?= _('Zip') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-envelope" name="Envelope" value="1">
                        <label class="custom-control-label" for="csv-envelope"><?= _('Envelope') ?></label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-country" name="Country" value="1" checked>
                        <label class="custom-control-label" for="csv-country"><?= _('Country') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-homephone" name="HomePhone" value="1">
                        <label class="custom-control-label" for="csv-homephone"><?= _('Home Phone') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-workphone" name="WorkPhone" value="1">
                        <label class="custom-control-label" for="csv-workphone"><?= _('Work Phone') ?></label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-cellphone" name="CellPhone" value="1">
                        <label class="custom-control-label" for="csv-cellphone"><?= _('Mobile Phone') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-email" name="Email" value="1">
                        <label class="custom-control-label" for="csv-email"><?= _('Email') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-workemail" name="WorkEmail" value="1">
                        <label class="custom-control-label" for="csv-workemail"><?= _('Work/Other Email') ?></label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-membershipdate" name="MembershipDate" value="1">
                        <label class="custom-control-label" for="csv-membershipdate"><?= _('Membership Date') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-birthdaydate" name="BirthdayDate" value="1">
                        <label class="custom-control-label" for="csv-birthdaydate">* <?= _('Birth / Anniversary Date') ?></label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-age" name="Age" value="1">
                        <label class="custom-control-label" for="csv-age">* <?= _('Age / Years Married') ?></label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-classification" name="PrintMembershipStatus" value="1">
                        <label class="custom-control-label" for="csv-classification"><?= _('Classification') ?></label>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="csv-familyrole" name="PrintFamilyRole" value="1">
                        <label class="custom-control-label" for="csv-familyrole"><?= _('Family Role') ?></label>
                    </div>
                    <span class="text-danger small"> <?= _('Depends whether using person or family output method') ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php
    if ($numCustomFields > 0 || $numFamCustomFields > 0) {
        ?>
        <div class="card card-outline card-secondary">
            <div class="card-header border-1">
                <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i><?= _('Custom Field Selection') ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <?php
                        if ($numCustomFields > 0) {
                            ?>
                            <h5 class="mb-2"><?= _('Custom Person Fields') ?></h5>
                            <table class="table table-sm table-borderless" cellpadding="4" align="left">
                                <?php
                                // Display the custom fields
                                foreach ($customFields as $customField) {
                                    if (OutputUtils::securityFilter($customField->getCustomFieldSec())) {
                                        ?>
                                        <tr>
                                            <td class="LabelColumn"><?= $customField->getCustomName() ?></td>
                                            <td class="TextColumn">
                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input"
                                                           id="person-<?= $customField->getCustomField() ?>"
                                                           name="<?= $customField->getCustomField() ?>"
                                                           value="1">
                                                    <label class="custom-control-label" for="person-<?= $customField->getCustomField() ?>"></label>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } ?>
                            </table>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col-lg-6">
                        <?php
                        if ($numFamCustomFields > 0) {
                            ?>
                            <h5 class="mb-2"><?= _('Custom Family Fields') ?></h5>
                            <table class="table table-sm table-borderless" cellpadding="4" align="left">
                                <?php
                                // Display the family custom fields
                                foreach ($famCustomFields as $famCustomField) {
                                    if (OutputUtils::securityFilter($famCustomField->getCustomFieldSec())) {
                                        ?>
                                        <tr>
                                            <td class="LabelColumn"><?= $famCustomField->getCustomName() ?></td>
                                            <td class="TextColumn">
                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input"
                                                           id="family-<?= $famCustomField->getCustomField() ?>"
                                                           name="<?= $famCustomField->getCustomField() ?>"
                                                           value="1">
                                                    <label class="custom-control-label" for="family-<?= $famCustomField->getCustomField() ?>"></label>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } ?>
                            </table>
                            <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="card card-outline card-warning">
        <div class="card-header border-1">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i><?= _('Filters') . ' (' . _('Ignored if you come from the CartView') . ')' ?></h3>
            <div class="card-tools pull-right">
                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#OtherFilters"
                        aria-expanded="false" aria-controls="OtherFilters">
                    <i
                        class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body collapse" id="OtherFilters">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Records to export') ?>:</h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#RecordFilters"
                                        aria-expanded="false" aria-controls="RecordFilters">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="RecordFilters">
                            <select name="Source" class="form-control form-control-sm">
                                <option value="filters"><?= _('Based on filters below..') ?></option>
                                <option
                                    value="cart" <?= (!empty($Source) && $Source == 'cart') ? 'selected' : '' ?>>
                                    <?= _('People in Cart (filters ignored)') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Classification') ?>:</h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#ClassificationFilters"
                                        aria-expanded="false" aria-controls="ClassificationFilters">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body no-padding collapse" id="ClassificationFilters">
                            <select name="Classification[]" size="5" multiple class="form-control form-control-sm">
                                <?php
                                foreach ($ormClassifications as $rsClassification) {
                                    ?>
                                    <option
                                        value="<?= $rsClassification->getOptionID() ?>"><?= $rsClassification->getOptionName() ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Family Role') ?>:</h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#FamilyFilters"
                                        aria-expanded="false" aria-controls="FamilyFilters">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="FamilyFilters">
                            <select name="FamilyRole[]" size="5" multiple class="form-control form-control-sm">
                                <?php
                                foreach ($ormFamilyRoles as $ormFamilyRole) {
                                    ?>
                                    <option
                                        value="<?= $ormFamilyRole->getOptionID() ?>"><?= $ormFamilyRole->getOptionName() ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Gender') ?>:</h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#GenderFilters"
                                        aria-expanded="false" aria-controls="GenderFilters">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="GenderFilters">
                            <select name="Gender" class="form-control form-control-sm">
                                <option value="0"><?= _("Don't Filter") ?></option>
                                <option value="1"><?= _('Male') ?></option>
                                <option value="2"><?= _('Female') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Group Membership') ?>:</h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#GroupFilters"
                                        aria-expanded="false" aria-controls="GroupFilters">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="GroupFilters">
                            <div class="SmallText"><?= _('Use Ctrl Key to select multiple') ?></div>
                            <select name="GroupID[]" size="5" multiple class="form-control form-control-sm">
                                <?php
                                foreach ($groups as $group) {
                                    ?>
                                    <option value="<?= $group->getId() ?>"><?= $group->getName() ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Membership Date') ?>:</h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#MembershipDateFilters"
                                        aria-expanded="false" aria-controls="MembershipDateFilters">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="MembershipDateFilters">
                            <table>
                                <tr>
                                    <td><b><?= _('From:') ?>&nbsp;</b></td>
                                    <td><input id="MembershipDate1" class="date-picker form-control form-control-sm" type="text"
                                               name="MembershipDate1" size="11" maxlength="10"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                    <td><b><?= _('To:') ?>&nbsp;</b></td>
                                    <td><input id="MembershipDate2" class="date-picker form-control form-control-sm" type="text"
                                               name="MembershipDate2" size="11" maxlength="10"
                                               value="<?= date(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Birthday Date') ?>:</h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#BirthdayDateFilter"
                                        aria-expanded="false" aria-controls="BirthdayDateFilter">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="BirthdayDateFilter">
                            <table>
                                <tr>
                                    <td><b><?= _('From:') ?>&nbsp;</b></td>
                                    <td><input type="text" name="BirthDate1" class="date-picker  form-control form-control-sm"
                                               size="11" maxlength="10" id="BirthdayDate1"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                    <td><b><?= _('To:') ?>&nbsp;</b></td>
                                    <td><input type="text" name="BirthDate2" class="date-picker  form-control form-control-sm"
                                               size="11" maxlength="10"
                                               value="<?= date(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                               id="BirthdayDate2"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Anniversary Date:') ?></h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#AnniversaryDateFilter"
                                        aria-expanded="false" aria-controls="AnniversaryDateFilter">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="AnniversaryDateFilter">
                            <table>
                                <tr>
                                    <td><b><?= _('From:') ?>&nbsp;</b></td>
                                    <td><input type="text" class="date-picker  form-control form-control-sm" name="AnniversaryDate1"
                                               size="11" maxlength="10" id="AnniversaryDate1"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                    <td><b><?= _('To:') ?>&nbsp;</b></td>
                                    <td><input type="text" class="date-picker  form-control form-control-sm" name="AnniversaryDate2"
                                               size="11" maxlength="10"
                                               value="<?= date(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                               id="AnniversaryDate2"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header border-1">
                            <h3 class="card-title"><?= _('Date Entered:') ?></h3>
                            <div class="card-tools pull-right">
                                <button class="btn btn-card-tool" type="button" data-toggle="collapse" data-target="#DateEnteredFilter"
                                        aria-expanded="false" aria-controls="DateEnteredFilter">
                                    <i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="card-body no-padding collapse" id="DateEnteredFilter">
                            <table>
                                <tr>
                                    <td><b><?= _('From:') ?>&nbsp;</b></td>
                                    <td><input id="EnterDate1" type="text" name="EnterDate1" size="11"
                                               maxlength="10" class="date-picker  form-control form-control-sm"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                    <td><b><?= _('To:') ?>&nbsp;</b></td>
                                    <td><input id="EnterDate2" type="text" name="EnterDate2" size="11"
                                               maxlength="10"
                                               value="<?= date(SystemConfig::getValue("sDatePickerFormat")) ?>"
                                               class="date-picker  form-control form-control-sm"
                                               placeholder="<?= SystemConfig::getValue("sDatePickerPlaceHolder") ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header border-1">
            <h3 class="card-title"><i class="fas fa-file-export mr-1"></i><?= _('Output Method:') ?></h3>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-lg-4 mb-2 mb-lg-0">
                    <label class="text-muted small text-uppercase mb-1 d-block"><?= _('Export type') ?></label>
                    <select name="Format" class="form-control">
                        <option value="Default"><?= _('CSV Individual Records') ?></option>
                        <option value="Rollup"><?= _('CSV Combine Families') ?></option>
                        <option value="AddToCart"><?= _('Add Individuals to Cart') ?></option>
                    </select>
                </div>
                <div class="col-lg-4 mb-2 mb-lg-0">
                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="csv-skip-incomplete-addr" name="SkipIncompleteAddr" value="1">
                        <label class="custom-control-label" for="csv-skip-incomplete-addr"><?= _('Skip records with incomplete mail address') ?></label>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-right">
                    <button type="submit" class="btn btn-primary px-4" name="Submit"><i class="fas fa-download mr-1"></i><?= _('Create File') ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
