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
 use EcclesiaCRM\dto\SystemURLs;
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
        $sRowClass = 'RowColorA';

        $aHiddenFormField = [];

        echo '<table align="center" cellpadding="5" cellspacing="0" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline">';

        echo '<tr class="'.$sRowClass.'">';

        //Loop through the fields and write the header row
        for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
            //If this field is called "AddToCart", don't display this field...
            $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);
            if ($fieldInfo->name != 'AddToCart') {
                echo '  <td align="center">
							<b>'.$fieldInfo->name.'</b>
							</td>';
            }
        }

        echo '</tr>';

        //Loop through the recordsert
        while ($aRow = mysqli_fetch_array($rsQueryResults)) {
            $sRowClass = MiscUtils::AlternateRowStyle($sRowClass);

            echo '<tr class="'.$sRowClass.'">';

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
                    echo '<td align="center">'.$aRow[$iCount].'</td>';
                }
            }
            echo '</tr>';
        }

        echo '</table>';
        echo '<p align="center">';

        if (count($aHiddenFormField) > 0) {
            ?>
			<form method="post" action="<?= SystemURLs::getRootPath() ?>/v2/cart/view"><p align="center">
				<input type="hidden" value="<?= implode(',', $aHiddenFormField) ?>" name="BulkAddToCart">
				<input type="submit" class="btn btn-default" name="AddToCartSubmit" value="<?php echo _('Add Results To Cart'); ?>">&nbsp;
				<input type="submit" class="btn btn-default" name="AndToCartSubmit" value="<?php echo _('Intersect Results With Cart'); ?>">&nbsp;
				<input type="submit" class="btn btn-default" name="NotToCartSubmit" value="<?php echo _('Remove Results From Cart'); ?>">
			</p></form>
			<?php
        }
        ?>
            <p align="center"><a href="<?= SystemURLs::getRootPath() ?>/v2/query/list"><?= _('Return to Query Menu') ?></a></p>
            <br><p class="ShadedBox" style="border-style: solid; margin-left: 50px; margin-right: 50px; border-width: 1px;"><span class="SmallText"><?= str_replace(chr(13),'<br>', htmlspecialchars($sSQL)) ?></span></p>
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

<form method="post">

    <center><table><tr>
        <td class="LabelColumn"> <?= _('Export Results to CSV file') ?> </td>
        <td class="TextColumn"><input name="CSV" type="checkbox" id="CSV" value="1"></td>
    </tr></table></center>

    <p align="center">
        <textarea style="font-family:courier,fixed; font-size:9pt; padding:1px;" cols="60" rows="20" name="SQL" class="form-control"><?= $sSQL ?></textarea>
    </p>
    <p align="center">
        <input type="submit" class="btn btn-primary" name="Submit" value="<?= _('Execute SQL') ?>">
    </p>

    </form>
<?php


if (isset($_POST['SQL'])) {
    if (strtolower(mb_substr($sSQL, 0, 6)) == 'select') {
        RunFreeQuery($cnInfoCentral, $aRowClass, $rsQueryResults, $sSQL, $iQueryID);
    }
}

//Display the count of the recordset
echo '<p align="center">';
if ($rsQueryResults != false) {
    echo mysqli_num_rows($rsQueryResults) . _(' record(s) returned');
}
echo '</p>';
?>

<script nonce="<?= $CSPNonce ?>">
    $(function() {
        window.CRM.queryTable = $("#query-table").DataTable(window.CRM.plugin.dataTable);
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
