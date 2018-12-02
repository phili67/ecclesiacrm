<?php
/*******************************************************************************
 *
 *  filename    : FamilyView.php
 *  last change : 2013-02-02
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker, 2003 Chris Gebhardt, 2004-2005 Michael Wilt
 *                Copyright 2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require "Include/Config.php";
require "Include/Functions.php";

use Propel\Runtime\Propel;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Service\TimelineService;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyCustomQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\Map\PersonTableMap;
use Propel\Runtime\ActiveQuery\Criteria;



//Set the page title
$sPageTitle = gettext("Family View");
require "Include/Header.php";

//Get the FamilyID out of the querystring
if (!empty($_GET['FamilyID'])) {
    $iFamilyID = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');
}

// we get the TimelineService
$maxMainTimeLineItems = 20; // max number

$timelineService           = new TimelineService();
$timelineServiceItems      = $timelineService->getForFamily($iFamilyID);
$timelineNotesServiceItems = $timelineService->getNotesForFamily($iFamilyID);

$mailchimp = new MailChimpService();
$curYear = (new DateTime)->format("Y");

//Deactivate/Activate Family
if ($_SESSION['user']->isDeleteRecordsEnabled() && !empty($_POST['FID']) && !empty($_POST['Action'])) {
    $family = FamilyQuery::create()->findOneById($_POST['FID']);
    if ($_POST['Action'] == "Deactivate") {
        $family->deactivate();
    } elseif ($_POST['Action'] == "Activate") {
        $family->activate();
    }
    $family->save();
    Redirect("FamilyView.php?FamilyID=" . $_POST['FID']);
    exit;
}

if ($_SESSION['user']->isFinanceEnabled()) {
    $_SESSION['sshowPledges'] = 1;
    $_SESSION['sshowPayments'] = 1;
}

$persons = PersonQuery::Create()->findByFamId($iFamilyID);

if ( !is_null ($persons) && $persons->count() == 1 ) {
    $person = PersonQuery::Create()->findOneByFamId($iFamilyID);
    
    Redirect("PersonView.php?PersonID=" . $person->getId());
}

$ormNextFamilies = PersonQuery::Create ()
                    ->useFamilyQuery()
                      ->orderByName()
                    ->endUse()
                    ->withColumn('COUNT(*)', 'count')
                    ->groupByFamId()
                    ->find();

/*$ormNextFamilies = PersonQuery::Create ()
                      ->useFamilyQuery()
                        ->orderByName()
                      ->endUse()
                      ->groupByFamId()
                      ->withColumn('COUNT(*)', 'count')
                      ->find();*/
//echo $ormNextFamilies;

$last_id = 0;
$next_id = 0;
$capture_next = 0;

foreach ($ormNextFamilies as $nextFamily) {
    $fid = $nextFamily->getFamId();
    $numberMembers = $nextFamily->getCount();
    if ($capture_next == 1 && $numberMembers > 1) {
        $next_id = $fid;
        break;
    }
    if ($fid == $iFamilyID) {
        $previous_id = $last_id;
        $capture_next = 1;
    }
    if ($numberMembers > 1) {
      $last_id = $fid;
    }
}

$iCurrentUserFamID = $_SESSION['user']->getPerson()->getFamId();

// Get the lists of custom person fields
$ormFamCustomFields = FamilyCustomMasterQuery::Create()
                     ->orderByCustomOrder()
                     ->find();

// Get the custom field data for this person.
$connection = Propel::getConnection();
$sSQL = "SELECT * FROM `family_custom` where fam_ID=" . $iFamilyID;

$statement = $connection->prepare($sSQL);
$statement->execute();
$aFamCustomData = $statement->fetch( PDO::FETCH_ASSOC );//fetchAll();//


$family = FamilyQuery::create()->findPk($iFamilyID);

if (empty($family)) {
    Redirect('members/404.php');
    exit;
}


if ($family->getDateDeactivated() != null) {
    $time = new DateTime('now');
    $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');
    
    if ( $new_time > $family->getDateDeactivated() ) {
      if ( !$_SESSION['user']->isGdrpDpoEnabled() ) {
        Redirect('members/404.php?type=Person');
        exit;
      }
    } else if (!$_SESSION['user']->isEditRecordsEnabled()){
      Redirect('members/404.php?type=Person');
      exit;
    }
}

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


