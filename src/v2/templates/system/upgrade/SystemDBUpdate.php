<?php
// Include the function library
$bSuppressSessionTests = true; // DO NOT MOVE

// Set the page title and include HTML header
require $sRootDocument . '/Include/HeaderNotLoggedIn.php'; ?>

<p></br></p>

<div class="error-page">
    <div class="row">
        <div class="col-3"><h1 class="headline text-green" style="font-size:60px">426</h1></div>
        <div class="col-6">
            <div class="error-content">
                <div class="row">
                    <h3><i class="fas fa-exclamation-triangle text-green"></i> <?= _('Upgrade Required') ?></h3>
                    <p>
                        <?= _("Current DB Version") . ": " . $dbVersion ?> <br/>
                        <?= _("Current Software Version") . ": " . $InstalledVersion ?> <br/>
                    </p>
                    <h5><?= _("Update and clean up the database and files") ?></h5>
                </div>
            </div>
        </div>
        <div class="col-3"></div>
    </div>
    <?php if (empty($errorMessage)) {
    ?>
        <div class="row">
            <div class="col-12">
                <p></br></p>                
                <form action="<?= $sRootPath ?>/v2/system/database/update/1" method="post">
                    <input type="hidden" name="upgrade" value="true"/>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-database"></i> <i class="fas fa-file"></i> <i class="fas fa-folder"></i> 
                        <?= _("Complete the update") ?>
                    </button>
                </form>
            </div>
        </div>
    <?php
} else {
        ?>
        <div class="main-box-body clearfix" id="globalMessage">
            <div class="alert alert-danger" id="globalMessageAlert">
                <i class="fas fa-exclamation-triangle  fa-lg"></i> <?= $errorMessage ?>
            </div>
        </div>
    <?php
    } ?>
</div>


<?php require $sRootDocument . '/Include/FooterNotLoggedIn.php'; ?>
