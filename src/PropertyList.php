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
use EcclesiaCRM\PropertyTypeQuery;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


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
        RedirectUtils::Redirect('Menu.php');
        exit;
        break;
}

//Set the page title
$sPageTitle = $sTypeName.' : '._('Property List');

// We need the properties types
$ormPropertyTypes = PropertyTypeQuery::Create()
                      ->filterByPrtClass($sType)
                      ->find();

require 'Include/Header.php'; ?>

<div class="box box-body">

<?php 
   if (SessionUser::getUser()->isMenuOptionsEnabled()) {
    //Display the new property link
?>
    <p align="center"><a class='btn btn-primary' href="#" id="add-new-prop"><?= _('Add a New') ?> <?= $sTypeName?> <?= _('Property') ?></a></p>
<?php
}

//Start the table
?>
<table class='table table-hover dt-responsive dataTable no-footer dtr-inline' id="property-listing-table-v2"></table>
</div>

<?php
require 'Include/Footer.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.menuOptionEnabled = <?= (SessionUser::getUser()->isMenuOptionsEnabled())?'true':'false' ?>;
  window.CRM.propertyType      = "<?= $sType ?>";
  window.CRM.propertyTypeName  = "<?= $sTypeName ?>";
  window.CRM.propertyTypesAll  = <?= json_encode($ormPropertyTypes->toArray()) ?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/sidebar/PropertyList.js" ></script>