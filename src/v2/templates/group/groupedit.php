<?php

/*******************************************************************************
 *
 *  filename    : groupedit.php
 *  last change : 2023-05-21
 *  description : edit a group
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2023 Philippe Logel all right reserved not MIT licence
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<!-- GROUP SPECIFIC PROPERTIES MODAL-->
<div class="modal fade" id="groupSpecificPropertiesModal" tabindex="-1" role="dialog" aria-labelledby="deleteGroup" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="gsproperties-label"></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <span style="color: red"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> <?= _('Close')?></button>
        <button name="setgroupSpecificProperties" id="setgroupSpecificProperties" type="button" class="btn btn-danger"></button>
      </div>
    </div>
  </div>
</div>
<!-- END GROUP SPECIFIC PROPERTIES MODAL-->

<div class="card">
  <div class="card-header  border-1">
    <h3 class="card-title"><?= (($theCurrentGroup->isSundaySchool())?_("Special Group Settings : Sunday School Type"):_('Group Settings')) ?></h3>
  </div>
  <div class="card-body">
    <form name="groupEditForm" id="groupEditForm">
      <div class="form-group">
        <div class="row">
          <div class="col-sm-6">
            <div class="row">
              <div class="col-sm-12">
                <label for="Name"><?= _('Name') ?>:</label>
                <input class= "form-control form-control-sm" type="text" Name="Name" value="<?= htmlentities(stripslashes($theCurrentGroup->getName()), ENT_NOQUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <label for="Description"><?= _('Description') ?>:</label>
                <textarea  class= "form-control form-control-sm" name="Description" cols="40" rows="5"><?= htmlentities(stripslashes($theCurrentGroup->getDescription()), ENT_NOQUOTES, 'UTF-8') ?></textarea></td>
              </div>
            </div>
            <br>
          </div>
          <div class="col-sm-6">
            <div class="row">
              <div class="col-sm-12">
                <label for="GroupType"><?= _('Type of Group and Menu Category') ?>:</label>
                <select class="form-control form-control-small" name="GroupType"
                        data-toggle="tooltip"  data-placement="bottom" title="<?= _("This will include the group in a menu item in the left sidebar") ?>">
                  <option value="0"><?= _('Unassigned') ?></option>
                  <option value="0">-----------------------</option>
                <?php
                  foreach ($rsGroupTypes as $groupType) {
                ?>
                  <option value="<?= $groupType->getOptionId() ?>" <?= ($theCurrentGroup->getListOptionId() == $groupType->getOptionId())?' selected':'' ?>><?= $groupType->getOptionName() ?></option>
                <?php
                  }
                ?>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3">
                <?php
                // Show Role Clone fields only when adding new group
                if (strlen($iGroupID) < 1) {
                    ?>
                  <b><?= _('Group Member Roles') ?>:</b>

                  <?= _('Clone roles') ?>:
                  <input type="checkbox" name="cloneGroupRole" id="cloneGroupRole" value="1">
                </div>
                <div class="col-sm-3" id="selectGroupIDDiv">
                  <?= _('from group') ?>:
                  <select class="form-control form-control-small" name="seedGroupID" id="seedGroupID" >
                    <option value="0"><?php _('Select a group'); ?></option>

                  <?php
                  foreach ($rsGroupRoleSeed as $groupRoleTemplate) {
                  ?>
                    <option value="<?= $groupRoleTemplate['grp_ID'] ?>"><?= $groupRoleTemplate['grp_Name'] ?></option>
                  <?php
                  }
                  ?>
                  </select>
              <?php
                }
              ?>
              </div>
            </div>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-sm-12">
            <label for="UseGroupProps"><?= _('Group Specific Properties') ?>:</label>

            <?php
            if ($theCurrentGroup->getHasSpecialProps()) {
            ?>
                <?= _('Enabled') ?><br/>
                <div class="row">
                  <div class="col-sm-4">
                    <button type="button" id="disableGroupProps" class="btn btn-danger groupSpecificProperties"
                            data-toggle="tooltip"  data-placement="bottom" title="<?= _("Group-specific properties are useful to classify your groups or to make a sort of Doodle") ?>">
                        <?= _('Disable Group Specific Properties') ?></button><br/>
                  </div>
                  <div class="col-sm-4">
                    <a  class="btn btn-success" href="<?= $sRootPath ?>/GroupPropsFormEditor.php?GroupID=<?= $iGroupID?>"
                        data-toggle="tooltip"  data-placement="bottom" title="<?= _("Group-specific properties are useful to make a sort of doodle") ?>"><?= _('Edit Group-Specific Properties Form') ?></a>
                  </div>
                </div>
          <?php
            } else {
          ?>
                <?= _('Disabled') ?><br/>
                <button type="button" id="enableGroupProps" class="btn btn-danger groupSpecificProperties"
                        data-toggle="tooltip"  data-placement="bottom" title="<?= _("Group-specific properties are useful to classify your groups or to make a sort of Doodle") ?>"><?= _('Enable Group Specific Properties') ?></button>&nbsp;
          <?php
            }
          ?>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <br>
            <input type="submit" id="saveGroup" class="btn btn-primary" value="<?= _('Save') ?>" Name="GroupSubmit">
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-header  border-1">
    <h3 class="card-title"><?= _('Group Roles') ?>:</h3>
  </div>
  <div class="card-body">
    <div class="alert alert-info alert-dismissable">
      <i class="fas fa-info"></i>
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <strong><?= _('Group role name changes are saved as soon as the box loses focus')?></strong>
    </div>
      <div class="table-responsive">
        <table class="table" class="table" id="groupRoleTable"></table>
      </div>
    <label for="newRole"><?= _('New Role')?>: </label><input type="text" class= "form-control form-control-sm" id="newRole" name="newRole">
    <br>
    <button type="button" id="addNewRole" class="btn btn-primary"><?= _('Add New Role')?></button>
  </div>
</div>

<script nonce="<?= $CSPNonce ?>">
  //setup some document-global variables for later on in the javascript
  var defaultRoleID = <?= ($theCurrentGroup->getDefaultRole() ? $theCurrentGroup->getDefaultRole() : 1) ?>;
  var groupRoleData = <?= json_encode($groupService->getGroupRoles($iGroupID)); ?>;
  window.CRM.roleCount = groupRoleData.length;
  window.CRM.groupID =<?= $iGroupID ?>;
</script>
<script src="<?= $sRootPath ?>/skin/js/group/GroupEditor.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
