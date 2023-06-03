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

<div class="card card-primary">
    <div class="card-body">
        <p class="text-right">
            <?php
                if (SessionUser::getUser()->isAdmin()) {
            ?>
              <a href="<?= $sRootPath ?>/v2/query/sql" class="text-red"><?= _('Run a Free-Text Query') ?></a>
            <?php
                }
            ?>
        </p>

        <ul>
            <?php
                $query_type = 0;
                $first_time = true;
                $open_ul = false;
                $count = 0;

                while ($aRow = $statement->fetch( \PDO::FETCH_ASSOC )) {
                    extract($aRow);

                    if ($qry_Type_ID != $query_type) {
                      if ($first_time == false) {
                        if ($count == 0) {
                ?>
                        <li><?= _("Forbidden") ?>
                <?php
                        }
                        $count = 0;
                ?>
                        </ul></li>
                <?php
                      }
                ?>
                      <li><b><?= mb_convert_case(_($qry_type_Category), MB_CASE_UPPER, "UTF-8") ?></b><br>
                      <ul>
                      <?php
                      $query_type = $qry_Type_ID;
                      $first_time = false;
                    }

                    // Filter out finance-related queries if the user doesn't have finance permissions
                    if (SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') && in_array($qry_ID, $aFinanceQueries) || !in_array($qry_ID, $aFinanceQueries)) {
                        // Display the query name and description
                    ?>
                    <li>
                        <a href="<?= $sRootPath ?>/v2/query/view/<?= $qry_ID ?>"><?= _($qry_Name) ?></a>:
                        <br>
                        <?= _($qry_Description) ?>
                    </li>
                <?php
                        $count++;
                    }
                }
                ?>
        </ul>
    </div>

</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
