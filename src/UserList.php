<?php
/*******************************************************************************
 *
 *  filename    : UserList.php
 *  last change : 2003-01-07
 *  description : displays a list of all users
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2002 Phillip Hullquist, Deane Barker
 *  Cpoyright 2018 Philippe Logel
 *
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';


use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserConfigQuery;

// Security: User must be an Admin to access this page.
// Otherwise, re-direct them to the main menu.
if (!$_SESSION['user']->isAdmin()) {
    Redirect('Menu.php');
    exit;
}

// Get all the User records
$rsUsers = UserQuery::create()
           ->leftJoinWithUserRole()
           ->find();

// Set the page title and include HTML header
$sPageTitle = gettext("System Users Listing");
require 'Include/Header.php';

// we search all the available roles
$userRoles = UserRoleQuery::Create()->find();

$first_roleID = 0;
foreach ($userRoles as $userRole) {
  $first_roleID = $userRole->getId();
  break;
}

if ($usr_role_id == null) {
  $usr_role_id = $first_roleID;
}

?>
<!-- Default box -->
<div class="box">
    <div class="box-header">
        <a href="<?= SystemURLs::getRootPath() ?>/UserEditor.php" class="btn btn-app"><i class="fa fa-user-plus"></i><?= gettext('New User') ?></a>
    
      <div class="btn-group pull-right">
        <a class="btn btn-app changeRole" id="mainbuttonRole" data-id="<?= $first_roleID ?>"><i class="fa fa-arrow-circle-o-down"></i><?= gettext("Add Role to Selected User(s)") ?></a>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu" role="menu" id="AllRoles">
            <?php 
               foreach ($userRoles as $userRole) {
            ?>               
               <li> <a href="#" class="changeRole" data-id="<?= $userRole->getId() ?>"><i class="fa fa-arrow-circle-o-down"></i><?= $userRole->getName() ?></a></li>
            <?php
               }
            ?>
        </ul>
      </div>
      <div class="pull-right" style="margin-right:15px;margin-top:10px">
        <h4><?= gettext("Apply Roles") ?></h4>
      </div>
    </div>
</div>
<div class="box">
    <div class="box-body">
        <table class="table table-hover dt-responsive" id="user-listing-table" style="width:100%;">
            <thead>
            <tr>
                <th align="center"></th>
                <th><?= gettext('Actions') ?></th>
                <th><?= gettext('Name') ?></th>
                <th><?= gettext('First Name') ?></th>
                <th align="center"><?= gettext('User Role') ?></th>
                <th align="center"><?= gettext('Last Login') ?></th>
                <th align="center"><?= gettext('Total Logins') ?></th>
                <th align="center"><?= gettext('Failed Logins') ?></th>
                <th align="center"><?= gettext('Password') ?></th>
                <th align="center"><?= gettext('Status') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rsUsers as $user) { //Loop through the person?>
                <tr>
                    <td>
                      <?php 
                         if ( $user->getPersonId() != 1 && $user->getId() != $_SESSION['user']->getId()) {
                      ?>
                        <input type="checkbox" class="checkbox_users checkbox_user<?= $user->getPersonId()?>" name="AddRecords" data-id="<?= $user->getPersonId() ?>">
                      <?php
                         }
                      ?>
                    </td>
                    <td>
                        <?php 
                           if ( $user->getPersonId() != 1 || $user->getId() == $_SESSION['user']->getId() && $user->getPersonId() == 1) {
                        ?>
                            <a href="<?= SystemURLs::getRootPath() ?>/UserEditor.php?PersonID=<?= $user->getId() ?>"><i class="fa fa-pencil"
                                                                                   aria-hidden="true"></i></a>&nbsp;&nbsp;
                        <?php
                            } else {
                        ?>
                           <span style="color:red"><?= gettext("Not modifiable") ?></span>
                        <?php
                            }
                        ?>
                         <?php 
                           if ( $user->getPersonId() != 1) {
                         ?>
                      
                          <a class="webdavkey" data-userid="<?= $user->getId()?>">
                             <i class="fa fa-eye" aria-hidden="true"></i>
                          </a>
                         <?php
                           }
                          ?>
                        <?php 
                          if ( $user->getId() != $_SESSION['user']->getId() && $user->getPersonId() != 1 ) {
                        ?>
                            <a onclick="deleteUser(<?= $user->getId() ?>, '<?= $user->getPerson()->getFullName() ?>')"><i
                                        class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php
                          } 
                        ?>
                      </td>
                    <td>
                        <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $user->getId() ?>"> <?= $user->getPerson()->getLastName() ?></a>
                    </td>
                    <td>
                        <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $user->getId() ?>"> <?= $user->getPerson()->getFirstName() ?></a>
                    </td>
                    <td class="role<?=$user->getPersonId()?>">
                        <?php 
                          if (!is_null($user->getUserRole())) { 
                        ?>
                          <?= $user->getUserRole()->getName() ?>
                        <?php 
                          } else {
                        ?>
                           <?= gettext("Undefined") ?>
                        <?php
                          }
                        ?>
                    </td>
                    <td align="center"><?= $user->getLastLogin(SystemConfig::getValue('sDateFormatLong')) ?></td>
                    <td align="center"><?= $user->getLoginCount() ?></td>
                    <td align="center">
                        <?php if ($user->isLocked()) {
        ?>
                            <span class="text-red"><?= $user->getFailedLogins() ?></span>
                            <?php
    } else {
        echo $user->getFailedLogins();
    }
    if ($user->getFailedLogins() > 0) {
        ?>
                            <a onclick="restUserLoginCount(<?= $user->getId() ?>, '<?= $user->getPerson()->getFullName() ?>')"><i
                                        class="fa fa-eraser" aria-hidden="true"></i></a>
                            <?php
    } ?>
                    </td>
                    <td>
                        <a href="<?= SystemURLs::getRootPath() ?>/UserPasswordChange.php?PersonID=<?= $user->getId() ?>&FromUserList=True"><i
                                    class="fa fa-wrench" aria-hidden="true"></i></a>&nbsp;&nbsp;
                        <?php if ($user->getId() != $_SESSION['user']->getId() && !empty($user->getEmail())) {
        ?>
                            <a onclick="resetUserPassword(<?= $user->getId() ?>, '<?= $user->getPerson()->getFullName() ?>')"><i
                                class="fa fa-send-o" aria-hidden="true"></i></a>
                            <?php
    } ?>
                    </td>
                    <td  align="center">
                    <?php 
                        if ( $user->getPersonId() != 1 && $user->getId() != $_SESSION['user']->getId()) {
                    ?>
                    
                          <a class="lock-unlock" data-userid="<?= $user->getId()?>" style="color:<?= ($user->getPerson()->getDateDeactivated() == null)?'green':'red'?>" data-userid="<?= $user->getId()?>">
                             <i class="fa <?= ($user->getPerson()->getDateDeactivated() == null)?'fa-unlock':'fa-lock' ?>" aria-hidden="true"></i>
                          </a>
                    <?php
                         }
                    ?>
                    </td>
                </tr>
                <?php
} ?>
            </tbody>
        </table>
        
        <input type="checkbox" class="check_all"> <?= gettext("Check all") ?>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->

<?php require 'Include/Footer.php' ?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/UserList.js" ></script>