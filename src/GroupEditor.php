<?php
/*******************************************************************************
 *
 *  filename    : GroupEditor.php
 *  last change : 2003-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
 *                Copyright 2004-2012 Michael Wilt
 *                Copyright 2019 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\Service\GroupService;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


// Security: User must have Manage Groups permission
if (!SessionUser::getUser()->isManageGroupsEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

//Set the page title
$sPageTitle = _('Group Editor');
$groupService = new GroupService();
//Get the GroupID from the querystring.  Redirect to Menu if no groupID is present, since this is an edit-only form.
if (array_key_exists('GroupID', $_GET)) {
    $iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');
} else {
    RedirectUtils::Redirect('v2/group/list');
}

$theCurrentGroup = GroupQuery::create()
   ->findOneById($iGroupID);   //get this group from the group service.

$optionId = $theCurrentGroup->getListOptionId();

$rsGroupTypes = ListOptionQuery::create()
   ->filterById('3') // only the groups
   ->orderByOptionSequence()
   ->filterByOptionType(($theCurrentGroup->isSundaySchool())?'sunday_school':'normal')->find();     // Get Group Types for the drop-down

$rsGroupRoleSeed = GroupQuery::create()->filterByRoleListId(['min'=>0], $comparison)->find();         //Group Group Role List
require 'Include/Header.php';
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
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> <?= _('Close')?></button>
        <button name="setgroupSpecificProperties" id="setgroupSpecificProperties" type="button" class="btn btn-danger"></button>
      </div>
    </div>
  </div>
</div>
<!-- END GROUP SPECIFIC PROPERTIES MODAL-->

<div class="card">
  <div class="card-header">
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
                <input class="form-control" type="text" Name="Name" value="<?= htmlentities(stripslashes($theCurrentGroup->getName()), ENT_NOQUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <label for="Description"><?= _('Description') ?>:</label>
                <textarea  class="form-control" name="Description" cols="40" rows="5"><?= htmlentities(stripslashes($theCurrentGroup->getDescription()), ENT_NOQUOTES, 'UTF-8') ?></textarea></td>
              </div>
            </div>
            <br>
          </div>
          <div class="col-sm-6">
            <div class="row">
              <div class="col-sm-12">
                <label for="GroupType"><?= _('Type of Group and Menu Category') ?>:</label>
                <select class="form-control input-small" name="GroupType">
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
                  <select class="form-control input-small" name="seedGroupID" id="seedGroupID" >
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
                    <button type="button" id="disableGroupProps" class="btn btn-danger groupSpecificProperties"><?= _('Disable Group Specific Properties') ?></button><br/>
                  </div>
                  <div class="col-sm-4">
                    <a  class="btn btn-success" href="GroupPropsFormEditor.php?GroupID=<?= $iGroupID?>"><?= _('Edit Group-Specific Properties Form') ?></a>
                  </div>
                </div>
          <?php
            } else {
          ?>
                <?= _('Disabled') ?><br/>
                <button type="button" id="enableGroupProps" class="btn btn-danger groupSpecificProperties"><?= _('Enable Group Specific Properties') ?></button>&nbsp;
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
  <div class="card-header">
    <h3 class="card-title"><?= _('Group Roles') ?>:</h3>
  </div>
  <div class="card-body">
    <div class="alert alert-info alert-dismissable">
      <i class="fa fa-info"></i>
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <strong><?= _('Group role name changes are saved as soon as the box loses focus')?></strong>
    </div>
      <div class="table-responsive">
        <table class="table" class="table" id="groupRoleTable"></table>
      </div>
    <label for="newRole"><?= _('New Role')?>: </label><input type="text" class="form-control" id="newRole" name="newRole">
    <br>
    <button type="button" id="addNewRole" class="btn btn-primary"><?= _('Add New Role')?></button>
  </div>
</div>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  //setup some document-global variables for later on in the javascript
  var defaultRoleID = <?= ($theCurrentGroup->getDefaultRole() ? $theCurrentGroup->getDefaultRole() : 1) ?>;
  var groupRoleData = <?= json_encode($groupService->getGroupRoles($iGroupID)); ?>;
  window.CRM.roleCount = groupRoleData.length;
  window.CRM.groupID =<?= $iGroupID ?>;
</script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/group/GroupEditor.js"></script>

<?php require 'Include/Footer.php' ?>
