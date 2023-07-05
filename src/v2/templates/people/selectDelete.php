<?php
/*******************************************************************************
 *
 *  filename    : templates/selectDelete.php
 *  last change : 2023-07-03
 *  website     : http://www.ecclesiacrm.com
 * 
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorization
 *
 ******************************************************************************/

 use EcclesiaCRM\SessionUser;
 use EcclesiaCRM\dto\SystemConfig;
 use EcclesiaCRM\Utils\MiscUtils;
 use EcclesiaCRM\Utils\OutputUtils;

 use EcclesiaCRM\PledgeQuery;
 use EcclesiaCRM\EgiveQuery;
 use EcclesiaCRM\NoteQuery;
 use EcclesiaCRM\PropertyQuery;
 use EcclesiaCRM\Record2propertyR2pQuery;
 use EcclesiaCRM\PersonQuery;
 use EcclesiaCRM\FamilyQuery;
 use EcclesiaCRM\FamilyCustomQuery;
 use EcclesiaCRM\Utils\RedirectUtils;

 use Propel\Runtime\ActiveQuery\Criteria;


 //Get the family record in question
$theFamily = FamilyQuery::Create()->findOneById ($iFamilyID);

$DonationMessage = '';

// Move Donations from 1 family to another
if (isset($_POST['MoveDonations']) && $iFamilyID && $iDonationFamilyID && $iFamilyID != $iDonationFamilyID) {
    $today = date('Y-m-d');

    $pledges = PledgeQuery::Create()->findByFamId($iFamilyID);

    foreach ($pledges as $pledge) {
      $pledge->setFamId ($iDonationFamilyID);
      $pledge->setDatelastedited ($today);
      $pledge->setEditedby (SessionUser::getUser()->getPersonId());
      $pledge->setMoveDonationsComment(_("Donations transferred from family") .":" .$theFamily->getName(). " (" . $theFamily->getAddress().")");
      $pledge->save();
    }

    $egives = EgiveQuery::Create()->findByFamId($iFamilyID);

    foreach ($egives as $egive) {
      $egive->setFamId ($iDonationFamilyID);
      $egive->setDateLastEdited ($today);
      $egive->setEditedby (SessionUser::getUser()->getPersonId());
      $pledge->setMoveDonationsComment(_("eGives transferred from family") .":" .$theFamily->getName(). " (" . $theFamily->getAddress().")");
      $egive->save();
    }

    $DonationMessage =  _('All donations from this family have been moved to another family.');
}

// Move Donations from 1 family to another
if (isset($_POST['DeleteDonations']) && $iFamilyID) {
  $today = date('Y-m-d');

  $pledges = PledgeQuery::Create()->findByFamId($iFamilyID);

  foreach ($pledges as $pledge) {
    $pledge->delete();
  }

  $egives = EgiveQuery::Create()->findByFamId($iFamilyID);

  foreach ($egives as $egive) {
    $egive->delete();
  }

  $DonationMessage = _('All donations from this family have been deleted.');
}

