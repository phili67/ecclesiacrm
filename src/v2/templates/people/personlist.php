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
  if ( $bNotGDRP ) {
?>
<div class="pull-right">
  <a class="btn btn-success" role="button" href="<?= $sRootPath ?>/PersonEditor.php">
    <span class="fas fa-plus" aria-hidden="true"></span><?= _('Add New Person') ?>
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
    <strong> <?= _('WARNING: Some persons may have some records of donations and may NOT be deleted until these donations are associated with another person or Family.') ?> </strong><br>
    <strong> <?= _('WARNING: This action can not be undone and may have legal implications!') ?> </strong>
</div>
<?php
  }
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user"></i> <?= _('Persons') ?></h3>
    </div>
    <div class="card-body">
        <table id="personlist" class="table table-striped table-bordered data-table" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th><?= _('First Name') ?></th>
                <th><?= _('Address') ?></th>
                <th><?= _('Home Phone') ?></th>
                <th><?= _('Cell Phone') ?></th>
                <th><?= _('email') ?></th>
                <th><?= _('Created') ?></th>
                <th><?= _('Edited') ?></th>
            <?php if (strtolower($sMode) == 'gdrp') { ?>
                <th><?= _('Deactivation date') ?></th>
                <th><?= _('Remove') ?></th>
            <?php } ?>
            </tr>
            </thead>
            <tbody>

            <!--Populate the table with Person details -->
          <?php
            foreach ($persons as $person) {
          ?>
            <tr>
                <td><a href='<?= $sRootPath ?>/PersonView.php?PersonID=<?= $person->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fas fa-square fa-stack-2x"></i>
                            <i class="fas fa-search-plus fa-stack-1x fa-inverse"></i>
                        </span>
                    </a>
                    <a href='<?= $sRootPath ?>/PersonEditor.php?PersonID=<?= $person->getId() ?>'>
                        <span class="fa-stack">
                            <i class="fas fa-square fa-stack-2x"></i>
                            <i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>
                        </span>
                    </a><?= $person->getLastName() ?>
                </td>
                <td> <?= $person->getFirstName() ?></td>
                <?php
                if (SessionUser::getUser()->isSeePrivacyDataEnabled()) {
                ?>
                  <td> <?= $person->getAddress() ?></td>
                  <td><?= $person->getHomePhone() ?></td>
                  <td><?= $person->getCellPhone() ?></td>
                  <td><?= $person->getEmail() ?></td>
                    <td><?= (!is_null($person->getDateEntered()))?date_format($person->getDateEntered(), SystemConfig::getValue('sDateFormatLong')):'' ?></td>
                    <td><?= (!is_null($person->getDateLastEdited()))?date_format($person->getDateLastEdited(), SystemConfig::getValue('sDateFormatLong')):'' ?></td>
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
              if (strtolower($sMode) == 'gdrp') {
                $famID = $person->getFamId();
                $pledges  = PledgeQuery::Create()->findByFamId($famID);
              ?>
                  <td> <?= date_format($person->getDateDeactivated(), SystemConfig::getValue('sDateFormatLong')) ?></td>
                  <td><a class="btn btn-danger remove-property-btn <?= ($pledges->count() > 0)?"disabled":"" ?>" data-person_id="<?= $person->getId() ?>"><?= _("Remove") ?></a></td>
              <?php
                }
           }
        ?>
            </tr>
            </tbody>
        </table>

        <?php if (strtolower($sMode) == 'gdrp') { ?>
           <a class="btn btn-danger <?= ($persons->count() == 0)?"disabled":"" ?>" id="remove-all"><?= _("Remove All") ?></a>
        <?php } ?>
    </div>
</div>

<script nonce="<?= $sCSPNonce ?>" >
  $(document).ready(function () {
    $('#personlist').DataTable(window.CRM.plugin.dataTable);
  });
</script>

<script src="<?= $sRootPath ?>/skin/js/people/PersonList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
