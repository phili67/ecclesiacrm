<?php

/*******************************************************************************
 *
 *  filename    : grouplist.php
 *  last change : 2019-06-23
 *  description : manage the group list
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorizaion
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-outline card-warning shadow-sm">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i><?= _('Groups') ?></h3>
        <div class="card-tools d-flex align-items-center gap-2">
            <select id="table-filter" class="form-control form-control-sm" style="width:160px;">
                <option value=""><?= _("All") ?></option>
                <option><?= _("Unassigned") ?></option>
                <?php foreach ($rsGroupTypes as $groupType): ?>
                    <option><?= $groupType->getOptionName() ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted text-nowrap ml-2">
                <?= _("Count:") ?> <span id="numberOfGroups" class="font-weight-bold"></span>
            </small>
            <?php if (SessionUser::getUser()->isManageGroupsEnabled()): ?>
            <button type="button" class="btn btn-success btn-sm ml-2" id="addNewGroup">
                <i class="fas fa-plus mr-1"></i><?= _('Add New') ?>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-sm data-table" id="groupsTable" style="width:100%">
        </table>
    </div>
</div>


<script src="<?= $sRootPath ?>/skin/js/group/GroupList.js"></script>
<script nonce="<?= $CSPNonce ?>">
    $(function() {
        var gS = localStorage.getItem("groupSelect");
        if (gS != null) {
            tf = document.getElementById("table-filter");
            tf.selectedIndex = gS;

            window.groupSelect = tf.value;
        }
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>