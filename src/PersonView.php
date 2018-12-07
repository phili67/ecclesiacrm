<?php

/*******************************************************************************
 *
 *  filename    : PersonView.php
 *  last change : 2003-04-14
 *  description : Displays all the information about a single person
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2003 Phillip Hullquist, Deane Barker, Chris Gebhardt
 *  Copyright : 2018 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use Propel\Runtime\Propel;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Service\TimelineService;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\UserQuery;

use EcclesiaCRM\Map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\Map\PersonVolunteerOpportunityTableMap;
use EcclesiaCRM\Map\VolunteerOpportunityTableMap;
use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\PersonCustomMasterQuery;


// Set the page title and include HTML header
$sPageTitle = gettext('Person Profile');
require 'Include/Header.php';

// Get the person ID from the querystring
$iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');

$user = UserQuery::Create()->findPk($iPersonID);

// we get the TimelineService
$maxMainTimeLineItems = 20; // max number

$timelineService           = new TimelineService();
$timelineServiceItems      = $timelineService->getForPerson($iPersonID);
$timelineNotesServiceItems = $timelineService->getNotesForPerson($iPersonID);

// we get the MailChimp Service
$mailchimp = new MailChimpService();

// person informations
$userName       = '';
$userDir        = '';
$Currentpath    = '';
$currentNoteDir = '';
$directories    = [];

if (!is_null($user)) {
  $realNoteDir = $userDir = $user->getUserRootDir();
  $userName    = $user->getUserName();
  $currentpath = $user->getCurrentpath();
  
  $currentNoteDir =  SystemURLs::getRootPath()."/".$realNoteDir."/".$userName;
                    
  $directories = MiscUtils::getDirectoriesInPath($currentNoteDir.$currentpath);
}

$iRemoveVO = 0;
if (array_key_exists('RemoveVO', $_GET)) {
    $iRemoveVO = InputUtils::LegacyFilterInput($_GET['RemoveVO'], 'int');
}

$bDocuments = false;

if (array_key_exists('documents', $_GET)) {
    $bDocuments = true;
}

$bEDrive = false;

if (array_key_exists('edrive', $_GET)) {
    $bEDrive = true;
}

// Get this person's data
$connection = Propel::getConnection();

$sSQL = "SELECT a.*, family_fam.*, COALESCE(cls.lst_OptionName , 'Unassigned') AS sClassName, clsicon.lst_ic_lst_url as sClassIcon, fmr.lst_OptionName AS sFamRole, b.per_FirstName AS EnteredFirstName, b.per_ID AS EnteredId,
        b.Per_LastName AS EnteredLastName, c.per_FirstName AS EditedFirstName, c.per_LastName AS EditedLastName, c.per_ID AS EditedId
      FROM person_per a
      LEFT JOIN family_fam ON a.per_fam_ID = family_fam.fam_ID
      LEFT JOIN list_lst  cls ON a.per_cls_ID = cls.lst_OptionID AND cls.lst_ID = 1
      LEFT JOIN list_icon clsicon ON clsicon.lst_ic_lst_Option_ID = cls.lst_OptionID 
      LEFT JOIN list_lst fmr ON a.per_fmr_ID = fmr.lst_OptionID AND fmr.lst_ID = 2
      LEFT JOIN person_per b ON a.per_EnteredBy = b.per_ID
      LEFT JOIN person_per c ON a.per_EditedBy = c.per_ID
      WHERE a.per_ID = ".$iPersonID;

$statement = $connection->prepare($sSQL);
$statement->execute();
$res = $statement->fetch( PDO::FETCH_ASSOC );

extract($res);


$person = PersonQuery::create()->findPk($iPersonID);

if (empty($person)) {
    Redirect('members/404.php?type=Person');
    exit;
}

if ($person->getDateDeactivated() != null) {
    $time = new DateTime('now');
    $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');
    
    if ( $new_time > $person->getDateDeactivated() ) {
      if ( !$_SESSION['user']->isGdrpDpoEnabled() ) {
        Redirect('members/404.php?type=Person');
        exit;
      }
    } else if (!$_SESSION['user']->isEditRecordsEnabled()){
      Redirect('members/404.php?type=Person');
      exit;
    }
}

$ormAssignedProperties = Record2propertyR2pQuery::Create()
                            ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                            ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                            ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                            ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                            ->where(PropertyTableMap::COL_PRO_CLASS."='p'")
                            ->addAscendingOrderByColumn('ProName')
                            ->addAscendingOrderByColumn('ProTypeName')
                            ->findByR2pRecordId($iPersonID);

$iFamilyID = $person->getFamId();

//Get the automatic payments for this family
$ormAutoPayments = AutoPaymentQuery::create()
           ->leftJoinPerson()
             ->withColumn('Person.FirstName','EnteredFirstName')
             ->withColumn('Person.LastName','EnteredLastName')
             ->withColumn('Person.FirstName','EnteredFirstName')
             ->withColumn('Person.LastName','EnteredLastName')
           ->leftJoinDonationFund()
             ->withColumn('DonationFund.Name','fundName')
           ->orderByNextPayDate()
           ->findByFamilyid($iFamilyID);


// Get the lists of custom person fields
$ormCustomFields = PersonCustomMasterQuery::Create()
                     ->orderByCustomOrder()
                     ->find();
                     
// Get the custom field data for this person.
$sSQL = 'SELECT * FROM `person_custom` WHERE per_ID = '.$iPersonID;

$statement = $connection->prepare($sSQL);
$statement->execute();
$aCustomData = $statement->fetch( PDO::FETCH_BOTH );

// Get the Groups this Person is assigned to
$ormAssignedGroups = Person2group2roleP2g2rQuery::Create()
       ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID,GroupTableMap::COL_GRP_ID,Criteria::LEFT_JOIN)
       ->addMultipleJoin(array(array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID,ListOptionTableMap::COL_LST_OPTIONID),array(GroupTableMap::COL_GRP_ROLELISTID,ListOptionTableMap::COL_LST_ID)),Criteria::LEFT_JOIN)
       ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
       ->Where(Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID.' = '.$iPersonID.' ORDER BY grp_Name')
       ->addAsColumn('roleName',ListOptionTableMap::COL_LST_OPTIONNAME)
       ->addAsColumn('groupName',GroupTableMap::COL_GRP_NAME)
       ->addAsColumn('groupID',GroupTableMap::COL_GRP_ID)
       ->addAsColumn('hasSpecialProps',GroupTableMap::COL_GRP_HASSPECIALPROPS)
       ->find();

// Get the volunteer opportunities this Person is assigned to
$ormAssignedVolunteerOpps = VolunteerOpportunityQuery::Create()
        ->addJoin(VolunteerOpportunityTableMap::COL_VOL_ID,PersonVolunteerOpportunityTableMap::COL_P2VO_VOL_ID,Criteria::LEFT_JOIN)
        ->Where(PersonVolunteerOpportunityTableMap::COL_P2VO_PER_ID.' = '.$iPersonID)
        ->find();

// Get all the volunteer opportunities
$ormVolunteerOpps = VolunteerOpportunityQuery::Create()->orderByOrder()->find();

//Get all the properties
$ormProperties = PropertyQuery::Create()
                  ->filterByProClass('p')
                  ->orderByProName()
                  ->find();

              
$dBirthDate = OutputUtils::FormatBirthDate($person->getBirthYear(), $person->getBirthMonth(), $person->getBirthDay(), '-', $person->getFlags());

$sFamilyInfoBegin = '<span style="color: red;">';
$sFamilyInfoEnd = '</span>';

// Assign the values locally, after selecting whether to display the family or person information

if ( !is_null ($person->getFamily()) ) {
  $famAddress1      = $person->getFamily()->getAddress1();
  $famAddress2      = $person->getFamily()->getAddress2();
  $famCity          = $person->getFamily()->getCity();
  $famSate          = $person->getFamily()->getState();
  $famZip           = $person->getFamily()->getZip();
  $famCountry       = $person->getFamily()->getCountry();
  $famHompePhone    = $person->getFamily()->getHomePhone();
  $famWorkPhone     = $person->getFamily()->getWorkPhone();
  $famCellPhone     = $person->getFamily()->getCellPhone();
  $famEmail         = $person->getFamily()->getEmail();
}

//Get an unformatted mailing address to pass as a parameter to a google maps search
SelectWhichAddress($Address1, $Address2, $person->getAddress1(), $person->getAddress2(), $famAddress1, $famAddress2, false);
$sCity = SelectWhichInfo($person->getCity(), $famCity, false);
$sState = SelectWhichInfo($person->getState(), $famSate, false);
$sZip = SelectWhichInfo($person->getZip(), $famZip, false);
$sCountry = SelectWhichInfo($person->getCountry(), $famCountry, false);
$plaintextMailingAddress = $person->getAddress();

//Get a formatted mailing address to use as display to the user.
SelectWhichAddress($Address1, $Address2, $person->getAddress1(), $person->getAddress2(), $famAddress1, $famAddress2, true);
$sCity = SelectWhichInfo($person->getCity(), $famCity, true);
$sState = SelectWhichInfo($person->getState(), $famSate, true);
$sZip = SelectWhichInfo($person->getZip(), $famZip, true);
$sCountry = SelectWhichInfo($person->getCountry(), $famCountry, true);
$formattedMailingAddress = $person->getAddress();

$sPhoneCountry = SelectWhichInfo($person->getCountry(), $famCountry, false);
$sHomePhone = SelectWhichInfo(ExpandPhoneNumber($person->getHomePhone(), $sPhoneCountry, $dummy),
ExpandPhoneNumber($famHompePhone, $famCountry, $dummy), true);
$sHomePhoneUnformatted = SelectWhichInfo(ExpandPhoneNumber($person->getHomePhone(), $sPhoneCountry, $dummy),
ExpandPhoneNumber($famHompePhone, $famCountry, $dummy), false);
$sWorkPhone = SelectWhichInfo(ExpandPhoneNumber($person->getWorkPhone(), $sPhoneCountry, $dummy),
ExpandPhoneNumber($famWorkPhone, $famCountry, $dummy), true);
$sWorkPhoneUnformatted = SelectWhichInfo(ExpandPhoneNumber($person->getWorkPhone(), $sPhoneCountry, $dummy),
ExpandPhoneNumber($famWorkPhone, $famCountry, $dummy), false);
$sCellPhone = SelectWhichInfo(ExpandPhoneNumber($person->getCellPhone(), $sPhoneCountry, $dummy),
ExpandPhoneNumber($famCellPhone, $famCountry, $dummy), true);
$sCellPhoneUnformatted = SelectWhichInfo(ExpandPhoneNumber($person->getCellPhone(), $sPhoneCountry, $dummy),
ExpandPhoneNumber($famCellPhone, $famCountry, $dummy), false);
$sEmail = SelectWhichInfo($person->getEmail(), $famEmail, true);
$sUnformattedEmail = SelectWhichInfo($person->getEmail(), $famEmail, false);

if ($person->getEnvelope() > 0) {
    $sEnvelope = $person->getEnvelope();
} else {
    $sEnvelope = gettext('Not assigned');
}

$iTableSpacerWidth = 10;

$isMailChimpActive = $mailchimp->isActive();

$bOkToEdit = ($_SESSION['user']->isEditRecordsEnabled() ||
    ($_SESSION['user']->isEditSelfEnabled() && $person->getId() == $_SESSION['user']->getPersonId()) ||
    ($_SESSION['user']->isEditSelfEnabled() && $person->getFamId() == $_SESSION['user']->getPerson()->getFamId())
    );
?>

<?php if (!empty($person->getDateDeactivated())) {
    ?>
    <div class="alert alert-warning">
        <strong><?= gettext("This Person is Deactivated") ?> </strong>
    </div>
    <?php
} ?>

<div class="row">
  <div class="col-lg-3 col-md-3 col-sm-3">
    <div class="box box-primary">
      <div class="box-body box-profile">
        <div class="image-container">
            <img src ="<?= SystemURLs::getRootPath().'/api/persons/'.$person->getId().'/photo' ?>"
            class="initials-image profile-user-img img-responsive img-rounded profile-user-img-md">
            <?php if ($bOkToEdit): ?>
                <div class="after">
                <div class="buttons">
                    <a id="view-larger-image-btn" class="hide"  title="<?= gettext("View Photo") ?>">
                        <i class="fa fa-search-plus"></i>
                    </a>&nbsp;
                    <a  class="" data-toggle="modal" data-target="#upload-image" title="<?= gettext("Upload Photo") ?>">
                        <i class="fa fa-camera"></i>
                    </a>&nbsp;
                    <a  data-toggle="modal" data-target="#confirm-delete-image" title="<?= gettext("Delete Photo") ?>">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </div>
                </div>
            <?php endif; ?>
        </div>
        <h3 class="profile-username text-center">
      <?php 
        if ($person->isMale()) {
      ?>
          <i class="fa fa-male"></i>
      <?php
        } elseif ($person->isFemale()) {
      ?>
          <i class="fa fa-female"></i>
      <?php
        } 
      ?>
      <?= $person->getFullName() ?></h3>

      <?php
         if ($person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() || $_SESSION['user']->isEditRecordsEnabled() ) {
      ?>
        <p class="text-muted text-center">
            <?= empty($person->getFamilyRoleName()) ? gettext('Undefined') : gettext($person->getFamilyRoleName()); ?>
            &nbsp;
            <a id="edit-role-btn" data-person_id="<?= $person->getId() ?>" data-family_role="<?= $person->getFamilyRoleName() ?>"
            data-family_role_id="<?= $person->getFmrId() ?>"  class="btn btn-primary btn-xs">
                <i class="fa fa-pencil"></i>
            </a>
        </p>
      <?php
        }
      ?>
        <p class="text-muted text-center">
        
          <?php
            if (!empty($sClassIcon)) {
          ?>
            <img src="<?= SystemURLs::getRootPath()."/skin/icons/markers/".$sClassIcon?>" boder=0>
          <?php
            }
          ?>
        <?= gettext($sClassName);
          if ($person->getMembershipDate()) {
        ?>
              <br><?= gettext('Member')." ".gettext(' Since:').' '.OutputUtils::FormatDate($person->getMembershipDate()->format('Y-m-d'), false) ?>
        <?php
          } 
        ?>
        </p>
      <?php 
        if ($bOkToEdit) {
      ?>
          <a href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?PersonID=<?= $person->getId() ?>" class="btn btn-primary btn-block"><b><?php echo gettext('Edit'); ?></b></a>
      <?php
        } 
      ?>
      </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->

    <!-- About Me Box -->
    <?php 
      $can_see_privatedata = ($person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId()  || $_SESSION['user']->isSeePrivacyDataEnabled() || $_SESSION['user']->isEditRecordsEnabled())?true:false;
    ?>
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title text-center"><?php echo gettext('About Me'); ?></h3>
      </div>
      <!-- /.box-header -->
      <div class="box-body">
        <ul class="fa-ul">
        <?php
          if ( $can_see_privatedata ) {
            if (count($person->getOtherFamilyMembers()) > 0) {
        ?>
          <li><i class="fa-li fa fa-group"></i><?php echo gettext('Family:'); ?> <span>
            <?php
              if (!is_null ($person->getFamily()) && $person->getFamily()->getId() != '') {
            ?>
                <a href="<?= SystemURLs::getRootPath() ?>/FamilyView.php?FamilyID=<?= $person->getFamily()->getId() ?>"><?= $person->getFamily()->getName() ?> </a>
                <a href="<?= SystemURLs::getRootPath() ?>/FamilyEditor.php?FamilyID=<?= $person->getFamily()->getId() ?>" class="table-link">
                  <span class="fa-stack">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                  </span>
                </a>
            <?php
              } else {
            ?>
                <?= gettext('(No assigned family)') ?>
            <?php
              } 
            ?>
            </span>
        </li>
      <?php
        }
      
        if (!empty($formattedMailingAddress)) {
      ?>
          <li><i class="fa-li fa fa-home"></i><?php echo gettext('Address'); ?>: 
            <span>
              <a href="http://maps.google.com/?q=<?= $plaintextMailingAddress ?>" target="_blank">
                <?= $formattedMailingAddress ?>
              </a>
            </span>
          </li>
      <?php
        }
        
        if ($dBirthDate) {
      ?>
          <li>
            <i class="fa-li fa fa-calendar"></i><?= gettext('Birth Date') ?>:
            <span><?= $dBirthDate ?></span>
          <?php 
            if (!$person->hideAge()) {
          ?>
            (<span data-birth-date="<?= $person->getBirthDate()->format('Y-m-d') ?>"></span> <?=FormatAgeSuffix($person->getBirthDate(), $person->getFlags()) ?>)
          <?php
            } 
          ?>
          </li>
  <?php
    }
    if (!SystemConfig::getValue('bHideFriendDate') && $person->getFriendDate() != '') { /* Friend Date can be hidden - General Settings */ 
  ?>
          <li><i class="fa-li fa fa-tasks"></i><?= gettext('Friend Date') ?>: <span><?= OutputUtils::FormatDate($person->getFriendDate()->format('Y-m-d'), false) ?></span></li>
  <?php
    }
    
    if ($sCellPhone) {
  ?>
          <li><i class="fa-li fa fa-mobile-phone"></i><?= gettext('Mobile Phone') ?>: <span><a href="tel:<?= $sCellPhoneUnformatted ?>"><?= $sCellPhone ?></a></span></li>
          <li><i class="fa-li fa fa-mobile-phone"></i><?= gettext('Text Message') ?>: <span><a href="sms:<?= str_replace(' ', '',$sCellPhoneUnformatted) ?>&body=<?= gettext("EcclesiaCRM text message") ?>"><?= $sCellPhone ?></a></span></li>
  <?php
    }
    
    if ($sHomePhone) {
  ?>
          <li><i class="fa-li fa fa-phone"></i><?= gettext('Home Phone') ?>: <span><a href="tel:<?= $sHomePhoneUnformatted ?>"><?= $sHomePhone ?></a></span></li>
  <?php
    }
    
    if (!SystemConfig::getValue("bHideFamilyNewsletter")) { /* Newsletter can be hidden - General Settings */ 
      ?>
          <li><i class="fa-li fa fa-hacker-news"></i><?= gettext("Send Newsletter") ?>:
            <span id="NewsLetterSend"></span>
          </li>
      <?php
        }
    if ($sEmail != '') {
  ?>
          <li><i class="fa-li fa fa-envelope"></i><?= gettext('Email') ?>: <span><a href="mailto:<?= $sUnformattedEmail ?>"><?= $sEmail ?></a></span></li>
        <?php 
          if ($isMailChimpActive) {
        ?>
          <li><i class="fa-li fa fa-send"></i>MailChimp: <span id="mailChimpUserNormal"></span></li>
        <?php
          }
    }
    
    if ($sWorkPhone) {
  ?>
          <li><i class="fa-li fa fa-phone"></i><?= gettext('Work Phone') ?>: <span><a href="tel:<?= $sWorkPhoneUnformatted ?>"><?= $sWorkPhone ?></a></span></li>
  <?php
    } 
   
    if ($person->getWorkEmail() != '') {
  ?>
          <li><i class="fa-li fa fa-envelope"></i><?= gettext('Work/Other Email') ?>: <span><a href="mailto:<?= $person->getWorkEmail() ?>"><?= $person->getWorkEmail() ?></a></span></li>
  <?php 
     if ($isMailChimpActive) {
  ?>
        <li><i class="fa-li fa fa-send"></i>MailChimp: <span id="mailChimpUserWork"></span></li>
  <?php
        }
    }

    if ($person->getFacebookID() > 0) {
  ?>
        <li><i class="fa-li fa fa-facebook-official"></i><?= gettext('Facebook') ?>: <span><a href="https://www.facebook.com/<?= InputUtils::FilterInt($person->getFacebookID()) ?>"><?= gettext('Facebook') ?></a></span></li>
  <?php
    }

    if (strlen($person->getTwitter()) > 0) {
  ?>
        <li><i class="fa-li fa fa-twitter"></i><?= gettext('Twitter') ?>: <span><a href="https://www.twitter.com/<?= InputUtils::FilterString($person->getTwitter()) ?>"><?= gettext('Twitter') ?></a></span></li>
  <?php
    }

    if (strlen($person->getLinkedIn()) > 0) {
  ?>
        <li><i class="fa-li fa fa-linkedin"></i><?= gettext('LinkedIn') ?>: <span><a href="https://www.linkedin.com/in/<?= InputUtils::FiltersTring($person->getLinkedIn()) ?>"><?= gettext('LinkedIn') ?></a></span></li>
  <?php
    }
    
  } // end of $can_see_privatedata

    // Display the right-side custom fields
  foreach ($ormCustomFields as $rowCustomField) {
    if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
      $currentData = trim($aCustomData[$rowCustomField->getCustomField()]);
        if ($currentData != '') {
          if ($rowCustomField->getTypeId() == 11) {
            $custom_Special = $sPhoneCountry;
          } else {
            $custom_Special = $rowCustomField->getCustomSpecial();
          }
              
          echo '<li><i class="fa-li '.(($rowCustomField->getTypeId() == 11)?'fa fa-phone':'fa fa-tag').'"></i>'.$rowCustomField->getCustomName().': <span>';
              $temp_string=nl2br(OutputUtils::displayCustomField($rowCustomField->getTypeId(), $currentData, $custom_Special));
              echo $temp_string;
              echo '</span></li>';
          }
        }    
    }
