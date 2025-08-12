<?php
/*******************************************************************************
 *
 *  filename    : depositslipeditor.php
 *  description : menu that appears after login, shows login attempts
 *
 *  http://www.ecclesiacrm.com/
 *
 *  2023 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\AutoPayment;
use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\SessionUser;

//Get Family name
if ($iFamily) {
    $ormFamily = FamilyQuery::Create()->findOneById($iFamily);
    $fam_Name = $ormFamily->getName();
} else {
    $fam_Name = 'TBD';
}

if (!is_null($ormFamily)) {
  $onePersonFamily = (count($ormFamily->getPeople()) == 1)?true:false;

  $fam_Address1  = $ormFamily->getAddress1();
  $fam_Address2  = $ormFamily->getAddress2();
  $fam_City      = $ormFamily->getCity();
  $fam_State     = $ormFamily->getState();
  $fam_Zip       = $ormFamily->getZip();
  $fam_Country   = $ormFamily->getCountry();
  $fam_HomePhone = $ormFamily->getHomePhone();
  $fam_Email     = $ormFamily->getEmail();
} else {
  $onePersonFamily = true;
}

if ($iAutID <= 0) {  // Need to create the record so there is a place to store the payment handle
    $dNextPayDate = date('Y-m-d');
    $tFirstName   = '';
    $tLastName    = '';
    $tAddress1    = $fam_Address1;
    $tAddress2    = $fam_Address2;
    $tCity        = $fam_City;
    $tState       = $fam_State;
    $tZip         = $fam_Zip;
    $tCountry     = $fam_Country;
    $tPhone       = $fam_HomePhone;
    $tEmail       = $fam_Email;
    $iInterval    = 1;
    $iFund        = 1;

    $bEnableBankDraft = 0;
    $bEnableCreditCard = 0;

    // Default to the current fiscal year ID
    $FYID = MiscUtils::CurrentFY();
    $iFYID = $FYID;

    $tCreditCard      = '';
    $tExpMonth        = '';
    $tExpYear         = '';
    $tBankName        = '';
    $tRoute           = '';
    $tAccount         = '';
    
    $nAmount = 0;

    if ($iFamily > 0) {
      $autoPayment = new AutoPayment();

      if ($iFund == 0) {
        $iFund = null;
      }

      $autoPayment->setFamilyid($iFamily);
      $autoPayment->setEnableBankDraft($bEnableBankDraft);
      $autoPayment->setEnableCreditCard($bEnableCreditCard);
      $autoPayment->setNextPayDate($dNextPayDate);
      $autoPayment->setFyid($iFYID);
      $autoPayment->setAmount($nAmount);
      $autoPayment->setInterval($iInterval);
      $autoPayment->setFund($iFund);
      $autoPayment->setFirstName($tFirstName);
      $autoPayment->setLastName($tLastName);
      $autoPayment->setAddress1($tAddress1);
      $autoPayment->setAddress2($tAddress2);
      $autoPayment->setCity($tCity);
      $autoPayment->setState($tState);
      $autoPayment->setZip($tZip);
      $autoPayment->setCountry($tCountry);
      $autoPayment->setPhone($tPhone);
      $autoPayment->setEmail($tEmail);
      $autoPayment->setCreditCard($tCreditCard);
      $autoPayment->setExpMonth($tExpMonth);
      $autoPayment->setExpYear($tExpYear);
      $autoPayment->setBankName($tBankName);
      $autoPayment->setRoute($tRoute);
      $autoPayment->setAccount($tAccount);
      $autoPayment->setSerial(1);
      $autoPayment->setDateLastEdited(date('YmdHis'));
      $autoPayment->getEditedby(SessionUser::getUser()->getPersonId());


      $autoPayment->save();

      $iAutID = $autoPayment->getId();
    }
}

//Is this the second pass?
if (isset($_POST['Submit'])) {
    $iFamily = InputUtils::LegacyFilterInput($_POST['Family']);

    if ($iFamily == 0) {
        RedirectUtils::Redirect($sRootPath."/".$iAutID."/".$iFamily."/".$origLinkBack);
    }

    $enableCode = InputUtils::LegacyFilterInput($_POST['EnableButton']);
    $bEnableBankDraft = ($enableCode == 1);
    if (!$bEnableBankDraft) {
        $bEnableBankDraft = 0;
    }
    $bEnableCreditCard = ($enableCode == 2);
    if (!$bEnableCreditCard) {
        $bEnableCreditCard = 0;
    }

    $dNextPayDate = InputUtils::FilterDate($_POST['NextPayDate']);
    $nAmount = InputUtils::LegacyFilterInput($_POST['Amount']);
    if (!$nAmount) {
        $nAmount = 0;
    }

    $iFYID     = InputUtils::LegacyFilterInput($_POST['FYID']);

    $iInterval = InputUtils::FilterInt($_POST['Interval']);
    $iFund     = InputUtils::FilterInt($_POST['Fund']);

    $tFirstName = InputUtils::FilterString($_POST['FirstName']);
    $tLastName  = InputUtils::FilterString($_POST['LastName']);

    $tAddress1 = InputUtils::FilterString($_POST['Address1']);
    $tAddress2 = InputUtils::FilterString($_POST['Address2']);
    $tCity     = InputUtils::FilterString($_POST['City']);
    $tState    = InputUtils::FilterString($_POST['State']);
    $tZip      = InputUtils::LegacyFilterInput($_POST['Zip']);
    $tCountry  = InputUtils::FilterString($_POST['Country']);
    $tPhone    = InputUtils::LegacyFilterInput($_POST['Phone']);
    $tEmail    = InputUtils::LegacyFilterInput($_POST['Email']);

    $tCreditCard = InputUtils::LegacyFilterInput($_POST['CreditCard']);
    $tExpMonth = InputUtils::LegacyFilterInput($_POST['ExpMonth']);
    $tExpYear = InputUtils::LegacyFilterInput($_POST['ExpYear']);

    $tBankName = InputUtils::FilterString($_POST['BankName']);
    $tRoute    = InputUtils::LegacyFilterInput($_POST['Route']);
    $tAccount  = InputUtils::LegacyFilterInput($_POST['Account']);

    $autoPayment = AutoPaymentQuery::Create()->findOneById($iAutID);

    if ($iFund == 0) {
      $iFund = null;
    }

    $autoPayment->setFamilyid($iFamily);
    $autoPayment->setEnableBankDraft($bEnableBankDraft);
    $autoPayment->setEnableCreditCard($bEnableCreditCard);
    $autoPayment->setNextPayDate($dNextPayDate);
    $autoPayment->setFyid($iFYID);
    $autoPayment->setAmount($nAmount);
    $autoPayment->setInterval($iInterval);
    $autoPayment->setFund($iFund);
    $autoPayment->setFirstName($tFirstName);
    $autoPayment->setLastName($tLastName);
    $autoPayment->setAddress1($tAddress1);
    $autoPayment->setAddress2($tAddress2);
    $autoPayment->setCity($tCity);
    $autoPayment->setState($tState);
    $autoPayment->setZip($tZip);
    $autoPayment->setCountry($tCountry);
    $autoPayment->setPhone($tPhone);
    $autoPayment->setEmail($tEmail);
    $autoPayment->setCreditCard($tCreditCard);
    $autoPayment->setExpMonth($tExpMonth);
    $autoPayment->setExpYear($tExpYear);
    $autoPayment->setBankName($tBankName);
    $autoPayment->setRoute($tRoute);
    $autoPayment->setAccount($tAccount);
    $autoPayment->setDateLastEdited(date('YmdHis'));
    $autoPayment->getEditedby(SessionUser::getUser()->getPersonId());

    $autoPayment->save();

    if (isset($_POST['Submit'])) {
        // Check for redirection to another page after saving information:
        if ($linkBack == "v2/deposit/autopayment/editor/".$iAutID."/".$iFamily."/".$origLinkBack) {
          RedirectUtils::Redirect($linkBack);
        } else if ($linkBack != '') {
          $ormFamily = FamilyQuery::Create()->findOneById($iFamily);
          if (!is_null ($ormFamily)) {
            $people = $ormFamily->getActivatedPeople();
            if (!empty($people) && count($people) == 1) {
               $personId = -1;

               foreach ($people as $person) {
                 $personId = $person->getId();
               }

               /*if ($personId > 0) {
                  RedirectUtils::Redirect("v2/people/person/view/".$personId);
               }*/
            }
          }
          RedirectUtils::Redirect($linkBack);
        } else {
            //Send to the view of this pledge
            RedirectUtils::Redirect($sRootPath."/".$iAutID."/".$iFamily."/".$origLinkBack);
        }
    }
} else if (isset($_POST['Cancel'])) {
    RedirectUtils::Redirect($linkBack);
} else if ($iAutID > 0) {// not submitting, just get ready to build the page
    $autoPayment = AutoPaymentQuery::Create()->findOneById($iAutID);

    $iFamily = $autoPayment->getFamilyid();
    $bEnableBankDraft = $autoPayment->getEnableBankDraft();
    $bEnableCreditCard = $autoPayment->getEnableCreditCard();
    $dNextPayDate = $autoPayment->getNextPayDate()->format('Y-m-d');
    $iFYID = $autoPayment->getFyid();
    $nAmount = $autoPayment->getAmount();
    $iInterval = $autoPayment->getInterval();
    $iFund = $autoPayment->getFund();
    $tFirstName = $autoPayment->getFirstName();
    $tLastName = $autoPayment->getLastName();
    $tAddress1 = $autoPayment->getAddress1();
    $tAddress2 = $autoPayment->getAddress2();
    $tCity = $autoPayment->getCity();
    $tState = $autoPayment->getState();
    $tZip = $autoPayment->getZip();
    $tCountry = $autoPayment->getCountry();
    $tPhone = $autoPayment->getPhone();
    $tEmail = $autoPayment->getEmail();
    $tCreditCard = $autoPayment->getCreditCard();
    $tExpMonth = $autoPayment->getExpMonth();
    $tExpYear = $autoPayment->getExpYear();
    $tBankName = $autoPayment->getBankName();
    $tRoute = $autoPayment->getRoute();
    $tAccount = $autoPayment->getAccount();
}

