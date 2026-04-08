<?php
// Include the function library
$bSuppressSessionTests = true; // DO NOT MOVE

// Set the page title and include HTML header
require $sRootDocument . '/Include/HeaderNotLoggedIn.php'; ?>

<section class="content pt-4">
    <div class="container-fluid px-0">
        <div class="row">
            <div class="col-12">

                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-tools text-warning mr-2"></i><?= _('Upgrade Required') ?>
                        </h3>
                        <span class="badge badge-warning">426</span>
                    </div>
                    <div class="card-body">

                        <div class="alert alert-light border mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="text-muted small"><?= _('Current DB Version') ?></div>
                                    <div class="font-weight-bold"><?= $dbVersion ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small"><?= _('Current Software Version') ?></div>
                                    <div class="font-weight-bold"><?= $InstalledVersion ?></div>
                                </div>
                            </div>
                        </div>

                        <p class="mb-4 text-secondary">
                            <?= _('Update and clean up the database and files') ?>
                        </p>

                        <?php if (empty($errorMessage)) { ?>
                            <form action="<?= $sRootPath ?>/v2/system/database/update/1" method="post">
                                <input type="hidden" name="upgrade" value="true"/>
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-database mr-1"></i>
                                    <i class="fas fa-file mr-1"></i>
                                    <i class="fas fa-folder mr-2"></i>
                                    <?= _('Complete the update') ?>
                                </button>
                            </form>
                        <?php } else { ?>
                            <div class="main-box-body clearfix" id="globalMessage">
                                <div class="alert alert-danger mb-0" id="globalMessageAlert">
                                    <i class="fas fa-exclamation-triangle fa-lg mr-2"></i>
                                    <span id="globalMessageText"><?= $errorMessage ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<?php require $sRootDocument . '/Include/FooterNotLoggedIn.php'; ?>
