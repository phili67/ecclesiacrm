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

$groupRoles = $groupService->getGroupRoles($iGroupID);
$groupRoleCount = count($groupRoles);
$groupTypeLabel = _('Unassigned');

foreach ($rsGroupTypes as $groupType) {
  if ($theCurrentGroup->getListOptionId() == $groupType->getOptionId()) {
    $groupTypeLabel = $groupType->getOptionName();
    break;
  }
}
?>

<!-- GROUP SPECIFIC PROPERTIES MODAL-->
<div class="modal fade" id="groupSpecificPropertiesModal" tabindex="-1" role="dialog" aria-labelledby="deleteGroup" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 bg-light">
        <h4 class="modal-title" id="gsproperties-label"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <span class="text-danger"></span>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i><?= _('Close') ?></button>
        <button name="setgroupSpecificProperties" id="setgroupSpecificProperties" type="button" class="btn btn-danger btn-sm"></button>
      </div>
    </div>
  </div>
</div>
<!-- END GROUP SPECIFIC PROPERTIES MODAL-->

<div class="card card-outline card-primary shadow-sm mb-3">
  <div class="card-body py-3 px-4">
    <div class="d-flex flex-wrap align-items-start justify-content-between">
      <div class="pr-3">
        <h2 class="h4 mb-1">
          <i class="fas fa-users-cog mr-2 text-primary"></i><?= (($theCurrentGroup->isSundaySchool()) ? _("Special Group Settings : Sunday School Type") : _('Group Settings')) ?>
        </h2>
        <div class="text-muted small mb-0">
          <?= _('Update the group identity, category and special options, then manage member roles below.') ?>
        </div>
      </div>
      <div class="mt-2 mt-md-0 text-md-right">
        <span class="badge badge-primary mr-1 mb-1"><i class="fas fa-layer-group mr-1"></i><?= $groupTypeLabel ?></span>
        <span class="badge badge-info mr-1 mb-1"><i class="fas fa-user-tag mr-1"></i><?= $groupRoleCount . ' ' . _('Roles') ?></span>
        <span class="badge <?= $theCurrentGroup->getHasSpecialProps() ? 'badge-success' : 'badge-secondary' ?> mb-1"><i class="fas fa-sliders-h mr-1"></i><?= $theCurrentGroup->getHasSpecialProps() ? _('Properties enabled') : _('Properties disabled') ?></span>
      </div>
    </div>
  </div>
</div>

