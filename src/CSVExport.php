<?php
/*******************************************************************************
 *
 *  filename    : CSVExport.php
 *  description : options for creating csv file
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2002 Phillip Hullquist, Deane Barker
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\SessionUser;


// If user does not have CSV Export permission, redirect to the menu.
if (!SessionUser::getUser()->isCSVExportEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

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
require 'Include/Header.php';
?>
<form method="post" action="<?= SystemURLs::getRootPath() ?>/CSVCreateFile.php">
    <div class="card">
        <div class="card-header ">
            <h3 class="card-title"><?= _('Field Selection') ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label><?= _('Last Name') ?>:</label>
                    <?= _('Required') ?>
                </div>

                <div class="col-md-4">
                    <label><?= _('Title') ?>:</label>
                    <input type="checkbox" name="Title" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('First Name') ?>:</label>
                    <input type="checkbox" name="FirstName" value="1" checked>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label><?= _('Middle Name') ?>:</label>
                    <input type="checkbox" name="MiddleName" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Suffix') ?>:</label>
                    <input type="checkbox" name="Suffix" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Address') ?> 1:</label>
                    <input type="checkbox" name="Address1" value="1" checked>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label><?= _('Address') ?> 2:</label>
                    <input type="checkbox" name="Address2" value="1" checked>
                </div>

                <div class="col-md-4">
                    <label><?= _('City') ?>:</label>
                    <input type="checkbox" name="City" value="1" checked>
                </div>

                <div class="col-md-4">
                    <label><?= _('State') ?>:</label>
                    <input type="checkbox" name="State" value="1" checked>
                </div>
            </div>
            <div class="row">

                <div class="col-md-4">
                    <label><?= _('Zip') ?>:</label>
                    <input type="checkbox" name="Zip" value="1" checked>
                </div>

                <div class="col-md-4">
                    <label><?= _('Envelope') ?>:</label>
                    <input type="checkbox" name="Envelope" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Country') ?>:</label>
                    <input type="checkbox" name="Country" value="1" checked>
                </div>
            </div>
            <div class="row">

                <div class="col-md-4">
                    <label><?= _('Home Phone') ?>:</label>
                    <input type="checkbox" name="HomePhone" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Work Phone') ?>:</label>
                    <input type="checkbox" name="WorkPhone" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Mobile Phone') ?>:</label>
                    <input type="checkbox" name="CellPhone" value="1">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label><?= _('Email') ?>:</label>
                    <input type="checkbox" name="Email" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Work/Other Email') ?>:</label>
                    <input type="checkbox" name="WorkEmail" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Membership Date') ?>:</label>
                    <input type="checkbox" name="MembershipDate" value="1">
                </div>
            </div>
            <div class="row">

                <div class="col-md-4">
                    <label>* <?= _('Birth / Anniversary Date') ?>:</label>
                    <input type="checkbox" name="BirthdayDate" value="1">
                </div>

                <div class="col-md-4">
                    <label>* <?= _('Age / Years Married') ?>:</label>
                    <input type="checkbox" name="Age" value="1">
                </div>

                <div class="col-md-4">
                    <label><?= _('Classification') ?>:</label>
                    <input type="checkbox" name="PrintMembershipStatus" value="1">
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label><?= _('Family Role') ?>:</label>
                        <input type="checkbox" name="PrintFamilyRole" value="1">
                        <br><br>
                        * <?= _('Depends whether using person or family output method') ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <?php
    if ($numCustomFields > 0 || $numFamCustomFields > 0) {
        ?>
        <div class="card">
            <div class="card-header ">
                <h3 class="card-title"><?= _('Custom Field Selection') ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <?php
                        if ($numCustomFields > 0) {
                            ?>
                            <h3><?= _('Custom Person Fields') ?></h3>
                            <table cellpadding="4" align="left">
                                <?php
                                // Display the custom fields
                                foreach ($customFields as $customField) {
                                    if (OutputUtils::securityFilter($customField->getCustomFieldSec())) {
                                        ?>
                                        <tr>
                                            <td class="LabelColumn"><?= $customField->getCustomName() ?></td>
                                            <td class="TextColumn"><input type="checkbox"
                                                                          name="<?= $customField->getCustomField() ?>"
                                                                          value="1"></td>
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
                            <h3><?= _('Custom Family Fields') ?></h3>
                            <table cellpadding="4" align="left">
                                <?php
                                // Display the family custom fields
                                foreach ($famCustomFields as $famCustomField) {
                                    if (OutputUtils::securityFilter($famCustomField->getCustomFieldSec())) {
                                        ?>
                                        <tr>
                                            <td class="LabelColumn"><?= $famCustomField->getCustomName() ?></td>
                                            <td class="TextColumn"><input type="checkbox"
                                                                          name="<?= $famCustomField->getCustomField() ?>"
                                                                          value="1"></td>
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

    <div class="card card-warning">
        <div class="card-header ">
            <h3 class="card-title"><?= _('Filters') . ' (' . _('Ignored if you come from the CartView') . ')' ?></h3>
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
                        <div class="card-header ">
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
                                    value="cart" <?= (array_key_exists('Source', $_GET) && $_GET['Source'] == 'cart') ? 'selected' : '' ?>>
                                    <?= _('People in Cart (filters ignored)') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card  collapsed-box">
                        <div class="card-header ">
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
                        <div class="card-header ">
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
                        <div class="card-header ">
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
                        <div class="card-header ">
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
                        <div class="card-header ">
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
                        <div class="card-header ">
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
                        <div class="card-header ">
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
                        <div class="card-header ">
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

    <div class="card card-success">
        <div class="card-header ">
            <h3 class="card-title"><?= _('Output Method:') ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <select name="Format" class="form-control form-control-sm">
                        <option value="Default"><?= _('CSV Individual Records') ?></option>
                        <option value="Rollup"><?= _('CSV Combine Families') ?></option>
                        <option value="AddToCart"><?= _('Add Individuals to Cart') ?></option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label><?= _('Skip records with incomplete mail address') ?></label><input
                        type="checkbox"
                        name="SkipIncompleteAddr"
                        value="1">
                </div>
                <div class="col-lg-5">
                    <input type="submit" class="btn btn-primary"
                           value=<?= '"' . _('Create File') . '"' ?> name="Submit">
                </div>
            </div>
        </div>
    </div>
</form>

<?php require 'Include/Footer.php' ?>
