<?php
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\StateDropDown;
use EcclesiaCRM\dto\CountryDropDown;
use EcclesiaCRM\Bootstrapper;

// Set the page title and include HTML header
$sPageTitle = _("Family Registration");
require(SystemURLs::getDocumentRoot() . "/Include/HeaderNotLoggedIn.php");
?>

    <div class="login-box register-box register-box-custom">
        <div class="card card-outline card-success register-box-body blur">
            <div class="card-header register-logo">
                <?php
                $headerHTML = Bootstrapper::getSoftwareName();
                $sHeader = SystemConfig::getValue("sHeader");
                $sEntityName = SystemConfig::getValue("sEntityName");
                if (!empty($sHeader)) {
                    $headerHTML = html_entity_decode($sHeader, ENT_QUOTES);
                } else if (!empty($sEntityName)) {
                    $headerHTML = $sEntityName;
                }
                ?>
                <a href="<?= SystemURLs::getRootPath() ?>/"><?= $headerHTML ?></a>
            </div>
            <div class="card-body">
                <p class="login-box-msg"><?= _('Register your family') ?></p>

                <form action="<?= SystemURLs::getRootPath() ?>/external/register/" method="post">
                    <div class="form-group has-feedback">
                        <div class="input-group mb-3">
                            <input name="familyName" type="text" class= "form-control form-control-sm"
                                   placeholder="<?= _('Family Name') ?>" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <div class="input-group mb-3">
                            <input name="familyAddress1" type="text" class= "form-control form-control-sm"
                                   placeholder="<?= _('Address') ?>" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <div class="row">
                            <div class="col-lg-6">
                                <input name="familyCity" class= "form-control form-control-sm" placeholder="<?= _('City') ?>" required
                                       value="<?= SystemConfig::getValue('sDefaultCity') ?>">
                            </div>
                            <div class="col-lg-6">
                                <!--<input name="familyState" class= "form-control form-control-sm" placeholder="<?= _('State') ?>" required value="<?= SystemConfig::getValue('sDefaultState') ?>">-->
                                <?php
                                $statesDDF = new StateDropDown();
                                echo $statesDDF->getDropDown(SystemConfig::getValue('sDefaultState'), "familyState");
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <div class="row">
                            <div class="col-lg-4">
                                <input name="familyZip" class= "form-control form-control-sm" placeholder="<?= _('Zip') ?>" required>
                            </div>
                            <div class="col-lg-8">
                                <?php
                                $countriesDDF = new CountryDropDown();
                                echo $countriesDDF->getDropDown(SystemConfig::getValue('sDefaultCountry'), "familyCountry");
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <div class="input-group mb-3">
                            <input name="familyHomePhone" class= "form-control form-control-sm" placeholder="<?= _('Home Phone') ?>"
                                   data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormat') ?>"' data-mask>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-phone"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label><?= _('How many people are in your family') ?></label>
                        <select name="familyCount" class= "form-control form-control-sm">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                            <option selected>4</option>
                            <option>5</option>
                            <option>6</option>
                            <option>7</option>
                            <option>8</option>
                        </select>
                    </div>
                    <div class="form-group has-feedback">
                        <hr/>
                    </div>
                    <div class="form-group has-feedback">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input custom-control-input-danger" type="checkbox" name="familyPrimaryChurch" id="familyPrimaryChurch" checked>&nbsp;
                            <label for="familyPrimaryChurch" class="custom-control-label"><?= _('This will be my primary church.') ?></label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-block bg-olive"><?= _('Next'); ?></button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
            </div>
        </div>
        <!-- /.form-box -->
    </div>
    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        $(function() {
            $("#familycountry-input").select2();
            $("#familystate-input").select2();
            $("[data-mask]").inputmask();
        });
    </script>
<?php
// Add the page footer
require(SystemURLs::getDocumentRoot() . "/Include/FooterNotLoggedIn.php");