?>
        </ul>
      </div>
    </div>
    <div class="alert alert-info alert-dismissable">
        <i class="fa fa-fw fa-tree"></i> <?php echo gettext('indicates items inherited from the associated family record.'); ?>
    </div>
    
  </div>
  <div class="col-lg-9 col-md-9 col-sm-9">
    <div class="box box-primary box-body">
      <?php
        if (Cart::PersonInCart($iPersonID) && $_SESSION['user']->isShowCartEnabled()) {
      ?>
        <a class="btn btn-app RemoveOneFromPeopleCart" id="AddPersonToCart" data-onecartpersonid="<?= $iPersonID ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= gettext("Remove from Cart") ?></span></a>
      <?php 
        } else if ($_SESSION['user']->isShowCartEnabled()) {
      ?>
          <a class="btn btn-app AddOneToPeopleCart" id="AddPersonToCart" data-onecartpersonid="<?= $iPersonID ?>"><i class="fa fa-cart-plus"></i><span class="cartActionDescription"><?= gettext("Add to Cart") ?></span></a>
      <?php 
       }
      ?>
      
      <?php       
       if ( $_SESSION['bEmailMailto'] && $person->getId() != $_SESSION['user']->getPersonId() ) {
      ?>
        <a class="btn btn-app" href="mailto:<?= urlencode($sEmail) ?>"><i class="fa fa-send-o"></i><?= gettext('Email') ?></a>
        <a class="btn btn-app" href="mailto:?bcc=<?= urlencode($sEmail) ?>"><i class="fa fa-send"></i><?= gettext('Email (BCC)') ?></a>
      <?php
       }
      ?>
      <?php 
        if ($person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() || $_SESSION['user']->isSeePrivacyDataEnabled()) {
          if ($person->getId() == $_SESSION['user']->getPersonId()) {
      ?>
            <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/SettingsIndividual.php"><i class="fa fa-cog"></i> <?= gettext("Change Settings") ?></a>
            <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/UserPasswordChange.php"><i class="fa fa-key"></i> <?= gettext("Change Password") ?></a>
      <?php
          }
      ?>
        <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/PrintView.php?PersonID=<?= $iPersonID ?>"><i class="fa fa-print"></i> <?= gettext("Printable Page") ?></a>
      <?php
       } 
      
       if ($_SESSION['user']->isPastoralCareEnabled()) {
      ?>
        <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/PastoralCare.php?PersonID=<?= $iPersonID ?>&linkBack=PersonView.php?PersonID=<?= $iPersonID ?>"><i class="fa fa-question-circle"></i> <?= gettext("Pastoral Care") ?></a>
      <?php
       }

       if ($_SESSION['user']->isDeleteRecordsEnabled() && $iPersonID != 1) {// the super user can't be deleted
         if ( count($person->getOtherFamilyMembers()) > 0 || is_null($person->getFamily()) ) {
    ?>        
        <a class="btn btn-app bg-maroon delete-person" data-person_name="<?= $person->getFullName()?>" data-person_id="<?= $iPersonID ?>"><i class="fa fa-trash-o"></i> <?= gettext("Delete this Record") ?></a>
    <?php
      } else {
    ?>
        <a class="btn btn-app bg-maroon" href="<?= SystemURLs::getRootPath() ?>/SelectDelete.php?FamilyID=<?= $person->getFamily()->getId() ?>"><i class="fa fa-trash-o"></i><?= gettext("Delete this Record") ?></a>
    <?php
      }
    }
    if ($_SESSION['user']->isManageGroupsEnabled()) {
  ?>
        <a class="btn btn-app" id="addGroup"><i class="fa fa-users"></i> <?= gettext("Assign New Group") ?></a>
  <?php
    }

    if ($_SESSION['user']->isAdmin()) {
        if (!$person->isUser()) {
  ?>
          <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/UserEditor.php?NewPersonID=<?= $iPersonID ?>"><i class="fa fa-user-secret"></i> <?= gettext('Make User') ?></a>
      <?php
        } else {
      ?>
          <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/UserEditor.php?PersonID=<?= $iPersonID ?>"><i class="fa fa-user-secret"></i> <?= gettext('Edit User') ?></a>
      <?php
        }
    } 
