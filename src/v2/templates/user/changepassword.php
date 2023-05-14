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
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="fas fa-ban"></i> <?= _("Alert") ?>!</h4>
        <?= _('Your account record indicates that you need to change your password before proceding.') ?>
    </div>
    <?php
} 
?>

<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="card card-secondary">
            <div class="card-header border-1">
                <?php
                  if (!$bAdminOtherUser) {
                ?>
                    <h3 class="card-title"><?= _('Enter your current password, then your new password twice.  Passwords must be at least').' '.SystemConfig::getValue('iMinPasswordLength').' '._('characters in length.') ?></h3>
                <?php
                  } else {
                ?>
                    <h3 class="card-title"><?= _('Enter a new password for this user.') ?></h3>
                <?php
                  }
                ?>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form method="post" action="<?= $sRootPath ?>/v2/users/change/password/<?= $iPersonID ?><?= $FromUserList?'/FromUserList':'' ?>">
                <div class="card-body">
                    <?php if (!$bAdminOtherUser) {
                    ?>
                    <div class="form-group">
                        <label for="OldPassword"><?= _('Old Password') ?>:</label>
                        <input type="password" name="OldPassword" id="OldPassword" class= "form-control form-control-sm" value="<?= $sOldPassword ?>" autofocus><?= $sOldPasswordError ?>
                    </div>
                    <?php
                } ?>
                    <div class="form-group">
                            <label for="NewPassword1"><?= _('New Password') ?>:</label>
                        <input type="password" name="NewPassword1" id="NewPassword1" class= "form-control form-control-sm" value="<?= $sNewPassword1 ?>">
                    </div>
                    <div class="form-group">
                        <label for="NewPassword2"><?= _('Confirm New Password') ?>:</label>
                        <input type="password" name="NewPassword2" id="NewPassword2"  class= "form-control form-control-sm" value="<?= $sNewPassword2 ?>"><?= $sNewPasswordError ?>
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="card-footer">
                    <input type="submit" class="btn btn-primary" name="Submit" value="<?= _('Save') ?>">
                </div>
            </form>
        </div>
    </div>
</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>