<?php
/*******************************************************************************
 *
 *  filename    : QueryList.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

//Set the page title
$sPageTitle = gettext('Query Listing');

$sSQL = 'SELECT * FROM query_qry ORDER BY qry_Type_ID, qry_Name';
$rsQueries = RunQuery($sSQL);

$aFinanceQueries = explode(',', $aFinanceQueries);

require 'Include/Header.php';

?>
<div class="box box-primary">
    <div class="box-body">
        <p class="text-right">
            <?php
                if ($_SESSION['bAdmin']) {
                    echo '<a href="QuerySQL.php" class="text-red">'.gettext('Run a Free-Text Query').'</a>';
                }
            ?>
        </p>
        
        <ul>
            <?php 
                $query_type = 0;
                $first_time = true;
                $open_ul = false;
                
                while ($aRow = mysqli_fetch_array($rsQueries)) {?>            
                <?php
                    extract($aRow);
                    
                    if ($qry_Type_ID != $query_type) {
                      // We search the name of the type
                      $sSQL = 'SELECT qry_type_Category FROM query_type WHERE qry_type_id='.$qry_Type_ID;
                      $rsQueryTypes = RunQuery($sSQL);
                      
                      $row = mysqli_fetch_row($rsQueryTypes);
                      
                      if ($first_time == false) {
                        echo "</ul></li>";
                      }
                      
                      echo "<li><b>".mb_convert_case(gettext($row[0]), MB_CASE_UPPER, "UTF-8")."</b><br>";
                      echo "<ul>";
                      
                      $query_type = $qry_Type_ID;
                      
                      $first_time = false;
                    }
                    
                    echo "<li>";
                    // Filter out finance-related queries if the user doesn't have finance permissions
                    if ($_SESSION['bFinance'] || !in_array($qry_ID, $aFinanceQueries)) {
                        // Display the query name and description
                        echo '<a href="QueryView.php?QueryID='.$qry_ID.'">'.gettext($qry_Name).'</a>:';
                        echo '<br>';
                        echo gettext($qry_Description);
                    }
                    echo "</li>";
                ?>
            <?php } ?>
        </ul>
    </div>
    
</div>
<?php

require 'Include/Footer.php';