?>
      <a class="btn btn-app" role="button" href="<?= SystemURLs::getRootPath() ?>/SelectList.php?mode=person"><i class="fa fa-list"></i> <?= gettext("List Members") ?></span></a>      
    <?php 
      if ($bOkToEdit && $_SESSION['user']->isAdmin() && $iPersonID != 1) {// the super user can't be deleted
    ?>
        <button class="btn btn-app bg-orange" id="activateDeactivate">
            <i class="fa <?= (empty($person->getDateDeactivated()) ? 'fa-times-circle-o' : 'fa-check-circle-o') ?> "></i><?php echo((empty($person->getDateDeactivated()) ? gettext('Deactivate') : gettext('Activate')) . " " .gettext(' this Person')); ?>
        </button>
    <?php
      } 
    ?>
    </div>
  </div>
  
  <?php 
    if ($_SESSION['user']->isManageGroupsEnabled() || ($_SESSION['user']->isEditSelfEnabled() && $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() || $_SESSION['user']->isSeePrivacyDataEnabled() )) {
  ?>
  <div class="col-lg-9 col-md-9 col-sm-9">
    <div class="nav-tabs-custom">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <?php 
          $activeTab = "";
          if ( ($person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() ||  $_SESSION['user']->isSeePrivacyDataEnabled()) ) {
            $activeTab = "timeline";
        ?>
          <li role="presentation" <?= (!$bDocuments && !$bEDrive)?"class=\"active\"":""?>><a href="#timeline" aria-controls="timeline" role="tab" data-toggle="tab"><?= gettext('Timeline') ?></a></li>
        <?php
          }
        ?>
        <?php
          if ($person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() ||  count($person->getOtherFamilyMembers()) > 0 && $_SESSION['user']->isEditRecordsEnabled() ) {
        ?>
        <li role="presentation" <?= (empty($activeTab))?'class="active"':'' ?>><a href="#family" aria-controls="family" role="tab" data-toggle="tab"><?= gettext('Family') ?></a></li>
        <?php
          if (empty($activeTab)) {
            $activeTab = 'family';
          }
        }
        ?>
        <?php
          if ( $_SESSION['user']->isManageGroupsEnabled() || $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() ) {
        ?>
        <li role="presentation" <?= (empty($activeTab))?'class="active"':'' ?>><a href="#groups" aria-controls="groups" role="tab" data-toggle="tab"><i class="fa fa-group"></i> <?= gettext('Assigned Groups') ?></a></li>
        <?php
          if (empty($activeTab)) {
            $activeTab = 'group';
          }
        }
        ?>
        <?php
          if ( $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() ||  $_SESSION['user']->isEditRecordsEnabled() ) {
        ?>
        <li role="presentation" <?= (empty($activeTab))?'class="active"':'' ?>><a href="#properties" aria-controls="properties" role="tab" data-toggle="tab"><?= gettext('Assigned Properties') ?></a></li>
        <li role="presentation"><a href="#volunteer" aria-controls="volunteer" role="tab" data-toggle="tab"><?= gettext('Volunteer Opportunities') ?></a></li>
        <?php
            if (empty($activeTab)) {
              $activeTab = 'properties';
            }
          }
        ?>
        <?php
          if (count($person->getOtherFamilyMembers()) == 0 && $_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) {
        ?>
            <li role="presentation" <?= (empty($activeTab))?'class="active"':'' ?>><a href="#finance" aria-controls="finance" role="tab" data-toggle="tab"><i class="fa fa-credit-card"></i> <?= gettext("Automatic Payments") ?></a></li>
            <li role="presentation"><a href="#pledges" aria-controls="pledges" role="tab" data-toggle="tab"><i class="fa fa-bank"></i> <?= gettext("Pledges and Payments") ?></a></li>
        <?php
            if (empty($activeTab)) {
              $activeTab = 'finance';
            }
          } 
        ?>
        <?php
          if ( $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() ||  $_SESSION['user']->isNotesEnabled() ) {
            if ($bDocuments) $activeTab = 'notes';
        ?>
        <li role="presentation" <?= ($bDocuments)?"class=\"active\"":""?>><a href="#notes" aria-controls="notes" role="tab" data-toggle="tab" <?= ($bDocuments)?"aria-expanded=\"true\"":""?>><i class="fa fa-files-o"></i> <?= gettext("Notes") ?></a></li>
        <?php
          }
        ?>
        <?php
          if ( !is_null($user) && ( $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() ||  $_SESSION['user']->isNotesEnabled() ) ) {
            if ($bEDrive) $activeTab = 'edrive';
        ?>        
        <li role="presentation" <?= ($bEDrive)?"class=\"active\"":""?>><a href="#edrive" aria-controls="edrive" role="tab" data-toggle="tab" <?= ($bDocuments)?"aria-expanded=\"true\"":""?>><i class="fa fa-cloud"></i> <?= gettext("EDrive") ?></a></li>
        <?php
          }
        ?>
      </ul>

      <!-- Tab panes -->
      <div class="tab-content">
        <?php 
          if ( $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId() ||  $_SESSION['user']->isSeePrivacyDataEnabled() ) {
        ?>
        <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'timeline')?"active":"" ?>" id="timeline">
          <div class="row filter-note-type">
              <div class="col-md-1" style="line-height:27px">
              <table width=400px>
                <tr>
                  <td>
                  <span class="time-line-head-red">
                    <?php 
                      $now = new DateTime('');
                      echo $now->format(SystemConfig::getValue('sDateFormatLong'))
                    ?>
                  </span>
                  </td>
                  <td style="vertical-align: middle;">
                  </td>
                  <td>
                  </td>
                </tr>
              </table>
              </div>
          </div>
          <ul class="timeline time-line-main">
            <!-- timeline time label -->
            <!--<li class="time-label">
                    <span class="bg-red">
                      <?php $now = new DateTime('');
                      echo $now->format(SystemConfig::getValue('sDateFormatLong')) ?>
                    </span>
            </li>-->
            <li class="time-label">
            </li>
            <!-- /.timeline-label -->        
                
            <!-- timeline item -->
            <?php
              $countMainTimeLine = 0;  // number of items in the MainTimeLines

              foreach ($timelineServiceItems as $item) {
                 $countMainTimeLine++;
                 
                 if ($countMainTimeLine > $maxMainTimeLineItems) break;// we break after 20 $items
            ?>
              <li>
                <!-- timeline icon -->
                <i class="fa <?= $item['style'] ?>"></i>

                <div class="timeline-item">
                  <span class="time">
                    <i class="fa fa-clock-o"></i> <?= $item['datetime'] ?>
                  </span>
                  <?php
                    if (isset($item['style2']) ) {
                  ?>
                   <i class="fa <?= $item['style2'] ?> share-type-2"></i>
                  <?php
                    }
                  ?>
                
                  <h3 class="timeline-header">
                    <?php 
                      if (in_array('headerlink', $item)) {
                    ?>
                      <a href="<?= $item['headerlink'] ?>"><?= $item['header'] ?></a>
                    <?php
                      } else {
                    ?>
                      <?= $item['header'] ?>
                    <?php
                      } 
                    ?>
                  </h3>
                  

                  <div class="timeline-body">
                     <?php 
                       if ($item['type'] != 'file') { 
                     ?>
                      <pre style="line-height: 1.2;"><?= ((!empty($item['info']))?$item['info']." : ":"").$item['text'] ?></pre>
                     <?php 
                       } else {
                      ?>
                       <pre style="line-height: 1.2;"><?= ((!empty($item['info']))?$item['info']." : ":"").'<a href="'.SystemURLs::getRootPath().'/api/filemanager/getFile/'.$item['perID']."/".$item['text'].'"><i class="fa '.$item['style2'].'share-type-2"></i> "'.gettext("click to download").'"</a>' ?></pre>
                      <?php 
                        } 
                      ?>
                  </div>
                </div>
              </li>
            <?php
              } 
            ?>
            <!-- END timeline item -->
          </ul>
        </div>
        <?php
          }
        ?>
        <div role="tab-pane fade <?= ($activeTab == 'family')?"active":"" ?>" class="tab-pane" id="family">
      <?php 
        if ($person->getFamId() != '') {
      ?>
          <table class="table user-list table-hover">
            <thead>
            <tr>
              <th><span><?= gettext('Family Members') ?></span></th>
              <th class="text-center"><span><?= gettext('Role') ?></span></th>
              <th><span><?= gettext('Birthday') ?></span></th>
              <th><span><?= gettext('Email') ?></span></th>
              <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php 
              foreach ($person->getOtherFamilyMembers() as $familyMember) {
              $tmpPersonId = $familyMember->getId(); 
            ?>
              <tr>
                <td>
                  <img style="width:40px; height:40px;display:inline-block" src = "<?= $sRootPath.'/api/persons/'.$familyMember->getId().'/thumbnail' ?>" class="initials-image profile-user-img img-responsive img-circle no-border">
                  <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $tmpPersonId ?>" class="user-link"><?= $familyMember->getFullName() ?> </a>
                </td>
                <td class="text-center">
                  <?= $familyMember->getFamilyRoleName() ?>
                </td>
                <td>
                  <?= OutputUtils::FormatBirthDate($familyMember->getBirthYear(), $familyMember->getBirthMonth(), $familyMember->getBirthDay(), '-', $familyMember->getFlags()); ?>
                </td>
                <td>
              <?php 
                $tmpEmail = $familyMember->getEmail();
                
                if ($tmpEmail != '') {
              ?>
                  <a href="mailto:<?= $tmpEmail ?>"><?= $tmpEmail ?></a>
              <?php
                } 
              ?>
                </td>
                <td style="width: 20%;">
                  <?php
                    if ($_SESSION['user']->isShowCartEnabled()) {
                  ?>
                  <a class="AddToPeopleCart" data-cartpersonid="<?= $tmpPersonId ?>">
                    <span class="fa-stack">
                      <i class="fa fa-square fa-stack-2x"></i>
                      <i class="fa fa-cart-plus fa-stack-1x fa-inverse"></i>
                    </span>
                  </a>
                  <?php 
                    }
                 
                    if ($bOkToEdit) {
                  ?>
                    <a href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?PersonID=<?= $tmpPersonId ?>">
                      <span class="fa-stack"  style="color:green">
                        <i class="fa fa-square fa-stack-2x"></i>
                        <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                      </span>
                    </a>
                    <a class="delete-person" data-person_name="<?= $familyMember->getFullName() ?>" data-person_id="<?= $tmpPersonId ?>" data-view="family">
                      <span class="fa-stack" style="color:red">
                          <i class="fa fa-square fa-stack-2x"></i>
                          <i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
                      </span>
                    </a>
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
        <?php
          } 
        ?>
        </div>
        <div role="tab-pane fade <?= ($activeTab == 'group')?"active":"" ?>" class="tab-pane" id="groups">
          <div class="main-box clearfix">
            <div class="main-box-body clearfix">
            <?php
              //Was anything returned?
              if ($ormAssignedGroups->count() == 0) {
            ?>
                <br>
                <div class="alert alert-warning">
                  <i class="fa fa-question-circle fa-fw fa-lg"></i> <span><?= gettext('No group assignments.') ?></span>
                </div>
            <?php
              } else {
            ?>
            <?php
                  // Loop through the rows
                  $i = 1;
                  foreach ($ormAssignedGroups as $ormAssignedGroup) {
                    if ($i%3 == 0) {
            ?>
                  <div class="row">
            <?php
                    }
            ?>
                    <div class="col-md-4">
                      <!-- Info box -->
                      <div class="box box-info">
                        <div class="box-header">
                          <h3 class="box-title" style="font-size:small"><a href="<?= SystemURLs::getRootPath() ?>/GroupView.php?GroupID=<?= $ormAssignedGroup->getGroupID() ?>"><?= $ormAssignedGroup->getGroupName() ?></a></h3>

                          <div class="box-tools pull-right">
                            <div class="label bg-aqua"><?= gettext($ormAssignedGroup->getRoleName()) ?></div>
                          </div>
                        </div>
                        <div class="box-footer" style="width:275px">
                            <?php 
                              if ($_SESSION['user']->isManageGroupsEnabled()) {
                            ?>
                             <code>
                              <a href="<?= SystemURLs::getRootPath() ?>/GroupView.php?GroupID=<?= $ormAssignedGroup->getGroupID() ?>" class="btn btn-default" role="button"><i class="fa fa-list"></i></a>
                              <div class="btn-group">
                                <button type="button" class="btn btn-default"><?= gettext('Action') ?></button>
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                  <li><a  class="changeRole" data-groupid="<?= $ormAssignedGroup->getGroupID() ?>"><?= gettext('Change Role') ?></a></li>
                                  <?php 
                                    if ($grp_hasSpecialProps) {
                                  ?>
                                    <li><a href="<?= SystemURLs::getRootPath() ?>/GroupPropsEditor.php?GroupID=<?= $ormAssignedGroup->getGroupID() ?>&PersonID=<?= $iPersonID ?>"><?= gettext('Update Properties') ?></a></li>
                                  <?php
                                    } 
                                  ?>
                                </ul>
                              </div>
                              <div class="btn-group">
                                 <button data-groupid="<?= $ormAssignedGroup->getGroupID() ?>" data-groupname="<?= $ormAssignedGroup->getGroupName() ?>" type="button" class="btn btn-danger groupRemove" data-toggle="dropdown"><i class="fa fa-trash-o"></i></button>
                              </div>
                          </code>
                        <?php
                          } 
                        ?>
                        </div>

                        <?php
                          // If this group has associated special properties, display those with values and prop_PersonDisplay flag set.
                          if ( $ormAssignedGroup->getHasSpecialProps() ) {
                            // Get the special properties for this group only for the group
                            $ormPropLists = GroupPropMasterQuery::Create()->filterByPersonDisplay('false')->orderByPropId()->findByGroupId($ormAssignedGroup->getGroupId());
                        ?>

                        <div class="box-body">

                        <?php  
                            if ( $ormPropLists->count() > 0 ) {
                        ?>
                          
                            <h4><?= gettext("Group Informations") ?></h4>
                            <ul>
                        <?php
                              foreach ($ormPropLists as $ormPropList) {
                                if ($ormPropList->getTypeId() == 11) {
                                  $prop_Special = $sPhoneCountry;
                                }  
                        ?>
                                <li><strong><?= $ormPropList->getName() ?></strong>: <?= OutputUtils::displayCustomField($ormPropList->getTypeId(), $ormPropList->getDescription(), $ormPropList->getSpecial()) ?></li>
                        <?php
                              }
                        ?>
                            </ul>
                        <?php
                            }

                            $ormPropLists = GroupPropMasterQuery::Create()->filterByPersonDisplay('true')->orderByPropId()->findByGroupId($ormAssignedGroup->getGroupId());
                          
                            $sSQL = 'SELECT * FROM groupprop_'.$ormAssignedGroup->getGroupId().' WHERE per_ID = '.$iPersonID;
                            
                            $statement = $connection->prepare($sSQL);
                            $statement->execute();
                            $aPersonProps = $statement->fetch( PDO::FETCH_BOTH );

                            if ( $ormPropLists->count() > 0 ) {
                        ?>
                            <h4><?= gettext("Person Informations") ?></h4>
                            <ul>
                            <?php
                              foreach ($ormPropLists as $ormPropList) {
                                $currentData = trim($aPersonProps[$ormPropList->getField()]);
                                if (strlen($currentData) > 0) {
                                    if ($type_ID == 11) {
                                        $prop_Special = $sPhoneCountry;
                                    }
                            ?>
                                    <li><strong><?= $ormPropList->getName() ?></strong>: <?= OutputUtils::displayCustomField($ormPropList->getTypeId(), $currentData, $ormPropList->getSpecial()) ?></li>
                            <?php
                                }
                              }
                          
                        ?>
                            </ul>
                          <a href="GroupPersonPropsFormEditor.php?GroupID=<?= $ormAssignedGroup->getGroupId() ?>&PersonID=<?= $iPersonID ?>" class="btn btn-primary"><?= gettext("Modify Specific Properties")?></a>
                        <?php
                            }
                        ?>

                          </div><!-- /.box-body -->
                        <?php
                          } 
                        ?>
                      
                        <!-- /.box-footer-->
                      </div>
                      <!-- /.box -->
                    </div>
                <?php
                  // NOTE: this method is crude.  Need to replace this with use of an array.
                  $sAssignedGroups .= $ormAssignedGroup->getGroupID().',';
                    if ($i%3 == 0) {
            ?>
                  </div>
            <?php
                    }
                    $i++;
                  }
              }
           ?>
            </div>
          </div>
        </div>
        <div role="tab-pane fade <?= ($activeTab == 'properties')?"active":"" ?>" class="tab-pane" id="properties">
          <div class="main-box clearfix">
          <div class="main-box-body clearfix">
            <div class="alert alert-warning" id="properties-warning" <?= ($ormAssignedProperties->count() > 0)?'style="display: none;"':''?>>
                <i class="fa fa-question-circle fa-fw fa-lg"></i> <span><?= gettext('No property assignments.') ?></span>
            </div>
            <?php
               $sAssignedProperties = ','; 
            ?>
            
            <div id="properties-table" <?= ($ormAssignedProperties->count() == 0)?'style="display: none;"':''?>>
              <table class="table table-condensed dt-responsive" id="assigned-properties-table" width="100%"></table>
            </div>

              <?php if ($_SESSION['user']->isEditRecordsEnabled() && $bOkToEdit && $ormProperties->count() != 0): ?>
                <div class="alert alert-info">
                  <div>
                    <h4><strong><?= gettext('Assign a New Property') ?>:</strong></h4>
                        <div class="row">
                            <div class="form-group col-xs-12 col-md-7">
                                <select name="PropertyId" id="input-person-properties" class="form-control input-person-properties select2"
                                    style="width:100%" data-placeholder="<?= gettext("Select") ?> ..."  data-personID="<?= $iPersonID ?>">
                                <option disabled selected> -- <?= gettext('select an option') ?> -- </option>
                                <?php
                                  foreach ($ormProperties as $ormProperty) {
                                      $attributes = "value=\"{$ormProperty->getProId()}\" ";
                                          if (strlen(strstr($sAssignedProperties, ','.$ormProperty->getProId().',')) == 0) {
                                          ?>
                                              <option value="<?= $ormProperty->getProId() ?>" data-pro_Prompt="<?= $ormProperty->getProPrompt() ?>" data-pro_Value=""><?= $ormProperty->getProName() ?></option>    
                                        <?php }      
          
                                } ?>
                                </select>
                            </div>
                            <div id="prompt-box" class="col-xs-12 col-md-7">

                            </div>
                            <div class="form-group col-xs-12 col-md-7">
                                <input id="assign-property-btn" type="submit" class="btn btn-primary  assign-property-btn" value="<?= gettext('Assign') ?>">
                            </div>
                        </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'finance')?"active":"" ?>" id="volunteer">
          <div class="main-box clearfix">
            <div class="main-box-body clearfix">
          <?php

            //Initialize row shading
            $sRowClass = 'RowColorA';

            $sAssignedVolunteerOpps = ',';

           //Was anything returned?
           ?>
              <div class="alert alert-warning" id="volunter-warning" <?= ($ormAssignedVolunteerOpps->count() > 0)?'style="display: none;"':''?>>
                <i class="fa fa-question-circle fa-fw fa-lg"></i> <span><?= gettext('No volunteer opportunity assignments.') ?></span>
              </div>
        
              <div id="volunter-table" <?= ($ormAssignedVolunteerOpps->count() == 0)?'style="display: none;"':''?>>
                 <table class="table table-condensed dt-responsive" id="assigned-volunteer-opps-table" width="100%"></table>
              </div>

              <?php 
                if ($_SESSION['user']->isEditRecordsEnabled() && $ormVolunteerOpps->count()) { 
              ?>
                <div class="alert alert-info">
                  <div>
                    <h4><strong><?= gettext('Assign a New Volunteer Opportunity') ?>:</strong></h4>

                    <div class="row">
                    <div class="form-group col-xs-12 col-md-7">
                      <select id="input-volunteer-opportunities" name="VolunteerOpportunityIDs[]" multiple class="form-control select2" style="width:100%" data-placeholder="<?= gettext("Select") ?>...">
                      <?php
                        foreach ($ormVolunteerOpps as $ormVolunteerOpp) {
                            //If the property doesn't already exist for this Person, write the <OPTION> tag
                            if (strlen(strstr($sAssignedVolunteerOpps, ','.$vol_ID.',')) == 0) {
                      ?>
                          <option value="<?= $ormVolunteerOpp->getId() ?>"><?= $ormVolunteerOpp->getName() ?></option>
                      <?php
                            }
                        } 
                      ?>
                        </select>
                      </div>
                      <div class="form-group col-xs-12 col-md-7">
                          <input type="submit" value="<?= gettext('Assign') ?>" name="VolunteerOpportunityAssign" class="btn btn-primary VolunteerOpportunityAssign">
                      </div>
                    </div>
                </div>
              </div>
            <?php 
              } 
            ?>
          </div>
        </div>
      </div>
      <?php 
        if ($_SESSION['user']->isFinanceEnabled() && !is_null ($person->getFamily()) ) {
      ?>
       <div role="tab-pane fade" class="tab-pane" id="finance">
          <div class="main-box clearfix">
              <div class="main-box-body clearfix">
              <?php 
                if ($ormAutoPayments->count() > 0) {
              ?>    
                  <table class="table table-striped table-bordered" id="automaticPaymentsTable" cellpadding="5" cellspacing="0"  width="100%"></table>
              <?php
                } 
              ?>
                  <p align="center">
                      <a class="btn btn-primary"
                         href="AutoPaymentEditor.php?AutID=-1&FamilyID=<?= $person->getFamily()->getId() ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>"><?= gettext("Add a new automatic payment") ?></a>
                  </p>
              </div>
          </div>
        </div>
        <div role="tab-pane fade" class="tab-pane" id="pledges">
          <div class="main-box clearfix">
              <div class="main-box-body clearfix">
                  <input type="checkbox" name="ShowPledges" id="ShowPledges" value="1" <?= ($_SESSION['sshowPledges'])?" checked":"" ?>><?= gettext("Show Pledges") ?>
                  <input type="checkbox" name="ShowPayments" id="ShowPayments" value="1" <?= ($_SESSION['sshowPayments'])?" checked":"" ?>><?= gettext("Show Payments") ?>
                  <label for="ShowSinceDate"><?= gettext("From") ?>:</label>
                  <input type="text" Name="Min" id="Min" value="<?= date("Y") ?>" maxlength="10" id="ShowSinceDate" size="15">                       
                  <label for="ShowSinceDate"><?= gettext("To") ?>:</label>
                  <input type="text" Name="Max" id="Max" value="<?= date("Y") ?>" maxlength="10" id="ShowSinceDate" size="15">
                <?php
                  $tog = 0;

                  if ($_SESSION['sshowPledges'] || $_SESSION['sshowPayments']) {
                ?>
                  <table id="pledgePaymentTable" class="table table-striped table-bordered"  cellspacing="0" width="100%"></table>
                <?php
                  } // if bShowPledges
                ?>
                    
                  <p align="center">
                      <a class="btn btn-primary"
                         href="PledgeEditor.php?FamilyID=<?= $person->getFamily()->getId() ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>&amp;PledgeOrPayment=Pledge"><?= gettext("Add a new pledge") ?></a>
                      <a class="btn btn-default"
                         href="PledgeEditor.php?FamilyID=<?= $person->getFamily()->getId() ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>&amp;PledgeOrPayment=Payment"><?= gettext("Add a new payment") ?></a>
                  </p>

              <?php 
                if ($_SESSION['user']->isCanvasserEnabled()) {
              ?>
                  <p align="center">
                      <a class="btn btn-default"
                         href="CanvassEditor.php?FamilyID=<?= $person->getFamily()->getId() ?>&amp;FYID=<?= $_SESSION['idefaultFY'] ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>"><?= MakeFYString($_SESSION['idefaultFY']) . gettext(" Canvass Entry") ?></a>
                  </p>
              <?php
                } 
              ?>
                </div>
            </div>
        </div>
      <?php
        } 
      ?>
        <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'notes')?"active":"" ?>" id="notes" >
          <div class="row filter-note-type">
              <div class="col-md-1" style="line-height:27px">
                <table width=370px>
                  <tr>
                    <td>
                    <span class="time-line-head-yellow">
                      <?php echo date_create()->format(SystemConfig::getValue('sDateFormatLong')) ?>
                    </span>
                    </td>
                    <td style="vertical-align: middle;">
                        <labe><?= gettext("Show") ?> : </label>
                    </td>
                    <td>
                        <select name="PropertyId" class="filter-timeline form-control input-sm" style="width:170px" data-placeholder="<?= gettext("Select") ?> ...">
                            <option value="all"><?= gettext("All type") ?></option>
                            <option value="note"><?= MiscUtils::noteType("note") ?></option>
                            <option value="video"><?= MiscUtils::noteType("video") ?></option>
                            <option value="audio"><?= MiscUtils::noteType("audio") ?></option>
                            <option disabled="disabled">_____________________________</option>
                            <option value="shared"><?= gettext("Shared documents") ?></option>
                        </select>
                    </td>
                    <?php 
                      if ($_SESSION['user']->isNotesEnabled() || ($_SESSION['user']->isEditSelfEnabled() && $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId())) {
                    ?>
                    <td>
                      <a href="<?= SystemURLs::getRootPath() ?>/NoteEditor.php?PersonID=<?= $iPersonID ?>&documents=true"  data-toggle="tooltip" data-placement="top" data-original-title="<?= gettext("Create a note") ?>">
                        <span class="fa-stack" data-personid="<?= $iPersonID ?>">
                            <i class="fa fa-square fa-stack-2x" style="color:green"></i>
                            <i class="fa fa-file-o fa-stack-1x fa-inverse"></i>
                        </span>
                      </a>
                    </td>
                    <?php
                      } 
                    ?>
                  </tr>
                </table>
              </div>
          </div>
          <ul class="timeline time-line-note">
            <!-- note time label -->
            <li class="time-label"></li>
            <!-- /.note-label -->

            <!-- note item -->
            <?php 
              $note_content = "";// this assume only the last note is visible
              
              foreach ($timelineNotesServiceItems as $item) {
                if ( $note_content != $item['text'] && $item['type'] != 'file') {// this assume only the last note is visible
                 
                 $note_content = $item['text']; // this assume only the last note is visible
            ?>
              <li class="type-<?= $item['type'] ?><?= (isset($item['style2'])?" type-shared":"") ?>">
                <!-- timeline icon -->
                <i class="fa <?= $item['style'] ?> icon-<?= $item['type'] ?><?= (isset($item['style2'])?" icon-shared":"") ?>" ></i>
 
                <div class="timeline-item">
                  <span class="time">
                     <i class="fa fa-clock-o"></i> <?= $item['datetime'] ?>
                      &nbsp;
                     <?php 
                     
                     if ( $item['slim'] && ( !isset($item['currentUserName']) || $item['userName'] == $person->getFullName() ) ) {
                       if ($item['editLink'] != '' || (isset($item['sharePersonID']) && $item['shareRights'] == 2 ) ) {
                     ?>
                      <a href="<?= $item['editLink'] ?>">
                        <span class="fa-stack">
                          <i class="fa fa-square fa-stack-2x"></i>
                          <i class="fa fa-edit fa-stack-1x fa-inverse"></i>
                        </span>
                      </a>
                      <?php
                        }
                        if ($item['deleteLink'] != '' && !isset($item['sharePersonID']) && ( !isset($item['currentUserName']) || $item['userName'] == $person->getFullName() ) ) {
                      ?>
                      <a href="<?= $item['deleteLink'] ?>">
                        <span class="fa-stack">
                          <i class="fa fa-square fa-stack-2x" style="color:red"></i>
                          <i class="fa fa-trash fa-stack-1x fa-inverse" ></i>
                        </span>
                      </a>
                      <?php
                        }
                        if (!isset($item['sharePersonID']) && ( !isset($item['currentUserName']) || $item['userName'] == $person->getFullName() ) ) {
                      ?>
                        <span class="fa-stack shareNote" data-id="<?= $item['id'] ?>" data-shared="<?= $item['isShared'] ?>">
                          <i class="fa fa-square fa-stack-2x" style="color:<?= $item['isShared']?"green":"#777" ?>"></i>
                          <i class="fa fa-share-square-o fa-stack-1x fa-inverse" ></i>
                        </span>
                      <?php
                        }
                      } ?>
                    </span>

                 <?php
                  if (isset($item['style2']) ) {
                 ?>
                   <i class="fa <?= $item['style2'] ?> share-type-2"></i>
                <?php
                  }
                ?>
                  <h3 class="timeline-header">

                    <?php 
                      if (in_array('headerlink', $item) && !isset($item['sharePersonID'])) {
                    ?>
                      <a href="<?= $item['headerlink'] ?>"><?= $item['header'] ?></a>
                    <?php
                      } else {
                    ?>
                      <?= $item['header'] ?>
                    <?php
                      } 
                    ?>
                  </h3>

                  <div class="timeline-body">
                  <?php
                     if (isset($item['currentUserName'])) {
                  ?>
                          <p class="text-danger"><small><?= $item['currentUserName'] ?></small></p><br>
                  <?php
                     }                    
                  ?>
                      <?= ((!empty($item['info']))?$item['info']." : ":"").$item['text'] ?>
                  </div>

                  <?php 
                    if (($_SESSION['user']->isNotesEnabled()) && ($item['editLink'] != '' || $item['deleteLink'] != '')) {
                  ?>
                    <div class="timeline-footer">
                  <?php 
                    if (!$item['slim']) {
                      if ($item['editLink'] != '') {
                  ?>
                        <a href="<?= $item['editLink'] ?>">
                          <button type="button" class="btn btn-primary"><i class="fa fa-edit"></i></button>
                        </a>
                  <?php
                      }
                            
                      if ($item['deleteLink'] != '') {
                  ?>
                        <a href="<?= $item['deleteLink'] ?>">
                          <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                        </a>
                  <?php
                        }
                         
                        if (!isset($item['sharePersonID']) ) {
                  ?>
                        <button type="button" data-id="<?= $item['id'] ?>" data-shared="<?= $item['isShared'] ?>" class="btn btn-<?= $item['isShared']?"success":"default" 
                        ?> shareNote"><i class="fa fa-share-square-o"></i></button>
                  <?php
                        }
                  ?>
                    </div>
                  <?php
                    }
                  } ?>
                </div>
              </li>
            <?php
                }
              } 
            ?>
            <!-- END timeline item -->
          </ul>
        </div>
        <div role="tab-pane fade" class="tab-pane <?= ($activeTab == 'edrive')?"active":"" ?>" id="edrive">
          <div class="row filter-note-type" style="line-height:54px">
              <div class="col-md-8" style="line-height:27px">
                <table width=400px>
                  <tr>
                    <td>
                      <span class="time-line-head-red">
                        <?= gettext("All Files") ?>
                      </span>
                      &nbsp;&nbsp;&nbsp;
                      <?php 
                        if ($_SESSION['user']->isNotesEnabled() || ($_SESSION['user']->isEditSelfEnabled() && $person->getId() == $_SESSION['user']->getPersonId() || $person->getFamId() == $_SESSION['user']->getPerson()->getFamId())) {
                      ?>
                        <a href="#" id="uploadFile">
                          <span class="fa-stack fa-special-icon drag-elements" data-personid="<?= $iPersonID ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?= gettext("Upload a file in EDrive") ?>">
                            <i class="fa fa-square fa-stack-2x" style="color:green"></i>
                            <i class="fa fa-cloud-upload fa-stack-1x fa-inverse"></i>
                          </span>
                        </a>
                      <?php 
                        }
                      ?>

                      <a class="new-folder" data-personid="<?= $iPersonID ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?= gettext("Create a Folder") ?>">
                      <span class="fa-stack fa-special-icon drag-elements">
                        <i class="fa fa-square fa-stack-2x" style="color:blue"></i>
                        <i class="fa fa-folder-o fa-stack-1x fa-inverse"></i>
                      </span>
                      </a>

                      <a class="trash-drop" data-personid="<?= $iPersonID ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?= gettext("Delete") ?>">
                      <span class="fa-stack fa-special-icon drag-elements">
                        <i class="fa fa-square fa-stack-2x" style="color:red"></i>
                        <i class="fa fa-trash fa-stack-1x fa-inverse"></i>
                      </span>
                      </a>

                      <a class="folder-back-drop" data-personid="<?= $iPersonID ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?= gettext("Up One Level") ?>" <?= ( !is_null ($user) && $user->getCurrentpath() != "/")?"":'style="display: none;"' ?>>
                        <span class="fa-stack fa-special-icon drag-elements">
                          <i class="fa fa-square fa-stack-2x" style="color:navy"></i>
                          <i class="fa fa-level-up fa-stack-1x fa-inverse"></i>
                        </span>
                      </a>
                    </td>
                  </tr>
                </table>
              </div>
          </div>
          <br>
          <br>
          <div class="row">
              <div class="col-md-12 filmanager-left">
                <table class="table table-striped table-bordered" id="edrive-table" width="100%"></table>
              </div>
              <div class="col-md-3 filmanager-right" style="display: none;">
                 <h3><?= gettext("Preview") ?><button type="button" class="close close-file-preview" data-dismiss="alert" aria-hidden="true">×</button></h3>
                 <span class="preview"></span>
              </div>
          </div>
          <hr/>
          <div class="row">
              <div class="col-md-12">
                <span  class="float-left" id="currentPath">
                  <?= !is_null($user)?MiscUtils::pathToPathWithIcons($user->getCurrentpath()):"" ?>
                </span>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  }
