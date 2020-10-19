<?php
/*******************************************************************************
 *
 *  filename    : SelectDelete
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001-2003 Deane Barker, Lewis Franklin
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\EgiveQuery;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\FamilyCustomQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;



// Security: User must have Delete records permission
// Otherwise, re-direct them to the main menu.
if (!SessionUser::getUser()->isDeleteRecordsEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

// default values to make the newer versions of php happy
$iFamilyID = 0;
$iDonationFamilyID = 0;
$sMode = 'family';

if (!empty($_GET['FamilyID'])) {
    $iFamilyID = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');
}

if (!empty($_GET['PersonID'])) {
    $iPersonId = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');
}

if (!empty($_GET['DonationFamilyID'])) {
    $iDonationFamilyID = InputUtils::LegacyFilterInput($_GET['DonationFamilyID'], 'int');
}
if (!empty($_GET['mode'])) {
    $sMode = $_GET['mode'];
}

if (isset($_GET['CancelFamily'])) {
    RedirectUtils::Redirect("FamilyView.php?FamilyID=$iFamilyID");
    exit;
}

$numberPersons = 0;

if ( $sMode == 'person' ) {
  $person    = PersonQuery::Create()->findOneById($iPersonId);
  $iFamilyID = $person->getFamId();
} else {
  $family = FamilyQuery::Create()->findOneById($iFamilyID);
  $numberPersons = $family->getPeople()->count();
  if (PersonQuery::Create()->findOneByFamId($iFamilyID)) {
    $iPersonId = PersonQuery::Create()->findOneByFamId($iFamilyID)->getId();
  }
}

$DonationMessage = '';

// Move Donations from 1 family to another
if (SessionUser::getUser()->isFinanceEnabled() && isset($_GET['MoveDonations']) && $iFamilyID && $iDonationFamilyID && $iFamilyID != $iDonationFamilyID) {
    $today = date('Y-m-d');

    $pledges = PledgeQuery::Create()->findByFamId($iFamilyID);

    foreach ($pledges as $pledge) {
      $pledge->setFamId ($iDonationFamilyID);
      $pledge->setDatelastedited ($today);
      $pledge->setEditedby (SessionUser::getUser()->getPersonId());
      $pledge->save();
    }

    $egives = EgiveQuery::Create()->findByFamId($iFamilyID);

    foreach ($egives as $egive) {
      $egive->setFamId ($iDonationFamilyID);
      $egive->setDateLastEdited ($today);
      $egive->setEditedby (SessionUser::getUser()->getPersonId());
      $egive->save();
    }

    $DonationMessage = '<p><b><font color=red>' . _('All donations from this family have been moved to another family.') . '</font></b></p>';
}

//Set the Page Title
if ($numberPersons > 1) {
   $sPageTitle = _('Family Delete Confirmation');
} else {
   $sPageTitle = _('Person Delete Confirmation');
}

//Do we have deletion confirmation?
if (isset($_GET['Confirmed'])) {
    // Delete Family
    // Delete all associated Notes associated with this Family record
    $notes = NoteQuery::Create()->findByFamId($iFamilyID);

    if (!is_null($notes)) {
      $notes->delete();
    }

    // Delete Family pledges
    $pledges = PledgeQuery::Create()->filterByPledgeorpayment('Pledge')->findByFamId($iFamilyID);

    if (!is_null($pledges)) {
      $pledges->delete();
    }

    // Remove family property data
    $properties = PropertyQuery::Create()->findByProClass('f');

    foreach ($properties as $property) {
      $records = Record2propertyR2pQuery::Create()->filterByR2pProId ($property->getProId())->findByR2pRecordId ($iFamilyID);
      $records->delete();
    }

    if (isset($_GET['Members'])) {
        // Delete all persons that were in this family
        PersonQuery::create()->filterByFamId($iFamilyID)->find()->delete();
    } else {
        // Reset previous members' family ID to 0 (undefined)
        $persons = PersonQuery::Create()->findByFamId ($iFamilyID);

        foreach ($persons as $person) {
          $person->setFamId (0);
          $person->save();
        }
    }

    // Delete the specified Family record
    $family = FamilyQuery::Create()->findById ($iFamilyID)->delete();

    // Remove custom field data
    $ormFamCusts = FamilyCustomQuery::create()
        ->findByFamId($iFamilyID);

    foreach ($ormFamCusts as $ormFamCust) {
        $ormFamCust->delete();
    }

    // Delete the photo files, if they exist
    $photoThumbnail = 'Images/Family/thumbnails/' . $iFamilyID . '.jpg';
    if (file_exists($photoThumbnail)) {
        unlink($photoThumbnail);
    }
    $photoFile = 'Images/Family/' . $iFamilyID . '.jpg';
    if (file_exists($photoFile)) {
        unlink($photoFile);
    }

    // Redirect back to the family listing
    RedirectUtils::Redirect('/v2/familylist');
}


//Get the family record in question
$theFamily = FamilyQuery::Create()->findOneById ($iFamilyID);

require 'Include/Header.php';

?>
<div class="card">
    <div class="card-body">
        <?php
        // Delete Family Confirmation
        // See if this family has any donations OR an Egive association
        $ormDonations = PledgeQuery::Create()->filterByPledgeorpayment('Payment')->findByFamId($iFamilyID);

        $bIsDonor = ($ormDonations->count() > 0);

        if ($bIsDonor && !SessionUser::getUser()->isFinanceEnabled()) {
            // Donations from Family. Current user not authorized for Finance
            if ($numberPersons > 1) {
        ?>
              <p class="LargeText">
                 <?= _('Sorry, there are records of donations from this family. This family may not be deleted.') ?>
                 <br><br>
                 <a href="<?= SystemURLs::getRootPath() ?>/FamilyView.php?FamilyID=<?= $iFamilyID ?>"><?= _('Return to Family View') ?></a>
              </p>
        <?php
            } else {
        ?>
              <p class="LargeText">
                 <?= _('Sorry, there are records of donations from this Person. This Person may not be deleted.') ?>
                 <br><br>
                 <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?=  $iPersonId ?>"><?= _('Return to Person View') ?></a>
              </p>
        <?php
            }
        } elseif ($bIsDonor && SessionUser::getUser()->isFinanceEnabled()) {
            // Donations from Family. Current user authorized for Finance.
            // Select another family to move donations to.
            if ($numberPersons > 1) {
          ?>
              <p class="LargeText">
                <?= _('WARNING: This family has records of donations and may NOT be deleted until these donations are associated with another family.') ?>
              </p>
          <?php
            } else {
          ?>
              <p class="LargeText">
                <?= _('WARNING: This person has records of donations and may NOT be deleted until these donations are associated with another person or another family.') ?>
              </p>
          <?php
            }
          ?>
          <form name=SelectFamily method=get action=SelectDelete.php>
            <div class="ShadedBox">
               <div class="LightShadedBox">
                 <strong><?= (!is_null ($theFamily)?_('Family Name'):_('Person Name')) ?> : <?= $theFamily->getName() ?></strong>
               </div>
               <p>
                 <?= _('Please select another person or family with whom to associate these donations:') ?>
                 <br>
                 <b><?= _('WARNING: This action can not be undone and may have legal implications!') ?></b>
               </p>
               <input name=FamilyID value="<?= $iFamilyID ?>" type=hidden>
               <select name="DonationFamilyID" class="form-control input-sm">
                  <option value=0 selected><?= _('Unassigned') ?></option>
                <?php
                  //Get Families for the drop-down
                  $ormFamilies = FamilyQuery::Create()
                      ->filterByDateDeactivated(NULL) // GDPR
                      ->orderByName()
                      ->find();

                  $personHeads = PersonQuery::create()
                        ->filterByDateDeactivated(NULL) // GDPR
                        ->filterByFmrId((SystemConfig::getValue('sDirRoleHead') ? SystemConfig::getValue('sDirRoleHead') : '1'));

                  if (intval(SystemConfig::getValue('sDirRoleSpouse')) > 0) {
                      $personHeads->_or()->filterByFmrId((SystemConfig::getValue('sDirRoleHead') ? SystemConfig::getValue('sDirRoleSpouse') : '1'));
                  }

                  $personHeads->filterByFamId(0, Criteria::NOT_EQUAL)
                        ->orderByFamId()
                        ->find();

                  $aHead = [];
                  foreach ($personHeads as $personHead) {
                      if ($personHead->getFirstName() && $aHead[$personHead->getFamId()]) {
                          $aHead[$personHead->getFamId()] .= ' & ' . $personHead->getFirstName();
                      } elseif ($personHead->getFirstName()) {
                          $aHead[$personHead->getFamId()] = $personHead->getFirstName();
                      }
                  }

                foreach ($ormFamilies as $family) {
              ?>
                  <option value="<?= $family->getId() ?>" <?= ($family->getId() == $iFamilyID)?'selected':''?>><?= $family->getName() ?>
              <?php
                    if ($aHead[$family->getId()]) {
                        echo ', ' . $aHead[$family->getId()];
                    }
                    if ($family->getId() == $iFamilyID) {
                        echo ' -- ' . (!is_null ($theFamily)?_('CURRENT FAMILY WITH DONATIONS'):_('CURRENT PERSON WITH DONATIONS'));
                    } else {
                        echo ' ' . MiscUtils::FormatAddressLine($family->getAddress1(), $family->getCity(), $family->getState());
                    }
                }
              ?>
                </select>
                <br><br>
          <?php
            if (!is_null ($theFamily)) {
          ?>
              <input type="submit" class="btn btn-primary" name="CancelFamily" value="<?= _("Cancel and Return to Family View") ?>"> &nbsp; &nbsp;
              <input type="submit" class="btn btn-danger" name="MoveDonations" value="<?= _("Move Donations to Selected Family") ?>">
          <?php
            } else {
          ?>
              <input type="submit" class="btn btn-primary" name="CancelFamily" value="<?= _("Cancel and Return to Person View") ?>"> &nbsp; &nbsp;
              <input type="submit" class="btn btn-danger" name="MoveDonations" value="<?= _("Move Donations to Selected Person") ?>">
          <?php
            }
          ?>
            </div>
          </form>

        <?php
            // Show payments connected with family
            // -----------------------------------
        ?>
          <br><br>
        <?php
            //Get the pledges for this family
            $ormPledges = PledgeQuery::create()
                ->leftJoinPerson()
                ->leftJoinDonationFund()
                ->filterByFamId($iFamilyID)
                ->orderByDate()
                ->find();
        ?>
        <table cellspacing="0" width="100%" class="table table-striped table-bordered data-table-pledges">
          <thead>
                <th><?= _('Type') ?></th>
                <th><?= _('Fund') ?></th>
                <th><?= _('Fiscal Year') ?></th>
                <th><?= _('Date') ?></th>
                <th><?= _('Amount') ?></th>
                <th><?= _('Schedule') ?></th>
                <th><?= _('Method') ?></th>
                <th><?= _('Comment') ?></th>
                <th><?= _('Date Updated') ?></th>
                <th><?= _('Updated By') ?></th>
          </thead>
          <tbody>
          <?php
            $tog = 0;
            //Loop through all pledges
            foreach ($ormPledges as $ormPledge) {
                ?>
                <tr>
                    <td><?= _($ormPledge->getPledgeorpayment()) ?></td>
                    <td><?= _($ormPledge->getDonationFund()->getName()) ?></td>
                    <td><?= MiscUtils::MakeFYString($ormPledge->getFyid()) ?></td>
                    <td><?= OutputUtils::change_date_for_place_holder($ormPledge->getDate()->format('Y-m-d')) ?></td>
                    <td><?= OutputUtils::money_localized($ormPledge->getAmount()) ?></td>
                    <td><?= _($ormPledge->getSchedule()) ?></td>
                    <td><?= _($ormPledge->getMethod()) ?></td>
                    <td><?= $ormPledge->getComment() ?></td>
                    <td><?= OutputUtils::change_date_for_place_holder($ormPledge->getDatelastedited()->format('Y-m-d')) ?></td>
                    <td><?= $ormPledge->getPerson()->getFirstname() . ' ' . $ormPledge->getPerson()->getLastName() ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
          </table>
        <?php
          } else {
            // No Donations from family.  Normal delete confirmation
        ?>
            <?= $DonationMessage ?>
            <p class='alert alert-warning'>
              <b><?= (!is_null ($theFamily)?_('Please confirm deletion of this family record:'):_('Please confirm deletion of this Person record:')) ?></b>
              <br/>
              <?= (!is_null ($theFamily)?_('Note: This will also delete all Notes associated with this Family record.'):_('Note: This will also delete all Notes associated with this Person record.')) ?>
              <?= _('(this action cannot be undone)') ?>
            </p>
            <div>
               <strong><?= (!is_null ($theFamily)?_('Family Name'):_('Person Name')) ?>:</strong>
               &nbsp;<?= (!is_null ($theFamily)?$theFamily->getName():$person->getFirstName()." ".$person->getLastName()) ?>
            </div>
            <br/>
            <div>
              <strong><?= ((!is_null ($theFamily) || $numberPersons > 1)?_('Family Members'):_('Member')) ?>:</strong>
              <ul>
              <?php
                //List Family Members
                $persons = PersonQuery::create()->findByFamId ($iFamilyID);
                foreach ($persons as $person) {
              ?>
                  <li><?= $person->getFirstName() ?> <?= $person->getLastName() ?></li>
              <?php
                }
              ?>
              </ul>
            </div>
          <?php
            if (!is_null ($theFamily)) {
          ?>
              <p class="text-center">
                <a class="btn btn-danger" href="<?= SystemURLs::getRootPath() ?>/SelectDelete.php?Confirmed=Yes&FamilyID=<?= $iFamilyID ?>"><?= _('Delete Family Record ONLY') ?></a>
                <a class="btn btn-danger" href="<?= SystemURLs::getRootPath() ?>/SelectDelete.php?Confirmed=Yes&Members=Yes&FamilyID=<?= $iFamilyID ?>"><?= _('Delete Family Record AND Family Members') ?></a>
                <a class="btn btn-info" href="<?= SystemURLs::getRootPath() ?>/FamilyView.php?FamilyID=<?= $iFamilyID ?>"><?= _('No, cancel this deletion') ?></a>
              </p>
          <?php
            } else {
          ?>
              <p class="text-center">
                <a class="btn btn-danger" href="<?= SystemURLs::getRootPath() ?>/SelectDelete.php?Confirmed=Yes&Members=Yes&FamilyID=<?= $iFamilyID ?>"><?= _('Delete Person Record') ?></a>
                <a class="btn btn-info" href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $iPersonId ?>"><?= _('No, cancel this deletion') ?></a>
              </p>
          <?php
            }
        }
      ?>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
$(document).ready(function () {
  $(".data-table-pledges").DataTable({
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    responsive: true});
});
</script>

<?php require 'Include/Footer.php' ?>
