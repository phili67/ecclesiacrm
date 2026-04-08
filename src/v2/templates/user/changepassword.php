<?php

/*******************************************************************************
 *
 *  filename    : changepassword.php
 *  last change : 2023-05-13
 *  description : displays a list of all users
 *
 *  http://www.ecclesiacrm.com/
 *  Cpoyright 2023 Philippe Logel all tight reserved not MIT
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

require $sRootDocument . '/Include/Header.php';

if (SessionUser::getUser()->getNeedPasswordChange()) {
    ?>
    <div class="alert alert-danger alert-dismissible shadow-sm">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i><?= _("Alert") ?>!</h4>
        <?= _('Your account record indicates that you need to change your password before proceding.') ?>
    </div>
    <?php
} 
?>

<div class="row">
    <div class="col-lg-8 col-xl-7 mx-auto">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header border-0">
                <?php
                  if (!$bAdminOtherUser) {
                ?>
                    <h3 class="card-title"><i class="fas fa-key mr-2"></i><?= _('Change Password') ?></h3>
                <?php
                  } else {
                ?>
                    <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i><?= _('Set User Password') ?></h3>
                <?php
                  }
                ?>
            </div>
            <form method="post" action="<?= $sRootPath ?>/v2/users/change/password/<?= $iPersonID ?><?= $FromUserList?'/FromUserList':'' ?>">
                <div class="card-body">
                    <?php if (!$bAdminOtherUser) { ?>
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-shield-alt text-primary mt-1 mr-2"></i>
                            <div>
                                <div class="font-weight-bold mb-1"><?= _('Security requirements') ?></div>
                                <div class="text-muted small"><?= _('Enter your current password, then your new password twice.  Passwords must be at least').' '.SystemConfig::getValue('iMinPasswordLength').' '._('characters in length.') ?></div>
                            </div>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-user-cog text-primary mt-1 mr-2"></i>
                            <div>
                                <div class="font-weight-bold mb-1"><?= _('Administrator action') ?></div>
                                <div class="text-muted small"><?= _('Enter a new password for this user.') ?></div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (!$bAdminOtherUser) {
                    ?>
                    <div class="form-group">
                        <label for="OldPassword" class="text-muted small font-weight-bold text-uppercase"><?= _('Old Password') ?></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="OldPassword" id="OldPassword" class="form-control" value="<?= $sOldPassword ?>" autofocus>
                        </div>
                        <?= $sOldPasswordError ?>
                    </div>
                    <?php
                } ?>
                    <div class="form-group">
                        <label for="NewPassword1" class="text-muted small font-weight-bold text-uppercase"><?= _('New Password') ?></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="password" name="NewPassword1" id="NewPassword1" class="form-control" value="<?= $sNewPassword1 ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="NewPassword2" class="text-muted small font-weight-bold text-uppercase"><?= _('Confirm New Password') ?></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            </div>
                            <input type="password" name="NewPassword2" id="NewPassword2" class="form-control" value="<?= $sNewPassword2 ?>">
                        </div>
                        <?= $sNewPasswordError ?>
                    </div>
                </div>

                <div class="card-footer border-0 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary" name="Submit">
                        <i class="fas fa-save mr-1"></i><?= _('Save') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>