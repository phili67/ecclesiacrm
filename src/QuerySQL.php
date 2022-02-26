<?php
/*******************************************************************************
 *
 *  filename    : QuerySQL.php
 *  last change : 2003-01-04
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002, 2003 Deane Barker, Chris Gebhardt
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;

//Set the page title
$sPageTitle = _('Free-Text Query');

// Security: User must be an Admin to access this page.  It allows unrestricted database access!
// Otherwise, re-direct them to the main menu.
if (!SessionUser::getUser()->isAdmin()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if (isset($_POST['SQL'])) {
    //Assign the value locally
    $sSQL = stripslashes(trim($_POST['SQL']));
} else {
    $sSQL = '';
}

if (isset($_POST['CSV'])) {
    ExportQueryResults();
    exit;
}

$rsQueryResults = false;

require 'Include/Header.php';
?>

<form method="post">

<center><table><tr>
    <td class="LabelColumn"> <?= _('Export Results to CSV file') ?> </td>
    <td class="TextColumn"><input name="CSV" type="checkbox" id="CSV" value="1"></td>
</tr></table></center>

<p align="center">
	<textarea style="font-family:courier,fixed; font-size:9pt; padding:1px;" cols="60" rows="10" name="SQL"><?= $sSQL ?></textarea>
</p>
<p align="center">
	<input type="submit" class="btn btn-default" name="Submit" value="<?= _('Execute SQL') ?>">
</p>

</form>

<?php


if (isset($_POST['SQL'])) {
    if (strtolower(mb_substr($sSQL, 0, 6)) == 'select') {
        RunFreeQuery();
    }
}

function ExportQueryResults()
{
    global $cnInfoCentral;
    global $aRowClass;
    global $rsQueryResults;
    global $sSQL;
    global $iQueryID;

    $sCSVstring = '';

    //Run the SQL
    $rsQueryResults = MiscUtils::RunQuery($sSQL);

    if (mysqli_error($cnInfoCentral) != '') {
        $sCSVstring = _('An error occured: ').mysqli_errno($cnInfoCentral).'--'.mysqli_error($cnInfoCentral);
    } else {

        //Loop through the fields and write the header row
        for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
            $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);
            $sCSVstring .= $fieldInfo->name.',';
        }

        $sCSVstring .= "\n";

        //Loop through the recordsert
        while ($aRow = mysqli_fetch_array($rsQueryResults)) {
            //Loop through the fields and write each one
            for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
                $outStr = str_replace('"', '""', $aRow[$iCount]);
                $sCSVstring .= '"'.$outStr.'",';
            }

            $sCSVstring .= "\n";
        }
    }

    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename=Query-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo $sCSVstring;
    exit;
}

//Display the count of the recordset
    echo '<p align="center">';
    if ($rsQueryResults != false) {
        echo mysqli_num_rows($rsQueryResults) . _(' record(s) returned');
    }
    echo '</p>';

function RunFreeQuery()
{
    global $cnInfoCentral;
    global $aRowClass;
    global $rsQueryResults;
    global $sSQL;
    global $iQueryID;

    //Run the SQL
    $rsQueryResults = MiscUtils::RunQuery($sSQL);

    if (mysqli_error($cnInfoCentral) != '') {
        echo _('An error occured: ').mysqli_errno($cnInfoCentral).'--'.mysqli_error($cnInfoCentral);
    } else {
        $sRowClass = 'RowColorA';

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

        echo '<p align="center"><a href="<?= SystemURLs::getRootPath() ?>/QueryList.php">'._('Return to Query Menu').'</a></p>';
        echo '<br><p class="ShadedBox" style="border-style: solid; margin-left: 50px; margin-right: 50px; border-width: 1px;"><span class="SmallText">'.str_replace(chr(13), '<br>', htmlspecialchars($sSQL)).'</span></p>';
    }
}

require 'Include/Footer.php';
?>
