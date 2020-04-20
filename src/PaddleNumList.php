<?php
/*******************************************************************************
 *
 *  filename    : PaddleNumList.php
 *  last change : 2009-04-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;

$linkBack = InputUtils::LegacyFilterInputArr($_GET, 'linkBack');

$iFundRaiserID = $_SESSION['iCurrentFundraiser'];

if ($iFundRaiserID > 0) {
    //Get the paddlenum records for this fundraiser
    $sSQL = "SELECT pn_ID, pn_fr_ID, pn_Num, pn_per_ID,
	                a.per_FirstName as buyerFirstName, a.per_LastName as buyerLastName
	         FROM paddlenum_pn
	         LEFT JOIN person_per a ON pn_per_ID=a.per_ID
	         WHERE pn_FR_ID = '".$iFundRaiserID."' ORDER BY pn_Num";
    $rsPaddleNums = RunQuery($sSQL);
} else {
    $rsPaddleNums = 0;
}

$sPageTitle = _('Buyers for this fundraiser:');
require 'Include/Header.php';
?>
<div class="card card-body">
<?php
echo "<form method=\"post\" action=\"Reports/FundRaiserStatement.php?CurrentFundraiser=$iFundRaiserID&linkBack=FundRaiserEditor.php?FundRaiserID=$iFundRaiserID&CurrentFundraiser=$iFundRaiserID\">\n";
if ($iFundRaiserID > 0) {
    echo '<input type=button class="btn btn-default btn-sm" value="'._('Select all')."\" name=SelectAll onclick=\"javascript:document.location='PaddleNumList.php?CurrentFundraiser=$iFundRaiserID&SelectAll=1&linkBack=PaddleNumList.php?FundRaiserID=$iFundRaiserID&CurrentFundraiser=$iFundRaiserID';\">\n";
}
    echo '<input type=button class="btn btn-default btn-sm" value="'._('Select none')."\" name=SelectNone onclick=\"javascript:document.location='PaddleNumList.php?CurrentFundraiser=$iFundRaiserID&linkBack=PaddleNumList.php?FundRaiserID=$iFundRaiserID&CurrentFundraiser=$iFundRaiserID';\">\n";
    echo '<input type=button class="btn btn-primary btn-sm" value="'._('Add Buyer')."\" name=AddBuyer onclick=\"javascript:document.location='PaddleNumEditor.php?CurrentFundraiser=$iFundRaiserID&linkBack=PaddleNumList.php?FundRaiserID=$iFundRaiserID&CurrentFundraiser=$iFundRaiserID';\">\n";
    echo '<input type=submit class="btn btn-info btn-sm" value="'._('Generate Statements for Selected')."\" name=GenerateStatements>\n";
?>
</div>
<div class="card card-body">

<table cellpadding="5" cellspacing="5" class="table table-striped table-bordered dataTable no-footer dtr-inline">

<tr class="TableHeader">
	<td><?= _('Select') ?></td>
	<td><?= _('Number') ?></td>
	<td><?= _('Buyer') ?></td>
	<td><?= _('Delete') ?></td>
</tr>

<?php
$tog = 0;

//Loop through all buyers
if ($rsPaddleNums) {
    while ($aRow = mysqli_fetch_array($rsPaddleNums)) {
        extract($aRow);

        $sRowClass = 'RowColorA'; ?>
		<tr class="<?= $sRowClass ?>">
			<td>
				<input type="checkbox" name="Chk<?= $pn_ID.'"';
        if (isset($_GET['SelectAll'])) {
            echo ' checked="yes"';
        } ?>></input>
			</td>
			<td>
				<?= "<a href=\"PaddleNumEditor.php?PaddleNumID=$pn_ID&linkBack=PaddleNumList.php\"> $pn_Num</a>\n" ?>
			</td>

			<td>
				<?= $buyerFirstName.' '.$buyerLastName ?>&nbsp;
			</td>
			<td>
				<a href="PaddleNumDelete.php?PaddleNumID=<?= $pn_ID.'&linkBack=PaddleNumList.php?FundRaiserID='.$iFundRaiserID ?>"> <i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>
			</td>
		</tr>
	<?php
    } // while
} // if
?>

</table>
  </div>
</form>

<?php require 'Include/Footer.php' ?>