//Do we have deletion confirmation?
if ($Confirmed == 'Yes') {
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

    if ($Members == 'Yes') {
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
    $photoThumbnail = $sRootDocument . '/Images/Family/thumbnails/' . $iFamilyID . '.jpg';
    if (file_exists($photoThumbnail)) {
        unlink($photoThumbnail);
    }
    $photoFile = $sRootDocument . '/Images/Family/' . $iFamilyID . '.jpg';
    if (file_exists($photoFile)) {
        unlink($photoFile);
    }

    // Redirect back to the family listing
    RedirectUtils::Redirect('/v2/familylist');
}


require $sRootDocument . '/Include/Header.php';
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
                 <a href="<?= $args ?>/v2/people/family/view/<?= $iFamilyID ?>"><?= _('Return to Family View') ?></a>
              </p>
        <?php
            } else {
        ?>
              <p class="LargeText">
                 <?= _('Sorry, there are records of donations from this Person. This Person may not be deleted.') ?>
                 <br><br>
                 <a href="<?= $sRootPath ?>/v2/people/person/view/<?=  $iPersonId ?>"><?= _('Return to Person View') ?></a>
              </p>
        <?php
            }
        } elseif ($bIsDonor && SessionUser::getUser()->isFinanceEnabled()) {
            // Donations from Family. Current user authorized for Finance.
            // Select another family to move donations to.
            if ($numberPersons > 1) {
          ?>
              <div class="alert alert-danger">
                <?= _('WARNING: This family has records of donations and may NOT be deleted until these donations are associated with another family.') ?>
              </div>
          <?php
            } else {
          ?>
              <div class="alert alert-danger">
                <?= _('WARNING: This person has records of donations and may NOT be deleted until these donations are associated with another person or another family.') ?>
              </div>
          <?php
            }
          ?>
          <form name=SelectFamily method="post" action="<?= $sRootPath ?>/v2/people/family/donate/<?= $iFamilyID?>">
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
               <select name="DonationFamilyID" class="form-control form-control-sm">
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
            if ($numberPersons > 1) {
          ?>
              <button type="submit" class="btn btn-default" name="CancelFamily"><i class="fa fa-times"></i>  <?= _("Cancel and Return to Family View") ?></button> &nbsp; &nbsp;
              <button type="submit" class="btn btn-success" name="MoveDonations"><i class="fa fa-shuffle"></i>  <?= _("Move Donations to Selected Family") ?></button> &nbsp; &nbsp;
              <button type="submit" class="btn btn-danger" name="DeleteDonations"><i class="fa fa-trash-can"></i> <?= _("Delete Donations of Selected Family") ?></button>
          <?php
            } else {
          ?>
              <button type="submit" class="btn btn-default" name="CancelFamily"><i class="fa fa-times"></i>  <?= _("Cancel and Return to Person View") ?></button> &nbsp; &nbsp;
              <button type="submit" class="btn btn-success" name="MoveDonations"><i class="fa fa-shuffle"></i>  <?= _("Move Donations to Selected Person") ?></button> &nbsp; &nbsp;
              <button type="submit" class="btn btn-danger" name="DeleteDonations"><i class="fa fa-trash-can"></i> <?= _("Delete Donations of Selected Person") ?></button>
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
            <?php if (!empty($DonationMessage)) { ?>
              <div class="alert alert-danger">
                <?= $DonationMessage ?>
              </div>
            <?php } ?>
            <div class='alert alert-danger'>
              <b><?= (!is_null ($theFamily)?_('Please confirm deletion of this family record:'):_('Please confirm deletion of this Person record:')) ?></b>
              <br/>
              <?= (!is_null ($theFamily)?_('Note: This will also delete all Notes associated with this Family record.'):_('Note: This will also delete all Notes associated with this Person record.')) ?>
              <?= _('(this action cannot be undone)') ?>
          </div>
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
                <a class="btn btn-danger" href="<?= $sRootPath ?>/v2/people/family/delete/<?= $iFamilyID ?>/Yes"><i class="fa fa-trash-can"></i> <?= _('Delete Family Record ONLY') ?></a>
                <a class="btn btn-danger" href="<?= $sRootPath ?>/v2/people/family/delete/<?= $iFamilyID ?>/Yes/Yes"><i class="fa fa-trash-can"></i>  <?= _('Delete Family Record AND Family Members') ?></a>
                <a class="btn btn-info" href="<?= $sRootPath ?>/v2/people/family/view/<?= $iFamilyID ?>"><i class="fa fa-times"></i>  <?= _('No, cancel this deletion') ?></a>
              </p>
          <?php
            } else {
          ?>
              <p class="text-center">
                <a class="btn btn-danger" href="<?= $sRootPath ?>/v2/people/family/delete/<?= $iFamilyID ?>/Yes/Yes"><i class="fa fa-trash-can"></i> <?= _('Delete Person Record') ?></a>
                <a class="btn btn-info" href="<?= $sRootPath ?>/v2/people/person/view/<?= $iPersonId ?>"><i class="fa fa-delete"></i> <?= _('No, cancel this deletion') ?></a>
              </p>
          <?php
            }
        }
      ?>
    </div>
</div>


<script nonce="<?= $sCSPNonce ?>" >
    $(document).ready(function () {
    $(".data-table-pledges").DataTable({
        "language": {
        "url": window.CRM.plugin.dataTable.language.url
        },
        responsive: true});
    });
</script>

<script src="<?= $sRootPath ?>/skin/js/people/FamilyList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>