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

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Service\TimelineService;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\Utils\MiscUtils;


$timelineService = new TimelineService();
$mailchimp = new MailChimpService();

// Set the page title and include HTML header

$sPageTitle = gettext('Person Profile');
require 'Include/Header.php';

// Get the person ID from the querystring
$iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');

$iRemoveVO = 0;
if (array_key_exists('RemoveVO', $_GET)) {
    $iRemoveVO = InputUtils::LegacyFilterInput($_GET['RemoveVO'], 'int');
}

if (isset($_POST['VolunteerOpportunityAssign']) && $_SESSION['bEditRecords']) {
    $volIDs = $_POST['VolunteerOpportunityIDs'];
    if ($volIDs) {
        foreach ($volIDs as $volID) {
            AddVolunteerOpportunity($iPersonID, $volID);
        }
    }
}

// Service remove-volunteer-opportunity (these links set RemoveVO)
if ($iRemoveVO > 0 && $_SESSION['bEditRecords']) {
    RemoveVolunteerOpportunity($iPersonID, $iRemoveVO);
}

// Get this person's data
$sSQL = "SELECT a.*, family_fam.*, COALESCE(cls.lst_OptionName , 'Unassigned') AS sClassName, fmr.lst_OptionName AS sFamRole, b.per_FirstName AS EnteredFirstName, b.per_ID AS EnteredId,
        b.Per_LastName AS EnteredLastName, c.per_FirstName AS EditedFirstName, c.per_LastName AS EditedLastName, c.per_ID AS EditedId
      FROM person_per a
      LEFT JOIN family_fam ON a.per_fam_ID = family_fam.fam_ID
      LEFT JOIN list_lst cls ON a.per_cls_ID = cls.lst_OptionID AND cls.lst_ID = 1
      LEFT JOIN list_lst fmr ON a.per_fmr_ID = fmr.lst_OptionID AND fmr.lst_ID = 2
      LEFT JOIN person_per b ON a.per_EnteredBy = b.per_ID
      LEFT JOIN person_per c ON a.per_EditedBy = c.per_ID
      WHERE a.per_ID = ".$iPersonID;
$rsPerson = RunQuery($sSQL);
extract(mysqli_fetch_array($rsPerson));


$person = PersonQuery::create()->findPk($iPersonID);

$iFamilyID = $person->getFamId();

//Get the pledges for this family
$ormPledges = PledgeQuery::Create()
            ->leftJoinPerson()
            ->withColumn('Person.FirstName', 'EnteredFirstName')
            ->withColumn('Person.LastName', 'EnteredLastName')
            ->leftJoinDonationFund()
            ->withColumn('DonationFund.Name', 'fundName')
            ->findByFamId($iFamilyID);

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


if (empty($person)) {
    Redirect('members/404.php?type=Person');
    exit;
}

$assignedProperties = Record2propertyR2pQuery::Create()
  ->findByR2pRecordId($iPersonID);

// Get the lists of custom person fields
$sSQL = 'SELECT person_custom_master.* FROM person_custom_master
  ORDER BY custom_Order';
$rsCustomFields = RunQuery($sSQL);

// Get the custom field data for this person.
$sSQL = 'SELECT * FROM person_custom WHERE per_ID = '.$iPersonID;
$rsCustomData = RunQuery($sSQL);
$aCustomData = mysqli_fetch_array($rsCustomData, MYSQLI_BOTH);

// Get the Groups this Person is assigned to
$sSQL = 'SELECT grp_ID, grp_Name, grp_hasSpecialProps, role.lst_OptionName AS roleName
FROM group_grp
LEFT JOIN person2group2role_p2g2r ON p2g2r_grp_ID = grp_ID
LEFT JOIN list_lst role ON lst_OptionID = p2g2r_rle_ID AND lst_ID = grp_RoleListID
WHERE person2group2role_p2g2r.p2g2r_per_ID = '.$iPersonID.'
ORDER BY grp_Name';
$rsAssignedGroups = RunQuery($sSQL);
$sAssignedGroups = ',';

// Get all the Groups
$sSQL = 'SELECT grp_ID, grp_Name FROM group_grp ORDER BY grp_Name';
$rsGroups = RunQuery($sSQL);

// Get the volunteer opportunities this Person is assigned to
$sSQL = 'SELECT vol_ID, vol_Name, vol_Description FROM volunteeropportunity_vol
LEFT JOIN person2volunteeropp_p2vo ON p2vo_vol_ID = vol_ID
WHERE person2volunteeropp_p2vo.p2vo_per_ID = '.$iPersonID.' ORDER by vol_Order';
$rsAssignedVolunteerOpps = RunQuery($sSQL);

// Get all the volunteer opportunities
$sSQL = 'SELECT vol_ID, vol_Name FROM volunteeropportunity_vol ORDER BY vol_Order';
$rsVolunteerOpps = RunQuery($sSQL);

// Get the Properties assigned to this Person
$sSQL = "SELECT pro_Name, pro_ID, pro_Prompt, r2p_Value, prt_Name, pro_prt_ID
FROM record2property_r2p
LEFT JOIN property_pro ON pro_ID = r2p_pro_ID
LEFT JOIN propertytype_prt ON propertytype_prt.prt_ID = property_pro.pro_prt_ID
WHERE pro_Class = 'p' AND r2p_record_ID = ".$iPersonID.
' ORDER BY prt_Name, pro_Name';
$rsAssignedProperties = RunQuery($sSQL);

//Get all the properties
$ormProperties = PropertyQuery::Create()
                  ->filterByProClass('p')
                  ->orderByProName()
                  ->find();


// Get Field Security List Matrix
$sSQL = 'SELECT * FROM list_lst WHERE lst_ID = 5 ORDER BY lst_OptionSequence';
$rsSecurityGrp = RunQuery($sSQL);

