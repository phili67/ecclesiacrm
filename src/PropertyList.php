<?php
/*******************************************************************************
 *
 *  filename    : PropertyList.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                          2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;

//Get the type to display
$sType = InputUtils::LegacyFilterInput($_GET['Type'], 'char', 1);

//Based on the type, set the TypeName
switch ($sType) {
    case 'p':
        $sTypeName = _('Person');
        break;

    case 'f':
        $sTypeName = _('Family');
        break;

    case 'g':
        $sTypeName = _('Group');
        break;

    case 'm':
        $sTypeName = _('Menu');
        break;

    default:
        Redirect('Menu.php');
        exit;
        break;
}

//Set the page title
$sPageTitle = $sTypeName.' '._('Property List');

//Get the properties
$sSQL = "SELECT * FROM property_pro, propertytype_prt WHERE prt_ID = pro_prt_ID AND pro_Class = '".$sType."' ORDER BY prt_Name,pro_Name";
$rsProperties = RunQuery($sSQL);

require 'Include/Header.php'; ?>

<div class="box box-body">

<?php 
   if ($_SESSION['user']->isMenuOptionsEnabled()) {
    //Display the new property link
?>
    <p align="center"><a class='btn btn-primary' href="PropertyEditor.php?Type=<?=$sType?>"><?= _('Add a New') ?> <?= $sTypeName?> <?= _('Property') ?></a></p>
<?php
}

//Start the table
?>

<table class='table table-hover dt-responsive dataTable no-footer dtr-inline' id="property-listing-table">
<thead>
<tr>
<?php
if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
    <td valign="top"><?= _('Action') ?></td>
<?php
}
?>
<th valign="top"><?= _('Name') ?></th>
<th valign="top"><?= _('A')?> <?= $sTypeName ?> <?= _('with this Property...') ?></b></th>
<th valign="top"><?= _('Prompt') ?></th>
</tr>
</thead>
<tbody>

<?php
//Initalize the row shading
$sRowClass = 'RowColorA';
$iPreviousPropertyType = -1;
$sBlankLine = '';

//Loop through the records
while ($aRow = mysqli_fetch_array($rsProperties)) {
    $pro_Prompt = '';
    $pro_Description = '';
    extract($aRow);

    //Did the Type change?
    if ($iPreviousPropertyType != $prt_ID) {

        //Write the header row
?>
        <tr class="RowColorA"><td><b><?= _($prt_Name) ?></b></td><td></td><td></td><td></td></tr>
<?php
        //Reset the row color
        $sRowClass = 'RowColorA';
    }

    $sRowClass = AlternateRowStyle($sRowClass);
    
?>

    <tr class="<?= $sRowClass ?>">
<?php
    if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
        <td valign="top">
        <a href="PropertyEditor.php?PropertyID=<?= $pro_ID?>&Type=<?= $sType ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
        &nbsp;&nbsp;&nbsp;<a href="PropertyDelete.php?PropertyID=<?= $pro_ID?>&Type=<?= $sType ?>"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>
        </td>
<?php
    } else {
?>
    <td></td>
<?php
    }
?>
    <td valign="top"><?= $pro_Name ?>&nbsp;</td>
    <td valign="top">
<?php
    if (strlen($pro_Description) > 0) {
?>
        ...<?= stripslashes($pro_Description) ?>
<?php
    }
?>
    &nbsp;</td>
    <td valign="top"><?= stripslashes($pro_Prompt) ?>&nbsp;</td>
    </tr>
<?php
    //Store the PropertyType
    $iPreviousPropertyType = $prt_ID;
}

//End the table
?>
</tbody>
</table></div>

<?php
require 'Include/Footer.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  $("#property-listing-table").DataTable({
       "language": {
         "url": window.CRM.plugin.dataTable.language.url
       },
       responsive: true,
       "order": [[ 2, "asc" ]]
  });
</script>