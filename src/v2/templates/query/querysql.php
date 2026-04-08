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

 use EcclesiaCRM\dto\SystemConfig;
 use EcclesiaCRM\Utils\MiscUtils;
 use EcclesiaCRM\SessionUser;

 // to get $cnInfoCentral
require $sRootDocument . '/Include/Header.php';

$cnInfoCentral = $GLOBALS['cnInfoCentral'];


function ExportQueryResults($cnInfoCentral, $aRowClass, $rsQueryResults, $sSQL, $iQueryID)
{
    $delimiter = SessionUser::getUser()->CSVExportDelemiter();
    $charset   = SessionUser::getUser()->CSVExportCharset();

    $sCSVstring = '';

    //Run the SQL
    $rsQueryResults = MiscUtils::RunQuery($sSQL);

    if (mysqli_error($cnInfoCentral) != '') {
        $sCSVstring = _('An error occured: ').mysqli_errno($cnInfoCentral).'--'.mysqli_error($cnInfoCentral);
    } else {

        //Loop through the fields and write the header row
        for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
            $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);
            $sCSVstring .= $fieldInfo->name.$delimiter;
        }

        $sCSVstring .= "\n";

        //Loop through the recordsert
        while ($aRow = mysqli_fetch_array($rsQueryResults)) {
            //Loop through the fields and write each one
            for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
                $outStr = str_replace('"', '""', $aRow[$iCount]);
                $sCSVstring .= '"'.$outStr.'"'.$delimiter;
            }

            $sCSVstring .= "\n";
        }
    }

    ob_clean();

    ob_start();
            
    header('Content-type: application/csv;charset='.$charset);
    header('Content-Disposition: attachment; filename=Query-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    echo $sCSVstring;

    $result = ob_get_clean();

    echo $result;
    exit;
}

function RunFreeQuery($cnInfoCentral, $aRowClass, $rsQueryResults, $sSQL, $iQueryID)
{
    //Run the SQL
    $rsQueryResults = MiscUtils::RunQuery($sSQL);

    if (mysqli_error($cnInfoCentral) != '') {
        echo _('An error occured: ').mysqli_errno($cnInfoCentral).'--'.mysqli_error($cnInfoCentral);
    } else {
        $aHiddenFormField = [];

        echo '<div class="card card-outline card-info mt-3">';
        echo '  <div class="card-header py-2 d-flex justify-content-between align-items-center">';
        echo '    <h3 class="card-title mb-0"><i class="fas fa-table mr-1"></i>' . _('Query Results') . '</h3>';
        echo '    <span class="badge badge-secondary">' . mysqli_num_rows($rsQueryResults) . ' ' . _('record(s) returned') . '</span>';
        echo '  </div>';
        echo '  <div class="card-body p-2">';
        echo '    <div class="table-responsive">';
        echo '      <table id="query-table" class="table table-sm table-hover table-bordered mb-0">';
        echo '        <thead><tr>';

        //Loop through the fields and write the header row
        for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
            //If this field is called "AddToCart", don't display this field...
            $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);
            if ($fieldInfo->name != 'AddToCart') {
                echo '<th class="text-center">' . $fieldInfo->name . '</th>';
            }
        }

        echo '        </tr></thead>';
        echo '        <tbody>';

        //Loop through the recordsert
        while ($aRow = mysqli_fetch_array($rsQueryResults)) {
            echo '<tr>';

            //Loop through the fields and write each one
            for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
                //If this field is called "AddToCart", add this to the hidden form field...
                $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);
                if ($fieldInfo->name == 'AddToCart') {
                    $aHiddenFormField[] = $aRow[$iCount];
                }
                //...otherwise just render the field
                else {
                    //Write the actual value of this row
                    echo '<td class="text-center">'.$aRow[$iCount].'</td>';
                }
            }
            echo '</tr>';
        }

        echo '        </tbody>';
        echo '      </table>';
        echo '    </div>';
        echo '  </div>';

        echo '  <div class="card-footer py-2">';

        if (count($aHiddenFormField) > 0) {
            ?>
            <form method="post" action="<?= \EcclesiaCRM\dto\SystemURLs::getRootPath() ?>/v2/cart/view" class="mb-2">
				<input type="hidden" value="<?= implode(',', $aHiddenFormField) ?>" name="BulkAddToCart">
				<div class="btn-group btn-group-sm" role="group">
					<button type="submit" class="btn btn-outline-secondary" name="AddToCartSubmit"><?php echo _('Add Results To Cart'); ?></button>
					<button type="submit" class="btn btn-outline-secondary" name="AndToCartSubmit"><?php echo _('Intersect Results With Cart'); ?></button>
					<button type="submit" class="btn btn-outline-secondary" name="NotToCartSubmit"><?php echo _('Remove Results From Cart'); ?></button>
				</div>
			</form>
			<?php
        }
        ?>
            <a class="btn btn-link btn-sm p-0" href="<?= \EcclesiaCRM\dto\SystemURLs::getRootPath() ?>/v2/query/list"><?= _('Return to Query Menu') ?></a>
        </div>
        </div>

        <div class="card card-light mt-2 mb-3">
            <div class="card-header py-1"><strong><?= _('Executed SQL') ?></strong></div>
            <div class="card-body py-2">
                <pre class="mb-0 small"><?= htmlspecialchars($sSQL) ?></pre>
            </div>
        </div>
        <?php
    }
}

if (isset($_POST['SQL'])) {
    //Assign the value locally
    $sSQL = stripslashes(trim($_POST['SQL']));
} else {
    $sSQL = '';
}

if (isset($_POST['CSV'])) {
    ExportQueryResults($cnInfoCentral, $aRowClass, $rsQueryResults, $sSQL, $iQueryID);
    exit;
}

$rsQueryResults = false;

?>

<div class="card card-primary card-outline">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-database mr-1"></i><?= _('SQL Query Runner') ?></h3>
        <span class="badge badge-secondary"><?= _('Advanced') ?></span>
    </div>
    <form method="post">
        <div class="card-body">
            <div class="alert alert-info py-2 mb-3">
                <i class="fas fa-circle-info mr-1"></i><?= _('Run SELECT queries and optionally export results to CSV.') ?>
            </div>

            <div class="form-group form-check mb-2">
                <input name="CSV" type="checkbox" id="CSV" value="1" class="form-check-input">
                <label class="form-check-label" for="CSV"><?= _('Export Results to CSV file') ?></label>
            </div>

            <div class="form-group mb-0">
                <label for="SQL"><?= _('SQL Statement') ?></label>
                <textarea id="SQL" style="font-family:courier,fixed;" cols="60" rows="14" name="SQL" class="form-control"><?= $sSQL ?></textarea>
            </div>
        </div>
        <div class="card-footer py-2 d-flex justify-content-end">
            <input type="submit" class="btn btn-primary" name="Submit" value="<?= _('Execute SQL') ?>">
        </div>
    </form>
</div>
<?php


if (isset($_POST['SQL'])) {
    if (strtolower(mb_substr($sSQL, 0, 6)) == 'select') {
        RunFreeQuery($cnInfoCentral, $aRowClass, $rsQueryResults, $sSQL, $iQueryID);
    }
}

?>

<script nonce="<?= $CSPNonce ?>">
    $(function() {
        if ($("#query-table").length) {
            window.CRM.queryTable = $("#query-table").DataTable(window.CRM.plugin.dataTable);
        }
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