?>

<!-- Modal -->
<div id="photoUploader">

</div>

<div class="modal fade" id="confirm-delete-image" tabindex="-1" role="dialog" aria-labelledby="delete-Image-label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="delete-Image-label"><?= gettext('Confirm Delete') ?></h4>
      </div>

      <div class="modal-body">
        <p><?= gettext('You are about to delete the profile photo, this procedure is irreversible.') ?></p>

        <p><?= gettext('Do you want to proceed?') ?></p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= gettext("Cancel") ?></button>
        <button class="btn btn-danger danger" id="deletePhoto"><?= gettext("Delete") ?></button>
      </div>
    </div>
  </div>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-photo-uploader/PhotoUploader.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/MemberView.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/PersonView.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/filemanager.js"></script>

<!-- Drag and drop -->
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui/jquery-ui.min.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
<!-- !Drag and Drop -->

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.currentPersonID = <?= $iPersonID ?>;
  window.CRM.currentFamily   = <?= $iFamilyID ?>;
  window.CRM.iPhotoHeight    = <?= SystemConfig::getValue("iPhotoHeight") ?>;
  window.CRM.iPhotoWidth     = <?= SystemConfig::getValue("iPhotoWidth") ?>;
  window.CRM.currentActive   = <?= (empty($person->getDateDeactivated()) ? 'true' : 'false') ?>;
  window.CRM.personFullName  = "<?= $person->getFullName() ?>";
  window.CRM.normalMail      = "<?= $sEmail ?>";
  window.CRM.workMail        = "<?= $person->getWorkEmail() ?>";
  
  if ( (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
      (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)) ) ) {
    $( ".fa-special-icon" ).addClass( "fa-2x" );
  }
</script>

<?php require 'Include/Footer.php' ?>