while ($aRow = mysqli_fetch_array($rsSecurityGrp)) {
    extract($aRow);
    $aSecurityType[$lst_OptionID] = $lst_OptionName;
}

$dBirthDate = OutputUtils::FormatBirthDate($per_BirthYear, $per_BirthMonth, $per_BirthDay, '-', $per_Flags);

$sFamilyInfoBegin = '<span style="color: red;">';
$sFamilyInfoEnd = '</span>';

// Assign the values locally, after selecting whether to display the family or person information

//Get an unformatted mailing address to pass as a parameter to a google maps search
SelectWhichAddress($Address1, $Address2, $per_Address1, $per_Address2, $fam_Address1, $fam_Address2, false);
$sCity = SelectWhichInfo($per_City, $fam_City, false);
$sState = SelectWhichInfo($per_State, $fam_State, false);
$sZip = SelectWhichInfo($per_Zip, $fam_Zip, false);
$sCountry = SelectWhichInfo($per_Country, $fam_Country, false);
$plaintextMailingAddress = $person->getAddress();

//Get a formatted mailing address to use as display to the user.
SelectWhichAddress($Address1, $Address2, $per_Address1, $per_Address2, $fam_Address1, $fam_Address2, true);
$sCity = SelectWhichInfo($per_City, $fam_City, true);
$sState = SelectWhichInfo($per_State, $fam_State, true);
$sZip = SelectWhichInfo($per_Zip, $fam_Zip, true);
$sCountry = SelectWhichInfo($per_Country, $fam_Country, true);
$formattedMailingAddress = $person->getAddress();

$sPhoneCountry = SelectWhichInfo($per_Country, $fam_Country, false);
$sHomePhone = SelectWhichInfo(ExpandPhoneNumber($per_HomePhone, $sPhoneCountry, $dummy),
ExpandPhoneNumber($fam_HomePhone, $fam_Country, $dummy), true);
$sHomePhoneUnformatted = SelectWhichInfo(ExpandPhoneNumber($per_HomePhone, $sPhoneCountry, $dummy),
ExpandPhoneNumber($fam_HomePhone, $fam_Country, $dummy), false);
$sWorkPhone = SelectWhichInfo(ExpandPhoneNumber($per_WorkPhone, $sPhoneCountry, $dummy),
ExpandPhoneNumber($fam_WorkPhone, $fam_Country, $dummy), true);
$sWorkPhoneUnformatted = SelectWhichInfo(ExpandPhoneNumber($per_WorkPhone, $sPhoneCountry, $dummy),
ExpandPhoneNumber($fam_WorkPhone, $fam_Country, $dummy), false);
$sCellPhone = SelectWhichInfo(ExpandPhoneNumber($per_CellPhone, $sPhoneCountry, $dummy),
ExpandPhoneNumber($fam_CellPhone, $fam_Country, $dummy), true);
$sCellPhoneUnformatted = SelectWhichInfo(ExpandPhoneNumber($per_CellPhone, $sPhoneCountry, $dummy),
ExpandPhoneNumber($fam_CellPhone, $fam_Country, $dummy), false);
$sEmail = SelectWhichInfo($per_Email, $fam_Email, true);
$sUnformattedEmail = SelectWhichInfo($per_Email, $fam_Email, false);

if ($per_Envelope > 0) {
    $sEnvelope = $per_Envelope;
} else {
    $sEnvelope = gettext('Not assigned');
}

$iTableSpacerWidth = 10;

$bOkToEdit = ($_SESSION['bEditRecords'] ||
    ($_SESSION['bEditSelf'] && $per_ID == $_SESSION['iUserID']) ||
    ($_SESSION['bEditSelf'] && $per_fam_ID == $_SESSION['iFamID'])
    );

