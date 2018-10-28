<?php
/*******************************************************************************
 *
 *  filename    : PropertyTypeList.php
 *  last change : 2003-03-27
 *  website     : http://www.churchcrm.io
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                Copyright 2018
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\PropertyTypeQuery;
use EcclesiaCRM\dto\SystemURLs;

// Set the page title
$sPageTitle = gettext('Property Type List');

$ormPropertyTypes = PropertyTypeQuery::Create()
  ->leftJoinProperty()
  ->groupByPrtId()
  ->groupByPrtClass()
  ->groupByPrtName()
  ->find();

if ( !( $_SESSION['user']->isMenuOptionsEnabled() ) ) {
    Redirect('Menu.php');
    exit;
}

require 'Include/Header.php';

//Display the new property link
if ( $_SESSION['user']->isMenuOptionsEnabled()) {
?>
    <p align="center"><a class='btn btn-primary' href="<?= SystemURLs::getRootPath() ?>/PropertyTypeEditor.php"><?= gettext('Add a New Property Type') ?></a></p>
<?php
} else {
?>
    <div class="callout callout-warning"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>

<div class="callout callout-danger"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Be carefull ! By deleting properties, all persons, families and groups will be affected.') ?></div>

<div class="box box-body">
    <div class="table-responsive">
<?php
//Start the table
?>
<table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="property-listing-table" style="width:100%">
<thead>
<tr>
<?php
if ( $_SESSION['user']->isMenuOptionsEnabled()) {
?>
   <th><?= gettext('Action') ?></th>
<?php
}
?>
   <th><?= gettext('Name') ?></th>
   <th><?= gettext('Class') ?></th>
   <th><?= gettext('Description') ?></th>
</tr>
</thead>
</tbody>
<?php
//Initalize the row shading
$sRowClass = 'RowColorA';

//Loop through the records
foreach ($ormPropertyTypes as $ormPropertyType)
{
    $sRowClass = AlternateRowStyle($sRowClass);
    
?>

  <tr>
<?php
  $activLink = "";
  
  if ($ormPropertyType->getPrtName() == 'Menu')
    $activLink = " disabled";

  if ($ormPropertyType->getPrtName() == 'Menu') {
?>
   <td></td>
<?php
    } else {
      if ($_SESSION['user']->isMenuOptionsEnabled()) {
?> 
     <td>
       <a href="<?= SystemURLs::getRootPath() ?>/PropertyTypeEditor.php?PropertyTypeID=<?= $ormPropertyType->getPrtId() ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
        
<?php
        if ($Properties == 0) {
?>
            &nbsp;&nbsp;&nbsp;<a href="<?= SystemURLs::getRootPath() ?>/PropertyTypeDelete.php?PropertyTypeID=<?= $ormPropertyType->getPrtId() ?>"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>
<?php
        } else {
?>
            &nbsp;&nbsp;&nbsp;<a href="<?= SystemURLs::getRootPath() ?>/PropertyTypeDelete.php?PropertyTypeID=<?= $ormPropertyType->getPrtId()?>&Warn"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>
<?php
        }
?>
   </td>
<?php
    }
  }
?>
    <td><?= gettext($ormPropertyType->getPrtName()) ?></td>
    <td>
<?php
  if ($ormPropertyType->getPrtName() == 'Menu') {
?>
    <?= gettext('Sunday School Sub Menu') ?></td>
<?php
   if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
    <td></td>
<?php
   }
?>
<?php
  } else {
      switch ($ormPropertyType->getPrtClass()) { case 'p': echo gettext('Person'); break; case 'f': echo gettext('Family'); break; case 'g': echo gettext('Group'); break;}
?>
      </td>
      <td><?= gettext($ormPropertyType->getPrtDescription()) ?></td>
<?php
  }
?>
    </tr>
<?php
  }

//End the table
?>
</tbody>
</table>
</div>
</div>


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

