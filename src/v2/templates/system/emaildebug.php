<?php
/*******************************************************************************
 *
 *  filename    : debug.php
 *  last change : 2025-07-23
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2025-07-23 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-body">

<p id="mailTest"><?= _("Testing connection .....") ?></p>

</div>

<script src="<?= $sRootPath ?>/skin/js/system/EmailDebug.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

