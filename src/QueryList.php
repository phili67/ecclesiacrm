<?php
/*******************************************************************************
 *
 *  filename    : QueryList.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *  Copyright   : 2018 Philippe Logel
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;

if ( !( $_SESSION['user']->isShowMenuQueryEnabled() ) ) {
    Redirect('Menu.php');
    exit;
}

//Set the page title
$sPageTitle = gettext('Query Listing');

$sSQL = 'SELECT * FROM query_qry LEFT JOIN query_type ON query_qry.qry_Type_ID = query_type.qry_type_id ORDER BY query_qry.qry_Type_ID, query_qry.qry_Name';
$rsQueries = RunQuery($sSQL);

$aFinanceQueries = explode(',', SystemConfig::getValue('aFinanceQueries'));

require 'Include/Header.php';

?>
<div class="box box-primary">
    <div class="box-body">
        <p class="text-right">
            <?php
                if ($_SESSION['user']->isAdmin()) {
            ?>
              <a href="QuerySQL.php" class="text-red"><?= gettext('Run a Free-Text Query') ?></a>
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
                
                while ($aRow = mysqli_fetch_array($rsQueries)) {?>            
                <?php
                    extract($aRow);
                    
                    if ($qry_Type_ID != $query_type) {
                      if ($first_time == false) {
                        if ($count == 0) {
                ?>
                        <li><?= gettext("Forbidden") ?>
                <?php
                        }
                        $count = 0;
                ?>
                        </ul></li>
                <?php
                      }
                ?>
                      <li><b><?= mb_convert_case(gettext($qry_type_Category), MB_CASE_UPPER, "UTF-8") ?></b><br>
                      <ul>
                      <?php
                      $query_type = $qry_Type_ID;
                      $first_time = false;
                    }

                    // Filter out finance-related queries if the user doesn't have finance permissions
                    if ($_SESSION['user']->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') && in_array($qry_ID, $aFinanceQueries) || !in_array($qry_ID, $aFinanceQueries)) {
                        // Display the query name and description
                    ?>
                    <li>
                        <a href="QueryView.php?QueryID=<?= $qry_ID ?>"><?= gettext($qry_Name) ?></a>:
                        <br>
                        <?= gettext($qry_Description) ?>
                    </li>
                <?php
                        $count++;
                    }
                } 
                ?>
        </ul>
    </div>
    
</div>
<?php

require 'Include/Footer.php';