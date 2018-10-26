<?php
/*******************************************************************************
 *
 *  filename    : AutoPaymentEditor.php
 *  copyright   : Copyright 2001, 2002, 2003, 2004 - 2014 Deane Barker, Chris Gebhardt, Michael Wilt
 *                Copyright 2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';
require 'bin/vancowebservices.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Family;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\AutoPayment;
use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\DonationFund;

$linkBack = InputUtils::LegacyFilterInput($_GET['linkBack']);
$iFamily  = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');
$iAutID   = InputUtils::LegacyFilterInput($_GET['AutID'], 'int');

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

if ($iAutID <= 0) {  // Need to create the record so there is a place to store the Vanco payment handle
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
    $FYID = CurrentFY();
    $iFYID = $FYID;

    $tCreditCard      = '';
    $tCreditCardVanco = '';
    $tExpMonth        = '';
    $tExpYear         = '';
    $tBankName        = '';
    $tRoute           = '';
    $tAccount         = '';
    $tAccountVanco    = '';

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
      $autoPayment->getEditedby($_SESSION['user']->getPersonId());
    
      $autoPayment->save();
    
      $iAutID = $autoPayment->getId();
    }
}

$sPageTitle = gettext('Automatic payment configuration');

//Is this the second pass?
if (isset($_POST['Submit'])) {
    $iFamily = InputUtils::LegacyFilterInput($_POST['Family']);
    
    if ($iFamily == 0) {
      Redirect('AutoPaymentEditor.php?AutID=' . $iAutID . '&FamilyID=' . $iFamily . '&linkBack=', $linkBack);
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
    $autoPayment->getEditedby($_SESSION['user']->getPersonId());
    
    $autoPayment->save();

    if (isset($_POST['Submit'])) {
        // Check for redirection to another page after saving information: (ie. PledgeEditor.php?previousPage=prev.php?a=1;b=2;c=3)
        if ($linkBack == "ElectronicPaymentList.php") {
          Redirect($linkBack);
        } else if ($linkBack != '') {
          $ormFamily = FamilyQuery::Create()->findOneById($iFamily);
          if (!is_null ($ormFamily)) {
            $people = $ormFamily->getActivatedPeople();
            if (!empty($people) && count($people) == 1) {
               $personId = -1;
             
               foreach ($people as $person) {
                 $personId = $person->getId();
               }
             
               if ($personId > 0) {
                  Redirect("PersonView.php?PersonID=".$personId);
               }
            }
          }
          Redirect($linkBack);
        } else {
            //Send to the view of this pledge
            Redirect('AutoPaymentEditor.php?AutID=' . $iAutID . '&FamilyID=' . $iFamily . '&linkBack=', $linkBack);
        }
    }
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
    $tCreditCardVanco = $autoPayment->getCreditcardvanco();
    $tExpMonth = $autoPayment->getExpMonth();
    $tExpYear = $autoPayment->getExpYear();
    $tBankName = $autoPayment->getBankName();
    $tRoute = $autoPayment->getRoute();
    $tAccount = $autoPayment->getAccount();
    $tAccountVanco = $autoPayment->getAccountVanco();
}

require 'Include/Header.php';

//Get Families for the drop-down
$ormFamilies = FamilyQuery::Create()->orderByName()->find();

// Get the list of funds
$ormFunds = DonationFundQuery::Create()->findByActive('true');

if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
    include 'Include/VancoConfig.php';
    $customerid = "$iAutID"; // This is an optional value that can be used to indicate a unique customer ID that is used in your system
    // put aut_ID into the $customerid field
    // Create object to preform API calls

    $workingobj = new VancoTools($VancoUserid, $VancoPassword, $VancoClientid, $VancoEnc_key, $VancoTest);
    // Call Login API to receive a session ID to be used in future API calls
    $sessionid = $workingobj->vancoLoginRequest();
    // Create content to be passed in the nvpvar variable for a TransparentRedirect API call
    $nvpvarcontent = $workingobj->vancoEFTTransparentRedirectNVPGenerator(RedirectURL('CatchCreatePayment.php'), $customerid, '', 'NO');
}
?>

<?php
if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
    ?>

    <script>

        function VancoErrorString(errNo) {
            switch (errNo) {
                case 10:
                    return "Invalid UserID/password combination";
                case 11:
                    return "Session expired";
                case 25:
                    return "All default address fields are required";
                case 32:
                    return "Name is required";
                case 33:
                    return "Unknown bank/bankpk";
                case 34:
                    return "Valid PaymentType is required";
                case 35:
                    return "Valid Routing Number Is Required";
                case 63:
                    return "Invalid StartDate";
                case 65:
                    return "Specified fund reference is not valid.";
                case 66:
                    return "Invalid End Date";
                case 67:
                    return "Transaction must have at least one transaction fund.";
                case 68:
                    return "User is Inactive";
                case 69:
                    return "Expiration Date Invalid";
                case 70:
                    return "Account Type must be C, S' for ACH and must be blank for Credit Card";
                case 71:
                    return "Class Code must be PPD, CCD, TEL, WEB, RCK or blank.";
                case 72:
                    return "Missing Client Data: Client ID";
                case 73:
                    return "Missing Customer Data: Customer ID or Name or Last Name & First Name";
                case 74:
                    return "PaymentMethod is required.";
                case 76:
                    return "Transaction Type is required";
                case 77:
                    return "Missing Credit Card Data: Card # or Expiration Date";
                case 78:
                    return "Missing ACH Data: Routing # or Account #";
                case 79:
                    return "Missing Transaction Data: Amount or Start Date";
                case 80:
                    return "Account Number has invalid characters in it";
                case 81:
                    return "Account Number has too many characters in it";
                case 82:
                    return "Customer name required";
                case 83:
                    return "Customer ID has not been set";
                case 86:
                    return "NextSettlement does not fall in today's processing dates";
                case 87:
                    return "Invalid FrequencyPK";
                case 88:
                    return "Processed yesterday";
                case 89:
                    return "Duplicate Transaction (matches another with PaymentMethod and NextSettlement)";
                case 91:
                    return "Dollar amount for transaction is over the allowed limit";
                case 92:
                    return "Invalid client reference occurred. - Transaction WILL NOT process";
                case 94:
                    return "Customer ID already exists for this client";
                case 95:
                    return "Payment Method is missing Account Number";
                case 101:
                    return "Dollar Amount for transaction cannot be negative";
                case 102:
                    return "Updated transaction's dollar amount violates amount limit";
                case 105:
                    return "PaymentMethod Date not valid yet.";
                case 125:
                    return "Email Address is required.";
                case 127:
                    return "User Is Not Proofed";
                case 134:
                    return "User does not have access to specified client.";
                case 157:
                    return "Client ID is required";
                case 158:
                    return "Specified Client is invalid";
                case 159:
                    return "Customer ID required";
                case 160:
                    return "Customer ID is already in use";
                case 161:
                    return "Customer name required";
                case 162:
                    return "Invalid Date Format";
                case 163:
                    return "Transaction Type is required";
                case 164:
                    return "Transaction Type is invalid";
                case 165:
                    return "Fund required";
                case 166:
                    return "Customer Required";
                case 167:
                    return "Payment Method Not Found";
                case 168:
                    return "Amount Required";
                case 169:
                    return "Amount Exceeds Limit. Set up manually.";
                case 170:
                    return "Start Date Required";
                case 171:
                    return "Invalid Start Date";
                case 172:
                    return "End Date earlier than Start Date";
                case 173:
                    return "Cannot Prenote a Credit Card";
                case 174:
                    return "Cannot Prenote processed account";
                case 175:
                    return "Transaction pending for Prenote account";
                case 176:
                    return "Invalid Account Type";
                case 177:
                    return "Account Number Required";
                case 178:
                    return "Invalid Routing Number";
                case 179:
                    return "Client doesn't accept Credit Card Transactions";
                case 180:
                    return "Client is in test mode for Credit Cards";
                case 181:
                    return "Client is cancelled for Credit Cards";
                case 182:
                    return "Name on Credit Card is Required";
                case 183:
                    return "Invalid Expiration Date";
                case 184:
                    return "Complete Billing Address is Required";
                case 195:
                    return "Transaction Cannot Be Deleted";
                case 196:
                    return "Recurring Telephone Entry Transaction NOT Allowed";
                case 198:
                    return "Invalid State";
                case 199:
                    return "Start Date Is Later Than Expiration date";
                case 201:
                    return "Frequency Required";
                case 202:
                    return "Account Cannot Be Deleted, Active Transaction Exists";
                case 203:
                    return "Client Does Not Accept ACH Transactions";
                case 204:
                    return "Duplicate Transaction";
                case 210:
                    return "Recurring Credits NOT Allowed";
                case 211:
                    return "ONHold/Cancelled Customer";
                case 217:
                    return "End Date Cannot Be Earlier Than The Last Settlement Date";
                case 218:
                    return "Fund ID Cannot Be W, P, T, or C";
                case 223:
                    return "Customer ID not on file";
                case 224:
                    return "Credit Card Credits NOT Allowed - Must Be Refunded";
                case 231:
                    return "Customer Not Found For Client";
                case 232:
                    return "Invalid Account Number";
                case 233:
                    return "Invalid Country Code";
                case 234:
                    return "Transactions Are Not Allow From This Country";
                case 242:
                    return "Valid State Required";
                case 251:
                    return "Transactionref Required";
                case 284:
                    return "User Has Been Deleted";
                case 286:
                    return "Client not set up for International Credit Card Processing";
                case 296:
                    return "Client Is Cancelled";
                case 328:
                    return "Credit Pending - Cancel Date cannot be earlier than Today";
                case 329:
                    return "Credit Pending - Account cannot be placed on hold until Tomorrow";
                case 341:
                    return "Cancel Date Cannot be Greater Than Today";
                case 344:
                    return "Phone Number Must be 10 Digits Long";
                case 365:
                    return "Invalid Email Address";
                case 378:
                    return "Invalid Loginkey";
                case 379:
                    return "Requesttype Unavailable";
                case 380:
                    return "Invalid Sessionid";
                case 381:
                    return "Invalid Clientid for Session";
                case 383:
                    return "Internal Handler Error. Contact Vanco Services.";
                case 384:
                    return "Invalid Requestid";
                case 385:
                    return "Duplicate Requestid";
                case 390:
                    return "Requesttype Not Authorized For User";
                case 391:
                    return "Requesttype Not Authorized For Client";
                case 392:
                    return "Invalid Value Format";
                case 393:
                    return "Blocked IP";
                case 395:
                    return "Transactions cannot be processed on Weekends";
                case 404:
                    return "Invalid Date";
                case 410:
                    return "Credits Cannot Be WEB or TEL";
                case 420:
                    return "Transaction Not Found";
                case 431:
                    return "Client Does Not Accept International Credit Cards";
                case 432:
                    return "Can not process credit card";
                case 434:
                    return "Credit Card Processor Error";
                case 445:
                    return "Cancel Date Cannot Be Prior to the Last Settlement Date";
                case 446:
                    return "End Date Cannot Be In The Past";
                case 447:
                    return "Masked Account";
                case 469:
                    return "Card Number Not Allowed";
                case 474:
                    return "MasterCard Not Accepted";
                case 475:
                    return "Visa Not Accepted";
                case 476:
                    return "American Express Not Accepted";
                case 477:
                    return "Discover Not Accepted";
                case 478:
                    return "Invalid Account Number";
                case 489:
                    return "Customer ID Exceeds 15 Characters";
                case 490:
                    return "Too Many Results, Please Narrow Search";
                case 495:
                    return "Field Contains Invalid Characters";
                case 496:
                    return "Field contains Too Many Characters";
                case 497:
                    return "Invalid Zip Code";
                case 498:
                    return "Invalid City";
                case 499:
                    return "Invalid Canadian Postal Code";
                case 500:
                    return "Invalid Canadian Province";
                case 506:
                    return "User Not Found";
                case 511:
                    return "Amount Exceeds Limit";
                case 512:
                    return "Client Not Set Up For Credit Card Processing";
                case 515:
                    return "Transaction Already Refunded";
                case 516:
                    return "Can Not Refund a Refund";
                case 517:
                    return "Invalid Customer";
                case 518:
                    return "Invalid Payment Method";
                case 519:
                    return "Client Only Accepts Debit Cards";
                case 520:
                    return "Transaction Max for Account Number Reached";
                case 521:
                    return "Thirty Day Max for Client Reached";
                case 523:
                    return "Invalid Login Request";
                case 527:
                    return "Change in account/routing# or type";
                case 535:
                    return "SSN Required";
                case 549:
                    return "CVV2 Number is Required";
                case 550:
                    return "Invalid Client ID";
                case 556:
                    return "Invalid Banking Information";
                case 569:
                    return "Please Contact This Organization for Assistance with Processing This Transaction";
                case 570:
                    return "City Required";
                case 571:
                    return "Zip Code Required";
                case 572:
                    return "Canadian Provence Required";
                case 573:
                    return "Canadian Postal Code Required";
                case 574:
                    return "Country Code Required";
                case 578:
                    return "Unable to Read Card Information. Please Click 'Click to Swipe' Button and Try Again.";
                case 610:
                    return "Invalid Banking Information. Previous Notification of Change Received for this Account";
                case 629:
                    return "Invalid CVV2";
                case 641:
                    return "Fund ID Not Found";
                case 642:
                    return "Request Amount Exceeds Total Transaction Amount";
                case 643:
                    return "Phone Extension Required";
                case 645:
                    return "Invalid Zip Code";
                case 652:
                    return "Invalid SSN";
                case 653:
                    return "SSN Required";
                case 657:
                    return "Billing State Required";
                case 659:
                    return "Phone Number Required";
                case 663:
                    return "Version Not Supported";
                case 665:
                    return "Invalid Billing Address";
                case 666:
                    return "Customer Not On Hold";
                case 667:
                    return "Account number for fund is invalid";
                case 678:
                    return "Password Expired";
                case 687:
                    return "Fund Name is currently in use. Please choose another name. If you would like to use this Fund Name, go to the other fund and change the Fund Name to something different.";
                case 688:
                    return "Fund ID is currently in use. Please choose another number. If you would like to use this Fund ID, go to the other fund and change the Fund ID to something different.";
                case 705:
                    return "Please Limit Your Date Range To 30 Days";
                case 706:
                    return "Last Digits of Account Number Required";
                case 721:
                    return "MS Transaction Amount Cannot Be Greater Than $50,000.";
                case 725:
                    return "User ID is for Web Services Only";
                case 730:
                    return "Start Date Required";
                case 734:
                    return "Date Range Cannot Be Greater Than One Year";
                case 764:
                    return "Start Date Cannot Occur In The Past";
                case 800:
                    return "The CustomerID Does Not Match The Given CustomerRef";
                case 801:
                    return "Default Payment Method Not Found";
                case 838:
                    return "Transaction Cannot Be Processed. Please contact your organization.";
                case 842:
                    return "Invalid Pin";
                case 844:
                    return "Phone Number Must be 10 Digits Long";
                case 850:
                    return "Invalid Authentication Signature";
                case 857:
                    return "Fund Name Can Not Be Greater Than 30 Characters";
                case 858:
                    return "Fund ID Can Not Be Greater Than 20 Characters";
                case 859:
                    return "Customer Is Unproofed";
                case 862:
                    return "Invalid Start Date";
                case 956:
                    return "Amount Must Be Greater Than $0.00";
                case 960:
                    return "Date of Birth Required";
                case 963:
                    return "Missing Field";
                case 973:
                    return "No match found for these credentials.";
                case 974:
                    return "Recurring Return Fee Not Allowed";
                case 992:
                    return "No Transaction Returned Within the Past 45 Days";
                case 993:
                    return "Return Fee Must Be Collected Within 45 Days";
                case 994:
                    return "Return Fee Is Greater Than the Return Fee Allowed";
                case 1005:
                    return "Phone Extension Must Be All Digits";
                case 1008:
                    return "We are sorry. This organization does not accept online credit card transactions. Please try again using a debit card.";
                case 1047:
                    return "Invalid nvpvar variables";
                case 1054:
                    return "Invalid. Debit Card Only";
                case 1067:
                    return "Invalid Original Request ID";
                case 1070:
                    return "Transaction Cannot Be Voided";
                case 1073:
                    return "Transaction Processed More Than 25 Minutes Ago";
                case 1127:
                    return "Declined - Tran Not Permitted";
                case 1128:
                    return "Unable To Process, Please Try Again";
            }
        }

        function CreatePaymentMethod() {
            var accountType = "CC";
            if (document.getElementById("EnableBankDraft").checked)
                accountType = "C";

            var accountNum = "";
            if (document.getElementById("EnableBankDraft").checked)
                accountNum = Account.value;
            if (document.getElementById("EnableCreditCard").checked)
                accountNum = CreditCard.value;

            $.ajax({
                type: "POST",
                url: "<?php if ($VancoTest) {
        echo 'https://www.vancodev.com/cgi-bin/wsnvptest.vps';
    } else {
        echo 'https://www.vancoservices.com/cgi-bin/wsnvp.vps';
    } ?>",
                data: {
                    "sessionid": "<?= $sessionid ?>",
                    "nvpvar": "<?= $nvpvarcontent ?>",
                    "newcustomer": "true",
                    "accounttype": accountType,
                    "accountnumber": accountNum,
                    "routingnumber": Route.value,
                    "expmonth": ExpMonth.value,
                    "expyear": ExpYear.value,
                    "email": Email.value,
                    "name": FirstName.value + " " + LastName.value,
                    "billingaddr1": Address1.value,
                    "billingcity": City.value,
                    "billingstate": State.value,
                    "billingzip": Zip.value,
                    "name_on_card": FirstName.value + " " + LastName.value
                },
                dataType: 'jsonp',
                async: true,
                traditional: false,
                success: function (vancodata) {
                    var gotPaymentRef = vancodata["paymentmethodref"];// replace the private account# with the Vanco payment method reference
                    var errorList = vancodata["errorlist"];
                    $.ajax({
                        type: "POST",
                        url: "<?= $VancoUrltoredirect ?>",
                        data: vancodata,
                        dataType: 'json',
                        async: true,
                        traditional: false,
                        success: function (postbackdata) {
                            if (gotPaymentRef > 0) {
                                if (document.getElementById("EnableBankDraft").checked) {
                                    accountVal = document.getElementById("Account").value;
                                    document.getElementById("Account").value = "*****" + accountVal.substr(accountVal.length - 4, 4);
                                    document.getElementById("AccountVanco").value = gotPaymentRef;
                                } else if (document.getElementById("EnableCreditCard").checked) {
                                    ccVal = document.getElementById("CreditCard").value;
                                    document.getElementById("CreditCard").value = "************" + ccVal.substr(ccVal.length - 4, 4);
                                    document.getElementById("CreditCardVanco").value = gotPaymentRef;
                                }
                            } else {
                                errorArr = errorList.split(',');
                                errorStr = "";
                                for (var i = 0; i < errorArr.length; i++)
                                    errorStr += "Error " + errorArr[i] + ": " + VancoErrorString(Number(errorArr[i])) + "\n";
                                alert(errorStr);
                                window.location = "<?= RedirectURL('AutoPaymentEditor.php') . "?AutID=$iAutID&FamilyID=$aut_FamID$&linkBack=$linkBack" ?>";
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown, nashuadata) {
                            alert("ErrorThrown calling back to register payment method: " + errorThrown);
                            alert("Error calling back to register payment method: " + textStatus);
                            alert("Data returned calling back to register payment method: " + JSON.stringify(postbackdata));
                        }
                    });
                },
                error: function (jqXHR, textStatus, errorThrown, vancodata) {
                    alert("Error calling Vanco: " + errorThrown);
                }
            });
        }
    </script>
    <?php
}
?>

<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title"><?= gettext("For the").' '.(($onePersonFamily == true)?gettext('Person'):gettext('Family'))?> : <?=  $fam_Name ?></h3>
  </div>
  <div class="body-text">
    <form method="post"  style="padding:10px"
          action="AutoPaymentEditor.php?<?= 'AutID=' . $iAutID . '&FamilyID=' . $iFamily . '&linkBack=' . $linkBack ?>"
          name="AutoPaymentEditor">

          <div class="row">
            <div class="col-md-3">
                <label><?= gettext('Person').' '.gettext('or').' '.gettext('Family') ?>:</label>
            </div>
            <div class="col-md-4">
                <div class="callout callout-danger"><?= gettext("WARNING ! You've to select a person or a family to create an auto-payment.") ?></div>
                <select name="Family" id="optionFamily" style="width:100%">
                   <option value="0" selected><?= gettext('Unassigned') ?></option>
                   <option value="0">-----------------------</option>

                <?php
                  foreach ($ormFamilies as $family) {
                ?>
                    <option value="<?= $family->getId() ?>" <?= ($iFamily == $family->getId())?' selected':'' ?>> <?= $family->getName() . '&nbsp;' . FormatAddressLine($family->getAddress1(), $family->getCity(), $family->getState()) ?>
                <?php
                   }
                ?>

                </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
                <label><?= gettext('Automatic payment type') ?></label>
            </div>
            <div class="col-md-9">
               <input type="radio" Name="EnableButton" value="1" id="EnableBankDraft"<?php if ($bEnableBankDraft) {
                                        echo ' checked';
                                    } ?>> <?= gettext("Bank Draft ") ?>
               <input type="radio" Name="EnableButton" value="2"
                                       id="EnableCreditCard" <?php if ($bEnableCreditCard) {
                                        echo ' checked';
                                    } ?>> <?= gettext("Credit Card ") ?>
               <input type="radio" Name="EnableButton" value="3"
                                       id="Disable" <?php if ((!$bEnableBankDraft) && (!$bEnableCreditCard)) {
                                        echo ' checked';
                                    } ?>> <?= gettext("Disable ") ?>
              </div>  
           </div>                   
           <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Date') ?>:</label>
             </div>
             <div class="col-md-4">             
                 <input type="text" name="NextPayDate" value="<?= OutputUtils::change_date_for_place_holder($dNextPayDate) ?>"
                                                          maxlength="10" id="NextPayDate" size="11"
                                                          class="form-control pull-right active date-picker">
             </div>
          </div>
           <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Fiscal Year') ?>:</label>
             </div>
             <div class="col-md-4">             
                <?php PrintFYIDSelect($iFYID, 'FYID') ?>
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                  <label><?= gettext('Payment amount') ?></label>
             </div>
             <div class="col-md-4">                            
                  <input type="number" step="any" name="Amount" value="<?= $nAmount ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Payment interval (months)') ?></label>
             </div>
             <div class="col-md-4">                            
               <input type="text" name="Interval" value="<?= $iInterval ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Fund') ?>:</label>
             </div>
             <div class="col-md-4">                            
                  <select name="Fund" class="form-control">
                      <option value="0"><?= gettext('None') ?></option>
                      <?php
                      foreach ($ormFunds as $fund) {
                          ?>
                          <option value="<?= $fund->getId()?>" <?= (($iFund == $fund->getId())?' selected':'').">".$fund->getName().(($fund->getActive() != 'true')?' (' . gettext('inactive') . ')':'') ?> 
                          </option>
                      <?php
                      }
                      ?>
                  </select>
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('First Name') ?></label>
             </div>
             <div class="col-md-4">                                             
                 <input type="text" id="FirstName" name="FirstName" value="<?= $tFirstName ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Last Name') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="LastName" name="LastName" value="<?= $tLastName ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Address') ?> 1</label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="Address1" name="Address1" value="<?= $tAddress1 ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Address') ?> 2</label>
             </div>
             <div class="col-md-4"> 
                <input type="text" id="Address2" name="Address2" value="<?= $tAddress2 ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('City') ?></label>
             </div>
             <div class="col-md-4"> 
                <input type="text" id="City" name="City" value="<?= $tCity ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('State') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="State" name="State" value="<?= $tState ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Zip') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="Zip" name="Zip" value="<?= $tZip ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Country') ?></label>
             </div>
             <div class="col-md-4"> 
                <input type="text" id="Country" name="Country" value="<?= $tCountry ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Phone') ?></label>
             </div>
             <div class="col-md-4"> 
                <input type="text" id="Phone" name="Phone" value="<?= $tPhone ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Email') ?></label>
             </div>
             <div class="col-md-4"> 
                <input type="text" id="Email" name="Email" value="<?= $tEmail ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Credit Card') ?></label>
             </div>
             <div class="col-md-4"> 
                <input type="text" id="CreditCard" name="CreditCard" value="<?= $tCreditCard ?>" class="form-control">
             </div>
          </div>
        <?php
          if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
        ?>
          <div class="row">
             <div class="col-md-3">
                  <label><?= gettext('Vanco Credit Card Method') ?></label>
             </div>
             <div class="col-md-4"> 
                    <input type="text" id="CreditCardVanco" name="CreditCardVanco" value="<?= $tCreditCardVanco ?>" readonly class="form-control">
             </div>
          </div>
        <?php
          }
        ?>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Expiration Month') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="ExpMonth" name="ExpMonth" value="<?= $tExpMonth ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Expiration Year') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="ExpYear" name="ExpYear" value="<?= $tExpYear ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Bank Name') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="BankName" name="BankName" value="<?= $tBankName ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                <label><?= gettext('Bank Route Number') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="Route" name="Route" value="<?= $tRoute ?>" class="form-control">
             </div>
          </div>
          <div class="row">
             <div class="col-md-3">
                 <label><?= gettext('Bank Account Number') ?></label>
             </div>
             <div class="col-md-4"> 
                 <input type="text" id="Account" name="Account" value="<?= $tAccount ?>" class="form-control">
             </div>
          </div>
        <?php
          if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
        ?>
          <div class="row">
             <div class="col-md-3">
                  <label><?= gettext('Vanco Bank Account Method') ?></label>
             </div>
             <div class="col-md-4"> 
                  <input type="text" id="AccountVanco" name="AccountVanco" value="<?= $tAccountVanco ?>" readonly class="form-control">
             </div>
          </div>
      <?php
        }
      ?>

      <?php
        if (SystemConfig::getValue('sElectronicTransactionProcessor') == 'Vanco') {
      ?>
          <div class="row">
             <div class="col-md-12">
                <?php
                  if ($iAutID > 0) {
                ?>
                  <input type="button" id="PressToCreatePaymentMethod" value="<?= gettext("Store Private Data at Vanco") ?>" onclick="CreatePaymentMethod();"/>
                <?php
                  } else {
                ?>
                  <b>Save this record to enable storing private data at Vanco</b>
                <?php
                  } 
                ?>
             </div>
          </div>
      <?php
        }
      ?>
          <div class="row">
             <div class="col-md-12">
               &nbsp;
             </div>
          </div>
          <div class="row">
             <div class="col-md-1">
             </div>
             <div class="col-md-4"> 
                    <input type="submit" class="btn btn-primary" value="<?= gettext('Save') ?>" name="Submit">
             </div>
             <div class="col-md-4"> 
                    <input type="button" class="btn btn-default" value="<?= gettext('Cancel') ?>" name="Cancel"
                           onclick="javascript:document.location='<?= (strlen($linkBack) > 0)?:'Menu.php' ?>';">
             </div>
             <div class="col-md-4"> 
             </div>
          </div>
    
    </form>
  </div>
</div>
    
<script>
   var iFamily  = <?= (empty($iFamily)?0:$iFamily) ?>;
   var iAutID   = <?= (empty($iAutID)?0:$iAutID) ?>;
   
    $(document).ready(function() {
      var selectedItem = $("#optionFamily option:selected").val();
      
      $('#optionFamily').val(1).change();
      $('#optionFamily').val(selectedItem).change();
  
  
      $('#optionFamily').change(function(data) {
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
            }).done(function(data) {
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
            }).done(function(data) {
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

<?php require 'Include/Footer.php' ?>