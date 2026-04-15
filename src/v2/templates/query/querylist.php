<?php
/*******************************************************************************
 *
 *  filename    : querylist.php
 *  last change : 2023-05-30
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                2023 Philippe Logel
 *
 ******************************************************************************/

 use EcclesiaCRM\SessionUser;
 use EcclesiaCRM\dto\SystemConfig;

require $sRootDocument . '/Include/Header.php';

?>

<div class="card card-primary card-outline">
    <div class="card-header border-1 d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-database mr-1"></i><?= _('Available Queries') ?></h3>
        <?php
            if (SessionUser::getUser()->isAdmin()) {
        ?>
            <a href="<?= $sRootPath ?>/v2/query/sql" class="btn btn-sm btn-outline-danger mt-2 mt-sm-0">
                <i class="fas fa-terminal mr-1"></i><?= _('Run a Free-Text Query') ?>
            </a>
        <?php
            }
        ?>
    </div>
    <div class="card-body">
        <div class="alert alert-light border d-flex align-items-start">
            <i class="fas fa-circle-info mt-1 mr-2"></i>
            <div class="mb-0">
                <strong><?= _('Tip') ?>:</strong>
                <span class="text-muted"><?= _('Choose a query category, then open a query to run it with or without parameters.') ?></span>
            </div>
        </div>

        <?php
            $query_type = 0;
            $first_time = true;
            $count = 0;

            while ($aRow = $statement->fetch(\PDO::FETCH_ASSOC)) {
                extract($aRow);

                if ($qry_Type_ID != $query_type) {
                    if ($first_time == false) {
                        if ($count == 0) {
        ?>
                        <li class="list-group-item text-muted"><?= _("Forbidden") ?></li>
        <?php
                        }
        ?>
                        </ul>
                    </div>
        <?php
                    }
        ?>
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h4 class="card-title text-uppercase mb-0"><?= mb_convert_case(_($qry_type_Category), MB_CASE_UPPER, "UTF-8") ?></h4>
                        </div>
                        <ul class="list-group list-group-flush">
        <?php
                    $query_type = $qry_Type_ID;
                    $first_time = false;
                    $count = 0;
                }

                // Filter out finance-related queries if the user doesn't have finance permissions
                if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') && in_array($qry_ID, $aFinanceQueries) || !in_array($qry_ID, $aFinanceQueries)) {
        ?>
                        <li class="list-group-item">
                            <a class="font-weight-bold" href="<?= $sRootPath ?>/v2/query/view/<?= $qry_ID ?>"><?= _($qry_Name) ?></a>
                            <div class="small text-muted mt-1"><?= _($qry_Description) ?></div>
                        </li>
        <?php
                    $count++;
                }
            }

            if ($first_time == false) {
                if ($count == 0) {
        ?>
                    <li class="list-group-item text-muted"><?= _("Forbidden") ?></li>
        <?php
                }
        ?>
                        </ul>
                    </div>
        <?php
            }
        ?>
    </div>

</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