<div class="card card-outline card-primary shadow-sm mb-3">
  <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
    <div>
      <h3 class="card-title mb-1"><i class="fas fa-cog mr-2"></i><?= _('Edit Group') ?></h3>
      <div class="small text-muted"><?= _('Keep the basic information clear so the group is easy to identify from menus and reports.') ?></div>
    </div>
    <div class="card-tools ml-auto text-right mt-2 mt-md-0">
      <button type="submit" form="groupEditForm" id="saveGroupTop" class="btn btn-sm btn-primary" name="GroupSubmit">
        <i class="fas fa-save mr-1"></i><?= _('Save') ?>
      </button>
    </div>
  </div>
  <div class="card-body">
    <form name="groupEditForm" id="groupEditForm">
      <div class="alert alert-light border shadow-sm mb-4">
        <i class="fas fa-info-circle text-primary mr-2"></i>
        <?= _('Changes to the group are saved from this form. Role names are managed separately and update instantly when edited.') ?>
      </div>

      <div class="border rounded p-3 bg-light mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
          <div class="font-weight-bold"><?= _('Basic Information') ?></div>
          <div class="small text-muted"><?= _('Name and description shown across the application.') ?></div>
        </div>
        <div class="form-group mb-0">
          <div class="row">
            <div class="col-md-5">
              <label for="Name"><?= _('Name') ?>:</label>
              <input class="form-control form-control-sm" type="text" Name="Name" value="<?= htmlentities(stripslashes($theCurrentGroup->getName()), ENT_NOQUOTES, 'UTF-8') ?>" placeholder="<?= _('Enter the group name') ?>">
            </div>
            <div class="col-md-7">
              <label for="Description"><?= _('Description') ?>:</label>
              <textarea class="form-control form-control-sm" name="Description" cols="40" rows="4" placeholder="<?= _('Describe the purpose of this group') ?>"><?= htmlentities(stripslashes($theCurrentGroup->getDescription()), ENT_NOQUOTES, 'UTF-8') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="border rounded p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
          <div class="font-weight-bold"><?= _('Category and Navigation') ?></div>
          <div class="small text-muted"><?= _('This controls where the group appears in the left menu and how it is categorized.') ?></div>
        </div>
        <div class="row">
          <div class="col-lg-7 mb-3 mb-lg-0">
            <label for="GroupType"><?= _('Type of Group and Menu Category') ?>:</label>
            <select class="form-control form-control-sm" name="GroupType"
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
          <div class="col-lg-5">
            <div class="alert alert-light border mb-0 h-100">
              <div class="font-weight-bold mb-1"><?= _('Current behavior') ?></div>
              <div class="small text-muted mb-0"><?= _('Selecting a group type places this group in the related menu category and helps users find it faster.') ?></div>
            </div>
          </div>
        </div>
      </div>

      <?php
      // Show Role Clone fields only when adding new group
      if (strlen($iGroupID) < 1) {
      ?>
      <div class="border rounded p-3 mb-4 bg-light">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
          <div class="font-weight-bold"><?= _('Initial Member Roles') ?></div>
          <div class="small text-muted"><?= _('Reuse an existing group structure to get started faster.') ?></div>
        </div>
        <div class="row align-items-end">
          <div class="col-md-4 mb-3 mb-md-0">
            <div class="custom-control custom-checkbox mt-2">
              <input type="checkbox" class="custom-control-input" name="cloneGroupRole" id="cloneGroupRole" value="1">
              <label class="custom-control-label font-weight-bold" for="cloneGroupRole"><?= _('Clone roles from another group') ?></label>
            </div>
          </div>
          <div class="col-md-8" id="selectGroupIDDiv">
            <label for="seedGroupID"><?= _('Source group') ?>:</label>
            <select class="form-control form-control-sm" name="seedGroupID" id="seedGroupID">
              <option value="0"><?= _('Select a group') ?></option>
            <?php
            foreach ($rsGroupRoleSeed as $groupRoleTemplate) {
            ?>
              <option value="<?= $groupRoleTemplate['grp_ID'] ?>"><?= $groupRoleTemplate['grp_Name'] ?></option>
            <?php
            }
            ?>
            </select>
          </div>
        </div>
      </div>
      <?php } ?>

      <div class="border rounded p-3 mb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
          <div>
            <div class="font-weight-bold"><?= _('Group Specific Properties') ?></div>
            <div class="small text-muted"><?= _('Useful when you need custom answers, classifications or form-driven information for this group.') ?></div>
          </div>
          <span class="badge <?= $theCurrentGroup->getHasSpecialProps() ? 'badge-success' : 'badge-secondary' ?> mt-2 mt-md-0">
            <?= $theCurrentGroup->getHasSpecialProps() ? _('Enabled') : _('Disabled') ?>
          </span>
        </div>
        <?php
        if ($theCurrentGroup->getHasSpecialProps()) {
        ?>
          <div class="row align-items-center">
            <div class="col-lg-5 mb-2">
              <button type="button" id="disableGroupProps" class="btn btn-sm btn-outline-danger groupSpecificProperties"
                      data-toggle="tooltip"  data-placement="bottom" title="<?= _("Group-specific properties are useful to classify your groups or to make a sort of Doodle") ?>">
                  <i class="fas fa-ban mr-1"></i><?= _('Disable Group Specific Properties') ?></button>
            </div>
            <div class="col-lg-7 mb-2 text-lg-right">
              <a class="btn btn-sm btn-success" href="<?= $sRootPath ?>/v2/group/props/Form/editor/<?= $iGroupID ?>"
                  data-toggle="tooltip" data-placement="bottom" title="<?= _("Group-specific properties are useful to make a sort of doodle") ?>"><i class="fas fa-edit mr-1"></i><?= _('Edit Group-Specific Properties Form') ?></a>
            </div>
          </div>
        <?php
        } else {
        ?>
          <button type="button" id="enableGroupProps" class="btn btn-sm btn-outline-primary groupSpecificProperties"
                  data-toggle="tooltip" data-placement="bottom" title="<?= _("Group-specific properties are useful to classify your groups or to make a sort of Doodle") ?>"><i class="fas fa-plus-circle mr-1"></i><?= _('Enable Group Specific Properties') ?></button>
        <?php
        }
        ?>
      </div>
    </form>
  </div>
</div>
<div class="card card-outline card-info shadow-sm">
  <div class="card-header border-0 d-flex justify-content-between align-items-start flex-wrap">
    <div>
      <h3 class="card-title mb-1"><i class="fas fa-users-cog mr-2"></i><?= _('Group Roles') ?></h3>
      <div class="small text-muted"><?= _('Manage the labels used for memberships in this group, choose the default role and keep their order tidy.') ?></div>
    </div>
    <div class="card-tools ml-auto text-right mt-2 mt-md-0">
      <span class="badge badge-info"><i class="fas fa-user-tag mr-1"></i><?= $groupRoleCount . ' ' . _('Roles') ?></span>
    </div>
  </div>
  <div class="card-body">
    <div class="alert alert-info alert-dismissible">
      <i class="fas fa-info"></i>
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <strong><?= _('Group role name changes are saved as soon as the box loses focus')?></strong>
    </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover" id="groupRoleTable"></table>
      </div>
    <div class="mt-3 p-3 border rounded bg-light">
      <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
          <div class="font-weight-bold"><?= _('Add a new role') ?></div>
          <div class="small text-muted"><?= _('Create a role name that members and leaders will easily understand.') ?></div>
        </div>
      </div>
      <div class="d-flex flex-wrap align-items-end">
        <div class="form-group mb-2 mr-2" style="min-width:260px;flex:1;">
          <label for="newRole" class="mb-1 font-weight-bold text-muted"><?= _('New Role') ?>:</label>
          <input type="text" class="form-control form-control-sm" id="newRole" name="newRole" placeholder="<?= _('Enter a role name') ?>">
        </div>
        <div class="mb-2">
          <button type="button" id="addNewRole" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i><?= _('Add New Role') ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script nonce="<?= $CSPNonce ?>">
  //setup some document-global variables for later on in the javascript
  var defaultRoleID = <?= ($theCurrentGroup->getDefaultRole() ? $theCurrentGroup->getDefaultRole() : 1) ?>;
  var groupRoleData = <?= json_encode($groupRoles); ?>;
  window.CRM.roleCount = groupRoleData.length;
  window.CRM.groupID =<?= $iGroupID ?>;
</script>
<script src="<?= $sRootPath ?>/skin/js/group/GroupEditor.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
