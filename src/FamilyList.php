<?php
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PledgeQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use Propel\Runtime\Propel;


$sMode = 'Active';
// Filter received user input as needed
if (isset($_GET['mode'])) {
    $sMode = InputUtils::LegacyFilterInput($_GET['mode']);
}

if (strtolower($sMode) == 'gdrp') {
  if (!SessionUser::getUser()->isGdrpDpoEnabled()) {
    RedirectUtils::Redirect("Menu.php");
    exit;
  }

  $time = new DateTime('now');
  $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');
    
  $subQuery = FamilyQuery::create()
      ->withColumn('Family.Id','FamId')
      ->leftJoinPerson()
        ->withColumn('COUNT(Person.Id)','cnt')
      ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
      ->groupById();//groupBy('Family.Id');
      
  $families = FamilyQuery::create()
       ->addSelectQuery($subQuery, 'res')
       ->where('res.cnt>1 AND Family.Id=res.FamId')// only real family with more than one member will be showed here
       ->find();

} else if (strtolower($sMode) == 'inactive') {
  if (!SessionUser::getUser()->isEditRecordsEnabled()) {
    RedirectUtils::Redirect("Menu.php");
    exit;
  }

  if (SystemConfig::getValue('bGDPR')) {
    $time = new DateTime('now');
    $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

    $families = FamilyQuery::create()
              ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// GDRP, when a person is completely deactivated, we only can see the person who are over a certain date
              ->orderByName()
              ->find();

    $subQuery = FamilyQuery::create()
      ->withColumn('Family.Id','FamId')
      ->leftJoinPerson()
        ->withColumn('COUNT(Person.Id)','cnt')
      ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)
      ->groupById();//groupBy('Family.Id');
      
    $families = FamilyQuery::create()
       ->addSelectQuery($subQuery, 'res')
       ->where('res.cnt>1 AND Family.Id=res.FamId')// only real family with more than one member will be showed here
       ->find();
  } else {
    $time = new DateTime('now');
    
    $families = FamilyQuery::create()
              ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)// GDRP, when a person is completely deactivated, we only can see the person who are over a certain date
              ->orderByName()
              ->find();
              
    $subQuery = FamilyQuery::create()
      ->withColumn('Family.Id','FamId')
      ->leftJoinPerson()
        ->withColumn('COUNT(Person.Id)','cnt')
      ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)
      ->groupById();//groupBy('Family.Id');
      
    $families = FamilyQuery::create()
       ->addSelectQuery($subQuery, 'res')
       ->where('res.cnt>1 AND Family.Id=res.FamId')// only real family with more than one member will be showed here
       ->find();

  }
} else if (strtolower($sMode) == 'empty') {
  if (!SessionUser::getUser()->isEditRecordsEnabled()) {
    RedirectUtils::Redirect("Menu.php");
    exit;
  }

  $subQuery = FamilyQuery::create()
      ->withColumn('Family.Id','FamId')
      ->leftJoinPerson()
        ->withColumn('COUNT(Person.Id)','cnt')
      ->filterByDateDeactivated(NULL)
      ->groupById();//groupBy('Family.Id');
      
  $families = FamilyQuery::create()
       ->addSelectQuery($subQuery, 'res')
       ->where('res.cnt=0 AND Family.Id=res.FamId') // The emptied addresses
       ->find();

} else {
  if (!SessionUser::getUser()->isEditRecordsEnabled()) {
    RedirectUtils::Redirect("Menu.php");
    exit;
  }
  
  $sMode = 'Active';
  $subQuery = FamilyQuery::create()
      ->withColumn('Family.Id','FamId')
      ->leftJoinPerson()
        ->withColumn('COUNT(Person.Id)','cnt')
      ->filterByDateDeactivated(NULL)
      ->groupById();//groupBy('Family.Id');
      
  $families = FamilyQuery::create()
       ->addSelectQuery($subQuery, 'res')
       ->where('res.cnt>1 AND Family.Id=res.FamId') // only real family with more than one member will be showed here
       ->find();
}

// Set the page title and include HTML header
$sPageTitle = _(ucfirst($sMode)) . ' : ' . ((strtolower($sMode) == 'empty')?_('Addresses'):_('Family List'));
require 'Include/Header.php'; ?>

<?php
  if (SessionUser::getUser()->isAddRecordsEnabled() && strtolower($sMode) != 'gdrp' && strtolower($sMode) != 'empty') {
?>
<div class="pull-right">
  <a class="btn btn-success" role="button" href="FamilyEditor.php"> <span class="fa fa-plus"
                                                                          aria-hidden="true"></span><?= _('Add Family') ?>
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
    <strong> <?= _('WARNING: Some families may have some records of donations and may NOT be deleted until these donations are associated with another person or Family.') ?> </strong><br>
    <strong> <?= _('WARNING: This action can not be undone and may have legal implications!') ?> </strong>
</div>
<?php 
  } 
?>


<div class="box">
    <div class="box-body">
        <table id="families" class="table table-striped table-bordered data-table" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th><?= _('Address') ?></th>
                <th><?= _('Home Phone') ?></th>
                <th><?= _('Cell Phone') ?></th>
                <th><?= _('email') ?></th>
                <th><?= _('Created') ?></th>
                <th><?= _('Edited') ?></th>
            <?php 
              if (strtolower($sMode) == 'gdrp') { ?>
                <th><?= _('Deactivation date') ?></th>
            <?php 
              } 
              if (SessionUser::getUser()->isDeleteRecordsEnabled()) {
            ?>
                <th><?= _('Remove') ?></th>
            <?php
              }
            ?>
            </tr>
            </thead>
            <tbody>

            <!--Populate the table with family details -->
            <?php 
              foreach ($families as $family) {
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
                if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
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
                  <td> <?= _('Private Data') ?></td>
                  <td> <?= _('Private Data') ?></td>
                  <td> <?= _('Private Data') ?></td>
                  <td> <?= _('Private Data') ?></td>
                  <td> <?= _('Private Data') ?></td>
                  <td> <?= _('Private Data') ?></td>
                <?php
                }
                $pledges  = PledgeQuery::Create()->findByFamId($family->getId());
                
                if (strtolower($sMode) == 'gdrp') { 
              ?>
                  <td> <?= date_format($family->getDateDeactivated(), SystemConfig::getValue('sDateFormatLong')) ?></td>
              <?php 
                } 
                if (SessionUser::getUser()->isDeleteRecordsEnabled()) {
              ?>
                <td><button class="btn btn-danger remove-property-btn" data-family_id="<?= $family->getId() ?>" ><?= _("Remove") ?></button></td>
              <?php
                }
           }
                ?>
            </tr>
            </tbody>
        </table>
        <?php if (strtolower($sMode) == 'gdrp') { ?>
          <a class="btn btn-danger <?= ($families->count() == 0)?"disabled":"" ?>" id="remove-all"><?= _("Remove All") ?></a>
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

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/FamilyList.js" ></script>