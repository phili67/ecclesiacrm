<?php

/*******************************************************************************
 *
 *  filename    : templates/cartToFamily.php
 *  last change : 2023-06-15
 *  description : manage the cart to family
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/


use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Family;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\StateDropDown;
use EcclesiaCRM\dto\CountryDropDown;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Bootstrapper;


// Was the form submitted?
if (isset($_POST['Submit']) && count($_SESSION['aPeopleCart']) > 0) {

    // Get the FamilyID
    $iFamilyID = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');

    // Are we creating a new family
    if ($iFamilyID == 0) {
        $sFamilyName = InputUtils::LegacyFilterInput($_POST['FamilyName']);

        $dWeddingDate = InputUtils::LegacyFilterInput($_POST['WeddingDate']);
        if (strlen($dWeddingDate) > 0) {
            $dWeddingDate = '"' . $dWeddingDate . '"';
        } else {
            $dWeddingDate = null;
        }

        $iPersonAddress = InputUtils::LegacyFilterInput($_POST['PersonAddress']);

        $per_Address1 = null;
        $per_Address2 = null;
        $per_City = null;
        $per_Zip = null;
        $per_Country = null;
        $per_State = null;
        $per_HomePhone = null;
        $per_WorkPhone = null;
        $per_CellPhone = null;
        $per_Email = null;

        if ($iPersonAddress != 0) {
            $person = PersonQuery::Create()->findOneById($iPersonAddress);

            if (!is_null($person)) {
                $per_Address1 = $person->getAddress1();
                $per_Address2 = $person->getAddress2();
                $per_City = $person->getCity();
                $per_Zip = $person->getZip();
                $per_Country = $person->getCountry();
                $per_State = $person->getState();
                $per_HomePhone = $person->getHomePhone();
                $per_WorkPhone = $person->getWorkPhone();
                $per_CellPhone = $person->getCellPhone();
                $per_Email = $person->getEmail();
            }
        }

        MiscUtils::SelectWhichAddress($sAddress1, $sAddress2, InputUtils::LegacyFilterInput($_POST['Address1']), InputUtils::LegacyFilterInput($_POST['Address2']), $per_Address1, $per_Address2, false);
        $sCity = MiscUtils::SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['City']), $per_City);
        $sZip = MiscUtils::SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['Zip']), $per_Zip);
        $sCountry = MiscUtils::SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['Country']), $per_Country);

        if ($sCountry == 'United States' || $sCountry == 'Canada') {
            $sState = InputUtils::LegacyFilterInput($_POST['State']);
        } else {
            $sState = InputUtils::LegacyFilterInput($_POST['StateTextbox']);
        }
        $sState = MiscUtils::SelectWhichInfo($sState, $per_State);

        // Get and format any phone data from the form.
        $sHomePhone = InputUtils::LegacyFilterInput($_POST['HomePhone']);
        $sWorkPhone = InputUtils::LegacyFilterInput($_POST['WorkPhone']);
        $sCellPhone = InputUtils::LegacyFilterInput($_POST['CellPhone']);
        if (!isset($_POST['NoFormat_HomePhone'])) {
            $sHomePhone = MiscUtils::CollapsePhoneNumber($sHomePhone, $sCountry);
        }
        if (!isset($_POST['NoFormat_WorkPhone'])) {
            $sWorkPhone = MiscUtils::CollapsePhoneNumber($sWorkPhone, $sCountry);
        }
        if (!isset($_POST['NoFormat_CellPhone'])) {
            $sCellPhone = MiscUtils::CollapsePhoneNumber($sCellPhone, $sCountry);
        }

        $sHomePhone = MiscUtils::SelectWhichInfo($sHomePhone, $per_HomePhone);
        $sWorkPhone = MiscUtils::SelectWhichInfo($sWorkPhone, $per_WorkPhone);
        $sCellPhone = MiscUtils::SelectWhichInfo($sCellPhone, $per_CellPhone);
        $sEmail = MiscUtils::SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['Email']), $per_Email);

        if (strlen($sFamilyName) == 0) {
            $sError = '<p class="alert alert-warning" class="text-center" style="color:red;">' . _('No family name entered!') . '</p>';
            $bError = true;
        } else {
            $dWeddingDate = InputUtils::parseAndValidateDate($dWeddingDate, Bootstrapper::getCurrentLocale()->getCountryCode(), $pasfut = 'past');

            $fam = new Family();

            $fam->setName($sFamilyName);
            $fam->setAddress1($sAddress1);
            $fam->setAddress1($sAddress2);
            $fam->setCity($sCity);
            $fam->setState($sState);
            $fam->setZip($sZip);
            $fam->setCountry($sCountry);
            $fam->setHomePhone($sHomePhone);
            $fam->setWorkPhone($sWorkPhone);
            $fam->setCellPhone($sCellPhone);
            $fam->setEmail($sEmail);
            $fam->setWeddingdate($dWeddingDate);
            $fam->setDateEntered(date('YmdHis'));
            $fam->setEnteredBy(SessionUser::getUser()->getPersonId());

            $fam->save();

            //Get the key back
            $last = FamilyQuery::create()
                ->addAsColumn('maxId', 'MAX(' . FamilyTableMap::COL_FAM_ID . ')')
                ->findOne();

            $iFamilyID = $last->getMaxId();

        }
    }

    if (!$bError) {
        // Loop through the cart array
        $iCount = 0;
        foreach ($_SESSION['aPeopleCart'] as $element) {
            $iPersonID = intval($element);
            $ormPerson = PersonQuery::Create()
                ->findOneById($iPersonID);

            // Make sure they are not already in a family : ??? I'm not sure this is a good idea ?
            // Here's the way to move
            if ( !is_null($ormPerson) /*&& $ormPerson->getFamId() == 0*/) {
                $iFamilyRoleID = 0;

                if (isset($_POST['role' . $iPersonID])) {
                    $iFamilyRoleID = InputUtils::LegacyFilterInput($_POST['role' . $iPersonID], 'int');
                }

                $ormPerson->setFamId($iFamilyID);
                $ormPerson->setFmrId($iFamilyRoleID);
                $ormPerson->save();

                $iCount++;
            }
        }

        // empty the cart        
        Cart::CleanCart();

        RedirectUtils::Redirect('v2/people/family/view/' . $iFamilyID);
    }
}