//Get all the properties
$ormProperties = PropertyQuery::Create()
                  ->filterByProClass('f')
                  ->orderByProName()
                  ->find();

//Get classifications
$ormClassifications = ListOptionQuery::Create()
              ->orderByOptionSequence()
              ->findById(1);


//Set the spacer cell width
$iTableSpacerWidth = 10;

// Format the phone numbers
$sHomePhone = ExpandPhoneNumber($family->getHomePhone(), $family->getCountry(), $dummy);
$sWorkPhone = ExpandPhoneNumber($family->getWorkPhone(), $family->getCountry(), $dummy);
$sCellPhone = ExpandPhoneNumber($family->getCellPhone(), $family->getCountry(), $dummy);

$sFamilyEmails = array();

$bOkToEdit = ($_SESSION['user']->isEditRecordsEnabled() || ($_SESSION['user']->isEditSelfEnabled() && ($iFamilyID == $_SESSION['user']->getPerson()->getFamId())));

?>

<?php if (!empty($family->getDateDeactivated())) {
    ?>
    <div class="alert alert-warning">
        <strong><?= gettext(" This Family is Deactivated") ?> </strong>
    </div>
    <?php
} ?>
<div class="row">
  <div class="col-lg-3 col-md-3 col-sm-3">
    <div class="box box-success">
      <div class="box-body">
        <div class="image-container">
          <img src="<?= SystemURLs::getRootPath() ?>/api/families/<?= $family->getId() ?>/photo" class="initials-image img-rounded img-responsive profile-user-img profile-family-img"/>
        <?php 
          if ($bOkToEdit) { 
        ?>
          <div class="after">
            <div class="buttons">
              <a class="hide" id="view-larger-image-btn" href="#"
                  title="<?= gettext("View Photo") ?>">
                <i class="fa fa-search-plus"></i>
              </a>&nbsp;
              <a href="#" data-toggle="modal" data-target="#upload-image"
                  title="<?= gettext("Upload Photo") ?>">
                <i class="fa fa-camera"></i>
              </a>&nbsp;
              <a href="#" data-toggle="modal" data-target="#confirm-delete-image"
                 title="<?= gettext("Delete Photo") ?>">
                  <i class="fa fa-trash-o"></i>
              </a>
            </div>
          </div>
        <?php 
          } 
        ?>
        </div>
        <h3 class="profile-username text-center"><?= gettext('Family') . ': ' . $family->getName() ?></h3>
      <?php 
        if ($bOkToEdit) {
      ?>
        <a href="<?= SystemURLs::getRootPath() ?>/FamilyEditor.php?FamilyID=<?= $family->getId() ?>"
           class="btn btn-primary btn-block"><b><?= gettext("Edit") ?></b></a>
      <?php
        } 
      ?>
        <hr/>
      <?php 
         $can_see_privatedata = ($iCurrentUserFamID == $iFamilyID || $_SESSION['user']->isSeePrivacyDataEnabled())?true:false;
      ?>
        <ul class="fa-ul">
      <?php
        if ($can_see_privatedata) {
      ?>
          <li><i class="fa-li fa fa-home"></i><?= gettext("Address") ?>:
          <span>
             <a href="http://maps.google.com/?q=<?= $family->getAddress() ?>"
                  target="_blank"><?= $family->getAddress() ?></a>
          </span><br>

        <?php 
          if ($family->getLatitude() && $family->getLongitude()) {
            if (SystemConfig::getValue("iChurchLatitude") && SystemConfig::getValue("iChurchLongitude")) {
              $sDistance = GeoUtils::LatLonDistance(SystemConfig::getValue("iChurchLatitude"), SystemConfig::getValue("iChurchLongitude"), $family->getLatitude(), $family->getLongitude());
              $sDirection = GeoUtils::LatLonBearing(SystemConfig::getValue("iChurchLatitude"), SystemConfig::getValue("iChurchLongitude"), $family->getLatitude(), $family->getLongitude());
              echo OutputUtils::number_localized($sDistance) . " " . gettext(strtolower(SystemConfig::getValue("sDistanceUnit"))) . " " . gettext($sDirection) . " " . gettext(" of church<br>");
            }
          } else {
            $bHideLatLon = true;
          } 
        ?>
      <?php 
        if (!$bHideLatLon && !SystemConfig::getBooleanValue('bHideLatLon')) { /* Lat/Lon can be hidden - General Settings */ ?>
          <li><i class="fa-li fa fa-compass"></i><?= gettext("Latitude/Longitude") ?>
              <span><?= $family->getLatitude() . " / " . $family->getLongitude() ?></span>
          </li>
      <?php
        }

        if (!SystemConfig::getValue("bHideFamilyNewsletter")) { /* Newsletter can be hidden - General Settings */ 
      ?>
          <li><i class="fa-li fa fa-hacker-news"></i><?= gettext("Send Newsletter") ?>:
            <span id="NewsLetterSend"></span>
          </li>
      <?php
        }
        if (!SystemConfig::getValue("bHideWeddingDate") && $family->getWeddingdate() != "") { /* Wedding Date can be hidden - General Settings */ 
      ?>
          <li>
            <i class="fa-li fa fa-magic"></i><?= gettext("Wedding Date") ?>:
            <span><?= OutputUtils::FormatDate($family->getWeddingdate()->format('Y-m-d'), false) ?></span>
          </li>
      <?php
        }
        if (SystemConfig::getValue("bUseDonationEnvelopes")) {
      ?>
          <li><i class="fa-li fa fa-phone"></i><?= gettext("Envelope Number") ?>
              <span><?= $family->getEnvelope() ?></span>
          </li>
      <?php
        }
        if ($sHomePhone != "") {
      ?>
          <li><i class="fa-li fa fa-phone"></i><?= gettext("Home Phone") ?>: <span><a
                          href="tel:<?= $sHomePhone ?>"><?= $sHomePhone ?></a></span></li>
      <?php
        }
        if ($sWorkPhone != "") {
      ?>
        <li><i class="fa-li fa fa-building"></i><?= gettext("Work Phone") ?>: <span>
          <a href="tel:<?= $sWorkPhone ?>"><?= $sWorkPhone ?></a></span>
        </li>
      <?php
        }
        if ($sCellPhone != "") {
      ?>
          <li><i class="fa-li fa fa-mobile"></i><?= gettext("Mobile Phone") ?>: <span><a
                          href="tel:<?= $sCellPhone ?>"><?= $sCellPhone ?></a></span></li>
          <li><i class="fa-li fa fa-mobile-phone"></i><?= gettext('Text Message') ?>: <span><a 
                          href="sms:<?= $sCellPhone ?>&body=<?= gettext("EcclesiaCRM text message") ?>"><?= $sCellPhone ?></a></span></li>

      <?php
        }
        if ($family->getEmail() != "") {
      ?>
          <li><i class="fa-li fa fa-envelope"></i><?= gettext("Email") ?>:
            <a href="mailto:<?= $family->getEmail() ?>"><span><?= $family->getEmail() ?></span></a>
          </li>
        <?php 
          if ($mailchimp->isActive()) {
        ?>
          <li><i class="fa-li fa fa-send"></i><?= gettext("MailChimp") ?>:
            <span id="mailChimpUserNormal"></span>
          </li>
        <?php
          }
        }

  } // end of can_see_privatedata

  // Display the left-side custom fields
  foreach ($ormFamCustomFields as $rowCustomField) {
    if (OutputUtils::securityFilter($rowCustomField->getCustomFieldSec())) {
      $currentData = trim($aFamCustomData[$rowCustomField->getCustomField()]);
      
      if ( empty($currentData) ) continue;
      
      if ($rowCustomField->getTypeId() == 11) {
        $fam_custom_Special = $sPhoneCountry;
      } else {
        $fam_custom_Special = $rowCustomField->getCustomSpecial();
      }
    ?>
          <li><i class="fa-li fa fa-tag"></i>
            <?= $rowCustomField->getCustomName() ?>: 
            <span><?= OutputUtils::displayCustomField($rowCustomField->getTypeId(), $currentData, $fam_custom_Special)  ?>
            </span>
          </li>
    <?php
      }      
    }
  ?>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-lg-9 col-md-9 col-sm-9">
    <div class="box box-success box-body">
      <?php
        if (Cart::FamilyInCart($iFamilyID) && $_SESSION['user']->isShowCartEnabled()) {
      ?>
        <a class="btn btn-app RemoveFromFamilyCart" id="AddToFamilyCart" data-cartfamilyid="<?= $iFamilyID ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= gettext("Remove from Cart") ?></span></a>
      <?php 
        } else if ($_SESSION['user']->isShowCartEnabled()) {
      ?>
        <a class="btn btn-app AddToFamilyCart" id="AddToFamilyCart" data-cartfamilyid="<?= $iFamilyID ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= gettext("Add to Cart") ?></span></a>
      <?php 
       }
      ?>

      <?php
        if ($_SESSION['user']->isAdmin()) {
      ?>
      <a class="btn btn-app" href="#" data-toggle="modal" data-target="#confirm-verify"><i class="fa fa-check-square"></i> <?= gettext("Verify Info") ?></a>
      <?php
        }
      ?>
      <?php
        if ($_SESSION['user']->isAddRecordsEnabled() || $iCurrentUserFamID == $iFamilyID) {
      ?>
         <a class="btn btn-app bg-olive" href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?FamilyID=<?= $iFamilyID ?>"><i class="fa fa-plus-square"></i> <?= gettext('Add New Member') ?></a>
      <?php
        }
      ?>
      
      <?php 
        if (($previous_id > 0)) {
      ?>
          <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/FamilyView.php?FamilyID=<?= $previous_id ?>"><i class="fa fa-hand-o-left"></i><?= gettext('Previous Family') ?></a>
      <?php
        } 
      ?>
      
      <a class="btn btn-app" role="button" href="<?= SystemURLs::getRootPath() ?>/FamilyList.php"><i class="fa fa-list-ul"></i><?= gettext('Family List') ?></a>
      <?php 
         if (($next_id > 0)) {
      ?>
          <a class="btn btn-app" role="button" href="<?= SystemURLs::getRootPath() ?>/FamilyView.php?FamilyID=<?= $next_id ?>"><i class="fa fa-hand-o-right"></i><?= gettext('Next Family') ?> </a>
      <?php
        } 
      ?>
      <?php       
       if ( $_SESSION['bEmailMailto'] && $family->containsMember($_SESSION['user']->getPersonId()) ) {
          $emails = "";
          foreach ($family->getActivatedPeople() as $person) {
            $emails .= $person->getEmail().$sMailtoDelimiter;
          }
          
           $emails = mb_substr($emails, 0, -1)
      ?>
          <a class="btn btn-app" href="mailto:<?= urlencode($emails) ?>"><i class="fa fa-send-o"></i><?= gettext('Email') ?></a>
          <a class="btn btn-app" href="mailto:?bcc=<?= urlencode($emails) ?>"><i class="fa fa-send"></i><?= gettext('Email (BCC)') ?></a>
      <?php
       }
      ?>
      <?php 
         if ($_SESSION['user']->isDeleteRecordsEnabled()) {
      ?>
          <a class="btn btn-app bg-maroon" href="<?= SystemURLs::getRootPath() ?>/SelectDelete.php?FamilyID=<?= $iFamilyID ?>"><i class="fa fa-trash-o"></i><?= gettext('Delete this Family') ?></a>
      <?php
        } 
      ?>
      <?php                 
       
      if ($_SESSION['user']->isNotesEnabled() || $iCurrentUserFamID == $iFamilyID) {
          ?>
          <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/NoteEditor.php?FamilyID=<?= $iFamilyID ?>"><i class="fa fa-sticky-note"></i><?= gettext("Add a Note") ?></a>
          <?php
      } ?>
              

      <?php if ($bOkToEdit && $_SESSION['user']->isAdmin()) {
          ?>
          <button class="btn btn-app bg-orange" id="activateDeactivate">
              <i class="fa <?= (empty($family->getDateDeactivated()) ? 'fa-times-circle-o' : 'fa-check-circle-o') ?> "></i><?php echo((empty($family->getDateDeactivated()) ? gettext('Deactivate') : gettext('Activate')) . gettext(' this Family')); ?>
          </button>
          <?php
      } ?>
  </div>
