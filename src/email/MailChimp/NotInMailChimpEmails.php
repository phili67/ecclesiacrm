<?php
/*******************************************************************************
 *
 *  filename    : NotInMailChimpEmails.php
 *  last change : 2018-11-12
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require '../../Include/Config.php';
require '../../Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;


//Set the page title
$sPageTitle = gettext('Families Without NewsLetter');

require '../../Include/Header.php'; ?>

<div class="box box-body">
<div class="box-header  with-border">
  <h3 class="box-title"><?= _("Families Without NewsLetter")?></h3>
</div>
<table class="table table-striped table-bordered" id="familiesWithoutEmailTable" cellpadding="5" cellspacing="0"  width="100%"></table>

</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/NotInMailChimpEmails.js" ></script>

<?php
require '../../Include/Footer.php';
?>