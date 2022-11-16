<?php
/*******************************************************************************
 *
 *  filename    : familylist.php
 *  last change : 2019-06-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorization
 *
 ******************************************************************************/
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\dto\SystemConfig;

require $sRootDocument . '/Include/Header.php';
?>

<?php
  if ($bNotGDRPNotEmpty) {
?>
<div class="pull-right">
  <a class="btn btn-success" role="button" href="<?= $sRootPath ?>/FamilyEditor.php"> <span class="fas fa-plus"
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


<div class="card">
    <div class="card-header  border-1">
        <?php if ($sMode == 'Single') { ?>
            <h3 class="card-title"><i class="fas fa-male"></i> <?= _('People') ?></h3>
        <?php } else { ?>
            <h3 class="card-title"><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i> <?= _('Families') ?></h3>
        <?php } ?>
    </div>
    <div class="card-body">
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
                <td><a href='<?= $sRootPath ?>/FamilyView.php?FamilyID=<?= $family->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fas fa-square fa-stack-2x"></i>
                            <i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>
                        </span>
                    </a>
                    <a href='<?= $sRootPath ?>/FamilyEditor.php?FamilyID=<?= $family->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fas fa-square fa-stack-2x"></i>
                            <i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>
                        </span>
                    </a><?= $family->getName() ?></td>
                <?php
                if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                ?>
                  <td> <?= $family->getAddress() ?></td>
                  <td><?= $family->getHomePhone() ?></td>
                  <td><?= $family->getCellPhone() ?></td>
                  <td><?= $family->getEmail() ?></td>
                  <td><?= (!is_null($family->getDateEntered()) && !is_null(SystemConfig::getValue('sDateFormatLong')) )?date_format($family->getDateEntered(), SystemConfig::getValue('sDateFormatLong')):"" ?></td>
                  <td><?= (!is_null($family->getDateLastEdited()) && !is_null(SystemConfig::getValue('sDateFormatLong')) )?date_format($family->getDateLastEdited(), SystemConfig::getValue('sDateFormatLong')):"" ?></td>
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
        <?php
          if (strtolower($sMode) == 'gdrp') {
        ?>
            <a class="btn btn-danger <?= ($families->count() == 0)?"disabled":"" ?>" id="remove-all"><?= _("Remove All") ?></a>
        <?php
          }
        ?>
    </div>
</div>


<script nonce="<?= $sCSPNonce ?>" >
  $(document).ready(function () {
      window.CRM.familiesListTable = $('#families').DataTable(window.CRM.plugin.dataTable);
  });
</script>


<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/people/FamilyList.js" ></script>
