<?php
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\FamilyQuery;
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
   
   $families = FamilyQuery::create()
        ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
        ->orderByName()
        ->find();
            
} else if (strtolower($sMode) == 'inactive') {
  if (!$_SESSION['user']->isEditRecordsEnabled()) {
    Redirect("Menu.php");
    exit;
  }

  if (SystemConfig::getValue('bGDPR')) {
    $time = new DateTime('now');
    $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

    $families = FamilyQuery::create()
              ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// RGPD, when a person is completely deactivated, we only can see the person who are over a certain date
              ->orderByName()
              ->find();
  } else {
    $time = new DateTime('now');
    
     $families = FamilyQuery::create()
              ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)// RGPD, when a person is completely deactivated, we only can see the person who are over a certain date
              ->orderByName()
              ->find();
  }
} else {
  if (!$_SESSION['user']->isEditRecordsEnabled()) {
    Redirect("Menu.php");
    exit;
  }
  
    $sMode = 'Active';
    $families = FamilyQuery::create()
        ->filterByDateDeactivated(null)
        ->orderByName()
        ->find();
}

// Set the page title and include HTML header
$sPageTitle = gettext(ucfirst($sMode)) . ' : ' . gettext('Family List');
require 'Include/Header.php'; ?>

<?php
  if ($_SESSION['user']->isAddRecordsEnabled() && strtolower($sMode) != 'gdrp' ) {
?>
<div class="pull-right">
  <a class="btn btn-success" role="button" href="FamilyEditor.php"> <span class="fa fa-plus"
                                                                          aria-hidden="true"></span><?= gettext('Add Family') ?>
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
    <strong> <?= gettext('WARNING: Some families may have some records of donations and may NOT be deleted until these donations are associated with another person or Family.') ?> </strong><br>
    <strong> <?= gettext('WARNING: This action can not be undone and may have legal implications!') ?> </strong>
</div>
<?php 
  } 
?>


<div class="box">
    <div class="box-body">
        <table id="families" class="table table-striped table-bordered data-table" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= gettext('Name') ?></th>
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

            <!--Populate the table with family details -->
            <?php 
              foreach ($families as $family) {
                if ($family->getPeople()->count() <= 1)// only real family with more than one member will be showed
                  continue;
            ?>
            <tr>
                <td><a href='FamilyView.php?FamilyID=<?= $family->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fa fa-square fa-stack-2x"></i>
                            <i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>
                        </span>
                    </a>
                    <a href='FamilyEditor.php?FamilyID=<?= $family->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fa fa-square fa-stack-2x"></i>
                            <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                        </span>
                    </a><?= $family->getName() ?></td>
                <?php    
                if ($_SESSION['user']->isSeePrivacyDataEnabled()) {
                ?>
                  <td> <?= $family->getAddress() ?></td>
                  <td><?= $family->getHomePhone() ?></td>
                  <td><?= $family->getCellPhone() ?></td>
                  <td><?= $family->getEmail() ?></td>
                  <td><?= date_format($family->getDateEntered(), SystemConfig::getValue('sDateFormatLong')) ?></td>
                  <td><?= date_format($family->getDateLastEdited(), SystemConfig::getValue('sDateFormatLong')) ?></td>
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
                $pledges  = PledgeQuery::Create()->findByFamId($family->getId());
              ?>
                  <td> <?= date_format($family->getDateDeactivated(), SystemConfig::getValue('sDateFormatLong')) ?></td>
                  <td><button class="btn btn-danger remove-property-btn" data-family_id="<?= $family->getId() ?>" <?= ($pledges->count() > 0)?"disabled":"" ?>><?= gettext("Remove") ?></button></td>
              <?php 
                } 
           }
                ?>
            </tr>
            </tbody>
        </table>
        <?php if (strtolower($sMode) == 'gdrp') { ?>
          <a class="btn btn-danger <?= ($families->count() == 0)?"disabled":"" ?>" id="remove-all"><?= gettext("Remove All") ?></a>
        <?php } ?>
    </div>
</div>


<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
  $(document).ready(function () {
    $('#families').DataTable(window.CRM.plugin.dataTable);
  });
</script>


<?php
require 'Include/Footer.php';
?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/FamilyList.js" ></script>