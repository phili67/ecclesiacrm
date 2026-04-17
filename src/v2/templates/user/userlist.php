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
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header border-0">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:.5rem;">
            <a href="<?= $sRootPath ?>/v2/users/editor/new" class="btn btn-sm btn-primary">
                <i class="fas fa-user-plus mr-1"></i><?= _('New User') ?>
            </a>
            <div class="d-flex align-items-center" style="gap:.5rem;">
                <span class="text-muted small font-weight-bold"><?= _("Apply Roles") ?> :</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary changeRole" id="mainbuttonRole" data-id="<?= $first_roleID ?>">
                        <i class="fas fa-tags mr-1"></i><?= _("Add Role to Selected User(s)") ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" role="menu" id="AllRoles">
                        <?php foreach ($userRoles as $userRole) { ?>
                            <a href="#" class="dropdown-item changeRole" data-id="<?= $userRole->getId() ?>">
                                <i class="fas fa-tag mr-1"></i><?= $userRole->getName() ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body py-2">
        <table class="table table-sm table-hover dt-responsive" id="user-listing-table" style="width:100%;">
            <thead>
            <tr class="border-bottom">
                <th style="width:40px" class="text-center">
                    <input type="checkbox" class="check_all" id="check_all" data-toggle="tooltip" data-placement="bottom" title="<?= _("Check all boxes") ?>">
                </th>
                <th class="text-muted small font-weight-bold" style="min-width:120px"><?= _('Actions') ?></th>
                <th class="text-muted small font-weight-bold"><?= _('Name') ?></th>
                <th class="text-muted small font-weight-bold"><?= _('First Name') ?></th>
                <th class="text-center text-muted small font-weight-bold"><?= _('User Role') ?></th>
                <th class="text-center text-muted small font-weight-bold"><?= _('Last Login') ?></th>
                <th class="text-center text-muted small font-weight-bold"><?= _('Total Logins') ?></th>
                <th class="text-center text-muted small font-weight-bold"><?= _('Failed Logins') ?></th>
                <th class="text-center text-muted small font-weight-bold" style="min-width:70px"><?= _('Password') ?></th>
                <?php if (SessionUser::isAdmin()) { ?>
                <th class="text-center text-muted small font-weight-bold"><?= _("Take control") ?></th>
                <?php } ?>
                <th class="text-center text-muted small font-weight-bold"><?= _('2FA authentication') ?></th>
                <th class="text-center text-muted small font-weight-bold"><?= _('Status') ?></th>
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
                    <td align="center">
                        <div class="btn-group btn-group-sm">
                        <?php if ( $user->getPersonId() != 1 || $user->getId() == $sessionUserId && $user->getPersonId() == 1) : ?>
                            <a href="<?= $sRootPath ?>/v2/users/editor/<?= $user->getId() ?>" 
                              class="btn btn-outline-primary"
                               data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?= _("Manage user account") ?>"
                                ><i class="fas fa-pencil-alt" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                         <?php if ( $user->getPersonId() != 1) : ?>
                          <a class="webdavkey btn btn-outline-secondary" data-userid="<?= $user->getId()?>" href="#"
                             data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?= _("User account webdav key") ?>">
                             <i class="far fa-eye" aria-hidden="true"></i>
                          </a>
                         <?php endif; ?>
                        <?php if ( $user->getId() != $sessionUserId && $user->getPersonId() != 1 ) : ?>
                            <a href="#" class="deleteUser btn btn-outline-danger" data-id="<?= $user->getId() ?>" data-name="<?= $user->getPerson()->getFullName() ?>"

                               data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?= _("Remove user account (not the profile)") ?>"><i
                                        class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>
                        <?php endif; ?>
                        </div>
                      </td>
                    <td>
                        <a href="<?= $sRootPath ?>/v2/people/person/view/<?= $user->getId() ?>"> <?= $user->getPerson()->getLastName() ?></a>
                    </td>
                    <td>
                        <a href="<?= $sRootPath ?>/v2/people/person/view/<?= $user->getId() ?>"> <?= $user->getPerson()->getFirstName() ?></a>
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
                    <td class="text-center">
                      <?php if ($user->isLocked()) : ?>
                            <span class="text-danger font-weight-bold"><?= $user->getFailedLogins() ?></span>
                      <?php else : ?>
                            <?= $user->getFailedLogins() ?>
                      <?php endif; ?>
                      <?php if ($user->getFailedLogins() > 0) : ?>
                          <a href="#" class="btn btn-sm btn-outline-warning ml-1 resetUserLoginCount" data-id="<?= $user->getId() ?>" data-name="<?= $user->getPerson()->getFullName() ?>"
                              data-toggle="tooltip" data-placement="bottom" title="<?= _("Reset failed login") ?>">
                              <i class="fas fa-eraser"></i>
                          </a>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm">
                        <a href="<?= $sRootPath ?>/v2/users/change/password/<?= $user->getId() ?>/FromUserList"
                           class="btn btn-sm btn-outline-secondary"
                           data-toggle="tooltip" data-placement="bottom" title="<?= _("Change user account password") ?>">
                            <i class="fas fa-wrench"></i>
                        </a>
                        <?php if ($user->getId() != $sessionUserId && !empty($user->getEmail())) : ?>
                            <a href="#" class="btn btn-sm btn-outline-info resetUserPassword" data-id="<?= $user->getId() ?>" data-name="<?= $user->getPerson()->getFullName() ?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Reset and send new user password") ?>">
                                <i class="far fa-paper-plane"></i>
                            </a>
                        <?php endif; ?>                        
                      </div>
                    </td>
                    <td class="text-center">
                        <?php if (SessionUser::isAdmin() and $user->getId() != $sessionUserId) : ?>
                            <a href="#" class="btn btn-sm btn-outline-warning control-account" data-userid="<?= $user->getId()?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Take control of the account") ?>">
                                <i class="fas fa-gamepad"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($user->getTwoFaSecretConfirm()) : ?>
                            <a href="#" class="two-fa-manage btn btn-sm btn-outline-secondary" data-userid="<?= $user->getId()?>"
                               data-userName="<?= $user->getPerson()->getFullName() ?>" data-userid="<?= $user->getId()?>"
                               data-toggle="tooltip" data-placement="bottom" title="<?= _("Manage 2 factor secret") ?>">
                                <i class="fas fa-key mr-1"></i><?= _("Management") ?>
                            </a>
                        <?php else : ?>
                            <span class="badge badge-secondary"><?= _("No") ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php if ( $user->getPersonId() != 1 && $user->getId() != $sessionUserId) : ?>
                          <a href="#" class="lock-unlock" data-userid="<?= $user->getId()?>" data-userName="<?= $user->getPerson()->getFullName() ?>" data-locktype="<?= ($user->getIsDeactivated() == false)?'unlock':'lock' ?>"
                             data-toggle="tooltip" data-placement="bottom" title="<?= _("Lock/unlock user account") ?>">
                             <i class="fas <?= ($user->getIsDeactivated() == false) ? 'fa-unlock text-success' : 'fa-lock text-danger' ?>"></i>
                          </a>
                      <?php endif; ?>
                    </td>
                </tr>
              <?php
                }
              ?>
            </tbody>
        </table>
    </div>
</div>


<script src="<?= $sRootPath ?>/skin/js/user/UserList.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
