<?php
/*******************************************************************************
 *
 *  filename    : GroupView.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001-2003 Deane Barker, Chris Gebhardt
 *
 *  Additional Contributors:
 *  2006-2007 Ed Davis
 *  2017 Philippe Logel
 *  2018 Philippe Logel all right reserved
 *
 *
 *  Copyright Contributors
 *
 * **************************************************************************** */

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Property;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;


//Get the GroupID out of the querystring
$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');

// check if the user belongs to the group
$currentUserBelongToGroup = $_SESSION['user']->belongsToGroup($iGroupID);
    
//Get the data on this group
$thisGroup = EcclesiaCRM\GroupQuery::create()->findOneById($iGroupID);

//Look up the default role name
$defaultRole = ListOptionQuery::create()->filterById($thisGroup->getRoleListId())->filterByOptionId($thisGroup->getDefaultRole())->findOne();

$sGroupType = gettext('Unassigned');

$manager = GroupManagerPersonQuery::Create()->filterByPersonID($_SESSION['user']->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();
  
$is_group_manager = false;

if (!empty($manager)) {
  $is_group_manager = true;
  $_SESSION['bManageGroups'] = true;
} else  {
  $_SESSION['bManageGroups'] = $_SESSION['user']->isManageGroupsEnabled();
}
       
//Get the group's type name
if ($thisGroup->getType() > 0) {
    $groupeType = ListOptionQuery::create()->filterById(3)->filterByOptionId($thisGroup->getType())->findOne();
    if (!empty($groupeType)) {
      $sGroupType = $groupeType->getOptionName();
    }
}

//Get all the properties
$ormProperties = PropertyQuery::Create()
                  ->filterByProClass('g')
                  ->orderByProName()
                  ->find();
                  

// Get data for the form as it now exists..
$ormPropList = GroupPropMasterQuery::Create()->orderByPropId()->findByGroupId($iGroupID);

//Set the page title
$sPageTitle = gettext('Group View').' : '.$thisGroup->getName();

require 'Include/Header.php';
?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Group Functions') ?></h3>
  </div>
  <div class="box-body">
    <?php 
      if ($_SESSION['user']->isShowMapEnabled() || $currentUserBelongToGroup == 1) {
        if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
    ?>
        <a class="btn btn-app" href="MapUsingLeaflet.php?GroupID=<?= $thisGroup->getId() ?>"><i class="fa fa-map-marker"></i><?= gettext('Map this group') ?></a>
      <?php
        } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps') {
      ?>
        <a class="btn btn-app" href="MapUsingGoogle.php?GroupID=<?= $thisGroup->getId() ?>"><i class="fa fa-map-marker"></i><?= gettext('Map this group') ?></a>
        
    <?php
        } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
    ?>
        <a class="btn btn-app" href="MapUsingBing.php?GroupID=<?= $thisGroup->getId() ?>"><i class="fa fa-map-marker"></i><?= gettext('Map this group') ?></a>
    <?php
        }    
      }
    ?>

    <?php
      if (Cart::GroupInCart($iGroupID) && $_SESSION['user']->isShowCartEnabled()) {
    ?>
       <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= gettext("Remove from Cart") ?></span></a>
    <?php
      } else if ($_SESSION['user']->isShowCartEnabled()){
    ?>
       <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= gettext("Add to Cart") ?></span></a>
    <?php
     }
    ?>
    <?php
      if ( $_SESSION['user']->isManageGroupsEnabled() ) {
    ?>
        <a class="btn btn-app" href="GroupEditor.php?GroupID=<?= $thisGroup->getId()?>"><i class="fa fa-pencil"></i><?= gettext("Edit this Group") ?></a>
        <button class="btn btn-app bg-maroon"  id="deleteGroupButton"><i class="fa fa-trash"></i><?= gettext("Delete this Group") ?></button>
    <?php
      }
    ?>
     
    <?php
      if ($_SESSION['bManageGroups']) {
    ?>
     <form method="POST" action="<?= SystemURLs::getRootPath() ?>/GroupReports.php" style="display:inline">
       <input type="hidden" id="GroupID" name="GroupID" value="<?= $iGroupID?>">
       <button type="submit" class="btn btn-app bg-green exportCheckOutCSV"><i class="fa fa-file-pdf-o"></i><?= gettext("Group reports") ?></button>
     </form>
    <?php
      }
    ?>
    <?php

