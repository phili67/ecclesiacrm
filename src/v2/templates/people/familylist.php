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
<div class="d-flex justify-content-end mb-3">
  <a class="btn btn-success btn-sm px-3" role="button" href="<?= $sRootPath ?>/v2/people/family/editor">
    <span class="fas fa-plus mr-1" aria-hidden="true"></span><?= _('Add Family') ?>
  </a>
</div>
<?php
  }
?>

<?php
  if (strtolower($sMode) == 'gdrp') {
?>
<div class="alert alert-warning shadow-sm border-0">
  <strong><?= _('WARNING: Some families may have some records of donations and may NOT be deleted until these donations are associated with another person or Family.') ?></strong><br>
  <strong><?= _('WARNING: This action can not be undone and may have legal implications!') ?></strong>
</div>
<?php
  }
?>


<div class="card shadow-sm border-0">
  <div class="card-header border-0 bg-white py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:.75rem;">
      <div>
        <?php if ($sMode == 'Single') { ?>
          <h3 class="card-title mb-1"><i class="fas fa-male mr-2 text-primary"></i><?= _('People') ?></h3>
          <p class="text-muted small mb-0"><?= _('Browse households and family records from a single list.') ?></p>
        <?php } else { ?>
          <h3 class="card-title mb-1"><i class="fas fa-male mr-1 text-primary"></i><i class="fas fa-female mr-1 text-primary"></i><i class="fas fa-child mr-2 text-primary"></i><?= _('Families') ?></h3>
          <p class="text-muted small mb-0"><?= _('Browse, edit and manage family records from one place.') ?></p>
        <?php } ?>
      </div>
      <span class="badge badge-light border px-3 py-2"><?= $families->count() ?> <?= _('records') ?></span>
    </div>
    </div>
    <div class="card-body">
    <div class="table-responsive">
    <table id="families" class="table table-sm table-hover table-striped data-table align-middle mb-0" cellspacing="0" width="100%">
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
                <td>
                  <div class="d-flex align-items-center justify-content-between" style="gap:.75rem;">
                    <div class="font-weight-600"><?= $family->getName() ?></div>
                    <div class="btn-group btn-group-sm" role="group">
                      <a href='<?= $sRootPath ?>/v2/people/family/view/<?= $family->getId() ?>' class="btn btn-outline-secondary" title="<?= _('View') ?>">
                        <i class="fas fa-search-plus"></i>
                      </a>
                      <a href='<?= $sRootPath ?>/v2/people/family/editor/<?= $family->getId() ?>' class="btn btn-outline-primary" title="<?= _('Edit') ?>">
                        <i class="fas fa-pencil-alt"></i>
                      </a>
                    </div>
                  </div>
                </td>
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
                <td><button class="btn btn-sm btn-outline-danger remove-property-btn" data-family_id="<?= $family->getId() ?>"><i class="far fa-trash-alt mr-1"></i><?= _("Remove") ?></button></td>
              <?php
                }
           }
                ?>
            </tr>
            </tbody>
        </table>
        </div>
        <?php
          if (strtolower($sMode) == 'gdrp') {
        ?>
            <div class="d-flex justify-content-end mt-3">
                <a class="btn btn-sm btn-danger <?= ($families->count() == 0)?"disabled":"" ?>" id="remove-all"><i class="fas fa-trash-alt mr-1"></i><?= _("Remove All") ?></a>
            </div>
        <?php
          }
        ?>
    </div>
</div>


<script nonce="<?= $sCSPNonce ?>" >
  $(function() {
      window.CRM.familiesListTable = $('#families').DataTable(window.CRM.plugin.dataTable);

    function styleFamiliesWrapper() {
      const wrapper = $('#families_wrapper');
      if (!wrapper.length) {
        return;
      }

      wrapper.addClass('border rounded p-2 bg-white');
      wrapper.find('.dataTables_filter input').addClass('form-control form-control-sm').attr('placeholder', '<?= _('Search') ?>');
      wrapper.find('.dataTables_length select').addClass('form-control form-control-sm');
      wrapper.find('.dataTables_info').addClass('small text-muted');
    }

    styleFamiliesWrapper();
    $('#families').on('draw.dt', styleFamiliesWrapper);
  });
</script>

<script src="<?= $sRootPath ?>/skin/js/people/FamilyList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>


