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

if ( !($_SESSION['user']->isMenuOptionsEnabled() && $_SESSION['user']->isPastoralCareEnabled() ) ) {// only an admin can change this settings, if a pastoral is deleted all the notes will be deleted too...
  Redirect('Menu.php');
  exit;
}

//Set the page title
$sPageTitle = gettext("Pastoral Care Type Editor");

require 'Include/Header.php';

if ($_SESSION['user']->isPastoralCareEnabled()) {
?>
    <div class="callout callout-danger"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Be carefull ! By deleting pastoral care type, the recorded datas for each persons will be lost.') ?></div>

    <p align="center"><button class="btn btn-primary" id="add-new-pastoral-care"><?= gettext("Add a New Pastoral Care Type") ?></button></p>
<?php 
}else {
?>
    <div class="callout callout-warning"><i class="fa fa-warning" aria-hidden="true"></i>   <?= gettext('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>


<div class="box box-body">

<table class="table table-striped table-bordered" id="pastoral-careTable" cellpadding="5" cellspacing="0"  width="100%"></table>

</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/PastoralCareList.js" ></script>

<?php
require 'Include/Footer.php';
?>
