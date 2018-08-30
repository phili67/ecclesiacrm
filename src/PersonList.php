<?php
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PledgeQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;

$sMode = 'Active';
// Filter received user input as needed
if (isset($_GET['mode'])) {
    $sMode = InputUtils::LegacyFilterInput($_GET['mode']);
}

if (strtolower($sMode) == 'gdrp') {
  if (!$_SESSION['user']->isGdrpDpoEnabled()) {
    Redirect("Menu.php");
    exit;
  }

   $time = new DateTime('now');
   $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');
   
   $persons = PersonQuery::create()
            ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// RGPD, when a person is completely deactivated
            ->_or() // or : this part is unusefull, it's only for debugging
            ->useFamilyQuery()
              ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// RGPD, when a Family is completely deactivated
            ->endUse()
            ->orderByLastName()
            ->find();
            
} else if (strtolower($sMode) == 'inactive') {
  if (!$_SESSION['user']->isEditRecordsEnabled()) {
    Redirect("Menu.php");
    exit;
  }

  if (SystemConfig::getValue('bGDPR')) {
    $time = new DateTime('now');
    $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

    $persons = PersonQuery::create()
            ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// GDRP, when a person isn't under GDRP but deactivated, we only can see the person who are over a certain date
            ->_or()// this part is unusefull, it's only for debugging
            ->useFamilyQuery()
              ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// RGPD, when a Family is completely deactivated
            ->endUse()
            ->orderByLastName()
            ->find();
  } else {
    $time = new DateTime('now');
    
    $persons = PersonQuery::create()
            ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)
            ->orderByLastName()
            ->find();
  }
} else {
  if (!$_SESSION['user']->isEditRecordsEnabled()) {
    Redirect("Menu.php");
    exit;
  }

  $sMode = 'Active';
  $persons = PersonQuery::create()
          ->filterByDateDeactivated(null)
          ->orderByLastName()
          ->find();
}

// Set the page title and include HTML header
$sPageTitle = gettext(ucfirst($sMode)) . ' : ' . gettext('Person List');
require 'Include/Header.php'; ?>

<?php
  if ($_SESSION['user']->isAddRecordsEnabled() && strtolower($sMode) != 'gdrp' ) {
?>
<div class="pull-right">
  <a class="btn btn-success" role="button" href="PersonEditor.php"> 
    <span class="fa fa-plus" aria-hidden="true"></span><?= gettext('Add New Person') ?>
  </a>
</div>

<p><br/><br/></p>
<?php
  }
?>

<?php 
  if (strtolower($sMode) == 'gdrp') { 
?>
<div class="alert alert-warning">
    <strong> <?= gettext('WARNING: Some persons may have some records of donations and may NOT be deleted until these donations are associated with another person or Family.') ?> </strong><br>
    <strong> <?= gettext('WARNING: This action can not be undone and may have legal implications!') ?> </strong>
</div>
<?php 
  } 
?>

<div class="box">
    <div class="box-body">
        <table id="personlist" class="table table-striped table-bordered data-table" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= gettext('Name') ?></th>
                <th><?= gettext('First Name') ?></th>
                <th><?= gettext('Address') ?></th>
                <th><?= gettext('Home Phone') ?></th>
                <th><?= gettext('Cell Phone') ?></th>
                <th><?= gettext('email') ?></th>
                <th><?= gettext('Created') ?></th>
                <th><?= gettext('Edited') ?></th>
            <?php if (strtolower($sMode) == 'gdrp') { ?>
                <th><?= gettext('Deactivation date') ?></th>
                <th><?= gettext('Remove') ?></th>
            <?php } ?>
            </tr>
            </thead>
            <tbody>

            <!--Populate the table with Person details -->
          <?php 
            foreach ($persons as $person) {
          ?>
            <tr>
                <td><a href='PersonView.php?PersonID=<?= $person->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fa fa-square fa-stack-2x"></i>
                            <i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
                        </span>
                    </a>
                    <a href='PersonEditor.php?PersonID=<?= $person->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fa fa-square fa-stack-2x"></i>
                            <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                        </span>
                    </a><?= $person->getLastName() ?>
                </td>
                <td> <?= $person->getFirstName() ?></td>
                <?php    
                if ($_SESSION['user']->isSeePrivacyDataEnabled()) {
                ?>
                  <td> <?= $person->getAddress() ?></td>
                  <td><?= $person->getHomePhone() ?></td>
                  <td><?= $person->getCellPhone() ?></td>
                  <td><?= $person->getEmail() ?></td>
                  <td><?= date_format($person->getDateEntered(), SystemConfig::getValue('sDateFormatLong')) ?></td>
                  <td><?= date_format($person->getDateLastEdited(), SystemConfig::getValue('sDateFormatLong')) ?></td>
                <?php
                } else {
                ?>
                  <td> <?= gettext('Private Data') ?></td>
                  <td> <?= gettext('Private Data') ?></td>
                  <td> <?= gettext('Private Data') ?></td>
                  <td> <?= gettext('Private Data') ?></td>
                  <td> <?= gettext('Private Data') ?></td>
                  <td> <?= gettext('Private Data') ?></td>
                <?php
                }
              if (strtolower($sMode) == 'gdrp') { 
                $famID = $person->getFamId();
                $pledges  = PledgeQuery::Create()->findByFamId($famID);
              ?>
                  <td> <?= date_format($person->getDateDeactivated(), SystemConfig::getValue('sDateFormatLong')) ?></td>
                  <td><a class="btn btn-danger remove-property-btn <?= ($pledges->count() > 0)?"disabled":"" ?>" data-person_id="<?= $person->getId() ?>"><?= gettext("Remove") ?></a></td>
              <?php 
                } 
           }
        ?>
            </tr>
            </tbody>
        </table>
        
        <?php if (strtolower($sMode) == 'gdrp') { ?>        
           <a class="btn btn-danger <?= ($persons->count() == 0)?"disabled":"" ?>" id="remove-all"><?= gettext("Remove All") ?></a>
        <?php } ?>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
  $(document).ready(function () {
    $('#personlist').DataTable(window.CRM.plugin.dataTable);
  });
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/PersonList.js" ></script>

<?php
require 'Include/Footer.php';
?>
