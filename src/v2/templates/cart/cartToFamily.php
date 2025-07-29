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
use EcclesiaCRM\dto\SystemURLs;
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
    <div class="card">
        <div class="card-header  border-1">
            <h3 class="card-title"><label><?= _("Members") ?></label></h3>
        </div>
        <div class="card-body">
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
        <table class='table table-hover dt-responsive table-bordered'>
            <tr class="print-table-header">
                <td>&nbsp;</td>
                <td><b><?= _('Name') ?></b></td>
                <td class="text-center"><b><?= _('Assign Role') ?></b></td>

                <?php
                $count = 1;
                foreach ($ormCartItems

                as $ormCartItem) {
                ?>
            <tr>
                <td class="text-center"><?= $count++ ?></td>
                <td>
                    <?= $ormCartItem->getJPGPhotoDatas() ?> &nbsp <a
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
                        <?= _('Already in a family') ?>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
            }
            ?>
        </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header  border-1">
            <h3 class="card-title"><label><?= _("Choose or create a family") ?></label></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label><?= _('Add to Family') ?>:</label>
                </div>
                <div class="col-md-6">
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
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">

                </div>
                <div class="col-md-6">
                    <p class="MediumLargeText"><?= _('If adding a new family, enter data below.') ?></p>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Family Name') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" Name="FamilyName" value="<?= $sName ?>" maxlength="48" class= "form-control form-control-sm">
                    </div>                     
                    <label color="red"><?= $sNameError ?></label>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Wedding Date') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fa-solid fa-calendar-days"></i></span>
                            </div>
                            <input type="text" Name="WeddingDate" value="<?= $dWeddingDate ?>" maxlength="10" id="sel1"
                           size="15"
                           class="form-control active date-picker">
                    </div> 
                    <label color="red"><BR><?= $sWeddingDateError ?></label>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Use address/contact data from') ?>:</label>
                </div>
                <div class="col-md-6">
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
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Address') ?> 1:</label>
                </div>
                <div class="col-md-6">
                    <input type="text" Name="Address1" value="<?= $sAddress1 ?>" size="50" maxlength="250"
                           class= "form-control form-control-sm">
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Address') ?> 2:</label>
                </div>
                <div class="col-md-6">
                    <input type="text" Name="Address2" value="<?= $sAddress2 ?>" size="50" maxlength="250"
                           class= "form-control form-control-sm">
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('City') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fa-solid fa-city"></i></span>
                            </div>
                            <input type="text" Name="City" value="<?= $sCity ?>" maxlength="50" class= "form-control form-control-sm">
                    </div>                     
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class state-class" <?= (SystemConfig::getValue('bStateUnusefull')) ? 'style="display: none;"' : "" ?>>
                <div class="col-md-6">
                    <label><?= _('State') ?>:</label>
                </div>
                <div class="col-md-6">
                    <?php
                    $statesDD = new StateDropDown();
                    echo $statesDD->getDropDown($sState, "State", "");
                    ?>
                    <?= _("OR") ?>
                    <input class= "form-control form-control-sm" type="text" name="StateTextbox"
                           value="<?php if ($sCountry != 'United States' && $sCountry != 'Canada') {
                               echo $sState;
                           } ?>" size="20" maxlength="30">
                    <BR><?= _('(Use the textbox for countries other than US and Canada)') ?>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Zip') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fa-solid fa-city"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" Name="Zip" value="<?= $sZip ?>" maxlength="10" size="8">
                    </div>                     
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Country') ?>:</label>
                </div>
                <div class="col-md-6">
                    <?= CountryDropDown::getDropDown($sCountry, "Country", ""); ?>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Home Phone') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" Name="HomePhone" value="<?= $sHomePhone ?>" size="30"
                           maxlength="30" data-inputmask="'mask': '<?= SystemConfig::getValue('sPhoneFormat') ?>'"
                           data-mask>
                    </div>                    
                    <input type="checkbox" name="NoFormat_HomePhone" value="1" <?php if ($bNoFormat_HomePhone) {
                        echo ' checked';
                    } ?>><?= _('Do not auto-format') ?>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Work Phone') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" name="WorkPhone" value="<?php echo $sWorkPhone ?>" size="30"
                           maxlength="30" data-inputmask="'mask': '<?= SystemConfig::getValue('sPhoneFormat') ?>'"
                           data-mask>
                    </div>    
                    
                    <input type="checkbox" name="NoFormat_WorkPhone" value="1" <?php if ($bNoFormat_WorkPhone) {
                        echo ' checked';
                    } ?>><?= _('Do not auto-format') ?>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Mobile Phone') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="fas fa-phone"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" name="CellPhone" value="<?php echo $sCellPhone ?>" size="30"
                           maxlength="30" data-inputmask="'mask': '<?= SystemConfig::getValue('sPhoneFormat') ?>'"
                           data-mask>
                    </div>                     
                    <input type="checkbox" name="NoFormat_CellPhone" value="1" <?php if ($bNoFormat_CellPhone) {
                        echo ' checked';
                    } ?>><?= _('Do not auto-format') ?>
                </div>
            </div>
            <br class="family-class" />
            <div class="row family-class">
                <div class="col-md-6">
                    <label><?= _('Email') ?>:</label>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> <i class="far fa-envelope"></i></span>
                            </div>
                            <input class= "form-control form-control-sm" type="text" Name="Email" value="<?= $sEmail ?>" size="30"
                           maxlength="50">
                    </div> 
                    
                </div>
            </div>
        </div>

        <div class="card-footer">
            <p class="text-center">
                <input type="submit" class="btn btn-primary" name="Submit" value="&#x2b;  <?= _('Add to Family') ?>">
            </p>
            <?php
            } else {
            ?>
                <div class="alert alert-warning">
                    <p class="text-center"><?= _('Your cart is empty!') ?></p>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</form>



<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    var bStateUnusefull = <?= (SystemConfig::getValue('bStateUnusefull')) ?'true':'false' ?>;

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
            //dom: window.CRM.plugin.dataTable.dom,
            fnDrawCallback: function (settings) {
                $("#selector thead").remove();
            }
        });

        $( "#FamilyID" ).on('change',function() {
            var e = document.getElementById("FamilyID");
            var res = e.options[e.selectedIndex].value;

            $('.family-class').each(function(index, value) {
                if (res !== "0") {
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
        });
    });
</script>
    
<?php require $sRootDocument . '/Include/Footer.php'; ?>