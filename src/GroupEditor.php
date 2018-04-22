<?php
/*******************************************************************************
 *
 *  filename    : GroupEditor.php
 *  last change : 2003-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
 *                Copyright 2004-2012 Michael Wilt
 *                Copyright 2018 Philippe Logel
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
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Property;
use EcclesiaCRM\Record2propertyR2pQuery;


// Security: User must have Manage Groups permission
if (!$_SESSION['user']->isManageGroupsEnabled()) {
    Redirect('Menu.php');
    exit;
}

//Set the page title
$sPageTitle = gettext('Group Editor');
$groupService = new GroupService();
//Get the GroupID from the querystring.  Redirect to Menu if no groupID is present, since this is an edit-only form.
if (array_key_exists('GroupID', $_GET)) {
    $iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');
} else {
    Redirect('GroupList.php');
}

$thisGroup = GroupQuery::create()->findOneById($iGroupID);   //get this group from the group service.
$rsGroupTypes = ListOptionQuery::create()->filterById('3')->find();     // Get Group Types for the drop-down
$rsGroupRoleSeed = GroupQuery::create()->filterByRoleListId(['min'=>0], $comparison)->find();     //Group Group Role List
require 'Include/Header.php';
?>
<!-- GROUP SPECIFIC PROPERTIES MODAL-->
<div class="modal fade" id="groupSpecificPropertiesModal" tabindex="-1" role="dialog" aria-labelledby="deleteGroup" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="gsproperties-label"></h4>
      </div>
      <div class="modal-body">
        <span style="color: red"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= gettext('Close')?></button>
        <button name="setgroupSpecificProperties" id="setgroupSpecificProperties" type="button" class="btn btn-danger"></button>
      </div>
    </div>
  </div>
</div>
<!-- END GROUP SPECIFIC PROPERTIES MODAL-->

<div class="box">
  <div class="box-header">
    <h3 class="box-title"><?= (($thisGroup->isSundaySchool())?gettext("Special Group Settings : Sunday School Type"):gettext('Group Settings')) ?></h3>
  </div>
  <div class="box-body">
    <form name="groupEditForm" id="groupEditForm">
      <div class="form-group">
        <div class="row">
          <div class="col-sm-6">
            <div class="row">
              <div class="col-sm-12">
                <label for="Name"><?= gettext('Name') ?>:</label>
                <input class="form-control" type="text" Name="Name" value="<?= htmlentities(stripslashes($thisGroup->getName()), ENT_NOQUOTES, 'UTF-8') ?>">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <label for="Description"><?= gettext('Description') ?>:</label>
                <textarea  class="form-control" name="Description" cols="40" rows="5"><?= htmlentities(stripslashes($thisGroup->getDescription()), ENT_NOQUOTES, 'UTF-8') ?></textarea></td>
              </div>
            </div>
            <br>
          </div>
          <div class="col-sm-6">
            <div class="row">
              <div class="col-sm-12">
                <label for="GroupType"><?= gettext('Type of Group and Menu Category') ?>:</label>
                <?php 
                    if ($thisGroup->isSundaySchool()) {
                        $hide = "style=\"display:none;\"";
                    } else {
                        $hide = "";
                    }
                ?>
                <select class="form-control input-small" name="GroupType" <?= $hide ?>>
                  <option value="0"><?= gettext('Unassigned') ?></option>
                  <option value="0">-----------------------</option>
                  <?php
                  foreach ($rsGroupTypes as $groupType) {
                      echo '<option value="'.$groupType->getOptionId().'"';
                      if ($thisGroup->getType() == $groupType->getOptionId()) {
                          echo ' selected';
                      }
                      echo '>'.$groupType->getOptionName().'</option>';
                  } ?>              
                </select>
                <?php
                  if ($thisGroup->isSundaySchool()) {
                    ?>
                  <b><?= gettext("Sunday School") ?></b>
                  <p><?= gettext("Sunday School group can't be modified, only in this two cases :")?></p>
                  <ul>
                    <li>
                      <?= gettext("You can create/delete sunday school group. ")?>
                    </li>
                    <li>
                      <?= gettext("Add new roles, but not modify or rename the Student and the Teacher roles.")?>
                    </li>
                  </ul>
                <?php
                   //Get all the Menu properties from the group ID

                  if ($_SESSION['user']->isManageGroupsEnabled()) {
                        //Get the Properties assigned to this Group
                        $sSQL = "SELECT pro_Name, pro_ID, pro_Prompt, r2p_Value, prt_Name, pro_prt_ID
                                FROM record2property_r2p
                                LEFT JOIN property_pro ON pro_ID = r2p_pro_ID
                                LEFT JOIN propertytype_prt ON propertytype_prt.prt_ID = property_pro.pro_prt_ID
                                WHERE pro_Class = 'm' AND r2p_record_ID = ".$iGroupID.
                                ' ORDER BY prt_Name, pro_Name';
                        $rsAssignedProperties = RunQuery($sSQL);
                        
                        // we call the properties
                        $properties = PropertyQuery::Create()
                                      ->filterByProClass('m')
                                      ->orderByProName()
                                      ->find();
                                      
                        // we search the menu property to get the R2pProId
                        $groupProperty = Record2propertyR2pQuery::create()
                          ->filterByR2pRecordId($iGroupID)
                          ->filterByR2pValue('Menu')
                          ->findOne();

                        $oldPropertyIDAssignement = (!empty($groupProperty))?$groupProperty->getR2pProId():0;
                 ?>
                    <input type="hidden" id="grouID" value="<?= $iGroupID ?>" />
                    <input type="hidden" id="oldPropertyIDAssignement" value="<?= $oldPropertyIDAssignement ?>" />
                    <p>
                    <label for="GroupType"><?= gettext('Assign a New Menu Sunday School Category')?> :</label>
                      <div class="row">
                        <div class="col-sm-8">
                        <select class="form-control input-small" id="PropertyIDAssignement" >
                           <option value="0"><?= gettext('Unassigned') ?></option>
                            <option value="0">-----------------------</option>
                            <?php
                            foreach ($properties as $property) {
                                //If the property doesn't already exist for this Person, write the <OPTION> tag
                                $selected = (!empty($groupProperty) && ($property->getProId() == $oldPropertyIDAssignement))?"selected":"";
                            ?>
                                    <option value="<?= $property->getProId() ?>" <?= $selected ?>> <?= $property->getProName() ?></option>
                            <?php
                            }
                        
                            ?>

                            </select>
                        </div>
                        <div class="col-sm-4">
                            <input type="button" id="menuAssignement" class="btn btn-success" value="<?= gettext('Assign') ?>" name="Submit">
                        </div>
                      </div>
                    </p>
                    <?php
                    } else {
                        echo '<br><br><br>';
                    }
                  } ?>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3">
                <?php
                // Show Role Clone fields only when adding new group
                if (strlen($iGroupID) < 1) {
                    ?>
                  <b><?= gettext('Group Member Roles') ?>:</b>

                  <?= gettext('Clone roles') ?>:
                  <input type="checkbox" name="cloneGroupRole" id="cloneGroupRole" value="1">
                </div>
                <div class="col-sm-3" id="selectGroupIDDiv">
                  <?= gettext('from group') ?>:
                  <select class="form-control input-small" name="seedGroupID" id="seedGroupID" >
                    <option value="0"><?php gettext('Select a group'); ?></option>

                    <?php
                    foreach ($rsGroupRoleSeed as $groupRoleTemplate) {
                        echo '<option value="'.$groupRoleTemplate['grp_ID'].'">'.$groupRoleTemplate['grp_Name'].'</option>';
                    } ?>
                  </select><?php
                }
                ?>
              </div>
            </div>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-sm-12">
            <label for="UseGroupProps"><?= gettext('Group Specific Properties: ') ?></label>

            <?php
            if ($thisGroup->getHasSpecialProps()) {
                echo gettext('Enabled').'<br/>';
                ?>
                <div class="row">
                  <div class="col-sm-4">
                    <button type="button" id="disableGroupProps" class="btn btn-danger groupSpecificProperties"><?= gettext('Disable Group Specific Properties') ?></button><br/>
                  </div>
                  <div class="col-sm-4">
                    <a  class="btn btn-success" href="GroupPropsFormEditor.php?GroupID=<?= $iGroupID?>"><?= gettext('Edit Group-Specific Properties Form') ?></a>
                  </div>
                </div>
              <?php
            } else {
                echo gettext('Disabled').'<br/>';
                echo '<button type="button" id="enableGroupProps" class="btn btn-danger groupSpecificProperties">'.gettext('Enable Group Specific Properties').'</button>&nbsp;';
            }
            ?>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3">
            <br>
            <input type="submit" id="saveGroup" class="btn btn-primary" <?= 'value="'.gettext('Save').'"' ?> Name="GroupSubmit">
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="box">
  <div class="box-header">
    <h3 class="box-title"><?= gettext('Group Roles') ?>:</h3>
  </div>
  <div class="box-body">
    <div class="alert alert-info alert-dismissable">
      <i class="fa fa-info"></i>
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <strong></strong><?= gettext('Group role name changes are saved as soon as the box loses focus')?>
    </div>
      <div class="table-responsive">
    <table class="table" class="table" id="groupRoleTable">
    </table>
      </div>
    <label for="newRole"><?= gettext('New Role')?>: </label><input type="text" class="form-control" id="newRole" name="newRole">
    <br>
    <button type="button" id="addNewRole" class="btn btn-primary"><?= gettext('Add New Role')?></button>
  </div>
</div>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  //setup some document-global variables for later on in the javascript
  var defaultRoleID = <?= ($thisGroup->getDefaultRole() ? $thisGroup->getDefaultRole() : 1) ?>;
  var dataT = 0;
  var groupRoleData = <?= json_encode($groupService->getGroupRoles($iGroupID)); ?>;
  var roleCount = groupRoleData.length;
  var groupID =<?= $iGroupID ?>;
</script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/GroupEditor.js"></script>

<?php require 'Include/Footer.php' ?>
