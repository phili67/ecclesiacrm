<?php
/*******************************************************************************
 *
 *  filename    : PastoralCareList.php
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

if ( !($_SESSION['user']->isAdmin()) ) {
  Redirect('Menu.php');
  exit;
}

//Set the page title
$sPageTitle = gettext("Pastoral Care Type Editor");

require 'Include/Header.php'; ?>

<div class="box box-body">

<?php if ($_SESSION['user']->isAdmin()) {
?>
    <p align="center"><button class="btn btn-primary" id="add-new-pastoral-care"><?= gettext("Add a New Pastoral Care Type") ?></button></p>
<?php 
}

?>

<table class="table table-striped table-bordered" id="pastoral-careTable" cellpadding="5" cellspacing="0"  width="100%"></table>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/PastoralCareList.js" ></script>

<?php
require 'Include/Footer.php';
?>
