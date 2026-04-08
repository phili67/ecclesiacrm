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

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <h2 class="h4 mb-2 mb-md-0"><i class="fas fa-user-friends mr-2 text-primary"></i><?= _('Persons') ?></h2>
    <?php if ($bNotGDRP) { ?>
        <a class="btn btn-primary btn-sm" role="button" href="<?= $sRootPath ?>/v2/people/person/editor">
            <span class="fas fa-plus mr-1" aria-hidden="true"></span><?= _('Add New Person') ?>
        </a>
    <?php } ?>
</div>

<?php if (strtolower($sMode) == 'gdrp') { ?>
<div class="alert alert-warning d-flex align-items-start">
    <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
    <div>
        <div><strong><?= _('WARNING: Some persons may have some records of donations and may NOT be deleted until these donations are associated with another person or Family.') ?></strong></div>
        <div><strong><?= _('WARNING: This action can not be undone and may have legal implications!') ?></strong></div>
    </div>
</div>
<?php } ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i><?= _('Person Directory') ?></h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table id="personlist" class="table table-sm table-hover table-bordered data-table mb-0" cellspacing="0" width="100%">
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
              <td>
                <div class="d-flex align-items-center justify-content-between">
                  <span class="font-weight-bold"><?= $person->getLastName() ?></span>
                  <span class="btn-group btn-group-sm ml-2" role="group">
                    <a class="btn btn-outline-secondary" href='<?= $sRootPath ?>/v2/people/person/view/<?= $person->getId() ?>' title="<?= _('View') ?>">
                      <i class="fas fa-search-plus"></i>
                    </a>
                    <a class="btn btn-outline-primary" href='<?= $sRootPath ?>/v2/people/person/editor/<?= $person->getId() ?>' title="<?= _('Edit') ?>">
                      <i class="fas fa-pencil-alt"></i>
                    </a>
                  </span>
                </div>
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
                  <td> <?= (!is_null($person->getDateDeactivated())?date_format($person->getDateDeactivated(), SystemConfig::getValue('sDateFormatLong')):"") ?></td>
                  <td><a class="btn btn-sm btn-outline-danger remove-property-btn <?= ($pledges->count() > 0)?"disabled":"" ?>" data-person_id="<?= $person->getId() ?>"><i class="fas fa-user-times mr-1"></i><?= _("Remove") ?></a></td>
              <?php
                }
              ?>
              </tr>
               <?php
               }
        ?>
            </tbody>
        </table>
            </div>

        <?php if (strtolower($sMode) == 'gdrp') { ?>
               <div class="mt-3 text-right">
                 <a class="btn btn-sm btn-danger <?= ($persons->count() == 0)?"disabled":"" ?>" id="remove-all"><i class="fas fa-trash-alt mr-1"></i><?= _("Remove All") ?></a>
               </div>
        <?php } ?>
    </div>
</div>

<script nonce="<?= $sCSPNonce ?>" >
  $(function() {
      window.CRM.personsListTable = $('#personlist').DataTable(window.CRM.plugin.dataTable);
  });
</script>

<script src="<?= $sRootPath ?>/skin/js/people/PersonList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