// Email Group link
// Note: This will email entire group, even if a specific role is currently selected.
    $sSQL = "SELECT per_Email, fam_Email, lst_OptionName as virt_RoleName
            FROM person_per
            LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
            LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
            LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
            INNER JOIN list_lst on  grp_RoleListID = lst_ID AND p2g2r_rle_ID = lst_OptionID
        WHERE per_ID NOT IN
            (SELECT per_ID
                FROM person_per
                INNER JOIN record2property_r2p ON r2p_record_ID = per_ID
                INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email')
            AND p2g2r_grp_ID = ".$iGroupID;
    $rsEmailList = RunQuery($sSQL);
    $sEmailLink = '';
    while (list($per_Email, $fam_Email, $virt_RoleName) = mysqli_fetch_row($rsEmailList)) {
        $sEmail = SelectWhichInfo($per_Email, $fam_Email, false);
        if ($sEmail) {
            /* if ($sEmailLink) // Don't put delimiter before first email
          $sEmailLink .= $sMailtoDelimiter; */
            // Add email only if email address is not already in string
            if (!stristr($sEmailLink, $sEmail)) {
                $sEmailLink .= $sEmail .= $sMailtoDelimiter;
                $roleEmails->$virt_RoleName .= $sEmail .= $sMailtoDelimiter;
            }
        }
    }
    if ($sEmailLink) {
        // Add default email if default email has been set and is not already in string
        if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
            $sEmailLink .= $sMailtoDelimiter.SystemConfig::getValue('sToEmailAddress');
        }
        $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

        if ($bEmailMailto) { // Does user have permission to email groups
        // Display link
        ?>
        <div class="btn-group">
          <a  class="btn btn-app" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>"><i class="fa fa-send-o"></i><?= gettext("Email Group") ?></a>
          <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown" >
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <?php generateGroupRoleEmailDropdown($roleEmails, 'mailto:') ?>
          </ul>
        </div>

        <div class="btn-group">
          <a class="btn btn-app" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>"><i class="fa fa-send"></i><?= gettext("Email (BCC)") ?></a>
          <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown" >
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <?php generateGroupRoleEmailDropdown($roleEmails, 'mailto:?bcc=') ?>
          </ul>
        </div>

        <?php
        }
    }
