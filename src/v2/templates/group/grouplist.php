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

<?php
if (SessionUser::getUser()->isManageGroupsEnabled()) {
?>
    <div class="card card-primary card-outline">
        <div class="card-body">
            <form action="#" method="get" class="form">
                <label for="addNewGroup"><?= _('Add New Group') ?> :</label>
                <input class="form-control newGroup" name="groupName" id="groupName" style="width:100%">
                <br>
                <button type="button" class="btn btn-primary" id="addNewGroup"><?= _('Add New Group') ?></button>
            </form>
        </div>
    </div>
    <br>
<?php
}
?>


<div class="card card-warning">
    <div class="card-header border-1">
        <h3 class="card-title"><i class="fas fa-users"></i> <?= _('Groups') ?></h3>
        <div class="card-tools">
            <div style="text-align: center;">
                <div class="row">                    
                    <div class="col-md-6">
                        <select id="table-filter" class="form-control form-control-sm">
                            <option value=""><?= _("All") ?></option>
                            <option><?= _("Unassigned") ?></option>
                            <?php
                            foreach ($rsGroupTypes as $groupType) {
                            ?>
                                <option><?= $groupType->getOptionName() ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>
                            <?= _("count:") ?>
                        </label>
                        <span id="numberOfGroups"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered data-table" id="groupsTable" style="width:100%">
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