</div>

<?php 
  if ($iCurrentUserFamID == $iFamilyID || $_SESSION['user']->isSeePrivacyDataEnabled()) {
?>
    <div class="col-lg-9 col-md-9 col-sm-9">
      <div class="box box-success box-body">
        <table class="table user-list table-hover data-person" width="100%">
          <thead>
            <tr>
              <th><span><?= gettext("Family Members") ?></span></th>
              <th class="text-center"><span><?= gettext("Role") ?></span></th>
              <th><span><?= gettext("Classification") ?></span></th>
              <th><span><?= gettext("Birthday") ?></span></th>
              <th><span><?= gettext("Email") ?></span></th>
              <th></th>
              </tr>
            </thead>
          <tbody>
        <?php 
          foreach ($family->getActivatedPeople() as $person) {
        ?>
            <tr>
              <td>
                <img src="<?= SystemURLs::getRootPath() ?>/api/persons/<?= $person->getId() ?>/thumbnail"
                                       width="40" height="40"
                                       class="initials-image img-circle"/>
                <a href="<?= $person->getViewURI() ?>"
                    class="user-link"><?= $person->getFullName() ?> </a>
              </td>
              <td class="text-center">
            <?php
              $famRole = $person->getFamilyRoleName();
              $labelColor = 'label-default';
              if ($famRole == gettext('Head of Household')) {
              } elseif ($famRole == gettext('Spouse')) {
                  $labelColor = 'label-info';
              } elseif ($famRole == gettext('Child')) {
                  $labelColor = 'label-warning';
              } 
            ?>
                <span class='label <?= $labelColor ?>'> <?= $famRole ?></span>
              </td>
              <td>
                  <?= $person->getClassification() ? $person->getClassification()->getOptionName() : "" ?>
              </td>
              <td>
                <?= OutputUtils::FormatBirthDate($person->getBirthYear(),
                    $person->getBirthMonth(), $person->getBirthDay(), "-", $person->getFlags()) ?>
              </td>
              <td>
            <?php $tmpEmail = $person->getEmail();
                    if ($tmpEmail != "") {
                      array_push($sFamilyEmails, $tmpEmail); 
                  ?>
                      <a href="#"><a href="mailto:<?= $tmpEmail ?>"><?= $tmpEmail ?></a></a>
                  <?php
                    } 
                  ?>
              </td>
              <td style="width: 20%;">
                <?php
                  if ($_SESSION['user']->isShowCartEnabled()) {
                ?>
                  <a class="AddToPeopleCart" data-cartpersonid="<?= $person->getId() ?>">
                    <span class="fa-stack">
                      <i class="fa fa-square fa-stack-2x"></i>
                      <i class="fa fa-cart-plus fa-stack-1x fa-inverse"></i>
                    </span>
                  </a>
                <?php
                  }
                ?>
                <?php 
                  if ($bOkToEdit) {
                ?>
                  <a href="<?= SystemURLs::getRootPath() ?>/PersonEditor.php?PersonID=<?= $person->getId() ?>" class="table-link">
                    <span class="fa-stack"  style="color:green">
                      <i class="fa fa-square fa-stack-2x"></i>
                      <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                    </span>
                  </a>
                  <a class="delete-person" data-person_name="<?= $person->getFullName() ?>"
                         data-person_id="<?= $person->getId() ?>" data-view="family">
                    <span class="fa-stack"  style="color:red">
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
      </div>
    </div>
<?php
  }
?>
</div>

<?php if ($iCurrentUserFamID == $iFamilyID || $_SESSION['user']->isSeePrivacyDataEnabled()) { ?>
<div class="row">
  <div class="col-lg-12">
     <div class="nav-tabs-custom tab-success">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" class="active"><a href="#timeline" aria-controls="timeline" role="tab" data-toggle="tab"><?= gettext("Timeline") ?></a></li>
          <li role="presentation"><a href="#properties" aria-controls="properties" role="tab" data-toggle="tab"><?= gettext("Assigned Properties") ?></a></li>
        <?php 
          if ( $_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) {
        ?>
          <li role="presentation"><a href="#finance" aria-controls="finance" role="tab" data-toggle="tab"><i class="fa fa-credit-card"></i> <?= gettext("Automatic Payments") ?></a></li>
          <li role="presentation"><a href="#pledges" aria-controls="pledges" role="tab" data-toggle="tab"><i class="fa fa-bank"></i> <?= gettext("Pledges and Payments") ?></a></li>
          <?php
            } 
          ?>
          <li role="presentation"><a href="#notes" aria-controls="notes" role="tab" data-toggle="tab"><i class="fa fa-files-o"></i> <?= gettext("Notes") ?></a></li>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
          <div role="tab-pane fade" class="tab-pane active" id="timeline">
              <ul class="timeline">
                <!-- timeline time label -->
                <li class="time-label">
                  <span class="bg-red">
                    <?= $curYear ?>
                  </span>
                </li>
              <!-- /.timeline-label -->

              <!-- timeline item -->
              <?php 
                $countMainTimeLine    = 0;  // number of items in the MainTimeLines

                foreach ($timelineServiceItems as $item) {
                 $countMainTimeLine++;
       
                 if ($countMainTimeLine > $maxMainTimeLineItems) break;// we break after 20 $items
                  if ($curYear != $item['year']) {
                    $curYear = $item['year']; 
              ?>
                <li class="time-label">
                    <span class="bg-gray">
                        <?= $curYear ?>
                    </span>
                </li>
              <?php
                } 
              ?>
                <li>
                  <!-- timeline icon -->
                  <i class="fa <?= $item['style'] ?>"></i>

                  <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i><?= $item['datetime'] ?> 
                    <?php 
                        if (($_SESSION['user']->isNotesEnabled()) && (isset($item["editLink"]) || isset($item["deleteLink"])) && $item['slim']) {
                    ?>
                    &nbsp;
                  <?php 
                    if (isset($item["editLink"])) {
                  ?>
                    <a href="<?= $item["editLink"] ?>">
                      <span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x"></i>
                        <i class="fa fa-edit fa-stack-1x fa-inverse"></i>
                      </span>
                    </a>
                  <?php
                    }
                    
                    if (isset($item["deleteLink"])) {
                  ?>
                    <a href="<?= $item["deleteLink"] ?>">
                        <span class="fa-stack">
                        <i class="fa fa-square fa-stack-2x" style="color:red"></i>
                        <i class="fa fa-trash fa-stack-1x fa-inverse" ></i>
                      </span>
                    </a>
              <?php
                } 
              } ?>
                  </span>

                  <h3 class="timeline-header">
                <?php 
                  if (in_array('headerlink', $item)) {
                ?>
                      <a href="<?= $item['headerlink'] ?>"><?= $item['header'] ?></a>
                <?php
                  } else {
                ?>
                      <?= gettext($item['header']) ?>
                <?php
                  } 
                ?>
                  </h3>

                  <div class="timeline-body">
                     <pre><?= $item['text'] ?></pre>
                  </div>

                <?php 
                   if (($_SESSION['user']->isNotesEnabled()) && (isset($item["editLink"]) || isset($item["deleteLink"])) && !$item['slim']) {
                ?>
                  <div class="timeline-footer">
                <?php 
                  if (isset($item["editLink"])) {
                ?>
                    <a href="<?= $item["editLink"] ?>">
                      <button type="button" class="btn btn-primary"><i class="fa fa-edit"></i></button>
                    </a>
                <?php
                  }
                  
                  if (isset($item["deleteLink"])) {
                ?>
                    <a href="<?= $item["deleteLink"] ?>">
                      <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                    </a>
                <?php
                  } 
                ?>
                  </div>
                <?php
                  } 
                ?>
              </div>
            </li>
          <?php
            } 
          ?>
            <!-- END timeline item -->
          </ul>
        </div>
        <div role="tab-pane fade" class="tab-pane" id="properties">
            <div class="main-box clearfix">
              <div class="main-box-body clearfix">
            <?php
              $sAssignedProperties = ",";
            ?>
                <table width="100%" cellpadding="4" id="assigned-properties-table" class="table table-condensed dt-responsive dataTable no-footer dtr-inline"></table>
              <?php
                if ($bOkToEdit) {
              ?>
                <div class="alert alert-info">
                <div>
                  <h4><strong><?= gettext("Assign a New Property") ?>:</strong></h4>

                  <div class="row">
                    <div class="form-group col-xs-12 col-md-7">
                        <select name="PropertyId" id="input-family-properties" class="input-family-properties form-control select2"
                                style="width:100%" data-placeholder="<?= gettext("Select") ?> ..." data-familyID="<?= $iFamilyID ?>">
                          <option selected disabled> -- <?= gettext('select an option') ?>--</option>
                        <?php
                          foreach ($ormProperties as $ormProperty) {
                            //If the property doesn't already exist for this Person, write the <OPTION> tag
                            if (strlen(strstr($sAssignedProperties, "," . $ormProperty->getProId() . ",")) == 0) {
                        ?>
                          <option value="<?= $ormProperty->getProId() ?>" data-pro_Prompt="<?= $ormProperty->getProPrompt() ?>" data-pro_Value=""><?= $ormProperty->getProName() ?></option>*/
                        <?php
                            }
                          } 
                        ?>
                        </select>
                      </div>
                    <div id="prompt-box" class="col-xs-12 col-md-7"></div>
                      <div class="form-group col-xs-12 col-md-7">
                        <input type="submit" class="btn btn-primary assign-property-btn" value="<?= gettext("Assign") ?>">
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
        if ($_SESSION['user']->isFinanceEnabled()) {
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
                      href="<?= SystemURLs::getRootPath() ?>/AutoPaymentEditor.php?AutID=-1&FamilyID=<?= $family->getId() ?>&amp;linkBack=FamilyView.php?FamilyID=<?= $iFamilyID ?>"><?= gettext("Add a new automatic payment") ?></a>
                </p>
              </div>
            </div>
          </div>
        <div role="tab-pane fade" class="tab-pane" id="pledges">
            <div class="main-box clearfix">
                <div class="main-box-body clearfix">
                  <input type="checkbox" name="ShowPledges" id="ShowPledges"
                      value="1" <?= ($_SESSION['sshowPledges'])?" checked":"" ?>><?= gettext("Show Pledges") ?>
                  <input type="checkbox" name="ShowPayments" id="ShowPayments"
                      value="1" <?= ($_SESSION['sshowPayments'])?" checked":"" ?>><?= gettext("Show Payments") ?>
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
                       href="<?= SystemURLs::getRootPath() ?>/PledgeEditor.php?FamilyID=<?= $family->getId() ?>&amp;linkBack=FamilyView.php?FamilyID=<?= $iFamilyID ?>&amp;PledgeOrPayment=Pledge"><?= gettext("Add a new pledge") ?></a>
                    <a class="btn btn-default"
                       href="<?= SystemURLs::getRootPath() ?>/PledgeEditor.php?FamilyID=<?= $family->getId() ?>&amp;linkBack=FamilyView.php?FamilyID=<?= $iFamilyID ?>&amp;PledgeOrPayment=Payment"><?= gettext("Add a new payment") ?></a>
                  </p>

                <?php 
                  if ($_SESSION['user']->isCanvasserEnabled()) {
                ?>
                  <p align="center">
                    <a class="btn btn-default"
                         href="<?= SystemURLs::getRootPath() ?>/CanvassEditor.php?FamilyID=<?= $family->getId() ?>&amp;FYID=<?= $_SESSION['idefaultFY'] ?>&amp;linkBack=FamilyView.php?FamilyID=<?= $iFamilyID ?>"><?= MakeFYString($_SESSION['idefaultFY']) . gettext(" Canvass Entry") ?></a>
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
        <div role="tab-pane fade" class="tab-pane" id="notes">
              <ul class="timeline">
                <!-- note time label -->
                <li class="time-label">
                  <span class="bg-yellow">
                    <?php echo date_create()->format(SystemConfig::getValue('sDateFormatLong')) ?>
                  </span>
                </li>
                <!-- /.note-label -->

                <!-- note item -->
              <?php 
                foreach ($timelineNotesServiceItems as $item) {
              ?>
                <li>
                  <!-- timeline icon -->
                  <i class="fa <?= $item['style'] ?>"></i>

                  <div class="timeline-item">
                    <span class="time">
                      <i class="fa fa-clock-o"></i> <?= $item['datetime'] ?>
                      &nbsp;

                    <?php 
                     if ($item['slim']) {
                       if ($item['editLink'] != '') {
                    ?>
                      <a href="<?= $item['editLink'] ?>">
                        <span class="fa-stack">
                          <i class="fa fa-square fa-stack-2x"></i>
                          <i class="fa fa-edit fa-stack-1x fa-inverse"></i>
                        </span>
                      </a>
                    <?php
                      }
                      
                      if ($item['deleteLink'] != '') {
                    ?>
                      <a href="<?= $item['deleteLink'] ?>">
                        <span class="fa-stack">
                          <i class="fa fa-square fa-stack-2x" style="color:red"></i>
                          <i class="fa fa-trash fa-stack-1x fa-inverse" ></i>
                        </span>
                      </a>
                   <?php
                      }
                    }
                   ?>
                  </span>
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
                      <?= $item['text'] ?>
                    </div>

                  <?php if (($_SESSION['user']->isNotesEnabled()) && ($item['editLink'] != '' || $item['deleteLink'] != '')) {                                            ?>
                    <div class="timeline-footer">
                    <?php 
                      if (!$item['slim']) {
                    ?>
                      <?php 
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
                      ?>
                    </div>
                  <?php
                    } 
                  } 
                ?>
                </div>
              </li>
            <?php
              } 
            ?>
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
<div id="photoUploader"></div>

<div class="modal fade" id="confirm-delete-image" tabindex="-1" role="dialog" aria-labelledby="delete-Image-label"
     aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="delete-Image-label"><?= gettext("Confirm Delete") ?></h4>
      </div>
      <div class="modal-body">
        <p><?= gettext("You are about to delete the profile photo, this procedure is irreversible.") ?></p>
        <p><?= gettext("Do you want to proceed?") ?></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= gettext("Cancel") ?></button>
        <button class="btn btn-danger danger" id="deletePhoto"><?= gettext("Delete") ?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirm-verify" tabindex="-1" role="dialog" aria-labelledby="confirm-verify-label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="confirm-verify-label"><?= gettext("Request Family Info Verification") ?></h4>
      </div>
      <div class="modal-body">
        <b><?= gettext("Select how do you want to request the family information to be verified") ?></b>
        <p>
        <?php 
          if (count($sFamilyEmails) > 0) {
        ?>
          <p><?= gettext("You are about to email copy of the family information in pdf to the following emails") ?>
          <ul>
        <?php 
          foreach ($sFamilyEmails as $tmpEmail) {
        ?>
            <li><?= $tmpEmail ?></li>
        <?php
          } 
        ?>
          </ul>
        </p>
      </div>
    <?php
      } 
    ?>
      <div class="modal-footer text-center">
    <?php 
      if (count($sFamilyEmails) > 0 && !empty(SystemConfig::getValue('sSMTPHost'))) {
    ?>
        <button type="button" id="onlineVerify" class="btn btn-warning warning">
          <i class="fa fa-envelope"></i> 
          <?= gettext("Online Verification") ?>
        </button>
    <?php
      } 
    ?>
        <button type="button" id="verifyDownloadPDF" class="btn btn-info">
          <i class="fa fa-download"></i> 
          <?= gettext("PDF Report") ?>
        </button>
        <button type="button" id="verifyNow" class="btn btn-success">
          <i class="fa fa-check"></i>
          <?= gettext("Verified In Person") ?>
        </button>
      </div>
    </div>
  </div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/jquery-photo-uploader/PhotoUploader.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/FamilyView.js" ></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/MemberView.js" ></script>
  
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.currentFamily = <?= $iFamilyID ?>;
  window.CRM.currentActive = <?= (empty($family->getDateDeactivated()) ? 'true' : 'false') ?>;
  window.CRM.fam_Name      = "<?= $family->getName() ?>";
  window.CRM.iPhotoHeight  = <?= SystemConfig::getValue("iPhotoHeight") ?>;
  window.CRM.iPhotoWidth   = <?= SystemConfig::getValue("iPhotoWidth") ?>;
  window.CRM.familyMail    = "<?= $family->getEmail() ?>";

  
  var dataT = 0;
  var dataPaymentTable = 0;
  var pledgePaymentTable = 0;
</script>

<?php require "Include/Footer.php" ?>
