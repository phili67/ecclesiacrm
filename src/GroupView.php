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
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\SessionUser;


//Get the GroupID out of the querystring
$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');


//Get the data on this group
$thisGroup = EcclesiaCRM\GroupQuery::create()->findOneById($iGroupID);

//Look up the default role name
$defaultRole = ListOptionQuery::create()->filterById($thisGroup->getRoleListId())->filterByOptionId($thisGroup->getDefaultRole())->findOne();

$sGroupType = _('Unassigned');

$manager = GroupManagerPersonQuery::Create()->filterByPersonID(SessionUser::getUser()->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();
  
$is_group_manager = false;

if (!empty($manager)) {
  $is_group_manager = true;
  $_SESSION['bManageGroups'] = true;
} else  {
  $_SESSION['bManageGroups'] = SessionUser::getUser()->isManageGroupsEnabled();
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
$sPageTitle = _('Group View').' : '.$thisGroup->getName();

require 'Include/Header.php';
?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?= _('Group Functions') ?></h3>
  </div>
  <div class="box-body">
    <?php 
      if ( SessionUser::getUser()->isShowMapEnabled() || SessionUser::getUser()->belongsToGroup($iGroupID) ) {
    ?>
        <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/v2/map/<?= $thisGroup->getId() ?>"><i class="fa fa-map-marker"></i><?= _('Map this group') ?></a>
    <?php
      }
    ?>

    <?php
      if (Cart::GroupInCart($iGroupID) && SessionUser::getUser()->isShowCartEnabled()) {
    ?>
       <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fa fa-remove"></i> <span class="cartActionDescription"><?= _("Remove from Cart") ?></span></a>
    <?php
      } else if (SessionUser::getUser()->isShowCartEnabled()){
    ?>
       <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartgroupid="<?= $thisGroup->getId() ?>"> <i class="fa fa-cart-plus"></i> <span class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
    <?php
     }
    ?>
    <?php
      if ( SessionUser::getUser()->isManageGroupsEnabled() ) {
    ?>
        <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/GroupEditor.php?GroupID=<?= $thisGroup->getId()?>"><i class="fa fa-pencil"></i><?= _("Edit this Group") ?></a>
        <button class="btn btn-app bg-maroon"  id="deleteGroupButton"><i class="fa fa-trash"></i><?= _("Delete this Group") ?></button>
    <?php
      }
    ?>
     
    <?php
      if ($_SESSION['bManageGroups']) {
    ?>
     <form method="POST" action="<?= SystemURLs::getRootPath() ?>/GroupReports.php" style="display:inline">
       <input type="hidden" id="GroupID" name="GroupID" value="<?= $iGroupID?>">
       <button type="submit" class="btn btn-app bg-green exportCheckOutCSV"><i class="fa fa-file-pdf-o"></i><?= _("Group reports") ?></button>
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
        $sEmail = MiscUtils::SelectWhichInfo($per_Email, $fam_Email, false);
        if ($sEmail) {
            /* if ($sEmailLink) // Don't put delimiter before first email
          $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
            // Add email only if email address is not already in string
            if (!stristr($sEmailLink, $sEmail)) {
                $sEmailLink .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
                $roleEmails->$virt_RoleName .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
            }
        }
    }
    if ($sEmailLink) {
        // Add default email if default email has been set and is not already in string
        if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
            $sEmailLink .= SessionUser::getUser()->MailtoDelimiter().SystemConfig::getValue('sToEmailAddress');
        }
        $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

        if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
        // Display link
        ?>
        <div class="btn-group">
          <a  class="btn btn-app" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>"><i class="fa fa-send-o"></i><?= _("Email Group") ?></a>
          <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown" >
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <?php MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:') ?>
          </ul>
        </div>

        <div class="btn-group">
          <a class="btn btn-app" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>"><i class="fa fa-send"></i><?= _("Email (BCC)") ?></a>
          <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown" >
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <?php MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:?bcc=') ?>
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
        $sPhone = MiscUtils::SelectWhichInfo($per_CellPhone, $fam_CellPhone, false);
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
        if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
            // Display link
            echo '<a class="btn btn-app" href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fa fa-mobile-phone"></i>'._('Text Group').'</a>';
            echo '<script nonce="'. SystemURLs::getCSPNonce() .'">function allPhonesCommaD() {prompt("'._("Press CTRL + C to copy all group members\' phone numbers").'", "'.mb_substr($sPhoneLink, 0, -2).'")};</script>';
        }
    }
    ?>
  </div>
</div>

<div class="box">
    <div class="box-body">
      <center>
        <button class="btn btn-success" type="button">
            <?= _('Type of Group') ?> <span class="badge"> <?= $sGroupType ?> </span>
        </button>
        <button class="btn btn-info" type="button">
        <?php 
          if (!empty($defaultRole)) {
        ?>
            <?= _('Default Role') ?> <span class="badge"><?= $defaultRole->getOptionName() ?></span>
        <?php
          } 
        ?>
        </button>
        <button class="btn btn-primary" type="button">
            <?= _('Total Members') ?> <span class="badge" id="iTotalMembers"></span>
        </button>
        <?php 
          if (SessionUser::getUser()->isAdmin()) { 
        ?>
        <a class="btn btn-danger" href="<?= SystemURLs::getRootPath() ?>/api/groups/addressbook/extract/<?= $iGroupID ?>">
            <?= _('Address Book') ?> 
            <span class="badge">
              <i class="fa fa fa-address-card-o" aria-hidden="true"></i>
            </span>
        </a>
        <?php 
          } 
        ?>
      </center>
    </div>
</div>

<?php 
   if ( SessionUser::getUser()->isManageGroupsEnabled() ) { 
?>

<div class="row">
  <div class="col-lg-6">
    <div class="box collapsed-box">
      <div class="box-header with-border">
        <h3 class="box-title"><?= _('Quick Settings') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
          <form>
              <div class="col-sm-4"> <b><?= _('Status') ?>:</b> <input data-size="small" id="isGroupActive" type="checkbox" data-toggle="toggle" data-on="<?= _('Active') ?>" data-off="<?= _('Disabled') ?>"> </div>
              <div class="col-sm-6"> <b><?= _('Email export') ?>:</b> <input data-size="small" id="isGroupEmailExport" type="checkbox" data-toggle="toggle" data-on="<?= _('Include') ?>" data-off="<?= _('Exclude') ?>"></div>
          </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="box collapsed-box">
      <div class="box-header with-border">
        <h3 class="box-title" data-toggle="tooltip"  title="" data-placement="bottom" data-original-title="<?= _("Assign a group manager only for This Group. He can add or remove member from This Group, but not create Members.") ?>"><?= _("Group Managers") ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
          <b><?= _("Assigned Managers") ?>:</b>
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
            <p><?= _("No assigned Manager") ?>.</p>
          <?php
            }
          ?>
          </div>
          <a class="btn btn-primary" id="add-manager"><?= _("Add Manager") ?></a>
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
        <h3 class="box-title" data-toggle="tooltip"  title="" data-placement="bottom" data-original-title="<?= _("Assign properties for This Group. This properties are global properties and this can be changed in the admin right side bar &rarr; Group Properties") ?>"><?= _('Group Properties') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
                <b><?= _('Assigned Properties') ?>:</b>
                <?php
                  $sAssignedProperties = ',';
                ?>
                <table width="100%" cellpadding="2" class="table table-condensed dt-responsive dataTable no-footer dtr-inline" id="AssignedPropertiesTable"></table>
                
                <?php
                //}

                if (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true ) {
                ?>
                    <div class="alert alert-info">
                      <div>
                          <h4><strong><?= _('Assign a New Property') ?>:</strong></h4>

                            <div class="row">
                              <div class="form-group col-xs-12 col-md-7">
                              <select name="PropertyId" id="input-group-properties" class="input-group-properties form-control select2" style="width:100%" data-groupID="<?= $iGroupID ?>">
                              <option disabled selected> -- <?= _('select an option') ?> -- </option>
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
                                 <input type="submit" class="btn btn-primary assign-property-btn" value="<?= _('Assign') ?>">
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
        <h3 class="box-title" data-toggle="tooltip" title="" data-placement="bottom" data-original-title="<?= _("Assign properties for all members of the group. This properties are visible in each Person Profile &rarr; Assigned Group") ?>"><?= _('Group-Specific Properties') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
      </div>
      <div class="box-body">
          <b><?= _('Assigned Properties') ?>:</b>
              <?php
              if ($thisGroup->getHasSpecialProps()) {
                  // Create arrays of the properties.

                  // Construct the table
                  if ($ormPropList->count() == 0) {
                  ?>
                      <p><?= _("No member properties have been created")?></p>
                  <?php
                  } else {
                  ?>
              
                  <table width="100%" cellpadding="2" cellspacing="0"  class="table table-condensed dt-responsive dataTable no-footer dtr-inline">
                    <tr class="TableHeader">
                      <!--<td><b><?= _('Type') ?></b></td>-->
                      <td><b><?= _('Name') ?></b></td>
                      <td><b><?= _('Description') ?></b></td>
                    </tr>
                    <?php
                      $sRowClass = 'RowColorA';
                
                      foreach ($ormPropList as $prop) {
                          $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);
                          if ( SessionUser::getUser()->isSeePrivacyDataEnabled() || SessionUser::getUser()->isManageGroupsEnabled()  || $is_group_manager == true || $prop->getPersonDisplay() == "true") {
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
                  <p><?= _("Disabled for this group.") ?> <?= _("You should Edit the group and \"Enable Group Specific Properties\". To do this, press the button above : \"Edit this Group\"") ?></p>
              <?php
              }          
                //Print Assigned Properties
              ?>
          
              <?php
                 if ($thisGroup->getHasSpecialProps() && (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) ) {
              ?>
                  <a class="btn btn-primary" href="<?= SystemURLs::getRootPath() ?>/GroupPropsFormEditor.php?GroupID=<?= $thisGroup->getId() ?>"><?= _('Edit Group-Specific Properties Form') ?></a>
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
    <h3 class="box-title"><?= _('Group Members:') ?></h3>
  </div>
  <div class="box-body">
    <!-- START GROUP MEMBERS LISTING  -->
    <table class="table" id="membersTable"></table>     
    <!-- END GROUP MEMBERS LISTING -->
  </div>
</div>

<?php 
   if (SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) { 
?>
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><?php echo _("Manage Group Members"); ?>:</h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-1">
        <?= _("Add") ?>
      </div>
      <div class="col-md-3">
        <select class="form-control personSearch  select2" name="addGroupMember" style="width:100%"></select>
      </div>
      <div class="col-md-4">
        <button type="button" id="deleteSelectedRows" class="btn btn-danger" disabled> <?= _('Remove Selected Members from group') ?> </button>
      </div>
      <?php 
        if (SessionUser::getUser()->isManageGroupsEnabled()) { 
      ?>
      <div class="col-md-4">
        <div class="btn-group">
          <button type="button" id="addSelectedToCart" class="btn btn-success"  disabled> <?= _('Add Selected Members to Cart') ?></button>
          <button type="button" id="buttonDropdown" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a id="addSelectedToGroup"   disabled> <?= _('Add Selected Members to Group') ?></a></li>
            <li><a id="moveSelectedToGroup"  disabled> <?= _('Move Selected Members to Group') ?></a></li>
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
      if (SessionUser::getUser()->isSeePrivacyDataEnabled() 
        || (!$thisGroup->isSundaySchool() && SessionUser::getUser()->belongsToGroup($iGroupID)) 
        || ($thisGroup->isSundaySchool() && SessionUser::getUser()->isSundayShoolTeacherForGroup($iGroupID))) {
         echo "true";
      } else {
         echo "false";
      }
   ?>;
   
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/group/GroupView.js" ></script>



