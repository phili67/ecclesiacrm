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
use EcclesiaCRM\PropertyQuery;

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
$sPageTitle = $sTypeName.' : '.gettext('Property List');

//Get the properties
$ormProperties = PropertyQuery::Create()
  ->leftJoinPropertyType()
  ->filterByProClass($sType)
  ->usePropertyTypeQuery()
    ->orderByPrtName()
  ->endUse()
  ->orderByProName()
  ->find();

require 'Include/Header.php'; ?>

<div class="box box-body">

<?php 
   if ($_SESSION['user']->isMenuOptionsEnabled()) {
    //Display the new property link
?>
    <p align="center"><a class='btn btn-primary' href="<?= SystemURLs::getRootPath() ?>/PropertyEditor.php?Type=<?=$sType?>"><?= gettext('Add a New') ?> <?= $sTypeName?> <?= gettext('Property') ?></a></p>
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
    <td valign="top"><?= gettext('Action') ?></td>
<?php
}
?>
<th valign="top"><?= gettext('Name') ?></th>
<th valign="top"><?= gettext('A')?> <?= $sTypeName ?> <?= gettext('with this Property...') ?></b></th>
<th valign="top"><?= gettext('Prompt') ?></th>
</tr>
</thead>
<tbody>

<?php
//Initalize the row shading
$sRowClass = 'RowColorA';
$iPreviousPropertyType = -1;
$sBlankLine = '';

//Loop through the records
foreach ($ormProperties as $ormProperty) {
    //Did the Type change?
    if ($iPreviousPropertyType != $ormProperty->getPropertyType()->getPrtId()) {

        //Write the header row
?>
        <tr class="RowColorA"><td><b><?= gettext($ormProperty->getPropertyType()->getPrtName()) ?></b></td><td></td><td></td>

<?php
    if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>        
        <td></td>
<?php 
    }
?>
</tr>
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
        <a href="<?= SystemURLs::getRootPath() ?>/PropertyEditor.php?PropertyID=<?= $ormProperty->getProId() ?>&Type=<?= $sType ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
        &nbsp;&nbsp;&nbsp;<a href="<?= SystemURLs::getRootPath() ?>/PropertyDelete.php?PropertyID=<?= $ormProperty->getProId()?>&Type=<?= $sType ?>"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>
      </td>
<?php
    }
?>
    <td valign="top"><?= $ormProperty->getProName() ?>&nbsp;</td>
    <td valign="top">
<?php
    if (strlen($ormProperty->getProDescription()) > 0) {
?>
        ...<?= stripslashes($ormProperty->getProDescription()) ?>
<?php
    }
?>
    &nbsp;</td>
    <td valign="top"><?= stripslashes($ormProperty->getProPrompt()) ?>&nbsp;</td>
    </tr>
<?php
    //Store the PropertyType
    $iPreviousPropertyType = $ormProperty->getPropertyType()->getPrtId();
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
       "order": [[ 1, "asc" ]]
  });
</script>