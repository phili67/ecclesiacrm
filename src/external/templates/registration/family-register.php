<?php
use EcclesiaCRM\data\Countries;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\StateDropDown;
use EcclesiaCRM\dto\CountryDropDown;

// Set the page title and include HTML header
$sPageTitle = gettext("Family Registration");
require(SystemURLs::getDocumentRoot(). "/Include/HeaderNotLoggedIn.php");
?>

  <div class="register-box" style="width: 600px;">
    <div class="register-logo">
      <?php
        $headerHTML = '<b>Ecclesia</b>CRM';
        $sHeader = SystemConfig::getValue("sHeader");
        $sChurchName = SystemConfig::getValue("sChurchName");
        if (!empty($sHeader)) {
          $headerHTML = html_entity_decode($sHeader, ENT_QUOTES);
        } else if(!empty($sChurchName)) {
            $headerHTML = $sChurchName;
        }
      ?>
      <a href="<?= SystemURLs::getRootPath() ?>/"><?= $headerHTML ?></a>
    </div>

    <div class="register-box-body">
      <p class="login-box-msg"><?= gettext('Register your family') ?></p>

      <form action="<?= SystemURLs::getRootPath() ?>/external/register/" method="post">
        <div class="form-group has-feedback">
          <input name="familyName" type="text" class="form-control" placeholder="<?= gettext('Family Name') ?>" required>
          <span class="fa fa-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <input name="familyAddress1" class="form-control" placeholder="<?= gettext('Address') ?>" required>
          <span class="fa fa-envelope form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <div class="row">
            <div class="col-lg-6">
              <input name="familyCity" class="form-control" placeholder="<?= gettext('City') ?>" required value="<?= SystemConfig::getValue('sDefaultCity') ?>">
            </div>
            <div class="col-lg-6">
              <!--<input name="familyState" class="form-control" placeholder="<?= gettext('State') ?>" required value="<?= SystemConfig::getValue('sDefaultState') ?>">-->
              <?php                          
                  $statesDDF = new StateDropDown();
                  echo $statesDDF->getDropDown(SystemConfig::getValue('sDefaultState'),"familyState");
              ?>
            </div>
          </div>
        </div>
        <div class="form-group has-feedback">
          <div class="row">
            <div class="col-lg-4">
              <input name="familyZip" class="form-control" placeholder="<?= gettext('Zip') ?>" required>
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
          <input name="familyHomePhone" class="form-control" placeholder="<?= gettext('Home Phone') ?>" data-inputmask='"mask": "<?= SystemConfig::getValue('sPhoneFormat')?>"' data-mask>
          <span class="fa fa-phone form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <label><?= gettext('How many people are in your family') ?></label>
          <select name="familyCount" class="form-control">
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
          <div class="checkbox">
            <label>
              <input type="checkbox" name="familyPrimaryChurch" checked>&nbsp;
              <?= gettext('This will be my primary church.') ?>
            </label>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 text-center">
            <button type="submit" class="btn bg-olive"><?= gettext('Next'); ?></button>
          </div>
          <!-- /.col -->
        </div>
      </form>
    </div>
    <!-- /.form-box -->
  </div>
    <script nonce="<?= SystemURLs::getCSPNonce() ?>" >
        $(document).ready(function() {
            $("#familycountry-input").select2();
            $("#familystate-input").select2();
            $("[data-mask]").inputmask();
        });
    </script>
<?php
// Add the page footer
require(SystemURLs::getDocumentRoot(). "/Include/FooterNotLoggedIn.php");