// we place this part to avoid a problem during the upgrade process
// Set the page title
require $sRootDocument . '/Include/Header.php';


//Get Families for the drop-down
$ormFamilies = FamilyQuery::Create()->orderByName()->find();

// Get the list of funds
$ormFunds = DonationFundQuery::Create()->findByActive('true');
?>

<form method="post"  style="padding:10px"
          action="<?= $sRootPath ?>/v2/deposit/autopayment/editor/<?= $iAutID ?>/<?=  $iFamily ?>/<?= $origLinkBack ?>"
          name="AutoPaymentEditor">
<div class="card card-info">
  <div class="card-header border-1">
    <h3 class="card-title"><?= _("For the").' '.(($onePersonFamily == true)?_('Person'):_('Family'))?> : <?=  $fam_Name ?></h3>
  </div>
  <div class="card-body">    
          <div class="row">
            <div class="col-md-3">
                <label><?= _('Person').' '._('or').' '._('Family') ?>:</label>
            </div>
            <div class="col-md-4">
                <div class="alert alert-danger"><?= _("WARNING ! You've to select a person or a family to create an auto-payment.") ?></div>
                <select name="Family" id="optionFamily" style="width:100%">
                   <option value="0" selected><?= _('Unassigned') ?></option>
                   <option value="0">-----------------------</option>

                <?php
                  foreach ($ormFamilies as $family) {
                ?>
                    <option value="<?= $family->getId() ?>" <?= ($iFamily == $family->getId())?' selected':'' ?>> <?= $family->getName() . '&nbsp;' . MiscUtils::FormatAddressLine($family->getAddress1(), $family->getCity(), $family->getState()) ?>
                <?php
                   }
                ?>

                </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
                <label><?= _('Automatic payment type') ?></label>
            </div>
            <div class="col-md-9">
               <input type="radio" Name="EnableButton" value="1" id="EnableBankDraft"<?php if ($bEnableBankDraft) {
                                        echo ' checked';
                                    } ?>> <?= _("Bank Draft ") ?>
               <input type="radio" Name="EnableButton" value="2"
                                       id="EnableCreditCard" <?php if ($bEnableCreditCard) {
                                        echo ' checked';
                                    } ?>> <?= _("Credit Card ") ?>
               <input type="radio" Name="EnableButton" value="3"
                                       id="Disable" <?php if ((!$bEnableBankDraft) && (!$bEnableCreditCard)) {
                                        echo ' checked';
                                    } ?>> <?= _("Disable ") ?>
              </div>
           </div>
           <div class="row">
             <div class="col-md-3">
                 <label><?= _('Date') ?>:</label>
             </div>
             <div class="col-md-4">
                 <input type="text" name="NextPayDate" value="<?= OutputUtils::change_date_for_place_holder($dNextPayDate) ?>"
                                                          maxlength="10" id="NextPayDate" size="11"
                                                          class="form-control pull-right active date-picker">
             </div>
          </div>
           <div class="row">
             <div class="col-md-3">
                <label><?= _('Fiscal Year') ?>:</label>
             </div>
             <div class="col-md-4">
                <?php MiscUtils::PrintFYIDSelect($iFYID, 'FYID') ?>
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                  <label><?= _('Payment amount') ?></label>
             </div>
             <div class="col-md-4">
                  <input type="number" step="any" name="Amount" value="<?= $nAmount ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Payment interval (months)') ?></label>
             </div>
             <div class="col-md-4">
               <input type="text" name="Interval" value="<?= $iInterval ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Fund') ?>:</label>
             </div>
             <div class="col-md-4">
                  <select name="Fund" class= "form-control form-control-sm">
                      <option value="0"><?= _('None') ?></option>
                      <?php
                      foreach ($ormFunds as $fund) {
                          ?>
                          <option value="<?= $fund->getId()?>" <?= (($iFund == $fund->getId())?' selected':'') ?>>
                            <?= $fund->getName().(($fund->getActive() != 'true')?' (' . _('inactive') . ')':'') ?>
                          </option>
                      <?php
                      }
                      ?>
                  </select>
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('First Name') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="FirstName" name="FirstName" value="<?= $tFirstName ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Last Name') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="LastName" name="LastName" value="<?= $tLastName ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Address') ?> 1</label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="Address1" name="Address1" value="<?= $tAddress1 ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('Address') ?> 2</label>
             </div>
             <div class="col-md-4">
                <input type="text" id="Address2" name="Address2" value="<?= $tAddress2 ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('City') ?></label>
             </div>
             <div class="col-md-4">
                <input type="text" id="City" name="City" value="<?= $tCity ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('State') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="State" name="State" value="<?= $tState ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Zip') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="Zip" name="Zip" value="<?= $tZip ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Country') ?></label>
             </div>
             <div class="col-md-4">
                <input type="text" id="Country" name="Country" value="<?= $tCountry ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('Phone') ?></label>
             </div>
             <div class="col-md-4">
                <input type="text" id="Phone" name="Phone" value="<?= $tPhone ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('Email') ?></label>
             </div>
             <div class="col-md-4">
                <input type="text" id="Email" name="Email" value="<?= $tEmail ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('Credit Card') ?></label>
             </div>
             <div class="col-md-4">
                <input type="text" id="CreditCard" name="CreditCard" value="<?= $tCreditCard ?>" class= "form-control form-control-sm">
             </div>
          </div>        
          <div class="row">
             <div class="col-md-3">
                <label><?= _('Expiration Month') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="ExpMonth" name="ExpMonth" value="<?= $tExpMonth ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Expiration Year') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="ExpYear" name="ExpYear" value="<?= $tExpYear ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('Bank Name') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="BankName" name="BankName" value="<?= $tBankName ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= _('Bank Route Number') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="Route" name="Route" value="<?= $tRoute ?>" class= "form-control form-control-sm">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= _('Bank Account Number') ?></label>
             </div>
             <div class="col-md-4">
                 <input type="text" id="Account" name="Account" value="<?= $tAccount ?>" class= "form-control form-control-sm">
             </div>
          </div>
  </div>
  <div class="card-footer">
    <div class="row">
             <div class="col-md-12">
               &nbsp;
             </div>
          </div>
          <div class="row">
             <div class="col-md-1">
             </div>
             <div class="col-md-4">
                    <input type="submit" class="btn btn-primary" value="&check; <?= _('Save') ?>" name="Submit">
             </div>
             <div class="col-md-4">
                    <input type="button" class="btn btn-default" value="x <?= _('Cancel') ?>" name="Cancel"
                           onclick="javascript:document.location='<?= !empty($linkBack)?$sRootPath."/". $linkBack:"" ?>';">
             </div>
             <div class="col-md-4">
             </div>
          </div>
  </div>