?>
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
            <?php if ($person->isMale()) {
    ?>
                <i class="fa fa-male"></i>
            <?php
} elseif ($person->isFemale()) {
        ?>
                <i class="fa fa-female"></i>
            <?php
    } ?>
          <?= $person->getFullName() ?></h3>

        <p class="text-muted text-center">
            <?= empty($sFamRole) ? gettext('Undefined') : gettext($sFamRole); ?>
            &nbsp;
            <a id="edit-role-btn" data-person_id="<?= $person->getId() ?>" data-family_role="<?= $person->getFamilyRoleName() ?>"
            data-family_role_id="<?= $person->getFmrId() ?>"  class="btn btn-primary btn-xs">
                <i class="fa fa-pencil"></i>
            </a>
        </p>

        <p class="text-muted text-center">
          <?= gettext($sClassName);
    if ($per_MembershipDate) {
        echo gettext(' Since:').' '.OutputUtils::FormatDate($per_MembershipDate, false);
    } ?>
        </p>
        <?php if ($bOkToEdit) {
        ?>
          <a href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?PersonID=<?= $per_ID ?>" class="btn btn-primary btn-block"><b><?php echo gettext('Edit'); ?></b></a>
        <?php
    } ?>
      </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->

    <!-- About Me Box -->
    <?php 
      if ($per_ID == $_SESSION['user']->getPersonId() || $per_fam_ID == $_SESSION['iFamID']  || $_SESSION['bSeePrivacyData'] || $_SESSION['bAdmin']) { 
    ?>
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title text-center"><?php echo gettext('About Me'); ?></h3>
      </div>
      <!-- /.box-header -->
      <div class="box-body">
        <ul class="fa-ul">
        <?php
          if (count($person->getOtherFamilyMembers()) > 0) {
        ?>
          <li><i class="fa-li fa fa-group"></i><?php echo gettext('Family:'); ?> <span>
              <?php
              if ($fam_ID != '') {
                  ?>
                <a href="<?= SystemURLs::getRootPath() ?>/FamilyView.php?FamilyID=<?= $fam_ID ?>"><?= $fam_Name ?> </a>
                <a href="<?= SystemURLs::getRootPath() ?>/FamilyEditor.php?FamilyID=<?= $fam_ID ?>" class="table-link">
                  <span class="fa-stack">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                  </span>
                </a>
              <?php
              } else {
                  echo gettext('(No assigned family)');
              } ?>
            </span>
        </li>
        <?php
        }
        ?>
            <?php if (!empty($formattedMailingAddress)) {
                  ?>
          <li><i class="fa-li fa fa-home"></i><?php echo gettext('Address'); ?>: <span>
            <a href="http://maps.google.com/?q=<?= $plaintextMailingAddress ?>" target="_blank">
              <?= $formattedMailingAddress ?>
            </a>
            </span></li>
          <?php
              }
    if ($dBirthDate) {
        ?>
            <li>
              <i class="fa-li fa fa-calendar"></i><?= gettext('Birth Date') ?>:
              <span><?= $dBirthDate ?></span>
              <?php if (!$person->hideAge()) {
            ?>
              (<span data-birth-date="<?= $person->getBirthDate()->format('Y-m-d') ?>"></span> <?=FormatAgeSuffix($person->getBirthDate(), $per_Flags) ?>)
              <?php
        } ?>
            </li>
          <?php
    }
    if (!SystemConfig::getValue('bHideFriendDate') && $per_FriendDate != '') { /* Friend Date can be hidden - General Settings */ ?>
            <li><i class="fa-li fa fa-tasks"></i><?= gettext('Friend Date') ?>: <span><?= OutputUtils::FormatDate($per_FriendDate, false) ?></span></li>
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
    if ($sEmail != '') {
        ?>
            <li><i class="fa-li fa fa-envelope"></i><?= gettext('Email') ?>: <span><a href="mailto:<?= $sUnformattedEmail ?>"><?= $sEmail ?></a></span></li>
            <?php if ($mailchimp->isActive()) {
            ?>
              <li><i class="fa-li fa fa-send"></i>MailChimp: <span><?= $mailchimp->isEmailInMailChimp($sEmail); ?></span></li>
            <?php
        }
    }
    if ($sWorkPhone) {
        ?>
            <li><i class="fa-li fa fa-phone"></i><?= gettext('Work Phone') ?>: <span><a href="tel:<?= $sWorkPhoneUnformatted ?>"><?= $sWorkPhone ?></a></span></li>
          <?php
    } ?>
          <?php if ($per_WorkEmail != '') {
        ?>
            <li><i class="fa-li fa fa-envelope"></i><?= gettext('Work/Other Email') ?>: <span><a href="mailto:<?= $per_WorkEmail ?>"><?= $per_WorkEmail ?></a></span></li>
            <?php if ($mailchimp->isActive()) {
            ?>
              <li><i class="fa-li fa fa-send"></i>MailChimp: <span><?= $mailchimp->isEmailInMailChimp($per_WorkEmail); ?></span></li>
              <?php
        }
    }

    if ($per_FacebookID > 0) {
        ?>
              <li><i class="fa-li fa fa-facebook-official"></i><?= gettext('Facebook') ?>: <span><a href="https://www.facebook.com/<?= InputUtils::FilterInt($per_FacebookID) ?>"><?= gettext('Facebook') ?></a></span></li>
          <?php
    }

    if (strlen($per_Twitter) > 0) {
        ?>
              <li><i class="fa-li fa fa-twitter"></i><?= gettext('Twitter') ?>: <span><a href="https://www.twitter.com/<?= InputUtils::FilterString($per_Twitter) ?>"><?= gettext('Twitter') ?></a></span></li>
          <?php
    }

    if (strlen($per_LinkedIn) > 0) {
        ?>
              <li><i class="fa-li fa fa-linkedin"></i><?= gettext('LinkedIn') ?>: <span><a href="https://www.linkedin.com/in/<?= InputUtils::FiltersTring($per_LinkedIn) ?>"><?= gettext('LinkedIn') ?></a></span></li>
          <?php
    }

    // Display the right-side custom fields
    while ($Row = mysqli_fetch_array($rsCustomFields)) {
        extract($Row);
        $currentData = trim($aCustomData[$custom_Field]);
        if ($currentData != '') {
            if ($type_ID == 11) {
                $custom_Special = $sPhoneCountry;
            }
            echo '<li><i class="fa-li '.(($type_ID == 11)?'fa fa-phone':'fa fa-tag').'"></i>'.$custom_Name.': <span>';
            $temp_string=nl2br((displayCustomField($type_ID, $currentData, $custom_Special)));
            if ($type_ID == 11) {
                echo "<a href=\"tel:".$temp_string."\">".$temp_string."</a>";
            } else {
                echo $temp_string;
            }
            echo '</span></li>';
        }
    } ?>
        </ul>
      </div>
    </div>
      <div class="alert alert-info alert-dismissable">
          <i class="fa fa-fw fa-tree"></i> <?php echo gettext('indicates items inherited from the associated family record.'); ?>
      </div>
    <?php
     }
    ?>
    
  </div>
  <div class="col-lg-9 col-md-9 col-sm-9">
    <div class="box box-primary box-body">
      <?php
        if (Cart::PersonInCart($iPersonID) && $_SESSION['bShowCart']) {
      ?>
        <a class="btn btn-app RemoveOneFromPeopleCart" id="AddPersonToCart" data-onecartpersonid="<?= $iPersonID ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= gettext("Remove from Cart") ?></span></a>
      <?php 
        } else if ($_SESSION['bShowCart']) {
      ?>
          <a class="btn btn-app AddOneToPeopleCart" id="AddPersonToCart" data-onecartpersonid="<?= $iPersonID ?>"><i class="fa fa-cart-plus"></i><span class="cartActionDescription"><?= gettext("Add to Cart") ?></span></a>
      <?php 
       }
      ?>

      <?php if ($per_ID == $_SESSION['user']->getPersonId() || $per_fam_ID == $_SESSION['iFamID'] || $_SESSION['bSeePrivacyData'] || $_SESSION['bAdmin']) {
        ?>
              <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/SettingsIndividual.php"><i class="fa fa-cog"></i> <?= gettext("Change Settings") ?></a>
              <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/UserPasswordChange.php"><i class="fa fa-key"></i> <?= gettext("Change Password") ?></a>
              <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/PrintView.php?PersonID=<?= $iPersonID ?>"><i class="fa fa-print"></i> <?= gettext("Printable Page") ?></a>
            <?php
       } ?>
      <?php if ($_SESSION['bNotes']) {
        ?>
        <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/WhyCameEditor.php?PersonID=<?= $iPersonID ?>"><i class="fa fa-question-circle"></i> <?= gettext("Edit \"Why Came\" Notes") ?></a>
        <?php
         }
        ?>
      <?php if ($_SESSION['bNotes'] || ($_SESSION['bEditSelf'] && $per_ID == $_SESSION['user']->getPersonId() || $per_fam_ID == $_SESSION['iFamID'])) {
      ?>
        <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/NoteEditor.php?PersonID=<?= $iPersonID ?>"><i class="fa fa-sticky-note"></i> <?= gettext("Add a Document") ?></a>
      
      <?php
    }
    if ($_SESSION['bDeleteRecords']) {
        ?>
        <a class="btn btn-app bg-maroon delete-person" data-person_name="<?= $person->getFullName()?>" data-person_id="<?= $iPersonID ?>"><i class="fa fa-trash-o"></i> <?= gettext("Delete this Record") ?></a>
      <?php
    }
    if ($_SESSION['bManageGroups']) {
        ?>
        <a class="btn btn-app" id="addGroup"><i class="fa fa-users"></i> <?= gettext("Assign New Group") ?></a>
      <?php
    }
    if ($_SESSION['bAdmin']) {
        if (!$person->isUser()) {
            ?>
          <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/UserEditor.php?NewPersonID=<?= $iPersonID ?>"><i class="fa fa-user-secret"></i> <?= gettext('Make User') ?></a>
        <?php
        } else {
            ?>
          <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/UserEditor.php?PersonID=<?= $iPersonID ?>"><i class="fa fa-user-secret"></i> <?= gettext('Edit User') ?></a>
        <?php
        }
    } ?>
      <a class="btn btn-app" role="button" href="<?= SystemURLs::getRootPath() ?>/SelectList.php?mode=person"><i class="fa fa-list"></i> <?= gettext("List Members") ?></span></a>
    </div>
  </div>
  
  <?php 
    if ($_SESSION['bManageGroups'] || ($_SESSION['bEditSelf'] && $per_ID == $_SESSION['user']->getPersonId() || $per_fam_ID == $_SESSION['iFamID'] || $_SESSION['bSeePrivacyData'] || $_SESSION['bAdmin'])) {
  ?>
  <div class="col-lg-9 col-md-9 col-sm-9">
    <div class="nav-tabs-custom">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#timeline" aria-controls="timeline" role="tab" data-toggle="tab"><?= gettext('Timeline') ?></a></li>
        <?php
          if (count($person->getOtherFamilyMembers()) > 0) {
        ?>
        <li role="presentation"><a href="#family" aria-controls="family" role="tab" data-toggle="tab"><?= gettext('Family') ?></a></li>
        <?php
        }
        ?>
        <li role="presentation"><a href="#groups" aria-controls="groups" role="tab" data-toggle="tab"><?= gettext('Assigned Groups') ?></a></li>
        <li role="presentation"><a href="#properties" aria-controls="properties" role="tab" data-toggle="tab"><?= gettext('Assigned Properties') ?></a></li>
        <li role="presentation"><a href="#volunteer" aria-controls="volunteer" role="tab" data-toggle="tab"><?= gettext('Volunteer Opportunities') ?></a></li>
        <?php
         if (count($person->getOtherFamilyMembers()) == 0 && $_SESSION['bFinance']) {
                    ?>
                    <li role="presentation"><a href="#finance" aria-controls="finance" role="tab"
                                               data-toggle="tab"><?= gettext("Automatic Payments") ?></a></li>
                    <li role="presentation"><a href="#pledges" aria-controls="pledges" role="tab"
                                               data-toggle="tab"><?= gettext("Pledges and Payments") ?></a></li>
        <?php
        } 
        ?>
        <li role="presentation"><a href="#notes" aria-controls="notes" role="tab" data-toggle="tab"><?= gettext("Documents") ?></a></li>
      </ul>

      <!-- Tab panes -->
      <div class="tab-content">
        <div role="tab-pane fade" class="tab-pane active" id="timeline">
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
            <?php foreach ($timelineService->getForPerson($iPersonID) as $item) {
        ?>
              <li>
                <!-- timeline icon -->
                <i class="fa <?= $item['style'] ?>"></i>

                <div class="timeline-item">
                  <span class="time">
                  <?php if ($item['slim']) {
                  ?>
                  <?php if ($item['editLink'] != '') {
                ?>
                        <!--<a href="<?= $item['editLink'] ?>">
                          <button type="button" class="btn-xs btn-primary"><i class="fa fa-edit"></i></button>
                        </a>-->
                      <?php
            }
            if ($item['deleteLink'] != '') {
                ?>
                        <!--<a href="<?= $item['deleteLink'] ?>">
                          <button type="button" class="btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                        </a>-->
                      <?php
            } ?>
            
            &nbsp;
            <?php } ?>
                  <i class="fa fa-clock-o"></i> <?= $item['datetime'] ?>
                  </span>
                  
                  <h3 class="timeline-header">
                    <?php if (in_array('headerlink', $item)) {
            ?>
                      <a href="<?= $item['headerlink'] ?>"><?= $item['header'] ?></a>
                    <?php
        } else {
            ?>
                      <?= $item['header'] ?>
                    <?php
        } ?>
                  </h3>

                  <div class="timeline-body">                     
                     <?php 
                       if ($item['type'] != 'file') { 
                     ?>
                      <pre style="line-height: 1.2;"><?= ((!empty($item['info']))?$item['info']." : ":"").$item['text'] ?></pre>
                     <?php 
                       } else {
                      ?>
                       <pre style="line-height: 1.2;"><?= ((!empty($item['info']))?$item['info']." : ":"")."<a href=\"".SystemURLs::getRootPath()."/private/userdir/".$item['text']."\"><i class=\"fa fa-file-o\"></i> \"".$item['text']."\"</a>" ?></pre>
                      <?php 
                        } 
                      ?>
                  </div>
                  <?php if (!$item['slim']) {
                  ?>
                  <?php if (($_SESSION['bNotes']) && ($item['editLink'] != '' || $item['deleteLink'] != '')) {
            ?>
                    <div class="timeline-footer">
                      <?php if ($item['editLink'] != '') {
                ?>
                        <!--<a href="<?= $item['editLink'] ?>">
                          <button type="button" class="btn btn-primary"><i class="fa fa-edit"></i></button>
                        </a>-->
                      <?php
            }
            if ($item['deleteLink'] != '') {
                ?>
                        <!--<a href="<?= $item['deleteLink'] ?>">
                          <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                        </a>-->
                      <?php
            } ?>
                    </div>
                  <?php
        } ?>
        
        <?php 
        } ?>
                </div>
              </li>
            <?php
    } ?>
            <!-- END timeline item -->
          </ul>
        </div>
        <div role="tab-pane fade" class="tab-pane" id="family">

          <?php if ($person->getFamId() != '') {
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
            <?php foreach ($person->getOtherFamilyMembers() as $familyMember) {
            $tmpPersonId = $familyMember->getId(); ?>
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
                  <?php $tmpEmail = $familyMember->getEmail();
            if ($tmpEmail != '') {
                ?>
                    <a href="mailto:<?= $tmpEmail ?>"><?= $tmpEmail ?></a>
                  <?php
            } ?>
                </td>
                <td style="width: 20%;">
                  <?php
                    if ($_SESSION['bShowCart']) {
                  ?>
                  <a class="AddToPeopleCart" data-cartpersonid="<?= $tmpPersonId ?>">
                    <span class="fa-stack">
                      <i class="fa fa-square fa-stack-2x"></i>
                      <i class="fa fa-cart-plus fa-stack-1x fa-inverse"></i>
                    </span>
                  </a>
                  <?php 
                    }
                  ?>
                  <?php if ($bOkToEdit) {
                ?>
                    <a href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?PersonID=<?= $tmpPersonId ?>">
                      <span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x"></i>
                        <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                      </span>
                    </a>
                    <a href="<?= SystemURLs::getRootPath() ?>/SelectDelete.php?mode=person&PersonID=<?= $tmpPersonId ?>">
                      <span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x"></i>
                        <i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
                      </span>
                    </a>
                  <?php
            } ?>
                </td>
              </tr>
            <?php
        } ?>
            </tbody>
          </table>
          <?php
    } ?>
        </div>
        <div role="tab-pane fade" class="tab-pane" id="groups">
          <div class="main-box clearfix">
            <div class="main-box-body clearfix">
              <?php
              //Was anything returned?
              if (mysqli_num_rows($rsAssignedGroups) == 0) {
                  ?>
                <br>
                <div class="alert alert-warning">
                  <i class="fa fa-question-circle fa-fw fa-lg"></i> <span><?= gettext('No group assignments.') ?></span>
                </div>
              <?php
              } else {
                  echo '<div class="row">';
                  // Loop through the rows
                  while ($aRow = mysqli_fetch_array($rsAssignedGroups)) {
                      extract($aRow); ?>
                  <div class="col-md-4">
                    <p><br/></p>
                    <!-- Info box -->
                    <div class="box box-info">
                      <div class="box-header">
                        <h3 class="box-title" style="font-size:small"><a href="<?= SystemURLs::getRootPath() ?>/GroupView.php?GroupID=<?= $grp_ID ?>"><?= $grp_Name ?></a></h3>

                        <div class="box-tools pull-right">
                          <div class="label bg-aqua"><?= gettext($roleName) ?></div>
                        </div>
                      </div>
                      <?php
                      // If this group has associated special properties, display those with values and prop_PersonDisplay flag set.
                      if ($grp_hasSpecialProps) {
                          // Get the special properties for this group
                          $sSQL = 'SELECT groupprop_master.* FROM groupprop_master WHERE grp_ID = '.$grp_ID." AND prop_PersonDisplay = 'true' ORDER BY prop_ID";
                          $rsPropList = RunQuery($sSQL);

                          $sSQL = 'SELECT * FROM groupprop_'.$grp_ID.' WHERE per_ID = '.$iPersonID;
                          $rsPersonProps = RunQuery($sSQL);
                          $aPersonProps = mysqli_fetch_array($rsPersonProps, MYSQLI_BOTH);

                          echo '<div class="box-body">';

                          while ($aProps = mysqli_fetch_array($rsPropList)) {
                              extract($aProps);
                              $currentData = trim($aPersonProps[$prop_Field]);
                              if (strlen($currentData) > 0) {
                                  $sRowClass = AlternateRowStyle($sRowClass);
                                  if ($type_ID == 11) {
                                      $prop_Special = $sPhoneCountry;
                                  }
                                  echo '<strong>'.$prop_Name.'</strong>: '.displayCustomField($type_ID, $currentData, $prop_Special).'<br/>';
                              }
                          }

                          echo '</div><!-- /.box-body -->';
                      } ?>
                      <div class="box-footer" style="width:275px">
                        <code>
                          <?php if ($_SESSION['bManageGroups']) {
                          ?>
                            <a href="<?= SystemURLs::getRootPath() ?>/GroupView.php?GroupID=<?= $grp_ID ?>" class="btn btn-default" role="button"><i class="fa fa-list"></i></a>
                            <div class="btn-group">
                              <button type="button" class="btn btn-default"><?= gettext('Action') ?></button>
                              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                              </button>
                              <ul class="dropdown-menu" role="menu">
                                <li><a  class="changeRole" data-groupid="<?= $grp_ID ?>"><?= gettext('Change Role') ?></a></li>
                                <?php if ($grp_hasSpecialProps) {
                              ?>
                                  <li><a href="<?= SystemURLs::getRootPath() ?>/GroupPropsEditor.php?GroupID=<?= $grp_ID ?>&PersonID=<?= $iPersonID ?>"><?= gettext('Update Properties') ?></a></li>
                                <?php
                          } ?>
                              </ul>
                                                            

                            </div>
                            <div class="btn-group">
                            <button data-groupid="<?= $grp_ID ?>" data-groupname="<?= $grp_Name ?>" type="button" class="btn btn-danger groupRemove" data-toggle="dropdown"><i class="fa fa-trash-o"></i></button>
                            </div>
                            <!--<a data-groupid="<?= $grp_ID ?>" data-groupname="<?= $grp_Name ?>" class="btn btn-danger groupRemove" role="button"><i class="fa fa-trash-o"></i></a>-->
                          <?php
                      } ?>
                        </code>
                      </div>
                      <!-- /.box-footer-->
                    </div>
                    <!-- /.box -->
                  </div>
                  <?php
                  // NOTE: this method is crude.  Need to replace this with use of an array.
                  $sAssignedGroups .= $grp_ID.',';
                  }
                  echo '</div>';
              }
           ?>
            </div>
          </div>
        </div>
        <div role="tab-pane fade" class="tab-pane" id="properties">
          <div class="main-box clearfix">
            <div class="main-box-body clearfix">
            <?php
            $sAssignedProperties = ','; ?>
            <?php if (mysqli_num_rows($rsAssignedProperties) == 0): ?>
                <br>
                <div class="alert alert-warning">
                  <i class="fa fa-question-circle fa-fw fa-lg"></i> <span><?= gettext('No property assignments.') ?></span>
                </div>
            <?php else: ?>
                <table class="table table-condensed dt-responsive" id="assigned-properties-table" width="100%">
                    <thead>
                        <tr class="TableHeader">
                            <th><?= gettext('Type') ?></th>
                            <th><?= gettext('Name') ?></th>
                            <th><?= gettext('Value') ?></th>
                            <?php if ($bOkToEdit): ?>
                            <th><?= gettext('Edit Value') ?></th>
                                <th><?= gettext('Remove') ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Loop through the rows
                        while ($aRow = mysqli_fetch_array($rsAssignedProperties)) {
                            $pro_Prompt = '';
                            $r2p_Value = '';
                            extract($aRow);
                            ?>

                            <tr>
                            <td><?= $prt_Name ?></td>
                            <td><?= $pro_Name ?></td>
                            <td><?= $r2p_Value ?></td>
                          <?php
                            if ($bOkToEdit) {
                                if (strlen($pro_Prompt) > 0) {
                                ?>
                                   <td valign="top"><a data-person_id="<?= $iPersonID?>" data-property_id="<?= $pro_ID ?>" data-property_Name="<?= $r2p_Value ?>" class="edit-property-btn btn btn-success"><?= gettext('Edit Value') ?></a></td>
                                <?php
                                } else {
                                ?>
                                   <td></td>
                                <?php
                                }
                                ?>
                                <td valign="top"><a data-person_id="<?= $iPersonID ?>" data-property_id="<?= $pro_ID ?>" class="remove-property-btn btn btn-danger"><?= gettext('Remove') ?></a></td>
                                <?php
                            }
                            ?>
                            </tr>
                            
                            <?php
                            $sAssignedProperties .= $pro_ID.',';                        
                        } ?>
                    </tbody>
                </table>
            <?php endif; ?>

              <?php if ($bOkToEdit && count($ormProperties) != 0): ?>
                <div class="alert alert-info">
                  <div>
                    <h4><strong><?= gettext('Assign a New Property') ?>:</strong></h4>

                    <form method="post" action="<?= SystemURLs::getRootPath(). '/api/properties/persons/assign' ?>" id="assign-property-form">
                        <input type="hidden" name="PersonId" value="<?= $person->getId() ?>" >
                        <div class="row">
                            <div class="form-group col-xs-12 col-md-7">
                                <select name="PropertyId" id="input-person-properties" class="form-control select2"
                                    style="width:100%" data-placeholder="<?= gettext("Select") ?> ...">
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
                                <input id="assign-property-btn" type="submit" class="btn btn-primary" value="<?= gettext('Assign') ?>" name="Submit">
                            </div>
                        </div>
                    </form>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div role="tab-pane fade" class="tab-pane" id="volunteer">
          <div class="main-box clearfix">
            <div class="main-box-body clearfix">
              <?php

              //Initialize row shading
              $sRowClass = 'RowColorA';

    $sAssignedVolunteerOpps = ',';

    //Was anything returned?
    if (mysqli_num_rows($rsAssignedVolunteerOpps) == 0) {
        ?>
                <br>
                <div class="alert alert-warning">
                  <i class="fa fa-question-circle fa-fw fa-lg"></i> <span><?= gettext('No volunteer opportunity assignments.') ?></span>
                </div>
              <?php
    } else {
        echo '<table class="table table-condensed dt-responsive" id="assigned-volunteer-opps-table" width="100%">';
        echo '<thead>';
        echo '<tr class="TableHeader">';
        echo '<th>'.gettext('Name').'</th>';
        echo '<th>'.gettext('Description').'</th>';
        if ($_SESSION['bEditRecords']) {
            echo '<th>'.gettext('Remove').'</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Loop through the rows
        while ($aRow = mysqli_fetch_array($rsAssignedVolunteerOpps)) {
            extract($aRow);

            // Alternate the row style
            $sRowClass = AlternateRowStyle($sRowClass);

            echo '<tr class="'.$sRowClass.'">';
            echo '<td>'.$vol_Name.'</a></td>';
            echo '<td>'.$vol_Description.'</a></td>';

            if ($_SESSION['bEditRecords']) {
                echo '<td><a class="SmallText btn btn-danger" href="'.SystemURLs::getRootPath().'/PersonView.php?PersonID='.$per_ID.'&RemoveVO='.$vol_ID.'">'.gettext('Remove').'</a></td>';
            }

            echo '</tr>';

            // NOTE: this method is crude.  Need to replace this with use of an array.
            $sAssignedVolunteerOpps .= $vol_ID.',';
        }
        echo '</tbody>';
        echo '</table>';
    } ?>

                <?php if ($_SESSION['bEditRecords'] && $rsVolunteerOpps->num_rows): ?>
                <div class="alert alert-info">
                    <div>
                        <h4><strong><?= gettext('Assign a New Volunteer Opportunity') ?>:</strong></h4>

                        <form method="post" action="PersonView.php?PersonID=<?= $iPersonID ?>">
                        <div class="row">
                            <div class="form-group col-xs-12 col-md-7">
                                <select id="input-volunteer-opportunities" name="VolunteerOpportunityIDs[]" multiple
                                    class="form-control select2" style="width:100%" data-placeholder="<?= gettext("Select") ?>...">
                                    <?php
                                    while ($aRow = mysqli_fetch_array($rsVolunteerOpps)) {
                                        extract($aRow);
                                        //If the property doesn't already exist for this Person, write the <OPTION> tag
                                        if (strlen(strstr($sAssignedVolunteerOpps, ','.$vol_ID.',')) == 0) {
                                            echo '<option value="'.$vol_ID.'">'.$vol_Name.'</option>';
                                        }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-xs-12 col-md-7">
                                <input type="submit" value="<?= gettext('Assign') ?>" name="VolunteerOpportunityAssign" class="btn btn-primary">
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
          </div>
        </div>
        <?php if ($_SESSION['bFinance']) {
        ?>
                <div role="tab-pane fade" class="tab-pane" id="finance">
                    <div class="main-box clearfix">
                        <div class="main-box-body clearfix">
                            <?php if ($ormAutoPayments->count() > 0) {
            ?>
            
                                <table class="table table-striped table-bordered" id="automaticPaymentsTable" cellpadding="5" cellspacing="0"  width="100%"></table>

                                <?php
        } ?>
                            <p align="center">
                                <a class="btn btn-primary"
                                   href="AutoPaymentEditor.php?AutID=-1&FamilyID=<?= $fam_ID ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>"><?= gettext("Add a new automatic payment") ?></a>
                            </p>
                        </div>
                    </div>
                </div>
                <div role="tab-pane fade" class="tab-pane" id="pledges">
                    <div class="main-box clearfix">
                        <div class="main-box-body clearfix">
                                <input type="checkbox" name="ShowPledges" id="ShowPledges"
                                       value="1" <?php if ($_SESSION['sshowPledges']) {
                                      echo " checked";
                                  } ?>><?= gettext("Show Pledges") ?>
                                                          <input type="checkbox" name="ShowPayments" id="ShowPayments"
                                                                 value="1" <?php if ($_SESSION['sshowPayments']) {
                                      echo " checked";
                                  } ?>><?= gettext("Show Payments") ?>
                                  <label for="ShowSinceDate"><?= gettext("From") ?>:</label>
                                  <input type="text" Name="Min" id="Min"
                                       value="<?= date("Y") ?>" maxlength="10" id="ShowSinceDate" size="15">
                                       
                                <label for="ShowSinceDate"><?= gettext("To") ?>:</label>
                                
                                <input type="text" Name="Max" id="Max"
                                       value="<?= date("Y") ?>" maxlength="10" id="ShowSinceDate" size="15">
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
                                   href="PledgeEditor.php?FamilyID=<?= $fam_ID ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>&amp;PledgeOrPayment=Pledge"><?= gettext("Add a new pledge") ?></a>
                                <a class="btn btn-default"
                                   href="PledgeEditor.php?FamilyID=<?= $fam_ID ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>&amp;PledgeOrPayment=Payment"><?= gettext("Add a new payment") ?></a>
                            </p>

                            <?php
    } ?>

                            <?php if ($_SESSION['bCanvasser']) {
        ?>

                            <p align="center">
                                <a class="btn btn-default"
                                   href="CanvassEditor.php?FamilyID=<?= $fam_ID ?>&amp;FYID=<?= $_SESSION['idefaultFY'] ?>&amp;linkBack=PersonView.php?PersonID=<?= $iPersonID ?>"><?= MakeFYString($_SESSION['idefaultFY']) . gettext(" Canvass Entry") ?></a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php
    } ?>
        <div role="tab-pane fade" class="tab-pane" id="notes">
          <div class="row filter-note-type">
              <div class="col-md-1" style="line-height:27px">
              <table width=400px>
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
                      <select name="PropertyId" id="filter-timeline" class="form-control input-sm" style="width:170px" data-placeholder="<?= gettext("Select") ?> ...">
                          <option value="all"><?= gettext("All type") ?></option>
                          <option value="note"><?= MiscUtils::noteType("note") ?></option>
                          <option value="video"><?= MiscUtils::noteType("video") ?></option>
                          <option value="file"><?= MiscUtils::noteType("file") ?></option>
                      </select>
                  </td>
                </tr>
              </table>
              </div>
          </div>
          <ul class="timeline time-line-note">
            <!-- note time label -->
            <li class="time-label">
            </li>
            <!-- /.note-label -->

            <!-- note item -->
            <?php 
              foreach ($timelineService->getNotesForPerson($iPersonID) as $item) {
                if ( $item['type'] == 'file' && ( $item['info'] == gettext("Create file") || $item['info'] == gettext("Dav create file")) 
                 || $item['type'] == 'file' && ( $item['info'] == gettext("Update file") || $item['info'] == gettext("Dav update file")) 
                 || $item['type'] != 'file') {      
            ?>
              <li>
                <!-- timeline icon -->
                <i class="fa <?= $item['style'] ?> icon-<?= $item['type'] ?>" ></i>
 
                <div class="timeline-item type-<?= $item['type'] ?>">
                  <span class="time">
                     <i class="fa fa-clock-o"></i> <?= $item['datetime'] ?>
                      &nbsp;
                     <?php 
                     
                     if ($item['slim'] && !isset($item['currentUserName']) ) {
                       if ($item['editLink'] != '' || (isset($item['sharePersonID']) && $item['shareRights'] == 2) ) {
                                                ?>
                        <a href="<?= $item['editLink'] ?>">
                          <button type="button" class="btn-xs btn-primary"><i class="fa fa-edit"></i></button>
                        </a>
                      <?php
                        }
                        
                        if ($item['deleteLink'] != '' && !isset($item['sharePersonID']) && !isset($item['currentUserName']) ) {
                      ?>
                        <a href="<?= $item['deleteLink'] ?>">
                          <button type="button" class="btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                        </a>
                      <?php
                        }
                        if (!isset($item['sharePersonID']) && !isset($item['currentUserName']) ) {
                      ?>
                        <button type="button" data-id="<?= $item['id'] ?>" data-shared="<?= $item['isShared'] ?>" class="btn-xs btn-<?= $item['isShared']?"success":"default" 
                        ?> shareNote"><i class="fa fa-share-square-o"></i></button>
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
                      if ($item['type'] != 'file') { 
                     ?>
                      <?= ((!empty($item['info']))?$item['info']." : ":"").$item['text'] ?>
                     <?php 
                       } else {                        
                      ?>
                       <?= ((!empty($item['info']))?$item['info']." : ":"").MiscUtils::embedFiles(SystemURLs::getRootPath()."/private/userdir/".$item['text']) ?>
                      <?php 
                        } 
                      ?>

                  </div>

                  <?php if (($_SESSION['bNotes']) && ($item['editLink'] != '' || $item['deleteLink'] != '')) {
                                            ?>
                    <div class="timeline-footer">
                    <?php if (!$item['slim']) {
                    ?>
                      <?php if ($item['editLink'] != '') {
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
                    } ?>
                  <?php
                  } ?>
                </div>
              </li>
            <?php
                }
              } ?>
            <!-- END timeline item -->
          </ul>
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
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.currentPersonID = <?= $iPersonID ?>;
  window.CRM.currentFamily = <?= $iFamilyID ?>;


  $("#deletePhoto").click (function () {
    $.ajax({
    type: "POST",
    url: window.CRM.root + "/api/persons/<?= $iPersonID ?>/photo",
    encode: true,
    dataType: 'json',
    data: {
      "_METHOD": "DELETE"
    }
    }).done(function(data) {
      location.reload();
    });
  });

  window.CRM.photoUploader =  $("#photoUploader").PhotoUploader({
    url: window.CRM.root + "/api/persons/<?= $iPersonID ?>/photo",
    maxPhotoSize: window.CRM.maxUploadSize,
    photoHeight: <?= SystemConfig::getValue("iPhotoHeight") ?>,
    photoWidth: <?= SystemConfig::getValue("iPhotoWidth") ?>,
    done: function(e) {
      window.location.reload();
    }
  });

  $("#uploadImageButton").click(function(){
    window.CRM.photoUploader.show();
  });


  $(document).ready(function() {
      $("#input-volunteer-opportunities").select2({ 
        language: window.CRM.shortLocale
      });
      $("#input-person-properties").select2({ 
        language: window.CRM.shortLocale
      });

      $("#assigned-volunteer-opps-table").DataTable(window.CRM.plugin.dataTable);
      $("#assigned-properties-table").DataTable(window.CRM.plugin.dataTable);


      contentExists(window.CRM.root + "/api/persons/" + window.CRM.currentPersonID + "/photo", function(success) {
          if (success) {
              $("#view-larger-image-btn").removeClass('hide');

              $("#view-larger-image-btn").click(function() {
                  bootbox.alert({
                      title: "<?= gettext('Photo') ?>",
                      message: '<img class="img-rounded img-responsive center-block" src="<?= SystemURLs::getRootPath() ?>/api/persons/' + window.CRM.currentPersonID + '/photo" />',
                      backdrop: true
                  });
              });
          }
      });

  });


</script>

<?php require 'Include/Footer.php' ?>
