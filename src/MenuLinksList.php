<?php
/*******************************************************************************
 *
 *  filename    : MenuLinksList.php
 *  last change : 2018-08-25
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

$personId = 0;

if (isset($_GET['personId'])) {
  $personId = InputUtils::LegacyFilterInput($_GET['personId']);
}

if ( !($_SESSION['user']->isMenuOptionsEnabled() || $personId > 0 && $personId == $_SESSION['user']->getPersonId())) {
  Redirect('Menu.php');
  exit;
}

//Set the page title
$sPageTitle = gettext("Custom Menus List");

if ($personId > 0) {// we are in the case of Personal Links
  $sPageTitle .= " ".gettext("For")." : ".$_SESSION['user']->getFullName();
}

require 'Include/Header.php';

if ($_SESSION['user']->isMenuOptionsEnabled() || $personId > 0 && $personId == $_SESSION['user']->getPersonId() ) {
?>
    <p align="center"><button class="btn btn-primary" id="add-new-menu-links"><?= gettext("Add Custom Menu Link") ?></button></p>
<?php 
} else {
?>
    <div class="callout callout-warning"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>

<div class="box box-body">

<table class="table table-striped table-bordered" id="menulinksTable" cellpadding="5" cellspacing="0"  width="100%"></table>

</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.personId  = <?= $personId ?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/MenuLinksList.js" ></script>

<?php
require 'Include/Footer.php';
?>
