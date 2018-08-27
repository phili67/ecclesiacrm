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
$sPageTitle = _('Property Type List');

$ormPropertyTypes = PropertyTypeQuery::Create()
  ->leftJoinProperty()
  ->groupByPrtId()
  ->groupByPrtClass()
  ->groupByPrtName()
  ->find();


require 'Include/Header.php';
?>
<div class="box box-body">
    <div class="table-responsive">
<?php //Display the new property link
if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
    <p align="center"><a class='btn btn-primary' href="<?= SystemURLs::getRootPath() ?>/PropertyTypeEditor.php"><?= _('Add a New Property Type') ?></a></p>
<?php
}

//Start the table
?>
<table class="table table-hover dt-responsive dataTable no-footer dtr-inline" id="property-listing-table" style="width:100%">
<thead>
<tr>
<?php
if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
   <th><?= _('Action') ?></th>
<?php
}
?>
   <th><?= _('Name') ?></th>
   <th><?= _('Class') ?></th>
   <th><?= _('Description') ?></th>
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
    <td><?= _($ormPropertyType->getPrtName()) ?></td>
    <td>
<?php
  if ($ormPropertyType->getPrtName() == 'Menu') {
?>
    <?= _('Sunday School Sub Menu') ?></td>
<?php
   if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
    <td></td>
<?php
   }
?>
<?php
  } else {
      switch ($ormPropertyType->getPrtClass()) { case 'p': echo _('Person'); break; case 'f': echo _('Family'); break; case 'g': echo _('Group'); break;}
?>
      </td>
      <td><?= _($ormPropertyType->getPrtDescription()) ?></td>
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

