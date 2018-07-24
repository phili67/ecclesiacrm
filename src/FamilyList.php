<?php
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\FamilyQuery;
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
   $newtime = $time->modify('-'.SystemConfig::getValue('sGdprExpirationDate').' year')->format('Y-m-d');
   
   $families = FamilyQuery::create()
        ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
            ->orderByName()
            ->find();
            
} else if (strtolower($sMode) == 'inactive') {
    $families = FamilyQuery::create()
        ->filterByDateDeactivated(null, Criteria::ISNOTNULL)
            ->orderByName()
            ->find();
} else {
    $sMode = 'Active';
    $families = FamilyQuery::create()
        ->filterByDateDeactivated(null)
            ->orderByName()
            ->find();
}

// Set the page title and include HTML header
$sPageTitle = gettext(ucfirst($sMode)) . ' ' . gettext('Family List');
require 'Include/Header.php'; ?>

<?php
  if ($_SESSION['user']->isAddRecordsEnabled()) {
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
            <?php foreach ($families as $family) {
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
              if (strtolower($sMode) == 'gdrp') { ?>
                  <td> <?= date_format($family->getDateDeactivated(), SystemConfig::getValue('sDateFormatLong')) ?></td>
                  <td><a class="btn btn-danger remove-property-btn" data-family_id="<?= $family->getId() ?>"><?= gettext("Remove") ?></a></td>
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