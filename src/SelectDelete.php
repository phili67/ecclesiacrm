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



// Security: User must have Delete records permission
// Otherwise, re-direct them to the main menu.
if (!SessionUser::getUser()->isDeleteRecordsEnabled()) {
    RedirectUtils::Redirect('Menu.php');
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
                  $ormFamilies = FamilyQuery::Create()->orderByName()->find();

                  // Build Criteria for Head of Household
                  $head_criteria = ' per_fmr_ID = ' . SystemConfig::getValue('sDirRoleHead') ? SystemConfig::getValue('sDirRoleHead') : '1';
                  // If more than one role assigned to Head of Household, add OR
                  $head_criteria = str_replace(',', ' OR per_fmr_ID = ', $head_criteria);
                  // Add Spouse to criteria
                  if (intval(SystemConfig::getValue('sDirRoleSpouse')) > 0) {
                      $head_criteria .= ' OR per_fmr_ID = ' . SystemConfig::getValue('sDirRoleSpouse');
                  }
                  // Build array of Head of Households and Spouses with fam_ID as the key
                  $sSQL = 'SELECT per_FirstName, per_fam_ID FROM person_per WHERE per_fam_ID > 0 AND (' . $head_criteria . ') ORDER BY per_fam_ID';

                  $connection = Propel::getConnection();
                  $pdo_head = $connection->prepare($sSQL);

                  $aHead = '';
                  while ($row = $pdo_head->fetch( \PDO::FETCH_ASSOC )) {

                  }

                  $rs_head = RunQuery($sSQL);
                  $aHead = '';
                  while (list($head_firstname, $head_famid) = mysqli_fetch_row($rs_head)) {
                      if ($head_firstname && $aHead[$head_famid]) {
                          $aHead[$head_famid] .= ' & ' . $head_firstname;
                      } elseif ($head_firstname) {
                          $aHead[$head_famid] = $head_firstname;
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
            $sSQL = 'SELECT plg_plgID, plg_FYID, plg_date, plg_amount, plg_schedule, plg_method,
             plg_comment, plg_DateLastEdited, plg_PledgeOrPayment, a.per_FirstName AS EnteredFirstName, a.Per_LastName AS EnteredLastName, b.fun_Name AS fundName
             FROM pledge_plg
             LEFT JOIN person_per a ON plg_EditedBy = a.per_ID
             LEFT JOIN donationfund_fun b ON plg_fundID = b.fun_ID
             WHERE plg_famID = ' . $iFamilyID . ' ORDER BY pledge_plg.plg_date';
            $rsPledges = RunQuery($sSQL);
        ?>
        <table cellspacing="0" width="100%" class="table table-striped table-bordered">
          <theader>
            <tr>
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
            </tr>
          </theader>
          <tbody>
          <?php
            $tog = 0;
            //Loop through all pledges
            while ($aRow = mysqli_fetch_array($rsPledges)) {
                $tog = (!$tog);
                $plg_FYID = '';
                $plg_date = '';
                $plg_amount = '';
                $plg_schedule = '';
                $plg_method = '';
                $plg_comment = '';
                $plg_plgID = 0;
                $plg_DateLastEdited = '';
                $plg_EditedBy = '';
                extract($aRow);

           ?>
                <tr>
                    <td><?= _($plg_PledgeOrPayment) ?></td>
                    <td><?= _($fundName) ?></td>
                    <td><?= MiscUtils::MakeFYString($plg_FYID) ?></td>
                    <td><?= OutputUtils::change_date_for_place_holder($plg_date) ?></td>
                    <td><?= OutputUtils::money_localized($plg_amount) ?></td>
                    <td><?= _($plg_schedule) ?></td>
                    <td><?= _($plg_method) ?></td>
                    <td><?= $plg_comment ?></td>
                    <td><?= OutputUtils::change_date_for_place_holder($plg_DateLastEdited) ?></td>
                    <td><?= $EnteredFirstName . ' ' . $EnteredLastName ?></td>
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
  $(".data-table").DataTable({
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    responsive: true});
});
</script>

<?php require 'Include/Footer.php' ?>
