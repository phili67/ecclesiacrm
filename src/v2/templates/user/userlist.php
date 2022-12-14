<?php
/*******************************************************************************
 *
 *  filename    : userlist.php
 *  last change : 2019-02-07
 *  description : displays a list of all users
 *
 *  http://www.ecclesiacrm.com/
 *  Cpoyright 2019 Philippe Logel all tight reserved not MIT
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>
<!-- Default box -->
<div class="card">
    <div class="card-header  border-1">
        <a href="<?= $sRootPath ?>/UserEditor.php" class="btn btn-app"><i class="fas fa-user-plus"></i><?= _('New User') ?></a>

      <div class="btn-group pull-right">
        <a class="btn btn-app changeRole" id="mainbuttonRole" data-id="<?= $first_roleID ?>"><i class="fas fa-arrow-circle-down"></i><?= _("Add Role to Selected User(s)") ?></a>
        <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu" role="menu" id="AllRoles">
            <?php
               foreach ($userRoles as $userRole) {
            ?>
               <a href="#" class="dropdown-item changeRole" data-id="<?= $userRole->getId() ?>"><i class="fas fa-arrow-circle-down"> </i><?= $userRole->getName() ?></a>
            <?php
               }
            ?>
        </div>
      </div>
      <div class="pull-right" style="margin-right:15px;margin-top:10px">
        <h4><?= _("Apply Roles") ?></h4>
      </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-hover dt-responsive" id="user-listing-table" style="width:100%;">
            <thead>
            <tr>
                <th align="center" style="width:60px">
                    <input type="checkbox" class="check_all" id="check_all" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?= _("Check all boxes") ?>">
                <th><?= _('Actions') ?></th>
                <th><?= _('Name') ?></th>
                <th><?= _('First Name') ?></th>
                <th align="center"><?= _('User Role') ?></th>
                <th align="center"><?= _('Last Login') ?></th>
                <th align="center"><?= _('Total Logins') ?></th>
                <th align="center"><?= _('Failed Logins') ?></th>
                <th align="center"><?= _('Password') ?></th>
                <?php if (SessionUser::isAdmin()) { ?>
                <th align="center"><?= _("Take control") ?></th>
                <?php } ?>
                <th align="center"><?= _('2FA authentication') ?></th>
                <th align="center"><?= _('Status') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rsUsers as $user) { //Loop through the person?>
                <tr id="row-<?= $user->getId() ?>">
                    <td>
                      <?php
                         if ( $user->getPersonId() != 1 && $user->getId() != $sessionUserId) {
                      ?>
                        <input type="checkbox" class="checkbox_users checkbox_user<?= $user->getPersonId()?>" name="AddRecords" data-id="<?= $user->getPersonId() ?>">
                      <?php
                         }
                      ?>
                    </td>
                    <td>
                        <?php
                          if ( $user->getPersonId() != 1 || $user->getId() == $sessionUserId && $user->getPersonId() == 1) {
                        ?>
                            <a href="<?= $sRootPath ?>/UserEditor.php?PersonID=<?= $user->getId() ?>"><i class="fas fa-pencil-alt"
                                                                                   aria-hidden="true"></i></a>&nbsp;&nbsp;
                        <?php
                          } else {
                        ?>
                           <span style="color:red"><?= _("Not modifiable") ?></span>
                        <?php
                          }
                        ?>
                         <?php
                           if ( $user->getPersonId() != 1) {
                         ?>

                          <a class="webdavkey" data-userid="<?= $user->getId()?>">
                             <i class="far fa-eye" aria-hidden="true"></i>
                          </a>
                         <?php
                           }
                          ?>
                        <?php
                          if ( $user->getId() != $sessionUserId && $user->getPersonId() != 1 ) {
                        ?>
                            <a href="#" class="deleteUser" data-id="<?= $user->getId() ?>" data-name="<?= $user->getPerson()->getFullName() ?>"><i
                                        class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>
                        <?php
                          }
                        ?>
                      </td>
                    <td>
                        <a href="<?= $sRootPath ?>/PersonView.php?PersonID=<?= $user->getId() ?>"> <?= $user->getPerson()->getLastName() ?></a>
                    </td>
                    <td>
                        <a href="<?= $sRootPath ?>/PersonView.php?PersonID=<?= $user->getId() ?>"> <?= $user->getPerson()->getFirstName() ?></a>
                    </td>

                    <td class="role<?=$user->getPersonId()?>">
                        <?php
                          if (!is_null($user->getUserRole())) {
                        ?>
                          <?= $user->getUserRole()->getName() ?>
                        <?php
                          } else {
                        ?>
                           <?= _("Undefined") ?>
                        <?php
                          }
                        ?>
                    </td>
                    <td align="center"><?= $user->getLastLogin($dateFormatLong) ?></td>
                    <td align="center"><?= $user->getLoginCount() ?></td>
                    <td align="center">
                      <?php
                        if ($user->isLocked()) {
                      ?>
                            <span class="text-red"><?= $user->getFailedLogins() ?></span>
                      <?php
                        } else {
                            echo $user->getFailedLogins();
                        }
                        if ($user->getFailedLogins() > 0) {
                      ?>
                            <a href="#" class="restUserLoginCount" data-id="<?= $user->getId() ?>" data-name="<?= $user->getPerson()->getFullName() ?>"><i
                                        class="fas fa-eraser" aria-hidden="true"></i></a>
                      <?php
                        }
                      ?>
                    </td>
                    <td>
                        <a href="<?= $sRootPath ?>/UserPasswordChange.php?PersonID=<?= $user->getId() ?>&FromUserList=True"><i
                                    class="fas fa-wrench" aria-hidden="true"></i></a>&nbsp;&nbsp;
                        <?php
                          if ($user->getId() != $sessionUserId && !empty($user->getEmail())) {
                        ?>
                            <a href="#" class="resetUserPassword" data-id="<?= $user->getId() ?>" data-name="<?= $user->getPerson()->getFullName() ?>"><i
                                class="far fa-paper-plane" aria-hidden="true"></i></a>
                        <?php
                          }
                        ?>
                    </td>
                    <td>
                        <?php if (SessionUser::isAdmin() and $user->getId() != $sessionUserId) { ?>

                            <a href="#" class="control-account" data-userid="<?= $user->getId()?>">
                                <i class="fa fa-gamepad"></i>
                            </a>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($user->getTwoFaSecretConfirm()) { ?>
                            <a href="#" class="two-fa-manage btn btn-secondary" data-userid="<?= $user->getId()?>" data-userName="<?= $user->getPerson()->getFullName() ?>" data-userid="<?= $user->getId()?>">
                                <i class="fas fa-key" aria-hidden="true"></i> <?= _("Management") ?>
                            </a>
                        <?php } else { ?>
                            <?= _("No") ?>
                        <?php } ?>
                    </td>
                    <td  align="center">
                      <?php
                        if ( $user->getPersonId() != 1 && $user->getId() != $sessionUserId) {
                      ?>
                          <a href="#" class="lock-unlock" data-userid="<?= $user->getId()?>" data-userName = "<?= $user->getPerson()->getFullName() ?>" data-locktype="<?= ($user->getIsDeactivated() == false)?'unlock':'lock' ?>" style="color:<?= ($user->getIsDeactivated() == false)?'green':'red'?>" data-userid="<?= $user->getId()?>">
                             <i class="fa <?= ($user->getIsDeactivated() == false)?'fa-unlock':'fa-lock' ?>" aria-hidden="true"></i>
                          </a>
                      <?php
                        }
                      ?>
                    </td>
                </tr>
              <?php
                }
              ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->

<?php
require $sRootDocument . '/Include/Footer.php';
?>

<script src="<?= $sRootPath ?>/skin/js/user/UserList.js" ></script>
