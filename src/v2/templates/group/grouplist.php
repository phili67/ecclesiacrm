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

<div class="row">
    <div class="col-lg-3">
        <label>
            <?= _("Show type of group:") ?>
        </label>
    </div>
    <div class="col-lg-3">
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
    <div class="col-lg-5" align="right">
        <label>
            <?= _("Groups count:") ?>
        </label>
        <span id="numberOfGroups"></span>
    </div>
</div>

<br>

<div class="card">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-users"></i> <?= _('Groups') ?></h3>
    </div>
    <div class="card-body">
    <table class="table table-striped table-bordered data-table" id="groupsTable" style="width:100%">
    </table>
    <?php
    if (SessionUser::getUser()->isManageGroupsEnabled()) {
        ?>
        <br>
        <form action="#" method="get" class="form">
            <label for="addNewGroup"><?= _('Add New Group') ?> :</label>
            <input class="form-control newGroup" name="groupName" id="groupName" style="width:100%">
            <br>
            <button type="button" class="btn btn-primary" id="addNewGroup"><?= _('Add New Group') ?></button>
        </form>
        <?php
    }
    ?>
    </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/group/GroupList.js"></script>
<script nonce="<?= $CSPNonce ?>">
    $( document).ready(function() {
        var gS = localStorage.getItem("groupSelect");
        if (gS != null)
        {
            tf = document.getElementById("table-filter");
            tf.selectedIndex = gS;

            window.groupSelect = tf.value;
        }
    });

</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