// Set the page title and include HTML header
require $sRootDocument . '/Include/Header.php';

echo $sError;
?>
<form method="post" action="<?= $sRootPath ?>/v2/cart/to/family">
    <div class="cart-family-shell">
    <div class="card card-primary card-outline mb-2">
        <div class="card-header border-1 d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-list-check mr-1"></i><?= _("Members") ?></h3>
            <span class="badge badge-secondary"><?= count($_SESSION['aPeopleCart']) ?> <?= _("selected") ?></span>
        </div>
        <div class="card-body py-2">
        <?php
        if (count($_SESSION['aPeopleCart']) > 0) {

        // Get all the families
        $ormFamilies = FamilyQuery::Create()
            ->filterByDateDeactivated(NULL)
            ->filterByName('', \Propel\Runtime\ActiveQuery\Criteria::NOT_EQUAL)
            ->orderByName()
            ->find();

        // Get the family roles
        $ormFamilyRoles = ListOptionQuery::Create()
            ->filterById(2)
            ->orderByOptionSequence()
            ->find();


        $sRoleOptionsHTML = '';
        foreach ($ormFamilyRoles as $ormFamilyRole) {
            $sRoleOptionsHTML .= '<option value="' . $ormFamilyRole->getOptionId() . '">' . $ormFamilyRole->getOptionName() . '</option>';
        }

        $ormCartItems = PersonQuery::Create()
            ->Where('per_ID IN (' . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')')
            ->orderByLastName()
            ->find();
        ?>
        <div class="table-responsive">
        <table id="cart-family-table" class='table table-sm table-hover dt-responsive table-bordered mb-0 w-100'>
            <thead>
                <tr class="print-table-header">
                    <th>&nbsp;</th>
                    <th><b><?= _('Name') ?></b></th>
                    <th class="text-center"><b><?= _('Assign Role') ?></b></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = 1;
                foreach ($ormCartItems as $ormCartItem) {
                ?>
                <tr>
                    <td class="text-center"><?= $count++ ?></td>
                    <td>
                        <?= $ormCartItem->getJPGPhotoDatas() ?><a class="ml-2"
                            href="<?= $sRootPath ?>/v2/people/person/view/<?= $ormCartItem->getId() ?>"><?= OutputUtils::FormatFullName($ormCartItem->getTitle(), $ormCartItem->getFirstName(), $ormCartItem->getMiddleName(), $ormCartItem->getLastName(), $ormCartItem->getSuffix(), 1) ?></a>
                    </td>
                    <td class="text-center">
                        <?php
                        if ($ormCartItem->getFamId() == 0) {
                            ?>
                            <select name="role<?= $ormCartItem->getId() ?>"
                                    class= "form-control form-control-sm"><?= $sRoleOptionsHTML ?></select>
                            <?php
                        } else {
                            ?>
                            <span class="badge badge-light border"><?= _('Already in a family') ?></span>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        </div>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header border-1">
            <h3 class="card-title mb-0"><i class="fas fa-house-user mr-1"></i><?= _("Choose or create a family") ?></h3>
        </div>
        <div class="card-body py-2">
            <div class="alert alert-light border py-2 mb-2">
                <i class="fas fa-circle-info mr-1"></i><?= _('Choose an existing family or create a new one using the fields below.') ?>
            </div>
            <div id="familyModeHint" class="alert alert-info py-2 mb-2">
                <i class="fas fa-wand-magic-sparkles mr-1"></i><?= _('Create new family selected: complete the fields below.') ?>
            </div>
            <div class="form-group row mb-2">
                <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Add to Family') ?>:</label>
                <div class="col-md-8">
                    <select name="FamilyID" class= "form-control select2" id="FamilyID">
                        <option value="0"><?= _('Create new family') ?></option>
                        <option value="—————————————" disabled="disabled">—————————————</option>
                        <?php
                        // Create the family select drop-down
                        foreach ($ormFamilies as $ormFamily) {
                            ?>
                            <option value="<?= $ormFamily->getId() ?>"><?= $ormFamily->getName() ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="family-class alert alert-secondary py-2 mb-2">
                <i class="fas fa-pen mr-1"></i><?= _('If adding a new family, enter data below.') ?>
            </div>

            <div class="card card-outline card-secondary family-class mb-2">
                <div class="card-header py-1">
                    <strong><i class="fas fa-id-card mr-1"></i><?= _('Family Information') ?></strong>
                </div>
                <div class="card-body py-2">
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Family Name') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"> <i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" Name="FamilyName" value="<?= $sName ?>" maxlength="48" class= "form-control form-control-sm">
                            </div>
                            <label color="red"><?= $sNameError ?></label>
                        </div>
                    </div>

                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Wedding Date') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fa-solid fa-calendar-days"></i></span>
                            </div>
                            <input type="text" Name="WeddingDate" value="<?= $dWeddingDate ?>" maxlength="10" id="sel1"
                           size="15"
                           class="form-control active date-picker">
                            </div>
                            <label color="red"><br><?= $sWeddingDateError ?></label>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Use address/contact data from') ?>:</label>
                        <div class="col-md-8">
                            <select name="PersonAddress" class= "form-control select2" id="PersonAddress">
                                <option value="0"><?= _('Only the new data below') ?></option>

                                <?php
                                foreach ($ormCartItems as $ormCartItem) {
                                    if ($ormCartItem->getFamId() == 0) {
                                        ?>
                                        <option
                                            value="<?= $ormCartItem->getId() ?>"><?= $ormCartItem->getFirstName() ?> <?= $ormCartItem->getLastName() ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary family-class mb-2">
                <div class="card-header py-1">
                    <strong><i class="fas fa-location-dot mr-1"></i><?= _('Address') ?></strong>
                </div>
                <div class="card-body py-2">
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Address') ?> 1:</label>
                        <div class="col-md-8">
                            <input type="text" Name="Address1" value="<?= $sAddress1 ?>" size="50" maxlength="250"
                                   class= "form-control form-control-sm">
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Address') ?> 2:</label>
                        <div class="col-md-8">
                            <input type="text" Name="Address2" value="<?= $sAddress2 ?>" size="50" maxlength="250"
                                   class= "form-control form-control-sm">
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('City') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fa-solid fa-city"></i></span>
                            </div>
                            <input type="text" Name="City" value="<?= $sCity ?>" maxlength="50" class= "form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row mb-2 state-class" <?= (SystemConfig::getValue('bStateUnusefull')) ? 'style="display: none;"' : "" ?>>
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('State') ?>:</label>
                        <div class="col-md-8">
                            <?php
                            $statesDD = new StateDropDown();
                            echo $statesDD->getDropDown($sState, "State", "");
                            ?>
                            <?= _("OR") ?>
                            <input class= "form-control form-control-sm" type="text" name="StateTextbox"
                                   value="<?php if ($sCountry != 'United States' && $sCountry != 'Canada') {
                                       echo $sState;
                                   } ?>" size="20" maxlength="30">
                            <small class="text-muted"><?= _('(Use the textbox for countries other than US and Canada)') ?></small>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Zip') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fa-solid fa-city"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" Name="Zip" value="<?= $sZip ?>" maxlength="10" size="8">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Country') ?>:</label>
                        <div class="col-md-8">
                            <?= CountryDropDown::getDropDown($sCountry, "Country", ""); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary family-class mb-0">
                <div class="card-header py-1">
                    <strong><i class="fas fa-address-book mr-1"></i><?= _('Contact') ?></strong>
                </div>
                <div class="card-body py-2">
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Home Phone') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" Name="HomePhone" value="<?= $sHomePhone ?>" size="30"
                           maxlength="30" data-inputmask="'mask': '<?= SystemConfig::getValue('sPhoneFormat') ?>'"
                           data-mask>
                            </div>
                            <input type="checkbox" name="NoFormat_HomePhone" value="1" <?php if ($bNoFormat_HomePhone) {
                                echo ' checked';
                            } ?>> <small class="text-muted"><?= _('Do not auto-format') ?></small>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Work Phone') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" name="WorkPhone" value="<?php echo $sWorkPhone ?>" size="30"
                           maxlength="30" data-inputmask="'mask': '<?= SystemConfig::getValue('sPhoneFormat') ?>'"
                           data-mask>
                            </div>
                            <input type="checkbox" name="NoFormat_WorkPhone" value="1" <?php if ($bNoFormat_WorkPhone) {
                                echo ' checked';
                            } ?>> <small class="text-muted"><?= _('Do not auto-format') ?></small>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Mobile Phone') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" name="CellPhone" value="<?php echo $sCellPhone ?>" size="30"
                           maxlength="30" data-inputmask="'mask': '<?= SystemConfig::getValue('sPhoneFormat') ?>'"
                           data-mask>
                            </div>
                            <input type="checkbox" name="NoFormat_CellPhone" value="1" <?php if ($bNoFormat_CellPhone) {
                                echo ' checked';
                            } ?>> <small class="text-muted"><?= _('Do not auto-format') ?></small>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-md-4 col-form-label col-form-label-sm"><?= _('Email') ?>:</label>
                        <div class="col-md-8">
                            <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="far fa-envelope"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" Name="Email" value="<?= $sEmail ?>" size="30"
                           maxlength="50">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer py-2">
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary" name="Submit"><i class="fas fa-plus mr-1"></i><?= _('Add to Family') ?></button>
            </div>
            <?php
            } else {
            ?>
                <div class="alert alert-warning py-2 mb-0">
                    <p class="text-center mb-0"><i class="fas fa-cart-shopping mr-1"></i><?= _('Your cart is empty!') ?></p>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
</form>



<script nonce="<?= \EcclesiaCRM\dto\SystemURLs::getCSPNonce() ?>">
    var bStateUnusefull = <?= (SystemConfig::getValue('bStateUnusefull')) ?'true':'false' ?>;
    var createFamilyHint = "<?= addslashes(_('Create new family selected: complete the fields below.')) ?>";
    var existingFamilyHint = "<?= addslashes(_('Existing family selected: detailed fields are hidden.')) ?>";

    $(function() {
        $("#country-input").select2();
        $("#state-input").select2();
        $("#FamilyID").select2();
        $("#PersonAddress").select2();
        $("#Country").select2();
        $("#State").select2();

        $(function () {
            $("[data-mask]").inputmask();
        });


        $("#cart-family-table").DataTable({
            responsive: true,
            paging: false,
            searching: false,
            ordering: false,
            info: false,
            autoWidth: false,
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 2 },
                { responsivePriority: 3, targets: 0 }
            ]
        });

        function toggleFamilyFields() {
            var e = document.getElementById("FamilyID");
            var res = e.options[e.selectedIndex].value;
            var isCreateMode = (res === "0");

            $('.family-class').each(function(index, value) {
                if (!isCreateMode) {
                    $(this).hide();

                } else {
                    $(this).show();

                    if (bStateUnusefull == true) {
                        $(".state-class").hide();
                    } else {
                        $(".state-class").show();
                    }

                }
            });

            if (isCreateMode) {
                $('#familyModeHint').removeClass('alert-secondary').addClass('alert-info').html('<i class="fas fa-wand-magic-sparkles mr-1"></i>' + createFamilyHint);
            } else {
                $('#familyModeHint').removeClass('alert-info').addClass('alert-secondary').html('<i class="fas fa-check-circle mr-1"></i>' + existingFamilyHint);
            }
        }

        $("#FamilyID").on('change', toggleFamilyFields);
        toggleFamilyFields();
    });
</script>
    
<?php require $sRootDocument . '/Include/Footer.php'; ?>