</div>
</form>

<script>
   var iFamily  = <?= (empty($iFamily)?0:$iFamily) ?>;
   var iAutID   = <?= (empty($iAutID)?0:$iAutID) ?>;

   $(function() {
      var selectedItem = $("#optionFamily option:selected").val();

      $('#optionFamily').val(1).on('change');
      $('#optionFamily').val(selectedItem).on('change');


      $('#optionFamily').on('change',function(data) {
        var famID = this.value;

        if (famID == 0) {
           $('#Country').val('');
           $('#State').val('');
           $('#LastName').val('');
           $('#Address1').val('');
           $('#Address2').val('');
           $('#City').val('');
           $('#Zip').val('');
           $('#Phone').val('');
           $('#Email').val('');
           $('#CreditCard').val('');
           $('#ExpMonth').val('');
           $('#ExpYear').val('');
           $('#BankName').val('');
           $('#Route').val('');
           $('#Account').val('');
        }  else {
          if (famID != iFamily) {
            window.CRM.APIRequest({
               method: 'POST',
               path: 'families/info',
               data: JSON.stringify({"familyId":famID})
            }, function(data) {
               $('#Country').val(data.Country);
               $('#State').val(data.State);
               $('#LastName').val(data.Name);
               $('#Address1').val(data.Address1);
               $('#Address2').val(data.Address2);
               $('#City').val(data.City);
               $('#Zip').val(data.Zip);
               $('#Phone').val(data.HomePhone);
               $('#Email').val(data.Email);
               $('#CreditCard').val('');
               $('#ExpMonth').val('');
               $('#ExpYear').val('');
               $('#BankName').val('');
               $('#Route').val('');
               $('#Account').val('');
            });
          } else {
            window.CRM.APIRequest({
               method: 'POST',
               path: 'payments/info',
               data: JSON.stringify({"autID":iAutID})
            }, function(data) {
               $('#Country').val(data.Country);
               $('#State').val(data.State);
               $('#FirstName').val(data.FirstName);
               $('#LastName').val(data.LastName);
               $('#Address1').val(data.Address1);
               $('#Address2').val(data.Address2);
               $('#City').val(data.City);
               $('#Zip').val(data.Zip);
               $('#Phone').val(data.HomePhone);
               $('#Email').val(data.Email);
               $('#CreditCard').val(data.CreditCard);
               $('#ExpMonth').val(data.ExpMonth);
               $('#ExpYear').val(data.ExpYear);
               $('#BankName').val(data.BankName);
               $('#Route').val(data.Route);
               $('#Account').val(data.Account);
            });
          }
        }
      });
      $("#optionFamily").select2();
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>