// Group Text Message Comma Delimited - added by RSBC
// Note: This will provide cell phone numbers for the entire group, even if a specific role is currently selected.
    $sSQL = "SELECT per_CellPhone, fam_CellPhone
            FROM person_per
            LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
            LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
            LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
        WHERE per_ID NOT IN
            (SELECT per_ID
            FROM person_per
            INNER JOIN record2property_r2p ON r2p_record_ID = per_ID
            INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS')
        AND p2g2r_grp_ID = ".$iGroupID;
    $rsPhoneList = RunQuery($sSQL);
    $sPhoneLink = '';
    $sCommaDelimiter = ', ';
    while (list($per_CellPhone, $fam_CellPhone) = mysqli_fetch_row($rsPhoneList)) {
        $sPhone = SelectWhichInfo($per_CellPhone, $fam_CellPhone, false);
        if ($sPhone) {
            /* if ($sPhoneLink) // Don't put delimiter before first phone
          $sPhoneLink .= $sCommaDelimiter; */
            // Add phone only if phone is not already in string
            if (!stristr($sPhoneLink, $sPhone)) {
                $sPhoneLink .= $sPhone .= $sCommaDelimiter;
            }
        }
    }
    if ($sPhoneLink) {
        if ($bEmailMailto) { // Does user have permission to email groups
            // Display link
            echo '<a class="btn btn-app" href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fa fa-mobile-phone"></i>'.gettext('Text Group').'</a>';
            echo '<script nonce="'. SystemURLs::getCSPNonce() .'">function allPhonesCommaD() {prompt("'.gettext("Press CTRL + C to copy all group members\' phone numbers").'", "'.mb_substr($sPhoneLink, 0, -2).'")};</script>';
        }
    }
    ?>
  </div>
</div>

<div class="box">
    <div class="box-body">
      <center>
        <button class="btn btn-success" type="button">
            <?= gettext('Type of Group') ?> <span class="badge"> <?= $sGroupType ?> </span>
        </button>
        <button class="btn btn-info" type="button">
        <?php 
          if (!empty($defaultRole)) {
        ?>
            <?= gettext('Default Role') ?> <span class="badge"><?= $defaultRole->getOptionName() ?></span>
        <?php
          } 
        ?>
        </button>
        <button class="btn btn-primary" type="button">
            <?= gettext('Total Members') ?> <span class="badge" id="iTotalMembers"></span>
        </button>
      </center>
    </div>
</div>

<?php 
   if ( $_SESSION['user']->isManageGroupsEnabled() ) { 
?>

<div class="row">
  <div class="col-lg-6">
    <div class="box collapsed-box">
      <div class="box-header with-border">
        <h3 class="box-title"><?= gettext('Quick Settings') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
          <form>
              <div class="col-sm-4"> <b><?= gettext('Status') ?>:</b> <input data-size="small" id="isGroupActive" type="checkbox" data-toggle="toggle" data-on="<?= gettext('Active') ?>" data-off="<?= gettext('Disabled') ?>"> </div>
              <div class="col-sm-6"> <b><?= gettext('Email export') ?>:</b> <input data-size="small" id="isGroupEmailExport" type="checkbox" data-toggle="toggle" data-on="<?= gettext('Include') ?>" data-off="<?= gettext('Exclude') ?>"></div>
          </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="box collapsed-box">
      <div class="box-header with-border">
        <h3 class="box-title" data-toggle="tooltip"  title="" data-placement="bottom" data-original-title="<?= gettext("Assign a group manager only for This Group. He can add or remove member from This Group, but not create Members.") ?>"><?= gettext("Group Managers") ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
          <b><?= gettext("Assigned Managers") ?>:</b>
          <div id="Manager-list">
          <?php
            $managers = GroupManagerPersonQuery::Create()->findByGroupId($iGroupID);
        
            if ($managers->count()) {
              foreach ($managers as $manager) {
                if (!$manager->getPerson()->isDeactivated()) {
            ?>
              <?= $manager->getPerson()->getFullName()?><a class="delete-person-manager" data-personid="<?= $manager->getPerson()->getId() ?>" data-groupid="<?= $iGroupID ?>"><i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></a>, 
            <?php
                }
              }
            } else {
          ?>
            <p><?= gettext("No assigned Manager") ?>.</p>
          <?php
            }
          ?>
          </div>
          <a class="btn btn-primary" id="add-manager"><?= gettext("Add Manager") ?></a>
      </div>
    </div>
  </div>
</div>

<?php
}
?>

<?php
  if ( $_SESSION['bManageGroups'] ) {
?>

<div class="row">
  <div class="col-lg-6">
    <div class="box collapsed-box">
      <div class="box-header with-border">
        <h3 class="box-title" data-toggle="tooltip"  title="" data-placement="bottom" data-original-title="<?= gettext("Assign properties for This Group. This properties are global properties and this can be changed in the admin right side bar &rarr; Group Properties") ?>"><?= gettext('Group Properties') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
                <b><?= gettext('Assigned Properties') ?>:</b>
                <?php
                  $sAssignedProperties = ',';
                ?>
                <table width="100%" cellpadding="2" class="table table-condensed dt-responsive dataTable no-footer dtr-inline" id="AssignedPropertiesTable"></table>
                
                <?php
                //}

                if ($_SESSION['user']->isManageGroupsEnabled() || $is_group_manager == true ) {
                ?>
                    <div class="alert alert-info">
                      <div>
                          <h4><strong><?= gettext('Assign a New Property') ?>:</strong></h4>

                            <div class="row">
                              <div class="form-group col-xs-12 col-md-7">
                              <select name="PropertyId" id="input-group-properties" class="input-group-properties form-control select2" style="width:100%" data-groupID="<?= $iGroupID ?>">
                              <option disabled selected> -- <?= gettext('select an option') ?> -- </option>
                                <?php
                                foreach ($ormProperties as $ormProperty) {
                                    //If the property doesn't already exist for this Person, write the <OPTION> tag
                                    if (strlen(strstr($sAssignedProperties, ','.$ormProperty->getProId().',')) == 0) {
                                    ?>
                                        <option value="<?= $ormProperty->getProId() ?>" data-pro_Prompt="<?= $ormProperty->getProPrompt() ?>" data-pro_Value=""><?= $ormProperty->getProName() ?></option>
                                    <?php
                                    }
                                }
                                ?>

                              </select>
                              </div>
                              <div id="prompt-box" class="col-xs-12 col-md-7"></div>
                              <div class="form-group col-xs-12 col-md-7">
                                 <input type="submit" class="btn btn-primary assign-property-btn" value="<?= gettext('Assign') ?>">
                              </div>
                            </div>
                      </div>
                    </div>
              <?php
                } else {
              ?>
                  <br><br><br>
              <?php
                }
              ?>
        </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="box collapsed-box">
      <div class="box-header with-border">
        <h3 class="box-title" data-toggle="tooltip" title="" data-placement="bottom" data-original-title="<?= gettext("Assign properties for all members of the group. This properties are visible in each Person Profile &rarr; Assigned Group") ?>"><?= gettext('Group-Specific Properties') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
          <b><?= gettext('Assigned Properties') ?>:</b>
              <?php
              if ($thisGroup->getHasSpecialProps()) {
                  // Create arrays of the properties.

                  // Construct the table
                  if ($ormPropList->count() == 0) {
                  ?>
                      <p><?= gettext("No member properties have been created")?></p>
                  <?php
                  } else {
                  ?>
              
                  <table width="100%" cellpadding="2" cellspacing="0"  class="table table-condensed dt-responsive dataTable no-footer dtr-inline">
                    <tr class="TableHeader">
                      <!--<td><b><?= gettext('Type') ?></b></td>-->
                      <td><b><?= gettext('Name') ?></b></td>
                      <td><b><?= gettext('Description') ?></b></td>
                    </tr>
                    <?php
                      $sRowClass = 'RowColorA';
                
                      foreach ($ormPropList as $prop) {
                          $sRowClass = AlternateRowStyle($sRowClass);
                          if ( $_SESSION['user']->isSeePrivacyDataEnabled() || $_SESSION['user']->isManageGroupsEnabled()  || $is_group_manager == true || $prop->getPersonDisplay() == "true") {
                          ?>
                        <tr class="<?= $sRowClass ?>">
                          <!--<td><?= $aPropTypes[$prop->getTypeId()] ?></td>-->
                          <td><?= $prop->getName() ?></td>
                          <td><?= OutputUtils::displayCustomField($prop->getTypeId(), $prop->getDescription(), $prop->getSpecial()) ?></td>
                        </tr>
                      <?php
                        }
                      }
                    ?>
                  </table>
                <?php
                  }
              } else {
              ?>
                  <p><?= gettext("Disabled for this group.") ?> <?= gettext("You should Edit the group and \"Enable Group Specific Properties\". To do this, press the button above : \"Edit this Group\"") ?></p>
              <?php
              }          
                //Print Assigned Properties
              ?>
          
              <?php
                 if ($thisGroup->getHasSpecialProps() && ($_SESSION['user']->isManageGroupsEnabled() || $is_group_manager == true) ) {
              ?>
                  <a class="btn btn-primary" href="GroupPropsFormEditor.php?GroupID=<?= $thisGroup->getId() ?>"><?= gettext('Edit Group-Specific Properties Form') ?></a>
              <?php
                 }
              ?>
      </div>
    </div>
  </div>
</div>

<?php
  }
?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?= gettext('Group Members:') ?></h3>
  </div>
  <div class="box-body">
    <!-- START GROUP MEMBERS LISTING  -->
    <table class="table" id="membersTable"></table>     
    <!-- END GROUP MEMBERS LISTING -->
  </div>
</div>

<?php 
   if ($_SESSION['user']->isManageGroupsEnabled() || $is_group_manager == true) { 
?>
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?php echo gettext("Manage Group Members"); ?>:</h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-1">
        <?= gettext("Add") ?>
      </div>
      <div class="col-md-3">
        <select class="form-control personSearch  select2" name="addGroupMember" style="width:100%"></select>
      </div>
      <div class="col-md-4">
        <button type="button" id="deleteSelectedRows" class="btn btn-danger" disabled> <?= gettext('Remove Selected Members from group') ?> </button>
      </div>
      <?php 
        if ($_SESSION['user']->isManageGroupsEnabled()) { 
      ?>
      <div class="col-md-4">
        <div class="btn-group">
          <button type="button" id="addSelectedToCart" class="btn btn-success"  disabled> <?= gettext('Add Selected Members to Cart') ?></button>
          <button type="button" id="buttonDropdown" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a id="addSelectedToGroup"   disabled> <?= gettext('Add Selected Members to Group') ?></a></li>
            <li><a id="moveSelectedToGroup"  disabled> <?= gettext('Move Selected Members to Group') ?></a></li>
          </ul>
        </div>
      </div>
      <?php 
        }
      ?>

    </div>
  </div>
</div>
<?php
   }
?>

<?php require 'Include/Footer.php' ?>


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.currentGroup            = <?= $iGroupID ?>;
  window.CRM.groupName               = "<?= $thisGroup->getName() ?>";
  window.CRM.isActive                = <?= $thisGroup->isActive()? 'true': 'false' ?>;
  window.CRM.isIncludeInEmailExport  = <?= $thisGroup->isIncludeInEmailExport()? 'true': 'false' ?>;
  
  var dataT = 0;
  
  var isShowable  = <?php
     // it should be better to write this part in the api/groups/members
      if ($_SESSION['user']->isSeePrivacyDataEnabled() 
        || (!$thisGroup->isSundaySchool() && $_SESSION['user']->belongsToGroup($iGroupID)) 
        || ($thisGroup->isSundaySchool() && $_SESSION['user']->isSundayShoolTeacherForGroup($iGroupID))) {
         echo "true";
      } else {
         echo "false";
      }
   ?>;
   
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/GroupView.js" ></script>



