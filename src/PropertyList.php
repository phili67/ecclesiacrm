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

//Get the type to display
$sType = InputUtils::LegacyFilterInput($_GET['Type'], 'char', 1);

//Based on the type, set the TypeName
switch ($sType) {
    case 'p':
        $sTypeName = gettext('Person');
        break;

    case 'f':
        $sTypeName = gettext('Family');
        break;

    case 'g':
        $sTypeName = gettext('Group');
        break;

    case 'm':
        $sTypeName = gettext('Menu');
        break;

    default:
        Redirect('Menu.php');
        exit;
        break;
}

//Set the page title
$sPageTitle = $sTypeName.' '.gettext('Property List');

//Get the properties
$sSQL = "SELECT * FROM property_pro, propertytype_prt WHERE prt_ID = pro_prt_ID AND pro_Class = '".$sType."' ORDER BY prt_Name,pro_Name";
$rsProperties = RunQuery($sSQL);

require 'Include/Header.php'; ?>

<div class="box box-body">

<?php 
   if ($_SESSION['user']->isMenuOptionsEnabled()) {
    //Display the new property link
?>
    <p align="center"><a class='btn btn-primary' href="PropertyEditor.php?Type=<?=$sType?>"><?= gettext('Add a New') ?> <?= $sTypeName?> <?= gettext('Property') ?></a></p>
<?php
}

//Start the table
?>

<table class='table'>
<tr>
<th valign="top"><?= gettext('Name') ?></th>
<th valign="top"><?= gettext('A')?> <?= $sTypeName ?> <?= gettext('with this Property...') ?></b></th>
<th valign="top"><?= gettext('Prompt') ?></th>

<?php
if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
    <td valign="top"><b><?= gettext('Edit') ?></b></td>
    <td valign="top"><b><?= gettext('Delete') ?></b></td>
    
<?php
}
?>
</tr>

<tr><td>&nbsp;</td></tr>

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
        <?= $sBlankLine ?>
        <tr class="RowColorA"><td colspan="5"><b><?= $prt_Name ?></b></td></tr>
<?php
        $sBlankLine = '<tr><td>&nbsp;</td></tr>';

        //Reset the row color
        $sRowClass = 'RowColorA';
    }

    $sRowClass = AlternateRowStyle($sRowClass);
    
?>

    <tr class="<?= $sRowClass ?>">
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
<?php
    if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
        <td valign="top"><a class='btn btn-success' href="PropertyEditor.php?PropertyID=<?= $pro_ID?>&Type=<?= $sType ?>"><?= gettext('Edit') ?></a></td>
        <td valign="top"><a class='btn btn-danger' href="PropertyDelete.php?PropertyID=<?= $pro_ID?>&Type=<?= $sType ?>"><?= gettext('Delete') ?></a></td>
<?php
    }
?>
    </tr>
<?php
    //Store the PropertyType
    $iPreviousPropertyType = $prt_ID;
}

//End the table
?>

</table></div>

<?php
require 'Include/Footer.php';
?>