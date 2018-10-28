<?php
/*******************************************************************************
 *
 *  filename    : FundList.php
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


//Set the page title
$sPageTitle = gettext('Donation Fund Editor');

require 'Include/Header.php'; ?>

<div class="callout callout-danger"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Be carefull ! By deleting Fund type, the recorded datas for pledges or payments will be lost.') ?></div>

<div class="box box-body">

<?php if ($_SESSION['user']->isMenuOptionsEnabled()) {
?>
    <p align="center"><button class="btn btn-primary delete-payment" id="add-new-fund"><?= gettext('Add a New Fund') ?></button></p>
<?php 
}

?>

<table class="table table-striped table-bordered" id="fundTable" cellpadding="5" cellspacing="0"  width="100%"></table>

</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/FundList.js" ></script>

<?php
require 'Include/Footer.php';
?>
